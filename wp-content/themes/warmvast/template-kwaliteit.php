<?php
/**
 * Template Name: Kwaliteit & garantie
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$kwaliteit = array(
	array( 'ruler', 'Technische opname vooraf', 'Een vakman controleert de woning voordat er wordt geoffreerd: spouwbreedte, vocht, ventilatie en de staat van de gevel of kap.' ),
	array( 'wall', 'Materiaalkeuze op maat', 'Het isolatiemateriaal wordt afgestemd op de opname — niet standaard hetzelfde voor elke woning.' ),
	array( 'shield', 'Uitvoering door vakmensen', 'De uitvoering gebeurt volgens de materiaal- en isolatiewaarde-eisen die ook voor de ISDE gelden.' ),
	array( 'camera', 'Fotobewijs en meldcodes', 'Tijdens de uitvoering leggen wij meldcodes en foto’s vast — bewijs dat u nodig heeft voor uw subsidiedossier.' ),
);

$faqs = array(
	array( 'q' => 'Wat is het verschil tussen materiaal- en uitvoeringsgarantie?', 'a' => 'Materiaalgarantie zit op het isolatieproduct zelf en komt van de fabrikant of leverancier. Uitvoeringsgarantie is de garantie die het isolatiebedrijf zelf geeft op de juiste toepassing daarvan. Bij een goede opname en uitvoering vullen beide elkaar aan.' ),
	array( 'q' => 'Wat gebeurt er als er na de uitvoering iets misgaat?', 'a' => 'Neem dan contact op via de contactpagina. We kijken samen met u naar de oorzaak en een passende oplossing.' ),
	array( 'q' => 'Is de ISDE-indicatie een garantie op subsidie?', 'a' => 'Nee. De scan en onze berekeningen zijn een indicatie op basis van de bekende ISDE-tarieven. RVO beoordeelt en beslist over elke aanvraag; aan een indicatie kunnen geen rechten worden ontleend.' ),
	array( 'q' => 'Hoe weet ik dat de m²-berekening klopt?', 'a' => 'De online scan geeft een eerste indicatie op basis van uw adres. De technische opname meet de exacte m² ter plaatse — die opname is bepalend voor de definitieve offerte en subsidieaanvraag.' ),
);
?>
<header class="page-hero page-hero--service">
	<div class="container page-hero__inner">
		<nav class="breadcrumb" aria-label="Kruimelpad">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span aria-hidden="true">/</span><span>Kwaliteit &amp; garantie</span>
		</nav>
		<div class="service-hero__grid">
			<div>
				<span class="service-hero__icon"><?php warmvast_the_icon( 'award' ); ?></span>
				<p class="kicker">Kwaliteit &amp; garantie</p>
				<h1 class="page-hero__title">Hoe Warmvast kwaliteit waarborgt</h1>
				<p class="page-hero__sub">Geen loze garanties, maar een werkwijze die controleerbaar is: opname vooraf, materiaal op maat, en fotobewijs voor uw dossier.</p>
				<div class="service-hero__actions">
					<?php warmvast_cta( 'Start gratis isolatiescan', 'accent', home_url( '/gratis-isolatiescan/' ) ); ?>
					<?php warmvast_phone_link( 'service-hero__phone' ); ?>
				</div>
			</div>
			<aside class="service-hero__facts">
				<p class="kicker">In het kort</p>
				<dl class="facts">
					<div><dt>Opname</dt><dd style="font-size:var(--step-0)">Altijd vooraf, ter plaatse</dd></div>
					<div><dt>Materiaal</dt><dd style="font-size:var(--step-0)">Gekozen op basis van de opname</dd></div>
					<div><dt>Bewijs</dt><dd style="font-size:var(--step-0)">Foto en meldcode per project</dd></div>
				</dl>
				<p class="facts__note">ISDE-indicaties zijn onder voorbehoud van RVO-beoordeling.</p>
			</aside>
		</div>
	</div>
</header>

<section class="section section--surface">
	<div class="container">
		<?php warmvast_the_trust_facts(); ?>
		<?php warmvast_the_keurmerken(); ?>
	</div>
</section>

<section class="section section--surface">
	<div class="container">
		<?php warmvast_section_header( 'Onze werkwijze', 'Waar kwaliteit uit bestaat', 'Vier stappen, in deze volgorde, bij elk project.' ); ?>
		<ol class="steps steps--4">
			<?php foreach ( $kwaliteit as $k ) : ?>
				<li data-reveal>
					<h3><?php echo esc_html( $k[1] ); ?></h3>
					<p><?php echo esc_html( $k[2] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<section class="section section--paper">
	<div class="container">
		<?php warmvast_section_header( 'Garantie uitgelegd', 'Materiaalgarantie versus uitvoeringsgarantie' ); ?>
		<div class="compare">
			<div class="compare__side" data-reveal>
				<span class="compare__icon"><?php warmvast_the_icon( 'doc' ); ?></span>
				<h3 class="compare__title">Materiaalgarantie</h3>
				<p>Komt van de fabrikant of leverancier van het isolatieproduct zelf — onafhankelijk van wie het aanbrengt.</p>
			</div>
			<div class="compare__side compare__side--hot" data-reveal>
				<span class="compare__icon"><?php warmvast_the_icon( 'shield' ); ?></span>
				<h3 class="compare__title">Uitvoeringsgarantie</h3>
				<p>Geeft het isolatiebedrijf zelf, op de juiste toepassing van het materiaal in úw woning.</p>
			</div>
		</div>
		<div class="grid grid--2" style="align-items:start;gap:clamp(1.5rem,4vw,3rem)">
			<p data-reveal>In de branche is een garantie van 10 jaar op spouwmuurisolatie gebruikelijk bij bedrijven die zijn aangesloten bij de brancheorganisatie VENIN. De exacte garantietermijn van Warmvast <?php echo WARMVAST_WARRANTY_YEARS ? 'is ' . esc_html( WARMVAST_WARRANTY_YEARS ) . ' jaar op materiaal en uitvoering' : 'wordt hier gepubliceerd zodra deze definitief is vastgesteld'; ?>.</p>
			<div class="card" data-reveal>
				<h3>Twijfelt u of uw woning geschikt is?</h3>
				<p>De technische opname geeft altijd definitief uitsluitsel — de online scan is een eerste, vrijblijvende indicatie.</p>
				<?php warmvast_cta( 'Start gratis isolatiescan', 'primary', home_url( '/gratis-isolatiescan/' ) ); ?>
			</div>
		</div>
	</div>
</section>

<section class="section section--surface">
	<div class="container">
		<?php warmvast_section_header( 'Veelgestelde vragen', 'Over kwaliteit en garantie' ); ?>
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
