# Warmvast — project & design constitution

Read this before any UI work. These rules override your defaults.

## What this site is

Warmvast Isolatie: a Dutch home-insulation company. We take a homeowner from a free
scan to the last euro of RVO/ISDE subsidy. Audience: Dutch homeowners, roughly 40–70,
not technical, cautious about contractors because the sector has a poor reputation.

The site must feel like a bank's website, not a marketing agency's: calm, exact, quiet,
and obviously run by people who know the rules. Every visual decision serves one goal —
this homeowner should feel safe handing us their house.

## Language

All user-facing copy is Dutch, formal "u"-form. Code, comments, commit messages and
your replies to me are English. Never translate existing Dutch copy unless I ask.
Never invent Dutch marketing copy — write `<!-- TODO: tekst van Alex -->` and tell me.

## Stack

WordPress, custom theme. All CSS lives in assets/css/main.css. No page builder. No new
npm or composer dependencies without asking. The page must be readable and the forms
usable with JavaScript disabled.

## The design language

One system, two surfaces:
- Dark surface (--ink #1b1f21): the homepage hero, the CTA band, the footer. Used
  deliberately and rarely.
- Light surface (--paper #f6f6f2): everything else.
A transition between the two is always designed, never accidental.

## Non-negotiable rules

1. Tokens are the only source of truth. Never hardcode a hex, a px value or a duration
   in a component. If a value is missing, add a token and tell me why.
2. Spacing comes from a 4px scale only.
3. One accent colour (--accent #fed03d), reserved for the single primary action on a
   page. Two yellow buttons in one viewport means one of them is wrong.
4. Backgrounds belong to the page, not to sections. A section never carries a
   gradient. Ever. This rule exists because violating it is what broke this site.
5. Every section that needs visual separation uses spacing or a hairline, not a
   background colour.
6. Motion is transform and opacity only, 120–240ms, ease-out. Decorative motion does
   not exist. Everything respects `prefers-reduced-motion: reduce`.
7. Every interactive element has a visible `:focus-visible` state.
8. Text measure caps at 65 characters. Long paragraphs are never centered.

## Forbidden — these are the tells of a generated page

- Emoji anywhere in the UI.
- Grids of icon cards with circular check marks used instead of content.
- An eyebrow label above every section. Two per page maximum.
- Purple or indigo gradients, glassmorphism, neon glow.
- `transition: all`, `!important`, inline styles, arbitrary z-index numbers.
- More than two elevation levels of box-shadow.
- Centered body text longer than one line.
- Placeholder copy, invented statistics, invented reviews, invented certifications.
- Stock photography, scraped photography, or AI-generated imagery of any kind.
  Images are either real Warmvast photographs or clearly-labelled grey placeholders.
- Infinite background animations.

## Definition of done for any UI change

1. Renders correctly at 360, 768, 1024, 1440 and 1920px.
2. No horizontal scroll at any width.
3. Body text contrast ≥ 4.5:1, large text ≥ 3:1.
4. No layout shift on load — space reserved for every image and embed.
5. Tab order matches visual order, focus always visible, no traps.
6. Zero new console errors or warnings.
7. The diff touches only the files listed in your plan.

## How to work with me

- Anything larger than a one-line change: plan first, wait for approval.
- State what you are uncertain about instead of guessing.
- After a change, tell me what to open and what I should see.
- Never say something is fixed if you have not seen it render.
- If a rule here blocks the best solution, argue for the exception. Do not ignore it.