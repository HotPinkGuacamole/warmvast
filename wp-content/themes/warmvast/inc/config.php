<?php
/**
 * Warmvast central configuration.
 *
 * Single source of truth for contact data, the Formspree endpoint and the
 * ISDE 2026 tariff table. The tariffs are output to JavaScript via
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
	define( 'WARMVAST_PHONE', '085 500 5070' );
}
if ( ! defined( 'WARMVAST_PHONE_RAW' ) ) {
	define( 'WARMVAST_PHONE_RAW', '+31855005070' );
}
if ( ! defined( 'WARMVAST_EMAIL' ) ) {
	define( 'WARMVAST_EMAIL', 'warmvastisolatie@gmail.com' );
}
if ( ! defined( 'WARMVAST_HOURS' ) ) {
	define( 'WARMVAST_HOURS', 'Ma t/m vr 08:30 - 17:30' );
}
if ( ! defined( 'WARMVAST_REGION' ) ) {
	define( 'WARMVAST_REGION', 'Noord-Holland en Noord-Zuid-Holland' );
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
 * Formspree endpoint for the isolatiescan / woningscan.
 */
if ( ! defined( 'WARMVAST_FORMSPREE' ) ) {
	define( 'WARMVAST_FORMSPREE', 'https://formspree.io/f/xeebwqro' );
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
 * Reviews shown on the site.
 *
 * ⚠️ SAMPLE DATA — replace `items`, `rating`, `count` with real Warmvast reviews
 * (e.g. from Google) before go-live. Brand rule: no fabricated reviews live.
 * Set `verified` to true ONLY when the data is real; that flag also gates the
 * AggregateRating schema output (fake review schema violates Google's policy).
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
