<?php
/**
 * Header + site navigation.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$services = warmvast_services();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script>document.documentElement.className += " wv-js";</script>
	<link rel="preload" href="<?php echo esc_url( WARMVAST_URI . '/assets/fonts/inter-latin-var.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo esc_url( WARMVAST_URI . '/assets/fonts/space-grotesk-latin-var.woff2' ); ?>" as="font" type="font/woff2" crossorigin>

	<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='7' fill='%230b6b5b'/%3E%3Cpath d='M8 22 16 9l8 13' stroke='%23fed03d' stroke-width='2.6' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="scroll-progress" id="scrollProgress" aria-hidden="true"></div>
<a class="skip-link" href="#main"><?php esc_html_e( 'Naar hoofdinhoud', 'warmvast' ); ?></a>

<header class="site-header" id="site-header" data-header>
	<div class="container site-header__inner">

		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Warmvast home">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<span class="brand__mark" aria-hidden="true">
					<svg viewBox="0 0 32 32" width="30" height="30" fill="none" aria-hidden="true">
						<rect x="1.5" y="1.5" width="29" height="29" rx="7" fill="currentColor"/>
						<path d="M8 22 L16 9 L24 22" stroke="#fed03d" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M11.5 22 L16 14.5 L20.5 22" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" opacity="0.85"/>
					</svg>
				</span>
				<span class="brand__word">Warm<span class="brand__word-alt">vast</span></span>
			<?php endif; ?>
		</a>

		<button class="nav-toggle" aria-expanded="false" aria-controls="primary-nav" data-nav-toggle>
			<span class="nav-toggle__bars" aria-hidden="true"><span></span><span></span><span></span></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'warmvast' ); ?></span>
		</button>

		<nav class="site-nav" id="primary-nav" aria-label="<?php esc_attr_e( 'Hoofdmenu', 'warmvast' ); ?>" data-nav>
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'nav__list',
						'menu_id'        => 'primary-menu',
						'depth'          => 2,
						'fallback_cb'    => 'warmvast_primary_menu_fallback',
					)
				);
			} else {
				// Hand-built menu with the Isolatie mega-dropdown for the first run.
				?>
				<ul id="primary-menu" class="nav__list">
					<li class="menu-item menu-item--has-children" data-dropdown>
						<a href="<?php echo esc_url( home_url( '/isolatie/' ) ); ?>" aria-haspopup="true" aria-expanded="false">
							Isolatie <?php warmvast_the_icon( 'chevron', 'wv-icon--sm nav__caret' ); ?>
						</a>
						<div class="dropdown">
							<div class="dropdown__grid">
								<?php foreach ( $services as $key => $s ) : ?>
									<a class="dropdown__item" href="<?php echo esc_url( $s['url'] ); ?>">
										<span class="dropdown__icon"><?php warmvast_the_icon( $s['icon'] ); ?></span>
										<span>
											<strong><?php echo esc_html( $s['label'] ); ?></strong>
											<em><?php echo esc_html( 'Vanaf ' . warmvast_rate( $s['baseRate'] ) . '/m²' ); ?></em>
										</span>
									</a>
								<?php endforeach; ?>
								<a class="dropdown__item dropdown__item--all" href="<?php echo esc_url( home_url( '/isolatie/' ) ); ?>">
									<span class="dropdown__icon"><?php warmvast_the_icon( 'arrow' ); ?></span>
									<span><strong>Alle isolatiemaatregelen</strong><em>Overzicht en keuzehulp</em></span>
								</a>
							</div>
						</div>
					</li>
					<li class="menu-item"><a href="<?php echo esc_url( home_url( '/subsidie-service/' ) ); ?>">Subsidie</a></li>
					<li class="menu-item menu-item--has-children" data-dropdown>
						<a href="<?php echo esc_url( home_url( '/over-warmvast/' ) ); ?>" aria-haspopup="true" aria-expanded="false">
							Over ons <?php warmvast_the_icon( 'chevron', 'wv-icon--sm nav__caret' ); ?>
						</a>
						<div class="dropdown dropdown--simple">
							<div class="dropdown__grid dropdown__grid--single">
								<a class="dropdown__item" href="<?php echo esc_url( home_url( '/over-warmvast/' ) ); ?>">
									<span class="dropdown__icon"><?php warmvast_the_icon( 'shield' ); ?></span>
									<span><strong>Over ons</strong><em>Wie is Warmvast</em></span>
								</a>
								<a class="dropdown__item" href="<?php echo esc_url( home_url( '/kennisbank/' ) ); ?>">
									<span class="dropdown__icon"><?php warmvast_the_icon( 'doc' ); ?></span>
									<span><strong>Kennisbank</strong><em>Uitleg over subsidie en isolatie</em></span>
								</a>
							</div>
						</div>
					</li>
					<li class="menu-item"><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
				</ul>
				<?php
			}
			?>

			<div class="site-nav__cta">
				<?php warmvast_phone_link( 'nav__phone' ); ?>
				<a class="btn btn--accent btn--sm" href="<?php echo esc_url( home_url( '/gratis-isolatiescan/' ) ); ?>" data-track="cta_click">
					Gratis isolatiescan <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?>
				</a>
			</div>
		</nav>
	</div>
</header>

<main id="main" class="site-main">
