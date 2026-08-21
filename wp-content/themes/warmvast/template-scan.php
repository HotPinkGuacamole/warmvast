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

// A service page can link here with ?maatregel=dak to preselect that measure,
// e.g. when its own dark inline-scan block was replaced by a plain link.
global $warmvast_scan_preselect;
$maatregel = isset( $_GET['maatregel'] ) ? sanitize_key( wp_unslash( $_GET['maatregel'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only preselect, no state change.
$warmvast_scan_preselect = isset( warmvast_isde_rates()[ $maatregel ] ) ? $maatregel : '';

// Identical USP set to the homepage hero's floating card -- same component,
// same copy, so this landing page reads as the same hero, not a lookalike.
$usps = array(
	array( 'ruler', 'Technische opname', 'vóór elke uitvoering' ),
	array( 'euro', 'Helder m²-overzicht', 'u weet wat u betaalt' ),
	array( 'doc', 'Subsidiedossier geregeld', 'meldcodes &amp; fotobewijs' ),
	array( 'clock', 'Reactie binnen 24 uur', 'werkzaam in ' . WARMVAST_REGION ),
);
?>
<section class="hero">
	<span class="hero__orb hero__orb--1" aria-hidden="true"></span>
	<span class="hero__orb hero__orb--2" aria-hidden="true"></span>
	<div class="hero__thermal" aria-hidden="true"></div>
	<div class="container hero__inner">
		<?php // Visually the scan card is the whole hero now -- no competing
		// copy column -- but the page still needs a real h1 for SEO/a11y,
		// just not a visible one duplicating what the card's own heading
		// ("Doe de isolatiescan voor uw woning") already says on screen. ?>
		<h1 class="screen-reader-text">Bereken in 2 minuten wat isoleren u oplevert</h1>

		<div class="hero__scan">
			<?php get_template_part( 'template-parts/woningscan' ); ?>
		</div>
	</div>

	<!-- floating USP overview: identical to the homepage hero's, so pricing
	     trust signals sit in the same place a returning visitor already knows -->
	<section class="usp-bar" aria-label="Waarom Warmvast">
		<span class="usp-bar__notch usp-bar__notch--left" aria-hidden="true"></span>
		<span class="usp-bar__notch usp-bar__notch--right" aria-hidden="true"></span>
		<div class="container">
			<div class="usp-bar__grid">
				<?php foreach ( $usps as $u ) : ?>
					<div class="usp">
						<span class="usp__icon"><?php warmvast_the_icon( $u[0] ); ?></span>
						<span class="usp__text"><strong><?php echo esc_html( $u[1] ); ?></strong><em><?php echo wp_kses_post( $u[2] ); ?></em></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
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

	<!-- Still deciding? Same bordered-card component as the homepage's
	     trust-facts strip, reused for real links instead of a plain list --
	     keeps someone not ready to fill in an address yet from hitting a
	     dead end right after "Zo werkt het". -->
	<section class="section section--paper">
		<div class="container">
			<?php warmvast_section_header( '', 'Nog niet zover? Lees eerst verder', '', 'center' ); ?>
			<div class="trust-facts" data-reveal>
				<a class="trust-facts__item" href="<?php echo esc_url( home_url( '/isolatie/' ) ); ?>">
					<span class="trust-facts__icon"><?php warmvast_the_icon( 'wall' ); ?></span>
					<span><strong>Welke isolatie past bij uw woning?</strong><em>Spouw, vloer, glas &amp; dak vergeleken</em></span>
				</a>
				<a class="trust-facts__item" href="<?php echo esc_url( home_url( '/subsidie-service/' ) ); ?>">
					<span class="trust-facts__icon"><?php warmvast_the_icon( 'euro' ); ?></span>
					<span><strong>ISDE-subsidie in 2026</strong><em>Tarieven en de verdubbelingsregel</em></span>
				</a>
				<a class="trust-facts__item" href="<?php echo esc_url( home_url( '/kennisbank/' ) ); ?>">
					<span class="trust-facts__icon"><?php warmvast_the_icon( 'doc' ); ?></span>
					<span><strong>Kennisbank</strong><em>Uitleg over subsidie en isolatie</em></span>
				</a>
				<a class="trust-facts__item" href="<?php echo esc_url( home_url( '/kwaliteit-en-garantie/' ) ); ?>">
					<span class="trust-facts__icon"><?php warmvast_the_icon( 'award' ); ?></span>
					<span><strong>Kwaliteit &amp; garantie</strong><em>Hoe wij kwaliteit waarborgen</em></span>
				</a>
			</div>
		</div>
	</section>
<?php
get_footer();
