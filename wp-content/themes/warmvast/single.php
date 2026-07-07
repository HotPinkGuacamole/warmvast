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
	<header class="page-hero page-hero--article">
		<div class="container page-hero__inner">
			<nav class="breadcrumb" aria-label="Kruimelpad">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
				<span aria-hidden="true">/</span>
				<a href="<?php echo esc_url( home_url( '/kennisbank/' ) ); ?>">Kennisbank</a>
				<span aria-hidden="true">/</span>
				<span><?php the_title(); ?></span>
			</nav>
			<p class="kicker">Kennisbank</p>
			<h1 class="page-hero__title"><?php the_title(); ?></h1>
			<p class="page-hero__meta"><?php echo esc_html( get_the_date() ); ?> &middot; <?php echo esc_html( ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 ) ); ?> min lezen</p>
		</div>
	</header>

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
