<?php
/**
 * 404 — page not found.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$shortcuts = array(
	array( 'thermo', 'Gratis isolatiescan', 'Bereken direct uw ISDE-indicatie voor uw eigen adres.', home_url( '/gratis-isolatiescan/' ) ),
	array( 'wall', 'Isolatie overzicht', 'Spouw, vloer, glas en dak: welke maatregel past bij uw woning?', home_url( '/isolatie/' ) ),
	array( 'doc', 'Kennisbank', 'Uitleg over ISDE-subsidie, m²-berekening en uitvoering.', home_url( '/kennisbank/' ) ),
	array( 'phone', 'Contact', 'Liever direct iemand spreken? Bel of mail Warmvast.', home_url( '/contact/' ) ),
);
?>
<header class="page-hero">
	<div class="container page-hero__inner">
		<p class="kicker">Pagina niet gevonden</p>
		<h1 class="page-hero__title">Deze pagina bestaat niet (meer)</h1>
		<p class="page-hero__sub">De link klopt niet of de pagina is verplaatst. Hieronder komt u snel weer verder.</p>
	</div>
</header>

<section class="section section--surface">
	<div class="container">
		<div class="grid grid--2">
			<?php foreach ( $shortcuts as $s ) : ?>
				<a class="card card--link service-card" href="<?php echo esc_url( $s[3] ); ?>" data-reveal>
					<span class="service-card__icon"><?php warmvast_the_icon( $s[0] ); ?></span>
					<h2><?php echo esc_html( $s[1] ); ?></h2>
					<p class="service-card__solution"><?php echo esc_html( $s[2] ); ?></p>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php
get_footer();
