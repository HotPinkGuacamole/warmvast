<?php
/**
 * Woningscan module — address-driven scan.
 *
 * Stages (toggled by JS): address -> result -> subsidie -> lead -> success.
 * Self-contained; safe to place once per page. Guards on #warmvast-woningscan.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$rates = warmvast_isde_rates();
?>
<section class="ws" id="warmvast-woningscan" data-endpoint="<?php echo esc_url( WARMVAST_FORMSPREE ); ?>">

	<!-- STAGE 1: address -->
	<div class="ws-stage ws-stage--address" data-stage="address">
		<header class="ws-head">
			<p class="kicker">Gratis isolatiescan</p>
			<h2>Doe de isolatiescan voor uw woning</h2>
			<p>Vul uw adres in en zie direct welke isolatie mogelijk is, met een ISDE-indicatie op maat.</p>
		</header>
		<form class="ws-address" id="wsAddressForm" novalidate>
			<div class="ws-address__fields">
				<label>Postcode <input type="text" name="postcode" id="wsPostcode" inputmode="text" autocomplete="postal-code" placeholder="1234 AB" required></label>
				<label>Huisnr. <input type="text" name="huisnummer" id="wsHuisnummer" inputmode="numeric" autocomplete="address-line2" placeholder="12" required></label>
				<label>Toev. <input type="text" name="toevoeging" id="wsToevoeging" placeholder="A" autocomplete="off"></label>
			</div>
			<button type="submit" class="btn btn--accent btn--lg ws-address__btn" id="wsScanBtn">
				Scan uw woning <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?>
			</button>
			<p class="ws-error" id="wsAddressError" role="alert" aria-live="assertive"></p>
			<p class="ws-privacy">Gratis en vrijblijvend. We gebruiken uw adres alleen voor deze scan.</p>
		</form>
	</div>

	<!-- Loading -->
	<div class="ws-loading" id="wsLoading" hidden>
		<span class="ws-spinner" aria-hidden="true"></span>
		<p>We halen de gegevens van uw woning op…</p>
	</div>

	<!-- STAGE 2: result -->
	<div class="ws-stage ws-stage--result" data-stage="result" hidden>
		<button type="button" class="ws-back" data-ws-back="address"><?php warmvast_the_icon( 'chevron', 'wv-icon--sm' ); ?> Ander adres</button>
		<header class="ws-head">
			<p class="kicker">Resultaat</p>
			<h2 id="wsResultTitle">Resultaat voor uw woning</h2>
			<p class="ws-verdict" id="wsVerdict"></p>
		</header>

		<div class="ws-visuals">
			<div class="ws-house" id="wsHouse" aria-label="Schematische weergave van uw woning"></div>
			<div class="ws-aerial"><img id="wsAerial" alt="Luchtfoto van uw woning" loading="lazy"></div>
		</div>

		<div class="ws-details">
			<h3>Woningdetails <span class="ws-hint">(indicatie — bij te stellen)</span></h3>
			<div class="ws-surfaces">
				<label>Vloer <span class="ws-suffix"><input type="number" id="wsVloer" min="0" step="1"> m²</span></label>
				<label>Dak <span class="ws-suffix"><input type="number" id="wsDak" min="0" step="1"> m²</span></label>
				<label>Buitenmuur <span class="ws-suffix"><input type="number" id="wsSpouw" min="0" step="1"> m²</span></label>
			</div>
			<p class="ws-meta" id="wsMeta"></p>
		</div>

		<div class="ws-label">
			<h3>Energielabel <span class="ws-hint">(indicatie o.b.v. bouwjaar)</span></h3>
			<div class="ws-label__scale" id="wsLabelScale" aria-hidden="true"></div>
		</div>

		<button type="button" class="btn btn--primary btn--lg ws-full" id="wsToSubsidie">
			Bekijk uw subsidie <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?>
		</button>
	</div>

	<!-- STAGE 3: subsidie -->
	<div class="ws-stage ws-stage--subsidie" data-stage="subsidie" hidden>
		<button type="button" class="ws-back" data-ws-back="result"><?php warmvast_the_icon( 'chevron', 'wv-icon--sm' ); ?> Terug</button>
		<header class="ws-head">
			<p class="kicker">Subsidie &amp; besparing</p>
			<h2>Uw isolatievoordeel</h2>
			<p>De ISDE-subsidie verdubbelt het bedrag per m² bij twee of meer maatregelen. Vink aan wat u wilt laten uitvoeren.</p>
		</header>

		<ul class="ws-measures" id="wsMeasures">
			<?php
			$order = array( 'dak', 'spouw', 'vloer' );
			foreach ( $order as $key ) :
				$r = $rates[ $key ];
				?>
				<li>
					<label class="ws-measure">
						<input type="checkbox" name="ws_measure" value="<?php echo esc_attr( $key ); ?>" checked>
						<span class="ws-measure__name"><?php warmvast_the_icon( 'check', 'wv-icon--sm' ); ?> <?php echo esc_html( $r['label'] ); ?></span>
						<span class="ws-measure__m2" data-m2="<?php echo esc_attr( $key ); ?>">– m²</span>
					</label>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="ws-figures">
			<div class="ws-figure ws-figure--subsidie">
				<span class="ws-figure__label">Geschatte ISDE-subsidie</span>
				<strong class="ws-figure__val" id="wsSubsidie"><?php echo esc_html( warmvast_euro( 0 ) ); ?></strong>
				<span class="ws-badge" id="wsDoubleBadge"><?php warmvast_the_icon( 'spark', 'wv-icon--sm' ); ?> Verdubbeld</span>
			</div>
			<div class="ws-figure ws-figure--besparing">
				<span class="ws-figure__label">Geschatte besparing / jaar</span>
				<strong class="ws-figure__val" id="wsBesparing"><?php echo esc_html( warmvast_euro( 0 ) ); ?></strong>
			</div>
		</div>
		<p class="ws-disclaimer">Indicatie op basis van ISDE-tarieven 2026 en uw geschatte m². Definitieve subsidie en besparing hangen af van de opname en RVO-beoordeling. Aan deze berekening kunnen geen rechten worden ontleend.</p>

		<button type="button" class="btn btn--accent btn--lg ws-full" id="wsToLead">
			Gratis adviesgesprek aanvragen <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?>
		</button>
	</div>

	<!-- STAGE 4: lead -->
	<div class="ws-stage ws-stage--lead" data-stage="lead" hidden>
		<button type="button" class="ws-back" data-ws-back="subsidie"><?php warmvast_the_icon( 'chevron', 'wv-icon--sm' ); ?> Terug</button>
		<header class="ws-head">
			<p class="kicker">Bijna klaar</p>
			<h2>Uw gratis adviesgesprek</h2>
			<p id="wsLeadSummary">We nemen binnen 24 uur contact met u op.</p>
		</header>
		<form class="ws-lead" id="wsLeadForm" novalidate>
			<label>Naam <input type="text" name="naam" autocomplete="name" required></label>
			<label>E-mail <input type="email" name="email" autocomplete="email" required></label>
			<label>Telefoon <input type="tel" name="telefoon" autocomplete="tel" required></label>
			<label>Opmerking (optioneel) <textarea name="opmerking" rows="2"></textarea></label>
			<div class="ws-honeypot" aria-hidden="true"><input type="text" name="_gotcha" tabindex="-1" autocomplete="off"></div>
			<label class="wv-consent"><input type="checkbox" name="privacy_akkoord" value="ja" required> <span>Warmvast mag contact met mij opnemen over mijn isolatiescan. Zie de <a href="<?php echo esc_url( home_url( '/privacyverklaring/' ) ); ?>">privacyverklaring</a>.</span></label>
			<p class="ws-error" id="wsLeadError" role="alert" aria-live="assertive"></p>
			<button type="submit" class="btn btn--accent btn--lg ws-full" id="wsSubmitLead">Verstuur mijn aanvraag <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?></button>
		</form>
	</div>

	<!-- Success -->
	<div class="ws-stage ws-success" data-stage="success" hidden>
		<div class="wv-success__mark"><?php warmvast_the_icon( 'check' ); ?></div>
		<h2>Uw aanvraag is verstuurd.</h2>
		<p>Warmvast neemt binnen 24 uur contact met u op om uw isolatiescan en subsidie door te nemen.</p>
	</div>

	<!-- hidden Formspree-bound fields, populated on submit -->
	<div hidden aria-hidden="true">
		<span id="wsData"></span>
	</div>
</section>
