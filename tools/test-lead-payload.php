<?php
/**
 * Focused tests for the woningscan lead -> n8n payload.
 *
 *     "/c/MAMP/bin/php/php8.2.14/php.exe" tools/test-lead-payload.php
 *     php tools/test-lead-payload.php
 *
 * This repository has no PHPUnit/WP test-suite setup, and standing one up to
 * cover one endpoint would be a bigger change than the endpoint itself. So
 * this is a dependency-free runner over the parts worth pinning down: what
 * ends up in the payload, what can never end up in it, and that the HMAC is
 * computed over exactly the bytes that get transmitted.
 *
 * The WordPress functions below are stubs, so this does NOT prove that real
 * WP sanitisation behaves identically -- the stubs are deliberately no more
 * permissive than core. What it does prove is stub-independent: field
 * presence/absence, the measure whitelist, rejection rules, and signing.
 *
 * @package Warmvast
 */

// ---------------------------------------------------------------------------
// Minimal WordPress stubs.
// ---------------------------------------------------------------------------

define( 'ABSPATH', __DIR__ . '/' );

$GLOBALS['wv_test_http'] = null;   // canned wp_remote_post result.
$GLOBALS['wv_test_sent'] = null;   // what wp_remote_post was called with.
$GLOBALS['wv_test_logs'] = array();
$GLOBALS['wv_test_transients'] = array();

class WP_Error {
	private $code;
	private $message;
	private $data;
	public function __construct( $code = '', $message = '', $data = array() ) {
		$this->code    = $code;
		$this->message = $message;
		$this->data    = $data;
	}
	public function get_error_code() {
		return $this->code; }
	public function get_error_message() {
		return $this->message; }
	public function get_error_data() {
		return $this->data; }
}

function is_wp_error( $thing ) {
	return $thing instanceof WP_Error;
}

function add_action() {}
function register_rest_route() {}
function __( $text ) {
	return $text; }

function wp_unslash( $value ) {
	return is_array( $value ) ? array_map( 'wp_unslash', $value ) : stripslashes( (string) $value );
}

function sanitize_text_field( $str ) {
	$str = strip_tags( (string) $str );
	$str = preg_replace( '/[\r\n\t]+/', ' ', $str );
	$str = preg_replace( '/ +/', ' ', $str );
	return trim( $str );
}

function sanitize_textarea_field( $str ) {
	return trim( strip_tags( (string) $str ) );
}

function sanitize_email( $email ) {
	return trim( preg_replace( '/[^a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~@\-]/', '', (string) $email ) );
}

function is_email( $email ) {
	return (bool) preg_match( '/^[^@\s]+@[^@\s.]+\.[^@\s]+$/', (string) $email );
}

function wp_json_encode( $data, $options = 0 ) {
	return json_encode( $data, $options );
}

function wp_remote_post( $url, $args ) {
	$GLOBALS['wv_test_sent'] = array( 'url' => $url, 'args' => $args );
	return $GLOBALS['wv_test_http'];
}

function wp_remote_retrieve_response_code( $res ) {
	return isset( $res['response']['code'] ) ? $res['response']['code'] : 0;
}

function get_transient( $key ) {
	return isset( $GLOBALS['wv_test_transients'][ $key ] ) ? $GLOBALS['wv_test_transients'][ $key ]['value'] : false;
}

function set_transient( $key, $value, $expiration = 0 ) {
	$GLOBALS['wv_test_transients'][ $key ] = array(
		'value'      => $value,
		'expiration' => $expiration,
	);
	return true;
}

// error_log() is a PHP builtin and cannot be stubbed, so redirect it to a
// scratch file and read that back instead.
$GLOBALS['wv_test_logfile'] = tempnam( sys_get_temp_dir(), 'wvlead' );
ini_set( 'error_log', $GLOBALS['wv_test_logfile'] );

function wv_test_read_log() {
	return file_exists( $GLOBALS['wv_test_logfile'] ) ? file_get_contents( $GLOBALS['wv_test_logfile'] ) : '';
}

function wv_test_clear_log() {
	file_put_contents( $GLOBALS['wv_test_logfile'], '' );
}

// ---------------------------------------------------------------------------
// Subject under test.
// ---------------------------------------------------------------------------

// Defined BEFORE the theme loads, exactly as wp-config.php does in production
// -- inc/config.php only fills in empty defaults for constants nobody set.
define( 'WARMVAST_N8N_LEAD_WEBHOOK_URL', 'https://n8n.example/webhook/test' );
define( 'WARMVAST_N8N_LEAD_WEBHOOK_SECRET', 'test-secret' );
define( 'WARMVAST_TRUSTED_PROXY_IPS', '10.0.0.5,10.0.0.6,2001:db8::5,192.0.2.0/24,2001:db8:abcd::/48' );

$theme = __DIR__ . '/../wp-content/themes/warmvast';
require_once $theme . '/inc/config.php';
require_once $theme . '/inc/lead.php';

// ---------------------------------------------------------------------------
// Tiny assertion harness.
// ---------------------------------------------------------------------------

$tests  = 0;
$failed = array();

function ok( $condition, $label ) {
	global $tests, $failed;
	$tests++;
	if ( $condition ) {
		echo "  PASS  $label\n";
	} else {
		$failed[] = $label;
		echo "  FAIL  $label\n";
	}
}

function same( $expected, $actual, $label ) {
	$pass = ( $expected === $actual );
	ok( $pass, $label );
	if ( ! $pass ) {
		echo "        expected: " . json_encode( $expected ) . "\n";
		echo "        actual:   " . json_encode( $actual ) . "\n";
	}
}

function section( $name ) {
	echo "\n$name\n";
}

/** A complete, realistic submission -- including every estimate a malicious or careless client might attach. */
function wv_submission( $overrides = array() ) {
	$base = array(
		'name'            => ' Alexander De Jong ',
		'email'           => '  AlexOTVNL@Gmail.com ',
		'phone'           => '+31 6 47551893',
		'address'         => array(
			'street'     => 'Sluiswaard 150',
			'postalCode' => '1824 tm',
			'city'       => 'Alkmaar',
		),
		'measures'        => array( 'dak', 'glas' ),
		'customerComment' => 'test123',
		'privacyAccepted' => true,
	);
	return array_merge( $base, $overrides );
}

function wv_server( $remote_addr, $xff = null ) {
	$server = array( 'REMOTE_ADDR' => $remote_addr );
	if ( null !== $xff ) {
		$server['HTTP_X_FORWARDED_FOR'] = $xff;
	}
	return $server;
}

function wv_reset_rate_limit_state() {
	$GLOBALS['wv_test_transients'] = array();
	unset( $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_X_FORWARDED_FOR'] );
}

// ---------------------------------------------------------------------------

section( '1. Canonical payload' );

$payload = warmvast_lead_build_payload( wv_submission() );
ok( ! is_wp_error( $payload ), 'a complete submission is accepted' );

same(
	array(
		'name'            => 'Alexander De Jong',
		'email'           => 'alexotvnl@gmail.com',
		'phone'           => '+31647551893',
		'address'         => array(
			'street'     => 'Sluiswaard 150',
			'postalCode' => '1824TM',
			'city'       => 'Alkmaar',
		),
		'measures'        => array( 'Dakisolatie', 'HR++ glas' ),
		'customerComment' => 'test123',
	),
	$payload,
	'payload matches the canonical shape exactly'
);

same(
	array( 'name', 'email', 'phone', 'address', 'measures', 'customerComment' ),
	array_keys( $payload ),
	'payload has exactly six top-level keys, in canonical order'
);

section( '2. Data minimisation -- estimates must never leave the site' );

// A client that tries to smuggle the whole scan result through anyway.
$greedy = warmvast_lead_build_payload(
	wv_submission(
		array(
			'bouwjaar'      => 1974,
			'energielabel'  => 'F',
			'gebruiksdoel'  => 'woonfunctie',
			'surfaces'      => array( 'vloer' => 66, 'dak' => 76, 'spouw' => 145, 'glas' => 45 ),
			'vloer'         => 66,
			'dak'           => 76,
			'spouw'         => 145,
			'glas'          => 45,
			'subsidie'      => 4720,
			'besparing'     => 574,
			'doubled'       => true,
			'samenvatting'  => 'Nieuwe woningscan lead ... ISDE-indicatie: 4.720',
		)
	)
);
$flat = json_encode( $greedy );

foreach ( array( 'bouwjaar', 'energielabel', 'gebruiksdoel', 'surfaces', 'subsidie', 'besparing', 'doubled', 'samenvatting', 'vloer', 'spouw' ) as $forbidden ) {
	ok( ! array_key_exists( $forbidden, $greedy ), "'$forbidden' is absent from the payload" );
}
ok( false === strpos( $flat, '1974' ), 'construction year does not appear anywhere in the payload' );
ok( false === strpos( $flat, '4720' ), 'subsidy estimate does not appear anywhere in the payload' );
ok( false === strpos( $flat, '574' ), 'savings estimate does not appear anywhere in the payload' );
ok( false === strpos( $flat, '"F"' ), 'energy label does not appear anywhere in the payload' );
ok( false === strpos( $flat, 'Samenvatting' ) && false === strpos( $flat, 'samenvatting' ), 'the old Formspree summary blob is gone' );
ok( ! array_key_exists( 'privacyAccepted', $greedy ), 'consent is validated but not sent as CRM data' );
same( 6, count( $greedy ), 'no extra keys survive from the request' );

section( '3. Measures' );

$json = warmvast_lead_encode( $payload );
ok( false !== strpos( $json, '"measures":["Dakisolatie","HR++ glas"]' ), 'measures serialise as a JSON array, not a blob' );

$injected = warmvast_lead_build_payload(
	wv_submission( array( 'measures' => array( 'dak', 'zonnepanelen', 'Gratis dakkapel', '../../etc', 42 ) ) )
);
same( array( 'Dakisolatie' ), $injected['measures'], 'unknown service names are dropped, not passed through' );

$ordered = warmvast_lead_build_payload( wv_submission( array( 'measures' => array( 'glas', 'vloer', 'dak', 'spouw' ) ) ) );
same(
	array( 'Dakisolatie', 'Spouwmuurisolatie', 'Vloerisolatie', 'HR++ glas' ),
	$ordered['measures'],
	'measures come out in the order the form presents them, whatever order they arrive in'
);

$duped = warmvast_lead_build_payload( wv_submission( array( 'measures' => array( 'dak', 'dak', 'dak' ) ) ) );
same( array( 'Dakisolatie' ), $duped['measures'], 'duplicate measures are collapsed' );

ok( is_wp_error( warmvast_lead_build_payload( wv_submission( array( 'measures' => array() ) ) ) ), 'a lead with no valid measure is rejected' );
ok( is_wp_error( warmvast_lead_build_payload( wv_submission( array( 'measures' => 'dak' ) ) ) ), 'a non-array measures value is rejected' );

section( '4. Customer comment' );

$comment = 'Graag na 17:00 bellen. Vraag: telt de "zolder" ook mee? Kosten < 5000 euro?';
$kept    = warmvast_lead_build_payload( wv_submission( array( 'customerComment' => $comment ) ) );
same( $comment, $kept['customerComment'], 'the comment survives sanitisation unchanged' );

$empty = warmvast_lead_build_payload( wv_submission( array( 'customerComment' => '' ) ) );
same( '', $empty['customerComment'], 'an empty comment stays an empty string' );

$missing = wv_submission();
unset( $missing['customerComment'] );
$absent = warmvast_lead_build_payload( $missing );
same( '', $absent['customerComment'], 'an omitted comment becomes an empty string, never invented' );

$long = warmvast_lead_build_payload( wv_submission( array( 'customerComment' => str_repeat( 'a', 2500 ) ) ) );
same( 2000, strlen( $long['customerComment'] ), 'an oversized comment is capped rather than rejected' );

section( '5. Rejection of malformed submissions' );

foreach ( array( 'not-an-email', 'a@b', '', 'foo@', '@bar.nl' ) as $bad ) {
	$res = warmvast_lead_build_payload( wv_submission( array( 'email' => $bad ) ) );
	ok( is_wp_error( $res ), "invalid e-mail is rejected: '" . $bad . "'" );
}

foreach ( array( 'name', 'phone' ) as $field ) {
	$res = warmvast_lead_build_payload( wv_submission( array( $field => '' ) ) );
	ok( is_wp_error( $res ), "missing required '$field' is rejected" );
}

foreach ( array( 'street', 'postalCode', 'city' ) as $field ) {
	$addr = wv_submission();
	$addr['address'][ $field ] = '';
	ok( is_wp_error( warmvast_lead_build_payload( $addr ) ), "missing required address.$field is rejected" );
}

$noaddr = wv_submission();
unset( $noaddr['address'] );
ok( is_wp_error( warmvast_lead_build_payload( $noaddr ) ), 'a submission with no address block at all is rejected' );

ok( is_wp_error( warmvast_lead_build_payload( wv_submission( array( 'privacyAccepted' => false ) ) ) ), 'privacy consent is required' );
$noconsent = wv_submission();
unset( $noconsent['privacyAccepted'] );
ok( is_wp_error( warmvast_lead_build_payload( $noconsent ) ), 'missing privacy consent is rejected' );

$err = warmvast_lead_build_payload( array() );
ok( is_wp_error( $err ), 'an entirely empty submission is rejected' );
$data = $err->get_error_data();
same( 400, $data['status'], 'validation failure reports HTTP 400' );

section( '6. Normalisation' );

foreach ( array( '1824TM', '1824 tm', ' 1824-TM ', '1824tm' ) as $variant ) {
	same( '1824TM', warmvast_lead_normalize_postcode( $variant ), "postcode '$variant' normalises to 1824TM" );
}
same( '', warmvast_lead_normalize_postcode( 'ABCDEF' ), 'a non-Dutch postcode is rejected, not mangled' );

same( '+31647551893', warmvast_lead_normalize_phone( '+31 (0)6 47551893' ), 'international notation is preserved' );
same( '0647551893', warmvast_lead_normalize_phone( '06-47551893' ), 'a national number is tidied but not rewritten to +31' );
same( '', warmvast_lead_normalize_phone( '123' ), 'a too-short phone number is rejected' );

section( '7. Webhook signature' );

$body = warmvast_lead_encode( $payload );
$ts   = '1730000000';
$sig  = warmvast_lead_signature( $ts, $body, 'test-secret' );

same( hash_hmac( 'sha256', $ts . '.' . $body, 'test-secret' ), $sig, 'signature is HMAC-SHA256 over "<timestamp>.<body>"' );
ok( $sig !== warmvast_lead_signature( '1730000001', $body, 'test-secret' ), 'changing the timestamp changes the signature (replay protection)' );
ok( $sig !== warmvast_lead_signature( $ts, $body . ' ', 'test-secret' ), 'changing one byte of the body changes the signature' );
ok( $sig !== warmvast_lead_signature( $ts, $body, 'other-secret' ), 'a different secret produces a different signature' );

// The signature must cover the exact transmitted bytes: re-encoding the
// payload separately for signing could differ from what is sent.
$GLOBALS['wv_test_http'] = array( 'response' => array( 'code' => 200 ) );
$result = warmvast_lead_dispatch( $payload, 'req-123' );
ok( true === $result, 'a 2xx from n8n is a successful dispatch' );

$sent      = $GLOBALS['wv_test_sent'];
$sent_body = $sent['args']['body'];
$sent_ts   = $sent['args']['headers']['X-Warmvast-Timestamp'];
$sent_sig  = $sent['args']['headers']['X-Warmvast-Signature'];

same(
	hash_hmac( 'sha256', $sent_ts . '.' . $sent_body, 'test-secret' ),
	$sent_sig,
	'the transmitted signature verifies against the exact transmitted body'
);
same( $payload, json_decode( $sent_body, true ), 'the transmitted body decodes back to the canonical payload' );
ok( 'application/json; charset=utf-8' === $sent['args']['headers']['Content-Type'], 'body is sent as JSON' );
ok( ! empty( $sent['args']['headers']['X-Warmvast-Request-Id'] ), 'a correlation id is sent for tracing' );
ok( false === strpos( $sent_body, 'test-secret' ), 'the secret is never part of the body' );

section( '8. Failure is never reported as success' );

wv_test_clear_log();
$GLOBALS['wv_test_http'] = array( 'response' => array( 'code' => 500 ) );
$res = warmvast_lead_dispatch( $payload, 'req-500' );
ok( is_wp_error( $res ), 'a 500 from n8n is a failed dispatch' );
same( 502, $res->get_error_data()['status'], 'a failed dispatch surfaces as 502 to the browser' );

$GLOBALS['wv_test_http'] = array( 'response' => array( 'code' => 302 ) );
ok( is_wp_error( warmvast_lead_dispatch( $payload, 'req-302' ) ), 'a redirect is not treated as success' );

$GLOBALS['wv_test_http'] = new WP_Error( 'http_request_failed', 'timed out' );
ok( is_wp_error( warmvast_lead_dispatch( $payload, 'req-timeout' ) ), 'a transport error/timeout is a failed dispatch' );

$log = wv_test_read_log();
ok( '' !== $log, 'failures are logged' );
ok( false !== strpos( $log, 'HTTP status: 500' ), 'the log records the HTTP status' );
ok( false !== strpos( $log, 'req-500' ), 'the log records the correlation id' );
foreach ( array( 'Alexander', 'alexotvnl', '47551893', 'Sluiswaard', 'test123', 'test-secret', $sent_sig ) as $secret_ish ) {
	ok( false === strpos( $log, $secret_ish ), "logs do not leak '" . substr( $secret_ish, 0, 12 ) . "'" );
}

section( '9. Secrets never reach the frontend' );

$frontend = array(
	$theme . '/assets/js/woningscan.js',
	$theme . '/assets/js/main.js',
	$theme . '/template-parts/woningscan.php',
	$theme . '/functions.php',
);
foreach ( $frontend as $file ) {
	$src  = file_get_contents( $file );
	$name = basename( $file );
	ok( false === strpos( $src, 'WARMVAST_N8N_LEAD_WEBHOOK_SECRET' ), "$name does not reference the webhook secret" );
	ok( false === strpos( $src, 'WARMVAST_N8N_LEAD_WEBHOOK_URL' ), "$name does not reference the webhook URL" );
}

$js = file_get_contents( $theme . '/assets/js/woningscan.js' );
ok( false === stripos( $js, 'formspree' ), 'no Formspree reference remains in the scan JS' );
ok( false === strpos( $js, '_replyto' ), 'the Formspree _replyto field is gone' );
ok( false === strpos( $js, '01. Samenvatting' ), 'the numbered Formspree summary fields are gone' );
ok( false !== strpos( $js, '_gotcha' ), 'the honeypot check is still in place' );
ok( false !== strpos( $js, 'X-WP-Nonce' ), 'the lead request sends a nonce' );

$tpl = file_get_contents( $theme . '/template-parts/woningscan.php' );
ok( false === stripos( $tpl, 'formspree' ), 'no Formspree reference remains in the scan template' );
ok( false === strpos( $tpl, 'data-endpoint' ), 'no submission endpoint is printed into the markup' );
ok( false !== strpos( $tpl, '_gotcha' ), 'the honeypot field is still rendered' );
ok( false !== strpos( $tpl, 'privacy_akkoord' ), 'the privacy consent checkbox is still rendered' );

section( '10. Trusted-proxy client IP detection and rate limit buckets' );

same(
	'10.0.0.5',
	warmvast_get_client_ip( wv_server( '10.0.0.5', '198.51.100.10' ), '' ),
	'no trusted proxies configured uses REMOTE_ADDR'
);
same(
	'203.0.113.9',
	warmvast_get_client_ip( wv_server( '203.0.113.9', '198.51.100.10' ), '10.0.0.5' ),
	'direct internet request spoofing X-Forwarded-For is ignored'
);
same(
	'198.51.100.10',
	warmvast_get_client_ip( wv_server( '10.0.0.5', '198.51.100.10' ), '10.0.0.5' ),
	'trusted immediate proxy extracts the real client IP'
);
same(
	'198.51.100.20',
	warmvast_get_client_ip( wv_server( '10.0.0.6', '198.51.100.20, 10.0.0.5' ), '10.0.0.5,10.0.0.6' ),
	'multiple trusted proxy hops are walked from the right'
);
same(
	'10.0.0.5',
	warmvast_get_client_ip( wv_server( '10.0.0.5', '198.51.100.20, not-an-ip' ), '10.0.0.5' ),
	'malformed X-Forwarded-For values fall back to REMOTE_ADDR'
);
same(
	'10.0.0.5',
	warmvast_get_client_ip( wv_server( '10.0.0.5', '   ' ), '10.0.0.5' ),
	'empty X-Forwarded-For falls back to REMOTE_ADDR'
);
same(
	'198.51.100.30',
	warmvast_get_client_ip( wv_server( '192.0.2.25', '198.51.100.30' ), '192.0.2.0/24' ),
	'IPv4 CIDR trusted proxy is supported'
);
same(
	'2001:db8:ffff::10',
	warmvast_get_client_ip( wv_server( '2001:db8::5', '2001:db8:ffff::10' ), '2001:db8::5' ),
	'IPv6 exact trusted proxy is supported'
);
same(
	'2001:db8:ffff::20',
	warmvast_get_client_ip( wv_server( '2001:db8:abcd::99', '2001:db8:ffff::20' ), '2001:db8:abcd::/48' ),
	'IPv6 CIDR trusted proxy is supported'
);

wv_reset_rate_limit_state();
$_SERVER['REMOTE_ADDR']          = '10.0.0.5';
$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.41';
for ( $i = 0; $i < WARMVAST_LEAD_RATE_LIMIT; $i++ ) {
	ok( warmvast_lead_rate_limit_ok(), 'client A request ' . ( $i + 1 ) . ' is allowed' );
}

$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.42';
ok( warmvast_lead_rate_limit_ok(), 'client B is not blocked by client A through the same trusted proxy' );

$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.41';
ok( ! warmvast_lead_rate_limit_ok(), 'client A sixth request is still rate limited' );

$client_a_key = 'warmvast_lead_rl_' . md5( '198.51.100.41' );
$client_b_key = 'warmvast_lead_rl_' . md5( '198.51.100.42' );
$proxy_key    = 'warmvast_lead_rl_' . md5( '10.0.0.5' );
same( WARMVAST_LEAD_RATE_LIMIT, $GLOBALS['wv_test_transients'][ $client_a_key ]['value'], 'client A has its own 5-request bucket' );
same( 1, $GLOBALS['wv_test_transients'][ $client_b_key ]['value'], 'client B has a distinct bucket behind the same proxy' );
ok( ! isset( $GLOBALS['wv_test_transients'][ $proxy_key ] ), 'trusted proxy REMOTE_ADDR is not used as the shared bucket key' );
same( WARMVAST_LEAD_RATE_WINDOW, $GLOBALS['wv_test_transients'][ $client_a_key ]['expiration'], 'rate-limit window remains 600 seconds' );

// ---------------------------------------------------------------------------

echo "\n" . str_repeat( '-', 60 ) . "\n";
if ( empty( $failed ) ) {
	echo "OK — $tests assertions passed.\n";
	exit( 0 );
}
echo count( $failed ) . " of $tests assertions FAILED:\n";
foreach ( $failed as $f ) {
	echo "  - $f\n";
}
exit( 1 );
