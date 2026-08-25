<?php
/**
 * Template Name: Ons werk
 *
 * A behind-the-scenes photo story from a real dakisolatie-klus, not a fake
 * "completed projects" grid with invented addresses/dates. warmvast_projecten()
 * stays wired for real case studies (with photo, plaats, maatregelen) as they
 * become available; this page's story rows are independent of that and
 * always show the real in-progress photos in assets/img/werk/.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$projecten = warmvast_projecten();
$team      = warmvast_team();
$levi      = null;
foreach ( $team as $member ) {
	if ( 'levi' === $member['key'] ) {
		$levi = $member;
		break;
	}
}

// The real, unretouched-story rows: reflective dakisolatiefolie going in
// between the sporen of a kap, told start to finish. Text describes
// technique, not a specific address/date/klant -- see the file docblock.
$rows = array(
	array(
		'file'  => 'werk-dakisolatie-04',
		'title' => 'Van sporen naar afgewerkt dak',
		'text'  => 'Dit dak begon als een kale kapconstructie — sporen, gordingen en verder niets tussen de bewoners en de buitenlucht. Warmvast isoleert met reflecterende isolatiefolie, strak aangebracht tussen de sporen tot aan de nok.',
	),
	array(
		'foto'  => $levi ? $levi['foto'] : '',
		'title' => $levi ? $levi['naam'] . ' voert het zelf uit' : '',
		'text'  => 'Bij Warmvast staat de uitvoering niet bij een wisselende onderaannemer, maar bij dezelfde persoon die u tijdens de technische opname al aan tafel had. Die kent de woning dus al, voordat de eerste baan folie wordt aangebracht.',
	),
	array(
		'file'  => 'werk-dakisolatie-03',
		'title' => 'Baan voor baan, langs elke gording',
		'text'  => 'Elke baan isolatiefolie overlapt de vorige en wordt luchtdicht afgetapet. Zo ontstaat een gesloten laag zonder koudebruggen — de details bepalen hier het resultaat, niet de snelheid.',
	),
	array(
		'file'  => 'werk-dakisolatie-06',
		'title' => 'Een dak dat zijn warmte niet meer weggeeft',
		'text'  => 'Het resultaat: een dakconstructie die warmte vasthoudt in plaats van weggeeft, met exact de m² die nodig zijn voor uw ISDE-dossier al vastgelegd tijdens de uitvoering.',
	),
);

// A few extra in-progress shots from the same klus, kept small and secondary.
$filmstrip = array(
	array( 'file' => 'werk-dakisolatie-01', 'cap' => 'De laatste baan wordt vastgezet.' ),
	array( 'file' => 'werk-dakisolatie-02', 'cap' => 'Precisiewerk langs elke gording.' ),
	array( 'file' => 'werk-dakisolatie-05', 'cap' => 'Ook rondom de cv-ketel blijft de folie intact.' ),
);
?>

<header class="page-hero">
	<div class="container page-hero__inner">
		<nav class="breadcrumb" aria-label="Kruimelpad">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span aria-hidden="true">/</span><span>Ons werk</span>
		</nav>
		<p class="kicker">Ons werk</p>
		<h1 class="page-hero__title">Achter de schermen bij een dakisolatie</h1>
		<p class="page-hero__sub">Geen stockfoto's — dit is hoe een dakisolatie er bij Warmvast daadwerkelijk uitziet, van sporen tot afgewerkte nok.</p>
	</div>
</header>

<!-- ============ STORY ROWS ============ -->
<section class="section section--surface">
	<div class="container">
		<?php warmvast_section_header( '', 'Reflecterende isolatiefolie, baan voor baan', 'Deze klus: dakisolatie tussen de sporen, luchtdicht afgetapet langs elke gording.' ); ?>

		<?php foreach ( $rows as $i => $row ) : ?>
			<?php $alt = 1 === $i % 2; ?>
			<div class="story-row<?php echo $alt ? ' story-row--alt' : ''; ?>">
				<div class="story-row__media" data-reveal="<?php echo $alt ? 'right' : 'left'; ?>">
					<div class="story-row__drift" data-parallax="0.045">
						<?php
						// Levi's portrait ships at 400/960; the werk photos at 480/960.
						$ws_base   = ! empty( $row['file'] ) ? '/assets/img/werk/' . $row['file'] : $row['foto'];
						$ws_widths = ! empty( $row['file'] ) ? array( 480, 960 ) : array( 400, 960 );
						warmvast_the_responsive_img( $ws_base, $ws_widths, '(max-width: 720px) 340px, 440px', $row['title'] );
						?>
					</div>
				</div>
				<div data-reveal="<?php echo $alt ? 'left' : 'right'; ?>">
					<span class="story-row__step"><?php echo esc_html( sprintf( 'Stap %02d', $i + 1 ) ); ?></span>
					<h3><?php echo esc_html( $row['title'] ); ?></h3>
					<p><?php echo esc_html( $row['text'] ); ?></p>
				</div>
			</div>
		<?php endforeach; ?>

		<div class="werk-filmstrip" data-reveal>
			<?php foreach ( $filmstrip as $f ) : ?>
				<div class="werk-filmstrip__item">
					<?php warmvast_the_responsive_img( '/assets/img/werk/' . $f['file'], array( 160, 320 ), '118px', $f['cap'] ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php if ( ! empty( $projecten ) ) : ?>
	<!-- ============ AFGERONDE PROJECTEN (met adres/maatregel) ============ -->
	<section class="section section--paper">
		<div class="container">
			<?php warmvast_section_header( '', 'Afgeronde projecten' ); ?>
			<div class="grid grid--3">
				<?php foreach ( $projecten as $p ) : ?>
					<article class="card card--post" data-reveal>
						<?php if ( ! empty( $p['foto'] ) ) : ?>
							<div class="card--post__media"><img src="<?php echo esc_url( $p['foto'] ); ?>" alt="<?php echo esc_attr( $p['titel'] ); ?>" loading="lazy"></div>
						<?php endif; ?>
						<div class="card--post__body">
							<h2 class="card--post__title"><?php echo esc_html( $p['titel'] ); ?></h2>
							<p>
								<?php warmvast_the_icon( 'map', 'wv-icon--sm' ); ?> <?php echo esc_html( $p['plaats'] ); ?>
								<?php if ( ! empty( $p['datum'] ) ) : ?>
									· <?php echo esc_html( $p['datum'] ); ?>
								<?php endif; ?>
							</p>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php else : ?>
	<section class="section section--paper">
		<div class="container u-center" style="max-width:640px;margin-inline:auto">
			<h2>Meer projecten volgen</h2>
			<p>Deze pagina groeit met elke klus. Zodra we de volgende projecten hebben vastgelegd — met adres, maatregel en resultaat — verschijnen ze hieronder.</p>
		</div>
	</section>
<?php endif; ?>

<section class="section section--surface">
	<div class="container u-center">
		<?php warmvast_section_header( '', 'Benieuwd wat isoleren úw woning oplevert?', '', 'center' ); ?>
		<?php warmvast_cta( 'Start gratis isolatiescan', 'primary', home_url( '/gratis-isolatiescan/' ) ); ?>
	</div>
</section>
<?php
get_footer();
