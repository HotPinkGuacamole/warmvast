<?php
/**
 * Template Name: Zakelijk & VvE
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$doelgroepen = array(
	array( 'building', 'VvE\'s', 'Isolatie van gemeenschappelijke bouwdelen — dak, gevel, begane grondvloer — met één aanspreekpunt voor het hele bestuur en een gezamenlijk subsidiedossier.' ),
	array( 'doc', 'Vastgoedbeheerders', 'Planning en uitvoering over meerdere panden en eigenaren heen, met heldere rapportage per complex en minimale overlast voor bewoners.' ),
	array( 'shield', 'Woningcorporaties', 'Programmatische verduurzaming van woningblokken: technische opname, fasering en subsidiebegeleiding op portefeuilleniveau.' ),
	array( 'map', 'Particuliere verhuurders', 'Meerdere panden isoleren zonder gedoe: één contactpersoon, één planning, per pand een eigen ISDE-indicatie.' ),
);

$werkwijze = array(
	array( 'Intake', 'We bespreken de omvang: aantal woningen of bouwdelen, gewenste maatregelen en planning.' ),
	array( 'Gezamenlijke opname', 'Een vakman neemt het complex of de portefeuille op en stelt de m² en geschikte maatregelen per bouwdeel vast.' ),
	array( 'Voorstel per complex', 'U ontvangt één overzichtelijk voorstel met kosten, planning en subsidie-indicatie voor het geheel.' ),
	array( 'Gefaseerde uitvoering', 'Uitvoering in overleg gepland, met beperkte overlast voor bewoners of huurders.' ),
	array( 'Collectief dossier', 'Meldcodes en fotobewijs per bouwdeel vastgelegd voor een compleet, herleidbaar subsidiedossier.' ),
);

$faqs = array(
	array( 'q' => 'Geldt de ISDE ook voor VvE\'s?', 'a' => 'Ja. Naast de ISDE voor individuele woningeigenaren bestaat er een aparte ISDE-regeling voor Verenigingen van Eigenaars, gericht op gemeenschappelijke bouwdelen zoals dak en gevel. De voorwaarden wijken op punten af van de regeling voor particuliere woningeigenaren. Warmvast rekent dit na een opname van uw pand voor u door.' ),
	array( 'q' => 'Kunnen jullie meerdere woningen tegelijk plannen?', 'a' => 'Ja, dat is precies waar deze aanpak voor bedoeld is. We stemmen de planning af op uw complex of portefeuille, zodat de uitvoering efficiënt verloopt en bewoners niet meerdere keren apart worden benaderd.' ),
	array( 'q' => 'Werkt Warmvast ook buiten regio Zaandam voor zakelijke opdrachtgevers?', 'a' => 'Warmvast is actief in ' . WARMVAST_REGION . '. Voor zakelijke trajecten kijken we per aanvraag naar de beste planning binnen dit werkgebied.' ),
	array( 'q' => 'Hoe verloopt de facturatie bij meerdere eenheden?', 'a' => 'Dat stemmen we af op uw situatie: per wooneenheid, per bouwdeel of in één keer voor het hele complex. Bespreek dit tijdens de intake.' ),
);
?>
<header class="page-hero page-hero--service">
	<div class="container page-hero__inner">
		<nav class="breadcrumb" aria-label="Kruimelpad">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span aria-hidden="true">/</span><span>Zakelijk &amp; VvE</span>
		</nav>
		<div class="service-hero__grid">
			<div>
				<span class="service-hero__icon"><?php warmvast_the_icon( 'building' ); ?></span>
				<p class="kicker">Zakelijk &amp; VvE</p>
				<h1 class="page-hero__title">Isolatie voor VvE's, vastgoedbeheerders en woningcorporaties</h1>
				<p class="page-hero__sub">Eén aanspreekpunt voor meerdere woningen of bouwdelen: technische opname, gefaseerde uitvoering en een compleet, collectief subsidiedossier.</p>
				<div class="service-hero__actions">
					<a class="btn btn--accent btn--lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" data-track="cta_click">Adviesgesprek aanvragen <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?></a>
					<?php warmvast_phone_link( 'service-hero__phone' ); ?>
				</div>
			</div>
			<aside class="service-hero__facts">
				<p class="kicker">Voor wie</p>
				<dl class="facts">
					<div><dt>VvE's</dt><dd style="font-size:var(--step-0)">Gemeenschappelijke bouwdelen</dd></div>
					<div><dt>Beheerders</dt><dd style="font-size:var(--step-0)">Meerdere panden</dd></div>
					<div><dt>Corporaties</dt><dd style="font-size:var(--step-0)">Portefeuilleniveau</dd></div>
				</dl>
				<p class="facts__note">Eén planning, één dossier, per bouwdeel een eigen ISDE-indicatie.</p>
			</aside>
		</div>
	</div>
</header>

<section class="section section--surface">
	<div class="container">
		<?php warmvast_section_header( 'Voor wie', 'Vier soorten zakelijke opdrachtgevers' ); ?>
		<div class="grid grid--4">
			<?php foreach ( $doelgroepen as $d ) : ?>
				<div class="card problem-card" data-reveal>
					<span class="problem-card__icon"><?php warmvast_the_icon( $d[0] ); ?></span>
					<div class="problem-card__body">
						<p><strong><?php echo esc_html( $d[1] ); ?></strong></p>
						<p><?php echo esc_html( $d[2] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section section--paper">
	<div class="container">
		<?php warmvast_section_header( 'Werkwijze', 'Zo verloopt een zakelijk traject' ); ?>
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

<section class="section section--surface">
	<div class="container grid grid--2" style="align-items:start;gap:clamp(1.5rem,4vw,3rem)">
		<div data-reveal>
			<?php warmvast_section_header( 'Subsidie', 'ISDE bij meerdere woningen of een VvE' ); ?>
			<p>De ISDE-regeling voor individuele woningeigenaren rekent Warmvast overal op de site voor met een vast tarief per m². Voor Verenigingen van Eigenaars bestaat daarnaast een <strong>aparte ISDE-regeling voor gemeenschappelijke bouwdelen</strong> zoals het dak of de gevel — de voorwaarden hiervan wijken op punten af.</p>
			<p>Bij vastgoedbeheerders en woningcorporaties met meerdere zelfstandige woningen wordt de subsidie doorgaans per wooneenheid aangevraagd, ook als de uitvoering gezamenlijk gepland wordt. Warmvast brengt dit onderscheid tijdens de opname helder in kaart, zodat u vooraf weet waar u aan toe bent.</p>
		</div>
		<div class="card" data-reveal>
			<h3>Wilt u een indicatie voor uw complex?</h3>
			<p>Gebruik de gratis woningscan voor een eerste indicatie per adres, of vraag direct een adviesgesprek aan voor een voorstel op maat van uw portefeuille.</p>
			<?php warmvast_cta( 'Adviesgesprek aanvragen', 'primary', home_url( '/contact/' ) ); ?>
		</div>
	</div>
</section>

<section class="section section--paper">
	<div class="container">
		<?php warmvast_section_header( 'Veelgestelde vragen', 'Over zakelijke trajecten' ); ?>
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
