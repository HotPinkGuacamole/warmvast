<?php
/**
 * Template Name: Kennisbank
 *
 * Lists the latest posts. Assign to the /kennisbank/ page.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<header class="page-hero">
	<div class="container page-hero__inner">
		<nav class="breadcrumb" aria-label="Kruimelpad">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span aria-hidden="true">/</span><span>Kennisbank</span>
		</nav>
		<p class="kicker">Kennisbank</p>
		<h1 class="page-hero__title">Alles over ISDE-subsidie en isolatie</h1>
		<p class="page-hero__sub">Feitelijke uitleg over subsidie, maatregelen en uitvoering. Geschreven om u te helpen begrijpen en beslissen.</p>
	</div>
</header>

<section class="section section--surface">
	<div class="container">
		<?php
		$q = new WP_Query(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 12,
				'no_found_rows'  => true,
			)
		);
		if ( $q->have_posts() ) :
			?>
			<div class="post-grid">
				<?php
				while ( $q->have_posts() ) :
					$q->the_post();
					?>
					<article <?php post_class( 'card card--link card--post' ); ?>>
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
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<p>De eerste artikelen verschijnen binnenkort. Wilt u nu al weten wat isoleren u oplevert?</p>
			<?php warmvast_cta( 'Start de gratis isolatiescan', 'accent', home_url( '/gratis-isolatiescan/' ) ); ?>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
