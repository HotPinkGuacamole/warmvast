#!/usr/bin/env bash
#
# Warmvast one-shot deploy setup.
#
# Run this ONCE, from the webroot, right after `git clone`/`git pull` has
# landed this repo there (the repo only tracks wp-content/themes/warmvast +
# docs, per .gitignore — WordPress core, wp-config.php, uploads and other
# plugins/themes are deliberately not versioned). This script fills in
# everything git can't: WordPress core, wp-config.php, and the database.
#
# It expects warmvast-db-localhost.sql (a content export of the live site —
# pages, kennisbank articles, settings) to already be sitting next to this
# script. That file is a one-time content snapshot, not code, so it isn't
# committed to git — upload it once via the panel's file manager.
#
# Usage:
#   bash setup.sh <domain> <db_name> <db_user> <db_pass> [db_host] [db_port]
#
# Example:
#   bash setup.sh https://warmvast.nl warmvast wv_user 'S3cret!' 127.0.0.1 3306
#
# The database itself (and its user) must already exist before running this
# — this script imports INTO it, it does not create it. If it doesn't exist
# yet, create it first, e.g.:
#   mysql -u root -p -e "CREATE DATABASE warmvast CHARACTER SET utf8mb4;
#     CREATE USER 'wv_user'@'%' IDENTIFIED BY 'S3cret!';
#     GRANT ALL PRIVILEGES ON warmvast.* TO 'wv_user'@'%'; FLUSH PRIVILEGES;"

set -euo pipefail

DOMAIN="${1:?Usage: bash setup.sh <domain> <db_name> <db_user> <db_pass> [db_host] [db_port]}"
DB_NAME="${2:?db name required}"
DB_USER="${3:?db user required}"
DB_PASS="${4:?db pass required}"
DB_HOST="${5:-127.0.0.1}"
DB_PORT="${6:-3306}"
SQL_FILE="warmvast-db-localhost.sql"
OLD_URL="http://localhost/warmvast"

if [ ! -f "$SQL_FILE" ]; then
	echo "ERROR: $SQL_FILE not found next to setup.sh. Upload it first (via the panel's file manager), then re-run." >&2
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
