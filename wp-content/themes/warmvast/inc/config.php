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
	define( 'WARMVAST_PHONE', '06 47 55 18 93' );
}
if ( ! defined( 'WARMVAST_PHONE_RAW' ) ) {
	define( 'WARMVAST_PHONE_RAW', '+31647551893' );
}
if ( ! defined( 'WARMVAST_EMAIL' ) ) {
	define( 'WARMVAST_EMAIL', 'info@warmvast.nl' );
}
if ( ! defined( 'WARMVAST_HOURS' ) ) {
	define( 'WARMVAST_HOURS', 'Ma t/m vr 08:30 - 17:30' );
}
if ( ! defined( 'WARMVAST_REGION' ) ) {
	define( 'WARMVAST_REGION', 'Heel Nederland' );
}

/**
 * Formspree endpoint for the isolatiescan. Replace REPLACE_WITH_ID with the
 * real form id. The scan JS refuses to submit while this placeholder is present.
 */
if ( ! defined( 'WARMVAST_FORMSPREE' ) ) {
	define( 'WARMVAST_FORMSPREE', 'https://formspree.io/f/REPLACE_WITH_ID' );
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
