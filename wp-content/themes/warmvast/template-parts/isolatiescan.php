<?php
/**
 * The isolatiescan: 4-step form + live ISDE calculator + Formspree AJAX.
 *
 * Globals (set by the shortcode or before get_template_part):
 *   $warmvast_scan_preselect  string  measure key to pre-check (spouw|vloer|glas|dak)
 *   $warmvast_scan_layout     string  'card' (default) | 'wide'
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $warmvast_scan_preselect, $warmvast_scan_layout;
$preselect = $warmvast_scan_preselect ? $warmvast_scan_preselect : '';
$layout    = ( 'wide' === $warmvast_scan_layout ) ? 'wide' : 'card';
$rates     = warmvast_isde_rates();

// Unique id so multiple scans on one page don't collide (kept simple: single instance expected per view).
$endpoint = WARMVAST_FORMSPREE;
?>
<section class="wv-scan wv-scan--<?php echo esc_attr( $layout ); ?>" id="warmvast-scan"
	data-endpoint="<?php echo esc_url( $endpoint ); ?>"
	data-preselect="<?php echo esc_attr( $preselect ); ?>">

	<header class="wv-scan__header">
		<p class="kicker">Gratis isolatiescan</p>
		<h2>Bereken uw isolatie- en ISDE-voordeel</h2>
		<p>Vul uw woninggegevens in en zie direct een subsidie-indicatie. Geen verplichtingen.</p>
	</header>

	<div class="wv-scan__grid">
		<form id="wvScanForm" class="wv-form" novalidate>
			<input type="hidden" name="bron" value="Warmvast isolatiescan">
			<input type="hidden" name="subsidie_totaal" id="subsidieTotaalInput">
			<input type="hidden" name="verdubbeld_tarief" id="verdubbeldTariefInput">
			<input type="hidden" name="subsidie_specificatie" id="subsidieSpecInput">
			<input type="hidden" name="_subject" value="Nieuwe Warmvast isolatiescan">

			<!-- honeypot -->
			<div class="wv-honeypot" aria-hidden="true">
				<label>Laat dit veld leeg
					<input type="text" name="_gotcha" tabindex="-1" autocomplete="off">
				</label>
			</div>

			<div class="wv-progress" aria-hidden="true">
				<span data-dot="1"></span>
				<span data-dot="2"></span>
				<span data-dot="3"></span>
				<span data-dot="4"></span>
			</div>

			<fieldset class="wv-step" data-step="1">
				<legend>Wat voor woning heeft u?</legend>
				<?php
				$woningtypes = array( 'Rijwoning', 'Hoekwoning', 'Twee-onder-een-kap', 'Vrijstaand', 'Appartement' );
				foreach ( $woningtypes as $i => $type ) :
					?>
					<label>
						<input type="radio" name="woningtype" value="<?php echo esc_attr( $type ); ?>" <?php echo 0 === $i ? 'required' : ''; ?>>
						<?php echo esc_html( $type ); ?>
					</label>
				<?php endforeach; ?>
				<label style="margin-top:.4rem">
					Bouwjaar (optioneel)
					<input type="text" name="bouwjaar" inputmode="numeric" placeholder="Bijv. 1975" autocomplete="off">
				</label>
			</fieldset>

			<fieldset class="wv-step" data-step="2">
				<legend>Welke isolatie wilt u laten beoordelen?</legend>

				<label>
					Huidige spouwmuur
					<select name="huidige_spouw">
						<option>Onbekend</option>
						<option>Niet geïsoleerd</option>
						<option>Wel geïsoleerd</option>
					</select>
				</label>

				<label>
					Huidige vloer
					<select name="huidige_vloer">
						<option>Onbekend</option>
						<option>Niet geïsoleerd</option>
						<option>Wel geïsoleerd</option>
					</select>
				</label>

				<label>
					Huidig glas
					<select name="huidige_glas">
						<option>Onbekend</option>
						<option>Enkel glas</option>
						<option>Oud dubbel glas</option>
						<option>HR++ of beter</option>
					</select>
				</label>

				<span class="wv-checks-label">Welke maatregelen wilt u overwegen?</span>
				<div class="wv-checks">
					<?php foreach ( $rates as $key => $rate ) : ?>
						<label>
							<input type="checkbox" name="maatregelen" value="<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( $rate['label'] ); ?>
						</label>
					<?php endforeach; ?>
				</div>
			</fieldset>

			<fieldset class="wv-step" data-step="3">
				<legend>Geschatte oppervlaktes</legend>
				<p>Een schatting is genoeg. Warmvast controleert de m² bij de technische opname.</p>
				<?php foreach ( $rates as $key => $rate ) : ?>
					<label data-area="<?php echo esc_attr( $key ); ?>">
						<?php echo esc_html( $rate['label'] ); ?> in m²
						<input type="number" name="<?php echo esc_attr( $rate['field'] ); ?>" min="0" step="<?php echo 'glas' === $key ? '0.5' : '1'; ?>" inputmode="decimal" placeholder="Bijv. <?php echo (int) ( $rate['minM2'] * 3 ); ?>">
					</label>
				<?php endforeach; ?>
			</fieldset>

			<fieldset class="wv-step" data-step="4">
				<legend>Waar mogen we de scan naartoe sturen?</legend>
				<label>Naam <input type="text" name="naam" autocomplete="name" required></label>
				<label>E-mail <input type="email" name="email" autocomplete="email" required></label>
				<label>Telefoon <input type="tel" name="telefoon" autocomplete="tel" required></label>
				<div class="grid grid--2" style="gap:.8rem">
					<label>Postcode <input type="text" name="postcode" autocomplete="postal-code" placeholder="1234 AB" required></label>
					<label>Huisnummer <input type="text" name="huisnummer" autocomplete="address-line2" required></label>
				</div>
				<label>Opmerking (optioneel) <textarea name="opmerking" rows="3" placeholder="Bijzonderheden over uw woning of situatie"></textarea></label>
				<label class="wv-consent">
					<input type="checkbox" name="privacy_akkoord" value="ja" required>
					<span>Warmvast mag contact met mij opnemen over mijn isolatiescan. Zie de <a href="<?php echo esc_url( home_url( '/privacyverklaring/' ) ); ?>">privacyverklaring</a>.</span>
				</label>
			</fieldset>

			<p id="wvFormError" class="wv-error" role="alert" aria-live="assertive"></p>

			<div class="wv-actions">
				<button type="button" class="btn btn--secondary" id="wvPrev" hidden>Vorige</button>
				<button type="button" class="btn btn--primary" id="wvNext">Volgende <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?></button>
				<button type="submit" class="btn btn--accent" id="wvSubmit" hidden>Verstuur mijn scan <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?></button>
			</div>
		</form>

		<aside class="wv-result" aria-live="polite">
			<p class="wv-kicker">Live ISDE-indicatie</p>
			<strong id="wvTotal" class="wv-result__total"><?php echo esc_html( warmvast_euro( 0 ) ); ?></strong>
			<p id="wvMode" class="wv-result__mode">Selecteer minimaal één maatregel om uw indicatie te zien.</p>
			<span id="wvBadge" class="wv-badge"><?php warmvast_the_icon( 'spark', 'wv-icon--sm' ); ?> Tarief verdubbeld</span>
			<ul id="wvEmpty" class="wv-result__empty">
				<li><?php warmvast_the_icon( 'check', 'wv-icon--sm' ); ?> Directe ISDE-indicatie per maatregel</li>
				<li><?php warmvast_the_icon( 'check', 'wv-icon--sm' ); ?> Zie het tarief verdubbelen bij 2+ maatregelen</li>
				<li><?php warmvast_the_icon( 'check', 'wv-icon--sm' ); ?> Geen verplichtingen, reactie binnen 24 uur</li>
			</ul>
			<ul id="wvBreakdown" class="wv-breakdown"></ul>
			<p class="wv-result__small">Indicatie op basis van bekende ISDE-tarieven 2026. Definitieve subsidie hangt af van RVO-beoordeling, meldcodes, bewijsstukken en minimale oppervlaktes. Aan deze berekening kunnen geen rechten worden ontleend.</p>
		</aside>
	</div>

	<div id="wvSuccess" class="wv-success" hidden>
		<div class="wv-success__mark"><?php warmvast_the_icon( 'check' ); ?></div>
		<h3>Uw isolatiescan is verzonden.</h3>
		<p>Warmvast neemt binnen 24 uur contact met u op om uw situatie door te nemen.</p>
	</div>

	<!-- sticky mobile summary bar (populated by JS) -->
	<div class="wv-mobilebar" id="wvMobileBar" aria-hidden="true">
		<div>
			<span class="wv-mobilebar__label">ISDE-indicatie</span>
			<span class="wv-mobilebar__amount" id="wvMobileAmount"><?php echo esc_html( warmvast_euro( 0 ) ); ?></span>
		</div>
		<a href="#warmvast-scan" class="btn btn--accent btn--sm" id="wvMobileCta">Verder</a>
	</div>
</section>
