# WARMVAST — WordPress site

Conversion-focused WordPress site for WARMVAST (Dutch insulation company), built around an
address-driven **woningscan** (PDOK building data + EP-Online energy label) with a live
**ISDE-subsidiecalculator**. Custom theme, vanilla HTML/CSS/JS, no page builder. See
[`warmvast_blueprint.md`](warmvast_blueprint.md) for the original spec — note that the scan
described there (a manual multi-step form) was superseded by the address-driven woningscan;
see Architecture below for what's actually live.

## Local setup (already done)

| Thing | Value |
|---|---|
| URL | http://localhost/warmvast/ |
| Admin | http://localhost/warmvast/wp-admin/ |
| Admin user | `warmvast_admin` / `Warmvast!2026` |
| DB | `warmvast` on `127.0.0.1:3306`, user `root` / `root` |
| WordPress | served from this repo via a Windows **junction** at `C:\MAMP\htdocs\warmvast` |
| Theme | `wp-content/themes/warmvast` (active) |

The repo folder *is* the WordPress root. MAMP serves it through the junction, so editing files
here updates the live site immediately. Only the theme is committed (see `.gitignore`); WP core
is re-downloadable.

## WP-CLI

MAMP's CLI PHP 8.3 ships without `mysqli`, so WP-CLI runs on **PHP 8.2.14** with a custom ini
(enables curl/openssl/mysqli + CA bundle). A wrapper script was used during setup:

```bash
php -c <custom-ini> wp-cli.phar --path=<repo> <command>
```

> Windows/Git Bash gotcha: never pass a leading-slash argument like `/%postname%/` to WP-CLI
> through Git Bash — MSYS rewrites it into a `C:\Program Files\Git\...` path and corrupts the
> permalink structure. Set such values via `wp eval` or prefix the command with
> `MSYS_NO_PATHCONV=1`.

## Architecture

- **Single source of truth for ISDE tariffs**: `inc/config.php` → `warmvast_isde_rates()`.
  Localised to JS via `wp_localize_script` (`WARMVAST_SCAN.rates`) so the calculator and the
  service pages can never drift.
- **The scan**: `template-parts/woningscan.php` + `assets/js/woningscan.js`, backed by a REST
  endpoint in `inc/woningscan.php` (`GET /wp-json/warmvast/v1/woningscan`). Flow: address ->
  PDOK Locatieserver + BAG WFS (footprint, bouwjaar) + luchtfoto WMS -> EP-Online energy label
  (public search, falls back to a bouwjaar estimate) -> indicative m² for vloer/dak/spouw/glas ->
  ISDE + besparing indication -> lead -> Formspree. Rendered via the `[warmvast_isolatiescan]`
  shortcode or directly with `get_template_part( 'template-parts/woningscan' )`. To preselect a
  measure (used by the service pages so e.g. `/dakisolatie/` starts with only "Dakisolatie"
  checked), set the `$warmvast_scan_preselect` global (`spouw`|`vloer`|`glas`|`dak`) before the
  `get_template_part` call and reset it to `''` right after.
  The older manual multi-step form (`template-parts/isolatiescan.php` / `assets/js/isolatiescan.js`)
  described in the blueprint has been removed — it was fully superseded and no longer enqueued.
- **Page templates**: `template-service.php` (slug-driven, all 4 services), `template-subsidie.php`,
  `template-scan.php`, `template-isolatie.php`, `template-contact.php`, `template-kennisbank.php`,
  `404.php`.
- **Service copy**: `inc/service-content.php`.
- **Nav & footer**: hand-built in `header.php` / `footer.php` (mega-dropdown with per-service
  tariffs). Assigning a WP menu to the `primary` location overrides the coded fallback.

## Deployment

`setup.sh` (repo root) turns a fresh `git clone` of this repo into a working WordPress install —
self-contained: the repo carries the theme *and* a content snapshot (`warmvast-db-localhost.sql`
— pages, kennisbank, settings), so a `git pull` on the host brings everything except WordPress
core itself (vendor code, re-downloaded by the script) and the database credentials (secrets,
can never live in git). One command supplies both:

```bash
bash setup.sh https://warmvast.nl <db_name> <db_user> <db_pass> [db_host] [db_port]
```

Or set `SITE_DOMAIN` / `DB_NAME` / `DB_USER` / `DB_PASS` (/ `DB_HOST` / `DB_PORT`) as environment
variables instead of passing args — e.g. in a hosting panel's "Startup command"/"Variables" tab,
so the whole deploy runs unattended every time the panel pulls from GitHub.

The script downloads matching WP core, writes `wp-config.php` (with fresh salts), imports the
SQL snapshot, then serialization-safely rewrites every `http://localhost/warmvast` URL to the
real domain via `wp search-replace` (never do this with a raw SQL find/replace — it corrupts
WordPress's serialized PHP arrays) and flushes permalinks. Safe to re-run; skips the core
download if `wp-load.php` already exists. The database itself (and its user) must already exist
— see the comment at the top of `setup.sh` for the one-line `CREATE DATABASE` if not.

**Repo contains a full content dump.** `warmvast-db-localhost.sql` has real (if pre-launch)
WordPress data including the admin account's hashed password. Keep this repository **private**
on GitHub, and once the site's own database is the source of truth post-launch, this snapshot
can be dropped from the repo (and ideally purged from git history) rather than kept indefinitely.

## ⚠️ Before go-live — required steps

1. ~~Formspree endpoint~~ / ~~Verify ISDE 2026 tariffs~~ / ~~Replace placeholder contact
   details~~ — still to verify against RVO / Formspree dashboard before relying on them for real
   leads, but no longer using setup-era placeholders.
2. **Privacyverklaring & Algemene voorwaarden have literal `[PLACEHOLDER]` fields** (KvK-nummer,
   vestigingsadres, and five policy terms: offerte-geldigheid, opleveringstermijn,
   **garantietermijn** — this one should also update `WARMVAST_WARRANTY_YEARS` in
   `inc/config.php` to match — annuleringstermijn, klachttermijn, and the lead bewaartermijn).
   Deliberately deferred past the initial launch per the site owner; fix before relying on these
   pages as real legal terms.
3. **Reviews are sample data.** `warmvast_reviews()` in `inc/config.php` has `verified => false`,
   which correctly suppresses the AggregateRating schema. Do not flip it to `true` until the
   `items` are real reviews — brand rule: no fabricated reviews or ratings go live.
4. **EP-Online label lookup** (`warmvast_ws_public_energylabel()` in `inc/woningscan.php`) scrapes
   EP-Online's public search page (verification token + HTML parsing) since no API key is
   configured (`WARMVAST_EP_ONLINE_API_KEY`). It already falls back gracefully to a bouwjaar
   estimate on any failure, but a real EP-Online API key would make label lookups more reliable.

## Analytics

The scan and CTAs dispatch events (`scan_start`, `scan_step_N_complete`, `scan_subsidy_seen`,
`scan_contact_step`, `scan_submit_success/error`, `phone_click`, `email_click`, `cta_click`) to
`dataLayer`, `gtag`, and as `warmvast:<event>` DOM CustomEvents. Wire up GA4/GTM to consume them.
