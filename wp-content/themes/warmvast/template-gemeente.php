<?php
/**
 * Template Name: Gemeente subsidiepagina
 *
 * Slug-driven gemeentepagina voor regio Zaandam (Zaanstreek-Waterland). De
 * pagina-slug (verwacht: "subsidie-" gevolgd door de gemeentesleutel, bv.
 * subsidie-zaanstad) selecteert de bijbehorende content uit
 * warmvast_zaanstreek_gemeenten(). Dezelfde landelijke
 * ISDE-tarieven als op /subsidie-service/ — die zijn overal in Nederland
 * gelijk — met lokale context over kernen, woningtype en welke maatregel in
 * die gemeente doorgaans het meeste oplevert.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

while ( have_posts() ) :
	the_post();

	$slug      = get_post_field( 'post_name', get_the_ID() );
	$gemeenten = warmvast_zaanstreek_gemeenten();
	$key       = '';
	foreach ( $gemeenten as $k => $g ) {
		if ( 'subsidie-' . $k === $slug ) {
			$key = $k;
			break;
		}
	}
	$gemeente = $key ? $gemeenten[ $key ] : null;

	if ( ! $gemeente ) :
		// Fallback: render the page body if the slug doesn't match a gemeente.
		?>
		<header class="page-hero"><div class="container page-hero__inner"><h1 class="page-hero__title"><?php the_title(); ?></h1></div></header>
		<div class="container page-body prose"><?php the_content(); ?></div>
		<?php
	else :
		$rates    = warmvast_isde_rates();
		$services = warmvast_services();
		$gs       = isset( $gemeente['gemeentesubsidie'] ) ? $gemeente['gemeentesubsidie'] : null;
		$faqs     = warmvast_gemeente_faqs( $gemeente );
		?>

		<!-- Gemeente hero -->
		<header class="page-hero page-hero--service">
			<div class="container page-hero__inner">
				<nav class="breadcrumb" aria-label="Kruimelpad">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
					<span aria-hidden="true">/</span>
					<a href="<?php echo esc_url( home_url( '/gemeentes/' ) ); ?>">Gemeentes</a>
					<span aria-hidden="true">/</span>
					<span><?php echo esc_html( $gemeente['naam'] ); ?></span>
				</nav>
				<div class="service-hero__grid">
					<div>
						<span class="service-hero__icon"><?php warmvast_the_icon( 'map' ); ?></span>
						<p class="kicker">ISDE-subsidie in regio Zaandam</p>
						<h1 class="page-hero__title">ISDE-subsidie voor isolatie in <?php echo esc_html( $gemeente['naam'] ); ?></h1>
						<p class="page-hero__sub">Warmvast helpt woningeigenaren in <?php echo esc_html( $gemeente['naam'] ); ?> met technische opname, heldere m²-berekening en een realistische ISDE-indicatie — <?php echo esc_html( lcfirst( $gemeente['positie'] ) ); ?>.</p>
						<div class="service-hero__actions">
							<a class="btn btn--accent btn--lg" href="<?php echo esc_url( home_url( '/gratis-isolatiescan/' ) ); ?>" data-track="cta_click">Bereken mijn indicatie <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?></a>
							<?php warmvast_phone_link( 'service-hero__phone' ); ?>
						</div>
					</div>
					<aside class="service-hero__facts">
						<p class="kicker">In het kort</p>
						<dl class="facts">
							<div><dt>Inwoners</dt><dd><?php echo esc_html( $gemeente['inwoners'] ); ?></dd></div>
							<div><dt>Kernen</dt><dd><?php echo esc_html( count( $gemeente['kernen'] ) ); ?></dd></div>
							<div><dt>Ligging</dt><dd style="font-size:var(--step--1);font-weight:600"><?php echo esc_html( $gemeente['positie'] ); ?></dd></div>
						</dl>
						<p class="facts__note">ISDE-tarieven zijn landelijk, ook in <?php echo esc_html( $gemeente['naam'] ); ?>.</p>
					</aside>
				</div>
			</div>
		</header>

		<!-- Kernen + karakter -->
		<section class="section section--paper">
			<div class="container grid grid--2" style="align-items:start;gap:clamp(1.5rem,4vw,3rem)">
				<div data-reveal>
					<?php warmvast_section_header( 'De regio', 'Regio Zaandam, kern voor kern' ); ?>
					<p><?php echo esc_html( $gemeente['karakter'] ); ?></p>
				</div>
				<div class="card" data-reveal>
					<h3>Kernen in <?php echo esc_html( $gemeente['naam'] ); ?></h3>
					<ul class="kernen-list">
						<?php foreach ( $gemeente['kernen'] as $kern ) : ?>
							<li><?php warmvast_the_icon( 'map', 'wv-icon--sm' ); ?> <?php echo esc_html( $kern ); ?></li>
						<?php endforeach; ?>
					</ul>
					<p class="facts__note">Warmvast isoleert woningen in heel <?php echo esc_html( $gemeente['naam'] ); ?>, van de hoofdkern tot de kleinere buurtschappen.</p>
				</div>
			</div>
		</section>

		<!-- Welke maatregel loont hier -->
		<section class="section section--surface">
			<div class="container">
				<?php warmvast_section_header( 'Aandachtspunt', 'Welke isolatie loont in ' . $gemeente['naam'] . '?', $gemeente['aandacht'] ); ?>
				<div class="grid grid--<?php echo count( $gemeente['focus'] ) >= 3 ? '3' : '2'; ?>">
					<?php
					foreach ( $gemeente['focus'] as $fkey ) :
						if ( ! isset( $services[ $fkey ] ) ) {
							continue;
						}
						$s = $services[ $fkey ];
						?>
						<article class="card card--link service-card" data-reveal>
							<span class="service-card__icon"><?php warmvast_the_icon( $s['icon'] ); ?></span>
							<h3><?php echo esc_html( $s['label'] ); ?></h3>
							<p class="service-card__solution"><?php echo esc_html( $s['solution'] ); ?></p>
							<span class="service-card__subsidy"><?php warmvast_the_icon( 'euro', 'wv-icon--sm' ); ?> Vanaf <?php echo esc_html( warmvast_rate( $s['baseRate'] ) ); ?>/m²</span>
							<div class="service-card__foot">
								<a class="link-arrow" href="<?php echo esc_url( $s['url'] ); ?>">Bekijk <?php echo esc_html( strtolower( $s['label'] ) ); ?> <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?></a>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<!-- ISDE tarieven (landelijk, dus identiek aan /subsidie-service/) -->
		<section class="section section--paper">
			<div class="container">
				<?php warmvast_section_header( 'Tarieven 2026', 'ISDE-bedragen in ' . $gemeente['naam'], 'Landelijke RVO-tarieven per m². Bij twee of meer isolatiemaatregelen verdubbelt het tarief — ook in ' . $gemeente['naam'] . '.' ); ?>
				<div class="table-wrap">
					<table class="rate-table">
						<thead>
							<tr><th>Maatregel</th><th>Basis /m²</th><th>Verdubbeld /m²</th><th>Min. m²</th><th>Max. m²</th></tr>
						</thead>
						<tbody>
							<?php foreach ( $rates as $rkey => $r ) : ?>
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

		<!-- ISDE + gemeentelijke subsidie combineren -->
		<?php if ( $gs ) : ?>
		<section class="section section--surface">
			<div class="container">
				<?php warmvast_section_header( 'Stapelen', 'ISDE + gemeentelijke subsidie in ' . $gemeente['naam'], 'Naast de landelijke ISDE heeft ' . $gemeente['naam'] . ' een eigen lokale isolatiesubsidie, gefinancierd vanuit het Nationaal Isolatieprogramma (NIP). Deze twee zijn in de regel te combineren.' ); ?>

				<div class="stapel">
					<div class="stapel__card stapel__card--rijk" data-reveal="left">
						<span class="stapel__kicker"><?php warmvast_the_icon( 'map', 'wv-icon--sm' ); ?> Landelijk</span>
						<span class="stapel__icon"><?php warmvast_the_icon( 'euro' ); ?></span>
						<h3>ISDE</h3>
						<p class="stapel__desc">De rijkssubsidie voor isolatie — vast bedrag per m², overal in Nederland gelijk.</p>
						<ul class="checklist">
							<li>Vast tarief per m², elke gemeente gelijk</li>
							<li>Verdubbelt bij twee of meer maatregelen</li>
							<li>Geen inkomens- of WOZ-toets</li>
							<li>Aan te vragen ná uitvoering, RVO beoordeelt</li>
						</ul>
						<a class="link-arrow" href="<?php echo esc_url( home_url( '/subsidie-service/' ) ); ?>">Bekijk de ISDE-tarieven <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?></a>
					</div>

					<span class="stapel__plus" aria-hidden="true">+</span>

					<div class="stapel__card stapel__card--lokaal" data-reveal="right">
						<span class="stapel__kicker"><?php warmvast_the_icon( 'doc', 'wv-icon--sm' ); ?> Lokaal &middot; NIP</span>
						<span class="stapel__icon"><?php warmvast_the_icon( 'doc' ); ?></span>
						<h3><?php echo esc_html( $gemeente['naam'] ); ?></h3>
						<strong class="stapel__amount"><?php echo esc_html( $gs['bedrag_max'] ); ?></strong>
						<?php if ( ! empty( $gs['bedrag_laag'] ) ) : ?>
							<span class="stapel__amount-sub"><?php warmvast_the_icon( 'spark', 'wv-icon--sm' ); ?> <?php echo esc_html( $gs['bedrag_laag'] ); ?></span>
						<?php endif; ?>
						<p class="stapel__desc"><?php echo esc_html( $gs['naam'] ); ?></p>
						<p class="stapel__meta"><?php echo esc_html( $gs['voorwaarden'] ); ?></p>
						<p class="stapel__meta"><?php echo esc_html( $gs['looptijd'] ); ?></p>
						<a class="link-arrow" href="<?php echo esc_url( $gs['bron_url'] ); ?>" target="_blank" rel="noopener">Bekijk de regeling bij gemeente <?php echo esc_html( $gemeente['naam'] ); ?> <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?></a>
					</div>
				</div>

				<div class="stapel__foot">
					<?php warmvast_the_icon( 'shield', 'wv-icon--sm' ); ?>
					<p>Gecontroleerd op <?php echo esc_html( $gs['gecontroleerd'] ); ?> aan de hand van de website van gemeente <?php echo esc_html( $gemeente['naam'] ); ?>. Gemeentelijke regelingen hebben een eigen, vaak beperkt budget en kunnen op elk moment wijzigen of vol raken ("op is op") — controleer de actuele voorwaarden altijd bij de gemeente zelf vóórdat u een aanvraag doet. Aan deze informatie kunnen geen rechten worden ontleend.</p>
				</div>
			</div>
		</section>
		<?php endif; ?>

		<!-- Direct naar de scan -->
		<section class="section section--paper">
			<div class="container u-center">
				<?php warmvast_section_header( '', 'Wat levert isoleren úw woning in ' . $gemeente['naam'] . ' op?', 'Vul uw adres in en zie direct de maatregelen, subsidie en besparing voor uw woning.', 'center' ); ?>
				<?php warmvast_cta( 'Start gratis isolatiescan', 'accent', home_url( '/gratis-isolatiescan/' ) ); ?>
			</div>
		</section>

		<!-- FAQ -->
		<section class="section section--surface">
			<div class="container">
				<?php warmvast_section_header( 'Veelgestelde vragen', 'Over subsidie en isolatie in ' . $gemeente['naam'] ); ?>
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
		<?php warmvast_faq_schema( $faqs ); ?>

		<!-- Andere gemeenten -->
		<section class="section section--paper">
			<div class="container">
				<?php warmvast_section_header( '', 'Ook actief in andere gemeenten van regio Zaandam' ); ?>
				<ul class="gemeenten-inline">
					<?php
					foreach ( $gemeenten as $ok => $og ) :
						if ( $ok === $key ) {
							continue;
						}
						?>
						<li><a href="<?php echo esc_url( home_url( '/subsidie-' . $ok . '/' ) ); ?>"><?php echo esc_html( $og['naam'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>

		<?php
	endif;
endwhile;

get_footer();
