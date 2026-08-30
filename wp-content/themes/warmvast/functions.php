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
require_once WARMVAST_DIR . '/inc/lead.php';
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

	// Single source of truth: PHP tariffs + REST urls + savings -> JS. Attached
	// to warmvast-main so it prints before any dependent script.
	//
	// Note what is NOT here: the lead webhook URL and its secret. The browser
	// only ever learns this site's own leadUrl; the n8n endpoint and the signing
	// secret stay server-side (see inc/lead.php).
	wp_localize_script(
		'warmvast-main',
		'WARMVAST_SCAN',
		array(
			'rates'     => warmvast_isde_rates(),
			'phone'     => WARMVAST_PHONE,
			'restUrl'   => esc_url_raw( rest_url( 'warmvast/v1/woningscan' ) ),
			'leadUrl'   => esc_url_raw( rest_url( 'warmvast/v1/lead' ) ),
			'leadNonce' => wp_create_nonce( 'wp_rest' ),
			'savings'   => warmvast_savings_factors(),
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
		// Pages that open on the dark .hero (front page, the scan landing page)
		// float the header transparently over it instead of the usual solid
		// bar -- see .site-header in main.css.
		if ( is_front_page() || is_page_template( 'template-scan.php' ) ) {
			$classes[] = 'has-dark-hero';
		}
		if ( is_page_template( 'template-scan.php' ) ) {
			$classes[] = 'is-scan-landing';
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

/**
 * Slug -> page-template fallback.
 *
 * Which template a page uses normally lives in the database, as each page's
 * `_wp_page_template` meta. That makes it deploy state: a page created after
 * warmvast-db.sql was dumped (or on a host whose MariaDB volume predates the
 * template) silently renders through page.php instead, showing raw editor
 * content where a designed template should be. start.sh only imports the dump
 * on the very FIRST boot, so re-dumping does not repair an existing host
 * either.
 *
 * Mapping the handful of known slugs in code removes that whole class of
 * problem: the right template is used on a fresh import, on an existing
 * database, and on a page someone recreates by hand. An explicit choice made
 * in the editor still wins -- this only fills in when nothing is set.
 *
 * @param string $template Template path resolved by WordPress.
 * @return string
 */
function warmvast_page_template_fallback( $template ) {
	if ( ! is_page() ) {
		return $template;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return $template;
	}
	// Respect a template explicitly chosen in the editor.
	if ( get_page_template_slug( $post ) ) {
		return $template;
	}

	$map = array(
		'isolatie'             => 'template-isolatie.php',
		'subsidie-service'     => 'template-subsidie.php',
		'gratis-isolatiescan'  => 'template-scan.php',
		'kennisbank'           => 'template-kennisbank.php',
		'contact'              => 'template-contact.php',
		'gemeentes'            => 'template-gemeentes.php',
		'zakelijk'             => 'template-zakelijk.php',
		'ons-werk'             => 'template-ons-werk.php',
		'kwaliteit-en-garantie' => 'template-kwaliteit.php',
		'over-warmvast'        => 'template-over-warmvast.php',
	);
	// Derived from the same config the pages themselves are built from, so a
	// new service or gemeente never needs this list edited by hand.
	foreach ( warmvast_isde_rates() as $rate ) {
		$map[ $rate['slug'] ] = 'template-service.php';
	}
	foreach ( array_keys( warmvast_zaanstreek_gemeenten() ) as $gkey ) {
		$map[ 'subsidie-' . $gkey ] = 'template-gemeente.php';
	}

	if ( isset( $map[ $post->post_name ] ) ) {
		$located = locate_template( $map[ $post->post_name ] );
		if ( $located ) {
			return $located;
		}
	}
	return $template;
}
add_filter( 'template_include', 'warmvast_page_template_fallback' );

/**
 * Baseline hardening. No security plugin is deployed here (see .gitignore --
 * only this theme ships to the server), so the handful of things a plugin
 * would normally handle are done directly instead.
 */

// XML-RPC is a standing brute-force/pingback-amplification target and this
// site doesn't use the mobile-app or pingback features it exists for.
add_filter( 'xmlrpc_enabled', '__return_false' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );

// Don't hand an attacker who *does* get an admin session a built-in code
// editor for the theme -- that's the fastest path to a persistent backdoor.
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

// Stop leaking the exact WP version to unauthenticated visitors (feeds,
// scripts/styles query strings, generator meta tag) -- makes it slightly
// harder to target version-specific known vulnerabilities.
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

/**
 * Strip the `ver` query arg ONLY when it is WordPress's own version number.
 *
 * The previous version of this dropped `ver` from every asset URL, which also
 * threw away this theme's filemtime cache-buster (see warmvast_assets()) --
 * main.css and main.js then shipped with no version at all. Apache serves them
 * with only Last-Modified/ETag and no Cache-Control, so browsers fall back to
 * heuristic caching and can hold a stale stylesheet across edits: the symptom
 * is CSS silently not applying (e.g. an <img> rendering at its full intrinsic
 * size because the rule that sizes it is missing from the cached file).
 *
 * Only the WP-version value is a disclosure risk, so only that is removed;
 * asset versions the theme sets itself are left intact.
 *
 * @param string $src Asset URL.
 * @return string
 */
function warmvast_strip_wp_version_query( $src ) {
	$wp_version = get_bloginfo( 'version' );
	if ( $wp_version && false !== strpos( $src, 'ver=' . $wp_version ) ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}
add_filter( 'style_loader_src', 'warmvast_strip_wp_version_query' );
add_filter( 'script_loader_src', 'warmvast_strip_wp_version_query' );

// Throttle wp-login.php / xmlrpc.php brute-force attempts without needing a
// plugin: a lightweight lockout keyed on IP + option table, since MariaDB is
// already the one thing guaranteed to be there.
add_action(
	'wp_login_failed',
	function ( $username ) {
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
		$key = 'warmvast_login_fails_' . md5( $ip );
		$fails = (int) get_transient( $key );
		set_transient( $key, $fails + 1, HOUR_IN_SECONDS );
	}
);
add_filter(
	'authenticate',
	function ( $user ) {
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
		$key = 'warmvast_login_fails_' . md5( $ip );
		if ( (int) get_transient( $key ) >= 10 ) {
			return new WP_Error( 'too_many_attempts', __( 'Te veel mislukte inlogpogingen. Probeer het over een uur opnieuw.' ) );
		}
		return $user;
	},
	30
);
