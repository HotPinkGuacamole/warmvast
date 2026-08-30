<?php
/**
 * Lead intake — woningscan lead -> this site -> n8n -> Teamleader.
 *
 * Public REST endpoint: POST /wp-json/warmvast/v1/lead
 *
 * The browser never talks to n8n. It posts the raw form input here; this file
 * validates it, builds the canonical payload and forwards it server-to-server
 * with an HMAC signature. That indirection exists for one reason: the webhook
 * secret must not be reachable from frontend JavaScript, and anything shipped
 * to the browser is reachable.
 *
 * DATA MINIMISATION. The woningscan computes a lot (bouwjaar, energielabel,
 * per-bouwdeel m², ISDE-indicatie, besparing, verdubbeling). Those are
 * estimates meant to help the visitor decide, and estimates are the wrong
 * thing to enshrine as authoritative CRM records -- so none of them leave this
 * endpoint. The payload carries contact details, the chosen measures and the
 * customer's own comment, and nothing else. It is built here field by field
 * from scratch rather than by filtering the request, so a field can only ever
 * reach n8n by being added to warmvast_lead_build_payload() deliberately.
 *
 * Measures arrive as keys (spouw|vloer|glas|dak) and are mapped to labels
 * against warmvast_isde_rates() here. The client therefore cannot inject a
 * service name that the form does not actually offer.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Max lead submissions per IP per window, and the window in seconds.
 *
 * A real visitor submits once. This is high enough to absorb someone
 * correcting a typo'd phone number and resubmitting, low enough that the
 * endpoint is not a free relay into our n8n instance.
 */
const WARMVAST_LEAD_RATE_LIMIT  = 5;
const WARMVAST_LEAD_RATE_WINDOW = 600;

/** How long we wait on n8n before treating the submission as failed. */
const WARMVAST_LEAD_TIMEOUT = 10;

/**
 * Register the lead route.
 */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'warmvast/v1',
			'/lead',
			array(
				'methods'             => 'POST',
				'permission_callback' => 'warmvast_lead_permission',
				'callback'            => 'warmvast_lead_handle',
			)
		);
	}
);

/**
 * Allowed measure key => the label sent to the CRM, in presentation order.
 *
 * Derived from warmvast_isde_rates(), so adding a measure to the tariff table
 * makes it valid here automatically and nowhere else needs editing.
 *
 * @return array<string,string>
 */
function warmvast_lead_measure_labels() {
	$rates  = warmvast_isde_rates();
	$labels = array();
	foreach ( warmvast_measure_order() as $key ) {
		if ( isset( $rates[ $key ]['label'] ) ) {
			$labels[ $key ] = $rates[ $key ]['label'];
		}
	}
	// Any measure in the tariff table that warmvast_measure_order() forgot is
	// still valid -- better a lead with an out-of-order measure than a lead
	// rejected because two lists drifted apart.
	foreach ( $rates as $key => $rate ) {
		if ( ! isset( $labels[ $key ] ) && isset( $rate['label'] ) ) {
			$labels[ $key ] = $rate['label'];
		}
	}
	return $labels;
}

/**
 * Multibyte-safe string length, with a fallback.
 *
 * mbstring is not guaranteed to be loaded (it is absent from the CLI PHP this
 * project's own tooling runs on, for instance), and this is the one code path
 * where a missing function would fatal an endpoint mid-submission and lose a
 * lead. The fallback slightly over-counts multibyte characters, which only
 * makes an already-generous length cap marginally stricter.
 *
 * @param string $str Subject.
 * @return int
 */
function warmvast_lead_strlen( $str ) {
	return function_exists( 'mb_strlen' ) ? mb_strlen( $str ) : strlen( $str );
}

/**
 * Multibyte-safe substring, with a fallback. See warmvast_lead_strlen().
 *
 * @param string $str    Subject.
 * @param int    $start  Start offset.
 * @param int    $length Length.
 * @return string
 */
function warmvast_lead_substr( $str, $start, $length ) {
	return function_exists( 'mb_substr' ) ? mb_substr( $str, $start, $length ) : substr( $str, $start, $length );
}

/**
 * Normalise a Dutch postcode to the compact canonical form: "1824 tm" -> "1824TM".
 *
 * Formatting only; the digits and letters themselves are never altered.
 *
 * @param string $raw Raw postcode input.
 * @return string Normalised postcode, or '' when it is not a Dutch postcode.
 */
function warmvast_lead_normalize_postcode( $raw ) {
	$compact = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', (string) $raw ) );
	return preg_match( '/^[0-9]{4}[A-Z]{2}$/', $compact ) ? $compact : '';
}

/**
 * Normalise a phone number: strip the separators people type, keep the number.
 *
 * A leading "+" is preserved so international notation (+31647551893) survives
 * intact. A national number is deliberately NOT rewritten into +31 form --
 * that would be changing the customer's input, not tidying it.
 *
 * The one digit that IS dropped is a parenthesised trunk zero: "+31 (0)6 …".
 * Those brackets mean "leave this out when dialling internationally", so
 * keeping the 0 would produce +3106…, a number that cannot be dialled. That is
 * reading the notation correctly, not overriding what the customer meant.
 *
 * @param string $raw Raw phone input.
 * @return string Normalised phone, or '' when it holds too few digits to be one.
 */
function warmvast_lead_normalize_phone( $raw ) {
	$raw    = trim( (string) $raw );
	$plus   = ( 0 === strpos( $raw, '+' ) ) ? '+' : '';
	$raw    = preg_replace( '/\(\s*0\s*\)/', '', $raw );
	$digits = preg_replace( '/\D+/', '', $raw );
	if ( strlen( $digits ) < 8 || strlen( $digits ) > 15 ) {
		return '';
	}
	return $plus . $digits;
}

/**
 * Build the canonical n8n payload from raw request input.
 *
 * Kept free of WordPress request/HTTP concerns so it can be exercised directly
 * (see tools/test-lead-payload.php).
 *
 * @param array<string,mixed> $raw Raw, untrusted input.
 * @return array<string,mixed>|WP_Error Canonical payload, or the fields at fault.
 */
function warmvast_lead_build_payload( $raw ) {
	$raw     = is_array( $raw ) ? $raw : array();
	$invalid = array();

	$get = function ( $key ) use ( $raw ) {
		return isset( $raw[ $key ] ) && is_scalar( $raw[ $key ] ) ? (string) $raw[ $key ] : '';
	};

	$name = trim( sanitize_text_field( $get( 'name' ) ) );
	if ( '' === $name || warmvast_lead_strlen( $name ) > 120 ) {
		$invalid[] = 'name';
	}

	// sanitize_email() strips illegal characters; is_email() then judges what
	// is left. Both are needed: sanitising alone would happily pass "a@b".
	$email = sanitize_email( strtolower( trim( $get( 'email' ) ) ) );
	if ( '' === $email || ! is_email( $email ) ) {
		$invalid[] = 'email';
		$email     = '';
	}

	$phone = warmvast_lead_normalize_phone( $get( 'phone' ) );
	if ( '' === $phone ) {
		$invalid[] = 'phone';
	}

	$address = isset( $raw['address'] ) && is_array( $raw['address'] ) ? $raw['address'] : array();
	$street  = trim( sanitize_text_field( isset( $address['street'] ) && is_scalar( $address['street'] ) ? (string) $address['street'] : '' ) );
	if ( '' === $street || warmvast_lead_strlen( $street ) > 200 ) {
		$invalid[] = 'address.street';
	}

	$postal = warmvast_lead_normalize_postcode( isset( $address['postalCode'] ) && is_scalar( $address['postalCode'] ) ? (string) $address['postalCode'] : '' );
	if ( '' === $postal ) {
		$invalid[] = 'address.postalCode';
	}

	$city = trim( sanitize_text_field( isset( $address['city'] ) && is_scalar( $address['city'] ) ? (string) $address['city'] : '' ) );
	if ( '' === $city || warmvast_lead_strlen( $city ) > 120 ) {
		$invalid[] = 'address.city';
	}

	// Measures: keys in, labels out. Anything not in the tariff table is
	// dropped rather than passed through, so the CRM can only ever receive a
	// service Warmvast actually offers.
	$allowed  = warmvast_lead_measure_labels();
	$claimed  = isset( $raw['measures'] ) && is_array( $raw['measures'] ) ? $raw['measures'] : array();
	$selected = array();
	foreach ( $claimed as $key ) {
		if ( is_scalar( $key ) && isset( $allowed[ (string) $key ] ) ) {
			$selected[ (string) $key ] = true;
		}
	}
	$measures = array();
	foreach ( $allowed as $key => $label ) {
		if ( isset( $selected[ $key ] ) ) {
			$measures[] = $label;
		}
	}
	if ( empty( $measures ) ) {
		$invalid[] = 'measures';
	}

	// Optional. An empty comment stays an empty string -- never invented, and
	// never omitted, so n8n sees one consistent shape.
	$comment = trim( sanitize_textarea_field( $get( 'customerComment' ) ) );
	if ( warmvast_lead_strlen( $comment ) > 2000 ) {
		$comment = warmvast_lead_substr( $comment, 0, 2000 );
	}

	// Consent is a precondition for processing the lead, not CRM data: it is
	// validated here and then deliberately left out of the payload.
	$privacy = isset( $raw['privacyAccepted'] ) ? $raw['privacyAccepted'] : null;
	if ( true !== $privacy && 'true' !== $privacy && '1' !== $privacy && 1 !== $privacy && 'ja' !== $privacy ) {
		$invalid[] = 'privacyAccepted';
	}

	if ( ! empty( $invalid ) ) {
		return new WP_Error(
			'warmvast_lead_invalid',
			__( 'Controleer uw gegevens en probeer het opnieuw.', 'warmvast' ),
			array(
				'status' => 400,
				'fields' => $invalid,
			)
		);
	}

	return array(
		'name'            => $name,
		'email'           => $email,
		'phone'           => $phone,
		'address'         => array(
			'street'     => $street,
			'postalCode' => $postal,
			'city'       => $city,
		),
		'measures'        => $measures,
		'customerComment' => $comment,
	);
}

/**
 * Sign the exact body being transmitted.
 *
 * Signing "<timestamp>.<body>" (rather than the body alone) is what lets n8n
 * reject a replayed request: the timestamp is covered by the signature, so it
 * cannot be rewritten without the secret.
 *
 * @param string $timestamp Unix timestamp, as sent in X-Warmvast-Timestamp.
 * @param string $body      Raw JSON body, byte for byte as sent.
 * @param string $secret    Shared secret.
 * @return string Hex HMAC-SHA256.
 */
function warmvast_lead_signature( $timestamp, $body, $secret ) {
	return hash_hmac( 'sha256', $timestamp . '.' . $body, $secret );
}

/**
 * Encode the payload exactly as it will be transmitted.
 *
 * One function so the signed bytes and the sent bytes can never diverge:
 * re-encoding for the signature would risk a different escaping of the same
 * data, and n8n would then reject every legitimate request.
 *
 * @param array<string,mixed> $payload Canonical payload.
 * @return string|false JSON, or false when encoding fails.
 */
function warmvast_lead_encode( $payload ) {
	return wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
}

/**
 * Forward a validated payload to n8n.
 *
 * @param array<string,mixed> $payload    Canonical payload.
 * @param string              $request_id Correlation id, echoed in the logs and headers.
 * @return true|WP_Error
 */
function warmvast_lead_dispatch( $payload, $request_id ) {
	$url    = defined( 'WARMVAST_N8N_LEAD_WEBHOOK_URL' ) ? trim( WARMVAST_N8N_LEAD_WEBHOOK_URL ) : '';
	$secret = defined( 'WARMVAST_N8N_LEAD_WEBHOOK_SECRET' ) ? trim( WARMVAST_N8N_LEAD_WEBHOOK_SECRET ) : '';

	if ( '' === $url || '' === $secret ) {
		warmvast_lead_log( 'not configured', '-', $request_id );
		return new WP_Error(
			'warmvast_lead_not_configured',
			__( 'De aanvraag kan op dit moment niet worden verstuurd. Belt u ons gerust even.', 'warmvast' ),
			array( 'status' => 503 )
		);
	}

	$body = warmvast_lead_encode( $payload );
	if ( ! is_string( $body ) ) {
		warmvast_lead_log( 'encode failed', '-', $request_id );
		return new WP_Error(
			'warmvast_lead_encode_failed',
			__( 'Verzenden lukt nu niet. Probeer het later opnieuw.', 'warmvast' ),
			array( 'status' => 500 )
		);
	}

	$timestamp = (string) time();

	$res = wp_remote_post(
		$url,
		array(
			'timeout'     => WARMVAST_LEAD_TIMEOUT,
			'redirection' => 0,
			'headers'     => array(
				'Content-Type'          => 'application/json; charset=utf-8',
				'Accept'                => 'application/json',
				'X-Warmvast-Timestamp'  => $timestamp,
				'X-Warmvast-Signature'  => warmvast_lead_signature( $timestamp, $body, $secret ),
				'X-Warmvast-Request-Id' => $request_id,
			),
			'body'        => $body,
		)
	);

	if ( is_wp_error( $res ) ) {
		// The WP_Error code (http_request_failed, timeout, ...) is diagnostic
		// and carries no customer data, so it is safe to log.
		warmvast_lead_log( 'transport: ' . $res->get_error_code(), '-', $request_id );
		return new WP_Error(
			'warmvast_lead_unreachable',
			__( 'Verzenden lukt nu niet. Probeer het later opnieuw of bel ons.', 'warmvast' ),
			array( 'status' => 502 )
		);
	}

	$status = (int) wp_remote_retrieve_response_code( $res );
	if ( $status < 200 || $status > 299 ) {
		warmvast_lead_log( 'non-2xx response', $status, $request_id );
		return new WP_Error(
			'warmvast_lead_rejected',
			__( 'Verzenden lukt nu niet. Probeer het later opnieuw of bel ons.', 'warmvast' ),
			array( 'status' => 502 )
		);
	}

	return true;
}

/**
 * Log a lead-webhook failure.
 *
 * Deliberately records only what is needed to debug the integration: never the
 * payload, the customer's details, the secret, the signature or the headers.
 * The correlation id is the join key -- it is sent to n8n as
 * X-Warmvast-Request-Id, so a failure here can be traced there without either
 * side logging personal data.
 *
 * @param string     $reason     Short machine-ish reason.
 * @param string|int $status     HTTP status, or '-' when there was no response.
 * @param string     $request_id Correlation id.
 */
function warmvast_lead_log( $reason, $status, $request_id ) {
	error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- no logging stack on this host; see README.
		sprintf(
			'Warmvast lead webhook failed | reason: %s | HTTP status: %s | request id: %s',
			$reason,
			$status,
			$request_id
		)
	);
}

/**
 * Per-IP rate limit for the lead endpoint. Mirrors the woningscan endpoint's
 * limiter (see warmvast_ws_rate_limit_ok()) but is much stricter, because a
 * legitimate visitor submits a lead once, not twenty times.
 *
 * @return bool True when the request may proceed.
 */
function warmvast_lead_rate_limit_ok() {
	$ip = warmvast_get_client_ip();
	if ( '' === $ip ) {
		return true; // can't identify the caller; don't block a possibly real lead.
	}
	$key   = 'warmvast_lead_rl_' . md5( $ip );
	$count = (int) get_transient( $key );
	if ( $count >= WARMVAST_LEAD_RATE_LIMIT ) {
		return false;
	}
	set_transient( $key, $count + 1, WARMVAST_LEAD_RATE_WINDOW );
	return true;
}

/**
 * Nonce check for the lead route.
 *
 * The route is public (visitors are logged out), so this is CSRF protection
 * rather than authentication: it proves the submission came from a page this
 * site rendered. WordPress's own cookie-nonce check only fires for requests
 * that carry a login cookie, so it does nothing for anonymous visitors and the
 * check has to be made explicitly here.
 *
 * Note for future caching: the nonce is printed into the page, so a full-page
 * cache older than the nonce lifetime would serve a stale one. The failure is
 * visible and recoverable (the visitor is told to refresh) rather than silent,
 * but if a page cache is ever added in front of this site, exclude the scan
 * pages or move the nonce to a small uncached request.
 *
 * @param WP_REST_Request $req Request.
 * @return true|WP_Error
 */
function warmvast_lead_permission( WP_REST_Request $req ) {
	$nonce = $req->get_header( 'x_wp_nonce' );
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error(
			'warmvast_lead_bad_nonce',
			__( 'Uw sessie is verlopen. Ververs de pagina en probeer het opnieuw.', 'warmvast' ),
			array( 'status' => 403 )
		);
	}
	return true;
}

/**
 * REST callback.
 *
 * @param WP_REST_Request $req Request.
 * @return WP_REST_Response|WP_Error
 */
function warmvast_lead_handle( WP_REST_Request $req ) {
	if ( ! warmvast_lead_rate_limit_ok() ) {
		return new WP_Error(
			'warmvast_lead_rate_limited',
			__( 'Te veel aanvragen vanaf dit adres. Probeer het straks opnieuw of bel ons.', 'warmvast' ),
			array( 'status' => 429 )
		);
	}

	$raw = $req->get_json_params();
	if ( ! is_array( $raw ) ) {
		$raw = $req->get_params();
	}

	// Honeypot, carried over from the Formspree form: a hidden field only an
	// automated filler completes. Answer as if it worked, forward nothing.
	$gotcha = isset( $raw['_gotcha'] ) && is_scalar( $raw['_gotcha'] ) ? trim( (string) $raw['_gotcha'] ) : '';
	if ( '' !== $gotcha ) {
		return new WP_REST_Response( array( 'ok' => true ), 200 );
	}

	$payload = warmvast_lead_build_payload( $raw );
	if ( is_wp_error( $payload ) ) {
		return $payload;
	}

	$request_id = function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : uniqid( 'wv', true );

	$sent = warmvast_lead_dispatch( $payload, $request_id );
	if ( is_wp_error( $sent ) ) {
		return $sent;
	}

	return new WP_REST_Response(
		array(
			'ok'        => true,
			'requestId' => $request_id,
		),
		200
	);
}
