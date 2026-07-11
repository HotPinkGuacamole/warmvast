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
 * Serve /robots.txt directly, independent of WordPress's virtual-robots
 * rewrite rule. Core only registers that rule when the site is installed at
 * the domain root (see WP_Rewrite::rewrite_rules()); on a subdirectory
 * install — like this one during local development at /warmvast/ — the rule
 * is never added and the request falls through to a 404. Detecting the
 * request ourselves and reusing core's own do_robots() (so blog_public,
 * the robots_txt filter, etc. all still apply) keeps this correct regardless
 * of install path or rewrite-rule state.
 */
function warmvast_maybe_serve_robots_txt() {
	if ( warmvast_seo_plugin_active() || is_robots() ) {
		return; // core already handles it when the rewrite rule exists.
	}
	$site_path    = trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
	$request_path = trim( (string) wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), PHP_URL_PATH ), '/' );
	$expected     = trim( $site_path . '/robots.txt', '/' );
	if ( $request_path !== $expected ) {
		return;
	}
	// The main query already ran and marked this a 404 (no page matches
	// "robots.txt"); override that status before core's own robots handler
	// prints the correct body.
	status_header( 200 );
	do_robots();
	exit;
}
add_action( 'template_redirect', 'warmvast_maybe_serve_robots_txt', 0 );

/**
 * Google Tag Manager <head> snippet. No-ops until WARMVAST_GTM_ID is set.
 * Called directly from header.php (GTM must load as high in <head> as
 * possible, before wp_head()'s other output).
 */
function warmvast_gtm_head() {
	if ( ! WARMVAST_GTM_ID ) {
		return;
	}
	$id = esc_js( WARMVAST_GTM_ID );
	echo "\n" . '<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({"gtm.start":new Date().getTime(),event:"gtm.js"});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!="dataLayer"?"&l="+l:"";j.async=true;j.src="https://www.googletagmanager.com/gtm.js?id="+i+dl;f.parentNode.insertBefore(j,f);})(window,document,"script","dataLayer","' . $id . '");</script>' . "\n";
}

/**
 * Google Tag Manager <noscript> fallback. Must sit immediately after the
 * opening <body> tag (Google's own placement requirement) -- called from
 * header.php before wp_body_open().
 */
function warmvast_gtm_body() {
	if ( ! WARMVAST_GTM_ID ) {
		return;
	}
	printf(
		'<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=%1$s" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' . "\n",
		esc_attr( WARMVAST_GTM_ID )
	);
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
	echo '<meta name="theme-color" content="#1b1f21">' . "\n";
}
add_action( 'wp_head', 'warmvast_head_meta', 5 );

/**
 * Favicon / app-icon link tags. Called directly from header.php <head>
 * (WordPress prints no site icon of its own unless one is set in the
 * Customizer). All assets are generated from the brand icon mark and live in
 * assets/img/favicons/.
 */
function warmvast_favicon_links() {
	$d = '/assets/img/favicons';
	printf( '<link rel="icon" href="%s" sizes="any">' . "\n", warmvast_asset( $d . '/favicon.ico' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- warmvast_asset escapes.
	printf( '<link rel="icon" type="image/png" sizes="32x32" href="%s">' . "\n", warmvast_asset( $d . '/favicon-32x32.png' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	printf( '<link rel="icon" type="image/png" sizes="16x16" href="%s">' . "\n", warmvast_asset( $d . '/favicon-16x16.png' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	printf( '<link rel="apple-touch-icon" sizes="180x180" href="%s">' . "\n", warmvast_asset( $d . '/apple-touch-icon.png' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	printf( '<link rel="manifest" href="%s">' . "\n", warmvast_asset( $d . '/site.webmanifest' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

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
