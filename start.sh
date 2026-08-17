#!/usr/bin/env bash
#
# Warmvast startup wrapper for Endurer Hosting's WordPress egg.
#
# Panel Startup Command should be:
#   curl -fsSL https://startup.endurerhosting.com/generic/latest.sh | bash && bash /home/container/www/start.sh
#
# ...with GIT_REPO_URL pointing at this repo and GIT_TARGET_DIR set to
# /home/container/www (the wordpress script below expects WP core to
# already be sitting in .../www — the generic script is what puts it there
# on every boot, this script never touches git itself).
#
# What this does, every single boot:
#  1. Runs Endurer's own wordpress/latest.sh (unmodified) in the background.
#     That script installs nginx/MariaDB/PHP-FPM if missing, starts MariaDB
#     against the persistent /home/container/mysql volume, creates the
#     `wordpress` database if it isn't there yet, auto-writes wp-config.php
#     (db=wordpress, user=root, no password, over the unix socket) if it
#     isn't there yet, then serves nginx in the foreground forever.
#  2. Waits for that MariaDB socket + wp-config.php to exist.
#  3. Checks whether wp_options already exists in the `wordpress` database.
#     - Fresh boot (no tables yet): imports warmvast-db.sql, which already
#       has the live domain baked in (produced locally via wp-cli's
#       serialization-safe `search-replace --export`, not a raw SQL
#       find/replace — that corrupts WordPress's serialized PHP arrays).
#     - Every later boot: tables already exist, import is skipped. This is
#       the only thing standing between "safe to restart the container any
#       time" and "wipes real leads/content on every restart" — do not
#       remove the wp_options check.
#  4. Hands control to the already-running nginx process (step 1).

set -uo pipefail
cd "$(dirname "$0")"

SOCKET=/run/mysqld/mysqld.sock
DB_NAME=wordpress
SQL_FILE=warmvast-db.sql

# Endurer's wordpress/latest.sh runs its own apt-get install with output
# fully suppressed (> /dev/null 2>&1) and `set -e`, so if it fails the
# script just dies silently mid-boot with no clue why. Run the same install
# ourselves FIRST, with output visible, so a real failure (network, repo
# mirror, whatever) actually shows up in the console. If this succeeds,
# their script's own `if ! command -v nginx` check sees the packages
# already present and skips straight past its silent install to the
# MariaDB/wp-config steps -- this isn't a workaround, it's just doing the
# same install earlier where we can see it.
if ! command -v nginx &>/dev/null; then
	echo "[start.sh] Installing runtime packages (visible, unlike the wrapped script's silent install)..."
	apt-get update
	DEBIAN_FRONTEND=noninteractive apt-get install -y nginx mariadb-server php-fpm php-mysql php-xml php-mbstring php-curl php-zip php-gd php-intl
	APT_STATUS=$?
	if [ "$APT_STATUS" -ne 0 ]; then
		echo "[start.sh] ERROR: package install failed (exit $APT_STATUS) -- see apt output above for the real reason." >&2
		exit "$APT_STATUS"
	fi
	echo "[start.sh] Packages installed."
fi

echo "[start.sh] Launching Endurer's WordPress runtime..."
bash <(curl -fsSL https://startup.endurerhosting.com/website/wordpress/latest.sh) &
WORDPRESS_PID=$!

echo "[start.sh] Waiting for MariaDB + wp-config.php..."
for _ in $(seq 1 90); do
	[ -S "$SOCKET" ] && [ -f wp-config.php ] && break
	sleep 1
done
if [ ! -S "$SOCKET" ] || [ ! -f wp-config.php ]; then
	echo "[start.sh] WARNING: MariaDB/wp-config.php never showed up after 90s — the wordpress runtime may have failed. Not importing; check its output above." >&2
else
	TABLE_CHECK=$(mysql -S "$SOCKET" -N -e "SHOW TABLES LIKE 'wp_options';" "$DB_NAME" 2>/dev/null || true)
	if [ -z "$TABLE_CHECK" ]; then
		if [ -f "$SQL_FILE" ]; then
			echo "[start.sh] Fresh database detected — importing $SQL_FILE..."
			if mysql -S "$SOCKET" "$DB_NAME" < "$SQL_FILE"; then
				echo "[start.sh] Import complete."
			else
				echo "[start.sh] ERROR: import failed (see mysql error above) — database left empty." >&2
			fi
		else
			echo "[start.sh] WARNING: $SQL_FILE not found — database stays empty, WordPress will show its install wizard." >&2
		fi
	else
		echo "[start.sh] Database already has content, skipping import."
	fi
fi

echo "[start.sh] Handing off to nginx."
wait "$WORDPRESS_PID"
