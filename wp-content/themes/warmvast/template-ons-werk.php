<?php
/**
 * Template Name: Ons werk
 *
 * Project gallery. Shows real projects once warmvast_projecten() is filled
 * in; until then an honest empty state instead of a fabricated gallery.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$projecten = warmvast_projecten();
$services  = warmvast_services();

/**
 * Illustrative examples only — NOT real completed projects. No address, date
 * or customer is claimed; each card is explicitly labelled "Voorbeeld" and
 * uses an icon instead of a photo so it can never be mistaken for a real
 * project photo. Real projects belong in warmvast_projecten() instead.
 */
$voorbeelden = array(
	array( 'wall', 'Spouwmuurisolatie', 'Injectie van spouwmuurisolatie bij een tussenwoning uit de jaren \'70.' ),
	array( 'roof', 'Dakisolatie', 'Dakisolatie tussen de sporen bij een woning met een hellend dak.' ),
	array( 'floor', 'Vloerisolatie', 'Vloerisolatie aangebracht vanuit de kruipruimte bij een vrijstaande woning.' ),
);
?>
<header class="page-hero">
	<div class="container page-hero__inner">
		<nav class="breadcrumb" aria-label="Kruimelpad">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span aria-hidden="true">/</span><span>Ons werk</span>
		</nav>
		<p class="kicker">Ons werk</p>
		<h1 class="page-hero__title">Isolatieprojecten door Warmvast</h1>
		<p class="page-hero__sub">Een kijkje bij afgeronde projecten: welke maatregelen, in welk type woning, met welk resultaat.</p>
	</div>
</header>

<?php if ( empty( $projecten ) ) : ?>
	<section class="section section--surface">
		<div class="container u-center" style="max-width:640px;margin-inline:auto">
			<span class="service-hero__icon" style="margin-inline:auto"><?php warmvast_the_icon( 'image' ); ?></span>
			<h2>Deze pagina groeit met elk project</h2>
			<p>We bouwen "Ons werk" op met échte foto's van afgeronde projecten — geen stockfoto's van andermans werk. Hieronder ziet u, ter illustratie, het type werk dat we uitvoeren; het zijn geen echte projecten. Zodra de eerste eigen projecten zijn vastgelegd, verschijnen ze hier met plaats, maatregel en resultaat.</p>
			<p class="wv-disclaimer">Wilt u alvast weten wat isoleren bij úw woning oplevert? De gratis scan geeft direct een indicatie op basis van uw eigen adres.</p>
			<div style="margin-top:1.5rem">
				<?php warmvast_cta( 'Start gratis isolatiescan', 'accent', home_url( '/gratis-isolatiescan/' ) ); ?>
			</div>
		</div>
	</section>
	<section class="section section--paper">
		<div class="container">
			<?php warmvast_section_header( 'Ter illustratie', 'Het soort werk dat u kunt verwachten' ); ?>
			<div class="grid grid--3">
				<?php foreach ( $voorbeelden as $v ) : ?>
					<article class="card card--post" data-reveal>
						<div class="card--post__media card--post__media--icon">
							<span class="project-card__badge">Voorbeeld</span>
							<?php warmvast_the_icon( $v[0] ); ?>
						</div>
						<div class="card--post__body">
							<h2 class="card--post__title"><?php echo esc_html( $v[1] ); ?></h2>
							<p><?php echo esc_html( $v[2] ); ?></p>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php else : ?>
	<section class="section section--surface">
		<div class="container">
			<div class="grid grid--3">
				<?php foreach ( $projecten as $p ) : ?>
					<article class="card card--post" data-reveal>
						<?php if ( ! empty( $p['foto'] ) ) : ?>
							<div class="card--post__media"><img src="<?php echo esc_url( $p['foto'] ); ?>" alt="<?php echo esc_attr( $p['titel'] ); ?>" loading="lazy"></div>
						<?php endif; ?>
						<div class="card--post__body">
							<h2 class="card--post__title"><?php echo esc_html( $p['titel'] ); ?></h2>
							<p>
								<?php warmvast_the_icon( 'map', 'wv-icon--sm' ); ?> <?php echo esc_html( $p['plaats'] ); ?>
								<?php if ( ! empty( $p['datum'] ) ) : ?>
									· <?php echo esc_html( $p['datum'] ); ?>
								<?php endif; ?>
							</p>
							<?php if ( ! empty( $p['maatregelen'] ) ) : ?>
								<p class="project-card__tags">
									<?php foreach ( $p['maatregelen'] as $mkey ) : ?>
										<?php if ( isset( $services[ $mkey ] ) ) : ?>
											<span class="project-card__tag"><?php echo esc_html( $services[ $mkey ]['label'] ); ?></span>
										<?php endif; ?>
									<?php endforeach; ?>
								</p>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<section class="section section--paper">
	<div class="container u-center">
		<?php warmvast_section_header( '', 'Benieuwd wat isoleren úw woning oplevert?', '', 'center' ); ?>
		<?php warmvast_cta( 'Start gratis isolatiescan', 'primary', home_url( '/gratis-isolatiescan/' ) ); ?>
	</div>
</section>
<?php
get_footer();
