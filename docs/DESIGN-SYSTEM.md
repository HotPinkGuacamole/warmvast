# Warmvast design system

**Decision (option C):** the dark surface is a rare, deliberate accent — the homepage hero, the
CTA band, the footer. Nothing else. Everything else is paper.

**Governing idea:** semantic names are surface-scoped. A component asks for `text-secondary`; the
surface it sits on decides the value. No component ever names a colour. This is what collapses the
14 hand-picked greys currently used for text on dark into one role with two definitions.

---

## 1. Surfaces

| Surface | Where | Never |
|---|---|---|
| **dark** (`--ink`) | Homepage hero. The CTA band. The footer. | Subpage openings. Cards. Sections. Anything a 5th time on one page. |
| **paper** (`--paper`) | Every page body, every subpage opening, every section. | — |

A subpage does not have a hero. It opens on paper: breadcrumb, `h1`, sub, separated by spacing.
There is no band, no tint, no gradient. A section never carries a background.

### The transition: a declared edge

**One mechanism.** Where dark meets light, the boundary is a dead-straight, full-bleed horizontal
edge at 15.33:1 contrast, with a full `loose` rhythm of empty surface on both sides. Nothing
straddles it, nothing fades across it, nothing is colour-matched to conceal it.

**Why this and not a fade.** A seam is an edge you tried to hide and half-succeeded at. Every
concealment mechanism relocates the problem: a gradient fade terminates *somewhere* and that
terminus is the new seam; a colour-matched card straddling the edge creates two concave corners,
which is why `.usp-bar__notch` exists as 60 lines of masked pseudo-elements. A boundary that is
unambiguously intentional — straight, full width, high contrast, clear air on both sides — reads as
a designed division, the way the rule above a bank statement's footer does. **Never blend; declare.**

**Consequence, stated plainly:** `.usp-bar` cannot straddle the hero edge. It moves fully inside the
dark hero or fully onto paper below it. Pick one.

---

## 2. Colour

All values exist in `main.css` today. One token is added (`--error`, §2.3). Implemented as
`--surface-base` / `-raised` / `-sunken` (`--surface` already names `#ffffff` in 21 places; taking
`surface` for the page role would have required editing every one of them).

`--paper` is corrected from `#f6f6f2` to **`#f4f4ef`** — the colour the page has always actually
been painted (`body`, `.site-main`, `.usp-bar__grid` all hardcoded `#f4f4ef` independently; `--paper`
itself had zero uses). Contrast figures below use the corrected value.

| Role | Paper scope | Dark scope |
|---|---|---|
| `surface-base` | `--paper` `#f4f4ef` | `--ink` `#1b1f21` |
| `surface-raised` | `--surface` `#ffffff` | — (no raised surface on dark) |
| `surface-sunken` | `--surface-2` `#fbfbf9` | — |
| `text-primary` | `--ink` | `#ffffff` |
| `text-secondary` | `--ink-2` `#3c4348` | `#b9c4c0` |
| `text-quiet` | `--ink-3` `#666e74` | `#8b9893` |
| `border-hairline` | `--line` `#e6e7e1` | `rgba(255,255,255,.12)` |
| `border-strong` | `--ink-3` `#666e74` | `--line` `#e6e7e1` |
| `accent` / `accent-hover` / `accent-text` | `--accent` `#fed03d` / `--accent-strong` `#f6c200` / `--accent-ink` `#2b2400` | same |
| `focus-ring` | `--primary` `#0b6b5b` | `--accent` `#fed03d` |

### 2.1 Measured contrast — every text-on-surface pair

| Pair | Ratio | |
|---|---|---|
| text-primary on paper / raised / sunken | 15.05 / 16.61 / 16.03 | ✓ |
| text-secondary on paper / raised / sunken | 9.11 / 10.05 / 9.70 | ✓ |
| text-quiet on paper / raised / sunken | **4.70** / 5.19 / 5.01 | ✓ thin |
| text-primary / -secondary / -quiet on dark | 16.61 / 9.27 / 5.54 | ✓ |
| accent-text on accent / accent-hover | 10.56 / 9.32 | ✓ |
| accent on dark | 11.31 | ✓ |
| `--primary` on paper / raised | 5.82 / 6.43 | ✓ |
| white on `--primary` / `--primary-700` (button) | 6.43 / 10.19 | ✓ |
| `--success` on `--success-bg` | 4.81 | ✓ |
| `--warn` on `--warn-bg` | 4.95 | ✓ |
| text-quiet on `--primary-050` | **4.53** | ✗ zero margin |
| focus-ring `--primary` on dark | **2.58** | ✗ fails 3:1 |
| border-strong `--line-strong` on raised | **1.44** | ✗ fails 3:1 |
| current field focus halo `--primary-050` on sunken | **1.10** | ✗ invisible |

### 2.2 The four failures, smallest fix each

1. **text-quiet on tinted fills (4.53).** Passes by 0.03 — not a margin, an accident.
   *Fix:* `text-quiet` is permitted on neutral surfaces only. On `--primary-050`, `--success-bg`,
   `--warn-bg`, step up to `text-secondary` (8.78 / 9.02 / 9.01). No colour changes.
2. **Focus ring invisible on dark (2.58).** *Fix:* `focus-ring` is surface-scoped —
   `--primary` on paper (5.93), `--accent` on dark (11.31). One token, two definitions.
3. **Form field borders (1.44).** A field's border is the only thing saying "type here", so
   WCAG 1.4.11's 3:1 applies. *Fix:* `border-strong` is `--ink-3`, not `--line-strong`
   (5.01 on the field fill, 5.19 on the card behind it). `--line-strong` is retired.
   Decorative hairlines (`--line`, 1.15) stay — they separate, they don't identify.
4. **Field focus halo (1.10).** *Fix:* delete it. Fields use the same `focus-ring` outline as
   everything else. `outline: none` is banned.

### 2.3 One added token, and why

The palette has no error colour. `--warn` `#a35314` is a caution amber and must stay distinct from
a form error — a homeowner who mistypes a postcode and a note about RVO conditions cannot look the
same. Per rule 1, a token is added rather than a hex improvised at the call site:

**`--error: #a3201c`** — the same red channel and lightness band as `--warn`, so it reads as its
sibling, not an import. Paper 6.97, raised 7.55, sunken 7.29. Error is never signalled by colour
alone: colour + a text message adjacent to the field.

### 2.4 Standing rules

- `--accent` is a **fill**, never text on paper (1.36 — unusable). As text it is dark-scope only.
- One accent element per viewport. Two yellow things means one is wrong.
- `--primary-600`, `--accent-700`, `#f4f4ef` and all 90 ad-hoc literals are retired.

---

## 3. Type

**Ratio: 1.25 (major third), base 17px.** A major third is the smallest ratio that still reads as
hierarchy at body size; 1.333 and above force display type past 60px, which on a calm site reads as
advertising rather than banking. At a 17px base every step lands within a pixel of a whole number.

| Step | Size | Tracking | Line-height | Use |
|---|---|---|---|---|
| `--step--1` | 13.6px | 0 | 1.6 | labels, captions — **the floor; nothing smaller ships** |
| `--step-0` | 17px | 0 | 1.6 | body |
| `--step-1` | 21.25px | -0.005em | 1.5 | lead paragraph, h3 |
| `--step-2` | 26.5px | -0.01em | 1.35 | h3 large, figures |
| `--step-3` | 33.25px | -0.02em | 1.22 | h2 |
| `--step-4` | 41.5px | -0.025em | 1.12 | page h1 |
| `--step-5` | 52px | -0.03em | 1.05 | **homepage h1 only** |

`--measure: 65ch` ≈ 600px at step 0. It is the max-width of every run of prose, including
`.prose` (today 760px ≈ 88 chars) and FAQ answers (today 820px ≈ 95 chars). Centred text is
permitted for one line only.

---

## 4. Spacing

4px base. `--space-1:4` `-2:8` `-3:12` `-4:16` `-5:24` `-6:32` `-7:48` `-8:64` `-9:96` `-10:128`.
Nothing off this scale. The current stylesheet holds 51 literal values, 20 of them unreachable here.

**Three section rhythms, and no fourth:**

| | Mobile → desktop | Use |
|---|---|---|
| `tight` | 32 → 48px | between blocks of one idea |
| `normal` | 48 → 80px | between ideas — the default |
| `loose` | 80 → 128px | either side of a declared edge (§1) |

**A rhythm is a gap, not a pair of paddings.** Blocks own `margin-block-start` only, never
`padding-block` on both sides. Two adjacent blocks therefore produce one rhythm, not two — which is
what currently yields 144px of empty page between two `--paper` sections.

---

## 5. Radius

`--radius-1:4` `-2:8` `-3:12` `-4:16` `-5:20` `-6:24`, plus `--radius-pill:999px`.

**Nesting rule: an outer container's radius equals its child's radius plus the padding between
them.** Both scales are 4px-based, so the arithmetic always lands on a real token.

> A field at `--radius-2` (8px) inside a card padded `--space-4` (16px) → the card is
> `--radius-6` (24px). Not 22px, not 14px, not `calc(var(--radius-lg) + 8px)`.

`50%` is permitted only on a true circle (avatar, numbered marker).

---

## 6. Elevation

**Two levels. Hairline borders are the default; shadow is the exception.** A card is identified by
its raised surface and a `border-hairline` — not by a shadow. Shadow is reserved for elements that
overlap other content.

| | Value | Only for |
|---|---|---|
| `--elevation-1` | `0 1px 2px rgba(20,30,28,.06)`, `0 2px 6px rgba(20,30,28,.05)` | an element the cursor has lifted |
| `--elevation-2` | `0 4px 8px rgba(12,40,34,.08)`, `0 12px 24px rgba(12,40,34,.10)` | dropdown, off-canvas panel, the scan card over the hero |

Both colours already exist in `main.css`. `--shadow-lg`, `--shadow-glass`, `--shadow-float` and the
eight hardcoded shadows are retired — 13 elevation treatments become 2.

---

## 7. Motion

| | | |
|---|---|---|
| `--duration-1` | 120ms | colour, opacity — state change |
| `--duration-2` | 180ms | transform — movement |
| `--duration-3` | 240ms | panel entrance. **The ceiling.** |
| `--ease-out` | `cubic-bezier(.22,.61,.36,1)` | everything that enters or settles |
| `--ease-in-out` | `cubic-bezier(.65,0,.35,1)` | only what both enters and leaves |

- **Transform and opacity only.** Never `gap`, `width`, `margin`, `background-position`.
- **Decorative motion does not exist. Infinite animation does not exist.** No
  `iteration-count: infinite` anywhere; no `requestAnimationFrame` loop driving CSS custom
  properties. The one permitted exception is a loading indicator while a real request is in flight.
- No `transition: all`. No transition-delay stagger.
- `prefers-reduced-motion: reduce` sets duration to 0 — it never substitutes a different animation.

**This retires:** `hero-drift` (16s, ×2), `wv-hero-aurora` (34s), `wv-card-trace` (3.6s conic
comet), `wv-hero-bloom`, `.btn--sheen`, the orb blur layers, the parallax, the scroll-progress bar,
the reveal cascade, and the ambient pointer loop that `main.js:22` itself documents as "a real
repaint, every frame, forever."
