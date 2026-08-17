<?php
/**
 * Long-form content for the four service pages. Kept in PHP so the CRO
 * structure and the ISDE numbers stay consistent and version-controlled.
 *
 * @package Warmvast
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the detail content for a single service key, merged with its tariffs.
 *
 * @param string $key spouw|vloer|glas|dak.
 * @return array<string,mixed>|null
 */
function warmvast_service_detail( $key ) {
	$services = warmvast_services();
	if ( ! isset( $services[ $key ] ) ) {
		return null;
	}

	$content = array(

		'spouw' => array(
			'h1'        => 'Spouwmuurisolatie voor minder warmteverlies via uw gevel',
			'intro'     => 'Bij een lege of slecht gevulde spouw lekt er veel warmte weg via de gevel. Na-isolatie van de spouwmuur is snel uitgevoerd, kosteneffectief en levert direct meer comfort op.',
			'symptoms'  => array(
				'Buitenmuren voelen koud aan de binnenzijde.',
				'Kamers koelen na het stoken snel weer af.',
				'Vochtplekken of schimmel op koude gevelvlakken.',
				'Een hoge energierekening zonder duidelijke oorzaak.',
			),
			'technical' => 'Spouwmuurisolatie brengt isolatiemateriaal aan in de luchtspouw tussen de binnen- en buitenmuur. Warmvast controleert eerst de spouwbreedte, de vulling, eventueel vocht en de staat van de gevel. De isolatie wordt via een net boorpatroon in de voegen ingebracht. Voor subsidie moet de na-isolatie voldoen aan de eisen voor materiaal en isolatiewaarde (Rd-waarde).',
			'suitable'  => array(
				'Woningen met een aantoonbaar lege of deels gevulde spouw.',
				'Een spouwbreedte die voldoende is om te isoleren.',
				'Een gevel zonder ernstige vocht- of scheurproblemen.',
				'Bouwjaren waarbij een spouwconstructie gebruikelijk is.',
			),
			'process'   => array(
				array( 'Inspectie', 'Endoscopisch onderzoek van de spouw en controle op vocht en vervuiling.' ),
				array( 'Voorbereiding', 'Boorpatroon uitzetten en de gevel gereedmaken.' ),
				array( 'Inblazen', 'Isolatiemateriaal onder druk gelijkmatig in de spouw brengen.' ),
				array( 'Afwerking', 'Boorgaten dichten en meldcodes en foto’s vastleggen voor uw ISDE-dossier.' ),
			),
			'faqs'      => array(
				array( 'q' => 'Is mijn spouw geschikt voor na-isolatie?', 'a' => 'Dat blijkt uit de technische opname. We meten de spouwbreedte en controleren op vocht en vervuiling. Alleen bij een geschikte, schone spouw isoleren we.' ),
				array( 'q' => 'Hoe lang duurt spouwmuurisolatie?', 'a' => 'Bij een gemiddelde woning is het inblazen doorgaans in een dagdeel klaar. De exacte tijd hangt af van de geveloppervlakte en bereikbaarheid.' ),
			),
		),

		'vloer' => array(
			'h1'        => 'Vloerisolatie voor een warmere en drogere begane grond',
			'intro'     => 'Een koude vloer en tocht uit de kruipruimte maken de begane grond ongezellig en verhogen uw stookkosten. Vloerisolatie houdt de warmte binnen en de kou onder de vloer.',
			'symptoms'  => array(
				'Koude voeten, ook met de verwarming aan.',
				'Tocht die van onderaf via de vloer omhoog komt.',
				'Een vochtige of muffe kruipruimte.',
				'Grote temperatuurverschillen tussen boven en beneden.',
			),
			'technical' => 'Vloerisolatie brengt een isolatielaag aan tegen de onderzijde van de begane grondvloer of in de kruipruimte. Warmvast controleert de kruipruimtehoogte, vocht, ventilatie en bereikbaarheid. Let op het verschil tussen vloerisolatie (tegen de vloer) en bodemisolatie (op de bodem van de kruipruimte); beide hebben eigen toepassingen en voorwaarden.',
			'suitable'  => array(
				'Een kruipruimte die toegankelijk en hoog genoeg is om in te werken.',
				'Een begane grondvloer boven een onverwarmde ruimte of kruipruimte.',
				'Geen structureel vochtprobleem dat eerst opgelost moet worden.',
			),
			'process'   => array(
				array( 'Opname', 'Controle van kruipruimtehoogte, vocht en ventilatie.' ),
				array( 'Advies', 'Keuze tussen vloer- of bodemisolatie op basis van uw situatie.' ),
				array( 'Uitvoering', 'Aanbrengen van de isolatie met behoud van voldoende ventilatie.' ),
				array( 'Dossier', 'Meldcodes en fotobewijs vastleggen voor de subsidieaanvraag.' ),
			),
			'faqs'      => array(
				array( 'q' => 'Wat is het verschil tussen vloer- en bodemisolatie?', 'a' => 'Vloerisolatie zit tegen de onderkant van de vloer, bodemisolatie ligt op de bodem van de kruipruimte. Welke het beste past hangt af van de hoogte en staat van uw kruipruimte.' ),
				array( 'q' => 'Moet mijn kruipruimte droog zijn?', 'a' => 'Een beheersbaar vochtniveau en goede ventilatie zijn belangrijk. Bij structureel vocht adviseren we dat eerst aan te pakken.' ),
			),
		),

		'glas'  => array(
			'h1'        => 'HR++ glas voor meer comfort en minder warmteverlies bij ramen',
			'intro'     => 'Enkel glas en oud dubbel glas laten veel warmte los en geven een koude uitstraling bij het raam. HR++ glas isoleert aanzienlijk beter en verhoogt direct het comfort.',
			'symptoms'  => array(
				'Kou- of tochtgevoel als u bij het raam zit.',
				'Condens aan de binnenzijde van de ruit.',
				'Geluid van buiten dringt makkelijk door.',
				'Enkel glas of dubbel glas van vóór de HR-generatie.',
			),
			'technical' => 'HR++ glas heeft een coating en een gasvulling tussen de glasbladen die warmteverlies sterk beperken. Warmvast controleert de staat van de kozijnen, de sponningdiepte en de ventilatie. Panelen en deuren hebben aparte voorwaarden en moeten voor subsidie vaak in combinatie met glas worden uitgevoerd.',
			'suitable'  => array(
				'Woningen met enkel glas of verouderd dubbel glas.',
				'Kozijnen die het zwaardere HR++ glas kunnen dragen.',
				'Voldoende ventilatiemogelijkheden na plaatsing.',
			),
			'process'   => array(
				array( 'Inmeten', 'Controle van kozijnen, sponning en glasoppervlak.' ),
				array( 'Advies', 'Glaskeuze en aandacht voor ventilatie en eventuele panelen.' ),
				array( 'Plaatsing', 'Verwijderen van het oude glas en plaatsen van HR++ glas.' ),
				array( 'Dossier', 'Meldcodes en foto’s vastleggen voor uw ISDE-aanvraag.' ),
			),
			'faqs'      => array(
				array( 'q' => 'Komt HR++ glas in aanmerking voor ISDE?', 'a' => 'Ja, mits het voldoet aan de eisen en de minimale oppervlakte per aanvraag wordt gehaald. De opname bevestigt de details.' ),
				array( 'q' => 'Kan mijn bestaande kozijn HR++ glas dragen?', 'a' => 'Dat beoordelen we tijdens de opname. HR++ glas is zwaarder; soms is aanpassing van de sponning of het kozijn nodig.' ),
			),
		),

		'dak'   => array(
			'h1'        => 'Dakisolatie om groot warmteverlies structureel aan te pakken',
			'intro'     => 'Warme lucht stijgt op: bij een ongeïsoleerd dak of zolder verdwijnt daar veel energie. Dakisolatie pakt het grootste warmteverlies bovenin de woning aan.',
			'symptoms'  => array(
				'Een snikhete zolder in de zomer en koud in de winter.',
				'Grote warmteverliezen via de kap of vlieringvloer.',
				'Tocht en temperatuurschommelingen op de bovenverdieping.',
				'Een ongeïsoleerde kap, zolder of plat dak.',
			),
			'technical' => 'Dakisolatie kan aan de binnen- of buitenzijde van de kap, of via de zolder-/vlieringvloer. Warmvast beoordeelt vocht, ventilatie, de dakopbouw en mogelijke koudebruggen. Let op: dakisolatie en een direct daarboven of -onder gelegen zoldervloer tellen niet dubbel voor de subsidie.',
			'suitable'  => array(
				'Een ongeïsoleerde of slecht geïsoleerde kap of zolder.',
				'Een dakconstructie die de gekozen isolatiemethode toelaat.',
				'Voldoende ruimte voor ventilatie om vochtproblemen te voorkomen.',
			),
			'process'   => array(
				array( 'Opname', 'Beoordeling van dakopbouw, vocht en ventilatie.' ),
				array( 'Advies', 'Keuze tussen binnen-, buiten- of zoldervloerisolatie.' ),
				array( 'Uitvoering', 'Aanbrengen van de isolatie met aandacht voor koudebruggen.' ),
				array( 'Dossier', 'Meldcodes en fotobewijs vastleggen voor uw ISDE-aanvraag.' ),
			),
			'faqs'      => array(
				array( 'q' => 'Isoleren aan de binnen- of buitenzijde?', 'a' => 'Buitenzijde (bij vervanging van de dakbedekking) geeft een strak resultaat zonder koudebruggen; binnenzijde is vaak sneller en goedkoper. We adviseren op basis van uw dak.' ),
				array( 'q' => 'Telt zoldervloerisolatie ook mee?', 'a' => 'Ja, isolatie van de zolder- of vlieringvloer is een geldige maatregel, maar telt niet dubbel met dakisolatie op dezelfde plek.' ),
			),
		),
	);

	if ( ! isset( $content[ $key ] ) ) {
		return null;
	}

	return array_merge( $services[ $key ], $content[ $key ] );
}
