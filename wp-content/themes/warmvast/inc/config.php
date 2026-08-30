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

/**
 * Contact details. Override in a child theme or via a real options page later.
 * Defaults are placeholders — replace with the real Warmvast data before launch.
 */
if ( ! defined( 'WARMVAST_PHONE' ) ) {
	define( 'WARMVAST_PHONE', '085 800 5070' );
}
if ( ! defined( 'WARMVAST_PHONE_RAW' ) ) {
	define( 'WARMVAST_PHONE_RAW', '+31858005070' );
}
if ( ! defined( 'WARMVAST_EMAIL' ) ) {
	define( 'WARMVAST_EMAIL', 'info@warmvastisolatie.nl' );
}
if ( ! defined( 'WARMVAST_HOURS' ) ) {
	define( 'WARMVAST_HOURS', 'Ma t/m vr 08:30 - 17:30' );
}
if ( ! defined( 'WARMVAST_REGION' ) ) {
	define( 'WARMVAST_REGION', 'Noord-Holland en Noord-Zuid-Holland' );
}

/**
 * Vestigingsadres. Used in the LocalBusiness schema and the footer/contact
 * card -- not a walk-in showroom, so it's presented as a registered address
 * rather than "bezoek ons".
 */
if ( ! defined( 'WARMVAST_ADDRESS_STREET' ) ) {
	define( 'WARMVAST_ADDRESS_STREET', 'Albert Schweitzerlaan 37' );
}
if ( ! defined( 'WARMVAST_ADDRESS_POSTAL' ) ) {
	define( 'WARMVAST_ADDRESS_POSTAL', '1902 EG' );
}
if ( ! defined( 'WARMVAST_ADDRESS_CITY' ) ) {
	define( 'WARMVAST_ADDRESS_CITY', 'Castricum' );
}

/**
 * WhatsApp Business number, digits only with country code (e.g. "31647551893",
 * no "+" or spaces — that is what wa.me requires). Leave empty to hide every
 * WhatsApp CTA on the site; nothing else needs to change once a real number
 * is filled in.
 */
if ( ! defined( 'WARMVAST_WHATSAPP' ) ) {
	define( 'WARMVAST_WHATSAPP', '' );
}

/**
 * Real social profile URLs, used for the schema.org `sameAs` array (helps
 * Google associate the profiles with the business entity / Knowledge Panel).
 * Leave a constant empty to omit that profile everywhere -- never fill these
 * with a placeholder/guessed URL, per the site's no-fabricated-trust-signal
 * rule (same reasoning as reviews and the homes-insulated count).
 */
if ( ! defined( 'WARMVAST_TWITTER_HANDLE' ) ) {
	define( 'WARMVAST_TWITTER_HANDLE', 'warmvast' );
}
if ( ! defined( 'WARMVAST_TWITTER_URL' ) ) {
	define( 'WARMVAST_TWITTER_URL', 'https://x.com/' . WARMVAST_TWITTER_HANDLE );
}
if ( ! defined( 'WARMVAST_FACEBOOK_URL' ) ) {
	define( 'WARMVAST_FACEBOOK_URL', 'https://www.facebook.com/profile.php?id=61593140505102' );
}

/**
 * Founding year and total homes insulated. Both scale-proof claims that every
 * major competitor states explicitly (Takkenkamp: sinds 1935; Isotech: 32.000
 * woningen) -- and exactly the kind of number this brand must never guess at.
 * WARMVAST_FOUNDED is real (confirmed by the owner). WARMVAST_HOMES_INSULATED
 * stays at 0 until a real count exists -- deliberately NOT filled with a
 * placeholder number: an exact-sounding figure like "112" reads as a factual
 * claim to consumers, and a false one is an oneerlijke handelspraktijk under
 * Dutch/EU consumer law, not just a cosmetic placeholder. Every place that
 * would show it checks for a truthy value first, so it simply stays invisible
 * rather than showing a fake number.
 */
if ( ! defined( 'WARMVAST_FOUNDED' ) ) {
	define( 'WARMVAST_FOUNDED', 2026 );
}
if ( ! defined( 'WARMVAST_HOMES_INSULATED' ) ) {
	define( 'WARMVAST_HOMES_INSULATED', 0 );
}

/**
 * Warranty length in years on materiaal + uitvoering. VENIN-aangesloten
 * bedrijven bieden doorgaans standaard 10 jaar garantie op spouwmuurisolatie;
 * dit is echter een keuze van Warmvast zelf en dus geen aanname. 0 = niet
 * tonen totdat een echte garantietermijn is vastgesteld.
 */
if ( ! defined( 'WARMVAST_WARRANTY_YEARS' ) ) {
	define( 'WARMVAST_WARRANTY_YEARS', 0 );
}

/**
 * n8n lead webhook — where a woningscan lead goes.
 *
 * The browser does NOT call n8n. It posts to this site's own REST route
 * (see inc/lead.php), which validates the submission and forwards a minimal,
 * HMAC-signed payload server-to-server. n8n owns everything downstream:
 * contact matching, deal creation, Teamleader OAuth and IDs. WordPress knows
 * none of that on purpose.
 *
 * The webhook URL is public routing information; the HMAC secret is not.
 * Prefer host environment variables so deployment can recreate wp-config.php
 * without losing the integration. While either value is empty the lead endpoint
 * refuses the submission and the visitor is told to phone us instead --
 * deliberately loud, because the one thing that must never happen is a lead
 * silently vanishing.
 *
 * The signature n8n must verify is:
 *   HMAC_SHA256( "<X-Warmvast-Timestamp>.<raw JSON body>", <secret> )
 */
if ( ! defined( 'WARMVAST_N8N_LEAD_WEBHOOK_URL' ) ) {
	define(
		'WARMVAST_N8N_LEAD_WEBHOOK_URL',
		getenv( 'WARMVAST_N8N_LEAD_WEBHOOK_URL' ) ?: 'https://n8n.warmvastisolatie.nl/webhook/warmvast-site-lead'
	);
}
if ( ! defined( 'WARMVAST_N8N_LEAD_WEBHOOK_SECRET' ) ) {
	define( 'WARMVAST_N8N_LEAD_WEBHOOK_SECRET', getenv( 'WARMVAST_N8N_LEAD_WEBHOOK_SECRET' ) ?: '' );
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
	define( 'WARMVAST_TRUSTED_PROXY_IPS', getenv( 'WARMVAST_TRUSTED_PROXY_IPS' ) ?: '' );
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
 * @return array<string,array<string,mixed>>
 */
function warmvast_isde_rates() {
	return array(
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
 * Reviews shown on the site.
 *
 * ⚠️ SAMPLE DATA — replace `items`, `rating`, `count` with real Warmvast reviews
 * (e.g. from Google) before go-live. Brand rule: no fabricated reviews live.
 *
 * `verified` must stay false until every field here is real. While it is
 * false the reviews section does not render AT ALL (see
 * template-parts/reviews.php) and no AggregateRating schema is emitted --
 * the sample rows below are kept only as a shape reference for whoever fills
 * in the real ones, and are never shown to a visitor.
 *
 * @return array<string,mixed>
 */
function warmvast_reviews() {
	return array(
		'verified' => false, // set true once `items` are real -> enables review schema.
		'source'   => 'Google',
		'rating'   => 4.8,
		'count'    => 127,
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
}

/**
 * Afgeronde projecten voor de /ons-werk/ galerij.
 *
 * ⚠️ EMPTY BY DESIGN. Every competitor shows a project gallery with real
 * photos; Warmvast has none yet. Brand rule: no fabricated projects or stock
 * photography posing as real work. /ons-werk/ checks `empty()` and shows an
 * honest "binnenkort" state (with a CTA into the scan) instead of a populated
 * gallery until real projects are supplied.
 *
 * Add one as: array( 'titel' => 'Jaren 30-woning, spouw + vloer',
 * 'plaats' => 'Enschede', 'maatregelen' => array('spouw','vloer'),
 * 'datum' => '2026-08', 'foto' => WARMVAST_URI . '/assets/img/projecten/...jpg' ).
 *
 * @return array<int,array<string,mixed>>
 */
function warmvast_projecten() {
	return array();
}

/**
 * Certificeringen / keurmerken (KOMO, VENIN, SKG-IKOB, VCA, ISO 9001, etc.).
 *
 * Every top-5 Dutch insulation company shows these prominently and they are
 * legally meaningful marks — showing one Warmvast does not actually hold
 * would be misleading (and likely a trademark violation), so this returns an
 * empty array until real certification data is supplied. Every place that
 * renders keurmerken (footer, trust sections, /kwaliteit-en-garantie/) checks
 * `empty()` first and simply omits the block when there is nothing real to
 * show — never a placeholder badge.
 *
 * To add one once certified: array( 'naam' => 'KOMO', 'beschrijving' =>
 * 'Procescertificaat voor na-isolatie', 'url' => 'https://...', 'logo' =>
 * WARMVAST_URI . '/assets/img/keurmerken/komo.svg' ).
 *
 * @return array<int,array{naam:string,beschrijving:string,url:string,logo:string}>
 */
function warmvast_certificeringen() {
	return array();
}

/**
 * Het team achter Warmvast. Real people, real photos (assets/img/team/) —
 * no stock photography, no invented names. Order here is the order they
 * render in on /over-warmvast/ and wherever a team credit is shown.
 *
 * `foto` is a BASE path with no -<width>.webp suffix: each portrait ships as
 * width variants (400 / 960) consumed via warmvast_responsive_img().
 *
 * @return array<int,array{key:string,naam:string,functie:string,bio:string,foto:string}>
 */
function warmvast_team() {
	return array(
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
}

/**
 * Kernwaarden — a compact badge strip restating Warmvast's own werkwijze
 * (already described in full on /kwaliteit-en-garantie/). Unlike
 * warmvast_certificeringen() these are NOT third-party certification claims
 * (no KOMO/VCA/InstallQ-style logos), so there is no trademark or misleading-
 * claim risk: it is just Warmvast's own true statements about how it works,
 * safe to show before any external certification is obtained.
 *
 * @return array<int,array{icon:string,naam:string}>
 */
function warmvast_kernwaarden() {
	return array(
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
