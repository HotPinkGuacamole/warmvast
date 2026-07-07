<?php
/**
 * Lightweight SEO: meta description + Open Graph + LocalBusiness/FAQ schema.
 * Deliberately minimal — swap for Yoast/RankMath later without conflict
 * (guards on whether those are active).
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Skip our meta output if a dedicated SEO plugin is active.
 *
 * @return bool
 */
function warmvast_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' );
}

/**
 * Resolve a sensible meta description for the current view.
 *
 * @return string
 */
function warmvast_meta_description() {
	$default = 'Warmvast helpt woningeigenaren isoleren op basis van feiten: technische opname, helder m²-overzicht en een realistische ISDE-indicatie. Start de gratis isolatiescan.';

	if ( is_front_page() ) {
		return $default;
	}
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post && ! empty( $post->post_excerpt ) ) {
			return wp_strip_all_tags( $post->post_excerpt );
		}
		if ( $post instanceof WP_Post ) {
			$text = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
			$text = trim( preg_replace( '/\s+/', ' ', $text ) );
			if ( $text ) {
				return mb_substr( $text, 0, 155 );
			}
		}
	}
	return $default;
}

/**
 * Output meta description + Open Graph tags.
 */
function warmvast_head_meta() {
	if ( warmvast_seo_plugin_active() ) {
		return;
	}
	$desc  = warmvast_meta_description();
	$title = wp_get_document_title();

	echo "\n" . '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta property="og:type" content="website">' . "\n";
	echo '<meta property="og:site_name" content="Warmvast">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( ( is_singular() ) ? get_permalink() : home_url( add_query_arg( array(), null ) ) ) . '">' . "\n";
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="theme-color" content="#0b6b5b">' . "\n";
}
add_action( 'wp_head', 'warmvast_head_meta', 5 );

/**
 * LocalBusiness structured data (site-wide, in the footer).
 */
function warmvast_schema_localbusiness() {
	if ( warmvast_seo_plugin_active() ) {
		return;
	}
	$data = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'HomeAndConstructionBusiness',
		'name'        => 'Warmvast',
		'description' => 'Isolatiebedrijf voor woningeigenaren: spouwmuur-, vloer-, glas- en dakisolatie met ISDE-subsidiebegeleiding.',
		'url'         => home_url( '/' ),
		'telephone'   => WARMVAST_PHONE_RAW,
		'email'       => WARMVAST_EMAIL,
		'areaServed'  => 'NL',
		'priceRange'  => '€€',
		'address'     => array(
			'@type'           => 'PostalAddress',
			'addressCountry'  => 'NL',
		),
		'openingHours' => 'Mo-Fr 08:30-17:30',
	);
	echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>' . "\n";
}
add_action( 'wp_footer', 'warmvast_schema_localbusiness' );

/**
 * Helper: emit FAQPage schema from an array of Q&A pairs.
 *
 * @param array<int,array{q:string,a:string}> $faqs FAQ pairs.
 */
function warmvast_faq_schema( $faqs ) {
	if ( warmvast_seo_plugin_active() || empty( $faqs ) ) {
		return;
	}
	$items = array();
	foreach ( $faqs as $faq ) {
		$items[] = array(
			'@type'          => 'Question',
			'name'           => $faq['q'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => wp_strip_all_tags( $faq['a'] ),
			),
		);
	}
	$data = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $items,
	);
	echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>' . "\n";
}
