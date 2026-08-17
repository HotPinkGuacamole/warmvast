<?php
/**
 * Template Name: Gemeentes overzicht
 *
 * Landingpagina voor de 7 gemeenten van regio Zaandam (Zaanstreek-Waterland). Elke kaart linkt door naar de
 * bijbehorende gemeentepagina (Template Name: Gemeente subsidiepagina).
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$gemeenten = warmvast_zaanstreek_gemeenten();
?>
<header class="page-hero">
	<div class="container page-hero__inner">
		<nav class="breadcrumb" aria-label="Kruimelpad">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span aria-hidden="true">/</span><span>Gemeentes</span>
		</nav>
		<p class="kicker">ISDE-subsidie in regio Zaandam</p>
		<h1 class="page-hero__title">Isolatie en ISDE-subsidie per gemeente in regio Zaandam</h1>
		<p class="page-hero__sub">De ISDE-subsidie is een landelijke regeling: het tarief per m² is overal in Nederland gelijk. Wat wél per gemeente verschilt, is het karakter van de woningvoorraad — en dus welke isolatiemaatregel bij úw woning het meeste oplevert. Kies uw gemeente voor lokale context, kernen en een directe subsidie-indicatie.</p>
	</div>
</header>

<section class="section section--tight section--paper">
	<div class="container">
		<div class="trust-stats">
			<div class="trust-stats__item"><span class="trust-stats__value"><?php echo count( $gemeenten ); ?></span><span class="trust-stats__label">Gemeenten in regio Zaandam</span></div>
			<div class="trust-stats__item"><span class="trust-stats__value">1</span><span class="trust-stats__label">Landelijk ISDE-tarief</span></div>
			<div class="trust-stats__item"><span class="trust-stats__value">4</span><span class="trust-stats__label">Isolatiemaatregelen</span></div>
		</div>
	</div>
</section>

<section class="section section--surface">
	<div class="container">
		<div class="grid grid--3">
			<?php foreach ( $gemeenten as $key => $g ) : ?>
				<article class="card card--link service-card" data-reveal>
					<span class="service-card__icon"><?php warmvast_the_icon( 'map' ); ?></span>
					<h2><?php echo esc_html( $g['naam'] ); ?></h2>
					<p class="service-card__problem"><?php echo esc_html( $g['positie'] ); ?></p>
					<p class="service-card__solution"><?php echo esc_html( implode( ', ', array_slice( $g['kernen'], 0, 4 ) ) . ( count( $g['kernen'] ) > 4 ? ' e.o.' : '' ) ); ?></p>
					<span class="service-card__subsidy"><?php warmvast_the_icon( 'ruler', 'wv-icon--sm' ); ?> <?php echo esc_html( $g['inwoners'] ); ?> inwoners</span>
					<?php if ( ! empty( $g['gemeentesubsidie']['bedrag'] ) ) : ?>
						<span class="service-card__subsidy"><?php warmvast_the_icon( 'euro', 'wv-icon--sm' ); ?> + gemeentelijke isolatiesubsidie</span>
					<?php endif; ?>
					<div class="service-card__foot">
						<a class="link-arrow" href="<?php echo esc_url( home_url( '/subsidie-' . $key . '/' ) ); ?>">ISDE-subsidie in <?php echo esc_html( $g['naam'] ); ?> <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?></a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section section--paper">
	<div class="container grid grid--2" style="align-items:start;gap:clamp(1.5rem,4vw,3rem)">
		<div data-reveal>
			<?php warmvast_section_header( 'Landelijk, geen uitzonderingen', 'Waarom deze pagina\'s per gemeente?' ); ?>
			<p>De ISDE-subsidie voor isolatie wordt vastgesteld door RVO, niet door de gemeente. Het basisbedrag per m² en de verdubbeling bij twee of meer maatregelen gelden overal in Nederland — dus ook in elke gemeente in regio Zaandam. Deze pagina's bestaan niet omdat het ISDE-tarief lokaal verschilt, maar om twee andere redenen: de woningvoorraad verschilt per gemeente (een boerderij in Landsmeer vraagt om een andere aanpak dan een jaren 70-portiekwoning in Purmerend), én elke gemeente heeft via het Nationaal Isolatieprogramma een eigen, aanvullende lokale isolatiesubsidie met eigen bedragen, voorwaarden en budget.</p>
		</div>
		<div class="card" data-reveal>
			<h3>Twijfelt u welke gemeente-pagina relevant is?</h3>
			<p>Start gewoon de scan met uw eigen adres. U krijgt direct een indicatie op basis van uw woning, ongeacht in welke gemeente in regio Zaandam u woont.</p>
			<?php warmvast_cta( 'Start gratis isolatiescan', 'primary', home_url( '/gratis-isolatiescan/' ) ); ?>
		</div>
	</div>
</section>
<?php
get_footer();
