<?php
/**
 * Header + site navigation.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$services  = warmvast_services();
$gemeenten = warmvast_zaanstreek_gemeenten();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php warmvast_gtm_head(); ?>
	<script>document.documentElement.className += " wv-js";</script>
	<link rel="preload" href="<?php echo esc_url( WARMVAST_URI . '/assets/fonts/inter-latin-var.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo esc_url( WARMVAST_URI . '/assets/fonts/space-grotesk-latin-var.woff2' ); ?>" as="font" type="font/woff2" crossorigin>

	<?php warmvast_favicon_links(); ?>
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php warmvast_gtm_body(); ?>
<?php wp_body_open(); ?>

<div class="scroll-progress" id="scrollProgress" aria-hidden="true"></div>
<a class="skip-link" href="#main"><?php esc_html_e( 'Naar hoofdinhoud', 'warmvast' ); ?></a>

<header class="site-header" id="site-header" data-header>
	<div class="container site-header__inner">

		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<img class="brand__logo" src="<?php echo warmvast_asset( '/assets/img/warmvast-logo-horizontal-black.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper escapes. ?>" width="730" height="212" alt="Warmvast Isolatie" fetchpriority="high" decoding="async">
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
						<a href="<?php echo esc_url( home_url( '/gemeentes/' ) ); ?>" aria-haspopup="true" aria-expanded="false">
							Gemeentes <?php warmvast_the_icon( 'chevron', 'wv-icon--sm nav__caret' ); ?>
						</a>
						<div class="dropdown dropdown--wide">
							<p class="dropdown__lead">ISDE-subsidie per gemeente in regio Zaandam</p>
							<div class="dropdown__links">
								<?php foreach ( $gemeenten as $gkey => $g ) : ?>
									<a href="<?php echo esc_url( home_url( '/subsidie-' . $gkey . '/' ) ); ?>"><?php echo esc_html( $g['naam'] ); ?></a>
								<?php endforeach; ?>
								<span class="dropdown__links-all">
									<a href="<?php echo esc_url( home_url( '/gemeentes/' ) ); ?>">Alle gemeentes bekijken →</a>
								</span>
							</div>
						</div>
					</li>
					<li class="menu-item menu-item--has-children" data-dropdown>
						<a href="<?php echo esc_url( home_url( '/over-warmvast/' ) ); ?>" aria-haspopup="true" aria-expanded="false">
							Bedrijf <?php warmvast_the_icon( 'chevron', 'wv-icon--sm nav__caret' ); ?>
						</a>
						<div class="dropdown">
							<div class="dropdown__grid">
								<a class="dropdown__item" href="<?php echo esc_url( home_url( '/over-warmvast/' ) ); ?>">
									<span class="dropdown__icon"><?php warmvast_the_icon( 'shield' ); ?></span>
									<span><strong>Over ons</strong><em>Wie is Warmvast</em></span>
								</a>
								<a class="dropdown__item" href="<?php echo esc_url( home_url( '/kennisbank/' ) ); ?>">
									<span class="dropdown__icon"><?php warmvast_the_icon( 'doc' ); ?></span>
									<span><strong>Kennisbank</strong><em>Uitleg over subsidie en isolatie</em></span>
								</a>
								<a class="dropdown__item" href="<?php echo esc_url( home_url( '/zakelijk/' ) ); ?>">
									<span class="dropdown__icon"><?php warmvast_the_icon( 'building' ); ?></span>
									<span><strong>Zakelijk &amp; VvE</strong><em>Meerdere woningen of complexen</em></span>
								</a>
								<a class="dropdown__item" href="<?php echo esc_url( home_url( '/ons-werk/' ) ); ?>">
									<span class="dropdown__icon"><?php warmvast_the_icon( 'image' ); ?></span>
									<span><strong>Ons werk</strong><em>Afgeronde projecten</em></span>
								</a>
								<a class="dropdown__item dropdown__item--all" href="<?php echo esc_url( home_url( '/kwaliteit-en-garantie/' ) ); ?>">
									<span class="dropdown__icon"><?php warmvast_the_icon( 'award' ); ?></span>
									<span><strong>Kwaliteit &amp; garantie</strong><em>Hoe wij kwaliteit waarborgen</em></span>
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
				<a class="btn btn--accent btn--sm" href="<?php echo esc_url( home_url( '/gratis-isolatiescan/' ) ); ?>" data-track="cta_click">
					Gratis isolatiescan <?php warmvast_the_icon( 'arrow', 'wv-icon--end' ); ?>
				</a>
			</div>
		</nav>
	</div>
</header>

<main id="main" class="site-main">
