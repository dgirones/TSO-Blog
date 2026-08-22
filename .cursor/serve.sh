#!/usr/bin/env bash
#
# TSO Theme — WordPress preview server.
#
# Serves the WordPress site built by .cursor/install.sh using PHP's built-in
# web server (via WP-CLI). Runs in the foreground so its logs stay visible.
#
set -euo pipefail

WP_DIR="${TSO_WP_DIR:-$HOME/wp}"
WP_HOST="${TSO_WP_HOST:-0.0.0.0}"
WP_PORT="${TSO_WP_PORT:-8080}"

if [ ! -f "$WP_DIR/wp-load.php" ]; then
    echo "WordPress is not installed yet at $WP_DIR. Run: bash .cursor/install.sh" >&2
    exit 1
fi

echo "Serving WordPress from $WP_DIR at http://$WP_HOST:$WP_PORT (admin/admin)"
exec wp server --path="$WP_DIR" --host="$WP_HOST" --port="$WP_PORT"
