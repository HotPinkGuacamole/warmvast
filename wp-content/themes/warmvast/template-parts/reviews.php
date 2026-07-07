<?php
/**
 * Reviews section. Auto-hides if there are no review items.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$reviews = warmvast_reviews();
if ( empty( $reviews['items'] ) ) {
	return;
}
?>
<section class="section section--paper reviews">
	<div class="container">
		<div class="reviews__head">
			<div class="reviews__score">
				<span class="reviews__num" data-countup="<?php echo esc_attr( $reviews['rating'] ); ?>" data-decimals="1"><?php echo esc_html( number_format_i18n( $reviews['rating'], 1 ) ); ?></span>
				<span>
					<?php echo warmvast_stars( $reviews['rating'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<em><?php echo esc_html( sprintf( 'Gemiddeld op basis van %d beoordelingen', $reviews['count'] ) ); ?><?php echo $reviews['source'] ? esc_html( ' · ' . $reviews['source'] ) : ''; ?></em>
				</span>
			</div>
			<?php warmvast_section_header( 'Ervaringen', 'Woningeigenaren over Warmvast' ); ?>
		</div>

		<div class="grid grid--3 reviews__grid">
			<?php foreach ( $reviews['items'] as $r ) : ?>
				<figure class="card review" data-reveal>
					<?php echo warmvast_stars( isset( $r['stars'] ) ? $r['stars'] : 5 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<blockquote><?php echo esc_html( $r['text'] ); ?></blockquote>
					<figcaption>
						<span class="review__avatar" aria-hidden="true"><?php echo esc_html( mb_substr( $r['name'], 0, 1 ) ); ?></span>
						<span><strong><?php echo esc_html( $r['name'] ); ?></strong><em><?php echo esc_html( $r['place'] ); ?></em></span>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php
// Only emit AggregateRating schema when the data is verified real.
if ( ! empty( $reviews['verified'] ) && ! warmvast_seo_plugin_active() ) {
	$schema = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'Product',
		'name'            => 'Woningisolatie door Warmvast',
		'aggregateRating' => array(
			'@type'       => 'AggregateRating',
			'ratingValue' => $reviews['rating'],
			'reviewCount' => $reviews['count'],
		),
	);
	echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
}
