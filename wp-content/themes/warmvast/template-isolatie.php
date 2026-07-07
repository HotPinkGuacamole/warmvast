<?php
/**
 * Template Name: Isolatie overzicht
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$services = warmvast_services();
?>
<header class="page-hero">
	<div class="container page-hero__inner">
		<nav class="breadcrumb" aria-label="Kruimelpad">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span aria-hidden="true">/</span><span>Isolatie</span>
		</nav>
		<p class="kicker">Isolatiemaatregelen</p>
		<h1 class="page-hero__title">Welke isolatie past bij uw woning?</h1>
		<p class="page-hero__sub">Warmvast kijkt naar uw woning als een systeem: waar lekt warmte weg, welke maatregel lost dat op en wat levert combineren op qua comfort én subsidie?</p>
		<div class="service-hero__actions">
			<a class="btn btn--accent btn--lg" href="<?php echo esc_url( home_url( '/gratis-isolatiescan/' ) ); ?>" data-track="cta_click">Start de keuzehulp <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?></a>
		</div>
	</div>
</header>

<section class="section section--surface">
	<div class="container">
		<div class="grid grid--2">
			<?php foreach ( $services as $key => $s ) : ?>
				<article class="card card--link service-card service-card--wide" data-reveal>
					<span class="service-card__icon"><?php warmvast_the_icon( $s['icon'] ); ?></span>
					<h2><?php echo esc_html( $s['label'] ); ?></h2>
					<p class="service-card__problem"><?php echo esc_html( $s['problem'] ); ?></p>
					<p class="service-card__solution"><?php echo esc_html( $s['solution'] ); ?></p>
					<span class="service-card__subsidy"><?php warmvast_the_icon( 'euro', 'wv-icon--sm' ); ?> Vanaf <?php echo esc_html( warmvast_rate( $s['baseRate'] ) ); ?>/m² · min. <?php echo esc_html( $s['minM2'] ); ?> m²</span>
					<div class="service-card__foot">
						<a class="btn btn--secondary" href="<?php echo esc_url( $s['url'] ); ?>">Bekijk <?php echo esc_html( strtolower( $s['label'] ) ); ?> <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?></a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section section--paper">
	<div class="container u-center">
		<?php warmvast_section_header( 'Niet zeker welke maatregel?', 'Laat de scan het uitrekenen', 'Vink uw situatie aan en zie direct welke combinatie het meeste oplevert — met verdubbeld ISDE-tarief.', 'center' ); ?>
		<?php warmvast_cta( 'Start gratis isolatiescan', 'accent', home_url( '/gratis-isolatiescan/' ) ); ?>
	</div>
</section>
<?php
get_footer();
