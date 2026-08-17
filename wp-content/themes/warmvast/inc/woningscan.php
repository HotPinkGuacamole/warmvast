<?php
/**
 * Woningscan backend — address-driven building scan.
 *
 * Public REST endpoint: GET /wp-json/warmvast/v1/woningscan?postcode=..&huisnummer=..&toevoeging=..
 *
 * Data sources (all PDOK, open, keyless):
 *  - Locatieserver : postcode+huisnummer -> address + RD coordinate + adresseerbaarobject id
 *  - BAG WFS (bbox): building footprint polygon + bouwjaar + gebruiksdoel + #verblijfsobjecten
 *  - Luchtfoto WMS : aerial photo (URL returned; the browser loads the image directly)
 *
 * Surfaces (vloer/dak/gevel/glas) are INDICATIONS derived from footprint
 * geometry and bouwjaar; glas is a window-to-facade ratio estimate since BAG
 * has no glazing data. The energielabel uses public EP-Online search first and
 * falls back to bouwjaar when no registered label can be read.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const WARMVAST_WS_LOCSERVER = 'https://api.pdok.nl/bzk/locatieserver/search/v3_1';
const WARMVAST_WS_BAG_WFS   = 'https://service.pdok.nl/lv/bag/wfs/v2_0';
const WARMVAST_WS_LUCHTFOTO = 'https://service.pdok.nl/hwh/luchtfotorgb/wms/v1_0';
const WARMVAST_WS_EP_ONLINE = 'https://public.ep-online.nl/api/v5';
const WARMVAST_WS_EP_SITE   = 'https://www.ep-online.nl';

/**
 * Register the REST route.
 */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'warmvast/v1',
			'/woningscan',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'args'                => array(
					'postcode'   => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
					'huisnummer' => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
					'toevoeging' => array( 'required' => false, 'sanitize_callback' => 'sanitize_text_field' ),
				),
				'callback'            => 'warmvast_ws_handle',
			)
		);
	}
);

/**
 * HTTP GET a JSON endpoint via WP HTTP API.
 *
 * @param string $url URL.
 * @return array|null Decoded JSON or null.
 */
function warmvast_ws_get_json( $url ) {
	$res = wp_remote_get( $url, array( 'timeout' => 8, 'headers' => array( 'Accept' => 'application/json' ) ) );
	if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
		return null;
	}
	$data = json_decode( wp_remote_retrieve_body( $res ), true );
	return is_array( $data ) ? $data : null;
}

/**
 * Parse "POINT(x y)" -> array(x, y) floats.
 *
 * @param string $wkt WKT point.
 * @return array{0:float,1:float}|null
 */
function warmvast_ws_parse_point( $wkt ) {
	if ( ! $wkt || ! preg_match( '/POINT\(([-0-9.]+)\s+([-0-9.]+)\)/', $wkt, $m ) ) {
		return null;
	}
	return array( (float) $m[1], (float) $m[2] );
}

/**
 * Look up the address via Locatieserver.
 *
 * @return array|null
 */
function warmvast_ws_lookup_address( $postcode, $huisnummer, $toevoeging ) {
	$postcode = strtoupper( preg_replace( '/\s+/', '', $postcode ) );
	$q        = rawurlencode( trim( $postcode . ' ' . $huisnummer . ' ' . $toevoeging ) );
	$fl       = 'weergavenaam,straatnaam,huisnummer,huisletter,huisnummertoevoeging,postcode,woonplaatsnaam,adresseerbaarobject_id,centroide_rd,type';
	$url      = WARMVAST_WS_LOCSERVER . "/free?q={$q}&rows=12&fq=type:adres&fl={$fl}";
	$data     = warmvast_ws_get_json( $url );
	if ( empty( $data['response']['docs'] ) ) {
		return null;
	}

	$docs = $data['response']['docs'];
	$want = strtolower( preg_replace( '/\s+/', '', (string) $toevoeging ) );

	// Prefer an exact postcode + huisnummer (+ toevoeging/huisletter) match.
	$best = null;
	foreach ( $docs as $d ) {
		if ( 'adres' !== ( $d['type'] ?? '' ) ) {
			continue;
		}
		$pc = strtoupper( preg_replace( '/\s+/', '', $d['postcode'] ?? '' ) );
		if ( $pc !== $postcode || (string) ( $d['huisnummer'] ?? '' ) !== (string) $huisnummer ) {
			continue;
		}
		$suffix = strtolower( preg_replace( '/\s+/', '', ( $d['huisletter'] ?? '' ) . ( $d['huisnummertoevoeging'] ?? '' ) ) );
		if ( '' === $want ) {
			$best = $d;
			break;
		}
		if ( $suffix === $want ) {
			$best = $d;
			break;
		}
		if ( null === $best ) {
			$best = $d; // fallback: same pc+nr, different toevoeging.
		}
	}
	// No candidate matched both postcode AND huisnummer: treat as not found.
	// (Never fall back to the top free-text hit -- that can silently return an
	// unrelated address for a typo'd input, which is exactly what this scan
	// must never do.)
	if ( ! $best ) {
		return null;
	}

	$rd = warmvast_ws_parse_point( $best['centroide_rd'] ?? '' );
	if ( ! $rd ) {
		return null;
	}

	return array(
		'weergavenaam' => $best['weergavenaam'] ?? '',
		'straat'       => $best['straatnaam'] ?? '',
		'huisnummer'   => $best['huisnummer'] ?? $huisnummer,
		'toevoeging'   => trim( ( $best['huisletter'] ?? '' ) . ' ' . ( $best['huisnummertoevoeging'] ?? '' ) ),
		'adresseerbaarobject_id' => $best['adresseerbaarobject_id'] ?? '',
		'postcode'     => $best['postcode'] ?? $postcode,
		'woonplaats'   => $best['woonplaatsnaam'] ?? '',
		'rd_x'         => $rd[0],
		'rd_y'         => $rd[1],
	);
}

/**
 * Find the BAG pand at a RD point via a small bbox query.
 *
 * @param float $x RD easting.
 * @param float $y RD northing.
 * @return array|null Pand feature properties + polygon.
 */
function warmvast_ws_lookup_pand( $x, $y ) {
	// Metres around the address point. Wide enough that large/complex buildings
	// (apartment blocks, where the geocoded address point can sit well away from
	// the pand's own centroid) still land in the candidate set; the point-in-
	// polygon / nearest-centroid logic below stays exactly as precise either way.
	$d    = 15;
	$bbox = sprintf( '%.3f,%.3f,%.3f,%.3f,urn:ogc:def:crs:EPSG::28992', $x - $d, $y - $d, $x + $d, $y + $d );
	$url  = WARMVAST_WS_BAG_WFS . '?service=WFS&version=2.0.0&request=GetFeature&typeNames=bag:pand'
		. '&outputFormat=application/json&srsName=EPSG:28992&count=25&bbox=' . rawurlencode( $bbox );
	$data = warmvast_ws_get_json( $url );
	if ( empty( $data['features'] ) ) {
		return null;
	}

	// Prefer the pand whose polygon contains the point; else the nearest bbox centre.
	$contain = null;
	$nearest = null;
	$best_d  = PHP_FLOAT_MAX;
	foreach ( $data['features'] as $f ) {
		if ( 'Polygon' !== ( $f['geometry']['type'] ?? '' ) ) {
			continue;
		}
		$ring = $f['geometry']['coordinates'][0];
		if ( warmvast_ws_point_in_ring( $x, $y, $ring ) ) {
			$contain = $f;
			break;
		}
		$bb  = $f['bbox'];
		$cx  = ( $bb[0] + $bb[2] ) / 2;
		$cy  = ( $bb[1] + $bb[3] ) / 2;
		$dst = ( $cx - $x ) * ( $cx - $x ) + ( $cy - $y ) * ( $cy - $y );
		if ( $dst < $best_d ) {
			$best_d  = $dst;
			$nearest = $f;
		}
	}
	$f = $contain ? $contain : $nearest;
	if ( ! $f ) {
		return null;
	}

	$ring = $f['geometry']['coordinates'][0];
	$p    = $f['properties'];
	return array(
		'identificatie'  => $p['identificatie'] ?? '',
		'bouwjaar'       => isset( $p['bouwjaar'] ) ? (int) $p['bouwjaar'] : null,
		'gebruiksdoel'   => $p['gebruiksdoel'] ?? '',
		'verblijfsobj'   => isset( $p['aantal_verblijfsobjecten'] ) ? (int) $p['aantal_verblijfsobjecten'] : 1,
		'ring'           => $ring,
	);
}

/**
 * Ray-casting point-in-polygon on a RD ring [[x,y],...].
 */
function warmvast_ws_point_in_ring( $px, $py, $ring ) {
	$inside = false;
	$n      = count( $ring );
	for ( $i = 0, $j = $n - 1; $i < $n; $j = $i++ ) {
		$xi = $ring[ $i ][0]; $yi = $ring[ $i ][1];
		$xj = $ring[ $j ][0]; $yj = $ring[ $j ][1];
		$intersect = ( ( $yi > $py ) !== ( $yj > $py ) )
			&& ( $px < ( $xj - $xi ) * ( $py - $yi ) / ( ( $yj - $yi ) ?: 1e-9 ) + $xi );
		if ( $intersect ) {
			$inside = ! $inside;
		}
	}
	return $inside;
}

/**
 * Shoelace area (m²) of a RD ring.
 */
function warmvast_ws_ring_area( $ring ) {
	$a = 0.0;
	$n = count( $ring );
	for ( $i = 0, $j = $n - 1; $i < $n; $j = $i++ ) {
		$a += ( $ring[ $j ][0] + $ring[ $i ][0] ) * ( $ring[ $j ][1] - $ring[ $i ][1] );
	}
	return abs( $a ) / 2.0;
}

/**
 * Perimeter (m) of a RD ring.
 */
function warmvast_ws_ring_perimeter( $ring ) {
	$p = 0.0;
	$n = count( $ring );
	for ( $i = 0; $i < $n - 1; $i++ ) {
		$dx = $ring[ $i + 1 ][0] - $ring[ $i ][0];
		$dy = $ring[ $i + 1 ][1] - $ring[ $i ][1];
		$p += sqrt( $dx * $dx + $dy * $dy );
	}
	return $p;
}

/**
 * Resolve an energy label's position on the display scale.
 *
 * @param string $letter Energy label, e.g. A++++, A, F.
 * @return int
 */
function warmvast_ws_energylabel_index( $letter ) {
	$order = array( 'A++++', 'A+++', 'A++', 'A+', 'A', 'B', 'C', 'D', 'E', 'F', 'G' );
	$idx   = array_search( strtoupper( trim( (string) $letter ) ), $order, true );
	return false === $idx ? 8 : (int) $idx;
}

/**
 * Estimate an energy label (indication) from bouwjaar.
 *
 * @return array<string,mixed>
 */
function warmvast_ws_energylabel_from_bouwjaar( $bouwjaar, $reason = 'not_found' ) {
	$letter = '?';
	$basis  = 'Geen geregistreerd EP-Online label gevonden; indicatie op basis van bouwjaar.';

	if ( ! $bouwjaar ) {
		return array(
			'letter' => $letter,
			'index'  => -1,
			'source' => 'bouwjaar',
			'status' => $reason,
			'basis'  => $basis,
		);
	}
	if ( $bouwjaar >= 2021 ) { $letter = 'A'; }
	elseif ( $bouwjaar >= 2010 ) { $letter = 'B'; }
	elseif ( $bouwjaar >= 2000 ) { $letter = 'C'; }
	elseif ( $bouwjaar >= 1992 ) { $letter = 'D'; }
	elseif ( $bouwjaar >= 1976 ) { $letter = 'E'; }
	elseif ( $bouwjaar >= 1965 ) { $letter = 'F'; }
	else { $letter = 'G'; }

	if ( 'not_found' === $reason ) {
		$basis = 'Geen geregistreerd EP-Online label gevonden; indicatie op basis van bouwjaar ' . (int) $bouwjaar . '.';
	} else {
		$basis = 'Indicatie op basis van bouwjaar ' . (int) $bouwjaar . '.';
	}

	return array(
		'letter' => $letter,
		'index'  => warmvast_ws_energylabel_index( $letter ),
		'source' => 'bouwjaar',
		'status' => $reason,
		'basis'  => $basis,
	);
}

/**
 * Extract a value from one EP-Online result card.
 *
 * @param string $card  Result card HTML.
 * @param string $label Field label.
 * @return string
 */
function warmvast_ws_ep_card_value( $card, $label ) {
	$pattern = '~<div[^>]*class="[^"]*se-item-description-nta[^"]*"[^>]*>\s*<span[^>]*>\s*'
		. preg_quote( $label, '~' )
		. '\s*</span>\s*</div>\s*<div[^>]*class="[^"]*se-item-value-nta[^"]*"[^>]*>\s*<span[^>]*>(.*?)</span>~is';

	if ( ! preg_match( $pattern, $card, $m ) ) {
		return '';
	}

	return trim( html_entity_decode( wp_strip_all_tags( $m[1] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
}

/**
 * Fetch the registered label from EP-Online's public single-address search.
 *
 * This mirrors the public website search intended for one-off address checks.
 *
 * @param array<string,mixed> $addr Address from Locatieserver.
 * @return array<string,mixed>
 */
function warmvast_ws_public_energylabel( $addr ) {
	$query = trim(
		( $addr['postcode'] ?? '' ) . ' '
		. ( $addr['huisnummer'] ?? '' ) . ' '
		. ( $addr['toevoeging'] ?? '' )
	);
	if ( '' === trim( $query ) ) {
		return array( 'found' => false, 'reason' => 'missing_address_id' );
	}

	$cache_key = 'warmvast_ep_public_' . md5( $query . '|' . ( $addr['adresseerbaarobject_id'] ?? '' ) );
	$cached    = get_transient( $cache_key );
	if ( false !== $cached ) {
		return is_array( $cached ) ? $cached : array( 'found' => false, 'reason' => 'lookup_error' );
	}

	$home = wp_remote_get(
		WARMVAST_WS_EP_SITE . '/',
		array(
			'timeout' => 8,
			'headers' => array( 'Accept' => 'text/html' ),
		)
	);
	if ( is_wp_error( $home ) || 200 !== wp_remote_retrieve_response_code( $home ) ) {
		$miss = array( 'found' => false, 'reason' => 'lookup_error' );
		set_transient( $cache_key, $miss, HOUR_IN_SECONDS );
		return $miss;
	}

	$body = wp_remote_retrieve_body( $home );
	if ( ! preg_match( '/name="__RequestVerificationToken"\s+type="hidden"\s+value="([^"]+)"/', $body, $m ) ) {
		$miss = array( 'found' => false, 'reason' => 'lookup_error' );
		set_transient( $cache_key, $miss, HOUR_IN_SECONDS );
		return $miss;
	}

	$res = wp_remote_post(
		WARMVAST_WS_EP_SITE . '/Energylabel/Search',
		array(
			'timeout' => 10,
			'headers' => array(
				'Accept'  => 'text/html',
				'Referer' => WARMVAST_WS_EP_SITE . '/',
			),
			'cookies' => wp_remote_retrieve_cookies( $home ),
			'body'    => array(
				'SearchValue'                => $query,
				'__RequestVerificationToken' => html_entity_decode( $m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
			),
		)
	);
	if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
		$miss = array( 'found' => false, 'reason' => 'lookup_error' );
		set_transient( $cache_key, $miss, HOUR_IN_SECONDS );
		return $miss;
	}

	$html = wp_remote_retrieve_body( $res );
	if ( ! preg_match_all( '/<article\b.*?<\/article>/is', $html, $matches ) ) {
		$miss = array( 'found' => false, 'reason' => 'not_found' );
		set_transient( $cache_key, $miss, DAY_IN_SECONDS );
		return $miss;
	}

	$target_id = preg_replace( '/\D+/', '', (string) ( $addr['adresseerbaarobject_id'] ?? '' ) );
	$best      = null;
	foreach ( $matches[0] as $card ) {
		$vbo    = preg_replace( '/\D+/', '', warmvast_ws_ep_card_value( $card, 'BAG verblijfsobject id' ) );
		$letter = strtoupper( warmvast_ws_ep_card_value( $card, 'Labelklasse' ) );
		if ( '' === $letter ) {
			continue;
		}
		$row = array(
			'vbo'          => $vbo,
			'letter'       => $letter,
			'registeredAt' => warmvast_ws_ep_card_value( $card, 'Registratiedatum' ),
			'validUntil'   => warmvast_ws_ep_card_value( $card, 'Geldig tot' ),
		);
		if ( $target_id && $vbo === $target_id ) {
			$best = $row;
			break;
		}
		if ( null === $best ) {
			$best = $row;
		}
	}

	if ( ! $best ) {
		$miss = array( 'found' => false, 'reason' => 'not_found' );
		set_transient( $cache_key, $miss, DAY_IN_SECONDS );
		return $miss;
	}

	$label = array(
		'found'  => true,
		'letter' => $best['letter'],
		'index'  => warmvast_ws_energylabel_index( $best['letter'] ),
		'source' => 'ep-online-public',
		'status' => 'registered',
		'basis'  => 'Geregistreerd energielabel uit EP-Online.',
	);
	if ( ! empty( $best['validUntil'] ) && '-' !== $best['validUntil'] ) {
		$label['validUntil'] = $best['validUntil'];
	}
	if ( ! empty( $best['registeredAt'] ) && '-' !== $best['registeredAt'] ) {
		$label['registeredAt'] = $best['registeredAt'];
	}

	set_transient( $cache_key, $label, DAY_IN_SECONDS );
	return $label;
}

/**
 * Fetch the registered EP-Online label for a BAG addressable object.
 *
 * @param string $adresseerbaarobject_id BAG VBO id.
 * @return array<string,mixed>|null
 */
function warmvast_ws_registered_energylabel( $adresseerbaarobject_id ) {
	if ( ! defined( 'WARMVAST_EP_ONLINE_API_KEY' ) || '' === trim( WARMVAST_EP_ONLINE_API_KEY ) ) {
		return array( 'found' => false, 'reason' => 'not_configured' );
	}
	if ( ! $adresseerbaarobject_id ) {
		return array( 'found' => false, 'reason' => 'missing_address_id' );
	}

	$cache_key = 'warmvast_ep_label_' . md5( $adresseerbaarobject_id );
	$cached    = get_transient( $cache_key );
	if ( false !== $cached ) {
		return is_array( $cached ) ? $cached : array( 'found' => false, 'reason' => 'not_found' );
	}

	$url = WARMVAST_WS_EP_ONLINE . '/PandEnergielabel/AdresseerbaarObject/' . rawurlencode( $adresseerbaarobject_id );
	$res = wp_remote_get(
		$url,
		array(
			'timeout' => 8,
			'headers' => array(
				'Accept'        => 'application/json',
				'Authorization' => WARMVAST_EP_ONLINE_API_KEY,
			),
		)
	);

	if ( is_wp_error( $res ) ) {
		$miss = array( 'found' => false, 'reason' => 'lookup_error' );
		set_transient( $cache_key, $miss, HOUR_IN_SECONDS );
		return $miss;
	}
	$code = wp_remote_retrieve_response_code( $res );
	if ( 404 === $code ) {
		$miss = array( 'found' => false, 'reason' => 'not_found' );
		set_transient( $cache_key, $miss, DAY_IN_SECONDS );
		return $miss;
	}
	if ( 200 !== $code ) {
		$miss = array( 'found' => false, 'reason' => 'lookup_error' );
		set_transient( $cache_key, $miss, HOUR_IN_SECONDS );
		return $miss;
	}

	$data = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( empty( $data ) || ! is_array( $data ) ) {
		$miss = array( 'found' => false, 'reason' => 'not_found' );
		set_transient( $cache_key, $miss, HOUR_IN_SECONDS );
		return $miss;
	}
	$row    = isset( $data[0] ) && is_array( $data[0] ) ? $data[0] : $data;
	$letter = isset( $row['Energieklasse'] ) ? strtoupper( trim( (string) $row['Energieklasse'] ) ) : '';
	if ( '' === $letter ) {
		$miss = array( 'found' => false, 'reason' => 'not_found' );
		set_transient( $cache_key, $miss, HOUR_IN_SECONDS );
		return $miss;
	}

	$label = array(
		'found'  => true,
		'letter' => $letter,
		'index'  => warmvast_ws_energylabel_index( $letter ),
		'source' => 'ep-online',
		'status' => 'registered',
		'basis'  => 'Geregistreerd energielabel uit EP-Online.',
	);
	if ( ! empty( $row['Geldig_tot'] ) ) {
		$label['validUntil'] = substr( (string) $row['Geldig_tot'], 0, 10 );
	}
	if ( ! empty( $row['Registratiedatum'] ) ) {
		$label['registeredAt'] = substr( (string) $row['Registratiedatum'], 0, 10 );
	}

	set_transient( $cache_key, $label, DAY_IN_SECONDS );
	return $label;
}

/**
 * Build the aerial WMS GetMap URL around a RD point.
 */
function warmvast_ws_aerial_url( $x, $y, $half_x = 32, $half_y = 24 ) {
	$bbox = sprintf( '%.2f,%.2f,%.2f,%.2f', $x - $half_x, $y - $half_y, $x + $half_x, $y + $half_y );
	return add_query_arg(
		array(
			'service' => 'WMS',
			'request' => 'GetMap',
			'version' => '1.3.0',
			'layers'  => 'Actueel_orthoHR',
			'styles'  => '',
			'crs'     => 'EPSG:28992',
			'bbox'    => $bbox,
			'width'   => 640,
			'height'  => 480,
			'format'  => 'image/jpeg',
		),
		WARMVAST_WS_LUCHTFOTO
	);
}

/**
 * Normalise a RD polygon ring to the aerial WMS image coordinate space.
 *
 * @param array<int,array{0:float,1:float}> $ring RD polygon ring.
 * @param float                             $x    RD centre easting.
 * @param float                             $y    RD centre northing.
 * @param float                             $half_x Half of WMS bbox width in metres.
 * @param float                             $half_y Half of WMS bbox height in metres.
 * @return array<int,array{0:float,1:float}>
 */
function warmvast_ws_aerial_polygon( $ring, $x, $y, $half_x = 32, $half_y = 24 ) {
	$minx = $x - $half_x;
	$maxx = $x + $half_x;
	$miny = $y - $half_y;
	$maxy = $y + $half_y;
	$w    = max( 0.001, $maxx - $minx );
	$h    = max( 0.001, $maxy - $miny );
	$poly = array();

	foreach ( $ring as $pt ) {
		$poly[] = array(
			round( ( (float) $pt[0] - $minx ) / $w, 5 ),
			round( ( $maxy - (float) $pt[1] ) / $h, 5 ),
		);
	}

	return $poly;
}

/**
 * Simple per-IP rate limit for the public woningscan endpoint. Each request
 * proxies up to four external HTTP calls (PDOK Locatieserver + BAG WFS, and
 * EP-Online's home page + search), so this guards against the endpoint being
 * used to hammer those services through our own server. The ceiling is
 * generous enough for a real visitor retrying a typo'd address several times.
 *
 * @return bool True when the request may proceed.
 */
function warmvast_ws_rate_limit_ok() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	if ( '' === $ip ) {
		return true; // can't identify the caller; don't block.
	}
	$key   = 'warmvast_ws_rl_' . md5( $ip );
	$count = (int) get_transient( $key );
	if ( $count >= 20 ) {
		return false;
	}
	set_transient( $key, $count + 1, MINUTE_IN_SECONDS );
	return true;
}

/**
 * REST callback.
 *
 * @param WP_REST_Request $req Request.
 * @return WP_REST_Response
 */
function warmvast_ws_handle( WP_REST_Request $req ) {
	if ( ! warmvast_ws_rate_limit_ok() ) {
		return new WP_REST_Response( array( 'ok' => false, 'error' => 'Te veel aanvragen vanaf dit adres. Probeer het over een minuut opnieuw.' ), 429 );
	}

	$postcode   = $req->get_param( 'postcode' );
	$huisnummer = preg_replace( '/[^0-9]/', '', $req->get_param( 'huisnummer' ) );
	$toevoeging = $req->get_param( 'toevoeging' );

	if ( ! preg_match( '/^[0-9]{4}\s?[A-Za-z]{2}$/', trim( $postcode ) ) || '' === $huisnummer ) {
		return new WP_REST_Response( array( 'ok' => false, 'error' => 'Vul een geldige postcode en huisnummer in.' ), 200 );
	}

	$addr = warmvast_ws_lookup_address( $postcode, $huisnummer, $toevoeging );
	if ( ! $addr ) {
		return new WP_REST_Response( array( 'ok' => false, 'error' => 'We konden dit adres niet vinden. Controleer postcode en huisnummer.' ), 200 );
	}

	$pand = warmvast_ws_lookup_pand( $addr['rd_x'], $addr['rd_y'] );
	if ( ! $pand ) {
		return new WP_REST_Response( array( 'ok' => false, 'error' => 'We konden geen gebouwgegevens ophalen voor dit adres.' ), 200 );
	}

	$area      = warmvast_ws_ring_area( $pand['ring'] );
	$perimeter = warmvast_ws_ring_perimeter( $pand['ring'] );
	$complex   = $pand['verblijfsobj'] > 4;

	// Storeys estimate: default 2 for a house; conservative for indication.
	$storeys       = 2;
	$storey_height = 2.8;

	// Surface indications (m²). Facade total is split into the net insulatable
	// wall area (spouw) and an indicative window area (glas), both derived from
	// the same perimeter × height figure so they stay internally consistent.
	$facade_total = $perimeter * ( $storeys * $storey_height );
	$vloer = (int) round( $area );
	$dak   = (int) round( $area * 1.15 );
	$gevel = (int) round( $facade_total * 0.65 );
	$glas  = (int) round( $facade_total * 0.20 );

	// Normalise the footprint ring to a 0..1 box (y flipped for screen), preserve aspect.
	$xs = array_column( $pand['ring'], 0 );
	$ys = array_column( $pand['ring'], 1 );
	$minx = min( $xs ); $maxx = max( $xs );
	$miny = min( $ys ); $maxy = max( $ys );
	$w = max( 0.001, $maxx - $minx );
	$h = max( 0.001, $maxy - $miny );
	$scale = 1 / max( $w, $h );
	$poly  = array();
	foreach ( $pand['ring'] as $pt ) {
		$poly[] = array(
			round( ( $pt[0] - $minx ) * $scale, 4 ),
			round( ( $maxy - $pt[1] ) * $scale, 4 ), // flip Y for screen space
		);
	}

	$label = warmvast_ws_public_energylabel( $addr );
	if ( empty( $label['found'] ) ) {
		$label = warmvast_ws_registered_energylabel( $addr['adresseerbaarobject_id'] );
	}
	if ( empty( $label['found'] ) ) {
		$label = warmvast_ws_energylabel_from_bouwjaar( $pand['bouwjaar'], isset( $label['reason'] ) ? $label['reason'] : 'not_found' );
	}

	$aerial_half_x = 32;
	$aerial_half_y = 24;
	$response = array(
		'ok'        => true,
		'address'   => array(
			'straat'      => $addr['straat'],
			'huisnummer'  => $addr['huisnummer'],
			'toevoeging'  => trim( $addr['toevoeging'] ),
			'postcode'    => $addr['postcode'],
			'woonplaats'  => $addr['woonplaats'],
			'weergave'    => trim( $addr['straat'] . ' ' . $addr['huisnummer'] . ' ' . $addr['toevoeging'] ),
		),
		'bouwjaar'  => $pand['bouwjaar'],
		'gebruiksdoel' => $pand['gebruiksdoel'],
		'geschikt'  => ( false !== strpos( (string) $pand['gebruiksdoel'], 'woonfunctie' ) ),
		'complex'   => $complex,
		'surfaces'  => array(
			'vloer' => $vloer,
			'dak'   => $dak,
			'spouw' => $gevel,
			'glas'  => $glas,
		),
		'footprint' => round( $area, 1 ),
		'energielabel' => $label,
		'aspect'    => round( $w / $h, 4 ),
		'polygon'   => $poly,
		'aerialPolygon' => warmvast_ws_aerial_polygon( $pand['ring'], $addr['rd_x'], $addr['rd_y'], $aerial_half_x, $aerial_half_y ),
		'aerial'    => warmvast_ws_aerial_url( $addr['rd_x'], $addr['rd_y'], $aerial_half_x, $aerial_half_y ),
	);

	return new WP_REST_Response( $response, 200 );
}
