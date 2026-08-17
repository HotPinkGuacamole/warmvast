# WARMVAST Cloud Remote prompts zonder visuele review

Gebruik deze prompts een voor een vanaf je telefoon. Laat Cloud Remote steeds bewijs teruggeven: screenshots, exacte viewport, URLs, console-errors, gewijzigde files en een korte risico-inschatting. Werk pas door naar de volgende prompt als de vorige helder is.

Belangrijke regel voor Cloud Remote:

```text
Werk in kleine stappen. Maak geen grote redesigns zonder eerst screenshots en meetbare bevindingen te tonen. Respecteer bestaande oncommitted wijzigingen en revert niets wat je niet zelf hebt aangepast.
```

## 1. Repo- en status-audit

```text
Lees de WARMVAST WordPress repository grondig, maar pas nog niets aan. Rapporteer:
1. huidige git status en welke files al gewijzigd zijn;
2. welke theme-files actief zijn voor homepage, scan, servicepagina's, subsidie, contact en kennisbank;
3. welke scan actief is: legacy isolatiescan of adresgedreven woningscan;
4. welke risico's je ziet als ik zonder visuele review doorprompt.
Geef alleen feitelijke observaties met file-paden.
```

## 2. Start lokale site en bewijs dat hij draait

```text
Start of gebruik de lokale WordPress/MAMP-site voor WARMVAST. Open de homepage en rapporteer:
1. exacte URL;
2. HTTP-status;
3. browser console errors/warnings;
4. of CSS en JS geladen zijn;
5. screenshot van desktop 1440x1000 en mobiel 390x844.
Pas nog niets aan.
```

## 3. Screenshot-only visuele nulmeting

```text
Maak een visuele nulmeting zonder codewijzigingen. Maak screenshots van:
- homepage top/hero desktop en mobiel;
- gratis-isolatiescan desktop en mobiel;
- een servicepagina desktop en mobiel;
- subsidie-service desktop en mobiel;
- contact desktop en mobiel.
Beschrijf per screenshot in maximaal 5 bullets: wat valt op, wat is sterk, wat oogt kapot/druk/onduidelijk, en of tekst overlapt.
```

## 4. Kritische scan-flow test

```text
Test de actieve woningscan end-to-end met een realistisch Nederlands adres. Gebruik geen echte klantgegevens; gebruik test-contactdata. Controleer:
1. adresinvoer;
2. PDOK/BAG resultaat;
3. luchtfoto en footprint overlay;
4. energielabelweergave;
5. subsidie/besparing;
6. leadformuliervalidatie;
7. Formspree-submit alleen als dit veilig is of gebruik anders een dry-run.
Rapporteer exacte errors, network calls, screenshots per fase, en verbeterpunten. Pas nog niets aan.
```

## 5. Mobile-first layout reparatie

```text
Focus alleen op mobiele layoutproblemen in de actieve woningscan en homepage hero. Zoek met screenshots op 360x800, 390x844 en 430x932 naar:
- overlappende tekst;
- knoppen buiten beeld;
- te brede elementen;
- onlogische scrollsprongen;
- energielabels of meetwaarden die niet passen.
Los alleen echte layoutbugs op. Geen smaak-redesign. Rapporteer gewijzigde files en geef voor/na screenshots.
```

## 6. Desktop layout reparatie

```text
Focus alleen op desktop layoutproblemen op 1366x768, 1440x1000 en 1920x1080. Controleer homepage, gratis-isolatiescan en servicepagina. Los alleen concrete problemen op zoals:
- rare lege ruimtes;
- scan te laag/te smal/te breed;
- CTA-band die uit balans is;
- dropdown/menu-crowding;
- tekst die overloopt.
Geef voor/na screenshots en noem exact welke CSS-selectors je wijzigde.
```

## 7. Navigatie en CTA-consistentie

```text
Audit alle hoofd-CTA's en navigatielinks. Controleer dat ze naar bestaande routes gaan en logisch zijn:
- Gratis isolatiescan;
- Bekijk subsidievoordeel;
- servicekaart-links;
- footerlinks;
- privacy/algemene voorwaarden;
- telefoon/mail links.
Repareer kapotte of inconsistente links. Rapporteer een tabel met linktekst, URL, status en aangepaste files.
```

## 8. Woningscan betrouwbaarheid en fallback-copy

```text
Verbeter alleen de fout- en fallback-states van de woningscan. Denk aan:
- adres niet gevonden;
- PDOK/BAG niet bereikbaar;
- luchtfoto faalt;
- energielabel niet gevonden;
- Formspree faalt;
- mobiel netwerk traag.
Maak de teksten menselijk, kort en conversiegericht. Geen grote UI-wijzigingen. Test minimaal 2 foutscenario's en rapporteer screenshots.
```

## 9. Performance en asset-audit

```text
Doe een performance-audit zonder meteen alles te refactoren. Meet met Lighthouse of browser tooling:
- performance;
- accessibility;
- best practices;
- SEO;
- grootste render-blockers;
- ongebruikte JS/CSS als indicatie.
Rapporteer top 5 quick wins met risico/impact. Voer alleen quick wins uit die laag risico zijn en toon voor/na meting.
```

## 10. Accessibility check

```text
Audit toegankelijkheid van header, dropdowns, scan, formulieren, focus states en kleurcontrast. Gebruik keyboard-only navigatie en eventueel axe/Lighthouse. Los concrete a11y-bugs op:
- ontbrekende labels;
- onduidelijke focus;
- aria-live/foutmeldingen;
- dropdown bediening;
- contrastproblemen.
Rapporteer tests, screenshots waar relevant, en gewijzigde files.
```

## 11. Content en trust zonder nepclaims

```text
Audit alle trustclaims, reviews, garanties, certificeringen en contactgegevens. Markeer alles dat placeholder, sample data of mogelijk niet bewezen is. Belangrijk: voeg geen nep-reviews, nep-certificaten of nep-statistieken toe.
Maak copy eerlijker waar nodig en voeg duidelijke TODO-commentaar of zichtbare neutrale copy toe als data ontbreekt. Rapporteer exact welke claims je hebt aangepast.
```

## 12. SEO en schema sanity check

```text
Controleer SEO-output zonder SEO-plugin:
- title-tag;
- meta description;
- OG-tags;
- LocalBusiness schema;
- FAQ schema;
- headings per template;
- canonical/robots/sitemap status.
Los alleen duidelijke technische fouten op. Als iets beter door Yoast/RankMath kan, noteer dat apart maar installeer geen plugin zonder toestemming.
```

## 13. Codekwaliteit en dode code

```text
Audit de theme-code op dode of dubbele code. Let vooral op legacy isolatiescan vs actieve woningscan, ongebruikte CSS/JS, duplicate IDs bij meerdere scans op een pagina, en functions die niet meer worden aangeroepen.
Doe nog geen verwijderingen zonder eerst een lijst met "veilig verwijderen", "mogelijk later nodig", en "laten staan" te geven. Als je iets verwijdert, doe het klein en test daarna.
```

## 14. Formspree leadkwaliteit

```text
Controleer de Formspree payload van de actieve woningscan. Het doel: een leadmail moet voor Warmvast direct bruikbaar zijn. Check of erin staat:
- naam, email, telefoon;
- volledig adres;
- bouwjaar;
- energielabel + bron;
- gekozen maatregelen;
- m2 per maatregel;
- subsidie-indicatie;
- besparing-indicatie;
- verdubbeld ja/nee;
- klantopmerking;
- privacy akkoord.
Gebruik indien mogelijk een test-submit of inspecteer FormData zonder live submit. Verbeter veldnamen/subject alleen als dat nodig is.
```

## 15. Eindsanity en overdracht

```text
Doe een volledige eindsanity na je laatste wijzigingen:
1. git diff --stat;
2. lijst gewijzigde files;
3. screenshots desktop/mobiel van homepage en gratis-isolatiescan;
4. console/network errors;
5. scan-flow status;
6. bekende resterende risico's;
7. exacte aanbeveling voor mijn volgende prompt.
Maak geen nieuwe wijzigingen meer tijdens deze eindsanity tenzij de site duidelijk kapot is.
```

