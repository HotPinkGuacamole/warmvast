<?php
/**
 * Fallback template.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<div class="container section">
	<?php if ( have_posts() ) : ?>
		<div class="section-head">
			<h1 class="section-head__title"><?php single_post_title(); ?></h1>
		</div>
		<div class="post-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'card card--post' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<a class="card--post__media" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'warmvast_card' ); ?></a>
					<?php endif; ?>
					<div class="card--post__body">
						<h2 class="card--post__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p><?php echo esc_html( get_the_excerpt() ); ?></p>
						<a class="link-arrow" href="<?php the_permalink(); ?>">Lees verder <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?></a>
					</div>
				</article>
				<?php
			endwhile;
			?>
		</div>
		<div class="pagination"><?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?></div>
	<?php else : ?>
		<div class="section-head">
			<h1 class="section-head__title">Niets gevonden</h1>
			<p class="section-head__intro">Er is nog geen inhoud op deze pagina.</p>
		</div>
	<?php endif; ?>
</div>
<?php
get_footer();
