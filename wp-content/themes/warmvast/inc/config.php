<?php
/**
 * Warmvast central configuration.
 *
 * Single source of truth for contact data, the lead-webhook configuration and
 * the ISDE 2026 tariff table. The tariffs are output to JavaScript via
 * wp_localize_script() in functions.php so the calculator and the service
 * pages can never drift apart.
 *
 * NOTE: verify the ISDE tariffs against RVO before go-live.
 * Bron: https://www.rvo.nl/subsidies-financiering/isde/woningeigenaren/isolatiemaatregelen
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WARMVAST_ENV_FILE' ) ) {
	define( 'WARMVAST_ENV_FILE', '/home/container/.warmvast-env' );
}

/**
 * Read supported Warmvast values from the production dotenv file.
 *
 * This deliberately parses a tiny KEY=value subset instead of executing shell
 * syntax. Environment variables still win; this is only a PHP-FPM fallback for
 * hosts that do not preserve arbitrary parent-process environment variables.
 *
 * @param string|null $path Optional file path for tests.
 * @return array<string,string>
 */
function warmvast_env_file_values( $path = null ) {
	$path = null === $path ? WARMVAST_ENV_FILE : (string) $path;
	if ( '' === $path || ! is_file( $path ) || ! is_readable( $path ) ) {
		return array();
	}

	$lines = file( $path, FILE_IGNORE_NEW_LINES );
	if ( false === $lines ) {
		return array();
	}

	$allowed = array(
		'WARMVAST_N8N_LEAD_WEBHOOK_URL'     => true,
		'WARMVAST_N8N_LEAD_WEBHOOK_SECRET'  => true,
		'WARMVAST_TRUSTED_PROXY_IPS'        => true,
		'WARMVAST_GOOGLE_PLACES_API_KEY'    => true,
	);
	$values  = array();

	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' === $line || '#' === substr( $line, 0, 1 ) || false === strpos( $line, '=' ) ) {
			continue;
		}

		list( $key, $value ) = explode( '=', $line, 2 );
		$key = trim( $key );
		if ( ! isset( $allowed[ $key ] ) ) {
			continue;
		}

		$value = trim( $value );
		$quote = '' !== $value ? substr( $value, 0, 1 ) : '';
		if ( '"' === $quote || "'" === $quote ) {
			if ( strlen( $value ) < 2 || substr( $value, -1 ) !== $quote ) {
				continue;
			}
			$value = substr( $value, 1, -1 );
		}

		if ( false !== strpos( $value, "\0" ) || preg_match( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value ) ) {
			continue;
		}

		$values[ $key ] = $value;
	}

	return $values;
}

/**
 * Resolve a Warmvast config value from environment, then dotenv file, then default.
 *
 * @param string      $key      Supported config key.
 * @param string      $default  Default value.
 * @param string|null $env_path Optional dotenv path for tests.
 * @return string
 */
function warmvast_config_value( $key, $default = '', $env_path = null ) {
	$env = getenv( $key );
	if ( false !== $env && '' !== trim( (string) $env ) ) {
		return trim( (string) $env );
	}

	$file_values = warmvast_env_file_values( $env_path );
	if ( isset( $file_values[ $key ] ) && '' !== trim( $file_values[ $key ] ) ) {
		return trim( $file_values[ $key ] );
	}

	return $default;
}

/**
 * Contact details, address, social links and the two "scale-proof claim"
 * numbers (founding year, homes insulated) -- all editable at Warmvast ->
 * Bedrijfsgegevens in wp-admin (see inc/admin.php) without touching code.
 *
 * THE OVERLAY PATTERN (used everywhere in this file for content that is now
 * wp-admin editable): the literal array below is the DEFAULT -- what the
 * site shows on a fresh install where nobody has saved anything yet, and
 * what a field falls back to when someone clears it in wp-admin. A saved
 * `warmvast_opt_company` option is merged on top, key by key
 * (wp_parse_args), so an empty/never-touched field always reads as "use the
 * default" rather than silently going blank sitewide.
 *
 * @return array<string,mixed>
 */
function warmvast_company_defaults() {
	return array(
		'phone'           => '085 800 5070',
		'phone_raw'       => '+31858005070',
		'email'           => 'info@warmvastisolatie.nl',
		'hours'           => 'Ma t/m vr 08:30 - 17:30',
		'region'          => 'Noord-Holland en Noord-Zuid-Holland',
		'address_street'  => 'Albert Schweitzerlaan 37',
		'address_postal'  => '1902 EG',
		'address_city'    => 'Castricum',
		'whatsapp'        => '',
		'twitter_handle'  => 'warmvast',
		'facebook_url'    => 'https://www.facebook.com/profile.php?id=61593140505102',
		'founded'         => 2026,
		'homes_insulated' => 0,
		'warranty_years'  => 0,
	);
}

/**
 * @return array<string,mixed>
 */
function warmvast_company() {
	$saved = get_option( 'warmvast_opt_company', array() );
	return wp_parse_args( is_array( $saved ) ? $saved : array(), warmvast_company_defaults() );
}

$warmvast_company_data = warmvast_company();

if ( ! defined( 'WARMVAST_PHONE' ) ) {
	define( 'WARMVAST_PHONE', $warmvast_company_data['phone'] );
}
if ( ! defined( 'WARMVAST_PHONE_RAW' ) ) {
	define( 'WARMVAST_PHONE_RAW', $warmvast_company_data['phone_raw'] );
}
if ( ! defined( 'WARMVAST_EMAIL' ) ) {
	define( 'WARMVAST_EMAIL', $warmvast_company_data['email'] );
}
if ( ! defined( 'WARMVAST_HOURS' ) ) {
	define( 'WARMVAST_HOURS', $warmvast_company_data['hours'] );
}
if ( ! defined( 'WARMVAST_REGION' ) ) {
	define( 'WARMVAST_REGION', $warmvast_company_data['region'] );
}

/**
 * Vestigingsadres. Used in the LocalBusiness schema and the footer/contact
 * card -- not a walk-in showroom, so it's presented as a registered address
 * rather than "bezoek ons".
 */
if ( ! defined( 'WARMVAST_ADDRESS_STREET' ) ) {
	define( 'WARMVAST_ADDRESS_STREET', $warmvast_company_data['address_street'] );
}
if ( ! defined( 'WARMVAST_ADDRESS_POSTAL' ) ) {
	define( 'WARMVAST_ADDRESS_POSTAL', $warmvast_company_data['address_postal'] );
}
if ( ! defined( 'WARMVAST_ADDRESS_CITY' ) ) {
	define( 'WARMVAST_ADDRESS_CITY', $warmvast_company_data['address_city'] );
}

/**
 * WhatsApp Business number, digits only with country code (e.g. "31647551893",
 * no "+" or spaces — that is what wa.me requires). Leave empty to hide every
 * WhatsApp CTA on the site; nothing else needs to change once a real number
 * is filled in.
 */
if ( ! defined( 'WARMVAST_WHATSAPP' ) ) {
	define( 'WARMVAST_WHATSAPP', $warmvast_company_data['whatsapp'] );
}

/**
 * Real social profile URLs, used for the schema.org `sameAs` array (helps
 * Google associate the profiles with the business entity / Knowledge Panel).
 * Leave a constant empty to omit that profile everywhere -- never fill these
 * with a placeholder/guessed URL, per the site's no-fabricated-trust-signal
 * rule (same reasoning as reviews and the homes-insulated count).
 */
if ( ! defined( 'WARMVAST_TWITTER_HANDLE' ) ) {
	define( 'WARMVAST_TWITTER_HANDLE', $warmvast_company_data['twitter_handle'] );
}
if ( ! defined( 'WARMVAST_TWITTER_URL' ) ) {
	define( 'WARMVAST_TWITTER_URL', WARMVAST_TWITTER_HANDLE ? 'https://x.com/' . WARMVAST_TWITTER_HANDLE : '' );
}
if ( ! defined( 'WARMVAST_FACEBOOK_URL' ) ) {
	define( 'WARMVAST_FACEBOOK_URL', $warmvast_company_data['facebook_url'] );
}

/**
 * Founding year and total homes insulated. Both scale-proof claims that every
 * major competitor states explicitly (Takkenkamp: sinds 1935; Isotech: 32.000
 * woningen) -- and exactly the kind of number this brand must never guess at.
 * WARMVAST_HOMES_INSULATED stays at 0 until a real count is entered in
 * wp-admin -- deliberately NOT defaulted to a placeholder number: an
 * exact-sounding figure like "112" reads as a factual claim to consumers,
 * and a false one is an oneerlijke handelspraktijk under Dutch/EU consumer
 * law, not just a cosmetic placeholder. Every place that would show it
 * checks for a truthy value first, so it simply stays invisible rather than
 * showing a fake number.
 */
if ( ! defined( 'WARMVAST_FOUNDED' ) ) {
	define( 'WARMVAST_FOUNDED', (int) $warmvast_company_data['founded'] );
}
if ( ! defined( 'WARMVAST_HOMES_INSULATED' ) ) {
	define( 'WARMVAST_HOMES_INSULATED', (int) $warmvast_company_data['homes_insulated'] );
}

/**
 * Warranty length in years on materiaal + uitvoering. VENIN-aangesloten
 * bedrijven bieden doorgaans standaard 10 jaar garantie op spouwmuurisolatie;
 * dit is echter een keuze van Warmvast zelf en dus geen aanname. 0 = niet
 * tonen totdat een echte garantietermijn is vastgesteld.
 */
if ( ! defined( 'WARMVAST_WARRANTY_YEARS' ) ) {
	define( 'WARMVAST_WARRANTY_YEARS', (int) $warmvast_company_data['warranty_years'] );
}
unset( $warmvast_company_data );

/**
 * n8n lead webhook — where a woningscan lead goes.
 *
 * The browser does NOT call n8n. It posts to this site's own REST route
 * (see inc/lead.php), which validates the submission and forwards a minimal,
 * Header-Auth-protected payload server-to-server. n8n owns everything downstream:
 * contact matching, deal creation, Teamleader OAuth and IDs. WordPress knows
 * none of that on purpose.
 *
 * The webhook URL is public routing information; the header-auth secret is not.
 * Prefer host environment variables so deployment can recreate wp-config.php
 * without losing the integration. While either value is empty the lead endpoint
 * refuses the submission and the visitor is told to phone us instead --
 * deliberately loud, because the one thing that must never happen is a lead
 * silently vanishing.
 *
 * n8n's Webhook node verifies X-Warmvast-Webhook-Secret before the workflow runs.
 */
if ( ! defined( 'WARMVAST_N8N_LEAD_WEBHOOK_URL' ) ) {
	define(
		'WARMVAST_N8N_LEAD_WEBHOOK_URL',
		warmvast_config_value( 'WARMVAST_N8N_LEAD_WEBHOOK_URL', 'https://n8n.warmvastisolatie.nl/webhook/warmvast-site-lead' )
	);
}
if ( ! defined( 'WARMVAST_N8N_LEAD_WEBHOOK_SECRET' ) ) {
	define( 'WARMVAST_N8N_LEAD_WEBHOOK_SECRET', warmvast_config_value( 'WARMVAST_N8N_LEAD_WEBHOOK_SECRET' ) );
}

/**
 * Trusted reverse proxies for client-IP detection.
 *
 * Empty by default, which means REMOTE_ADDR is always used. On production,
 * set this in the host environment/server config to Endurer's real proxy
 * IP/CIDR values, comma-separated, e.g.:
 *   WARMVAST_TRUSTED_PROXY_IPS=10.0.0.5,10.0.0.6,2001:db8::/48
 *
 * Forwarded headers are inspected only when the immediate REMOTE_ADDR is in
 * this allowlist. Never set this from frontend JavaScript.
 */
if ( ! defined( 'WARMVAST_TRUSTED_PROXY_IPS' ) ) {
	define( 'WARMVAST_TRUSTED_PROXY_IPS', warmvast_config_value( 'WARMVAST_TRUSTED_PROXY_IPS' ) );
}

/**
 * Test whether an IP address belongs to a CIDR range.
 *
 * @param string $ip   Valid IP address.
 * @param string $cidr CIDR block, e.g. 192.0.2.0/24 or 2001:db8::/32.
 * @return bool
 */
function warmvast_ip_in_cidr( $ip, $cidr ) {
	$parts = explode( '/', (string) $cidr, 2 );
	if ( 2 !== count( $parts ) || '' === trim( $parts[0] ) || '' === trim( $parts[1] ) || ! ctype_digit( trim( $parts[1] ) ) ) {
		return false;
	}

	$range = trim( $parts[0] );
	$bits  = (int) trim( $parts[1] );
	if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) || ! filter_var( $range, FILTER_VALIDATE_IP ) ) {
		return false;
	}

	$ip_packed    = @inet_pton( $ip );
	$range_packed = @inet_pton( $range );
	if ( false === $ip_packed || false === $range_packed || strlen( $ip_packed ) !== strlen( $range_packed ) ) {
		return false;
	}

	$max_bits = 4 === strlen( $ip_packed ) ? 32 : 128;
	if ( $bits < 0 || $bits > $max_bits ) {
		return false;
	}

	$full_bytes = intdiv( $bits, 8 );
	$remainder  = $bits % 8;
	if ( $full_bytes > 0 && substr( $ip_packed, 0, $full_bytes ) !== substr( $range_packed, 0, $full_bytes ) ) {
		return false;
	}
	if ( 0 === $remainder ) {
		return true;
	}

	$mask = ( 0xff << ( 8 - $remainder ) ) & 0xff;
	return ( ord( $ip_packed[ $full_bytes ] ) & $mask ) === ( ord( $range_packed[ $full_bytes ] ) & $mask );
}

/**
 * Parse trusted proxy configuration into valid exact IPs and CIDR ranges.
 *
 * Invalid entries are ignored so a typo does not make arbitrary forwarded
 * headers trusted.
 *
 * @param string|null $trusted_proxy_ips Optional raw config for tests.
 * @return array<int,string>
 */
function warmvast_trusted_proxy_entries( $trusted_proxy_ips = null ) {
	$raw     = null === $trusted_proxy_ips && defined( 'WARMVAST_TRUSTED_PROXY_IPS' ) ? WARMVAST_TRUSTED_PROXY_IPS : (string) $trusted_proxy_ips;
	$entries = array();

	foreach ( explode( ',', (string) $raw ) as $entry ) {
		$entry = trim( $entry );
		if ( '' === $entry ) {
			continue;
		}
		if ( false !== strpos( $entry, '/' ) ) {
			$parts = explode( '/', $entry, 2 );
			if ( 2 === count( $parts ) && warmvast_ip_in_cidr( trim( $parts[0] ), $entry ) ) {
				$entries[] = $entry;
			}
			continue;
		}
		if ( filter_var( $entry, FILTER_VALIDATE_IP ) ) {
			$entries[] = $entry;
		}
	}

	return $entries;
}

/**
 * Check whether an IP is configured as a trusted immediate proxy.
 *
 * @param string      $ip                IP address to test.
 * @param string|null $trusted_proxy_ips Optional raw config for tests.
 * @return bool
 */
function warmvast_ip_is_trusted_proxy( $ip, $trusted_proxy_ips = null ) {
	if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
		return false;
	}

	foreach ( warmvast_trusted_proxy_entries( $trusted_proxy_ips ) as $entry ) {
		if ( false !== strpos( $entry, '/' ) ) {
			if ( warmvast_ip_in_cidr( $ip, $entry ) ) {
				return true;
			}
			continue;
		}
		if ( $ip === $entry ) {
			return true;
		}
	}

	return false;
}

/**
 * Return the safest client IP for server-side throttling.
 *
 * REMOTE_ADDR is always the fallback. X-Forwarded-For is inspected only when
 * REMOTE_ADDR is a configured trusted proxy. The full chain is then walked
 * from the trusted/right side back toward the client, returning the first
 * untrusted valid IP. Malformed or uncertain headers fall back to REMOTE_ADDR.
 *
 * @param array<string,mixed>|null $server            Optional server array for tests.
 * @param string|null              $trusted_proxy_ips Optional raw config for tests.
 * @return string
 */
function warmvast_get_client_ip( $server = null, $trusted_proxy_ips = null ) {
	$server     = null === $server ? $_SERVER : $server;
	$remote_raw = isset( $server['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $server['REMOTE_ADDR'] ) ) : '';
	$remote     = filter_var( $remote_raw, FILTER_VALIDATE_IP ) ? $remote_raw : '';

	if ( '' === $remote ) {
		return $remote_raw;
	}
	if ( ! warmvast_ip_is_trusted_proxy( $remote, $trusted_proxy_ips ) ) {
		return $remote;
	}

	$xff_raw = isset( $server['HTTP_X_FORWARDED_FOR'] ) ? trim( (string) wp_unslash( $server['HTTP_X_FORWARDED_FOR'] ) ) : '';
	if ( '' === $xff_raw ) {
		return $remote;
	}

	$chain = array();
	foreach ( explode( ',', $xff_raw ) as $part ) {
		$ip = trim( $part );
		if ( '' === $ip || ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return $remote;
		}
		$chain[] = $ip;
	}
	$chain[] = $remote;

	for ( $i = count( $chain ) - 1; $i >= 0; $i-- ) {
		if ( ! warmvast_ip_is_trusted_proxy( $chain[ $i ], $trusted_proxy_ips ) ) {
			return $chain[ $i ];
		}
	}

	return $remote;
}

/**
 * EP-Online Public API key for registered energy labels.
 *
 * Request a key via EP-Online/RVO and define it here or in wp-config.php. When
 * empty, the woningscan shows that the registered label was not fetched.
 * Docs: https://public.ep-online.nl/swagger/index.html
 */
if ( ! defined( 'WARMVAST_EP_ONLINE_API_KEY' ) ) {
	define( 'WARMVAST_EP_ONLINE_API_KEY', '' );
}

/**
 * Google Places API key, for live reviews (see inc/reviews.php). Same
 * resolution chain as the n8n webhook secret: host environment preferred,
 * then the production dotenv file, then empty. Server-to-server only --
 * the browser never sees this key. Empty = the reviews section falls back
 * to the wp-admin-maintained list (Warmvast -> Reviews).
 */
if ( ! defined( 'WARMVAST_GOOGLE_PLACES_API_KEY' ) ) {
	define( 'WARMVAST_GOOGLE_PLACES_API_KEY', warmvast_config_value( 'WARMVAST_GOOGLE_PLACES_API_KEY' ) );
}

/**
 * Google Tag Manager container ID (e.g. "GTM-XXXXXXX").
 *
 * Every conversion event (scan_start, scan_subsidy_seen, scan_submit_success,
 * phone_click, cta_click, etc. -- see assets/js/main.js and woningscan.js)
 * already dispatches to window.dataLayer / window.gtag. Nothing is listening
 * until a real container ID is set here; the GTM snippet in header.php
 * activates automatically once it is, no other change needed.
 */
if ( ! defined( 'WARMVAST_GTM_ID' ) ) {
	define( 'WARMVAST_GTM_ID', '' );
}

/**
 * ISDE 2026 tariff table (basisbedrag per m2, min/max m2).
 *
 * Editable at Warmvast -> ISDE-tarieven in wp-admin (see inc/admin.php),
 * but ONLY the numeric fields (baseRate/minM2/maxM2) -- label/short/field/
 * slug always come from the literal array below and are never overlaid, on
 * purpose. `slug` in particular drives page-template routing (see
 * warmvast_page_template_fallback() in functions.php) and `field` drives
 * the woningscan's m²-per-bouwdeel mapping; those are wiring, not content,
 * and a typo there breaks the app rather than just reading oddly.
 *
 * @return array<string,array<string,mixed>>
 */
function warmvast_isde_rates() {
	$defaults = array(
		'spouw' => array(
			'label'    => 'Spouwmuurisolatie',
			'short'    => 'Spouw',
			'baseRate' => 5.25,
			'minM2'    => 10,
			'maxM2'    => 170,
			'field'    => 'm2_spouw',
			'slug'     => 'spouwmuurisolatie',
		),
		'vloer' => array(
			'label'    => 'Vloerisolatie',
			'short'    => 'Vloer',
			'baseRate' => 5.50,
			'minM2'    => 20,
			'maxM2'    => 130,
			'field'    => 'm2_vloer',
			'slug'     => 'vloerisolatie',
		),
		'glas'  => array(
			'label'    => 'HR++ glas',
			'short'    => 'Glas',
			'baseRate' => 25.00,
			'minM2'    => 3,
			'maxM2'    => 45,
			'field'    => 'm2_glas',
			'slug'     => 'glasisolatie-hr',
		),
		'dak'   => array(
			'label'    => 'Dakisolatie',
			'short'    => 'Dak',
			'baseRate' => 16.25,
			'minM2'    => 20,
			'maxM2'    => 200,
			'field'    => 'm2_dak',
			'slug'     => 'dakisolatie',
		),
	);

	$saved = get_option( 'warmvast_opt_isde_rates', array() );
	if ( ! is_array( $saved ) ) {
		return $defaults;
	}
	foreach ( $defaults as $key => $rate ) {
		if ( ! isset( $saved[ $key ] ) || ! is_array( $saved[ $key ] ) ) {
			continue;
		}
		if ( isset( $saved[ $key ]['baseRate'] ) ) {
			$defaults[ $key ]['baseRate'] = max( 0, (float) $saved[ $key ]['baseRate'] );
		}
		if ( isset( $saved[ $key ]['minM2'] ) ) {
			$defaults[ $key ]['minM2'] = max( 0, (int) $saved[ $key ]['minM2'] );
		}
		if ( isset( $saved[ $key ]['maxM2'] ) ) {
			// Never allow a saved value to leave maxM2 below minM2 -- the
			// admin form's own clamp is the first line of defense, this is
			// the one that actually matters (it runs on every read).
			$defaults[ $key ]['maxM2'] = max( $defaults[ $key ]['minM2'], (int) $saved[ $key ]['maxM2'] );
		}
	}
	return $defaults;
}

/**
 * The order measures are presented to (and read back from) the visitor.
 *
 * The scan's measure checkboxes render in this order, and a lead's
 * `measures` array is emitted in it too, so what the customer saw and what
 * lands in the CRM read the same way round. Keys are validated against
 * warmvast_isde_rates(), so this can never introduce an unknown measure.
 *
 * @return array<int,string>
 */
function warmvast_measure_order() {
	return array( 'dak', 'spouw', 'vloer', 'glas' );
}

/**
 * Reviews shown on the site, in priority order:
 *
 *  1. Live from Google Places API, if WARMVAST_GOOGLE_PLACES_API_KEY and a
 *     Place ID are both configured (see inc/reviews.php). `verified` is
 *     implied true here -- data read live from Google needs no separate
 *     human flag the way hand-typed content does.
 *  2. The wp-admin-maintained list (Warmvast -> Reviews, see inc/admin.php),
 *     if saved. This is also the fallback when the Google fetch fails.
 *  3. The array below: a pre-launch SAMPLE SHAPE, `verified => false`, kept
 *     only as a shape reference and never shown to a visitor -- see the
 *     overlay-pattern note on warmvast_company() for why an empty/untouched
 *     field always falls through like this rather than showing nothing.
 *
 * Whichever source wins, `verified` staying false is what actually matters:
 * while false the reviews section does not render AT ALL (see
 * template-parts/reviews.php) and no AggregateRating schema is emitted --
 * enforced in code (inc/admin.php's save handler, and unconditionally here
 * for the Google path), not just by convention.
 *
 * @return array<string,mixed>
 */
function warmvast_reviews() {
	$google = warmvast_google_reviews_fetch();
	if ( is_array( $google ) && ! empty( $google['items'] ) ) {
		return array(
			'verified' => true,
			'source'   => 'Google',
			'rating'   => $google['rating'],
			'count'    => $google['count'],
			'items'    => $google['items'],
			'url'      => $google['url'], // each review card links out here -- see template-parts/reviews.php.
		);
	}

	$defaults = array(
		'verified' => false, // set true (in wp-admin) once `items` are real -> enables review schema.
		'source'   => 'Google',
		'rating'   => 4.8,
		'count'    => 127,
		'url'      => '', // no Maps deep link for hand-entered/sample reviews -- the card just doesn't link out.
		'items'    => array(
			array(
				'name'  => 'Familie de Vries',
				'place' => 'Utrecht',
				'stars' => 5,
				'text'  => 'Heldere opname en een eerlijk verhaal over wat wél en niet kon. De m²-berekening klopte precies met de offerte.',
			),
			array(
				'name'  => 'J. Bakker',
				'place' => 'Apeldoorn',
				'stars' => 5,
				'text'  => 'Spouw en vloer laten doen. De verdubbeling van de subsidie werd vooraf duidelijk uitgelegd, geen verrassingen achteraf.',
			),
			array(
				'name'  => 'Mevr. Jansen',
				'place' => 'Breda',
				'stars' => 5,
				'text'  => 'Netjes gewerkt en het subsidiedossier met foto’s helemaal geregeld. Binnen een dag reactie na de scan.',
			),
		),
	);

	$saved = get_option( 'warmvast_opt_reviews', null );
	return is_array( $saved ) ? wp_parse_args( $saved, $defaults ) : $defaults;
}

/**
 * Afgeronde projecten voor de /ons-werk/ galerij. Editable at Warmvast ->
 * Ons werk in wp-admin (see inc/admin.php).
 *
 * ⚠️ EMPTY BY DESIGN until real ones are added. Every competitor shows a
 * project gallery with real photos; Warmvast has none yet. Brand rule: no
 * fabricated projects or stock photography posing as real work. /ons-werk/
 * checks `empty()` and shows an honest "binnenkort" state (with a CTA into
 * the scan) instead of a populated gallery until real projects exist.
 *
 * @return array<int,array<string,mixed>>
 */
function warmvast_projecten() {
	$saved = get_option( 'warmvast_opt_projecten', null );
	return is_array( $saved ) ? $saved : array();
}

/**
 * Certificeringen / keurmerken (KOMO, VENIN, SKG-IKOB, VCA, ISO 9001, etc.).
 * Editable at Warmvast -> Certificeringen in wp-admin (see inc/admin.php).
 *
 * Every top-5 Dutch insulation company shows these prominently and they are
 * legally meaningful marks — showing one Warmvast does not actually hold
 * would be misleading (and likely a trademark violation), so this returns an
 * empty array until real certification data is supplied. Every place that
 * renders keurmerken (footer, trust sections, /kwaliteit-en-garantie/) checks
 * `empty()` first and simply omits the block when there is nothing real to
 * show — never a placeholder badge.
 *
 * @return array<int,array{naam:string,beschrijving:string,url:string,logo:string}>
 */
function warmvast_certificeringen() {
	$saved = get_option( 'warmvast_opt_certificeringen', null );
	return is_array( $saved ) ? $saved : array();
}

/**
 * Het team achter Warmvast. Real people, real photos (assets/img/team/) —
 * no stock photography, no invented names. Editable at Warmvast -> Team in
 * wp-admin (see inc/admin.php); order there is the order they render in on
 * /over-warmvast/ and wherever a team credit is shown.
 *
 * `foto` is a BASE path with no -<width>.webp suffix: each portrait ships as
 * width variants (400 / 960) consumed via warmvast_responsive_img(). A photo
 * added via the wp-admin media picker is a single full-size URL instead --
 * warmvast_responsive_img() falls back to rendering that verbatim when no
 * matching -<width>.webp variant exists (see inc/template-tags.php).
 *
 * @return array<int,array{key:string,naam:string,functie:string,bio:string,foto:string}>
 */
function warmvast_team() {
	$defaults = array(
		array(
			'key'     => 'levi',
			'naam'    => 'Levi Kok',
			'functie' => 'Uitvoering',
			'bio'     => 'Levi staat zelf op de vloer, in de spouw of op zolder. Hij voert de isolatie uit en laat een woning nooit half af achter.',
			'foto'    => '/assets/img/team/team-makita-cap',
		),
		array(
			'key'     => 'alex',
			'naam'    => 'Alex de Jong',
			'functie' => 'Backoffice',
			'bio'     => 'Alex regelt de planning, offertes en het subsidiedossier, zodat u niet zelf achter formulieren en meldcodes aan hoeft.',
			'foto'    => '/assets/img/team/team-krullen-bril',
		),
		array(
			'key'     => 'noah',
			'naam'    => 'Noah',
			'functie' => 'Technisch specialist',
			'bio'     => 'Noah kent isolatiematerialen en -technieken van binnen en buiten, en bepaalt per woning wat technisch het beste werkt.',
			'foto'    => '/assets/img/team/team-golvend-haar',
		),
	);

	$saved = get_option( 'warmvast_opt_team', null );
	return is_array( $saved ) ? $saved : $defaults;
}

/**
 * Kernwaarden — a compact badge strip restating Warmvast's own werkwijze
 * (already described in full on /kwaliteit-en-garantie/). Unlike
 * warmvast_certificeringen() these are NOT third-party certification claims
 * (no KOMO/VCA/InstallQ-style logos), so there is no trademark or misleading-
 * claim risk: it is just Warmvast's own true statements about how it works,
 * safe to show before any external certification is obtained. Editable at
 * Warmvast -> Kernwaarden in wp-admin (see inc/admin.php).
 *
 * @return array<int,array{icon:string,naam:string}>
 */
function warmvast_kernwaarden() {
	$defaults = array(
		array(
			'icon' => 'ruler',
			'naam' => 'Technische opname vooraf',
		),
		array(
			'icon' => 'shield',
			'naam' => 'Vakkundige uitvoering',
		),
		array(
			'icon' => 'camera',
			'naam' => 'Fotobewijs voor uw dossier',
		),
		array(
			'icon' => 'map',
			'naam' => 'Actief in ' . WARMVAST_REGION,
		),
	);

	$saved = get_option( 'warmvast_opt_kernwaarden', null );
	return is_array( $saved ) ? $saved : $defaults;
}

/**
 * Indicative yearly energy-bill saving per m² per measure (€/m²/jaar).
 *
 * ⚠️ INDICATION ONLY. Real savings depend on the current state of the home,
 * gas price and usage. Documented assumptions; surfaced with a disclaimer.
 * Tune against Milieu Centraal / your own data before leaning on these hard.
 *
 * @return array<string,float>
 */
function warmvast_savings_factors() {
	return array(
		'spouw' => 4.5,
		'vloer' => 3.5,
		'dak'   => 4.0,
		'glas'  => 6.0,
	);
}

/**
 * Format a euro amount the Dutch way, no decimals: € 1.234.
 *
 * @param float $value Amount.
 * @return string
 */
function warmvast_euro( $value ) {
	return '€ ' . number_format( (float) $value, 0, ',', '.' );
}

/**
 * Format a per-m2 rate: € 5,25.
 *
 * @param float $value Amount.
 * @return string
 */
function warmvast_rate( $value ) {
	return '€ ' . number_format( (float) $value, 2, ',', '.' );
}
