# WARMVAST Blueprint Voor Nieuwe WordPress-Site

Doel van dit document: gebruik dit als startprompt voor Claude of een andere coding agent om vanaf nul een nieuwe WARMVAST-site te bouwen.

## 1. Projectcontext

WARMVAST is een Nederlands isolatiebedrijf dat particuliere woningeigenaren helpt met woningisolatie en ISDE-subsidie. De site moet vanaf de eerste versie gericht zijn op leadgeneratie via een gratis isolatiescan.

De site moet voelen als een technisch sterk isolatiebedrijf, niet als een generieke aannemer. De kernpositionering:

> Isolatie op basis van feiten, niet van beloftes.

WARMVAST kijkt naar een woning als een systeem: waar lekt energie weg, welke isolatiemaatregel lost dat op, hoeveel m2 gaat het om, wat is de subsidie-indicatie en wat levert het praktisch op voor comfort en energiekosten?

## 2. Techstack

Gebruik deze stack:

- Local development: MAMP
- CMS: WordPress
- Theme: custom WordPress theme of lichtgewicht custom childless theme
- PHP: compatibel met gangbare MAMP PHP-versies
- Database: MySQL/MariaDB via MAMP
- Form handling: Formspree via AJAX `fetch`
- Frontend: semantic HTML, vanilla CSS, vanilla JavaScript
- Geen page builder als harde afhankelijkheid
- Geen Avada, Elementor, Divi of andere builder aannemen
- Geen React/Vue/Svelte tenzij expliciet gevraagd
- Geen zware plugin voor formulierlogica

Aanbevolen repo-structuur:

```text
warmvast/
  wp-content/
    themes/
      warmvast/
        style.css
        functions.php
        index.php
        front-page.php
        page.php
        single.php
        archive.php
        header.php
        footer.php
        template-parts/
          hero.php
          isolatiescan.php
          service-card.php
          subsidy-calculator.php
          faq.php
        assets/
          css/
            main.css
          js/
            main.js
            isolatiescan.js
          img/
```

Als de agent liever een plugin maakt voor de scan, gebruik:

```text
wp-content/
  plugins/
    warmvast-isolatiescan/
      warmvast-isolatiescan.php
      assets/
        css/isolatiescan.css
        js/isolatiescan.js
```

Voorkeur: zet de scan als herbruikbare shortcode of block-like template-part op, zodat deze op meerdere pagina's kan worden geplaatst.

## 3. Brand & Tone Of Voice

### 3.1 Merkpersoonlijkheid

WARMVAST is:

- Analytisch
- Technisch onderlegd
- Resultaatgericht
- Transparant
- Rustig overtuigend
- Landelijk inzetbaar
- Gericht op woningeigenaren die eerst willen begrijpen en daarna willen beslissen

### 3.2 Schrijfstijl

Gebruik korte, concrete zinnen. Vermijd marketinghype. Claim alleen wat uitlegbaar is.

Gebruik woorden als:

- meetbaar
- m2
- isolatiewaarde
- warmteverlies
- comfort
- subsidie-indicatie
- meldcode
- fotobewijs
- technische opname
- uitvoering door vakmensen
- ISDE

Vermijd:

- "gratis geld"
- "100% subsidiegarantie"
- "de goedkoopste"
- "de subsidie stopt bijna" als nep-schaarste
- vage termen als "duurzame totaaloplossing" zonder uitleg

### 3.3 Kernboodschappen

Primaire claim:

> WARMVAST helpt u isoleren op basis van feiten: technische opname, helder m2-overzicht en een realistische ISDE-indicatie.

Secundaire claims:

- Minder warmteverlies, meer comfort en een lagere energierekening.
- Combineer maatregelen slim en benut de ISDE-systematiek optimaal.
- Wij leggen de benodigde m2, meldcodes en foto's vast voor uw subsidieaanvraag.
- Geen verkooppraatjes, maar een concreet advies voor uw woning.

## 4. Conversiedoel

De primaire conversie is niet "contact opnemen", maar:

> Start de gratis isolatiescan.

Waarom:

- Een scan voelt laagdrempeliger dan een offerte.
- De bezoeker krijgt direct waarde via een subsidie-indicatie.
- WARMVAST krijgt betere leaddata: woningtype, maatregelen, oppervlaktes, postcode, contactgegevens.
- Leads met twee of meer maatregelen kunnen commercieel worden geprioriteerd.

Secundaire conversies:

- Bel WARMVAST
- Vraag offerte aan
- Bekijk ISDE-subsidie
- Lees dienstpagina

## 5. Ideale Sitearchitectuur

### 5.1 Hoofdnavigatie

Header:

- Home
- Isolatie
- Subsidie
- Kennisbank
- Over WARMVAST
- Contact
- CTA-button: Gratis Isolatiescan

Dropdown onder "Isolatie":

- Spouwmuurisolatie
- Vloerisolatie
- Glasisolatie HR++
- Dakisolatie
- Alle isolatiemaatregelen

Footer:

- Gratis isolatiescan
- Subsidie service
- Spouwmuurisolatie
- Vloerisolatie
- HR++ glas
- Dakisolatie
- Kennisbank
- Privacyverklaring
- Algemene voorwaarden
- Contact

### 5.2 Pagina's

Maak minimaal deze pagina's:

| Pagina | Slug | Doel |
|---|---|---|
| Home | `/` | Hoofdconversie naar isolatiescan |
| Isolatie | `/isolatie/` | Overzicht en keuzehulp |
| Spouwmuurisolatie | `/spouwmuurisolatie/` | SEO + high-intent lead |
| Vloerisolatie | `/vloerisolatie/` | SEO + high-intent lead |
| Glasisolatie HR++ | `/glasisolatie-hr/` | SEO + high-intent lead |
| Dakisolatie | `/dakisolatie/` | SEO + high-intent lead |
| Subsidie Service | `/subsidie-service/` | ISDE-uitleg en vertrouwen |
| Gratis Isolatiescan | `/gratis-isolatiescan/` | Dedicated landingpage |
| Kennisbank | `/kennisbank/` | SEO-clusters |
| Over WARMVAST | `/over-warmvast/` | Vertrouwen |
| Contact | `/contact/` | Contact en lokale gegevens |
| Privacyverklaring | `/privacyverklaring/` | Juridisch |
| Algemene voorwaarden | `/algemene-voorwaarden/` | Juridisch |

## 6. Homepage Blueprint

### 6.1 Hero

H1:

> Isolatie op basis van feiten, niet van beloftes.

Subcopy:

> Ontdek waar uw woning warmte verliest en welke isolatiemaatregelen technisch en financieel het meeste opleveren. Inclusief directe ISDE-indicatie en advies op basis van uw situatie.

CTA primair:

> Start gratis isolatiescan

CTA secundair:

> Bekijk subsidievoordeel

Trustline:

> Binnen 24 uur reactie. Heel Nederland. Spouw, vloer, glas en dak.

Hero mag een echte woningisolatie/foto-achtige sfeer hebben, maar moet niet stockachtig worden. Toon bij voorkeur vakwerk, thermische inspectie, kruipruimte, spouwboring of glasplaatsing.

### 6.2 Direct Onder Hero: Isolatiescan

Plaats de multi-step scan direct onder de hero of als rechterkolom naast de hero op desktop. Op mobiel komt de scan onder de herotekst.

De scan is de kern van de site.

### 6.3 Probleemherkenning

Sectietitel:

> Herkent u dit in uw woning?

Kaarten:

- De woonkamer koelt snel af.
- De vloer voelt koud aan.
- Er is tocht of kou bij ramen.
- De energierekening blijft hoog.
- De kruipruimte is vochtig.
- U weet niet welke subsidie mogelijk is.

CTA:

> Laat WARMVAST meekijken

### 6.4 Dienstenblok

Vier kaarten:

1. Spouwmuurisolatie
   - Snel minder warmteverlies via de gevel.
   - Geschikt bij een lege of slecht gevulde spouw.
   - ISDE vanaf EUR 5,25/m2 bij een maatregel.

2. Vloerisolatie
   - Warmere begane grond en minder kou uit de kruipruimte.
   - Extra relevant bij koude voeten of vochtige kruipruimte.
   - ISDE vanaf EUR 5,50/m2 bij een maatregel.

3. HR++ glasisolatie
   - Minder warmteverlies en minder kou bij ramen.
   - Relevant bij enkel glas of oud dubbel glas.
   - ISDE vanaf EUR 25/m2 bij een maatregel.

4. Dakisolatie
   - Pak groot warmteverlies via het dak structureel aan.
   - Relevant bij ongeisoleerde kap of zolder.
   - ISDE vanaf EUR 16,25/m2 bij een maatregel.

Elke kaart krijgt:

- Probleem
- Oplossing
- Subsidie-indicatie
- CTA: "Check deze maatregel"

### 6.5 ISDE Verdubbelaar

Sectietitel:

> Twee maatregelen? Dan kan uw ISDE-tarief per m2 verdubbelen.

Uitleg:

> De ISDE werkt met vaste bedragen per m2. Laat u meer dan een isolatiemaatregel uitvoeren binnen de voorwaarden, dan verdubbelt het subsidiebedrag voor isolatie. De WARMVAST-scan laat dit direct zien.

Voorbeeld:

- Alleen spouw: 65 m2 x EUR 5,25 = EUR 341
- Spouw + vloer: spouwtarief EUR 10,50/m2 en vloertarief EUR 11/m2

Disclaimer:

> Indicatie onder voorbehoud van RVO-voorwaarden en beoordeling.

### 6.6 Werkwijze

Titel:

> Zo werkt isoleren met WARMVAST

Stappen:

1. Gratis isolatiescan
2. Telefonische check
3. Technische opname
4. Heldere offerte met m2 en maatregelen
5. Vakkundige uitvoering
6. Subsidiedossier met meldcodes en fotobewijs

### 6.7 Vertrouwen

Toon bewijsblokken:

- Technische opname voor uitvoering
- Heldere m2-berekening
- Materiaalkeuze op basis van woning
- Fotobewijs voor subsidie
- Reactie binnen 24 uur
- Heel Nederland

Gebruik echte testimonials pas als ze beschikbaar zijn. Gebruik geen nep-reviews.

### 6.8 FAQ

Minimaal:

- Kom ik in aanmerking voor ISDE?
- Waarom verdubbelt subsidie bij twee maatregelen?
- Moet ik zelf subsidie aanvragen?
- Kan ik subsidie krijgen voor doe-het-zelf isolatie?
- Hoe betrouwbaar is de online berekening?
- Wanneer neemt WARMVAST contact op?

## 7. Servicepagina Blueprint

Elke servicepagina gebruikt dezelfde CRO-structuur:

1. Hero met duidelijke maatregel
2. Symptomen/problemen
3. Technische uitleg
4. Geschiktheid woning
5. ISDE-tarieven en voorwaarden
6. Uitvoeringsproces
7. Veelgestelde vragen
8. Inline isolatiescan met maatregel voorgeselecteerd

### 7.1 Spouwmuurisolatie

H1:

> Spouwmuurisolatie voor minder warmteverlies via uw gevel

Belangrijke punten:

- Geschikt bij lege of slecht geisoleerde spouw.
- Controle op spouwbreedte, vervuiling, vocht en gevelstaat.
- Uitvoering via boorpatroon in voegen.
- Na-isolatie moet voldoen aan materiaal- en isolatiewaarden voor subsidie.

ISDE 2026:

- Basis: EUR 5,25/m2
- Bij twee of meer maatregelen: EUR 10,50/m2
- Minimaal 10 m2
- Maximaal 170 m2

### 7.2 Vloerisolatie

H1:

> Vloerisolatie voor een warmere en drogere begane grond

Belangrijke punten:

- Relevant bij koude vloer, tocht uit kruipruimte of vocht.
- Controle op kruipruimtehoogte, vocht, ventilatie en bereikbaarheid.
- Let op verschil tussen vloerisolatie en bodemisolatie.

ISDE 2026:

- Basis vloerisolatie: EUR 5,50/m2
- Bij twee of meer maatregelen: EUR 11/m2
- Minimaal 20 m2
- Maximaal 130 m2

### 7.3 HR++ Glasisolatie

H1:

> HR++ glas voor meer comfort en minder warmteverlies bij ramen

Belangrijke punten:

- Relevant bij enkel glas of oud dubbel glas.
- Controleer kozijnen, ventilatie en glasoppervlak.
- Panelen/deuren hebben aparte voorwaarden en moeten vaak gecombineerd worden met glas.

ISDE 2026:

- HR++ glas basis: EUR 25/m2
- Bij twee of meer maatregelen: EUR 50/m2
- Minimaal 3 m2 voor glasmaatregelen
- Maximaal 45 m2 voor glasmaatregelen

### 7.4 Dakisolatie

H1:

> Dakisolatie om groot warmteverlies structureel aan te pakken

Belangrijke punten:

- Relevant bij ongeisoleerde kap, zolder of plat dak.
- Controle op vocht, ventilatie, dakopbouw en koudebruggen.
- Dakisolatie en zolder/vlieringvloer direct boven elkaar tellen niet dubbel.

ISDE 2026:

- Basis dakisolatie: EUR 16,25/m2
- Bij twee of meer maatregelen: EUR 32,50/m2
- Minimaal 20 m2
- Maximaal 200 m2

## 8. ISDE-Calculator Specificatie

### 8.1 Bronnen en systematiek

Gebruik de actuele RVO-systematiek:

- ISDE is voor woningeigenaren die de woning waarin zij zelf wonen verduurzamen.
- Isolatiesubsidie wordt berekend met een vast bedrag per m2.
- Bij meer dan een isolatiemaatregel verdubbelt het subsidiebedrag voor isolatie.
- Dit geldt ook als een isolatiemaatregel wordt gecombineerd met bijvoorbeeld warmtepomp, zonneboiler of aansluiting op warmtenet.
- De volgende maatregel moet binnen 24 maanden worden uitgevoerd.
- De aanvraag gebeurt na uitvoering.
- Doe-het-zelf isolatie komt niet in aanmerking.

Bronnen:

- RVO ISDE isolatiemaatregelen: https://www.rvo.nl/subsidies-financiering/isde/woningeigenaren/isolatiemaatregelen
- RVO ISDE woningeigenaren: https://www.rvo.nl/subsidies-financiering/isde/woningeigenaren
- RVO rekentool, gecontroleerd op 8 juni 2026: https://www.rvo.nl/onderwerpen/isde/woningeigenaren/rekentool

### 8.2 Tarieventabel 2026 Voor Eerste Versie

Gebruik deze waarden als constante in JavaScript:

```javascript
const ISDE_RATES_2026 = {
  spouw: {
    label: "Spouwmuurisolatie",
    baseRate: 5.25,
    minM2: 10,
    maxM2: 170
  },
  vloer: {
    label: "Vloerisolatie",
    baseRate: 5.50,
    minM2: 20,
    maxM2: 130
  },
  glas: {
    label: "HR++ glas",
    baseRate: 25.00,
    minM2: 3,
    maxM2: 45
  },
  dak: {
    label: "Dakisolatie",
    baseRate: 16.25,
    minM2: 20,
    maxM2: 200
  }
};
```

### 8.3 Rekenregels

Input:

- geselecteerde maatregelen
- m2 per maatregel
- optioneel: eerder uitgevoerde tweede maatregel binnen 24 maanden

Regel:

```text
als aantal geselecteerde isolatiemaatregelen >= 2:
  tarief = basisbedrag * 2
anders:
  tarief = basisbedrag
subsidie per maatregel = m2 * tarief
totaal = som van alle maatregelen
```

Belangrijk:

- Toon waarschuwing als ingevulde m2 onder minimum ligt.
- Reken standaard niet boven maximum m2 door, of toon dat het bedrag wordt afgetopt.
- Voor leadgeneratie is het beter om een transparante indicatie te tonen dan om exact alle uitzonderingen af te vangen.
- Plaats altijd een disclaimer.

### 8.4 Losse Calculatorfunctie

```javascript
function calculateIsdeSubsidy(measures) {
  const rates = {
    spouw: { label: "Spouwmuurisolatie", baseRate: 5.25, minM2: 10, maxM2: 170 },
    vloer: { label: "Vloerisolatie", baseRate: 5.50, minM2: 20, maxM2: 130 },
    glas: { label: "HR++ glas", baseRate: 25.00, minM2: 3, maxM2: 45 },
    dak: { label: "Dakisolatie", baseRate: 16.25, minM2: 20, maxM2: 200 }
  };

  const selected = Object.entries(measures)
    .filter(([key, m2]) => rates[key] && Number(m2) > 0);

  const doubled = selected.length >= 2;

  const breakdown = selected.map(([key, rawM2]) => {
    const config = rates[key];
    const m2 = Math.max(0, Number(rawM2) || 0);
    const cappedM2 = Math.min(m2, config.maxM2);
    const appliedRate = doubled ? config.baseRate * 2 : config.baseRate;
    const subtotal = cappedM2 * appliedRate;

    return {
      key,
      label: config.label,
      enteredM2: m2,
      subsidyM2: cappedM2,
      rate: appliedRate,
      subtotal,
      belowMinimum: m2 > 0 && m2 < config.minM2,
      capped: m2 > config.maxM2
    };
  });

  return {
    measureCount: selected.length,
    doubled,
    total: breakdown.reduce((sum, item) => sum + item.subtotal, 0),
    breakdown
  };
}
```

Disclaimer bij output:

> Dit is een indicatie op basis van bekende ISDE-tarieven en algemene voorwaarden. Definitieve subsidie hangt af van RVO-beoordeling, meldcodes, uitvoering, bewijsstukken, minimale oppervlaktes en eerdere aanvragen. Aan deze berekening kunnen geen rechten worden ontleend.

## 9. Multi-Step Formspree Isolatiescan

### 9.1 UX-structuur

De scan bestaat uit 4 stappen:

1. Woningtype
2. Huidige isolatie en gewenste maatregelen
3. Geschatte oppervlaktes
4. Contactgegevens

Toon rechts of onderaan altijd:

- gekozen maatregelen
- live subsidie-indicatie
- melding of tarief verdubbeld is
- disclaimer

Vraag contactgegevens pas in stap 4, nadat de bezoeker waarde heeft gezien.

### 9.2 Velden

Stap 1:

- woningtype: rijwoning, hoekwoning, twee-onder-een-kap, vrijstaand, appartement
- bouwjaar optioneel

Stap 2:

- huidige spouw: onbekend, niet geisoleerd, wel geisoleerd
- huidige vloer: onbekend, niet geisoleerd, wel geisoleerd
- huidige glas: onbekend, enkel glas, oud dubbel glas, HR++ of beter
- maatregelen: spouw, vloer, glas, dak

Stap 3:

- m2 spouw
- m2 vloer
- m2 glas
- m2 dak

Stap 4:

- naam
- e-mail
- telefoon
- postcode
- huisnummer
- opmerking
- privacy akkoord

### 9.3 HTML

Gebruik dit als basis. Vervang het Formspree-endpoint.

```html
<section class="wv-scan" id="warmvast-scan" data-endpoint="https://formspree.io/f/REPLACE_WITH_ID">
  <header class="wv-scan__header">
    <p class="wv-kicker">Gratis isolatiescan</p>
    <h2>Bereken uw isolatie- en ISDE-voordeel</h2>
    <p>Vul uw woninggegevens in en zie direct een subsidie-indicatie.</p>
  </header>

  <div class="wv-scan__grid">
    <form id="wvScanForm" class="wv-form" novalidate>
      <input type="hidden" name="bron" value="WARMVAST isolatiescan">
      <input type="hidden" name="subsidie_totaal" id="subsidieTotaalInput">
      <input type="hidden" name="verdubbeld_tarief" id="verdubbeldTariefInput">

      <div class="wv-progress">
        <span data-dot="1"></span>
        <span data-dot="2"></span>
        <span data-dot="3"></span>
        <span data-dot="4"></span>
      </div>

      <fieldset class="wv-step" data-step="1">
        <legend>Wat voor woning heeft u?</legend>
        <label><input type="radio" name="woningtype" value="Rijwoning" required> Rijwoning</label>
        <label><input type="radio" name="woningtype" value="Hoekwoning"> Hoekwoning</label>
        <label><input type="radio" name="woningtype" value="Twee-onder-een-kap"> Twee-onder-een-kap</label>
        <label><input type="radio" name="woningtype" value="Vrijstaand"> Vrijstaand</label>
        <label><input type="radio" name="woningtype" value="Appartement"> Appartement</label>
      </fieldset>

      <fieldset class="wv-step" data-step="2">
        <legend>Welke isolatie wilt u laten beoordelen?</legend>

        <label>
          Huidige spouwmuur
          <select name="huidige_spouw">
            <option>Onbekend</option>
            <option>Niet geisoleerd</option>
            <option>Wel geisoleerd</option>
          </select>
        </label>

        <label>
          Huidige vloer
          <select name="huidige_vloer">
            <option>Onbekend</option>
            <option>Niet geisoleerd</option>
            <option>Wel geisoleerd</option>
          </select>
        </label>

        <label>
          Huidig glas
          <select name="huidige_glas">
            <option>Onbekend</option>
            <option>Enkel glas</option>
            <option>Oud dubbel glas</option>
            <option>HR++ of beter</option>
          </select>
        </label>

        <div class="wv-checks">
          <label><input type="checkbox" name="maatregelen" value="spouw"> Spouwmuurisolatie</label>
          <label><input type="checkbox" name="maatregelen" value="vloer"> Vloerisolatie</label>
          <label><input type="checkbox" name="maatregelen" value="glas"> HR++ glas</label>
          <label><input type="checkbox" name="maatregelen" value="dak"> Dakisolatie</label>
        </div>
      </fieldset>

      <fieldset class="wv-step" data-step="3">
        <legend>Geschatte oppervlaktes</legend>
        <p>Een schatting is genoeg. WARMVAST controleert de m2 bij de technische opname.</p>
        <label data-area="spouw">Spouw in m2 <input type="number" name="m2_spouw" min="0" step="1"></label>
        <label data-area="vloer">Vloer in m2 <input type="number" name="m2_vloer" min="0" step="1"></label>
        <label data-area="glas">Glas in m2 <input type="number" name="m2_glas" min="0" step="0.5"></label>
        <label data-area="dak">Dak in m2 <input type="number" name="m2_dak" min="0" step="1"></label>
      </fieldset>

      <fieldset class="wv-step" data-step="4">
        <legend>Waar mogen we de scan naartoe sturen?</legend>
        <label>Naam <input type="text" name="naam" autocomplete="name" required></label>
        <label>E-mail <input type="email" name="email" autocomplete="email" required></label>
        <label>Telefoon <input type="tel" name="telefoon" autocomplete="tel" required></label>
        <label>Postcode <input type="text" name="postcode" autocomplete="postal-code" required></label>
        <label>Huisnummer <input type="text" name="huisnummer" required></label>
        <label>Opmerking <textarea name="opmerking" rows="3"></textarea></label>
        <label class="wv-consent"><input type="checkbox" name="privacy_akkoord" value="ja" required> WARMVAST mag contact opnemen over mijn isolatiescan.</label>
      </fieldset>

      <p id="wvFormError" class="wv-error" aria-live="polite"></p>

      <div class="wv-actions">
        <button type="button" id="wvPrev">Vorige</button>
        <button type="button" id="wvNext">Volgende</button>
        <button type="submit" id="wvSubmit">Verstuur mijn scan</button>
      </div>
    </form>

    <aside class="wv-result" aria-live="polite">
      <p class="wv-kicker">Live ISDE-indicatie</p>
      <strong id="wvTotal">EUR 0</strong>
      <p id="wvMode">Selecteer minimaal een maatregel.</p>
      <ul id="wvBreakdown"></ul>
      <p class="wv-small">Indicatie onder voorbehoud van RVO-voorwaarden en beoordeling.</p>
    </aside>
  </div>

  <div id="wvSuccess" class="wv-success" hidden>
    <h3>Uw isolatiescan is verzonden.</h3>
    <p>WARMVAST neemt binnen 24 uur contact met u op.</p>
  </div>
</section>
```

### 9.4 JavaScript

```javascript
(function () {
  const root = document.getElementById("warmvast-scan");
  if (!root) return;

  const form = document.getElementById("wvScanForm");
  const endpoint = root.dataset.endpoint;
  const steps = Array.from(root.querySelectorAll(".wv-step"));
  const dots = Array.from(root.querySelectorAll("[data-dot]"));
  const prev = document.getElementById("wvPrev");
  const next = document.getElementById("wvNext");
  const submit = document.getElementById("wvSubmit");
  const error = document.getElementById("wvFormError");
  const success = document.getElementById("wvSuccess");
  const totalEl = document.getElementById("wvTotal");
  const modeEl = document.getElementById("wvMode");
  const breakdownEl = document.getElementById("wvBreakdown");
  const totalInput = document.getElementById("subsidieTotaalInput");
  const doubledInput = document.getElementById("verdubbeldTariefInput");

  let currentStep = 1;

  const rates = {
    spouw: { label: "Spouwmuurisolatie", baseRate: 5.25, minM2: 10, maxM2: 170, field: "m2_spouw" },
    vloer: { label: "Vloerisolatie", baseRate: 5.50, minM2: 20, maxM2: 130, field: "m2_vloer" },
    glas: { label: "HR++ glas", baseRate: 25.00, minM2: 3, maxM2: 45, field: "m2_glas" },
    dak: { label: "Dakisolatie", baseRate: 16.25, minM2: 20, maxM2: 200, field: "m2_dak" }
  };

  function euro(value) {
    return new Intl.NumberFormat("nl-NL", {
      style: "currency",
      currency: "EUR",
      maximumFractionDigits: 0
    }).format(value || 0);
  }

  function selectedMeasures() {
    return Array.from(form.querySelectorAll('input[name="maatregelen"]:checked')).map((input) => input.value);
  }

  function fieldNumber(name) {
    const field = form.elements[name];
    return Math.max(0, Number(String(field?.value || "0").replace(",", ".")) || 0);
  }

  function calculate() {
    const selected = selectedMeasures();
    const doubled = selected.length >= 2;

    const breakdown = selected.map((key) => {
      const rate = rates[key];
      const enteredM2 = fieldNumber(rate.field);
      const subsidyM2 = Math.min(enteredM2, rate.maxM2);
      const appliedRate = doubled ? rate.baseRate * 2 : rate.baseRate;

      return {
        key,
        label: rate.label,
        enteredM2,
        subsidyM2,
        rate: appliedRate,
        subtotal: subsidyM2 * appliedRate,
        belowMinimum: enteredM2 > 0 && enteredM2 < rate.minM2,
        capped: enteredM2 > rate.maxM2
      };
    });

    return {
      selected,
      doubled,
      breakdown,
      total: breakdown.reduce((sum, item) => sum + item.subtotal, 0)
    };
  }

  function renderCalculator() {
    const result = calculate();
    totalEl.textContent = euro(result.total);
    totalInput.value = Math.round(result.total);
    doubledInput.value = result.doubled ? "ja" : "nee";

    if (!result.selected.length) {
      modeEl.textContent = "Selecteer minimaal een maatregel.";
      breakdownEl.innerHTML = "";
      return;
    }

    modeEl.textContent = result.doubled
      ? "Twee of meer maatregelen: het m2-tarief is verdubbeld."
      : "Een maatregel: basisbedrag per m2.";

    breakdownEl.innerHTML = result.breakdown.map((item) => {
      const notes = [];
      if (item.belowMinimum) notes.push("mogelijk onder minimale m2-eis");
      if (item.capped) notes.push("berekening afgetopt op maximum m2");
      const note = notes.length ? ` (${notes.join(", ")})` : "";

      return `<li>${item.label}: ${item.subsidyM2} m2 x ${euro(item.rate)} = <strong>${euro(item.subtotal)}</strong>${note}</li>`;
    }).join("");
  }

  function syncAreaFields() {
    const selected = selectedMeasures();
    root.querySelectorAll("[data-area]").forEach((label) => {
      const key = label.dataset.area;
      const visible = selected.includes(key);
      label.hidden = !visible;
      if (!visible) {
        const input = label.querySelector("input");
        if (input) input.value = "";
      }
    });
    renderCalculator();
  }

  function showStep(step) {
    currentStep = Math.max(1, Math.min(4, step));
    steps.forEach((el) => {
      el.hidden = Number(el.dataset.step) !== currentStep;
    });
    dots.forEach((dot) => {
      dot.classList.toggle("is-active", Number(dot.dataset.dot) <= currentStep);
    });
    prev.hidden = currentStep === 1;
    next.hidden = currentStep === 4;
    submit.hidden = currentStep !== 4;
    error.textContent = "";
  }

  function validateStep() {
    const active = steps.find((step) => Number(step.dataset.step) === currentStep);
    const required = Array.from(active.querySelectorAll("[required]"));

    if (currentStep === 2 && selectedMeasures().length === 0) {
      return "Selecteer minimaal een isolatiemaatregel.";
    }

    for (const field of required) {
      if (!field.checkValidity()) {
        return "Controleer de verplichte velden.";
      }
    }

    return "";
  }

  next.addEventListener("click", () => {
    const message = validateStep();
    if (message) {
      error.textContent = message;
      return;
    }
    showStep(currentStep + 1);
  });

  prev.addEventListener("click", () => showStep(currentStep - 1));

  form.addEventListener("change", (event) => {
    if (event.target.name === "maatregelen") syncAreaFields();
    renderCalculator();
  });

  form.addEventListener("input", renderCalculator);

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    error.textContent = "";

    const message = validateStep();
    if (message) {
      error.textContent = message;
      return;
    }

    if (!endpoint || endpoint.includes("REPLACE_WITH_ID")) {
      error.textContent = "Formspree endpoint ontbreekt.";
      return;
    }

    const result = calculate();
    const data = new FormData(form);
    data.set("subsidie_totaal", Math.round(result.total));
    data.set("verdubbeld_tarief", result.doubled ? "ja" : "nee");
    data.set("subsidie_specificatie", JSON.stringify(result.breakdown));

    submit.disabled = true;
    submit.textContent = "Versturen...";

    try {
      const response = await fetch(endpoint, {
        method: "POST",
        headers: { Accept: "application/json" },
        body: data
      });

      if (!response.ok) throw new Error("Formspree error");

      form.reset();
      syncAreaFields();
      showStep(1);
      success.hidden = false;
    } catch (err) {
      error.textContent = "Verzenden lukt nu niet. Probeer het later opnieuw of bel WARMVAST.";
    } finally {
      submit.disabled = false;
      submit.textContent = "Verstuur mijn scan";
    }
  });

  syncAreaFields();
  showStep(1);
})();
```

### 9.5 CSS-Richting

De visuele stijl moet:

- professioneel
- technisch
- rustig
- conversiegericht
- niet schreeuwerig

Gebruik geen drukke gradients of decoratieve vormen. Kies een heldere layout met veel witruimte, sterke typografie en duidelijke CTA's.

Kleurrichting (suggestie, geen harde spec):

De onderstaande waarden zijn een startpunt. De ontwerper/agent mag hiervan afwijken op basis van professioneel inzicht, mits het merk warm, technisch en betrouwbaar blijft en CTA's een duidelijk, toegankelijk contrast houden. Warm geel mag als energie-/secundair accent blijven; een sterker vertrouwensaccent voor primaire CTA's is toegestaan.

- Donker antraciet: `#222222`
- Warm geel: `#fed03d`
- Off-white: `#f7f7f4`
- Lichtgrijs: `#e8e8e8`
- Succesgroen subtiel: `#e8f6ed`

## 10. Formspree Specificatie

Formspree moet via AJAX worden aangeroepen met `fetch`.

Verwachte velden:

- `bron`
- `woningtype`
- `huidige_spouw`
- `huidige_vloer`
- `huidige_glas`
- `maatregelen`
- `m2_spouw`
- `m2_vloer`
- `m2_glas`
- `m2_dak`
- `subsidie_totaal`
- `verdubbeld_tarief`
- `subsidie_specificatie`
- `naam`
- `email`
- `telefoon`
- `postcode`
- `huisnummer`
- `opmerking`
- `privacy_akkoord`

Aanbevolen Formspree onderwerp:

```text
Nieuwe WARMVAST isolatiescan - {{postcode}} - subsidie {{subsidie_totaal}}
```

Aanbevolen anti-spam:

- Formspree spam protection inschakelen
- Honeypot toevoegen
- Telefoon verplicht maken
- Rate limiting via Formspree-instellingen

Honeypot:

```html
<input type="text" name="_gotcha" style="display:none" tabindex="-1" autocomplete="off">
```

## 11. WordPress Implementatierichtlijnen

### 11.1 Theme setup

`style.css` header:

```css
/*
Theme Name: WARMVAST
Author: WARMVAST
Description: Custom conversion-focused WordPress theme for WARMVAST insulation services.
Version: 1.0.0
Text Domain: warmvast
*/
```

### 11.2 `functions.php`

Vereisten:

- enqueue `assets/css/main.css`
- enqueue `assets/js/main.js`
- enqueue `assets/js/isolatiescan.js` alleen op pagina's waar scan staat, of globaal als klein genoeg
- theme support voor title-tag, post-thumbnails, menus, html5
- registreer menu `primary` en `footer`
- shortcode `[warmvast_isolatiescan]`

Shortcode:

```php
add_shortcode('warmvast_isolatiescan', function () {
    ob_start();
    get_template_part('template-parts/isolatiescan');
    return ob_get_clean();
});
```

### 11.3 Customizer of config

Maak instellingen makkelijk aanpasbaar:

- telefoonnummer
- e-mailadres
- adres
- Formspree endpoint
- openingstijden

Simpelste eerste versie: constants in `functions.php`.

Betere versie: WordPress options page of Customizer.

### 11.4 SEO

Gebruik nette semantische HTML:

- een H1 per pagina
- logische H2/H3-structuur
- interne links tussen diensten en subsidiepagina
- FAQ schema optioneel
- LocalBusiness schema optioneel

Meta titles:

- Home: `WARMVAST | Isolatie op basis van feiten`
- Spouw: `Spouwmuurisolatie laten uitvoeren | WARMVAST`
- Vloer: `Vloerisolatie en kruipruimteisolatie | WARMVAST`
- Glas: `HR++ glas en glasisolatie | WARMVAST`
- Dak: `Dakisolatie laten uitvoeren | WARMVAST`
- Subsidie: `ISDE subsidie voor isolatie | WARMVAST`

## 12. Kennisbankstrategie

Maak kennisbank als SEO-cluster rond ISDE en isolatie.

Eerste artikelen:

1. ISDE 2026: hoe werkt subsidie voor isolatie?
2. Waarom verdubbelt ISDE bij twee isolatiemaatregelen?
3. Spouwmuurisolatie: wanneer is uw woning geschikt?
4. Vloerisolatie of bodemisolatie: wat is het verschil?
5. HR++ glas subsidie: hoeveel krijgt u per m2?
6. Dakisolatie: binnenzijde, buitenzijde of zoldervloer?
7. Welke foto's en meldcodes zijn nodig voor ISDE?
8. Hoe berekent u het aantal m2 isolatie?

Elk artikel eindigt met CTA:

> Bereken uw isolatie- en subsidievoordeel met de gratis WARMVAST isolatiescan.

## 13. Tracking en CRO

Meet minimaal:

- `scan_start`
- `scan_step_1_complete`
- `scan_step_2_complete`
- `scan_subsidy_seen`
- `scan_contact_step`
- `scan_submit_success`
- `scan_submit_error`
- `phone_click`
- `email_click`
- `cta_click`

Lead scoring:

- 1 maatregel: standaard lead
- 2+ maatregelen: hoge prioriteit
- subsidie-indicatie boven EUR 1000: hoge prioriteit
- postcode binnen actieve regio: hoge prioriteit

A/B-tests:

- Hero CTA: "Start gratis isolatiescan" vs "Bereken mijn subsidie"
- Scan in hero vs scan onder hero
- Subsidiebedrag direct tonen vs pas na m2-invoer
- Telefoon verplicht vs telefoon optioneel

## 14. Privacy en Juridisch

Privacyverklaring moet uitleggen:

- welke gegevens worden verzameld
- dat gegevens worden gebruikt voor isolatiescan, offerte en contact
- dat Formspree als formulierverwerker wordt gebruikt
- bewaartermijn
- rechten van gebruiker
- contactadres voor privacyvragen

Bij formulier:

> We gebruiken uw gegevens alleen om uw isolatiescan te beoordelen en contact met u op te nemen. Geen spam.

Algemene voorwaarden moeten duidelijk maken:

- offertes zijn gebaseerd op beschikbare informatie en opname
- werkelijk m2 kan afwijken
- subsidie is afhankelijk van RVO
- WARMVAST kan helpen met dossier en aanvraagvoorbereiding, maar RVO beslist

## 15. Definition Of Done

De nieuwe site is klaar voor eerste release wanneer:

- Custom WordPress theme actief is
- Homepage volledig werkt
- Isolatiescan werkt op desktop en mobiel
- Formspree submission succesvol binnenkomt
- ISDE-calculator rekent met actuele waarden
- Servicepagina's gevuld zijn
- Subsidiepagina gevuld is
- Contactgegevens kloppen
- Privacyverklaring aanwezig is
- Geen demo-content zichtbaar is
- Lighthouse-basics op orde zijn
- Alle CTA's naar scan of contact leiden

## 16. Belangrijkste Prompt Voor Claude

Gebruik dit als directe opdracht:

```text
Je bouwt een volledig nieuwe WordPress-site voor WARMVAST, een Nederlands isolatiebedrijf. Gebruik geen Avada, Elementor, Divi of andere page builder. Ga uit van een verse repo op MAMP met WordPress, PHP, MySQL/MariaDB, vanilla CSS/JS en Formspree.

Bouw een custom WordPress theme genaamd warmvast. De site moet conversiegericht zijn en draaien om een gratis multi-step isolatiescan met live ISDE-subsidiecalculator. De scan verstuurt leads via AJAX naar Formspree.

Merkpositionering: "Isolatie op basis van feiten, niet van beloftes." WARMVAST is analytisch, technisch, transparant en resultaatgericht. Vermijd hype en valse subsidiegaranties.

Maak minimaal:
- front-page.php met hero, isolatiescan, diensten, ISDE-verdubbeling, werkwijze, vertrouwen en FAQ
- servicepagina-templates of pagina-content voor spouwmuurisolatie, vloerisolatie, HR++ glasisolatie en dakisolatie
- subsidie-service pagina
- contactpagina
- privacyverklaring en algemene voorwaarden basis
- template-part voor isolatiescan
- assets/js/isolatiescan.js met Formspree AJAX en ISDE-calculator
- assets/css/main.css met responsive styling
- functions.php met enqueue, menu's, theme support en shortcode [warmvast_isolatiescan]

ISDE-calculator:
- spouw: EUR 5,25/m2, minimum 10 m2, maximum 170 m2
- vloer: EUR 5,50/m2, minimum 20 m2, maximum 130 m2
- HR++ glas: EUR 25/m2, minimum 3 m2, maximum 45 m2
- dak: EUR 16,25/m2, minimum 20 m2, maximum 200 m2
- als twee of meer isolatiemaatregelen geselecteerd zijn, verdubbel het m2-tarief
- toon waarschuwingen bij minimum/maximum
- toon altijd disclaimer dat het een indicatie is en RVO beslist

Formulierstappen:
1. woningtype
2. huidige isolatie en gewenste maatregelen
3. geschatte oppervlaktes
4. contactgegevens

Contactvelden:
naam, email, telefoon, postcode, huisnummer, opmerking, privacy akkoord.

De site moet mobiel goed werken, snel laden, semantische HTML gebruiken en geen demo-content bevatten.
```

## 17. Korte Samenvatting

WARMVAST moet vanaf nul worden gebouwd als een conversiegerichte isolatiesite. De isolatiescan is het product op de website: hij kwalificeert de lead, toont direct subsidievoordeel en stuurt de aanvraag naar Formspree.

De site moet technisch betrouwbaar voelen: helder, rustig, feitelijk en gericht op meetbare woningverbetering. De ISDE-verdubbeling bij twee of meer maatregelen is de belangrijkste commerciële hefboom en moet duidelijk maar eerlijk worden uitgelegd.
