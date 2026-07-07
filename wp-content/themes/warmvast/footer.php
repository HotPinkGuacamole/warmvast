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
	<div class="container cta-band__inner">
		<div>
			<p class="kicker kicker--light">Klaar om te beginnen?</p>
			<h2>Weet binnen 2 minuten wat isoleren u oplevert.</h2>
			<p class="cta-band__sub">Gratis isolatiescan met directe ISDE-indicatie. Reactie binnen 24 uur. Heel Nederland.</p>
		</div>
		<div class="cta-band__actions">
			<a class="btn btn--accent btn--lg" href="<?php echo esc_url( home_url( '/gratis-isolatiescan/' ) ); ?>" data-track="cta_click">
				Start gratis isolatiescan <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?>
			</a>
			<?php warmvast_phone_link( 'cta-band__phone' ); ?>
		</div>
	</div>
</section>

<footer class="site-footer">
	<div class="container site-footer__grid">

		<div class="site-footer__brand">
			<span class="brand brand--footer">
				<span class="brand__mark" aria-hidden="true">
					<svg viewBox="0 0 32 32" width="28" height="28" fill="none" aria-hidden="true">
						<rect x="1.5" y="1.5" width="29" height="29" rx="7" fill="#fff"/>
						<path d="M8 22 L16 9 L24 22" stroke="#0b6b5b" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M11.5 22 L16 14.5 L20.5 22" stroke="#fed03d" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</span>
				<span class="brand__word">Warm<span class="brand__word-alt">vast</span></span>
			</span>
			<p class="site-footer__tag">Wij houden de warmte in uw woning vast. Technische opname, helder m²-overzicht en een realistische ISDE-indicatie.</p>
			<ul class="site-footer__contact">
				<li><?php warmvast_phone_link(); ?></li>
				<li><?php warmvast_email_link(); ?></li>
				<li><?php warmvast_the_icon( 'map' ); ?> <span><?php echo esc_html( WARMVAST_REGION ); ?></span></li>
				<li><?php warmvast_the_icon( 'clock' ); ?> <span><?php echo esc_html( WARMVAST_HOURS ); ?></span></li>
			</ul>
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
