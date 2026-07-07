<?php
/**
 * Template tags & shared view helpers for Warmvast.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inline SVG icon set. Stroke-based, currentColor, 24x24 grid.
 *
 * @param string $name  Icon key.
 * @param string $class Extra CSS class.
 * @return string SVG markup.
 */
function warmvast_icon( $name, $class = '' ) {
	$paths = array(
		'arrow'   => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'check'   => '<path d="M20 6 9 17l-5-5"/>',
		'phone'   => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.5-1.1a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2Z"/>',
		'mail'    => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/>',
		'shield'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>',
		'ruler'   => '<path d="M3 15 15 3l6 6L9 21z"/><path d="M7 11l2 2M11 7l2 2M15 11l1 1"/>',
		'camera'  => '<path d="M14.5 4h-5L8 6H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-4l-1.5-2Z"/><circle cx="12" cy="13" r="3.5"/>',
		'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'map'     => '<path d="M12 21s7-6.5 7-12a7 7 0 1 0-14 0c0 5.5 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/>',
		'wall'    => '<path d="M3 5h18v14H3z"/><path d="M3 10h18M3 14.5h18M8 5v5m8-5v5M12 10v4.5M8 14.5V19m8-4.5V19"/>',
		'floor'   => '<path d="M3 8h18M3 8l3-4h12l3 4M5 8v12h14V8"/><path d="M9 12h6M9 16h6"/>',
		'glass'   => '<rect x="4" y="3" width="16" height="18" rx="1"/><path d="M4 9h16M12 3v18"/>',
		'roof'    => '<path d="M2 12 12 4l10 8"/><path d="M5 10.5V20h14v-9.5"/><path d="M9 20v-5h6v5"/>',
		'thermo'  => '<path d="M14 14.8V4a2 2 0 0 0-4 0v10.8a4 4 0 1 0 4 0Z"/><path d="M12 9v6"/>',
		'euro'    => '<circle cx="12" cy="12" r="9"/><path d="M15 8.5a4 4 0 0 0-6 3.5 4 4 0 0 0 6 3.5M7.5 11h5M7.5 13h4"/>',
		'spark'   => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M18 6l-2.5 2.5M8.5 15.5 6 18"/>',
		'chevron' => '<path d="m6 9 6 6 6-6"/>',
		'phone-check' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6"/>',
		'doc'     => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M9 13h6M9 17h6"/>',
	);

	$d = isset( $paths[ $name ] ) ? $paths[ $name ] : '';
	$class = $class ? ' ' . esc_attr( $class ) : '';

	return '<svg class="wv-icon' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $d . '</svg>';
}

/**
 * Print an icon.
 */
function warmvast_the_icon( $name, $class = '' ) {
	echo warmvast_icon( $name, $class ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG.
}

/**
 * Canonical service data, built on top of the ISDE tariffs so numbers stay in sync.
 *
 * @return array<string,array<string,mixed>>
 */
function warmvast_services() {
	$rates = warmvast_isde_rates();

	$meta = array(
		'spouw' => array(
			'icon'     => 'wall',
			'problem'  => 'Koude gevels en snel afkoelende kamers.',
			'solution' => 'Snel minder warmteverlies via de gevel, bij een lege of slecht gevulde spouw.',
		),
		'vloer' => array(
			'icon'     => 'floor',
			'problem'  => 'Koude vloer en kou uit de kruipruimte.',
			'solution' => 'Warmere begane grond en minder vocht en tocht van onderaf.',
		),
		'glas'  => array(
			'icon'     => 'glass',
			'problem'  => 'Kou en tocht bij ramen, enkel of oud dubbel glas.',
			'solution' => 'Minder warmteverlies en meer comfort bij ramen met HR++ glas.',
		),
		'dak'   => array(
			'icon'     => 'roof',
			'problem'  => 'Groot warmteverlies via een ongeïsoleerde kap of zolder.',
			'solution' => 'Pak het grootste warmteverlies bovenin de woning structureel aan.',
		),
	);

	$services = array();
	foreach ( $rates as $key => $rate ) {
		$services[ $key ] = array_merge(
			$rate,
			$meta[ $key ],
			array(
				'subsidy_line' => sprintf( 'ISDE vanaf %s/m² bij één maatregel.', warmvast_rate( $rate['baseRate'] ) ),
				'url'          => home_url( '/' . $rate['slug'] . '/' ),
			)
		);
	}
	return $services;
}

/**
 * Section header block: kicker + title (+ optional intro).
 */
function warmvast_section_header( $kicker, $title, $intro = '', $align = 'left' ) {
	$align_class = 'center' === $align ? ' section-head--center' : '';
	echo '<div class="section-head' . esc_attr( $align_class ) . '" data-reveal>';
	if ( $kicker ) {
		echo '<p class="kicker">' . esc_html( $kicker ) . '</p>';
	}
	echo '<h2 class="section-head__title">' . wp_kses_post( $title ) . '</h2>';
	if ( $intro ) {
		echo '<p class="section-head__intro">' . wp_kses_post( $intro ) . '</p>';
	}
	echo '</div>';
}

/**
 * Primary CTA button linking to the scan.
 *
 * @param string $label  Button label.
 * @param string $style  primary|secondary|ghost|accent.
 * @param string $target Anchor or URL. Defaults to the scan on the current/home page.
 * @param string $event  data-track event name.
 */
function warmvast_cta( $label, $style = 'primary', $target = '#warmvast-scan', $event = 'cta_click' ) {
	$icon = warmvast_icon( 'arrow', 'wv-icon--end' );
	printf(
		'<a class="btn btn--%1$s" href="%2$s" data-track="%3$s">%4$s%5$s</a>',
		esc_attr( $style ),
		esc_url( $target ),
		esc_attr( $event ),
		esc_html( $label ),
		$icon // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}

/**
 * Phone / email helpers.
 */
function warmvast_phone_link( $class = '' ) {
	printf(
		'<a class="%1$s" href="tel:%2$s" data-track="phone_click">%3$s%4$s</a>',
		esc_attr( $class ),
		esc_attr( WARMVAST_PHONE_RAW ),
		warmvast_icon( 'phone' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html( WARMVAST_PHONE )
	);
}

function warmvast_email_link( $class = '' ) {
	printf(
		'<a class="%1$s" href="mailto:%2$s" data-track="email_click">%3$s%4$s</a>',
		esc_attr( $class ),
		esc_attr( WARMVAST_EMAIL ),
		warmvast_icon( 'mail' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html( WARMVAST_EMAIL )
	);
}

/**
 * Render a row of star icons for a 0–5 rating.
 *
 * @param float $rating Rating value.
 * @return string
 */
function warmvast_stars( $rating ) {
	$full = (int) floor( $rating );
	$html = '<span class="stars" role="img" aria-label="' . esc_attr( sprintf( '%s van 5 sterren', number_format_i18n( $rating, 1 ) ) ) . '">';
	for ( $i = 1; $i <= 5; $i++ ) {
		$on   = $i <= $full;
		$html .= '<svg class="star' . ( $on ? ' star--on' : '' ) . '" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 18.9 6.1 20.5l1.2-6.5L2.5 9.4l6.6-.9z"/></svg>';
	}
	$html .= '</span>';
	return $html;
}

/**
 * The scan's live subsidy computed server-side for a default preview so the
 * hero never shows an empty €0 on first paint.
 *
 * @return string Euro string.
 */
function warmvast_preview_amount() {
	// Default preview: spouw 65 m2 + vloer 55 m2 (doubled) — a realistic terraced house.
	$rates = warmvast_isde_rates();
	$total = ( 65 * $rates['spouw']['baseRate'] * 2 ) + ( 55 * $rates['vloer']['baseRate'] * 2 );
	return warmvast_euro( $total );
}
