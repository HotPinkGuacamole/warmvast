#!/bin/bash
# WARMVAST: thin wrapper around Endurer's wordpress/latest.sh.
#
# That script already handles everything a fresh boot needs (MariaDB init,
# wp-config.php, PHP-FPM, nginx in the foreground) and already auto-imports
# any *.sql it finds in /home/container/*.sql exactly once, tracked via its
# own /home/container/mysql/.imported marker on the persistent volume. The
# one thing it can't know about is warmvast-db.sql, because that lives in
# the git-managed webroot (/home/container/www), not /home/container. So
# the only job left here is staging the file where its importer looks,
# then handing off.
set -e

if [ -f /home/container/.warmvast-env ]; then
  set -a
  . /home/container/.warmvast-env
  set +a
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
if [ -f "$SCRIPT_DIR/warmvast-db.sql" ]; then
  cp "$SCRIPT_DIR/warmvast-db.sql" /home/container/warmvast-db.sql
fi

exec bash <(curl -fsSL https://startup.endurerhosting.com/website/wordpress/latest.sh)
