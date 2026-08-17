<?php
/**
 * Warmvast theme functions.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WARMVAST_VERSION', '1.0.0' );
define( 'WARMVAST_DIR', get_template_directory() );
define( 'WARMVAST_URI', get_template_directory_uri() );

require_once WARMVAST_DIR . '/inc/config.php';
require_once WARMVAST_DIR . '/inc/template-tags.php';
require_once WARMVAST_DIR . '/inc/service-content.php';
require_once WARMVAST_DIR . '/inc/gemeente-content.php';
require_once WARMVAST_DIR . '/inc/woningscan.php';
require_once WARMVAST_DIR . '/inc/article-visuals.php';
require_once WARMVAST_DIR . '/inc/seo.php';

/**
 * Theme setup.
 */
function warmvast_setup() {
	load_theme_textdomain( 'warmvast', WARMVAST_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support( 'custom-logo', array( 'height' => 40, 'width' => 160, 'flex-width' => true, 'flex-height' => true ) );
	add_theme_support( 'responsive-embeds' );

	register_nav_menus(
		array(
			'primary' => __( 'Hoofdmenu', 'warmvast' ),
			'footer'  => __( 'Footer menu', 'warmvast' ),
		)
	);

	add_image_size( 'warmvast_card', 720, 480, true );
}
add_action( 'after_setup_theme', 'warmvast_setup' );

/**
 * Enqueue styles and scripts.
 */
function warmvast_assets() {
	// Main stylesheet. File modification time as version for cache busting in dev.
	$css_path = WARMVAST_DIR . '/assets/css/main.css';
	$css_ver  = file_exists( $css_path ) ? filemtime( $css_path ) : WARMVAST_VERSION;
	wp_enqueue_style( 'warmvast-main', WARMVAST_URI . '/assets/css/main.css', array(), $css_ver );

	// Site interactions (nav, accordions, analytics hooks). Carries the shared data object.
	$js_path = WARMVAST_DIR . '/assets/js/main.js';
	$js_ver  = file_exists( $js_path ) ? filemtime( $js_path ) : WARMVAST_VERSION;
	wp_enqueue_script( 'warmvast-main', WARMVAST_URI . '/assets/js/main.js', array(), $js_ver, true );

	// Single source of truth: PHP tariffs + REST url + savings -> JS. Attached to
	// warmvast-main so it prints before any dependent script.
	wp_localize_script(
		'warmvast-main',
		'WARMVAST_SCAN',
		array(
			'endpoint' => WARMVAST_FORMSPREE,
			'rates'    => warmvast_isde_rates(),
			'phone'    => WARMVAST_PHONE,
			'restUrl'  => esc_url_raw( rest_url( 'warmvast/v1/woningscan' ) ),
			'savings'  => warmvast_savings_factors(),
		)
	);

	// The address-driven woningscan (primary scan everywhere). Self-guards on #warmvast-woningscan.
	$ws_path = WARMVAST_DIR . '/assets/js/woningscan.js';
	$ws_ver  = file_exists( $ws_path ) ? filemtime( $ws_path ) : WARMVAST_VERSION;
	wp_enqueue_script( 'warmvast-woningscan', WARMVAST_URI . '/assets/js/woningscan.js', array( 'warmvast-main' ), $ws_ver, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'warmvast_assets' );

/**
 * Shortcode: [warmvast_isolatiescan] — renders the address-driven woningscan.
 * (The legacy multi-step form is retired; the shortcode name is kept for
 * backwards compatibility.) Accepts an optional measure attribute
 * (spouw|vloer|glas|dak) to preselect that measure, e.g.
 * [warmvast_isolatiescan measure="dak"].
 */
function warmvast_isolatiescan_shortcode( $atts ) {
	$atts  = shortcode_atts( array( 'measure' => '' ), $atts, 'warmvast_isolatiescan' );
	$rates = warmvast_isde_rates();

	global $warmvast_scan_preselect;
	$warmvast_scan_preselect = isset( $rates[ $atts['measure'] ] ) ? $atts['measure'] : '';

	ob_start();
	get_template_part( 'template-parts/woningscan' );
	$html = ob_get_clean();

	$warmvast_scan_preselect = '';
	return $html;
}
add_shortcode( 'warmvast_isolatiescan', 'warmvast_isolatiescan_shortcode' );

/**
 * Register footer/sidebar widget areas (kept minimal; footer is hand-built).
 */
function warmvast_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Kennisbank zijbalk', 'warmvast' ),
			'id'            => 'kennisbank-sidebar',
			'description'   => __( 'Verschijnt naast kennisbankartikelen.', 'warmvast' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget__title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'warmvast_widgets_init' );

/**
 * Excerpt tweaks.
 */
add_filter( 'excerpt_length', function () { return 26; } );
add_filter( 'excerpt_more', function () { return '&hellip;'; } );

/**
 * Performance / hygiene: trim head clutter that a lean marketing site doesn't need.
 */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );

// Disable emoji script/style bloat.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );

/**
 * Body classes for template-aware styling.
 */
add_filter(
	'body_class',
	function ( $classes ) {
		if ( is_front_page() ) {
			$classes[] = 'is-front';
		}
		if ( is_page() ) {
			$classes[] = 'is-page';
		}
		return $classes;
	}
);

/**
 * Fallback for the primary menu when none is assigned yet. Flat list only (no
 * dropdown support) -- mirrors every top-level destination in header.php's own
 * hand-built menu, which is what actually renders while no WP menu exists.
 */
function warmvast_primary_menu_fallback() {
	$items = array(
		'Isolatie'                => home_url( '/isolatie/' ),
		'Subsidie'                => home_url( '/subsidie-service/' ),
		'Gemeentes'               => home_url( '/gemeentes/' ),
		'Over ons'                => home_url( '/over-warmvast/' ),
		'Kennisbank'              => home_url( '/kennisbank/' ),
		'Zakelijk & VvE'          => home_url( '/zakelijk/' ),
		'Ons werk'                => home_url( '/ons-werk/' ),
		'Kwaliteit & garantie'    => home_url( '/kwaliteit-en-garantie/' ),
		'Contact'                 => home_url( '/contact/' ),
	);
	echo '<ul id="primary-menu" class="nav__list">';
	foreach ( $items as $label => $url ) {
		printf( '<li class="menu-item"><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}
