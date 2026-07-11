<?php
/**
 * Front page — the conversion homepage.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$services = warmvast_services();

$problems = array(
	array( 'thermo', 'De woonkamer koelt snel af.' ),
	array( 'floor', 'De vloer voelt koud aan.' ),
	array( 'glass', 'Er is tocht of kou bij de ramen.' ),
	array( 'euro', 'De energierekening blijft hoog.' ),
	array( 'wall', 'De kruipruimte is vochtig.' ),
	array( 'shield', 'U weet niet welke subsidie mogelijk is.' ),
);

$werkwijze = array(
	array( 'Gratis isolatiescan', 'U vult online uw woninggegevens in en ziet direct een ISDE-indicatie.' ),
	array( 'Telefonische check', 'We bellen u binnen 24 uur om uw situatie en wensen door te nemen.' ),
	array( 'Technische opname', 'Een vakman controlemeet spouw, vloer, glas of dak bij u thuis.' ),
	array( 'Heldere offerte', 'U ontvangt een offerte met exacte m² en de bijbehorende maatregelen.' ),
	array( 'Vakkundige uitvoering', 'Ervaren monteurs voeren de isolatie uit volgens de subsidie-eisen.' ),
	array( 'Subsidiedossier', 'Wij leggen meldcodes en fotobewijs vast voor uw ISDE-aanvraag.' ),
);

$trust = array(
	array( 'wall', 'Materiaalkeuze op basis van uw woning' ),
	array( 'doc', 'Vaste offerte na de opname, geen verrassingen achteraf' ),
	array( 'shield', 'Ervaren monteurs voeren de uitvoering uit' ),
	array( 'check', 'Onafhankelijk advies per maatregel' ),
);

$faqs = array(
	array(
		'q' => 'Kom ik in aanmerking voor ISDE-subsidie?',
		'a' => 'ISDE is bedoeld voor woningeigenaren die zelf in de woning wonen en die verduurzamen met bijvoorbeeld isolatie. De maatregel moet voldoen aan de RVO-eisen voor materiaal, isolatiewaarde en minimale oppervlakte. De Warmvast-scan geeft een indicatie; de technische opname bevestigt of uw woning geschikt is.',
	),
	array(
		'q' => 'Waarom verdubbelt de subsidie bij twee maatregelen?',
		'a' => 'De ISDE werkt met vaste bedragen per m². Laat u binnen de voorwaarden twee of meer isolatiemaatregelen uitvoeren, dan verdubbelt het subsidiebedrag per m² voor de isolatie. Dit geldt ook in combinatie met bijvoorbeeld een warmtepomp of zonneboiler. De tweede maatregel moet binnen 24 maanden worden uitgevoerd.',
	),
	array(
		'q' => 'Moet ik zelf de subsidie aanvragen?',
		'a' => 'De aanvraag gebeurt na uitvoering. Warmvast legt de benodigde meldcodes, m² en foto’s vast en helpt u met de voorbereiding van uw dossier. RVO beoordeelt en beslist uiteindelijk over de toekenning.',
	),
	array(
		'q' => 'Kan ik subsidie krijgen voor doe-het-zelf isolatie?',
		'a' => 'Nee. Isolatie die u zelf aanbrengt komt niet in aanmerking voor ISDE. De maatregel moet door een uitvoerend bedrijf worden geplaatst en voldoen aan de gestelde eisen.',
	),
	array(
		'q' => 'Hoe betrouwbaar is de online berekening?',
		'a' => 'De scan rekent met de bekende ISDE-tarieven en toont een indicatie op basis van uw ingevoerde m². Het is een richtbedrag, geen garantie. De werkelijke m² en subsidie worden vastgesteld bij de technische opname en de RVO-beoordeling.',
	),
	array(
		'q' => 'Wanneer neemt Warmvast contact op?',
		'a' => 'Na het versturen van uw scan nemen we binnen 24 uur (op werkdagen) telefonisch contact met u op om uw situatie door te nemen en een afspraak voor de opname te plannen.',
	),
);
?>

<!-- ============ HERO + SCAN ============ -->
<section class="hero">
	<span class="hero__orb hero__orb--1" aria-hidden="true"></span>
	<span class="hero__orb hero__orb--2" aria-hidden="true"></span>
	<div class="hero__thermal" aria-hidden="true"></div>
	<div class="hero__art" aria-hidden="true">
		<svg viewBox="0 0 320 300" fill="none" xmlns="http://www.w3.org/2000/svg">
			<!-- rising heat waves (animated) -->
			<g class="hero__heat" stroke="#fed03d" stroke-width="2.2" stroke-linecap="round" fill="none">
				<path class="hero__wave" d="M120 70c0-10 12-10 12-20s-12-10-12-20"/>
				<path class="hero__wave" d="M160 62c0-10 12-10 12-20s-12-10-12-20"/>
				<path class="hero__wave" d="M200 70c0-10 12-10 12-20s-12-10-12-20"/>
			</g>
			<!-- house -->
			<g stroke="#4fd0b8" stroke-width="2.4" stroke-linejoin="round" stroke-linecap="round" fill="none">
				<path class="hero__roof" d="M70 150 L165 82 L260 150" stroke="#fed03d"/>
				<path d="M92 138 V250 H238 V138"/>
				<rect x="120" y="176" width="42" height="42" rx="2"/>
				<rect x="180" y="176" width="34" height="74"/>
				<path d="M108 250 V138 M222 250 V138" opacity="0.5"/>
			</g>
		</svg>
	</div>
	<div class="container hero__inner">
		<div class="hero__copy">
			<span class="hero__eyebrow"><?php warmvast_the_icon( 'thermo', 'wv-icon--sm' ); ?> Comfortabel &amp; energiezuinig wonen</span>
			<h1 class="hero__title">Zit je er warmpjes bij? <em>Wij houden die warmte vast.</em></h1>
			<p class="hero__sub">Ontdek waar uw woning warmte verliest en welke isolatie het meeste oplevert, met een directe ISDE-indicatie en besparing op basis van úw adres.</p>
			<div class="hero__actions">
				<a class="btn btn--accent btn--lg btn--sheen" href="#warmvast-woningscan" data-track="cta_click">Start gratis isolatiescan <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?></a>
				<a class="btn btn--ghost btn--lg" href="<?php echo esc_url( home_url( '/subsidie-service/' ) ); ?>" data-track="cta_click">Bekijk subsidievoordeel</a>
			</div>
			<div class="hero__trust">
				<span><?php warmvast_the_icon( 'check', 'wv-icon--sm' ); ?> Gratis, 2 minuten, geen verplichtingen</span>
				<span><?php warmvast_the_icon( 'check', 'wv-icon--sm' ); ?> Spouw, vloer, glas &amp; dak</span>
			</div>
		</div>

		<div class="hero__scan">
			<?php get_template_part( 'template-parts/woningscan' ); ?>
		</div>
	</div>
</section>

<!-- ============ USP BAR ============ -->
<?php
$usps = array(
	array( 'ruler', 'Technische opname', 'vóór elke uitvoering' ),
	array( 'euro', 'Helder m²-overzicht', 'u weet wat u betaalt' ),
	array( 'doc', 'Subsidiedossier geregeld', 'meldcodes &amp; fotobewijs' ),
	array( 'clock', 'Reactie binnen 24 uur', 'werkzaam in heel Nederland' ),
);
?>
<section class="usp-bar flow-top" aria-label="Waarom Warmvast">
	<div class="container usp-bar__grid">
		<?php foreach ( $usps as $u ) : ?>
			<div class="usp">
				<span class="usp__icon"><?php warmvast_the_icon( $u[0] ); ?></span>
				<span class="usp__text"><strong><?php echo esc_html( $u[1] ); ?></strong><em><?php echo wp_kses_post( $u[2] ); ?></em></span>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<!-- ============ PROBLEEMHERKENNING ============ -->
<section class="section section--paper">
	<div class="container">
		<?php warmvast_section_header( '', 'Herkent u dit in uw woning?', 'Kleine signalen wijzen vaak op onnodig warmteverlies. Warmvast kijkt naar uw woning als een systeem.' ); ?>
		<div class="grid grid--3">
			<?php foreach ( $problems as $p ) : ?>
				<div class="card problem-card" data-reveal>
					<span class="problem-card__icon"><?php warmvast_the_icon( $p[0] ); ?></span>
					<p><?php echo esc_html( $p[1] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
		<div style="margin-top:2rem">
			<?php warmvast_cta( 'Laat Warmvast meekijken', 'primary', '#warmvast-woningscan' ); ?>
		</div>
	</div>
</section>

<!-- ============ DIENSTEN ============ -->
<section class="section section--surface">
	<div class="container">
		<?php warmvast_section_header( '', 'Vier maatregelen, één heldere aanpak', 'Elke maatregel lost een specifiek warmteverlies op. Combineer slim en benut de ISDE-systematiek optimaal.' ); ?>
		<div class="grid grid--4">
			<?php foreach ( $services as $key => $s ) : ?>
				<article class="card card--link service-card" data-reveal>
					<span class="service-card__icon"><?php warmvast_the_icon( $s['icon'] ); ?></span>
					<h3><?php echo esc_html( $s['label'] ); ?></h3>
					<p class="service-card__problem"><?php echo esc_html( $s['problem'] ); ?></p>
					<p class="service-card__solution"><?php echo esc_html( $s['solution'] ); ?></p>
					<span class="service-card__subsidy"><?php warmvast_the_icon( 'euro', 'wv-icon--sm' ); ?> Vanaf <?php echo esc_html( warmvast_rate( $s['baseRate'] ) ); ?>/m²</span>
					<div class="service-card__foot">
						<a class="link-arrow" href="<?php echo esc_url( $s['url'] ); ?>">Check deze maatregel <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?></a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ============ ISDE VERDUBBELAAR ============ -->
<?php
$r          = warmvast_isde_rates();
$spouw_solo = 65 * $r['spouw']['baseRate'];
$spouw_dub  = 65 * $r['spouw']['baseRate'] * 2;
$vloer_dub  = 55 * $r['vloer']['baseRate'] * 2;
$combo      = $spouw_dub + $vloer_dub;
?>
<section class="section section--paper">
	<div class="container verdub">
		<div class="verdub__copy" data-reveal="left">
			<span class="verdub__badge"><?php warmvast_the_icon( 'spark', 'wv-icon--sm' ); ?> De grootste hefboom</span>
			<h2>Twee maatregelen? Dan verdubbelt uw ISDE-tarief per m².</h2>
			<p>De ISDE werkt met vaste bedragen per m². Laat u meer dan één isolatiemaatregel uitvoeren binnen de voorwaarden, dan verdubbelt het subsidiebedrag voor isolatie. De Warmvast-scan laat dit direct zien, eerlijk en zonder verrassingen.</p>
			<ul class="checklist">
				<li>Geldt ook in combinatie met warmtepomp, zonneboiler of warmtenet.</li>
				<li>De tweede maatregel voert u binnen 24 maanden uit.</li>
				<li>U vraagt de subsidie aan ná uitvoering.</li>
			</ul>
			<?php warmvast_cta( 'Bereken uw verdubbeling', 'primary', '#warmvast-woningscan' ); ?>
		</div>

		<div class="verdub__calc" data-reveal="right">
			<div class="verdub__row">
				<span class="verdub__label">Alleen spouw<small>65 m² × <?php echo esc_html( warmvast_rate( $r['spouw']['baseRate'] ) ); ?></small></span>
				<span class="verdub__amount"><?php echo esc_html( warmvast_euro( $spouw_solo ) ); ?></span>
			</div>
			<div class="verdub__row">
				<span class="verdub__label">Spouw (verdubbeld)<small>65 m² × <?php echo esc_html( warmvast_rate( $r['spouw']['baseRate'] * 2 ) ); ?></small></span>
				<span class="verdub__amount"><?php echo esc_html( warmvast_euro( $spouw_dub ) ); ?></span>
			</div>
			<div class="verdub__row">
				<span class="verdub__label">Vloer (verdubbeld)<small>55 m² × <?php echo esc_html( warmvast_rate( $r['vloer']['baseRate'] * 2 ) ); ?></small></span>
				<span class="verdub__amount"><?php echo esc_html( warmvast_euro( $vloer_dub ) ); ?></span>
			</div>
			<div class="verdub__row verdub__row--total">
				<span class="verdub__label">Spouw + vloer samen<small>ISDE-indicatie</small></span>
				<span class="verdub__amount"><?php echo esc_html( warmvast_euro( $combo ) ); ?></span>
			</div>
			<p class="verdub__note">Indicatie onder voorbehoud van RVO-voorwaarden en beoordeling. Bedragen gebaseerd op ISDE-tarieven 2026.</p>
		</div>
	</div>
</section>

<!-- ============ WERKWIJZE ============ -->
<section class="section section--surface">
	<div class="container">
		<?php warmvast_section_header( '', 'Zo werkt isoleren met Warmvast', 'Van gratis scan tot een compleet subsidiedossier, in zes overzichtelijke stappen.' ); ?>
		<ol class="steps">
			<?php foreach ( $werkwijze as $step ) : ?>
				<li data-reveal>
					<h3><?php echo esc_html( $step[0] ); ?></h3>
					<p><?php echo esc_html( $step[1] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<!-- ============ VERTROUWEN ============ -->
<section class="section section--paper">
	<div class="container">
		<?php warmvast_section_header( '', 'Nuchter, technisch en transparant', 'Wij claimen alleen wat we kunnen uitleggen en vastleggen.' ); ?>
		<div class="trust-grid">
			<?php foreach ( $trust as $t ) : ?>
				<div class="trust-item" data-reveal>
					<?php warmvast_the_icon( $t[0] ); ?>
					<span><?php echo esc_html( $t[1] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ============ REVIEWS ============ -->
<?php get_template_part( 'template-parts/reviews' ); ?>

<!-- ============ FAQ ============ -->
<section class="section section--surface">
	<div class="container">
		<?php warmvast_section_header( '', 'Helder over subsidie en uitvoering' ); ?>
		<div class="faq">
			<?php foreach ( $faqs as $i => $faq ) : ?>
				<details class="faq__item" <?php echo 0 === $i ? 'open' : ''; ?>>
					<summary><?php echo esc_html( $faq['q'] ); ?> <?php warmvast_the_icon( 'chevron' ); ?></summary>
					<div class="faq__answer"><p><?php echo wp_kses_post( $faq['a'] ); ?></p></div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
warmvast_faq_schema( $faqs );
get_footer();
