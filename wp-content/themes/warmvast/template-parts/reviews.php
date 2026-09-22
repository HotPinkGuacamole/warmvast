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
// `verified` gates the VISIBLE section, not just the schema below. Publishing
// invented reviews or an invented average is an oneerlijke handelspraktijk
// under Dutch/EU consumer law (the Omnibus Directive names fake reviews
// explicitly), not a cosmetic placeholder -- exactly the reasoning already
// applied to WARMVAST_HOMES_INSULATED and warmvast_certificeringen(). So this
// behaves like those: nothing renders until the data is real, rather than
// shipping sample copy to visitors. Set verified => true in inc/config.php
// once `items`, `rating` and `count` are genuine and the block returns.
if ( empty( $reviews['items'] ) || empty( $reviews['verified'] ) ) {
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
			<?php warmvast_section_header( '', 'Woningeigenaren over Warmvast' ); ?>
		</div>

		<div class="grid grid--3 reviews__grid">
			<?php
			$review_url = ! empty( $reviews['url'] ) ? $reviews['url'] : '';
			foreach ( $reviews['items'] as $r ) :
				?>
				<figure class="card review<?php echo $review_url ? ' card--link' : ''; ?>" data-reveal>
					<?php
					// The stretched link is placed FIRST, not last: a <figcaption>
					// is only valid HTML as the first or last child of <figure>,
					// and it needs to stay last (see below) -- position:absolute
					// means its own box doesn't care where it sits in the DOM.
					if ( $review_url ) :
						?>
						<a class="review__link" href="<?php echo esc_url( $review_url ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( 'Lees de volledige review van ' . $r['name'] . ' op Google (opent in nieuw tabblad)' ); ?>"></a>
					<?php endif; ?>
					<?php echo warmvast_stars( isset( $r['stars'] ) ? $r['stars'] : 5 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<blockquote><?php echo esc_html( $r['text'] ); ?></blockquote>
					<?php if ( $review_url ) : ?>
						<span class="review__cta">Lees volledige review op Google &#8599;</span>
					<?php endif; ?>
					<figcaption>
						<span class="review__avatar" aria-hidden="true"><?php echo esc_html( mb_substr( $r['name'], 0, 1 ) ); ?></span>
						<span><strong><?php echo esc_html( $r['name'] ); ?></strong><?php echo ! empty( $r['place'] ) ? '<em>' . esc_html( $r['place'] ) . '</em>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_html above. ?></span>
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
	// ratingValue is a PHP float (4.9, etc.) and some hosts (this MAMP
	// setup included: serialize_precision=100 in its Apache-served php.ini,
	// vs. PHP's own sane default of -1) print that as the float's full
	// ~50-digit binary expansion instead of "4.9" -- 4.9 cannot be
	// represented exactly in binary, and serialize_precision controls how
	// many of those digits json_encode is willing to show, not the value
	// itself. Scoped ini_set/restore around just this encode call, rather
	// than trusting whatever the host's global php.ini happens to say.
	$prev_precision = ini_set( 'serialize_precision', '-1' );
	$json           = wp_json_encode( $schema );
	if ( false !== $prev_precision ) {
		ini_set( 'serialize_precision', $prev_precision );
	}
	echo "\n" . '<script type="application/ld+json">' . $json . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode output above.
}
