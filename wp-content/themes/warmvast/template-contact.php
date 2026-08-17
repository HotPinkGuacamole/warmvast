<?php
/**
 * Template Name: Contact
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<header class="page-hero">
	<div class="container page-hero__inner">
		<nav class="breadcrumb" aria-label="Kruimelpad">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span aria-hidden="true">/</span><span>Contact</span>
		</nav>
		<p class="kicker">Contact</p>
		<h1 class="page-hero__title">Persoonlijk contact met Warmvast</h1>
		<p class="page-hero__sub">Liever direct de scan doen? Dat gaat het snelst. Wilt u ons spreken? Bel of mail: we reageren binnen 24 uur op werkdagen.</p>
	</div>
</header>

<section class="section section--surface">
	<div class="container grid grid--2" style="align-items:start;gap:clamp(1.5rem,4vw,3rem)">
		<div data-reveal>
			<div class="contact-cards">
				<a class="card contact-card" href="tel:<?php echo esc_attr( WARMVAST_PHONE_RAW ); ?>" data-track="phone_click">
					<span class="contact-card__icon"><?php warmvast_the_icon( 'phone' ); ?></span>
					<span><strong>Bel ons</strong><em><?php echo esc_html( WARMVAST_PHONE ); ?></em></span>
				</a>
				<a class="card contact-card" href="mailto:<?php echo esc_attr( WARMVAST_EMAIL ); ?>" data-track="email_click">
					<span class="contact-card__icon"><?php warmvast_the_icon( 'mail' ); ?></span>
					<span><strong>Mail ons</strong><em><?php echo esc_html( WARMVAST_EMAIL ); ?></em></span>
				</a>
				<?php if ( WARMVAST_WHATSAPP ) : ?>
					<a class="card contact-card" href="https://wa.me/<?php echo esc_attr( WARMVAST_WHATSAPP ); ?>" data-track="whatsapp_click" target="_blank" rel="noopener">
						<span class="contact-card__icon"><?php warmvast_the_icon( 'whatsapp' ); ?></span>
						<span><strong>WhatsApp ons</strong><em>Snel een vraag stellen</em></span>
					</a>
				<?php endif; ?>
				<div class="card contact-card">
					<span class="contact-card__icon"><?php warmvast_the_icon( 'clock' ); ?></span>
					<span><strong>Openingstijden</strong><em><?php echo esc_html( WARMVAST_HOURS ); ?></em></span>
				</div>
				<div class="card contact-card">
					<span class="contact-card__icon"><?php warmvast_the_icon( 'map' ); ?></span>
					<span><strong>Werkgebied</strong><em><?php echo esc_html( WARMVAST_REGION ); ?></em></span>
				</div>
			</div>
			<?php if ( trim( get_the_content() ) ) : ?>
				<div class="prose" style="margin-top:1.5rem"><?php the_content(); ?></div>
			<?php endif; ?>
		</div>

		<div data-reveal>
			<?php get_template_part( 'template-parts/woningscan' ); ?>
		</div>
	</div>
</section>
<?php
get_footer();
