<?php
/**
 * Template Name: Subsidie service (ISDE)
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$rates = warmvast_isde_rates();

$systematiek = array(
	'ISDE is voor woningeigenaren die de woning waarin zij zelf wonen verduurzamen.',
	'Isolatiesubsidie wordt berekend met een vast bedrag per m².',
	'Bij twee of meer isolatiemaatregelen verdubbelt het subsidiebedrag voor isolatie.',
	'De verdubbeling geldt ook in combinatie met bijvoorbeeld een warmtepomp, zonneboiler of warmtenet.',
	'Een volgende maatregel voert u binnen 24 maanden uit.',
	'U vraagt de subsidie aan ná uitvoering.',
	'Doe-het-zelf isolatie komt niet in aanmerking.',
);

// End-to-end unburdening: what Warmvast takes off the applicant's hands.
$ontzorging = array(
	array( 'euro', 'Advies &amp; indicatie', 'Wij bepalen welke maatregelen in aanmerking komen en wat de subsidie realistisch oplevert voor úw woning.' ),
	array( 'ruler', 'Technische opname', 'Een vakman meet de exacte m² en legt de uitgangssituatie vast — de basis van een aanvraag die klopt.' ),
	array( 'shield', 'Uitvoering volgens de eisen', 'Onze monteurs voeren de isolatie uit volgens de materiaal- en isolatiewaarde-eisen van de ISDE.' ),
	array( 'camera', 'Meldcodes &amp; fotobewijs', 'Wij leggen tijdens de uitvoering de verplichte meldcodes en foto’s vast, precies zoals RVO ze vraagt.' ),
	array( 'doc', 'Dossier &amp; aanvraag', 'Wij stellen uw complete subsidiedossier samen en bereiden de ISDE-aanvraag volledig voor.' ),
	array( 'phone-check', 'Begeleiding tot toekenning', 'U dient in; wij blijven bereikbaar en denken mee tot de subsidie is afgehandeld.' ),
);

$faqs = array(
	array( 'q' => 'Hoeveel subsidie kan ik krijgen?', 'a' => 'Dat hangt af van de maatregelen en het aantal m². Gebruik de isolatiescan voor een directe indicatie op basis van uw woning.' ),
	array( 'q' => 'Wat betekent de verdubbeling precies?', 'a' => 'Voert u binnen de voorwaarden twee of meer isolatiemaatregelen uit, dan verdubbelt het bedrag per m² voor die isolatie. Dat maakt combineren financieel aantrekkelijk.' ),
	array( 'q' => 'Moet ik zelf iets regelen voor de aanvraag?', 'a' => 'Nauwelijks. Warmvast bereidt het volledige dossier voor — meldcodes, m² en fotobewijs — en zet de aanvraag klaar. U dient in na uitvoering; RVO beoordeelt en beslist.' ),
);
?>
<header class="page-hero">
	<div class="container page-hero__inner">
		<nav class="breadcrumb" aria-label="Kruimelpad">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span aria-hidden="true">/</span><span>Subsidie service</span>
		</nav>
		<p class="kicker">ISDE-subsidie</p>
		<h1 class="page-hero__title">ISDE-subsidie, van aanvraag tot toekenning uit handen genomen</h1>
		<p class="page-hero__sub">De ISDE beloont woningeigenaren die isoleren. Warmvast legt de systematiek eerlijk uit én regelt het hele traject: van advies en meldcodes tot een compleet dossier en de aanvraag. U hoeft zelf niets uit te zoeken.</p>
		<div class="service-hero__actions">
			<a class="btn btn--accent btn--lg" href="<?php echo esc_url( home_url( '/gratis-isolatiescan/' ) ); ?>" data-track="cta_click">Bereken mijn indicatie <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?></a>
			<a class="btn btn--secondary btn--lg" href="#ontzorging" data-track="cta_click">Zo ontzorgen wij u</a>
		</div>
	</div>
</header>

<section class="section section--surface" id="ontzorging">
	<div class="container">
		<?php warmvast_section_header( '', 'Volledige ontzorging, van begin tot eind', 'De ISDE-regels, de meldcodes, het fotobewijs en de aanvraag — het hele subsidietraject regelt Warmvast. U levert uw akkoord, wij doen de rest.', 'center' ); ?>
		<ol class="ontzorg-grid">
			<?php foreach ( $ontzorging as $i => $o ) : ?>
				<li class="card ontzorg-card" data-reveal>
					<span class="ontzorg-card__step" aria-hidden="true"><?php echo esc_html( $i + 1 ); ?></span>
					<span class="ontzorg-card__icon"><?php warmvast_the_icon( $o[0] ); ?></span>
					<h3><?php echo wp_kses_post( $o[1] ); ?></h3>
					<p><?php echo wp_kses_post( $o[2] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
		<p class="ontzorg-note">
			<?php warmvast_the_icon( 'shield', 'wv-icon--sm' ); ?>
			RVO beoordeelt en beslist over elke aanvraag. Met een compleet en correct dossier van Warmvast maakt u de meeste kans — maar een toekenning kunnen we nooit garanderen.
		</p>
	</div>
</section>

<section class="section section--paper">
	<div class="container grid grid--2" style="align-items:start;gap:clamp(1.5rem,4vw,3rem)">
		<div data-reveal>
			<?php warmvast_section_header( 'De systematiek', 'Zo werkt de ISDE voor isolatie' ); ?>
			<ul class="checklist">
				<?php foreach ( $systematiek as $s ) : ?>
					<li><?php echo esc_html( $s ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="card" data-reveal>
			<h3>De verdubbelaar in het kort</h3>
			<p>De grootste hefboom zit in het combineren van maatregelen. Twee isolatiemaatregelen binnen de voorwaarden betekenen een dubbel tarief per m².</p>
			<p class="wv-callout" style="margin-top:1rem">Voorbeeld: 65 m² spouw + 55 m² vloer. Door de verdubbeling stijgt de indicatie fors ten opzichte van één losse maatregel. De scan rekent het direct voor u uit.</p>
			<?php warmvast_cta( 'Toon mijn verdubbeling', 'primary', home_url( '/gratis-isolatiescan/' ) ); ?>
		</div>
	</div>
</section>

<section class="section section--surface">
	<div class="container">
		<?php warmvast_section_header( 'Tarieven 2026', 'ISDE-bedragen per maatregel', 'Basisbedrag per m² en het verdubbelde tarief bij twee of meer maatregelen.' ); ?>
		<div class="table-wrap">
			<table class="rate-table">
				<thead>
					<tr><th>Maatregel</th><th>Basis /m²</th><th>Verdubbeld /m²</th><th>Min. m²</th><th>Max. m²</th></tr>
				</thead>
				<tbody>
					<?php foreach ( $rates as $key => $r ) : ?>
						<tr>
							<td><a href="<?php echo esc_url( home_url( '/' . $r['slug'] . '/' ) ); ?>"><?php echo esc_html( $r['label'] ); ?></a></td>
							<td><?php echo esc_html( warmvast_rate( $r['baseRate'] ) ); ?></td>
							<td class="rate-table__hot"><?php echo esc_html( warmvast_rate( $r['baseRate'] * 2 ) ); ?></td>
							<td><?php echo esc_html( $r['minM2'] ); ?></td>
							<td><?php echo esc_html( $r['maxM2'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<p class="wv-disclaimer">Bedragen gebaseerd op de bekende ISDE-tarieven 2026. Definitieve subsidie hangt af van RVO-beoordeling, meldcodes, bewijsstukken en minimale oppervlaktes. Aan deze cijfers kunnen geen rechten worden ontleend.</p>
	</div>
</section>

<section class="section section--ink">
	<div class="container">
		<?php warmvast_section_header( 'Bereken direct', 'Uw ISDE-indicatie in beeld', 'Vul uw adres in en zie direct de maatregelen, subsidie en besparing voor uw woning.', 'center' ); ?>
		<div class="scan-embed scan-embed--ws">
			<?php get_template_part( 'template-parts/woningscan' ); ?>
		</div>
	</div>
</section>

<section class="section section--surface">
	<div class="container">
		<?php warmvast_section_header( 'Veelgestelde vragen', 'Over ISDE-subsidie' ); ?>
		<div class="faq">
			<?php foreach ( $faqs as $i => $faq ) : ?>
				<details class="faq__item" <?php echo 0 === $i ? 'open' : ''; ?>>
					<summary><?php echo esc_html( $faq['q'] ); ?> <?php warmvast_the_icon( 'chevron' ); ?></summary>
					<div class="faq__answer"><p><?php echo esc_html( $faq['a'] ); ?></p></div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php
warmvast_faq_schema( $faqs );
get_footer();
