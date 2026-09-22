<?php
/**
 * Live Google reviews -- optional. When WARMVAST_GOOGLE_PLACES_API_KEY and a
 * Place ID (set at Warmvast -> Reviews in wp-admin) are both configured,
 * warmvast_reviews() (inc/config.php) pulls the rating, review count and up
 * to 5 review snippets straight from Google's Places API instead of the
 * hand-maintained wp-admin list -- and `verified` is set automatically,
 * since data read live from Google needs no separate human "yes this is
 * real" flag the way hand-typed content does.
 *
 * WHY THIS IS OPTIONAL, NOT THE ONLY PATH: Google's API returns at most 5
 * reviews, picked by Google's own relevance ranking -- you cannot choose
 * which 5, or feature the one that happens to mention a doubled subsidy.
 * The wp-admin Reviews page (see inc/admin.php) still exists as the
 * fallback for when this isn't configured, or if it ever needs to be
 * disabled again, and as the source of truth for reviews from anywhere
 * other than Google.
 *
 * SETUP (not something this code can do for you):
 *  1. Google Cloud Console -> enable "Places API (New)" on a project with
 *     billing enabled, create an API key, and RESTRICT it (API restriction:
 *     Places API only; if the key is only ever called from this server,
 *     also restrict it by server IP) -- this key is only ever used
 *     server-to-server (see warmvast_google_reviews_fetch() below), the
 *     browser never sees it, same reasoning as WARMVAST_EP_ONLINE_API_KEY.
 *  2. Define WARMVAST_GOOGLE_PLACES_API_KEY (wp-config.php locally, or the
 *     production dotenv file -- see inc/config.php's warmvast_config_value()).
 *  3. Find the Warmvast Isolatie Place ID (Google's "Place ID Finder" tool,
 *     or it's embedded in the Google Business Profile / Maps share link)
 *     and paste it into Warmvast -> Reviews in wp-admin.
 *  4. Reviews API calls are a billed Google Maps Platform SKU. Caching to
 *     once per day (see below) keeps this to ~30 calls/month regardless of
 *     site traffic -- comfortably inside Google's free monthly allowance as
 *     of this writing, but verify current pricing on Google's own page
 *     before relying on that not changing.
 *  5. Google's Places API terms require reviews to be shown as Google
 *     supplied them (author name, star rating, text) without alteration --
 *     this integration already does that; if review COPY is ever edited by
 *     hand, do it on the wp-admin fallback list, never by rewriting what
 *     came back from Google.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const WARMVAST_GOOGLE_REVIEWS_ENDPOINT = 'https://places.googleapis.com/v1/places/';

/**
 * Fetch rating + review count + up to 5 review snippets from Google Places
 * API (New), cached. Returns null when not configured or unavailable, in
 * which case warmvast_reviews() falls back to the wp-admin-maintained list.
 *
 * @return array{rating:float,count:int,items:array<int,array<string,mixed>>}|null
 */
function warmvast_google_reviews_fetch() {
	$key      = defined( 'WARMVAST_GOOGLE_PLACES_API_KEY' ) ? trim( WARMVAST_GOOGLE_PLACES_API_KEY ) : '';
	$place_id = trim( (string) get_option( 'warmvast_opt_google_place_id', '' ) );
	if ( '' === $key || '' === $place_id ) {
		return null;
	}

	$cache_key = 'warmvast_google_reviews_' . md5( $place_id );
	$cached    = get_transient( $cache_key );
	if ( false !== $cached ) {
		return is_array( $cached ) ? $cached : null; // cached failure marker (see below) decodes to null.
	}

	$res = wp_remote_get(
		// languageCode=nl matters: without it Google auto-translates review
		// text into whatever locale it guesses (observed: English, for
		// reviewers who wrote in Dutch) -- which is both wrong for a Dutch
		// site and, arguably, no longer showing the review as the reviewer
		// actually wrote it. Explicitly asking for nl returns their
		// original Dutch text.
		WARMVAST_GOOGLE_REVIEWS_ENDPOINT . rawurlencode( $place_id ) . '?languageCode=nl',
		array(
			'timeout' => 8,
			'headers' => array(
				'X-Goog-Api-Key'   => $key,
				// Field mask is required by Places API (New) -- requesting
				// exactly what's used keeps both the response small and the
				// call inside the cheapest priced field set.
				'X-Goog-FieldMask' => 'rating,userRatingCount,reviews.rating,reviews.text,reviews.authorAttribution,reviews.publishTime',
				'Accept'           => 'application/json',
			),
		)
	);

	if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
		// Cache the failure briefly so a misconfigured key or a Google outage
		// doesn't turn into a request-per-pageview retry loop, but recovers
		// on its own well within a day -- same pattern as the woningscan's
		// EP-Online lookups (see warmvast_ws_registered_energylabel()).
		set_transient( $cache_key, 'failure', HOUR_IN_SECONDS );
		return null;
	}

	$data = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( ! is_array( $data ) || ! isset( $data['rating'], $data['userRatingCount'] ) ) {
		set_transient( $cache_key, 'failure', HOUR_IN_SECONDS );
		return null;
	}

	$items = array();
	foreach ( (array) ( $data['reviews'] ?? array() ) as $r ) {
		$text = isset( $r['text']['text'] ) ? (string) $r['text']['text'] : '';
		$name = isset( $r['authorAttribution']['displayName'] ) ? (string) $r['authorAttribution']['displayName'] : '';
		if ( '' === $text || '' === $name ) {
			continue;
		}
		$items[] = array(
			'name'  => sanitize_text_field( $name ),
			// Google's API does not expose the reviewer's location -- unlike
			// the wp-admin-entered list, a live Google review has no 'place'.
			// template-parts/reviews.php only prints that line when it's non-empty.
			'place' => '',
			'stars' => isset( $r['rating'] ) ? max( 1, min( 5, (int) $r['rating'] ) ) : 5,
			'text'  => sanitize_textarea_field( $text ),
		);
	}

	$result = array(
		'rating' => round( (float) $data['rating'], 1 ),
		'count'  => (int) $data['userRatingCount'],
		'items'  => $items,
		// Where a card links out to. The Places API has no per-review deep
		// link (only a link to the REVIEWER's own contributions page, which
		// isn't about Warmvast), so every card points at the same place --
		// Google's own documented Maps Search URL scheme
		// (developers.google.com/maps/documentation/urls/get-started),
		// guaranteed to resolve regardless of how Warmvast's listing name
		// ever changes, because query_place_id -- not the query text --
		// is what actually identifies the place.
		'url'    => add_query_arg(
			array(
				'api'            => '1',
				'query'          => rawurlencode( 'Warmvast Isolatie' ),
				'query_place_id' => rawurlencode( $place_id ),
			),
			'https://www.google.com/maps/search/'
		),
	);

	set_transient( $cache_key, $result, DAY_IN_SECONDS );
	return $result;
}
