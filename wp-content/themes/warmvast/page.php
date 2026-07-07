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
	?>
	<header class="page-hero">
		<div class="container page-hero__inner">
			<nav class="breadcrumb" aria-label="Kruimelpad">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
				<span aria-hidden="true">/</span>
				<span><?php the_title(); ?></span>
			</nav>
			<h1 class="page-hero__title"><?php the_title(); ?></h1>
			<?php if ( $subtitle ) : ?>
				<p class="page-hero__sub"><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>
		</div>
	</header>

	<article <?php post_class( 'page-body' ); ?>>
		<div class="container prose">
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
