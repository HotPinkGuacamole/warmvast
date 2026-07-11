<?php
/**
 * Template Name: Gratis isolatiescan (landingpage)
 *
 * Dedicated, distraction-light landing page built around the wide scan.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$trust = array(
	array( 'clock', 'Binnen 24 uur reactie' ),
	array( 'ruler', 'Technische opname vóór uitvoering' ),
	array( 'camera', 'Fotobewijs voor uw subsidie' ),
	array( 'map', 'Werkzaam in heel Nederland' ),
);
?>
<section class="section section--ink scan-landing">
	<div class="scan-landing__bg" aria-hidden="true">
		<span class="scan-landing__heat scan-landing__heat--roof"></span>
		<span class="scan-landing__heat scan-landing__heat--floor"></span>
		<span class="scan-landing__beam scan-landing__beam--1"></span>
		<span class="scan-landing__beam scan-landing__beam--2"></span>
	</div>
	<div class="container">
		<div class="scan-landing__head">
			<span class="hero__eyebrow"><?php warmvast_the_icon( 'thermo', 'wv-icon--sm' ); ?> Gratis &amp; vrijblijvend</span>
			<h1>Bereken in 2 minuten wat isoleren u oplevert</h1>
			<p>Vul uw woninggegevens in en zie direct een ISDE-indicatie. Geen verplichtingen. Pas op het laatste moment vragen we uw contactgegevens.</p>
			<ul class="scan-landing__trust">
				<?php foreach ( $trust as $t ) : ?>
					<li><?php warmvast_the_icon( $t[0], 'wv-icon--sm' ); ?> <?php echo esc_html( $t[1] ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="scan-embed scan-embed--ws">
			<?php get_template_part( 'template-parts/woningscan' ); ?>
		</div>
	</div>
</section>

<section class="section section--surface">
	<div class="container">
		<?php warmvast_section_header( 'Zo werkt het', 'Na uw scan', 'De scan is de eerste stap. Daarna neemt Warmvast het van u over.', 'center' ); ?>
		<ol class="steps steps--4">
			<li data-reveal><h3>Wij bellen u</h3><p>Binnen 24 uur nemen we telefonisch contact op om uw situatie door te nemen.</p></li>
			<li data-reveal><h3>Technische opname</h3><p>Een vakman meet de exacte m² en beoordeelt uw woning ter plaatse.</p></li>
			<li data-reveal><h3>Heldere offerte</h3><p>U ontvangt een offerte met exacte oppervlaktes en maatregelen.</p></li>
			<li data-reveal><h3>Subsidiedossier</h3><p>Wij leggen meldcodes en foto’s vast voor uw ISDE-aanvraag.</p></li>
		</ol>
	</div>
</section>
<?php
get_footer();
