# WARMVAST — WordPress site

Conversion-focused WordPress site for WARMVAST (Dutch insulation company), built around a
multi-step **isolatiescan** with a live **ISDE-subsidiecalculator**. Custom theme, vanilla
HTML/CSS/JS, no page builder. See [`warmvast_blueprint.md`](warmvast_blueprint.md) for the spec.

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
- **The scan**: `template-parts/isolatiescan.php` + `assets/js/isolatiescan.js`. Reusable via
  shortcode `[warmvast_isolatiescan measure="spouw"]`, or by setting `$warmvast_scan_layout`
  (`card`|`wide`) / `$warmvast_scan_preselect` before `get_template_part`.
- **Page templates**: `template-service.php` (slug-driven, all 4 services), `template-subsidie.php`,
  `template-scan.php`, `template-isolatie.php`, `template-contact.php`, `template-kennisbank.php`.
- **Service copy**: `inc/service-content.php`.
- **Nav & footer**: hand-built in `header.php` / `footer.php` (mega-dropdown with per-service
  tariffs). Assigning a WP menu to the `primary` location overrides the coded fallback.

## ⚠️ Before go-live — 2 required steps

1. **Formspree endpoint.** In `inc/config.php`, replace `WARMVAST_FORMSPREE` (`REPLACE_WITH_ID`)
   with the real form id. The scan refuses to submit while the placeholder is present.
2. **Verify ISDE 2026 tariffs** in `inc/config.php` against RVO. They are currently taken from the
   blueprint and dated but unverified:
   https://www.rvo.nl/subsidies-financiering/isde/woningeigenaren/isolatiemaatregelen

Also replace the placeholder **contact details** (phone/email/hours) in `inc/config.php`.

## Analytics

The scan and CTAs dispatch events (`scan_start`, `scan_step_N_complete`, `scan_subsidy_seen`,
`scan_contact_step`, `scan_submit_success/error`, `phone_click`, `email_click`, `cta_click`) to
`dataLayer`, `gtag`, and as `warmvast:<event>` DOM CustomEvents. Wire up GA4/GTM to consume them.
