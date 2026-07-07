<?php
/**
 * Template Name: Servicepagina
 *
 * Slug-driven service page. The page slug (spouwmuurisolatie, vloerisolatie,
 * glasisolatie-hr, dakisolatie) selects the matching service content. Same CRO
 * structure for all four; the isolatiescan is inlined with the measure preselected.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

while ( have_posts() ) :
	the_post();

	// Map the current page slug to a service key.
	$slug   = get_post_field( 'post_name', get_the_ID() );
	$key    = '';
	foreach ( warmvast_isde_rates() as $k => $rate ) {
		if ( $rate['slug'] === $slug ) {
			$key = $k;
			break;
		}
	}
	$detail = $key ? warmvast_service_detail( $key ) : null;

	if ( ! $detail ) :
		// Fallback: render the page body if the slug doesn't match a service.
		?>
		<header class="page-hero"><div class="container page-hero__inner"><h1 class="page-hero__title"><?php the_title(); ?></h1></div></header>
		<div class="container page-body prose"><?php the_content(); ?></div>
		<?php
	else :
		?>

		<!-- Service hero -->
		<header class="page-hero page-hero--service">
			<div class="container page-hero__inner">
				<nav class="breadcrumb" aria-label="Kruimelpad">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
					<span aria-hidden="true">/</span>
					<a href="<?php echo esc_url( home_url( '/isolatie/' ) ); ?>">Isolatie</a>
					<span aria-hidden="true">/</span>
					<span><?php echo esc_html( $detail['label'] ); ?></span>
				</nav>
				<div class="service-hero__grid">
					<div>
						<span class="service-hero__icon"><?php warmvast_the_icon( $detail['icon'] ); ?></span>
						<h1 class="page-hero__title"><?php echo esc_html( $detail['h1'] ); ?></h1>
						<p class="page-hero__sub"><?php echo esc_html( $detail['intro'] ); ?></p>
						<div class="service-hero__actions">
							<a class="btn btn--accent btn--lg" href="#warmvast-scan" data-track="cta_click">Check deze maatregel <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?></a>
							<?php warmvast_phone_link( 'service-hero__phone' ); ?>
						</div>
					</div>
					<aside class="service-hero__facts">
						<p class="kicker">ISDE 2026</p>
						<dl class="facts">
							<div><dt>Basisbedrag</dt><dd><?php echo esc_html( warmvast_rate( $detail['baseRate'] ) ); ?>/m²</dd></div>
							<div><dt>Bij 2+ maatregelen</dt><dd class="facts__hot"><?php echo esc_html( warmvast_rate( $detail['baseRate'] * 2 ) ); ?>/m²</dd></div>
							<div><dt>Minimaal</dt><dd><?php echo esc_html( $detail['minM2'] ); ?> m²</dd></div>
							<div><dt>Maximaal</dt><dd><?php echo esc_html( $detail['maxM2'] ); ?> m²</dd></div>
						</dl>
						<p class="facts__note">Indicatie onder voorbehoud van RVO-beoordeling.</p>
					</aside>
				</div>
			</div>
		</header>

		<!-- Symptomen -->
		<section class="section section--paper">
			<div class="container">
				<?php warmvast_section_header( 'Herkenbaar?', 'Wanneer speelt dit bij u?' ); ?>
				<div class="grid grid--2">
					<?php foreach ( $detail['symptoms'] as $s ) : ?>
						<div class="card problem-card" data-reveal>
							<span class="problem-card__icon"><?php warmvast_the_icon( 'check' ); ?></span>
							<p><?php echo esc_html( $s ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<!-- Technische uitleg + geschiktheid -->
		<section class="section section--surface">
			<div class="container grid grid--2" style="align-items:start;gap:clamp(1.5rem,4vw,3rem)">
				<div data-reveal>
					<?php warmvast_section_header( 'Techniek', 'Hoe werkt ' . strtolower( $detail['label'] ) . '?' ); ?>
					<p><?php echo esc_html( $detail['technical'] ); ?></p>
				</div>
				<div class="card" data-reveal>
					<h3>Is uw woning geschikt?</h3>
					<ul class="checklist" style="margin-top:1rem">
						<?php foreach ( $detail['suitable'] as $item ) : ?>
							<li><?php echo esc_html( $item ); ?></li>
						<?php endforeach; ?>
					</ul>
					<p class="facts__note">De technische opname geeft definitief uitsluitsel.</p>
				</div>
			</div>
		</section>

		<!-- ISDE tarieven -->
		<section class="section section--paper">
			<div class="container">
				<?php warmvast_section_header( 'Subsidie', 'ISDE-tarieven voor ' . strtolower( $detail['label'] ), 'De ISDE werkt met vaste bedragen per m². Bij twee of meer isolatiemaatregelen verdubbelt het tarief.' ); ?>
				<div class="tariff">
					<div class="tariff__cell">
						<span class="tariff__label">Basisbedrag (1 maatregel)</span>
						<span class="tariff__value"><?php echo esc_html( warmvast_rate( $detail['baseRate'] ) ); ?><small>/m²</small></span>
					</div>
					<div class="tariff__cell tariff__cell--hot">
						<span class="tariff__label">Verdubbeld (2+ maatregelen)</span>
						<span class="tariff__value"><?php echo esc_html( warmvast_rate( $detail['baseRate'] * 2 ) ); ?><small>/m²</small></span>
					</div>
					<div class="tariff__cell">
						<span class="tariff__label">Minimale oppervlakte</span>
						<span class="tariff__value"><?php echo esc_html( $detail['minM2'] ); ?><small>m²</small></span>
					</div>
					<div class="tariff__cell">
						<span class="tariff__label">Maximale oppervlakte</span>
						<span class="tariff__value"><?php echo esc_html( $detail['maxM2'] ); ?><small>m²</small></span>
					</div>
				</div>
				<p class="wv-disclaimer">Bedragen gebaseerd op de bekende ISDE-tarieven 2026. Definitieve subsidie hangt af van RVO-beoordeling, meldcodes, bewijsstukken en minimale oppervlaktes. Aan deze cijfers kunnen geen rechten worden ontleend.</p>
			</div>
		</section>

		<!-- Uitvoeringsproces -->
		<section class="section section--surface">
			<div class="container">
				<?php warmvast_section_header( 'Uitvoering', 'Van opname tot subsidiedossier' ); ?>
				<ol class="steps steps--4">
					<?php foreach ( $detail['process'] as $step ) : ?>
						<li data-reveal>
							<h3><?php echo esc_html( $step[0] ); ?></h3>
							<p><?php echo esc_html( $step[1] ); ?></p>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>

		<!-- Optionele editor-content -->
		<?php if ( trim( get_the_content() ) ) : ?>
			<section class="section section--paper">
				<div class="container prose"><?php the_content(); ?></div>
			</section>
		<?php endif; ?>

		<!-- Inline woningscan -->
		<section class="section section--ink">
			<div class="container">
				<?php warmvast_section_header( 'Direct berekenen', 'Wat levert isoleren úw woning op?', 'Vul uw adres in en zie direct de maatregelen, subsidie en besparing voor uw woning.', 'center' ); ?>
				<div class="scan-embed scan-embed--ws">
					<?php get_template_part( 'template-parts/woningscan' ); ?>
				</div>
			</div>
		</section>

		<!-- FAQ -->
		<?php if ( ! empty( $detail['faqs'] ) ) : ?>
			<section class="section section--surface">
				<div class="container">
					<?php warmvast_section_header( 'Veelgestelde vragen', 'Over ' . strtolower( $detail['label'] ) ); ?>
					<div class="faq">
						<?php foreach ( $detail['faqs'] as $i => $faq ) : ?>
							<details class="faq__item" <?php echo 0 === $i ? 'open' : ''; ?>>
								<summary><?php echo esc_html( $faq['q'] ); ?> <?php warmvast_the_icon( 'chevron' ); ?></summary>
								<div class="faq__answer"><p><?php echo esc_html( $faq['a'] ); ?></p></div>
							</details>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php warmvast_faq_schema( $detail['faqs'] ); ?>
		<?php endif; ?>

		<?php
	endif;
endwhile;

get_footer();
