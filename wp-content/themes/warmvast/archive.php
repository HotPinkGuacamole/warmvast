<?php
/**
 * Archive (kennisbank listing).
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

// wp_strip_all_tags on the description too, matching the title just below --
// the_archive_description() can echo block-editor markup (paragraphs, etc.)
// that a single-line hero lead isn't built to hold.
$archive_description = wp_strip_all_tags( get_the_archive_description() );

warmvast_the_hero(
	array(
		'variant' => 'bedrijf',
		'eyebrow' => 'Kennisbank',
		'title'   => wp_strip_all_tags( get_the_archive_title() ),
		'lead'    => $archive_description ? $archive_description : null,
	)
);
?>

<div class="container section">
	<?php if ( have_posts() ) : ?>
		<div class="post-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'card card--post' ); ?>>
					<?php $slug = get_post_field( 'post_name', get_the_ID() ); ?>
					<?php if ( warmvast_has_article_visual( $slug ) ) : ?>
						<a class="card--post__media" href="<?php the_permalink(); ?>"><?php warmvast_the_article_visual( $slug, 'article-visual--card' ); ?></a>
					<?php elseif ( has_post_thumbnail() ) : ?>
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
		<p>Er zijn nog geen artikelen. Kom binnenkort terug.</p>
	<?php endif; ?>
</div>
<?php
get_footer();
