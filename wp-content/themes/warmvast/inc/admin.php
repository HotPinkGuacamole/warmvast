<?php
/**
 * Warmvast content admin -- makes the business data that used to live only
 * in PHP arrays (see inc/config.php, inc/gemeente-content.php,
 * inc/service-content.php) editable from wp-admin, with no plugin.
 *
 * WHY THIS EXISTS: before this file, updating a review, a team member, a
 * municipal subsidy figure or the ISDE tariff table meant editing a PHP file
 * and redeploying -- exactly the "can't touch texts without touching code"
 * problem this theme had. WordPress's own answer to that is the options API
 * (for sitewide singleton data) and post meta (for data that belongs to one
 * page). This file wires both up by hand, deliberately not via a plugin
 * (ACF etc.) -- same reasoning as the security hardening in functions.php:
 * a plugin means trusting its update cadence and its own attack surface for
 * something a few hundred well-understood lines of theme code can do.
 *
 * THE OVERLAY PATTERN, used consistently everywhere in this file: every
 * piece of content this unlocks still has its current hardcoded value as a
 * DEFAULT. warmvast_reviews(), warmvast_team(), warmvast_isde_rates(),
 * warmvast_zaanstreek_gemeenten(), warmvast_service_detail() etc. (see
 * inc/config.php, inc/gemeente-content.php, inc/service-content.php) start
 * from that literal array and overlay a saved option/postmeta value ON TOP
 * of it, field by field, only where something was actually saved. That
 * means: nothing changes on deploy until someone actually edits a field in
 * wp-admin, an empty field always means "use the code default" rather than
 * "show nothing", and a field this file doesn't expose (or a brand-new
 * install with no options saved yet) still renders exactly as it does today.
 *
 * WHAT DELIBERATELY STAYS OUT OF THIS: the woningscan/lead REST logic, the
 * n8n webhook URL+secret, the trusted-proxy allowlist, and the structural
 * identifiers inside warmvast_isde_rates() (label/short/field/slug -- the
 * slug in particular drives page-template routing, see
 * warmvast_page_template_fallback() in functions.php). Those are wiring,
 * not content; a typo there breaks the app rather than just reading oddly,
 * so they stay in code review instead of a form.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =====================================================================
 * Shared field renderers -- every settings/meta-box page below is built
 * from these, so a form field looks and behaves the same everywhere.
 * ===================================================================== */

/**
 * A single-line text input.
 */
function warmvast_admin_field_text( $name, $value, $placeholder = '' ) {
	printf(
		'<input type="text" class="regular-text" name="%s" value="%s" placeholder="%s">',
		esc_attr( $name ),
		esc_attr( (string) $value ),
		esc_attr( $placeholder )
	);
}

/**
 * A multi-line textarea.
 */
function warmvast_admin_field_textarea( $name, $value, $rows = 4 ) {
	printf(
		'<textarea class="large-text" rows="%d" name="%s">%s</textarea>',
		(int) $rows,
		esc_attr( $name ),
		esc_textarea( (string) $value )
	);
}

/**
 * A number input. $step accepts '1' (integers) or '0.01' etc. (decimals).
 */
function warmvast_admin_field_number( $name, $value, $step = '1', $min = null, $max = null ) {
	printf(
		'<input type="number" step="%s" %s %s class="small-text" name="%s" value="%s">',
		esc_attr( $step ),
		null !== $min ? 'min="' . esc_attr( $min ) . '"' : '',
		null !== $max ? 'max="' . esc_attr( $max ) . '"' : '',
		esc_attr( $name ),
		esc_attr( (string) $value )
	);
}

/**
 * An image field: a text input holding the URL (so it degrades gracefully
 * with JS off / media library unavailable) plus a "Kies afbeelding" button
 * that opens WordPress's own core media library (wp.media -- no plugin) and
 * a live thumbnail preview. See warmvast_admin_enqueue_media() for the JS
 * that binds every .wv-media-button on the page generically.
 */
function warmvast_admin_field_media( $name, $value ) {
	$value = (string) $value;
	printf(
		'<span class="wv-media-field">
			<img class="wv-media-preview" src="%s" style="max-width:80px;max-height:60px;vertical-align:middle;margin-right:8px;%s">
			<input type="text" class="regular-text wv-media-input" name="%s" value="%s" placeholder="https://...">
			<button type="button" class="button wv-media-button">Kies afbeelding</button>
		</span>',
		esc_url( $value ),
		$value ? '' : 'display:none;',
		esc_attr( $name ),
		esc_attr( $value )
	);
}

/**
 * Enqueue WordPress's core media library + the small inline script that
 * binds every .wv-media-button on the page to it. Scoped to only our own
 * admin screens (checked via the page slug) so it never loads elsewhere.
 */
function warmvast_admin_enqueue_media( $hook ) {
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	if ( 0 !== strpos( $page, 'warmvast-' ) ) {
		return;
	}
	wp_enqueue_media();
	$js = <<<'JS'
document.addEventListener('click', function (e) {
	var btn = e.target.closest('.wv-media-button');
	if (!btn) return;
	e.preventDefault();
	var field = btn.closest('.wv-media-field');
	var input = field.querySelector('.wv-media-input');
	var preview = field.querySelector('.wv-media-preview');
	var frame = wp.media({ title: 'Kies afbeelding', multiple: false, library: { type: 'image' } });
	frame.on('select', function () {
		var att = frame.state().get('selection').first().toJSON();
		var url = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
		input.value = url;
		preview.src = url;
		preview.style.display = '';
	});
	frame.open();
});
JS;
	wp_add_inline_script( 'media-editor', $js );
}
add_action( 'admin_enqueue_scripts', 'warmvast_admin_enqueue_media' );

/**
 * Read one repeater's POSTed rows, drop rows where $required_key is empty
 * (an untouched spare row), and sanitize every remaining field through the
 * callback named for it in $schema.
 *
 * @param mixed                 $raw_rows     $_POST[...] for this repeater.
 * @param array<string,callable> $schema      field name => sanitizer.
 * @param string                $required_key A row with this field empty is dropped.
 * @return array<int,array<string,mixed>>
 */
function warmvast_admin_sanitize_rows( $raw_rows, $schema, $required_key ) {
	$out = array();
	if ( ! is_array( $raw_rows ) ) {
		return $out;
	}
	foreach ( $raw_rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$required_val = isset( $row[ $required_key ] ) ? trim( wp_unslash( (string) $row[ $required_key ] ) ) : '';
		if ( '' === $required_val ) {
			continue;
		}
		$clean = array();
		foreach ( $schema as $field => $sanitizer ) {
			$raw            = isset( $row[ $field ] ) ? $row[ $field ] : '';
			$clean[ $field ] = call_user_func( $sanitizer, $raw );
		}
		$out[] = $clean;
	}
	return $out;
}

function warmvast_admin_sanitize_text( $v ) {
	return sanitize_text_field( wp_unslash( (string) $v ) );
}
function warmvast_admin_sanitize_textarea( $v ) {
	return sanitize_textarea_field( wp_unslash( (string) $v ) );
}
function warmvast_admin_sanitize_url( $v ) {
	return esc_url_raw( wp_unslash( (string) $v ) );
}
function warmvast_admin_sanitize_int( $v ) {
	return (int) $v;
}
function warmvast_admin_sanitize_stars( $v ) {
	return max( 1, min( 5, (int) $v ) );
}
/**
 * A textarea where each non-empty line becomes one list item -- used for
 * the plain bullet lists (symptoms/suitable) on a service page, which don't
 * need a full repeater grid just to add or remove one line.
 */
function warmvast_admin_sanitize_lines( $v ) {
	$lines = preg_split( '/\r\n|\r|\n/', (string) $v );
	$lines = array_map( 'sanitize_text_field', array_map( 'wp_unslash', $lines ) );
	$lines = array_map( 'trim', $lines );
	return array_values( array_filter( $lines, static fn( $l ) => '' !== $l ) );
}
/**
 * Checkbox group against a fixed allowlist (spouw/vloer/glas/dak) -- the
 * same "keys in, never client-invented values out" discipline inc/lead.php
 * already uses for the lead form's own measures field.
 */
function warmvast_admin_sanitize_measures( $v ) {
	$allowed  = array_keys( warmvast_isde_rates() );
	$claimed  = is_array( $v ) ? $v : array();
	$selected = array();
	foreach ( $claimed as $key ) {
		if ( is_scalar( $key ) && in_array( (string) $key, $allowed, true ) ) {
			$selected[] = (string) $key;
		}
	}
	// Keep presentation order consistent, same as inc/lead.php does.
	return array_values( array_intersect( warmvast_measure_order(), $selected ) );
}

/* =====================================================================
 * Menu registration
 * ===================================================================== */

add_action(
	'admin_menu',
	function () {
		add_menu_page( 'Warmvast', 'Warmvast', 'manage_options', 'warmvast-company', 'warmvast_admin_render_company', 'dashicons-admin-multisite', 58 );
		add_submenu_page( 'warmvast-company', 'Bedrijfsgegevens', 'Bedrijfsgegevens', 'manage_options', 'warmvast-company', 'warmvast_admin_render_company' );
		add_submenu_page( 'warmvast-company', 'ISDE-tarieven', 'ISDE-tarieven', 'manage_options', 'warmvast-isde', 'warmvast_admin_render_isde' );
		add_submenu_page( 'warmvast-company', 'Reviews', 'Reviews', 'manage_options', 'warmvast-reviews', 'warmvast_admin_render_reviews' );
		add_submenu_page( 'warmvast-company', 'Team', 'Team', 'manage_options', 'warmvast-team', 'warmvast_admin_render_team' );
		add_submenu_page( 'warmvast-company', 'Kernwaarden', 'Kernwaarden', 'manage_options', 'warmvast-kernwaarden', 'warmvast_admin_render_kernwaarden' );
		add_submenu_page( 'warmvast-company', 'Certificeringen', 'Certificeringen', 'manage_options', 'warmvast-certificeringen', 'warmvast_admin_render_certificeringen' );
		add_submenu_page( 'warmvast-company', 'Ons werk', 'Ons werk', 'manage_options', 'warmvast-projecten', 'warmvast_admin_render_projecten' );
	}
);

/**
 * Shared page chrome: title + a "saved" notice when ?updated=1.
 */
function warmvast_admin_page_open( $title ) {
	echo '<div class="wrap"><h1>' . esc_html( $title ) . '</h1>';
	if ( isset( $_GET['updated'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>Opgeslagen.</p></div>';
	}
}

/* =====================================================================
 * 1. Bedrijfsgegevens (company/contact) -- option: warmvast_opt_company
 * ===================================================================== */

/**
 * warmvast_company() and its defaults live in inc/config.php, not here --
 * that file is required before this one, and its WARMVAST_PHONE etc.
 * constants need to call it at require-time. This file only renders/saves
 * the form; see inc/config.php for the data side of the overlay pattern.
 */

function warmvast_admin_render_company() {
	warmvast_admin_page_open( 'Warmvast — Bedrijfsgegevens' );
	$c = warmvast_company();
	?>
	<p>Contactgegevens, adres en de cijfers die sitebreed getoond worden (oprichtingsjaar, aantal geïsoleerde woningen, garantietermijn). Leeg = niet tonen, precies zoals nu.</p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="warmvast_save_company">
		<?php wp_nonce_field( 'warmvast_save_company', 'warmvast_nonce' ); ?>
		<table class="form-table" role="presentation">
			<tr><th><label>Telefoonnummer (weergave)</label></th><td><?php warmvast_admin_field_text( 'phone', $c['phone'] ); ?></td></tr>
			<tr><th><label>Telefoonnummer (tel: link, +31...)</label></th><td><?php warmvast_admin_field_text( 'phone_raw', $c['phone_raw'] ); ?></td></tr>
			<tr><th><label>E-mailadres</label></th><td><?php warmvast_admin_field_text( 'email', $c['email'] ); ?></td></tr>
			<tr><th><label>Openingstijden</label></th><td><?php warmvast_admin_field_text( 'hours', $c['hours'] ); ?></td></tr>
			<tr><th><label>Werkgebied (omschrijving)</label></th><td><?php warmvast_admin_field_text( 'region', $c['region'] ); ?></td></tr>
			<tr><th><label>Straat + huisnummer (vestiging)</label></th><td><?php warmvast_admin_field_text( 'address_street', $c['address_street'] ); ?></td></tr>
			<tr><th><label>Postcode</label></th><td><?php warmvast_admin_field_text( 'address_postal', $c['address_postal'] ); ?></td></tr>
			<tr><th><label>Plaats</label></th><td><?php warmvast_admin_field_text( 'address_city', $c['address_city'] ); ?></td></tr>
			<tr><th><label>WhatsApp-nummer (cijfers, met landcode, geen +)</label></th><td><?php warmvast_admin_field_text( 'whatsapp', $c['whatsapp'], '31612345678' ); ?><p class="description">Leeg = geen WhatsApp-knoppen op de site.</p></td></tr>
			<tr><th><label>X / Twitter handle (zonder @)</label></th><td><?php warmvast_admin_field_text( 'twitter_handle', $c['twitter_handle'] ); ?></td></tr>
			<tr><th><label>Facebook-URL</label></th><td><?php warmvast_admin_field_text( 'facebook_url', $c['facebook_url'] ); ?></td></tr>
			<tr><th><label>Opgericht in (jaar)</label></th><td><?php warmvast_admin_field_number( 'founded', $c['founded'], '1', 1900, 2100 ); ?></td></tr>
			<tr><th><label>Aantal geïsoleerde woningen</label></th><td><?php warmvast_admin_field_number( 'homes_insulated', $c['homes_insulated'], '1', 0 ); ?><p class="description">0 = verbergt dit cijfer sitebreed (bewust: nooit een verzonnen aantal tonen).</p></td></tr>
			<tr><th><label>Garantietermijn (jaar)</label></th><td><?php warmvast_admin_field_number( 'warranty_years', $c['warranty_years'], '1', 0 ); ?><p class="description">0 = verbergt elke garantieclaim sitebreed, incl. de wettelijke pagina's.</p></td></tr>
		</table>
		<?php submit_button( 'Opslaan' ); ?>
	</form>
	<?php
	echo '</div>';
}

add_action(
	'admin_post_warmvast_save_company',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Geen toegang.', 'warmvast' ) );
		}
		check_admin_referer( 'warmvast_save_company', 'warmvast_nonce' );

		$defaults = warmvast_company_defaults();
		$clean    = array();
		foreach ( $defaults as $key => $default ) {
			$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
			if ( in_array( $key, array( 'founded', 'homes_insulated', 'warranty_years' ), true ) ) {
				$clean[ $key ] = max( 0, (int) $raw );
			} elseif ( 'facebook_url' === $key ) {
				$clean[ $key ] = esc_url_raw( $raw );
			} else {
				$clean[ $key ] = sanitize_text_field( $raw );
			}
		}
		update_option( 'warmvast_opt_company', $clean );
		warmvast_page_cache_purge_all(); // phone/address/etc. can be baked into any cached page.
		wp_safe_redirect( admin_url( 'admin.php?page=warmvast-company&updated=1' ) );
		exit;
	}
);

/* =====================================================================
 * 2. ISDE-tarieven -- option: warmvast_opt_isde_rates
 *
 * Only the numeric fields (baseRate/minM2/maxM2) are editable. label/short/
 * field/slug are structural identifiers used elsewhere in the codebase
 * (page routing, the lead payload's measure labels, the JS calculator's
 * field mapping) and stay fixed -- see this file's header comment.
 * ===================================================================== */

function warmvast_admin_render_isde() {
	warmvast_admin_page_open( 'Warmvast — ISDE-tarieven' );
	$rates   = warmvast_isde_rates(); // already overlaid -- see inc/config.php.
	$saved   = get_option( 'warmvast_opt_isde_rates', array() );
	$checked = isset( $saved['verified_on'] ) ? $saved['verified_on'] : '';
	?>
	<p>Basisbedrag, minimum en maximum m² per maatregel -- bron: <a href="https://www.rvo.nl/subsidies-financiering/isde/woningeigenaren/isolatiemaatregelen" target="_blank" rel="noopener">RVO</a>. Verifieer bij elke wijziging tegen de officiële RVO-tabel; deze tarieven voeden direct de rekentool op de site.</p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="warmvast_save_isde">
		<?php wp_nonce_field( 'warmvast_save_isde', 'warmvast_nonce' ); ?>
		<table class="widefat" style="max-width:760px">
			<thead><tr><th>Maatregel</th><th>€ per m²</th><th>Min. m²</th><th>Max. m²</th></tr></thead>
			<tbody>
			<?php foreach ( $rates as $key => $rate ) : ?>
				<tr>
					<td><strong><?php echo esc_html( $rate['label'] ); ?></strong></td>
					<td><?php warmvast_admin_field_number( "rates[$key][baseRate]", $rate['baseRate'], '0.01', 0 ); ?></td>
					<td><?php warmvast_admin_field_number( "rates[$key][minM2]", $rate['minM2'], '1', 0 ); ?></td>
					<td><?php warmvast_admin_field_number( "rates[$key][maxM2]", $rate['maxM2'], '1', 0 ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<table class="form-table" role="presentation">
			<tr><th><label>Laatst geverifieerd tegen RVO op</label></th><td><input type="date" name="verified_on" value="<?php echo esc_attr( $checked ); ?>"></td></tr>
		</table>
		<?php submit_button( 'Opslaan' ); ?>
	</form>
	<?php
	echo '</div>';
}

add_action(
	'admin_post_warmvast_save_isde',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Geen toegang.', 'warmvast' ) );
		}
		check_admin_referer( 'warmvast_save_isde', 'warmvast_nonce' );

		$raw   = isset( $_POST['rates'] ) && is_array( $_POST['rates'] ) ? wp_unslash( $_POST['rates'] ) : array();
		$clean = array( 'verified_on' => isset( $_POST['verified_on'] ) ? sanitize_text_field( wp_unslash( $_POST['verified_on'] ) ) : '' );

		// Only known measure keys -- never lets a form field introduce a
		// new "measure" that the rest of the codebase (lead labels, page
		// template mapping) doesn't know about.
		foreach ( array_keys( warmvast_isde_rates() ) as $key ) {
			if ( ! isset( $raw[ $key ] ) || ! is_array( $raw[ $key ] ) ) {
				continue;
			}
			$min_m2 = max( 0, (int) ( $raw[ $key ]['minM2'] ?? 0 ) );
			$max_m2 = max( $min_m2, (int) ( $raw[ $key ]['maxM2'] ?? 0 ) ); // never below min, however it's typed in.
			$clean[ $key ] = array(
				'baseRate' => max( 0, (float) ( $raw[ $key ]['baseRate'] ?? 0 ) ),
				'minM2'    => $min_m2,
				'maxM2'    => $max_m2,
			);
		}
		update_option( 'warmvast_opt_isde_rates', $clean );
		warmvast_page_cache_purge_all(); // the rates feed every service page and the calculator's localized JS.
		wp_safe_redirect( admin_url( 'admin.php?page=warmvast-isde&updated=1' ) );
		exit;
	}
);

/* =====================================================================
 * 3. Reviews -- option: warmvast_opt_reviews
 * ===================================================================== */

const WARMVAST_REVIEW_SPARE_ROWS = 3;

function warmvast_admin_render_reviews() {
	warmvast_admin_page_open( 'Warmvast — Reviews' );

	$google_configured = defined( 'WARMVAST_GOOGLE_PLACES_API_KEY' ) && '' !== trim( WARMVAST_GOOGLE_PLACES_API_KEY );
	$place_id          = get_option( 'warmvast_opt_google_place_id', '' );
	$live              = warmvast_google_reviews_fetch();

	// Pre-fill the manual/fallback form below from the RAW saved option,
	// never from warmvast_reviews()'s overlaid result -- if Google is live,
	// that result IS Google's data, and pre-filling the fallback form with
	// it would silently overwrite the manual list with a Google snapshot
	// the next time this form is saved.
	$saved = get_option( 'warmvast_opt_reviews', array() );
	$r     = wp_parse_args(
		is_array( $saved ) ? $saved : array(),
		array(
			'verified' => false,
			'source'   => 'Google',
			'rating'   => 0,
			'count'    => 0,
			'items'    => array(),
		)
	);
	?>
	<p><strong>Merkregel: nooit verzonnen reviews.</strong> Zolang er geen live Google-koppeling actief is EN "Reviews tonen" hieronder uit staat, verschijnt de hele reviewsectie én de bijbehorende schema.org-review-schema nergens op de site.</p>

	<h2>Live Google-koppeling</h2>
	<?php if ( $google_configured ) : ?>
		<?php if ( is_array( $live ) && ! empty( $live['items'] ) ) : ?>
			<div class="notice notice-success inline"><p>✅ Actief: <?php echo esc_html( number_format_i18n( $live['rating'], 1 ) ); ?>/5 op basis van <?php echo (int) $live['count']; ?> reviews bij Google, <?php echo (int) count( $live['items'] ); ?> tonen live op de site (ververst dagelijks). De handmatige lijst hieronder is nu de back-up, niet wat er live staat.</p></div>
		<?php else : ?>
			<div class="notice notice-warning inline"><p>⚠️ API-sleutel is ingesteld maar er komt nu geen data terug (verkeerde Place ID, geen reviews bij Google, of een tijdelijke fout -- geeft zichzelf binnen een uur een nieuwe kans). De handmatige lijst hieronder wordt ondertussen getoond.</p></div>
		<?php endif; ?>
	<?php else : ?>
		<p>Nog niet actief. <code>WARMVAST_GOOGLE_PLACES_API_KEY</code> staat niet in de configuratie -- zie de installatie-uitleg bovenaan <code>inc/reviews.php</code>. Zolang dat leeg is, is de handmatige lijst hieronder de enige bron.</p>
	<?php endif; ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="warmvast_save_google_place">
		<?php wp_nonce_field( 'warmvast_save_google_place', 'warmvast_nonce' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th><label>Google Place ID</label></th>
				<td>
					<?php warmvast_admin_field_text( 'place_id', $place_id, 'ChIJ...' ); ?>
					<p class="description">Vind deze via Google's <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank" rel="noopener">Place ID Finder</a> (zoek op "Warmvast Isolatie"), of haal 'm uit de share-link van de Google Bedrijfspagina.</p>
				</td>
			</tr>
		</table>
		<?php submit_button( 'Place ID opslaan' ); ?>
	</form>

	<h2>Handmatige reviews (back-up)</h2>
	<p class="description">Wordt getoond zolang de live Google-koppeling hierboven niet actief is, en blijft daarna de back-up voor als Google een keer niet reageert.</p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="warmvast_save_reviews">
		<?php wp_nonce_field( 'warmvast_save_reviews', 'warmvast_nonce' ); ?>
		<table class="form-table" role="presentation">
			<tr><th><label>Reviews tonen op de site</label></th><td><label><input type="checkbox" name="verified" value="1" <?php checked( ! empty( $r['verified'] ) ); ?>> Aan (alleen aanzetten als onderstaande reviews echt zijn)</label></td></tr>
			<tr><th><label>Bron</label></th><td><?php warmvast_admin_field_text( 'source', $r['source'] ); ?></td></tr>
			<tr><th><label>Gemiddelde score</label></th><td><?php warmvast_admin_field_number( 'rating', $r['rating'], '0.1', 0, 5 ); ?> / 5</td></tr>
			<tr><th><label>Aantal reviews</label></th><td><?php warmvast_admin_field_number( 'count', $r['count'], '1', 0 ); ?></td></tr>
		</table>
		<h2>Individuele reviews</h2>
		<table class="widefat">
			<thead><tr><th style="width:16%">Naam</th><th style="width:14%">Plaats</th><th style="width:8%">Sterren</th><th>Tekst</th></tr></thead>
			<tbody>
			<?php
			$rows = $r['items'];
			for ( $i = 0; $i < count( $rows ) + WARMVAST_REVIEW_SPARE_ROWS; $i++ ) :
				$row = $rows[ $i ] ?? array(
					'name'  => '',
					'place' => '',
					'stars' => 5,
					'text'  => '',
				);
				?>
				<tr>
					<td><?php warmvast_admin_field_text( "items[$i][name]", $row['name'] ); ?></td>
					<td><?php warmvast_admin_field_text( "items[$i][place]", $row['place'] ); ?></td>
					<td><?php warmvast_admin_field_number( "items[$i][stars]", $row['stars'], '1', 1, 5 ); ?></td>
					<td><?php warmvast_admin_field_textarea( "items[$i][text]", $row['text'], 2 ); ?></td>
				</tr>
			<?php endfor; ?>
			</tbody>
		</table>
		<p class="description">Een rij met een lege naam wordt genegeerd -- meer ruimte nodig? Sla op en er verschijnen weer <?php echo (int) WARMVAST_REVIEW_SPARE_ROWS; ?> lege rijen onderaan.</p>
		<?php submit_button( 'Opslaan' ); ?>
	</form>
	<?php
	echo '</div>';
}

add_action(
	'admin_post_warmvast_save_google_place',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Geen toegang.', 'warmvast' ) );
		}
		check_admin_referer( 'warmvast_save_google_place', 'warmvast_nonce' );

		$place_id = isset( $_POST['place_id'] ) ? sanitize_text_field( wp_unslash( $_POST['place_id'] ) ) : '';
		update_option( 'warmvast_opt_google_place_id', $place_id );
		// Force a fresh fetch on the very next page load instead of waiting
		// up to a day for the old (or absent) cache entry to expire --
		// otherwise saving a corrected Place ID would appear to do nothing.
		delete_transient( 'warmvast_google_reviews_' . md5( $place_id ) );
		warmvast_page_cache_purge_all();
		wp_safe_redirect( admin_url( 'admin.php?page=warmvast-reviews&updated=1' ) );
		exit;
	}
);

add_action(
	'admin_post_warmvast_save_reviews',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Geen toegang.', 'warmvast' ) );
		}
		check_admin_referer( 'warmvast_save_reviews', 'warmvast_nonce' );

		$items = warmvast_admin_sanitize_rows(
			isset( $_POST['items'] ) ? wp_unslash( $_POST['items'] ) : array(),
			array(
				'name'  => 'warmvast_admin_sanitize_text',
				'place' => 'warmvast_admin_sanitize_text',
				'stars' => 'warmvast_admin_sanitize_stars',
				'text'  => 'warmvast_admin_sanitize_textarea',
			),
			'name'
		);

		$clean = array(
			'verified' => ! empty( $_POST['verified'] ),
			'source'   => isset( $_POST['source'] ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : 'Google',
			'rating'   => isset( $_POST['rating'] ) ? max( 0, min( 5, (float) $_POST['rating'] ) ) : 0,
			'count'    => isset( $_POST['count'] ) ? max( 0, (int) $_POST['count'] ) : 0,
			'items'    => $items,
		);
		// Never let "verified" stay true with nothing real behind it -- the
		// one guardrail the brand rule actually needs enforced in code.
		if ( empty( $clean['items'] ) ) {
			$clean['verified'] = false;
		}
		update_option( 'warmvast_opt_reviews', $clean );
		warmvast_page_cache_purge_all();
		wp_safe_redirect( admin_url( 'admin.php?page=warmvast-reviews&updated=1' ) );
		exit;
	}
);

/* =====================================================================
 * 4. Team -- option: warmvast_opt_team
 * ===================================================================== */

const WARMVAST_TEAM_SPARE_ROWS = 2;

function warmvast_admin_render_team() {
	warmvast_admin_page_open( 'Warmvast — Team' );
	$rows = warmvast_team();
	?>
	<p>Echte mensen, echte foto's -- geen stockfoto's, geen verzonnen namen (merkregel). Foto's: gebruik de media-picker; voor de beste weergave draai je nieuwe foto's ook door <code>tools/build-images.py</code> voor de bijgesneden webp-varianten zoals de bestaande teamfoto's die hebben.</p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="warmvast_save_team">
		<?php wp_nonce_field( 'warmvast_save_team', 'warmvast_nonce' ); ?>
		<table class="widefat">
			<thead><tr><th style="width:16%">Naam</th><th style="width:16%">Functie</th><th>Bio</th><th style="width:22%">Foto</th></tr></thead>
			<tbody>
			<?php
			for ( $i = 0; $i < count( $rows ) + WARMVAST_TEAM_SPARE_ROWS; $i++ ) :
				$row = $rows[ $i ] ?? array(
					'naam'    => '',
					'functie' => '',
					'bio'     => '',
					'foto'    => '',
				);
				?>
				<tr>
					<td><?php warmvast_admin_field_text( "rows[$i][naam]", $row['naam'] ); ?></td>
					<td><?php warmvast_admin_field_text( "rows[$i][functie]", $row['functie'] ); ?></td>
					<td><?php warmvast_admin_field_textarea( "rows[$i][bio]", $row['bio'], 2 ); ?></td>
					<td><?php warmvast_admin_field_media( "rows[$i][foto]", $row['foto'] ); ?></td>
				</tr>
			<?php endfor; ?>
			</tbody>
		</table>
		<p class="description">Een rij zonder naam wordt genegeerd.</p>
		<?php submit_button( 'Opslaan' ); ?>
	</form>
	<?php
	echo '</div>';
}

add_action(
	'admin_post_warmvast_save_team',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Geen toegang.', 'warmvast' ) );
		}
		check_admin_referer( 'warmvast_save_team', 'warmvast_nonce' );

		$rows = warmvast_admin_sanitize_rows(
			isset( $_POST['rows'] ) ? wp_unslash( $_POST['rows'] ) : array(),
			array(
				'naam'    => 'warmvast_admin_sanitize_text',
				'functie' => 'warmvast_admin_sanitize_text',
				'bio'     => 'warmvast_admin_sanitize_textarea',
				'foto'    => 'warmvast_admin_sanitize_text', // a media-library URL, not necessarily http (can be a theme-relative path) -- kept as plain text on purpose.
			),
			'naam'
		);
		foreach ( $rows as &$row ) {
			$row['key'] = sanitize_title( $row['naam'] );
		}
		unset( $row );
		update_option( 'warmvast_opt_team', $rows );
		warmvast_page_cache_purge_all();
		wp_safe_redirect( admin_url( 'admin.php?page=warmvast-team&updated=1' ) );
		exit;
	}
);

/* =====================================================================
 * 5. Kernwaarden -- option: warmvast_opt_kernwaarden
 * ===================================================================== */

const WARMVAST_KERNWAARDEN_SPARE_ROWS = 2;

function warmvast_admin_render_kernwaarden() {
	warmvast_admin_page_open( 'Warmvast — Kernwaarden' );
	$rows = warmvast_kernwaarden();
	$icons = array( 'ruler', 'shield', 'camera', 'map', 'award', 'building', 'check', 'clock', 'doc', 'euro', 'phone', 'thermo', 'wall', 'floor', 'glass' );
	?>
	<p>Korte badge-strip met Warmvast's eigen werkwijze (geen keurmerklogo's -- zie Certificeringen daarvoor).</p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="warmvast_save_kernwaarden">
		<?php wp_nonce_field( 'warmvast_save_kernwaarden', 'warmvast_nonce' ); ?>
		<table class="widefat">
			<thead><tr><th style="width:20%">Icoon</th><th>Tekst</th></tr></thead>
			<tbody>
			<?php
			for ( $i = 0; $i < count( $rows ) + WARMVAST_KERNWAARDEN_SPARE_ROWS; $i++ ) :
				$row = $rows[ $i ] ?? array(
					'icon' => 'shield',
					'naam' => '',
				);
				?>
				<tr>
					<td>
						<select name="rows[<?php echo (int) $i; ?>][icon]">
							<?php foreach ( $icons as $icon_name ) : ?>
								<option value="<?php echo esc_attr( $icon_name ); ?>" <?php selected( $row['icon'], $icon_name ); ?>><?php echo esc_html( $icon_name ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
					<td><?php warmvast_admin_field_text( "rows[$i][naam]", $row['naam'] ); ?></td>
				</tr>
			<?php endfor; ?>
			</tbody>
		</table>
		<p class="description">Een rij zonder tekst wordt genegeerd.</p>
		<?php submit_button( 'Opslaan' ); ?>
	</form>
	<?php
	echo '</div>';
}

add_action(
	'admin_post_warmvast_save_kernwaarden',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Geen toegang.', 'warmvast' ) );
		}
		check_admin_referer( 'warmvast_save_kernwaarden', 'warmvast_nonce' );

		$rows = warmvast_admin_sanitize_rows(
			isset( $_POST['rows'] ) ? wp_unslash( $_POST['rows'] ) : array(),
			array(
				'icon' => 'warmvast_admin_sanitize_text',
				'naam' => 'warmvast_admin_sanitize_text',
			),
			'naam'
		);
		update_option( 'warmvast_opt_kernwaarden', $rows );
		warmvast_page_cache_purge_all();
		wp_safe_redirect( admin_url( 'admin.php?page=warmvast-kernwaarden&updated=1' ) );
		exit;
	}
);

/* =====================================================================
 * 6. Certificeringen -- option: warmvast_opt_certificeringen
 * ===================================================================== */

const WARMVAST_CERT_SPARE_ROWS = 4;

function warmvast_admin_render_certificeringen() {
	warmvast_admin_page_open( 'Warmvast — Certificeringen' );
	$rows = warmvast_certificeringen();
	?>
	<p><strong>Merkregel: alleen keurmerken tonen die Warmvast daadwerkelijk heeft</strong> (KOMO, VENIN, SKG-IKOB, VCA, ISO 9001, etc.) -- dit zijn juridisch betekenisvolle merken, ten onrechte tonen kan een merkinbreuk zijn.</p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="warmvast_save_certificeringen">
		<?php wp_nonce_field( 'warmvast_save_certificeringen', 'warmvast_nonce' ); ?>
		<table class="widefat">
			<thead><tr><th style="width:14%">Naam</th><th>Beschrijving</th><th style="width:20%">URL</th><th style="width:18%">Logo</th></tr></thead>
			<tbody>
			<?php
			for ( $i = 0; $i < count( $rows ) + WARMVAST_CERT_SPARE_ROWS; $i++ ) :
				$row = $rows[ $i ] ?? array(
					'naam'         => '',
					'beschrijving' => '',
					'url'          => '',
					'logo'         => '',
				);
				?>
				<tr>
					<td><?php warmvast_admin_field_text( "rows[$i][naam]", $row['naam'] ); ?></td>
					<td><?php warmvast_admin_field_text( "rows[$i][beschrijving]", $row['beschrijving'] ); ?></td>
					<td><?php warmvast_admin_field_text( "rows[$i][url]", $row['url'] ); ?></td>
					<td><?php warmvast_admin_field_media( "rows[$i][logo]", $row['logo'] ); ?></td>
				</tr>
			<?php endfor; ?>
			</tbody>
		</table>
		<p class="description">Een rij zonder naam wordt genegeerd.</p>
		<?php submit_button( 'Opslaan' ); ?>
	</form>
	<?php
	echo '</div>';
}

add_action(
	'admin_post_warmvast_save_certificeringen',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Geen toegang.', 'warmvast' ) );
		}
		check_admin_referer( 'warmvast_save_certificeringen', 'warmvast_nonce' );

		$rows = warmvast_admin_sanitize_rows(
			isset( $_POST['rows'] ) ? wp_unslash( $_POST['rows'] ) : array(),
			array(
				'naam'         => 'warmvast_admin_sanitize_text',
				'beschrijving' => 'warmvast_admin_sanitize_text',
				'url'          => 'warmvast_admin_sanitize_url',
				'logo'         => 'warmvast_admin_sanitize_text',
			),
			'naam'
		);
		update_option( 'warmvast_opt_certificeringen', $rows );
		warmvast_page_cache_purge_all();
		wp_safe_redirect( admin_url( 'admin.php?page=warmvast-certificeringen&updated=1' ) );
		exit;
	}
);

/* =====================================================================
 * 7. Ons werk / Projecten -- option: warmvast_opt_projecten
 * ===================================================================== */

const WARMVAST_PROJECT_SPARE_ROWS = 6;

function warmvast_admin_render_projecten() {
	warmvast_admin_page_open( 'Warmvast — Ons werk (projecten)' );
	$rows        = warmvast_projecten();
	$measure_map = warmvast_isde_rates();
	?>
	<p><strong>Merkregel: alleen echte, uitgevoerde projecten</strong> -- geen stockfoto's, geen voorbeeldprojecten. Leeg = de /ons-werk/ pagina toont een eerlijke "binnenkort" staat in plaats van een gevulde galerij.</p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="warmvast_save_projecten">
		<?php wp_nonce_field( 'warmvast_save_projecten', 'warmvast_nonce' ); ?>
		<table class="widefat">
			<thead><tr><th style="width:16%">Titel</th><th style="width:12%">Plaats</th><th style="width:18%">Maatregelen</th><th style="width:10%">Datum</th><th style="width:20%">Foto</th></tr></thead>
			<tbody>
			<?php
			for ( $i = 0; $i < count( $rows ) + WARMVAST_PROJECT_SPARE_ROWS; $i++ ) :
				$row = $rows[ $i ] ?? array(
					'titel'       => '',
					'plaats'      => '',
					'maatregelen' => array(),
					'datum'       => '',
					'foto'        => '',
				);
				?>
				<tr>
					<td><?php warmvast_admin_field_text( "rows[$i][titel]", $row['titel'] ); ?></td>
					<td><?php warmvast_admin_field_text( "rows[$i][plaats]", $row['plaats'] ); ?></td>
					<td>
						<?php foreach ( $measure_map as $mkey => $mrate ) : ?>
							<label style="display:inline-block;margin-right:8px;">
								<input type="checkbox" name="rows[<?php echo (int) $i; ?>][maatregelen][]" value="<?php echo esc_attr( $mkey ); ?>" <?php checked( in_array( $mkey, $row['maatregelen'], true ) ); ?>>
								<?php echo esc_html( $mrate['short'] ); ?>
							</label>
						<?php endforeach; ?>
					</td>
					<td><input type="month" name="rows[<?php echo (int) $i; ?>][datum]" value="<?php echo esc_attr( $row['datum'] ); ?>"></td>
					<td><?php warmvast_admin_field_media( "rows[$i][foto]", $row['foto'] ); ?></td>
				</tr>
			<?php endfor; ?>
			</tbody>
		</table>
		<p class="description">Een rij zonder titel wordt genegeerd.</p>
		<?php submit_button( 'Opslaan' ); ?>
	</form>
	<?php
	echo '</div>';
}

add_action(
	'admin_post_warmvast_save_projecten',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Geen toegang.', 'warmvast' ) );
		}
		check_admin_referer( 'warmvast_save_projecten', 'warmvast_nonce' );

		$rows = warmvast_admin_sanitize_rows(
			isset( $_POST['rows'] ) ? wp_unslash( $_POST['rows'] ) : array(),
			array(
				'titel'       => 'warmvast_admin_sanitize_text',
				'plaats'      => 'warmvast_admin_sanitize_text',
				'maatregelen' => 'warmvast_admin_sanitize_measures',
				'datum'       => 'warmvast_admin_sanitize_text',
				'foto'        => 'warmvast_admin_sanitize_text',
			),
			'titel'
		);
		update_option( 'warmvast_opt_projecten', $rows );
		warmvast_page_cache_purge_all();
		wp_safe_redirect( admin_url( 'admin.php?page=warmvast-projecten&updated=1' ) );
		exit;
	}
);

/* =====================================================================
 * Per-page content meta boxes: gemeente pages and service pages.
 *
 * Unlike the sitewide settings above, this content belongs to ONE specific
 * page (e.g. the Zaanstad prose only makes sense on /subsidie-zaanstad/),
 * so it lives as post meta on that page's own edit screen -- the standard
 * WordPress pattern for page-specific structured content -- rather than in
 * a separate global settings menu.
 * ===================================================================== */

/**
 * Whether $post is one of the 7 gemeente pages, and if so, its config key.
 *
 * @return string|null
 */
function warmvast_admin_gemeente_key_for_post( $post ) {
	if ( ! $post || 'page' !== $post->post_type ) {
		return null;
	}
	foreach ( array_keys( warmvast_zaanstreek_gemeenten() ) as $gkey ) {
		if ( 'subsidie-' . $gkey === $post->post_name ) {
			return $gkey;
		}
	}
	return null;
}

/**
 * Whether $post is one of the 4 service pages, and if so, its rate key.
 *
 * @return string|null
 */
function warmvast_admin_service_key_for_post( $post ) {
	if ( ! $post || 'page' !== $post->post_type ) {
		return null;
	}
	foreach ( warmvast_isde_rates() as $skey => $rate ) {
		if ( $rate['slug'] === $post->post_name ) {
			return $skey;
		}
	}
	return null;
}

add_action(
	'add_meta_boxes',
	function () {
		global $post;
		if ( warmvast_admin_gemeente_key_for_post( $post ) ) {
			add_meta_box( 'warmvast_gemeente', 'Warmvast — gemeente-inhoud', 'warmvast_admin_render_gemeente_box', 'page', 'normal', 'high' );
		}
		if ( warmvast_admin_service_key_for_post( $post ) ) {
			add_meta_box( 'warmvast_service', 'Warmvast — dienst-inhoud', 'warmvast_admin_render_service_box', 'page', 'normal', 'high' );
		}
	}
);

function warmvast_admin_render_gemeente_box( $post ) {
	$gkey     = warmvast_admin_gemeente_key_for_post( $post );
	$defaults = warmvast_zaanstreek_gemeenten();
	$g        = $defaults[ $gkey ];
	$get      = static fn( $field ) => get_post_meta( $post->ID, '_warmvast_' . $field, true );
	wp_nonce_field( 'warmvast_save_gemeente', 'warmvast_gemeente_nonce' );
	?>
	<p class="description">Leeg laten = de standaardtekst hieronder placeholder wordt gebruikt. Dit betreft alleen "<?php echo esc_html( $g['naam'] ); ?>".</p>
	<table class="form-table" role="presentation">
		<tr><th><label>Karakter van de woningvoorraad</label></th><td><?php warmvast_admin_field_textarea( 'karakter', $get( 'karakter' ) ?: $g['karakter'], 4 ); ?></td></tr>
		<tr><th><label>Aandachtspunt / advies</label></th><td><?php warmvast_admin_field_textarea( 'aandacht', $get( 'aandacht' ) ?: $g['aandacht'], 3 ); ?></td></tr>
	</table>
	<h4>Lokale isolatiesubsidie (NIP)</h4>
	<p class="description">Controleer altijd de bron-URL voor de actuele stand vóórdat dit gepubliceerd blijft -- gemeentelijke budgetten en voorwaarden wijzigen regelmatig.</p>
	<table class="form-table" role="presentation">
		<?php
		$gs = $g['gemeentesubsidie'];
		$gs_get = static fn( $field ) => $get( 'gs_' . $field ) ?: ( $gs[ $field ] ?? '' );
		?>
		<tr><th><label>Naam regeling</label></th><td><?php warmvast_admin_field_text( 'gs_naam', $gs_get( 'naam' ) ); ?></td></tr>
		<tr><th><label>Bedrag (max, kort)</label></th><td><?php warmvast_admin_field_text( 'gs_bedrag_max', $gs_get( 'bedrag_max' ) ); ?></td></tr>
		<tr><th><label>Bedrag (omschrijving)</label></th><td><?php warmvast_admin_field_text( 'gs_bedrag', $gs_get( 'bedrag' ) ); ?></td></tr>
		<tr><th><label>Bedrag bij laag inkomen</label></th><td><?php warmvast_admin_field_text( 'gs_bedrag_laag', $gs_get( 'bedrag_laag' ) ); ?></td></tr>
		<tr><th><label>Voorwaarden</label></th><td><?php warmvast_admin_field_textarea( 'gs_voorwaarden', $gs_get( 'voorwaarden' ), 3 ); ?></td></tr>
		<tr><th><label>Looptijd</label></th><td><?php warmvast_admin_field_text( 'gs_looptijd', $gs_get( 'looptijd' ) ); ?></td></tr>
		<tr><th><label>Bron-URL</label></th><td><?php warmvast_admin_field_text( 'gs_bron_url', $gs_get( 'bron_url' ) ); ?></td></tr>
		<tr><th><label>Gecontroleerd op</label></th><td><input type="date" name="gs_gecontroleerd" value="<?php echo esc_attr( $get( 'gs_gecontroleerd' ) ); ?>"></td></tr>
	</table>
	<?php
}

function warmvast_admin_render_service_box( $post ) {
	$skey = warmvast_admin_service_key_for_post( $post );
	$d    = warmvast_service_detail( $skey );
	$get  = static fn( $field ) => get_post_meta( $post->ID, '_warmvast_' . $field, true );
	wp_nonce_field( 'warmvast_save_service', 'warmvast_service_nonce' );
	$symptoms_txt = $get( 'symptoms' ) ? $get( 'symptoms' ) : implode( "\n", $d['symptoms'] );
	$suitable_txt = $get( 'suitable' ) ? $get( 'suitable' ) : implode( "\n", $d['suitable'] );
	?>
	<p class="description">Leeg laten = de bestaande tekst blijft staan. "process" (de 4-stappen sectie) is bewust niet hier -- dat blijft code-beheerd, zie inc/service-content.php.</p>
	<table class="form-table" role="presentation">
		<tr><th><label>H1 / paginatitel</label></th><td><?php warmvast_admin_field_text( 'h1', $get( 'h1' ) ?: $d['h1'] ); ?></td></tr>
		<tr><th><label>Intro</label></th><td><?php warmvast_admin_field_textarea( 'intro', $get( 'intro' ) ?: $d['intro'], 3 ); ?></td></tr>
		<tr><th><label>Technische uitleg</label></th><td><?php warmvast_admin_field_textarea( 'technical', $get( 'technical' ) ?: $d['technical'], 4 ); ?></td></tr>
		<tr><th><label>Herkenbare signalen<br><span class="description">(één per regel)</span></label></th><td><?php warmvast_admin_field_textarea( 'symptoms', $symptoms_txt, 4 ); ?></td></tr>
		<tr><th><label>Geschikt wanneer<br><span class="description">(één per regel)</span></label></th><td><?php warmvast_admin_field_textarea( 'suitable', $suitable_txt, 4 ); ?></td></tr>
	</table>
	<h4>Veelgestelde vragen</h4>
	<?php
	$faqs = $get( 'faqs_q1' ) ? array(
		array(
			'q' => $get( 'faqs_q1' ),
			'a' => $get( 'faqs_a1' ),
		),
		array(
			'q' => $get( 'faqs_q2' ),
			'a' => $get( 'faqs_a2' ),
		),
	) : $d['faqs'];
	for ( $i = 0; $i < 2; $i++ ) :
		$row = $faqs[ $i ] ?? array(
			'q' => '',
			'a' => '',
		);
		?>
		<p><strong>Vraag <?php echo (int) $i + 1; ?></strong></p>
		<?php warmvast_admin_field_text( 'faqs_q' . ( $i + 1 ), $row['q'] ); ?>
		<p><?php warmvast_admin_field_textarea( 'faqs_a' . ( $i + 1 ), $row['a'], 2 ); ?></p>
	<?php endfor; ?>
	<?php
}

add_action(
	'save_post_page',
	function ( $post_id ) {
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

		$post = get_post( $post_id );

		if ( warmvast_admin_gemeente_key_for_post( $post ) && isset( $_POST['warmvast_gemeente_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['warmvast_gemeente_nonce'] ) ), 'warmvast_save_gemeente' ) ) {
			$fields = array( 'karakter', 'aandacht', 'gs_naam', 'gs_bedrag_max', 'gs_bedrag', 'gs_bedrag_laag', 'gs_voorwaarden', 'gs_looptijd', 'gs_bron_url', 'gs_gecontroleerd' );
			foreach ( $fields as $field ) {
				$raw = isset( $_POST[ $field ] ) ? wp_unslash( $_POST[ $field ] ) : '';
				$val = in_array( $field, array( 'karakter', 'aandacht', 'gs_voorwaarden' ), true )
					? sanitize_textarea_field( $raw )
					: ( 'gs_bron_url' === $field ? esc_url_raw( $raw ) : sanitize_text_field( $raw ) );
				if ( '' === trim( $val ) ) {
					delete_post_meta( $post_id, '_warmvast_' . $field ); // empty = fall back to the code default.
				} else {
					update_post_meta( $post_id, '_warmvast_' . $field, $val );
				}
			}
		}

		if ( warmvast_admin_service_key_for_post( $post ) && isset( $_POST['warmvast_service_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['warmvast_service_nonce'] ) ), 'warmvast_save_service' ) ) {
			$text_fields = array( 'h1', 'faqs_q1', 'faqs_q2' );
			$area_fields = array( 'intro', 'technical', 'symptoms', 'suitable', 'faqs_a1', 'faqs_a2' );
			foreach ( array_merge( $text_fields, $area_fields ) as $field ) {
				$raw = isset( $_POST[ $field ] ) ? wp_unslash( $_POST[ $field ] ) : '';
				$val = in_array( $field, $area_fields, true ) ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
				if ( '' === trim( $val ) ) {
					delete_post_meta( $post_id, '_warmvast_' . $field );
				} else {
					update_post_meta( $post_id, '_warmvast_' . $field, $val );
				}
			}
		}
	}
);
