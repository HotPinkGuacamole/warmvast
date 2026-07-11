<?php
/**
 * Kennisbank article illustrations.
 *
 * Custom inline SVG diagrams — one per article topic — instead of stock
 * photography. Each is a small technical cross-section/diagram in the site's
 * own visual language (same palette as the icon set and the hero illustration),
 * so it is guaranteed accurate, on-brand, and carries zero licensing risk.
 *
 * Keyed by post slug so no postmeta/attachment is needed. Falls back to
 * nothing (not a broken image) when a slug has no mapping.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the inline SVG illustration for a kennisbank article slug, or ''.
 *
 * @param string $slug Post slug.
 * @return string
 */
function warmvast_article_visual( $slug ) {
	$ink     = '#1b1f21';
	$primary = '#0b6b5b';
	$primary_l = '#e7f2ef';
	$accent  = '#fed03d';
	$line    = '#d6d8d0';

	$svgs = array();

	// 1) ISDE 2026: hoe werkt subsidie, flow: uitvoering -> bewijs -> subsidie.
	$svgs['isde-2026-hoe-werkt-subsidie-voor-isolatie'] = '
	<svg viewBox="0 0 640 260" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Hoe ISDE-subsidie werkt: uitvoering, bewijs, subsidie">
		<rect width="640" height="260" fill="' . $primary_l . '"/>
		<g stroke="' . $primary . '" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none">
			<path d="M70 170 140 110 210 170" />
			<path d="M85 160v50h110v-50" />
			<rect x="118" y="178" width="24" height="32" fill="' . $accent . '" stroke="none"/>
		</g>
		<path d="M240 150h60" stroke="' . $ink . '" stroke-width="3" stroke-dasharray="2 10" stroke-linecap="round"/>
		<path d="M292 140l14 10-14 10" stroke="' . $ink . '" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
		<g stroke="' . $primary . '" stroke-width="3" stroke-linejoin="round" fill="#fff">
			<rect x="335" y="95" width="90" height="110" rx="6"/>
			<path d="M353 125h54M353 145h54M353 165h34" stroke-linecap="round"/>
		</g>
		<circle cx="424" cy="185" r="20" fill="' . $accent . '" stroke="' . $ink . '" stroke-width="2.5"/>
		<path d="M415 185l6 6 12-13" stroke="' . $ink . '" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
		<path d="M460 150h60" stroke="' . $ink . '" stroke-width="3" stroke-dasharray="2 10" stroke-linecap="round"/>
		<path d="M512 140l14 10-14 10" stroke="' . $ink . '" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
		<circle cx="580" cy="150" r="38" fill="#fff" stroke="' . $primary . '" stroke-width="3"/>
		<text x="580" y="163" font-family="Space Grotesk, sans-serif" font-size="30" font-weight="700" fill="' . $primary . '" text-anchor="middle">€</text>
		<g font-family="Inter, sans-serif" font-size="13" font-weight="700" fill="' . $ink . '" text-anchor="middle">
			<text x="140" y="235">Uitvoering</text>
			<text x="380" y="235">Meldcode + foto</text>
			<text x="580" y="222">Subsidie</text>
		</g>
	</svg>';

	// 2) Waarom verdubbelt ISDE, bar chart: 1 maatregel vs 2 maatregelen (x2).
	$svgs['waarom-verdubbelt-isde-bij-twee-maatregelen'] = '
	<svg viewBox="0 0 640 260" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Bij twee maatregelen verdubbelt het ISDE-tarief per m2">
		<rect width="640" height="260" fill="' . $primary_l . '"/>
		<line x1="90" y1="205" x2="560" y2="205" stroke="' . $line . '" stroke-width="2"/>
		<g>
			<rect x="150" y="150" width="70" height="55" rx="4" fill="#fff" stroke="' . $primary . '" stroke-width="3"/>
			<text x="185" y="140" font-family="Inter, sans-serif" font-size="14" font-weight="700" fill="' . $ink . '" text-anchor="middle">1 maatregel</text>
			<text x="185" y="182" font-family="Space Grotesk, sans-serif" font-size="16" font-weight="700" fill="' . $primary . '" text-anchor="middle">1×</text>
		</g>
		<path d="M255 175h40" stroke="' . $ink . '" stroke-width="3" stroke-dasharray="2 8" stroke-linecap="round"/>
		<path d="M285 165l14 10-14 10" stroke="' . $ink . '" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
		<g>
			<rect x="345" y="85" width="90" height="120" rx="4" fill="' . $accent . '" stroke="' . $ink . '" stroke-width="3"/>
			<text x="390" y="75" font-family="Inter, sans-serif" font-size="14" font-weight="700" fill="' . $ink . '" text-anchor="middle">2 maatregelen</text>
			<text x="390" y="152" font-family="Space Grotesk, sans-serif" font-size="20" font-weight="800" fill="' . $ink . '" text-anchor="middle">2×</text>
		</g>
		<path d="M390 60v-20" stroke="' . $primary . '" stroke-width="3" stroke-linecap="round"/>
		<path d="M380 48l10-14 10 14" stroke="' . $primary . '" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
		<text x="470" y="130" font-family="Inter, sans-serif" font-size="13" fill="' . $ink . '">binnen 24</text>
		<text x="470" y="148" font-family="Inter, sans-serif" font-size="13" fill="' . $ink . '">maanden</text>
	</svg>';

	// 3) Spouwmuurisolatie, cavity wall cross-section with Rd label.
	$svgs['spouwmuurisolatie-wanneer-geschikt'] = '
	<svg viewBox="0 0 640 260" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Doorsnede van een gevulde spouwmuur">
		<rect width="640" height="260" fill="' . $primary_l . '"/>
		<g stroke="' . $ink . '" stroke-width="1.5">
			<pattern id="brick1" width="28" height="14" patternUnits="userSpaceOnUse">
				<rect width="28" height="14" fill="#c98a5e"/>
				<path d="M0 7h28M14 0v7M0 7v7M28 7v7" stroke="#a06a43" stroke-width="1"/>
			</pattern>
		</g>
		<rect x="180" y="40" width="40" height="180" fill="url(#brick1)"/>
		<rect x="220" y="40" width="70" height="180" fill="#fbe9b0"/>
		<g fill="' . $primary . '" opacity="0.55">
			<circle cx="235" cy="65" r="6"/><circle cx="252" cy="80" r="6"/><circle cx="270" cy="60" r="6"/>
			<circle cx="242" cy="100" r="6"/><circle cx="262" cy="110" r="6"/><circle cx="280" cy="95" r="6"/>
			<circle cx="232" cy="135" r="6"/><circle cx="255" cy="145" r="6"/><circle cx="275" cy="130" r="6"/>
			<circle cx="245" cy="170" r="6"/><circle cx="265" cy="180" r="6"/><circle cx="282" cy="165" r="6"/>
		</g>
		<rect x="290" y="40" width="34" height="180" fill="#e7ded1"/>
		<g stroke="' . $ink . '" stroke-width="1.2" opacity="0.5">
			<path d="M290 60h34M290 100h34M290 140h34M290 180h34"/>
		</g>
		<g stroke="' . $ink . '" stroke-width="2" stroke-linecap="round">
			<path d="M180 30v-8M324 30v-8M180 22h144"/>
		</g>
		<g font-family="Inter, sans-serif" font-size="13" fill="' . $ink . '">
			<text x="200" y="238">buiten-</text>
			<text x="200" y="252">spouwblad</text>
			<text x="230" y="16" font-weight="700">gevulde spouw</text>
			<text x="295" y="238">binnen-</text>
			<text x="295" y="252">spouwblad</text>
		</g>
		<g>
			<rect x="400" y="90" width="180" height="70" rx="8" fill="#fff" stroke="' . $primary . '" stroke-width="2"/>
			<text x="490" y="118" font-family="Space Grotesk, sans-serif" font-size="18" font-weight="700" fill="' . $primary . '" text-anchor="middle">Rd ≥ 1,1</text>
			<text x="490" y="140" font-family="Inter, sans-serif" font-size="12" fill="' . $ink . '" text-anchor="middle">minimale isolatiewaarde</text>
			<text x="490" y="155" font-family="Inter, sans-serif" font-size="12" fill="' . $ink . '" text-anchor="middle">voor ISDE-subsidie</text>
		</g>
	</svg>';

	// 4) Vloerisolatie vs bodemisolatie, side-by-side cross-sections.
	$svgs['vloerisolatie-of-bodemisolatie-verschil'] = '
	<svg viewBox="0 0 640 260" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Verschil tussen vloerisolatie en bodemisolatie">
		<rect width="640" height="260" fill="' . $primary_l . '"/>
		<g>
			<rect x="60" y="70" width="230" height="14" fill="#e7ded1" stroke="' . $ink . '" stroke-width="1.5"/>
			<rect x="60" y="84" width="230" height="16" fill="' . $primary . '" opacity="0.75"/>
			<rect x="60" y="100" width="230" height="90" fill="none" stroke="' . $line . '" stroke-width="1.5" stroke-dasharray="4 4"/>
			<text x="175" y="55" font-family="Inter, sans-serif" font-size="14" font-weight="700" fill="' . $ink . '" text-anchor="middle">Vloerisolatie</text>
			<text x="175" y="225" font-family="Inter, sans-serif" font-size="12" fill="' . $ink . '" text-anchor="middle">isolatie tegen de vloer</text>
		</g>
		<g>
			<rect x="350" y="70" width="230" height="14" fill="#e7ded1" stroke="' . $ink . '" stroke-width="1.5"/>
			<rect x="350" y="84" width="230" height="90" fill="none" stroke="' . $line . '" stroke-width="1.5" stroke-dasharray="4 4"/>
			<rect x="350" y="174" width="230" height="16" fill="' . $primary . '" opacity="0.75"/>
			<text x="465" y="55" font-family="Inter, sans-serif" font-size="14" font-weight="700" fill="' . $ink . '" text-anchor="middle">Bodemisolatie</text>
			<text x="465" y="225" font-family="Inter, sans-serif" font-size="12" fill="' . $ink . '" text-anchor="middle">isolatie op de kruipruimtebodem</text>
		</g>
		<line x1="320" y1="50" x2="320" y2="210" stroke="' . $line . '" stroke-width="2"/>
	</svg>';

	// 5) HR++ / triple glas, window cross-section with U-waarde.
	$svgs['hr-glas-subsidie-per-m2'] = '
	<svg viewBox="0 0 640 260" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="HR++ en triple glas houden warmte binnen">
		<rect width="640" height="260" fill="' . $primary_l . '"/>
		<rect x="230" y="45" width="180" height="170" rx="4" fill="#fff" stroke="' . $ink . '" stroke-width="4"/>
		<rect x="250" y="65" width="30" height="130" fill="#bfe3f5" opacity="0.7"/>
		<rect x="292" y="65" width="16" height="130" fill="#eaf6fb" opacity="0.9"/>
		<rect x="320" y="65" width="30" height="130" fill="#bfe3f5" opacity="0.7"/>
		<g stroke="' . $ink . '" stroke-width="1.5"><path d="M280 65v130M310 65v130"/></g>
		<g stroke="#d64545" stroke-width="3" fill="none" stroke-linecap="round" marker-end="none">
			<path d="M150 100c40 0 40 30 80 30"/>
			<path d="M150 160c40 0 40 -30 80 -30"/>
		</g>
		<path d="M225 100l10 4-4 10" stroke="#d64545" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
		<g stroke="#2a6fb0" stroke-width="3" fill="none" stroke-linecap="round">
			<path d="M490 90c-40 0-30 40-70 40"/>
			<path d="M490 170c-40 0-30 -40-70 -40"/>
		</g>
		<path d="M425 128l-10 4 4 10" stroke="#2a6fb0" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
		<g font-family="Inter, sans-serif" font-size="13" fill="' . $ink . '">
			<text x="150" y="90">warm binnen</text>
			<text x="490" y="80">koud buiten</text>
		</g>
		<g font-family="Space Grotesk, sans-serif" font-size="15" font-weight="700" fill="' . $primary . '" text-anchor="middle">
			<text x="320" y="235">HR++ U ≤ 1,2 · triple U ≤ 0,7 (W/m²K)</text>
		</g>
	</svg>';

	// 6) Dakisolatie: three house cross-sections. The insulation layer (green)
	//    sits in a different place per method; a warm wash (accent) shows which
	//    volume stays heated — for the zoldervloer the attic deliberately stays
	//    cold, which is the whole point of that method.
	$svgs['dakisolatie-binnen-buiten-zoldervloer'] = '
	<svg viewBox="0 0 640 260" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Drie manieren om een dak te isoleren: aan de binnenzijde, aan de buitenzijde of op de zoldervloer. De isolatielaag zit telkens op een andere plek; bij de zoldervloer blijft de zolder onverwarmd.">
		<rect width="640" height="260" fill="' . $primary_l . '"/>

		<!-- warm (heated) zones -->
		<g fill="' . $accent . '" opacity="0.18">
			<path d="M39 196 V150 L107 90 L175 150 V196 Z"/>
			<path d="M252 196 V150 L320 90 L388 150 V196 Z"/>
			<path d="M465 196 V152 H601 V196 Z"/>
		</g>

		<!-- house structures -->
		<g stroke="' . $ink . '" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" fill="none">
			<path d="M37 150 L107 74 L177 150 M37 150 V196 M177 150 V196 M33 196 H181"/>
			<path d="M250 150 L320 74 L390 150 M250 150 V196 M390 150 V196 M246 196 H394"/>
			<path d="M463 150 L533 74 L603 150 M463 150 V196 M603 150 V196 M459 196 H607"/>
		</g>
		<!-- faint attic-floor line where the attic is part of the heated volume -->
		<g stroke="' . $ink . '" stroke-width="1.4" opacity="0.35">
			<path d="M37 150 H177"/><path d="M250 150 H390"/>
		</g>

		<!-- insulation layer (green) in its method-specific position -->
		<g stroke="' . $primary . '" stroke-linecap="round" stroke-linejoin="round" fill="none">
			<path d="M51 148 L107 92 L163 148" stroke-width="8"/>
			<path d="M240 150 L320 60 L400 150" stroke-width="8"/>
			<path d="M471 150 H595" stroke-width="11"/>
		</g>

		<!-- panel dividers -->
		<g stroke="' . $line . '" stroke-width="1.5">
			<path d="M213 46 V212"/><path d="M427 46 V212"/>
		</g>

		<g font-family="Inter, sans-serif" font-weight="700" font-size="14" fill="' . $ink . '" text-anchor="middle">
			<text x="107" y="226">Binnenzijde</text>
			<text x="320" y="226">Buitenzijde</text>
			<text x="533" y="226">Zoldervloer</text>
		</g>
		<g font-family="Inter, sans-serif" font-size="11.5" fill="' . $ink . '" text-anchor="middle" opacity="0.7">
			<text x="107" y="243">onder de dakhelling</text>
			<text x="320" y="243">boven de constructie</text>
			<text x="533" y="243">zolder blijft koud</text>
		</g>
	</svg>';

	// 7) Meldcodes en fotobewijs, camera + approved code + document.
	$svgs['meldcodes-en-fotobewijs-isde'] = '
	<svg viewBox="0 0 640 260" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Meldcode en fotobewijs voor de ISDE-aanvraag">
		<rect width="640" height="260" fill="' . $primary_l . '"/>
		<g stroke="' . $primary . '" stroke-width="3" stroke-linejoin="round" fill="#fff">
			<path d="M175 90h-20l-8 10h-45a10 10 0 0 0-10 10v65a10 10 0 0 0 10 10h95a10 10 0 0 0 10-10v-65a10 10 0 0 0-10-10h-20z"/>
			<circle cx="145" cy="145" r="24"/>
		</g>
		<circle cx="145" cy="145" r="12" fill="' . $primary . '" opacity="0.25"/>
		<g stroke="' . $ink . '" stroke-width="3" stroke-linejoin="round" fill="#fff">
			<rect x="280" y="70" width="110" height="130" rx="6"/>
			<path d="M298 100h74M298 120h74M298 140h50" stroke-linecap="round"/>
		</g>
		<g stroke="' . $accent . '" stroke-width="3">
			<rect x="298" y="160" width="74" height="20" rx="3" fill="' . $accent . '" opacity="0.3"/>
			<path d="M303 170h64" stroke-dasharray="3 3"/>
		</g>
		<g stroke="' . $primary . '" stroke-width="3" stroke-linejoin="round" stroke-linecap="round" fill="none">
			<circle cx="480" cy="135" r="42" fill="' . $accent . '" fill-opacity="0.25"/>
			<path d="M462 135l13 13 27-27"/>
		</g>
		<g font-family="Inter, sans-serif" font-size="13" font-weight="700" fill="' . $ink . '" text-anchor="middle">
			<text x="145" y="225">Foto tijdens uitvoering</text>
			<text x="335" y="222">Meldcode op de bon</text>
			<text x="480" y="200">Goedgekeurd</text>
		</g>
	</svg>';

	// 8) m² berekenen, facade with measuring dimensions.
	$svgs['hoe-bereken-je-m2-isolatie'] = '
	<svg viewBox="0 0 640 260" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Zo berekent u het aantal m2 voor isolatie">
		<rect width="640" height="260" fill="' . $primary_l . '"/>
		<g stroke="' . $ink . '" stroke-width="3" stroke-linejoin="round" fill="#fff">
			<path d="M230 190V90l90-45 90 45v100z"/>
		</g>
		<rect x="295" y="120" width="34" height="34" fill="' . $primary_l . '" stroke="' . $ink . '" stroke-width="2"/>
		<rect x="345" y="140" width="26" height="50" fill="' . $primary . '" opacity="0.85"/>
		<g stroke="' . $primary . '" stroke-width="2.5" stroke-linecap="round">
			<path d="M200 90v100"/>
			<path d="M192 90h16M192 190h16"/>
		</g>
		<text x="178" y="145" font-family="Space Grotesk, sans-serif" font-size="15" font-weight="700" fill="' . $primary . '" text-anchor="middle" transform="rotate(-90 178 145)">hoogte</text>
		<g stroke="' . $primary . '" stroke-width="2.5" stroke-linecap="round">
			<path d="M230 210h180"/>
			<path d="M230 202v16M410 202v16"/>
		</g>
		<text x="320" y="232" font-family="Space Grotesk, sans-serif" font-size="15" font-weight="700" fill="' . $primary . '" text-anchor="middle">breedte</text>
		<g font-family="Space Grotesk, sans-serif" font-size="18" font-weight="700" fill="' . $ink . '" text-anchor="middle">
			<text x="500" y="140">m² = b × h</text>
		</g>
		<text x="500" y="164" font-family="Inter, sans-serif" font-size="12" fill="' . $ink . '" text-anchor="middle">min ramen &amp; deuren</text>
	</svg>';

	if ( ! isset( $svgs[ $slug ] ) ) {
		return '';
	}

	return $svgs[ $slug ];
}

/**
 * Print the article visual wrapped in a figure, or nothing if none mapped.
 *
 * @param string $slug  Post slug.
 * @param string $class Extra wrapper class.
 */
function warmvast_the_article_visual( $slug, $class = '' ) {
	$svg = warmvast_article_visual( $slug );
	if ( ! $svg ) {
		return;
	}
	echo '<figure class="article-visual ' . esc_attr( $class ) . '">' . $svg . '</figure>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG.
}

/**
 * Whether a slug has a mapped illustration (used to decide fallback layout).
 *
 * @param string $slug Post slug.
 * @return bool
 */
function warmvast_has_article_visual( $slug ) {
	return '' !== warmvast_article_visual( $slug );
}
