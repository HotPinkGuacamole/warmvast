<?php
/**
 * Default page template. Content-driven pages (services, subsidie, legal)
 * render their body via the editor; a lightweight page hero frames them.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

while ( have_posts() ) :
	the_post();
	$subtitle = get_post_meta( get_the_ID(), 'wv_subtitle', true );
	warmvast_the_hero(
		array(
			'variant'    => 'bedrijf',
			'breadcrumb' => array(
				array( 'label' => 'Home', 'url' => home_url( '/' ) ),
				array( 'label' => get_the_title() ),
			),
			'title'      => get_the_title(),
			'lead'       => $subtitle ? $subtitle : null,
		)
	);
	?>

	<article <?php post_class( 'page-body' ); ?>>
		<div class="container prose">
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
