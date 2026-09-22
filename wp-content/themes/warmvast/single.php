<?php
/**
 * Single post (kennisbank article).
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

while ( have_posts() ) :
	the_post();
	?>
	<?php
	// No eyebrow: the breadcrumb already carries "Kennisbank" as the category,
	// so a duplicate label above the h1 would say it a second time. The date
	// and read time take the meta slot instead of a lead -- they're a fact
	// line, not prose.
	warmvast_the_hero(
		array(
			'variant'    => 'artikel',
			'breadcrumb' => array(
				array( 'label' => 'Home', 'url' => home_url( '/' ) ),
				array( 'label' => 'Kennisbank', 'url' => home_url( '/kennisbank/' ) ),
				array( 'label' => get_the_title() ),
			),
			'title'      => get_the_title(),
			'meta'       => esc_html( get_the_date() ) . ' &middot; ' . esc_html( ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 ) ) . ' min lezen',
		)
	);
	?>

	<article <?php post_class( 'page-body' ); ?>>
		<div class="container prose">
			<?php warmvast_the_article_visual( get_post_field( 'post_name', get_the_ID() ) ); ?>
			<?php the_content(); ?>
		</div>

		<div class="container">
			<div class="article-cta">
				<div>
					<h2>Bereken uw isolatie- en subsidievoordeel</h2>
					<p>Gebruik de gratis Warmvast isolatiescan en zie direct een ISDE-indicatie voor uw woning.</p>
				</div>
				<?php warmvast_cta( 'Start gratis isolatiescan', 'accent', home_url( '/gratis-isolatiescan/' ) ); ?>
			</div>
		</div>
	</article>
	<?php
endwhile;

get_footer();
