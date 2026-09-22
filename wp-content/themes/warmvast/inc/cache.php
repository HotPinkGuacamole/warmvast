<?php
/**
 * Full-page cache for anonymous visitors.
 *
 * This site had no page cache at all: every request, including a repeat
 * visitor hitting the same marketing page twice in a row, re-ran the full
 * WordPress query + template render. That is real, avoidable load on the
 * one thing a small site's hosting has the least headroom for.
 *
 * WHY A HAND-ROLLED CACHE, NOT A PLUGIN: this theme deliberately ships no
 * plugins beyond Akismet (see functions.php's own hardening section for the
 * same reasoning) -- a caching plugin would also mean trusting its update
 * cadence and its own attack surface for something this theme can do in
 * ~100 lines with an exact understanding of what this site needs cached.
 *
 * WHAT IS CACHED: the full rendered HTML of a GET request, for a visitor who
 * is not logged in, carries no query string, and is not a REST/AJAX/admin
 * request. Stored as static files under wp-content/cache/warmvast-pages/
 * (already reserved in .gitignore) rather than as options-table transients,
 * so large HTML blobs never bloat wp_options.
 *
 * THE NONCE PROBLEM (see inc/lead.php's own note on this): the woningscan
 * prints a wp_rest nonce into the page via wp_localize_script(), and that
 * nonce appears on nearly every page (the scan is sitewide, not just the
 * landing page), so excluding "pages with the scan" from caching would
 * defeat the point. Instead this cache's TTL (30 minutes) is kept well
 * under the shortest a WordPress nonce is ever valid for (~12 hours, see
 * wp_verify_nonce()'s two-tick window) -- a cached page recycles many times
 * over before any nonce baked into it could actually expire, so a visitor
 * can never be served a stale one from cache.
 *
 * INVALIDATION: rather than track which cached page depends on which post
 * (error-prone, and this site is small enough that it doesn't matter), any
 * content edit just wipes the whole cache. Wrong-for-30-minutes is a bug;
 * a cold cache for the next visitor after an edit is not.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WARMVAST_PAGE_CACHE_DIR', WP_CONTENT_DIR . '/cache/warmvast-pages' );

/**
 * How long a cached page is served before it's rebuilt. Deliberately short
 * relative to typical page-cache TTLs (hours/days) -- see the nonce note in
 * this file's header comment for why. A save_post/etc. edit purges sooner
 * than this anyway, so the TTL mainly protects against a cache entry from a
 * deploy or a manual DB edit (which fires no WordPress hook) outliving its
 * usefulness.
 */
const WARMVAST_PAGE_CACHE_TTL = 30 * MINUTE_IN_SECONDS;

/**
 * Resolve the cache file path for the current request. Keyed on host + path
 * only (see warmvast_page_cache_eligible() -- a query string always makes a
 * request ineligible, so it never needs to be part of the key).
 *
 * @return string
 */
function warmvast_page_cache_file() {
	$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
	$path = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	// Strip a query string defensively even though warmvast_page_cache_eligible()
	// already excludes requests that carry one -- this function should never
	// itself be the reason two different query strings collide on one file.
	$path = strtok( $path, '?' );
	return trailingslashit( WARMVAST_PAGE_CACHE_DIR ) . md5( $host . $path ) . '.html';
}

/**
 * Whether the current request may be served from, and saved to, the page
 * cache. Anything that varies per visitor (logged-in state, a query string,
 * a non-GET method) or isn't a normal front-end page view (admin, REST,
 * AJAX, cron, WP-CLI) is excluded.
 *
 * @return bool
 */
function warmvast_page_cache_eligible() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return false;
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return false;
	}
	if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'GET' !== $_SERVER['REQUEST_METHOD'] ) {
		return false;
	}
	if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
		return false;
	}
	if ( is_user_logged_in() ) {
		return false;
	}
	// A visitor mid comment-preview/moderation flow carries WP's own comment
	// cookies; leave those requests uncached rather than risk showing one
	// visitor's unmoderated comment to the next.
	foreach ( array_keys( $_COOKIE ) as $cookie_name ) {
		if ( 0 === strpos( $cookie_name, 'comment_author_' ) ) {
			return false;
		}
	}
	return true;
}

/**
 * Serve a cached page and exit, if one exists and is still fresh.
 * Hooked early on template_redirect so a hit skips template loading and the
 * page queries entirely -- the whole point of a page cache.
 */
function warmvast_page_cache_maybe_serve() {
	if ( ! warmvast_page_cache_eligible() ) {
		return;
	}
	$file = warmvast_page_cache_file();
	if ( ! is_file( $file ) ) {
		return;
	}
	$age = time() - filemtime( $file );
	if ( $age < 0 || $age > WARMVAST_PAGE_CACHE_TTL ) {
		return; // stale; fall through to a normal render (which will overwrite it).
	}
	$html = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local cache file, not a remote request.
	if ( false === $html || '' === $html ) {
		return;
	}
	header( 'X-Warmvast-Cache: HIT' );
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- our own previously-rendered, already-escaped page output.
	exit;
}
add_action( 'template_redirect', 'warmvast_page_cache_maybe_serve', 0 );

/**
 * Start capturing output for an eligible request that missed the cache, so
 * it can be written to disk once WordPress finishes rendering it.
 */
function warmvast_page_cache_start_capture() {
	if ( ! warmvast_page_cache_eligible() ) {
		return;
	}
	ob_start();
	header( 'X-Warmvast-Cache: MISS' );
	add_action( 'shutdown', 'warmvast_page_cache_save', 0 );
}
// Priority 1: after warmvast_page_cache_maybe_serve()'s exit-on-hit at
// priority 0, before anything else on template_redirect starts rendering.
add_action( 'template_redirect', 'warmvast_page_cache_start_capture', 1 );

/**
 * Flush the just-rendered page to the output buffer's own destination AND
 * to the cache file, on 'shutdown' -- by then the full page, including
 * anything hooked as late as wp_footer, has been generated.
 *
 * Only a real 200 response is cached: an error page cached under a URL that
 * later resolves successfully (or vice versa) is exactly the kind of stale
 * response a page cache must never produce.
 */
function warmvast_page_cache_save() {
	if ( ob_get_level() < 1 ) {
		return;
	}
	$html = ob_get_clean();
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the page's own rendered output, passing straight through.

	if ( 200 !== http_response_code() || '' === trim( (string) $html ) ) {
		return;
	}
	if ( ! wp_mkdir_p( WARMVAST_PAGE_CACHE_DIR ) ) {
		return;
	}
	// Protect the cache directory the same way wp-content/uploads is: no
	// directory listing, no PHP execution if a request ever reached it.
	$htaccess = trailingslashit( WARMVAST_PAGE_CACHE_DIR ) . '.htaccess';
	if ( ! is_file( $htaccess ) ) {
		file_put_contents( $htaccess, "Options -Indexes\n<FilesMatch \"\\.php$\">\nRequire all denied\n</FilesMatch>\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- local cache dir, not user-supplied content.
	}
	file_put_contents( warmvast_page_cache_file(), $html, LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
}

/**
 * Wipe the entire page cache. Deliberately total rather than selective --
 * see this file's header comment for why that's the right trade for a site
 * this size.
 */
function warmvast_page_cache_purge_all() {
	if ( ! is_dir( WARMVAST_PAGE_CACHE_DIR ) ) {
		return;
	}
	foreach ( glob( trailingslashit( WARMVAST_PAGE_CACHE_DIR ) . '*.html' ) as $file ) {
		wp_delete_file( $file );
	}
}
// Any of these means something a cached page could show has changed.
add_action( 'save_post', 'warmvast_page_cache_purge_all' );
add_action( 'deleted_post', 'warmvast_page_cache_purge_all' );
add_action( 'trashed_post', 'warmvast_page_cache_purge_all' );
add_action( 'wp_update_nav_menu', 'warmvast_page_cache_purge_all' );
add_action( 'switch_theme', 'warmvast_page_cache_purge_all' );
add_action( 'customize_save_after', 'warmvast_page_cache_purge_all' );

/**
 * Auto-purge on deploy. Every purge trigger above is a WordPress hook, and a
 * `git pull` that changes theme PHP directly on disk (see the deploy note at
 * the top of .gitignore -- the hosting panel does exactly this on every
 * restart) fires none of them: a page edited in this deploy keeps serving
 * its pre-deploy cached HTML for up to WARMVAST_PAGE_CACHE_TTL after the new
 * code is live.
 *
 * Detected via .git/FETCH_HEAD's mtime, which a `git pull` always rewrites
 * regardless of whether the branch fast-forwarded or refs ended up packed
 * (unlike refs/heads/<branch>, which git may omit from disk after a gc).
 * One stat() call, so it runs on every request -- including cache HITS --
 * without reintroducing the per-request cost a page cache exists to avoid;
 * the compare-and-maybe-purge itself only does real work the first request
 * after a deploy.
 */
function warmvast_page_cache_deploy_fingerprint() {
	$git_marker = ABSPATH . '.git/FETCH_HEAD';
	if ( is_file( $git_marker ) ) {
		return (string) filemtime( $git_marker );
	}
	// No .git present (e.g. a non-git deploy) -- fall back to this file's
	// own mtime so the cache still self-heals after a manual file edit.
	return (string) filemtime( __FILE__ );
}

function warmvast_page_cache_maybe_purge_on_deploy() {
	$marker  = trailingslashit( WARMVAST_PAGE_CACHE_DIR ) . '.deploy-fingerprint';
	$current = warmvast_page_cache_deploy_fingerprint();
	$known   = is_file( $marker ) ? file_get_contents( $marker ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local cache file.
	if ( $known === $current ) {
		return;
	}
	warmvast_page_cache_purge_all();
	if ( wp_mkdir_p( WARMVAST_PAGE_CACHE_DIR ) ) {
		file_put_contents( $marker, $current, LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	}
}
// Priority -1: before warmvast_page_cache_maybe_serve()'s priority-0 HIT
// check, so a deploy purges its own stale cache before anything is served
// from it, on the very first request after that deploy.
add_action( 'template_redirect', 'warmvast_page_cache_maybe_purge_on_deploy', -1 );
