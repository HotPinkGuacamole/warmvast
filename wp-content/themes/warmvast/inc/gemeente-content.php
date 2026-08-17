<?php
/**
 * Zaanstreek-Waterland gemeenten content — één subsidiepagina per gemeente.
 *
 * Belangrijk: de ISDE-subsidie zelf is een landelijke RVO-regeling. Het tarief
 * per m² is overal in Nederland identiek, dus ook in elke gemeente in de
 * Zaanstreek-Waterland — er bestaat geen "gemeentelijk ISDE-tarief". Wat per
 * gemeente wél verschilt, en dus de reden is dat deze pagina's bestaan, is de
 * kern/dorpenstructuur en het karakter van de woningvoorraad (bouwperiode,
 * veel boerderijen/polderdorpen versus naoorlogse wijken), én — sinds de komst
 * van het Nationaal Isolatieprogramma (NIP) — een eigen, per gemeente
 * verschillende lokale isolatiesubsidie ("Lokale Aanpak Isolatie") die vaak
 * WÉL met de ISDE te combineren is. Zie het 'gemeentesubsidie'-veld per
 * gemeente hieronder.
 *
 * Inwonertallen zijn afgeronde, indicatieve orde-van-grootte cijfers
 * ("circa") — geen exacte CBS-peildatum, bewust niet als harde
 * marketingclaim gebruikt.
 *
 * Let op — gemeentelijke herindeling: Beemster is sinds 1 januari 2022 GEEN
 * zelfstandige gemeente meer; het fuseerde met Purmerend en is nu een kern
 * (met Middenbeemster als hoofdkern) van gemeente Purmerend. De regio
 * Zaanstreek-Waterland bestaat daardoor nu uit 7 gemeenten: Edam-Volendam,
 * Landsmeer, Oostzaan, Purmerend, Waterland, Wormerland en Zaanstad (vgl. de
 * indeling van GGD Zaanstreek-Waterland).
 *
 * Elk 'gemeentesubsidie'-blok is gebaseerd op de officiële gemeentelijke
 * regeling zoals gevonden op de website van de gemeente zelf (of de
 * uitvoerder, bv. Duurzaam Bouwloket / Woonwijzerwinkel) op de datum in
 * 'gecontroleerd'. Deze regelingen hebben een eigen, vaak beperkt budget
 * ("op = op") en wijzigen regelmatig — controleer daarom altijd de bron-URL
 * voor de actuele stand vóórdat een bezoeker hierop een beslissing baseert.
 * Nooit een bedrag of voorwaarde verzinnen of "bijwerken" zonder een nieuwe
 * check bij de bron.
 *
 * Warmvast is werkzaam in heel Noord-Holland en Noord-Zuid-Holland (zie
 * WARMVAST_REGION in inc/config.php); deze gemeentepagina's dekken specifiek
 * regio Zaandam (Zaanstreek-Waterland), waar Warmvast extra ervaring heeft.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * De 7 gemeenten van de regio Zaanstreek-Waterland, inclusief hun eigen
 * NIP-gefinancierde lokale isolatiesubsidie ("gemeentesubsidie").
 *
 * @return array<string,array<string,mixed>>
 */
function warmvast_zaanstreek_gemeenten() {
	return array(

		'zaanstad' => array(
			'naam'     => 'Zaanstad',
			'inwoners' => 'circa 155.000',
			'kernen'   => array( 'Zaandam', 'Koog aan de Zaan', 'Zaandijk', 'Wormerveer', 'Krommenie', 'Assendelft', 'Westzaan' ),
			'positie'  => 'Hart van de Zaanstreek, aan de Zaan, direct ten noorden van Amsterdam',
			'karakter' => 'Zaanstad groeide door zijn industriële verleden — houtzagerijen, voedingsmiddelenfabrieken en de kenmerkende, groen geverfde houten Zaanse huizen langs de Zaan — uit tot de grootste gemeente van de regio. Rond de historische kernen liggen brede naoorlogse wijken zoals Poelenburg, Peldersveld en Rosmolenwijk, en nieuwbouw in Saendelft (Assendelft). De jaren 60- en 70-portiek- en flatwoningen hebben vaak een matig gevulde spouw; de oudere houten woningen langs de Zaan hebben doorgaans geen spouwconstructie en vragen om een andere aanpak.',
			'aandacht' => 'In de naoorlogse wijken van Zaanstad is spouwmuurisolatie meestal de snelste stap met het meeste directe effect; bij de karakteristieke Zaanse huizen zonder spouw ligt de winst vaker bij dak- en vloerisolatie. Twee maatregelen tegelijk laten uitvoeren verdubbelt het ISDE-tarief per m².',
			'focus'    => array( 'spouw', 'vloer' ),
			'gemeentesubsidie' => array(
				'naam'          => 'Subsidieregeling Lokale Aanpak Isolatie Zaanstad 2024–2028',
				'bedrag_max'    => '€ 1.500',
				'bedrag'        => 'Tot 50% van de kosten, max. € 1.500 per woning (+ € 250 extra bij natuurinclusieve/duurzame isolatiematerialen)',
				'bedrag_laag'   => 'Bij energiearmoede (huishoudinkomen tot 140% van het sociaal minimum): tot 100% van de kosten, max. € 4.000',
				'voorwaarden'   => 'Eigenaar-bewoner, woning gebouwd vóór 1995, WOZ-waarde max. € 477.000 (peildatum 2022), energielabel D of lager, óf minimaal 2 slecht geïsoleerde bouwdelen (gevel, ramen, dak, vloer).',
				'looptijd'      => 'Aan te vragen t/m 31 december 2028',
				'bron_url'      => 'https://www.zaanstad.nl/direct-regelen/subsidies/beschikbare-subsidies/lokale-aanpak-isolatie-zaanstad/',
				'gecontroleerd' => '17 augustus 2026',
			),
		),

		'wormerland' => array(
			'naam'     => 'Wormerland',
			'inwoners' => 'circa 16.000',
			'kernen'   => array( 'Wormer', 'Jisp', 'Neck', 'Oostknollendam' ),
			'positie'  => 'Direct ten noorden van Zaanstad, tussen de Zaan en Purmerend',
			'karakter' => 'Wormerland bestaat uit het grotere Wormer, met lintbebouwing langs de Zaan, en de kleinere veenweidedorpen Jisp, Neck en Oostknollendam. Het buitengebied kent verspreide boerderijen en vrijstaande woningen, vaak met een grote, matig geïsoleerde kapconstructie en een kruipruimte zonder bodemisolatie.',
			'aandacht' => 'Bij boerderijen en vrijstaande woningen in de kleinere kernen van Wormerland leveren dak- en vloerisolatie doorgaans het meeste op, simpelweg omdat het te isoleren oppervlak groot is.',
			'focus'    => array( 'dak', 'vloer' ),
			'gemeentesubsidie' => array(
				'naam'          => 'Isolatiesubsidie Wormerland (Nationaal Isolatieprogramma)',
				'bedrag_max'    => '€ 30 /m²',
				'bedrag'        => 'Tot € 30 per m², afhankelijk van de maatregel, de geschiktheid van de woning en het beschikbare budget',
				'bedrag_laag'   => '',
				'voorwaarden'   => 'Particuliere woningeigenaar, WOZ-waarde max. € 496.000 (peildatum 2022), energielabel D, E, F of G — of label C/onbekend met minimaal 2 slecht geïsoleerde bouwdelen. De regeling is beperkt tot maximaal 602 woningen (budgetplafond, op = op).',
				'looptijd'      => 'Loopt zolang het budget (602 woningen) niet is uitgeput',
				'bron_url'      => 'https://wormerland.nl/DuurzaamWormerland/subsidies-isoleren-en-aardgasvrij',
				'gecontroleerd' => '17 augustus 2026',
			),
		),

		'oostzaan' => array(
			'naam'     => 'Oostzaan',
			'inwoners' => 'circa 9.500',
			'kernen'   => array( 'Oostzaan' ),
			'positie'  => 'Tussen Zaanstad en Amsterdam-Noord, in het veenweidegebied Oostzanerveld',
			'karakter' => 'Oostzaan is de kleinste gemeente van de regio: een lintdorp met overwegend laagbouw — vrijstaande en twee-onder-een-kapwoningen — aangevuld met enkele naoorlogse rijwoningen. De ligging op veengrond betekent dat kruipruimtes vaker gevoelig zijn voor vocht, wat vloerisolatie extra relevant maakt.',
			'aandacht' => 'In Oostzaan is vloerisolatie een veelgevraagde maatregel tegen kou en vocht uit de kruipruimte; spouwmuurisolatie is de logische aanvulling voor het verdubbelde ISDE-tarief.',
			'focus'    => array( 'vloer', 'spouw' ),
			'gemeentesubsidie' => array(
				'naam'          => 'Isolatieactie gemeente Oostzaan (Nationaal Isolatieprogramma)',
				'bedrag_max'    => '€ 1.750',
				'bedrag'        => 'Tot € 1.750 per woning',
				'bedrag_laag'   => 'Bij (risico op) energiearmoede: tot € 4.000',
				'voorwaarden'   => 'WOZ-waarde max. € 568.000, energielabel D of lager (of vergelijkbaar slecht geïsoleerd).',
				'looptijd'      => 'Nieuwe isolatieactie georganiseerd in 2026',
				'bron_url'      => 'https://oostzaan.nl/Duurzaam',
				'gecontroleerd' => '17 augustus 2026',
			),
		),

		'purmerend' => array(
			'naam'     => 'Purmerend',
			'inwoners' => 'circa 92.000',
			'kernen'   => array( 'Purmerend', 'Middenbeemster', 'Zuidoostbeemster', 'Westbeemster', 'Noordbeemster' ),
			'positie'  => 'Ten oosten van de Zaanstreek, aan het Noordhollandsch Kanaal; sinds de fusie van 1 januari 2022 met de Beemster als achtste kern',
			'karakter' => 'Purmerend is van oudsher een marktstad en groeide vanaf de jaren 70 en 80 sterk met grootschalige uitbreidingswijken zoals Purmer en, later, Weidevenne — met woningen die uiteenlopen van jaren 70-portiekwoningen met een matig gevulde spouw tot recentere wijken waar vooral het glas nog voor verbetering vatbaar is. De voormalige gemeente Beemster, sinds 2022 met Middenbeemster als hoofdkern onderdeel van Purmerend, is een rationeel verkavelde 17e-eeuwse droogmakerij en UNESCO Werelderfgoed, met verspreide boerderijen langs rechte polderlinten waarbij het behoud van de karakteristieke uitstraling meespeelt bij de uitvoering.',
			'aandacht' => 'In de oudere stadswijken van Purmerend is spouwmuurisolatie meestal de eerste stap en HR++ glas de meest zichtbare comfortverbetering in de latere uitbreidingswijken; bij de boerderijen in de Beemster-kernen leveren dak- en vloerisolatie doorgaans het meeste op. Twee maatregelen combineren verdubbelt het ISDE-tarief per m².',
			'focus'    => array( 'spouw', 'glas', 'dak' ),
			'gemeentesubsidie' => array(
				'naam'          => 'Nationaal Isolatieprogramma via gemeente Purmerend',
				'bedrag_max'    => '€ 1.700',
				'bedrag'        => '€ 1.700 (stand februari 2026)',
				'bedrag_laag'   => 'Voor koopstarters met een starterslening: € 2.450',
				'voorwaarden'   => 'Eigenaar-bewoner, WOZ-waarde max. € 477.000 (peildatum 2024), energielabel D, E, F of G, óf minimaal 2 slecht geïsoleerde bouwdelen. Geldt voor de hele gemeente Purmerend, dus ook voor de Beemster-kernen.',
				'looptijd'      => 'Budget loopt tot 2028, maar is beperkt: wie het eerst komt, wie het eerst maalt (op = op)',
				'bron_url'      => 'https://purmerend.nl/aanvragen/subsidies/duurzaamheid-en-wonen/isolatieprogramma-met-rijkssubsidie',
				'gecontroleerd' => '17 augustus 2026',
			),
		),

		'edam-volendam' => array(
			'naam'     => 'Edam-Volendam',
			'inwoners' => 'circa 36.000',
			'kernen'   => array( 'Edam', 'Volendam', 'Beets', 'Kwadijk', 'Middelie', 'Oosthuizen', 'Warder' ),
			'positie'  => 'Aan het IJsselmeer, ten noordoosten van Purmerend',
			'karakter' => 'De historische kernen Edam en Volendam hebben karakteristieke, deels beschermde bebouwing; de kleinere polderdorpen van het voormalige Zeevang (Beets, Kwadijk, Middelie, Oosthuizen, Warder) zijn landelijker, met boerderijen en vrijstaande woningen. Bij de oudere woningen in Edam en Volendam is uitvoering vaak maatwerk vanwege de monumentale of beeldbepalende status.',
			'aandacht' => 'In de polderdorpen leveren dak- en vloerisolatie bij boerderijen en vrijstaande woningen meestal het meeste op; in de historische kernen van Edam en Volendam stemmen wij de uitvoering af op welstands- en monumenteneisen.',
			'focus'    => array( 'dak', 'vloer' ),
			'gemeentesubsidie' => array(
				'naam'          => 'Eenmalige subsidie voor het isoleren van uw woning — gemeente Edam-Volendam',
				'bedrag_max'    => '€ 1.550',
				'bedrag'        => 'Tot € 30 per m², max. € 1.550 per woning',
				'bedrag_laag'   => '',
				'voorwaarden'   => 'Eigenaar-bewoner, WOZ-waarde max. € 429.300 (peildatum 1 januari 2022), energielabel D, E, F of G, óf minimaal 2 slecht geïsoleerde bouwdelen. Spouwmuurisolatie alleen via de natuurvriendelijke methode (i.v.m. beschermde diersoorten).',
				'looptijd'      => 'Let op: het budget is vrijwel uitgeput — op 7 augustus 2026 was nog circa € 19.700 beschikbaar van het totaal van ruim € 1 miljoen; aanvragen kan tot 1 oktober 2026 of zolang er budget is',
				'bron_url'      => 'https://www.edam-volendam.nl/eenmalige-subsidie-voor-het-isoleren-van-uw-woning',
				'gecontroleerd' => '17 augustus 2026',
			),
		),

		'waterland' => array(
			'naam'     => 'Waterland',
			'inwoners' => 'circa 17.500',
			'kernen'   => array( 'Monnickendam', 'Broek in Waterland', 'Marken', 'Ilpendam', 'Watergang', 'Zuiderwoude' ),
			'positie'  => 'Tussen Amsterdam en Purmerend, aan het Markermeer/IJsselmeer',
			'karakter' => 'Waterland bestaat uit kleinschalige, veelal beschermde dorpskernen — Monnickendam, Broek in Waterland en het voormalige eiland Marken — omringd door veenweidepolders met verspreide boerderijen en vrijstaande woningen. Beschermde dorpsgezichten vragen om extra zorgvuldigheid bij de uitvoering.',
			'aandacht' => 'Bij de boerderijen en vrijstaande woningen in het buitengebied van Waterland zijn dak- en vloerisolatie doorgaans de grootste hefboom; in de beschermde kernen stemmen wij de uitvoering af op de welstands- en monumenteneisen.',
			'focus'    => array( 'dak', 'vloer' ),
			'gemeentesubsidie' => array(
				'naam'          => 'Uitvoeringsregeling isolatiesubsidie gemeente Waterland 2024',
				'bedrag_max'    => '€ 2.000',
				'bedrag'        => 'Max. € 2.000 (incl. btw) bij een WOZ-waarde tot € 390.000',
				'bedrag_laag'   => 'Voor ontvangers van de energietoeslag: max. € 4.000 (incl. btw) bij een WOZ-waarde tot € 429.300',
				'voorwaarden'   => 'Woning gebouwd in 1992 of eerder, eigen voordeur op de begane grond (geen appartement, woonboot of stacaravan), minimaal 2 slecht geïsoleerde bouwdelen (vloer/bodem, gevel/spouw, dak/zolder of glas). Subsidie is eenmalig per woning aan te vragen.',
				'looptijd'      => 'Doorlopend zolang budget beschikbaar is',
				'bron_url'      => 'https://lokaleregelgeving.overheid.nl/CVDR726538/1',
				'gecontroleerd' => '17 augustus 2026',
			),
		),

		'landsmeer' => array(
			'naam'     => 'Landsmeer',
			'inwoners' => 'circa 11.700',
			'kernen'   => array( 'Landsmeer', 'Den Ilp', 'Purmerland' ),
			'positie'  => 'Veenweidegebied van Waterland, tussen Oostzaan en Amsterdam-Noord',
			'karakter' => 'Landsmeer bestaat uit drie kernen met elk hun eigen historie: het dorp Landsmeer zelf en de kleinere polderdorpen Den Ilp en Purmerland, sinds 1991 samengevoegd tot één gemeente. Het landschap van uitgestrekte polders, sloten en dijken kent veel vrijstaande en twee-onder-een-kapwoningen en verspreide boerderijen, vaak met een matig geïsoleerd dak en een kruipruimte zonder bodemisolatie.',
			'aandacht' => 'Bij de vrijstaande woningen en boerderijen in Landsmeer, Den Ilp en Purmerland leveren dak- en vloerisolatie doorgaans het meeste op; combineren met spouwmuurisolatie verdubbelt het ISDE-tarief per m².',
			'focus'    => array( 'dak', 'vloer' ),
			'gemeentesubsidie' => array(
				'naam'          => 'Subsidieregeling Lokale aanpak Isolatie Landsmeer 2025–2026',
				'bedrag_max'    => '€ 1.850',
				'bedrag'        => 'Tot € 1.850 per woning',
				'bedrag_laag'   => 'Voor lage inkomens: tot € 4.000 per woning',
				'voorwaarden'   => 'Eigenaar-bewoner, WOZ-waarde max. € 666.000, woning doorgaans met energielabel D, E, F of G.',
				'looptijd'      => 'Regeling in 2026 verlengd door de gemeente',
				'bron_url'      => 'https://nip.duurzaambouwloket.nl/landsmeer',
				'gecontroleerd' => '17 augustus 2026',
			),
		),

	);
}

/**
 * Shared FAQ items for every gemeente-pagina. The first item explicitly
 * separates the (landelijk, gelijk) ISDE-tarief from the gemeente's OWN
 * NIP-gefinancierde lokale isolatiesubsidie, using the real figures from
 * 'gemeentesubsidie' so the answer is specific instead of a generic hedge.
 *
 * @param array<string,mixed> $gemeente Eén item uit warmvast_zaanstreek_gemeenten().
 * @return array<int,array{q:string,a:string}>
 */
function warmvast_gemeente_faqs( $gemeente ) {
	$naam = $gemeente['naam'];
	$gs   = isset( $gemeente['gemeentesubsidie'] ) ? $gemeente['gemeentesubsidie'] : null;

	$combine_answer = 'Nee, dat zijn twee losse regelingen. De ISDE is landelijk en overal in Nederland gelijk. ' . $naam . ' heeft daarnaast, gefinancierd vanuit het landelijke Nationaal Isolatieprogramma (NIP), een eigen lokale isolatiesubsidie met eigen voorwaarden en een eigen (beperkt) budget.';
	if ( $gs ) {
		$combine_answer .= ' Op dit moment ("' . esc_html( $gs['naam'] ) . '", gecontroleerd op ' . esc_html( $gs['gecontroleerd'] ) . '): ' . esc_html( $gs['bedrag'] ) . '.';
		if ( ! empty( $gs['bedrag_laag'] ) ) {
			$combine_answer .= ' ' . esc_html( $gs['bedrag_laag'] ) . '.';
		}
		$combine_answer .= ' Voorwaarden: ' . esc_html( $gs['voorwaarden'] ) . ' Deze gemeentelijke subsidie is in de regel te combineren met de landelijke ISDE, zolang het totale subsidiebedrag de werkelijke kosten niet overschrijdt. Het budget van gemeentelijke regelingen raakt regelmatig op ("op is op") en de voorwaarden wijzigen — controleer daarom altijd de actuele stand op <a href="' . esc_url( $gs['bron_url'] ) . '" target="_blank" rel="noopener">de website van gemeente ' . esc_html( $naam ) . '</a> vóórdat u een aanvraag doet.';
	} else {
		$combine_answer .= ' Informeer naar de actuele voorwaarden bij de gemeente ' . $naam . ' zelf, want dit verandert regelmatig.';
	}

	return array(
		array(
			'q' => 'Is de ISDE-subsidie in ' . $naam . ' anders dan elders in Nederland, en kan ik dit combineren met een subsidie van de gemeente?',
			'a' => $combine_answer,
		),
		array(
			'q' => 'Werkt Warmvast ook in ' . $naam . '?',
			'a' => 'Ja. Warmvast is actief in Noord-Holland en Noord-Zuid-Holland, met specifieke ervaring in regio Zaandam (Zaanstreek-Waterland) en dus ook in ' . $naam . '. De technische opname, uitvoering en subsidiebegeleiding verlopen op dezelfde manier als elders in ons werkgebied.',
		),
		array(
			'q' => 'Hoe weet ik welke maatregel voor mijn woning in ' . $naam . ' het meeste oplevert?',
			'a' => 'De gratis woningscan geeft op basis van uw adres direct een indicatie van geschikte maatregelen en de ISDE-subsidie. De technische opname bevestigt vervolgens de exacte situatie en het definitieve advies — en of u ook voor de lokale gemeentelijke isolatiesubsidie in aanmerking komt.',
		),
	);
}
