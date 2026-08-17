<?php
/**
 * Footer.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$services = warmvast_services();
?>
</main><!-- #main -->

<section class="cta-band">
	<span class="cta-band__orb cta-band__orb--1" aria-hidden="true"></span>
	<span class="cta-band__orb cta-band__orb--2" aria-hidden="true"></span>
	<div class="container cta-band__inner">
		<div class="cta-band__copy" data-reveal>
			<p class="kicker kicker--light">Klaar om te beginnen?</p>
			<h2>Weet binnen 2 minuten wat isoleren u oplevert.</h2>
			<p class="cta-band__sub">Gratis isolatiescan met een directe ISDE-indicatie en besparing, op basis van uw eigen adres.</p>
			<ul class="cta-band__proof" aria-label="Wat de scan oplevert">
				<li><?php warmvast_the_icon( 'map', 'wv-icon--sm' ); ?> Adrescheck</li>
				<li><?php warmvast_the_icon( 'ruler', 'wv-icon--sm' ); ?> Geschatte m²</li>
				<li><?php warmvast_the_icon( 'euro', 'wv-icon--sm' ); ?> ISDE-indicatie</li>
			</ul>
		</div>

		<div class="cta-band__actions" data-reveal>
			<a class="btn btn--accent btn--lg btn--sheen cta-band__cta" href="<?php echo esc_url( home_url( '/gratis-isolatiescan/' ) ); ?>" data-track="cta_click">
				Start gratis isolatiescan <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?>
			</a>
			<?php warmvast_phone_link( 'cta-band__phone' ); ?>
			<?php if ( WARMVAST_WHATSAPP ) : ?>
				<?php warmvast_whatsapp_link( 'cta-band__phone' ); ?>
			<?php endif; ?>
		</div>
	</div>
</section>

<footer class="site-footer">
	<div class="container site-footer__grid">

		<div class="site-footer__brand">
			<img class="brand__logo brand__logo--footer" src="<?php echo warmvast_asset( '/assets/img/warmvast-logo-horizontal-white.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper escapes. ?>" width="730" height="212" alt="Warmvast Isolatie" loading="lazy" decoding="async">
			<p class="site-footer__tag">Wij houden de warmte in uw woning vast. Technische opname, helder m²-overzicht en een realistische ISDE-indicatie.</p>
			<ul class="site-footer__contact">
				<li><?php warmvast_phone_link(); ?></li>
				<?php if ( WARMVAST_WHATSAPP ) : ?>
					<li><?php warmvast_whatsapp_link(); ?></li>
				<?php endif; ?>
				<li><?php warmvast_email_link(); ?></li>
				<li><?php warmvast_the_icon( 'map' ); ?> <span><?php echo esc_html( WARMVAST_REGION ); ?></span></li>
				<li><?php warmvast_the_icon( 'clock' ); ?> <span><?php echo esc_html( WARMVAST_HOURS ); ?></span></li>
			</ul>
			<?php warmvast_the_keurmerken( 'site-footer__keurmerken' ); ?>
		</div>

		<nav class="site-footer__col" aria-label="Diensten">
			<h3 class="site-footer__title">Isolatie</h3>
			<ul>
				<?php foreach ( $services as $s ) : ?>
					<li><a href="<?php echo esc_url( $s['url'] ); ?>"><?php echo esc_html( $s['label'] ); ?></a></li>
				<?php endforeach; ?>
				<li><a href="<?php echo esc_url( home_url( '/isolatie/' ) ); ?>">Alle maatregelen</a></li>
			</ul>
		</nav>

		<nav class="site-footer__col" aria-label="Service">
			<h3 class="site-footer__title">Service</h3>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/gratis-isolatiescan/' ) ); ?>">Gratis isolatiescan</a></li>
				<li><a href="<?php echo esc_url( home_url( '/subsidie-service/' ) ); ?>">Subsidie service</a></li>
				<li><a href="<?php echo esc_url( home_url( '/gemeentes/' ) ); ?>">Gemeentes regio Zaandam</a></li>
				<li><a href="<?php echo esc_url( home_url( '/zakelijk/' ) ); ?>">Zakelijk &amp; VvE</a></li>
				<li><a href="<?php echo esc_url( home_url( '/ons-werk/' ) ); ?>">Ons werk</a></li>
				<li><a href="<?php echo esc_url( home_url( '/kwaliteit-en-garantie/' ) ); ?>">Kwaliteit &amp; garantie</a></li>
				<li><a href="<?php echo esc_url( home_url( '/kennisbank/' ) ); ?>">Kennisbank</a></li>
				<li><a href="<?php echo esc_url( home_url( '/over-warmvast/' ) ); ?>">Over ons</a></li>
				<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
			</ul>
		</nav>

		<nav class="site-footer__col" aria-label="Juridisch">
			<h3 class="site-footer__title">Juridisch</h3>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/privacyverklaring/' ) ); ?>">Privacyverklaring</a></li>
				<li><a href="<?php echo esc_url( home_url( '/algemene-voorwaarden/' ) ); ?>">Algemene voorwaarden</a></li>
			</ul>
			<div class="site-footer__badge">
				<?php warmvast_the_icon( 'shield' ); ?>
				<span>Indicaties onder voorbehoud van RVO-beoordeling.</span>
			</div>
		</nav>
	</div>

	<div class="site-footer__bar">
		<div class="container site-footer__bar-inner">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Warmvast. Alle rechten voorbehouden.</p>
			<p>ISDE-indicaties zijn geen garantie. RVO beoordeelt elke aanvraag.</p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
