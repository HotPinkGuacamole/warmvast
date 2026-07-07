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
 * Surfaces (vloer/dak/gevel) and the energielabel are INDICATIONS derived from the
 * footprint geometry and bouwjaar. They are confirmed during the technische opname.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const WARMVAST_WS_LOCSERVER = 'https://api.pdok.nl/bzk/locatieserver/search/v3_1';
const WARMVAST_WS_BAG_WFS   = 'https://service.pdok.nl/lv/bag/wfs/v2_0';
const WARMVAST_WS_LUCHTFOTO = 'https://service.pdok.nl/hwh/luchtfotorgb/wms/v1_0';

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
	if ( ! $best ) {
		$best = $docs[0];
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
	$d    = 8; // metres around the point.
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
 * Estimate an energy label (indication) from bouwjaar.
 *
 * @return array{letter:string,index:int}  index: 0=A .. 6=G (matches the scale order A..G reversed for display).
 */
function warmvast_ws_energylabel( $bouwjaar ) {
	if ( ! $bouwjaar ) {
		return array( 'letter' => '?', 'index' => 4 );
	}
	if ( $bouwjaar >= 2021 ) { return array( 'letter' => 'A', 'index' => 0 ); }
	if ( $bouwjaar >= 2010 ) { return array( 'letter' => 'B', 'index' => 1 ); }
	if ( $bouwjaar >= 2000 ) { return array( 'letter' => 'C', 'index' => 2 ); }
	if ( $bouwjaar >= 1992 ) { return array( 'letter' => 'D', 'index' => 3 ); }
	if ( $bouwjaar >= 1976 ) { return array( 'letter' => 'E', 'index' => 4 ); }
	if ( $bouwjaar >= 1965 ) { return array( 'letter' => 'F', 'index' => 5 ); }
	return array( 'letter' => 'G', 'index' => 6 );
}

/**
 * Build the aerial WMS GetMap URL around a RD point.
 */
function warmvast_ws_aerial_url( $x, $y, $half = 32 ) {
	$bbox = sprintf( '%.2f,%.2f,%.2f,%.2f', $x - $half, $y - $half, $x + $half, $y + $half );
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
			'height'  => 640,
			'format'  => 'image/jpeg',
		),
		WARMVAST_WS_LUCHTFOTO
	);
}

/**
 * REST callback.
 *
 * @param WP_REST_Request $req Request.
 * @return WP_REST_Response
 */
function warmvast_ws_handle( WP_REST_Request $req ) {
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

	// Surface indications (m²).
	$vloer = (int) round( $area );
	$dak   = (int) round( $area * 1.15 );
	$gevel = (int) round( $perimeter * ( $storeys * $storey_height ) * 0.65 );

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

	$label = warmvast_ws_energylabel( $pand['bouwjaar'] );

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
		),
		'footprint' => round( $area, 1 ),
		'energielabel' => $label,
		'aspect'    => round( $w / $h, 4 ),
		'polygon'   => $poly,
		'aerial'    => warmvast_ws_aerial_url( $addr['rd_x'], $addr['rd_y'] ),
	);

	return new WP_REST_Response( $response, 200 );
}
