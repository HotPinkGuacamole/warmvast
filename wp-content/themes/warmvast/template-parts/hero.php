<?php
/**
 * Hero — the one hero on every page. Rendered via warmvast_the_hero( $args );
 * see that function in inc/template-tags.php for the full parameter contract,
 * and docs/DESIGN-SYSTEM.md for the design rules this file exists to enforce.
 *
 * Two branches, not two templates: 'home' renders the existing, pixel-frozen
 * dark hero (its markup, classes and mechanics are UNCHANGED from before this
 * component existed -- only now parametrized instead of hand-duplicated
 * across front-page.php and template-scan.php). Every other variant renders
 * through the shared light system below it. Both branches are reached from
 * this one file so a change to slot ORDER only ever needs to happen once.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $warmvast_hero_args;
$a = $warmvast_hero_args;

$is_dark      = 'home' === $a['variant'];
$is_narrow    = 'artikel' === $a['variant'];
$has_aside    = ! empty( $a['aside'] );
$has_icon_row = ! $is_dark && $a['eyebrow'] && $a['icon'];

$hero_class   = 'hero';
if ( $is_dark ) {
	$hero_class .= ' hero--dark';
}
if ( $has_aside ) {
	$hero_class .= ' hero--has-aside';
}
if ( $is_narrow ) {
	$hero_class .= ' hero--narrow';
}
?>
<header class="<?php echo esc_attr( $hero_class ); ?>">

<?php if ( $is_dark ) : ?>

	<span class="hero__orb hero__orb--1" aria-hidden="true"></span>
	<span class="hero__orb hero__orb--2" aria-hidden="true"></span>
	<div class="hero__thermal" aria-hidden="true"></div>
	<div class="container hero__inner">
		<div class="hero__copy">
			<?php if ( $a['eyebrow'] ) : ?>
				<p class="kicker kicker--light"><?php echo esc_html( $a['eyebrow'] ); ?></p>
			<?php endif; ?>

			<?php if ( $a['title_sr_only'] ) : ?>
				<h1 class="screen-reader-text"><?php echo esc_html( $a['title'] ); ?></h1>
			<?php else : ?>
				<h1 class="hero__title"><?php echo wp_kses_post( $a['title'] ); ?></h1>
			<?php endif; ?>

			<?php if ( $a['lead'] ) : ?>
				<p class="hero__sub"><?php echo esc_html( $a['lead'] ); ?></p>
			<?php endif; ?>

			<?php if ( $a['actions'] || $a['phone'] ) : ?>
				<div class="hero__actions">
					<?php
					// home's two buttons don't fit the uniform {label,style,url}
					// schema every other variant uses (one carries btn--lg
					// btn--sheen and an icon, the other neither) -- rather than
					// invent flags to cover a case that exists on exactly 2
					// pages, this ONE branch takes pre-built markup, consistent
					// with home already being the system's one documented
					// exception. Raw echo, not wp_kses_post: this is trusted,
					// theme-authored markup containing an inline <svg> icon,
					// and wp_kses_post's default allowlist has no svg/path/
					// stroke-* -- it silently ate the arrow icon here, same as
					// warmvast_icon()'s own output is echoed raw everywhere else.
					foreach ( $a['actions'] as $action ) {
						echo $action; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme's own literal markup, built in the calling template, not user input.
					}
					if ( $a['phone'] ) {
						warmvast_phone_link( 'hero__phone' );
					}
					?>
				</div>
			<?php endif; ?>

			<?php if ( $a['trust'] ) : ?>
				<div class="hero__trust">
					<?php foreach ( $a['trust'] as $item ) : ?>
						<span><?php warmvast_the_icon( 'check', 'wv-icon--sm' ); ?> <?php echo wp_kses_post( $item ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $has_aside ) : ?>
			<div class="hero__scan">
				<?php warmvast_render_hero_aside( $a['aside'] ); ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $a['usps'] ) : ?>
		<!-- floating USP overview: fully contained inside the hero, so it's always
		     visible on first paint without scrolling, whatever the viewport height -->
		<section class="usp-bar" aria-label="Waarom Warmvast">
			<span class="usp-bar__notch usp-bar__notch--left" aria-hidden="true"></span>
			<span class="usp-bar__notch usp-bar__notch--right" aria-hidden="true"></span>
			<div class="container">
				<div class="usp-bar__grid">
					<?php foreach ( $a['usps'] as $u ) : ?>
						<div class="usp">
							<span class="usp__icon"><?php warmvast_the_icon( $u[0] ); ?></span>
							<span class="usp__text"><strong><?php echo esc_html( $u[1] ); ?></strong><em><?php echo wp_kses_post( $u[2] ); ?></em></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

<?php else : ?>

	<div class="container hero__grid">
		<div class="hero__body">

			<?php if ( $a['breadcrumb'] ) : ?>
				<?php warmvast_the_breadcrumb( $a['breadcrumb'] ); ?>
			<?php endif; ?>

			<?php if ( $a['eyebrow'] ) : ?>
				<?php if ( $has_icon_row ) : ?>
					<div class="hero__eyebrow-row">
						<span class="hero__icon"><?php warmvast_the_icon( $a['icon'] ); ?></span>
						<p class="kicker"><?php echo esc_html( $a['eyebrow'] ); ?></p>
					</div>
				<?php else : ?>
					<p class="kicker"><?php echo esc_html( $a['eyebrow'] ); ?></p>
				<?php endif; ?>
			<?php endif; ?>

			<h1 class="hero__title"><?php echo wp_kses_post( $a['title'] ); ?></h1>

			<?php if ( $a['lead'] ) : ?>
				<p class="hero__lead"><?php echo esc_html( $a['lead'] ); ?></p>
			<?php endif; ?>

			<?php if ( $a['meta'] ) : ?>
				<p class="hero__meta"><?php echo wp_kses_post( $a['meta'] ); ?></p>
			<?php endif; ?>

			<?php if ( $a['actions'] || $a['phone'] ) : ?>
				<div class="hero__actions">
					<?php
					foreach ( $a['actions'] as $action ) {
						warmvast_the_hero_action( $action );
					}
					if ( $a['phone'] ) {
						warmvast_phone_link( 'hero__phone' );
					}
					?>
				</div>
			<?php endif; ?>

			<?php if ( $a['trust'] ) : ?>
				<div class="hero__trust">
					<?php foreach ( $a['trust'] as $item ) : ?>
						<span><?php warmvast_the_icon( 'check', 'wv-icon--sm' ); ?> <?php echo wp_kses_post( $item ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		</div>

		<?php if ( $has_aside ) : ?>
			<aside class="hero__aside">
				<?php warmvast_render_hero_aside( $a['aside'] ); ?>
			</aside>
		<?php endif; ?>
	</div>

<?php endif; ?>

</header>
