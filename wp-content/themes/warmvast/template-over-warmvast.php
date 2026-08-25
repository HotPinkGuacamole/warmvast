<?php
/**
 * Template Name: Over Warmvast
 *
 * The story + the team. Real photos only (assets/img/), no stock imagery, no
 * invented facts — see warmvast_team() in inc/config.php for the single source
 * of truth on names/roles.
 *
 * Layout: the same plain .page-hero every other inner page uses, compact team
 * cards, then the origin story told as alternating small-photo .story-row
 * blocks (see main.css) so the eye zigzags down the page instead of scrolling
 * past a stack of big pictures.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$team = warmvast_team();

// The origin story, told as alternating photo/text rows. Photos alternate
// sides via --alt; each media block reveals in from its own side.
$verhaal = array(
	array(
		'foto'  => '/assets/img/team/team-groep',
		'alt'   => 'Levi, Alex en Noah van Warmvast samen buiten, in hun werkshirts',
		'step'  => 'Waar het begon',
		'title' => 'Drie collega’s, dezelfde frustratie',
		'text'  => 'Voordat Warmvast bestond, werkten Levi, Alex en Noah alle drie al in de isolatiebranche — ieder aan een ander stuk van hetzelfde traject: uitvoering, planning, techniek. En alle drie zagen we dezelfde frictie. Een monteur die op de dag zelf iets anders aantrof dan er verkocht was. Een subsidie-aanvraag die pas ná de uitvoering ter sprake kwam. Een klant die zelf moest uitzoeken welk formulier waarheen moest.',
	),
	array(
		'foto'  => '/assets/img/werk/werk-oplevering-klant',
		'alt'   => 'Warmvast-monteur en bewoner maken kennis bij de voordeur',
		'step'  => 'Wat we anders doen',
		'title' => 'Van a tot z ontzorgd',
		'text'  => 'In ' . WARMVAST_FOUNDED . ' zijn we voor onszelf begonnen, met één uitgangspunt. Wij komen zelf voor de technische schouw langs, brengen ter plekke uw subsidiemogelijkheden in kaart, en blijven daarna hetzelfde aanspreekpunt — tot en met de uitvoering en het dossier. Geen overdracht tussen verkoop en uitvoering. Geen los eindje.',
	),
	array(
		'foto'  => '/assets/img/werk/werk-dakisolatie-04',
		'alt'   => 'Afgewerkte dakisolatie met reflecterende folie tussen de sporen',
		'step'  => 'Waar we op afgerekend willen worden',
		'title' => 'Het werk zelf, niet de verkoop ervan',
		'text'  => 'Wat ons drijft is niet dat we de goedkoopste zijn — dat claimen we ook nergens. Het is dat u bij ons precies weet waar u aan toe bent: welke maatregel zin heeft voor úw woning, wat het aan subsidie oplevert, en wanneer het klaar is.',
	),
);

// What Warmvast already commits to elsewhere on the site (USP bar,
// subsidie-service's ontzorging list) -- reused here, not invented fresh.
$verwacht = array(
	'Wij komen zelf voor de technische schouw langs',
	'Subsidiemogelijkheden al bij de opname in kaart',
	'Eén vast aanspreekpunt, van scan tot dossier',
	'Meldcodes en fotobewijs voor uw subsidiedossier',
);
?>

<header class="page-hero">
	<div class="container page-hero__inner">
		<nav class="breadcrumb" aria-label="Kruimelpad">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span aria-hidden="true">/</span><span>Over Warmvast</span>
		</nav>
		<p class="kicker">Over ons</p>
		<h1 class="page-hero__title">Wij zijn Warmvast</h1>
		<p class="page-hero__sub">Drie mensen die vonden dat het isoleren van een woning eerlijker en gestroomlijnder kon — en het zelf zijn gaan doen.</p>
	</div>
</header>

<!-- ============ TEAM: compact cards ============ -->
<section class="section section--surface">
	<div class="container">
		<?php warmvast_section_header( 'Het team', 'De mensen die het werk doen', 'Drie vaste gezichten, geen wisselende onderaannemers.', 'center' ); ?>
		<div class="grid team-grid">
			<?php foreach ( $team as $member ) : ?>
				<article class="card team-card" data-reveal>
					<div class="team-card__photo">
						<?php
						warmvast_the_responsive_img(
							$member['foto'],
							array( 400, 960 ),
							'(max-width: 860px) 400px, 348px',
							$member['naam'] . ', ' . $member['functie'] . ' bij Warmvast'
						);
						?>
						<span class="team-card__role-tag"><?php echo esc_html( $member['functie'] ); ?></span>
					</div>
					<div class="team-card__body">
						<h3 class="team-card__name"><?php echo esc_html( $member['naam'] ); ?></h3>
						<p class="team-card__bio"><?php echo esc_html( $member['bio'] ); ?></p>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ============ VERHAAL: alternating story rows ============ -->
<section class="section section--paper">
	<div class="container">
		<?php warmvast_section_header( '', 'Hoe Warmvast is ontstaan', '', 'center' ); ?>

		<?php foreach ( $verhaal as $i => $row ) : ?>
			<?php $alt = 1 === $i % 2; ?>
			<div class="story-row<?php echo $alt ? ' story-row--alt' : ''; ?>">
				<div class="story-row__media" data-reveal="<?php echo $alt ? 'right' : 'left'; ?>">
					<div class="story-row__drift" data-parallax="0.045">
						<?php warmvast_the_responsive_img( $row['foto'], array( 480, 960 ), '(max-width: 720px) 340px, 440px', $row['alt'] ); ?>
					</div>
				</div>
				<div data-reveal="<?php echo $alt ? 'left' : 'right'; ?>">
					<span class="story-row__step"><?php echo esc_html( $row['step'] ); ?></span>
					<h3><?php echo esc_html( $row['title'] ); ?></h3>
					<p><?php echo esc_html( $row['text'] ); ?></p>
				</div>
			</div>
		<?php endforeach; ?>

		<div style="margin-top:clamp(2.5rem,6vw,4rem)">
			<blockquote class="about-story__quote" data-reveal="scale">Isolatie op basis van feiten, niet van beloftes.</blockquote>
		</div>
	</div>
</section>

<!-- ============ WAT U VAN ONS KUNT VERWACHTEN ============ -->
<section class="section section--surface">
	<div class="container u-center" style="max-width:760px;margin-inline:auto">
		<?php warmvast_section_header( 'Wat ons drijft', 'Wat u van ons kunt verwachten', '', 'center' ); ?>
		<ul class="checklist checklist--2" data-reveal>
			<?php foreach ( $verwacht as $item ) : ?>
				<li><?php echo esc_html( $item ); ?></li>
			<?php endforeach; ?>
		</ul>
		<div style="margin-top:1.8rem">
			<?php warmvast_cta( 'Maak kennis via de gratis isolatiescan', 'primary', home_url( '/gratis-isolatiescan/' ) ); ?>
		</div>
	</div>
</section>

<!-- ============ WAAR WE VOOR STAAN ============ -->
<section class="section section--paper">
	<div class="container u-center">
		<?php warmvast_section_header( '', 'Waar we voor staan', '', 'center' ); ?>
		<?php warmvast_the_trust_facts( 'u-center' ); ?>
		<?php warmvast_the_keurmerken( 'u-center' ); ?>
	</div>
</section>
<?php
get_footer();
