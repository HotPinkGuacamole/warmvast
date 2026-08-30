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
| Admin user | `warmvast_admin` — password stored in your own local password manager, never here |
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
  ISDE + besparing indication -> lead (see below). Rendered via the `[warmvast_isolatiescan]`
  shortcode or directly with `get_template_part( 'template-parts/woningscan' )`. To preselect a
  measure (used by the service pages so e.g. `/dakisolatie/` starts with only "Dakisolatie"
  checked), set the `$warmvast_scan_preselect` global (`spouw`|`vloer`|`glas`|`dak`) before the
  `get_template_part` call and reset it to `''` right after.
  The older manual multi-step form (`template-parts/isolatiescan.php` / `assets/js/isolatiescan.js`)
  described in the blueprint has been removed — it was fully superseded and no longer enqueued.
- **Lead intake**: `inc/lead.php` (`POST /wp-json/warmvast/v1/lead`). The browser posts the form
  to *this site*, which validates it and forwards a minimal, HMAC-signed JSON payload to n8n;
  n8n creates/reuses the Teamleader Contact and its Deal. WordPress knows nothing about
  Teamleader — no OAuth, no API endpoints, no IDs, no pipelines — and the webhook URL/secret
  never reach the browser. The production webhook URL defaults to
  `https://n8n.warmvastisolatie.nl/webhook/warmvast-site-lead` and can be overridden with
  `WARMVAST_N8N_LEAD_WEBHOOK_URL`; the HMAC key must be supplied outside Git as
  `WARMVAST_N8N_LEAD_WEBHOOK_SECRET` (host environment preferred, or gitignored `wp-config.php`).
  While either is empty the form refuses to submit and tells the visitor to phone, rather than
  dropping a lead. **Only the six canonical fields are sent** — name, email, phone,
  address{street,postalCode,city}, measures[], customerComment. The scan's estimates (bouwjaar,
  energielabel, m² per bouwdeel, ISDE, besparing, verdubbeling) stay on the site on purpose:
  they are indications, and indications are the wrong thing to store as authoritative CRM data.
  Measures cross the wire as keys and are mapped to labels server-side against
  `warmvast_isde_rates()`, so a client cannot invent a service. Signature n8n must verify:
  `HMAC_SHA256("<X-Warmvast-Timestamp>.<raw JSON body>", secret)`. Only a 2xx from n8n is
  reported to the visitor as success. The lead endpoint is rate-limited at 5 submissions per
  600 seconds with transient keys shaped as `warmvast_lead_rl_<md5(client-ip)>`. Client IPs use
  `REMOTE_ADDR` unless the immediate peer is configured as a trusted reverse proxy via
  `WARMVAST_TRUSTED_PROXY_IPS` (comma-separated exact IPs/CIDRs, for example
  `10.0.0.5,10.0.0.6,2001:db8::/48`); only then is `X-Forwarded-For` parsed from the trusted
  side back toward the visitor. Run `php tools/test-lead-payload.php` after touching any of this.
- **Page templates**: `template-service.php` (slug-driven, all 4 services), `template-subsidie.php`,
  `template-scan.php`, `template-isolatie.php`, `template-contact.php`, `template-kennisbank.php`,
  `template-over-warmvast.php`, `template-ons-werk.php`, `template-gemeente(s).php`,
  `template-zakelijk.php`, `template-kwaliteit.php`, `404.php`. Slug→template mapping is also
  enforced in code — see `warmvast_page_template_fallback()` and the Deployment note below.
- **Service copy**: `inc/service-content.php`. **Team**: `warmvast_team()` in `inc/config.php`.
- **Photography**: real photos only, in `assets/img/team/` and `assets/img/werk/`. They ship as
  width-suffixed WebP variants (`team-groep-480.webp`, `-960.webp`) served via `srcset` by
  `warmvast_responsive_img()`, and are pre-cropped to the exact aspect of the slot they render
  into — so CSS never has to guess a crop and clip someone's head. Regenerate with
  `python tools/build-images.py` (see that file's header for the reasoning); the camera originals
  are gitignored, only the derived variants are committed.
- **Shared layout components**: `.story-row` (small photo + copy, alternating sides, used by
  Over Warmvast, Ons werk and Subsidie service) and `.team-card`, both in `assets/css/main.css`.
  Motion is split across separate elements on purpose — `.story-row__media` takes the
  scroll-reveal, `.story-row__drift` the parallax, the `<img>` the hover — because all three
  animate `transform` and would otherwise silently overwrite one another.
- **Nav & footer**: hand-built in `header.php` / `footer.php` (mega-dropdown with per-service
  tariffs). Assigning a WP menu to the `primary` location overrides the coded fallback.

## Deployment — Endurer Hosting

Hosted on an Endurer Hosting Pterodactyl egg, which drives deploys via two of its own scripts:
`generic/latest.sh` (git-pulls a repo, or downloads+extracts a GitHub/GitLab/Gitea release, into
`GIT_TARGET_DIR`) and `website/wordpress/latest.sh` (installs nginx/MariaDB/PHP-FPM if missing,
starts MariaDB against the persistent `/home/container/mysql` volume, creates a `wordpress`
database and a matching `wp-config.php` if they don't exist yet, then serves nginx). Neither of
those downloads WordPress core itself, and neither imports any content — this repo supplies both:

- **`warmvast-db.sql`** — a full content snapshot (pages, kennisbank, settings), already
  domain-corrected for the live site via `wp search-replace ... --export` (never a raw SQL
  find/replace — that corrupts WordPress's serialized PHP arrays).
- **`start.sh`** — the wrapper that ties it together: runs Endurer's `wordpress/latest.sh` in the
  background, waits for MariaDB + `wp-config.php`, imports `warmvast-db.sql` **only** if
  `wp_options` doesn't exist yet (i.e. only on the very first boot — every later restart reuses
  the persistent MariaDB volume and skips the import, so it never clobbers real content/leads),
  then hands off to nginx.

**Panel setup (once):**
- Variables tab: `GIT_REPO_URL` = this repo's URL, `GIT_TARGET_DIR` = `/home/container/www`
  (the wordpress script expects WP core in `.../www`), `GIT_PULL_ON_RESTART` = `true`.
- Startup Command:
  ```
  curl -fsSL https://startup.endurerhosting.com/generic/latest.sh | bash && bash /home/container/www/start.sh
  ```
- Production secrets live in `/home/container/.warmvast-env`, outside Git. `start.sh` exports
  that file before PHP/nginx/WordPress services start, so use shell assignments such as
  `WARMVAST_N8N_LEAD_WEBHOOK_SECRET=...` there.

**WordPress core is committed** (see `.gitignore`), so `generic/latest.sh` pulls an immediately
servable webroot — no separate install step. Update core with wp-cli (`wp core update`) and
re-commit the result; it is deliberately not a deploy-time build step.

**Page templates do not depend on the database.** Which template a page uses is normally stored
per page as `_wp_page_template` meta, which makes it deploy state — and `start.sh` imports
`warmvast-db.sql` only on the *first* boot, so a page added after the dump was taken (or a host
whose MariaDB volume predates it) would silently fall back to `page.php` and show raw editor
content. `warmvast_page_template_fallback()` in `functions.php` maps the known slugs to their
templates in code, deriving the service and gemeente slugs from `inc/config.php` so new ones need
no edit. An explicit choice made in the editor still wins. If you add a page with its own
template, add it to that map rather than relying on the dump.

**Repo contains a full content dump.** `warmvast-db.sql` has real (if pre-launch) WordPress data
including the admin account's hashed password. Keep this repository **private** on GitHub, and
once the live database is the source of truth post-launch, this snapshot can be dropped from the
repo (and ideally purged from git history) rather than kept indefinitely.

## ⚠️ Before go-live — required steps

Anything the code can enforce is enforced; what is left needs facts nobody can derive from the
repo. **Items 1 and 2 are blockers — they are legal text, not cosmetics.**

1. **Both legal pages still contain `[KVK-NUMMER]`** and `[DATUM INVULLEN BIJ PUBLICATIE]`.
   Vestigingsadres, e-mail and telefoon are now filled from `inc/config.php` and correct; the KvK
   number cannot be guessed. Fill both, then set the date to the actual publication date.
2. **Algemene voorwaarden has five `[AANTAL]` terms** (offerte-geldigheid, opleveringstermijn,
   **garantietermijn**, annuleringstermijn, klachttermijn) and the Privacyverklaring one
   (bewaartermijn for leads). The garantietermijn must match `WARMVAST_WARRANTY_YEARS` in
   `inc/config.php` — that constant is `0` today, which correctly hides every garantie claim
   sitewide until a real term is set.
3. **Reviews do not render.** `warmvast_reviews()` has `verified => false`, which now hides the
   whole section *and* the AggregateRating schema (it used to hide only the schema, so the sample
   rows were reaching visitors). Flip to `true` only once `items`, `rating` and `count` are real:
   publishing invented reviews is an oneerlijke handelspraktijk, not a placeholder.
4. **Verify ISDE 2026 tariffs against RVO** (`warmvast_isde_rates()`), and set
   `WARMVAST_N8N_LEAD_WEBHOOK_SECRET` in `/home/container/.warmvast-env`. The production n8n URL is the
   tracked default, and `WARMVAST_N8N_LEAD_WEBHOOK_URL` is available only if it ever needs to be
   overridden. Until the secret is set the lead form cannot submit at all (by design — see Lead
   intake above). Confirm end to end that a test submission reaches n8n and lands as a Teamleader
   Deal before relying on it. Also set `WARMVAST_TRUSTED_PROXY_IPS` to Endurer's real
   reverse-proxy IP/CIDR allowlist before relying on per-visitor production lead throttling; do
   not guess these values.
5. **EP-Online label lookup** (`warmvast_ws_public_energylabel()` in `inc/woningscan.php`) scrapes
   EP-Online's public search page since no API key is configured
   (`WARMVAST_EP_ONLINE_API_KEY`). It falls back gracefully to a bouwjaar estimate, but a real
   API key would make lookups more reliable.

**Editing legal text after launch:** these two pages live in the database, and `start.sh` imports
`warmvast-db.sql` only on the *first* boot. On a host that has already booted, edit them in
wp-admin — re-committing the dump will not reach it. Contact details are duplicated as literal
text there on purpose (a legal document should state stable, auditable values rather than pull
live constants), so if `inc/config.php` contact data ever changes, mirror it into both pages.

## Analytics

The scan and CTAs dispatch events (`scan_start`, `scan_step_N_complete`, `scan_subsidy_seen`,
`scan_contact_step`, `scan_submit_success/error`, `phone_click`, `email_click`, `cta_click`) to
`dataLayer`, `gtag`, and as `warmvast:<event>` DOM CustomEvents. Wire up GA4/GTM to consume them.
