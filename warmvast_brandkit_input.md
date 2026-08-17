# WARMVAST brandkit input uit huidige site

Dit document verzamelt de bestaande visuele en verbale stijl van de huidige WARMVAST WordPress-site. Gebruik dit als briefing voor een nieuwe brandkit met logo's, fonts, kleuren, componenten, imagery en tone-of-voice.

Bronnen in de repo:

- `wp-content/themes/warmvast/assets/css/main.css`
- `wp-content/themes/warmvast/header.php`
- `wp-content/themes/warmvast/front-page.php`
- `wp-content/themes/warmvast/inc/template-tags.php`
- `wp-content/themes/warmvast/inc/config.php`
- `wp-content/themes/warmvast/inc/service-content.php`
- `warmvast_blueprint.md`

## 1. Merkessentie

### Bestaande positionering

WARMVAST is neergezet als een technisch, nuchter isolatiebedrijf voor particuliere woningeigenaren. De site draait niet om "vraag offerte aan", maar om de gratis isolatiescan als laagdrempelige eerste stap.

Kernzin uit de oorspronkelijke blueprint:

> Isolatie op basis van feiten, niet van beloftes.

Huidige homepage-H1:

> Zit je er warmpjes bij? Wij houden die warmte vast.

Huidige belofte in normale taal:

> Ontdek waar uw woning warmte verliest en welke isolatie het meeste oplevert, met een directe ISDE-indicatie en besparing op basis van uw adres.

### Gewenst gevoel

- Technisch betrouwbaar
- Rustig en deskundig
- Warm, maar niet wollig
- Conversiegericht zonder schreeuwerige sales
- Meetbaar en feitelijk
- Nederlandse woningmarkt, subsidie en uitvoering
- Geen generieke aannemer-look
- Geen duurzaamheidsmarketing zonder concrete onderbouwing

### Kernassociaties

- Warmte vasthouden
- Warmteverlies zichtbaar maken
- Woning als systeem
- Meetbare m2
- Technische opname
- ISDE-subsidie
- Meldcodes en fotobewijs
- Comfort en energiebesparing

## 2. Tone-of-voice

### Merkpersoonlijkheid

- Analytisch
- Technisch onderlegd
- Transparant
- Resultaatgericht
- Rustig overtuigend
- Praktisch
- Behulpzaam

### Schrijfstijl

Gebruik:

- korte zinnen
- concrete termen
- actieve formuleringen
- meetbare taal
- directe uitleg
- disclaimers zonder angstzaaierij

Vermijd:

- "gratis geld"
- "100% subsidiegarantie"
- "de goedkoopste"
- nep-schaarste
- vage duurzaamheidstaal
- te Amerikaanse SaaS-copy
- overdreven enthousiasme

### Woorden die bij het merk passen

- meetbaar
- m2
- warmteverlies
- comfort
- isolatiewaarde
- subsidie-indicatie
- technische opname
- meldcode
- fotobewijs
- uitvoering door vakmensen
- ISDE
- op basis van uw woning
- eerlijk
- zonder verrassingen

### Voorbeelden van bestaande copy

- "Gratis, 2 minuten, geen verplichtingen"
- "Spouw, vloer, glas en dak"
- "Technische opname voor elke uitvoering"
- "Helder m2-overzicht"
- "Subsidiedossier geregeld"
- "Reactie binnen 24 uur"
- "Nuchter, technisch en transparant"
- "Wij claimen alleen wat we kunnen uitleggen en vastleggen."
- "Indicatie onder voorbehoud van RVO-beoordeling."

## 3. Huidige visuele richting

De huidige site gebruikt een technisch-warme stijl:

- Donkere hero met thermische warmte-effecten
- Diep groen als betrouwbaarheidskleur
- Warm geel als energie/subsidie/accentkleur
- Off-white "engineering paper" achtergrond met subtiel grid
- Inline technische iconen
- Kaarten met zachte randen en subtiele schaduwen
- Nummers en subsidiebedragen in display-font
- Rustige, functionele layouts

De stijl voelt nu als:

> technisch isolatieadvies + digitale woningscan + warme energiebesparing

## 4. Kleuren

### Primaire kleuren uit CSS

| Token | Hex | Gebruik |
|---|---:|---|
| `--ink` | `#1b1f21` | Donkere hero, footer, tekst, result-panel |
| `--ink-2` | `#3c4348` | Secundaire tekst |
| `--ink-3` | `#6c757b` | Muted tekst, labels, meta |
| `--paper` | `#f6f6f2` | Papier/off-white basis |
| `--surface` | `#ffffff` | Kaarten, header, panelen |
| `--surface-2` | `#fbfbf9` | Form fields, zachte panelen |
| `--line` | `#e6e7e1` | Subtiele borders |
| `--line-strong` | `#d6d8d0` | Sterkere borders |
| `--primary` | `#0b6b5b` | Hoofdgroen, vertrouwen, iconen, CTA secundair |
| `--primary-600` | `#0a5d4f` | Links |
| `--primary-700` | `#074a3f` | Donker groen, hover, CTA-band |
| `--primary-050` | `#e7f2ef` | Lichtgroene backgrounds |
| `--accent` | `#fed03d` | Warmte, subsidie, highlights, primary CTA |
| `--accent-strong` | `#f6c200` | Hover/geel sterker |
| `--accent-700` | `#e0a900` | Donker geel voor strokes |
| `--accent-ink` | `#2b2400` | Tekst op geel |
| `--success` | `#1c7a45` | Positieve status |
| `--success-bg` | `#e8f6ed` | Success background |
| `--warn` | `#a35314` | Waarschuwingen |
| `--warn-bg` | `#fbf1e6` | Waarschuwingsvlak |

### Extra kleuren uit componenten

| Hex / waarde | Gebruik |
|---|---|
| `#f4f4ef` | Body en main achtergrond |
| `#06473d` | CTA-band gradient start |
| `#151b1d`, `#172021`, `#111718` | Scan-landing donkere achtergrond |
| `#4fd0b8` | Hero technical house stroke |
| `#f18b2f` | Thermische heat-bar overgang |
| `#b9c4c0`, `#c6d0cc`, `#eef2f0` | Tekst op donkere vlakken |

### Energie-label schaal in woningscan

| Label | Kleur |
|---|---:|
| A++++ | `#006b3c` |
| A+++ | `#00864a` |
| A++ | `#14a05a` |
| A+ | `#45b96f` |
| A | `#66bd63` |
| B | `#8bd06b` |
| C | `#c8df7a` |
| D | `#f6c945` |
| E | `#fdae61` |
| F | `#f46d43` |
| G | `#d73027` |

### Kleurrollen voor brandkit

Aanbevolen rollen:

- Brand primary: diep technisch groen `#0b6b5b`
- Brand dark: antraciet/zwartgroen `#1b1f21`
- Energy accent: warm geel `#fed03d`
- Paper neutral: warm off-white `#f6f6f2` / `#f4f4ef`
- Functional success: groen `#1c7a45`
- Functional warning: warm oranje/bruin `#a35314`

Belangrijk: geel werkt nu als accent en CTA-kleur. Als de brandkit een professionelere primaire CTA wil, kan groen de primaire actie worden en geel de energie-highlight blijven.

## 5. Typografie

### Huidige fonts

De site laadt twee self-hosted variable fonts:

- `Inter` via `assets/fonts/inter-latin-var.woff2`
- `Space Grotesk` via `assets/fonts/space-grotesk-latin-var.woff2`

CSS:

```css
--font-sans: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
--font-display: "Space Grotesk", system-ui, -apple-system, "Segoe UI", sans-serif;
--font-mono: ui-monospace, "SF Mono", "Cascadia Mono", Menlo, Consolas, monospace;
```

### Fontgebruik

Inter:

- bodytekst
- formulieren
- navigatie
- labels
- kleine uitleg
- card-copy

Space Grotesk:

- H1/H2/H3/H4
- merkwoord "Warmvast"
- bedragen
- statistieken
- tariefwaarden
- energielabels

### Huidige type scale

```css
--step--1: clamp(.82rem, .8rem + .1vw, .88rem);
--step-0:  clamp(1rem, .96rem + .2vw, 1.075rem);
--step-1:  clamp(1.15rem, 1.08rem + .35vw, 1.3rem);
--step-2:  clamp(1.4rem, 1.28rem + .6vw, 1.7rem);
--step-3:  clamp(1.75rem, 1.5rem + 1.1vw, 2.3rem);
--step-4:  clamp(2.1rem, 1.7rem + 1.9vw, 3.2rem);
--step-5:  clamp(2.5rem, 1.9rem + 2.8vw, 3.9rem);
```

### Typografische eigenschappen

- Headings: Space Grotesk, weight 700, line-height 1.1.
- H1/H2 hebben licht negatieve letterspacing in CSS (`-.02em`), maar voor nieuwe brandkit liever voorzichtig gebruiken.
- Bedragen en meetwaarden gebruiken tabular figures.
- Body line-height is 1.62.
- Buttons gebruiken font-weight circa 650.
- Kicker labels gebruiken uppercase, 0.09em letterspacing.

## 6. Logo en brand mark

### Huidige fallback-logo

De site heeft nog geen extern logo-bestand als vaste asset. In `header.php` staat een inline SVG fallback:

- Vierkant met afgeronde hoeken
- Donkergroene vulling via `currentColor`
- Gele daklijn
- Witte binnenlijn als tweede dak/warmte-vorm
- Woordmerk: `Warm` in donker, `vast` in groen

Beschrijving:

```text
Icon: afgerond groen vierkant met abstract dak / warmte-huis.
Woordmerk: Warmvast, waarbij "vast" groen is.
```

### Huidige favicon

Inline SVG favicon:

```text
32x32 vierkant, radius 7, fill #0b6b5b, daklijn stroke #fed03d.
```

### Richting voor nieuwe logo's

Maak straks minimaal:

- primair horizontaal logo
- compact logo/beeldmerk
- favicon/app-icon
- dark-mode versie
- one-color versie
- wit-op-donker versie
- social/avatar versie

Logo moet werken naast technische UI, niet te organisch of te speels.

## 7. Iconografie

### Huidige stijl

Alle iconen zijn inline SVG, 24x24 viewBox, stroke-based:

```text
fill: none
stroke: currentColor
stroke-width: 1.8
stroke-linecap: round
stroke-linejoin: round
```

### Bestaande icon keys

- `arrow`
- `check`
- `phone`
- `mail`
- `shield`
- `ruler`
- `camera`
- `clock`
- `map`
- `wall`
- `floor`
- `glass`
- `roof`
- `thermo`
- `euro`
- `spark`
- `chevron`
- `phone-check`
- `doc`

### Visuele rol

- Service-iconen: wall, floor, glass, roof.
- Trust-iconen: shield, ruler, camera, clock, doc.
- Scan-iconen: map, ruler, euro, thermo.
- Conversie-iconen: arrow, phone, mail.

### Brandkit-aanbeveling

Ontwerp een eigen iconset op basis van deze thema's:

- spouwmuur
- vloer/kruipruimte
- HR++ glas
- dak
- thermische scan/warmteverlies
- meetlint/m2
- subsidie/euro
- dossier/meldcode
- fotobewijs
- technische opname
- telefoongesprek

Houd de iconen lijn-gebaseerd, geometrisch en technisch. Niet cartoonachtig.

## 8. Layout en spacing

### Container

```css
--container: 1180px;
--gutter: clamp(1rem, 4vw, 2.5rem);
```

### Secties

- Normale sectie-padding: `clamp(3rem, 6vw, 5.5rem)`.
- Tight secties: `clamp(2rem, 4vw, 3.5rem)`.
- Light sections zijn transparant over een doorlopende "engineering paper" achtergrond.
- Dark sections gebruiken `--ink`.

### Achtergrondconcept

De site gebruikt een doorlopend technisch papier:

- off-white basis
- subtiel grid van 32px
- lichte noise texture via inline SVG
- ambient heat/cool radial gradients

Brandkit-termen:

```text
engineering sheet
thermal depth
measured warmth
technical paper
```

## 9. Randen, radius en schaduw

### Radius tokens

```css
--radius-xs:  6px;
--radius-sm:  8px;
--radius-md:  11px;
--radius:     14px;
--radius-lg:  22px;
--radius-pill: 999px;
```

### Schaduwen

```css
--shadow-sm: 0 1px 2px rgba(20,30,28,.06), 0 1px 3px rgba(20,30,28,.06);
--shadow: 0 6px 20px rgba(20,40,36,.08), 0 2px 6px rgba(20,40,36,.05);
--shadow-lg: 0 24px 60px rgba(12,40,34,.16), 0 6px 18px rgba(12,40,34,.08);
```

### Glas/elevation stijl

Voor high-value modules zoals service cards en de verdubbelaar:

- semi-transparante witte vulling
- gradient border
- subtiele backdrop blur
- zachte zwevende schaduw

Let op: dit moet spaarzaam blijven. Niet elke component moet glassmorphism krijgen.

## 10. Componenten in huidige site

### Header

Bestaat uit:

- sticky header
- wit/transparant met blur
- logo links
- navigatie midden
- CTA rechts
- mobiele slide-in navigatie
- mega-dropdown onder "Isolatie"

Brandkit-componenten:

- desktop header
- mobile header
- nav dropdown
- CTA in nav
- active/hover states

### Hero

Bestaat uit:

- donkere technische achtergrond
- thermal gradients
- ambient orbs
- decoratieve line-art woning
- eyebrow pill
- H1 met geel accent op tweede zin
- primaire gele CTA
- ghost secondary CTA
- trustline met checks
- scan-module rechts of onder op mobiel

Hero-richting:

```text
donker, warm, technisch, scan-first, geen stockfoto als primaire drager tenzij echte vakfoto beschikbaar is.
```

### Buttons

Huidige varianten:

- `.btn--primary`: groen, witte tekst
- `.btn--accent`: geel, donkere tekst
- `.btn--secondary`: transparant, border
- `.btn--ghost`: lichte border op donkere achtergrond
- `.btn--sm`
- `.btn--lg`
- `.btn--sheen` alleen op primaire hero CTA

Button-richtlijn:

- Accent/geel trekt aandacht.
- Groen voelt betrouwbaarder en kan als primaire brand-CTA dienen.
- Ghost alleen op dark hero.
- Gebruik pijlicoon aan einde voor navigerende CTA's.

### Cards

Huidige cardtypen:

- probleemkaart
- service card
- review card
- contact card
- post card
- trust item
- tariff cell
- scan result panels

Cardstijl:

- witte of licht transparante vulling
- border `--line`
- radius rond 14px
- subtiele schaduw
- technische icon-badge links/boven
- hover lift voor klikbare kaarten

### USP bar

Direct onder hero:

- 4 kolommen desktop
- 2 kolommen tablet
- 1 kolom mobiel
- icon in lichtgroene badge
- korte titel + subtitel

Bestaande USP's:

- Technische opname, voor elke uitvoering
- Helder m2-overzicht, u weet wat u betaalt
- Subsidiedossier geregeld, meldcodes & fotobewijs
- Reactie binnen 24 uur, werkzaam in heel Nederland

### Woningscan module

De actieve scan is de adresgedreven `woningscan`, niet de oude multi-step isolatiescan.

Fases:

1. Adresinvoer
2. Loading
3. Resultaat
4. Subsidie & besparing
5. Leadformulier
6. Success

Visuele elementen:

- postcode/huisnummer inputs
- loader met huis + warmtegolven
- schematische woning op basis van footprint
- luchtfoto met footprint overlay
- energielabel schaal A++++ t/m G
- m2 inputs
- maatregel-checklist
- subsidie- en besparingsfiguren
- privacy/leadformulier

Brandkit moet scan als kernproduct behandelen. Dit is niet zomaar een formulier.

### CTA-band

Footer CTA gebruikt:

- donker groene gradient
- subtiel grid
- preview report-card
- thermal bar visual
- pills met "Adrescheck", "Geschatte m2", "ISDE-indicatie"
- gele CTA
- telefoonlink

Deze stijl is sterk als "conversion footer module".

### Reviews

Huidige reviews zijn sample-data in `inc/config.php` en `verified=false`.

Brandkit-regel:

```text
Geen nep-reviews, geen fake AggregateRating schema, geen verzonnen certificaten.
```

Visueel:

- ratingnummer groot in Space Grotesk
- gele sterren
- reviewkaarten
- avatar met initiaal in groen rondje

## 11. Imagery en illustratie

### Huidige aanpak

De site gebruikt vooral:

- inline SVG iconen
- technische line-art van huis/warmte
- custom kennisbank-illustraties
- luchtfoto via PDOK WMS in de scan
- geen stockfotografie als kernstijl

### Bestaande illustratietaal

- technische doorsnedes
- meetlijnen
- energielabels
- huis-silhouetten
- warmtegolven
- documenten/meldcodes
- grafiekachtige subsidievoorbeelden

### Aanbevolen beeldstijl voor brandkit

Kies een van deze richtingen:

1. Realistisch vakwerk
   - echte foto's van isolatiewerk, kruipruimte, spouw, glasplaatsing, dak
   - natuurlijke belichting
   - niet stockachtig

2. Technische illustratie
   - line-art en doorsnedes
   - meetpunten, labels, warmtestromen
   - consistent met iconset

3. Thermal visual language
   - subtiele heatmap accenten
   - geel/oranje voor warmte
   - groen/donker voor isolatie/controle

Beste combinatie:

```text
Echte vakfoto's voor vertrouwen + technische illustraties voor uitleg + thermal accent voor merkherkenning.
```

## 12. Pagina- en contentstructuur

### Hoofdnavigatie

- Isolatie
- Subsidie
- Over ons
- Kennisbank
- Contact
- Gratis isolatiescan

### Belangrijkste pagina's

- Home
- Isolatie overzicht
- Spouwmuurisolatie
- Vloerisolatie
- Glasisolatie HR++
- Dakisolatie
- Subsidie service
- Gratis isolatiescan
- Kennisbank
- Contact
- Privacyverklaring
- Algemene voorwaarden

### Servicekaartstructuur

Elke service heeft:

- icon
- label
- probleem
- oplossing
- subsidie vanaf-bedrag
- CTA

Services:

| Key | Label | Icon | Kernprobleem |
|---|---|---|---|
| `spouw` | Spouwmuurisolatie | wall | Koude gevels en snel afkoelende kamers |
| `vloer` | Vloerisolatie | floor | Koude vloer en kou uit kruipruimte |
| `glas` | HR++ glas | glass | Kou en tocht bij ramen |
| `dak` | Dakisolatie | roof | Groot warmteverlies via dak/zolder |

## 13. Conversie en trust

### Primaire conversie

```text
Start gratis isolatiescan
```

### Secundaire conversies

- Bekijk subsidievoordeel
- Bel Warmvast
- Mail Warmvast
- Check deze maatregel
- Gratis adviesgesprek aanvragen

### Trust proof die nu in site zit

- Binnen 24 uur reactie
- Heel Nederland
- Technische opname
- Heldere m2
- Subsidiedossier met meldcodes en fotobewijs
- Geen verplichtingen
- RVO beslist, indicatie is geen garantie

### Trust proof die nog echte assets nodig heeft

- echte klantreviews
- Google/Trustpilot/Klantenvertellen score
- certificaten
- garantievoorwaarden
- KvK/BTW/adres
- echte teamfoto's
- echte projectfoto's
- echte aantallen/statistieken

## 14. Motion en interactie

Huidige motion:

- sticky header shadow bij scroll
- scroll progress bar bovenaan
- hero thermal drift
- warmtegolven bij hero en loader
- button sheen alleen op hero CTA
- card hover lift
- staggered reveal on scroll
- count-up voor reviews/bedragen
- FAQ open animatie
- scan value animation

Alle motion respecteert grotendeels `prefers-reduced-motion`.

Brandkit-richtlijn:

```text
Motion moet meetbaar/technisch voelen: subtiel, functioneel en warm. Geen drukke confetti, geen speelse bounce, geen flashy marketing-effecten.
```

## 15. Accessibility en praktische UI-regels

Huidige regels/stijl:

- focus-visible outline in groen
- skip-link aanwezig
- iconen zijn decorative `aria-hidden`
- formulieren hebben labels
- errors hebben `role="alert"` / `aria-live`
- motion reduce wordt gerespecteerd

Brandkit moet vastleggen:

- minimale contrastwaarden
- button states
- focus rings
- form error states
- disabled states
- dark-mode contrast
- mobile tap targets

## 16. Wat de brandkit moet opleveren

Minimale deliverables:

- Primair logo horizontaal
- Secundair/compact logo
- Favicon/app icon
- Donkere en lichte logo-varianten
- Kleurpalet met rollen
- Typografiehierarchie
- Iconsetstijl
- Button styles
- Form styles
- Card styles
- Scan/report UI-stijl
- Illustratiestijl
- Fotografie-richtlijnen
- Tone-of-voice regels
- Voorbeelden van wel/niet doen

## 17. Openstaande waarschuwingen voor brandkit

### Niet blind overnemen

- De huidige reviews zijn sample-data en mogen niet als echte merkproof in brandkit.
- ISDE 2026-tarieven moeten voor live publicatie tegen RVO worden geverifieerd.
- Contactgegevens kunnen placeholder of voorlopig zijn.
- Logo is nu een fallback-mark, geen definitieve huisstijl.
- De huidige visuele stijl is al bruikbaar, maar nog geen volledig brandsysteem.

### Belangrijkste strategische keuze

Maak de brandkit niet alleen "isolatiebedrijf groen/geel". Het onderscheid zit in:

```text
Warmvast maakt warmteverlies meetbaar en vertaalt dat naar comfort, m2, subsidie en uitvoering.
```

Daar moet de visuele identiteit omheen gebouwd worden.

## 18. Korte creatieve briefing voor designer/AI

```text
Ontwerp een brandkit voor WARMVAST, een Nederlands isolatiebedrijf dat particuliere woningeigenaren helpt met technische woningisolatie en ISDE-subsidie. Het merk moet warm maar feitelijk voelen: geen generieke aannemer, geen vage duurzaamheidshype, maar meetbaar woningadvies.

De kern is de gratis woningscan: de gebruiker vult een adres in, ziet woninggegevens, geschatte m2, energielabel, ISDE-indicatie en besparing. De huisstijl moet daarom technisch, betrouwbaar en conversiegericht zijn.

Gebruik diep groen als vertrouwensbasis, warm geel als energie/subsidie-accent, een donkere hero/rapport-stijl, en off-white technisch papier met subtiele grid-invloeden. Combineer echte vakfotografie met technische line-art, warmtegolven, meetlijnen en woningdoorsnedes.

Logo-richting: compact, technisch, warm, herkenbaar als woning/warmte/isolatie. Geen cartoonhuis, geen standaard eco-blad als hoofdsymbool. Het beeldmerk moet ook als favicon en app-icon werken.

Typografie: helder sans voor tekst, karaktervolle technische displayfont voor headings en cijfers. Huidige site gebruikt Inter + Space Grotesk als referentie.

Tone-of-voice: kort, concreet, nuchter, transparant. Claim alleen wat bewezen of uitlegbaar is. Vermijd nep-schaarste, subsidiegaranties en marketinghype.
```

