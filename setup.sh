#!/usr/bin/env bash
#
# Warmvast one-shot deploy setup — fully self-contained.
#
# The repo carries everything content-wise (theme + warmvast-db-localhost.sql
# content snapshot); only WordPress core and the database *credentials* can't
# live in git (core is vendor code, credentials are secrets). This script
# supplies both, so a `git clone`/`git pull` of this repo + running this
# script is the entire deploy.
#
# Reads its settings from environment variables first (so a hosting panel's
# "Startup command" can wire this up to run automatically on every deploy —
# just set these as the container's startup variables), falling back to
# positional CLI args for a manual run:
#
#   bash setup.sh [domain] [db_name] [db_user] [db_pass] [db_host] [db_port]
#
# Env var names (override to match whatever your panel injects):
#   SITE_DOMAIN, DB_NAME (or DB_DATABASE), DB_USER (or DB_USERNAME),
#   DB_PASS (or DB_PASSWORD), DB_HOST, DB_PORT
#
# The database itself (and its user) must already exist — this script
# imports INTO it, it does not create it. If it doesn't exist yet:
#   mysql -u root -p -e "CREATE DATABASE warmvast CHARACTER SET utf8mb4;
#     CREATE USER 'wv_user'@'%' IDENTIFIED BY 'S3cret!';
#     GRANT ALL PRIVILEGES ON warmvast.* TO 'wv_user'@'%'; FLUSH PRIVILEGES;"

set -euo pipefail

DOMAIN="${1:-${SITE_DOMAIN:-}}"
DB_NAME="${2:-${DB_NAME:-${DB_DATABASE:-}}}"
DB_USER="${3:-${DB_USER:-${DB_USERNAME:-}}}"
DB_PASS="${4:-${DB_PASS:-${DB_PASSWORD:-}}}"
DB_HOST="${5:-${DB_HOST:-127.0.0.1}}"
DB_PORT="${6:-${DB_PORT:-3306}}"
SQL_FILE="warmvast-db-localhost.sql"
OLD_URL="http://localhost/warmvast"

if [ -z "$DOMAIN" ] || [ -z "$DB_NAME" ] || [ -z "$DB_USER" ] || [ -z "$DB_PASS" ]; then
	echo "ERROR: missing domain/db settings. Pass them as args, or set SITE_DOMAIN/DB_NAME/DB_USER/DB_PASS as env vars (e.g. in the panel's Startup/Variables tab)." >&2
	echo "Usage: bash setup.sh <domain> <db_name> <db_user> <db_pass> [db_host] [db_port]" >&2
	exit 1
fi

if [ ! -f "$SQL_FILE" ]; then
	echo "ERROR: $SQL_FILE not found — it should have come with the git clone. Did the pull actually include the repo root, not just wp-content?" >&2
	exit 1
fi

# 1. Get wp-cli if it isn't already on this box.
if command -v wp >/dev/null 2>&1; then
	WP="wp"
else
	if [ ! -f wp-cli.phar ]; then
		echo "Fetching wp-cli..."
		curl -fsSL -o wp-cli.phar https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
	fi
	chmod +x wp-cli.phar
	WP="php wp-cli.phar"
fi
# WP-CLI refuses to run as root by default; this box is a single-tenant
# container so that protection doesn't apply here.
export WP_CLI_ALLOW_ROOT=1

# 2. WordPress core (skip if it's already there, e.g. on a re-run).
if [ ! -f wp-load.php ]; then
	echo "Downloading WordPress core..."
	$WP core download --version=7.0 --force
fi

# 3. wp-config.php with this server's real database credentials.
echo "Writing wp-config.php..."
$WP config create \
	--dbname="$DB_NAME" --dbuser="$DB_USER" --dbpass="$DB_PASS" \
	--dbhost="${DB_HOST}:${DB_PORT}" --skip-check --force

# Fresh, unique auth keys/salts for this install (never reuse the dev ones).
$WP config shuffle-salts

# 4. Import the actual site content (pages, kennisbank, settings).
echo "Importing database..."
$WP db import "$SQL_FILE"

# 5. Point every URL at the real domain. This is serialization-safe (unlike
# a raw SQL find/replace, which corrupts WordPress's serialized PHP arrays
# whenever the replacement string is a different length than the original).
echo "Rewriting URLs from $OLD_URL to $DOMAIN..."
$WP search-replace "$OLD_URL" "$DOMAIN" --all-tables --precise --skip-columns=guid
$WP option update siteurl "$DOMAIN"
$WP option update home "$DOMAIN"

# 6. Pretty permalinks (/spouwmuurisolatie/ instead of ?p=123) + .htaccess.
$WP rewrite flush --hard

# 7. Theme sanity check.
$WP theme activate warmvast

echo ""
echo "Done. $DOMAIN should now be serving the live site."
echo "Log in at $DOMAIN/wp-admin/ with the warmvast_admin account from the dev site."
echo "Once you've confirmed it works, you can delete $SQL_FILE from the server."
