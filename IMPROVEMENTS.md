# WARMVAST — Critical Improvement Checklist

A deliberately harsh audit of the current build, with precise design decisions and
patterns borrowed from strong Dutch isolation sites (isolatiebedrijven, subsidie-portals).
Grouped by theme, prioritized **P0** (broken / do now) → **P3** (nice-to-have).

Legend: `[x]` done · `[~]` partially done · `[ ]` open · **needs client asset** = I cannot
build this honestly without real data (brand rule: no fake reviews/certs).

---

## ✅ Shipped so far (2026-07-07)

**P0 bugs**: hero copy jump · hero scan invisible-until-scroll · wide result-panel void · anchor-under-header.
**Glamour / motion**: count-up animation on the subsidy total · animated scan step transitions · smooth FAQ open · button sheen · service-card top accent · staggered card reveals · animated thermal gradient.
**Identity (bolder direction)**: self-hosted **Space Grotesk** display font for headings + tabular-figure numbers · **animated thermal-house** watermark in the hero (glowing roof + rising heat waves, reduced-motion aware).
**Trust**: **USP bar** under the hero (honest process claims) · **reviews section** built as a data-driven component (`warmvast_reviews()`), currently SAMPLE data — `verified=false` gates the review schema until you paste real Google reviews.
**Content**: result-panel empty-state ("wat u ontvangt") replacing the €0 void.

---

## 0. Bugs & correctness (P0)

- [x] **Hero copy jumps/re-centers as the scan grows** (your report). Cause: `align-items:center`. Fixed → top-aligned.
- [x] **Hero scan invisible until scroll** — it had `data-reveal` (opacity:0). A critical above-the-fold element must never start hidden. Removed.
- [x] **Wide result panel = tall empty void** (disclaimer pinned to bottom). Regrouped content to top.
- [x] **Anchor jumps hide behind sticky header.** Added `scroll-margin-top`.
- [ ] **Wide scan structure**: the full-width white header sits above both columns, so the dark result panel starts lower than the form and leaves a corner notch. Restructure: move the header *inside* the form column, let the result panel span the full card height.
- [ ] **`scrollIntoView` on hero "Volgende"** pushes the H1 off-screen — on the *homepage* hero this feels like the page lurching. Only auto-scroll when the scan is NOT already fully in view.
- [ ] Register a **WP nav menu** for `primary`/`footer` (currently hand-coded fallback) so content is editable — OR document clearly that nav is code-managed.

---

## 1. Trust & credibility — the #1 CRO lever for isolation (P1)

Every high-converting isolation site leads with proof. We have almost none yet.

- [ ] **USP bar** directly under the hero: 4 icon+label items (e.g. *Erkend installateur · Vaste prijsafspraak · Subsidie geregeld · 10 jaar garantie*). Sticky-adjacent, high contrast. **needs client asset** (which claims are true).
- [ ] **Reviews block**: Google/Trustpilot/Klantenvertellen star rating + 2–3 quotes. Isolation buyers lean heavily on peer proof. **needs client asset** (no fake reviews — brand rule). Build the *component* now, wire real data later.
- [ ] **Certification / guarantee badges** near every form and in the footer: KOMO, InstallQ, Bouwgarant, ISSO, "Erkend leerbedrijf", garantie-jaren. **needs client asset**.
- [ ] **"Bekend van" / partner logos** strip (media or supplier brands). **needs client asset**.
- [ ] **Impact stats band** with count-up animation: *woningen geïsoleerd*, *m² geplaatst*, *gem. besparing*, *€ subsidie begeleid*. **needs client asset** (real numbers).
- [ ] **Team / "de mensen achter WARMVAST"**: real photo(s) build trust for an in-home service. **needs client asset**.
- [ ] **Region coverage**: a simple NL map or province list reassures "werkt dit in mijn regio?".
- [ ] Show a **real, named contact + response promise** near forms ("Vraag? Bel Mark — 085…").

## 2. Content & logic gaps (P1–P2)

- [ ] **Energy-bill savings, not just subsidy.** Isolation buyers care about *besparing per jaar* more than subsidy. Add an indicative €/jaar saving per measure to the scan result (clear disclaimer, assumptions documented). This roughly doubles the perceived value of the calculator.
- [ ] **"Wat u ontvangt na de scan"** mini-list inside the empty result panel (step 1) so it's never a dead €0 void.
- [ ] **Comparison / keuzehulp**: a small "welke maatregel eerst?" decision aid on /isolatie/.
- [ ] **Honest urgency**: ISDE budget/validity note ("subsidie geldt zolang budget beschikbaar is") — factual, not fake scarcity.
- [ ] **Kennisbank**: only 4 stub articles; blueprint lists 8. Add the rest + internal links + featured images.
- [ ] **Contact page**: add a map, KvK/BTW, and a real address block. **needs client asset**.
- [ ] **404 page** template (`404.php`) with search + links back to scan.
- [ ] **Thank-you state**: after submit, offer next step (bel-nu, WhatsApp, or "bekijk uw maatregelen") instead of a dead-end confirmation.

## 3. Design polish & glamour (P2)

- [ ] **A display typeface for headings.** System font is clean but generic; a self-hosted variable font (e.g. a grotesk like *Space Grotesk*/*Fraunces* for numbers) adds real character. Self-host = still fast, no CLS.
- [ ] **Hero imagery.** Replace the pure-gradient hero with a real photo or a crafted **thermal-camera house illustration** (SVG) with subtle animated heat-loss → sealed states. Currently it reads a bit flat/empty on the left.
- [ ] **Numeric styling**: tabular figures + a mono/grotesk for € amounts and m² — makes the calculator feel like an instrument.
- [ ] **Section transitions**: alternating backgrounds are fine but add subtle **angled/rounded dividers** or a thin hairline rule for rhythm.
- [ ] **Cards**: add a faint top accent line or number chip; the service cards could show a small thermal swatch.
- [ ] **Verdubbelaar section**: make it the visual centerpiece — bigger, an animated bar that visibly *doubles*, before/after amount morph.
- [ ] **Buttons**: add a subtle hover sheen and pressed state; accent button could get a soft glow on the primary CTA only.
- [ ] **Consistent iconography weight** and a couple of custom brand icons (spouw/vloer/glas/dak) instead of generic outlines.
- [ ] **Photography section**: a small gallery of "ons werk" (thermal + placement). **needs client asset**.
- [ ] **Footer**: add the USP/garantie row and a newsletter or "download subsidiegids" lead magnet.

## 4. Animation & micro-interactions (P2)

- [ ] **Count-up on the subsidy total** — animate from old → new value on change (not just a scale bump). Signature moment.
- [ ] **Verdubbelaar reveal**: when the 2nd measure is ticked, animate the number *doubling* with a brief highlight sweep + the badge sliding in.
- [ ] **Scan step transitions**: slide/fade between steps instead of instant show/hide; animate the progress bar fill.
- [ ] **Selectable tiles**: animated check + subtle scale when a woningtype/maatregel is chosen.
- [ ] **FAQ accordion**: animate open/close height (native `<details>` snaps).
- [ ] **Stat counters** animate on scroll-into-view.
- [ ] **Hero thermal gradient**: slow, looping drift (respect `prefers-reduced-motion`).
- [ ] **Refine reveal**: add a small **stagger** to grids (cards cascade) and shorten distance/duration so it feels crisp, not laggy.
- [ ] Everything above must **honor `prefers-reduced-motion`** (already scaffolded).

## 5. Scan UX specifics (P1)

- [ ] **Persistent result on desktop** in the *card* (hero) layout too — currently the € sits below the form, so on step 1–2 you don't see it without scrolling the card. Add a compact running-total bar at the top of the card.
- [ ] **Inline validation** (per-field, on blur) instead of a single generic error line.
- [ ] **Postcode format help / auto-uppercase**; consider postcode→address lookup (Dutch API) to reduce friction. **needs API key**.
- [ ] **Autosave to localStorage** so a refresh doesn't wipe progress.
- [ ] **Show the doubling opportunity proactively**: if only 1 measure is selected, nudge "voeg een 2e maatregel toe en verdubbel uw tarief".
- [ ] **m² guidance**: tiny helper ("gemiddelde rijwoning ≈ 45 m² gevel") per field.
- [ ] **Submit spinner** inside the button (currently text swap only).

## 6. Performance, SEO & technical (P2)

- [ ] Self-host fonts (if added) with `font-display: swap`; preload the display face.
- [ ] Add **real favicon set** (PNG/ICO + apple-touch-icon) and an **OG share image**.
- [ ] `wp_localize_script` is fine, but **only load `isolatiescan.js` where the scan exists** (conditional enqueue) to trim other pages.
- [ ] Add **BreadcrumbList schema** (we have LocalBusiness + FAQ).
- [ ] Add `<link rel="canonical">`, XML sitemap (or Yoast/RankMath), robots review.
- [ ] Image strategy: lazy-load, width/height to avoid CLS, AVIF/WebP.
- [ ] Lighthouse pass (perf/a11y/best-practices/SEO) — target 95+.
- [ ] Consider **caching**/minification for production; version assets by content hash.

## 7. Accessibility (P1)

- [ ] Audit color contrast of muted greys on paper (some `--ink-3` on `--paper` is borderline).
- [ ] Ensure the scan is fully **keyboard operable** incl. the tiles and step focus (mostly there — verify).
- [ ] `aria-current` on active nav; visible focus on dropdown items.
- [ ] The mega-dropdown must be keyboard/screenreader navigable (hover-only is a risk).
- [ ] Announce step changes and the live total to assistive tech (`aria-live` present — verify wording).
- [ ] Prefers-reduced-motion covers all new animations.

## 8. Conversion mechanics (P2)

- [ ] **Sticky mobile action bar** (Bel · Start scan) on all pages, not only the scan result.
- [ ] **Exit-intent / scroll-depth** soft prompt to start the scan (tasteful).
- [ ] **A/B hooks** for the blueprint's tests (hero CTA copy, scan placement, phone required).
- [ ] **WhatsApp / callback** option for users who won't fill a form.
- [ ] Lead-magnet: "Gratis ISDE-subsidiegids (PDF)" email capture as a secondary conversion.

---

## Suggested execution order

1. **P0 structural**: finish wide-scan restructure + hero auto-scroll guard. *(this batch)*
2. **P1 design/animation that needs no assets**: count-up total, verdubbelaar animation, step transitions, hero illustration, USP bar (placeholder copy), result-panel empty state, desktop running total, savings estimate. *(next batch)*
3. **P1 trust components as reusable blocks** wired to placeholder/among data, ready for real content.
4. **Client-asset items** once you provide: reviews, certifications, real photos, stats, guarantees, address.
