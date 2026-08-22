#!/usr/bin/env bash
#
# TSO Theme — Cloud Agent install phase.
#
# Builds a local WordPress site (SQLite backend, no MySQL daemon required)
# around this theme repository so the theme can be developed and previewed
# end-to-end. Idempotent: safe to run repeatedly and against a warm snapshot.
#
set -euo pipefail

# Repository root (this script lives in <repo>/.cursor/).
REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

WP_DIR="${TSO_WP_DIR:-$HOME/wp}"
WP_URL="${TSO_WP_URL:-http://localhost:8080}"
WP_LOCALE="${TSO_WP_LOCALE:-es_ES}"
THEME_SLUG="tso-theme"

wp() { command wp --path="$WP_DIR" "$@"; }

echo "==> WordPress dir: $WP_DIR"
echo "==> Theme repo:    $REPO_DIR"

# 1. WordPress core -----------------------------------------------------------
if [ ! -f "$WP_DIR/wp-load.php" ]; then
    echo "==> Downloading WordPress core ($WP_LOCALE)"
    mkdir -p "$WP_DIR"
    wp core download --version=latest --locale="$WP_LOCALE"
else
    echo "==> WordPress core already present"
fi

# 2. wp-config.php ------------------------------------------------------------
if [ ! -f "$WP_DIR/wp-config.php" ]; then
    echo "==> Creating wp-config.php"
    # DB credentials are placeholders; the SQLite drop-in bypasses MySQL.
    wp config create \
        --dbname=wordpress_tso --dbuser=nobody --dbpass=nopass --dbhost=localhost \
        --locale="$WP_LOCALE" --skip-check
    wp config set WP_DEBUG true --raw --type=constant
    wp config set WP_DEBUG_LOG true --raw --type=constant
    wp config set WP_DEBUG_DISPLAY false --raw --type=constant
fi

# 3. SQLite database integration (drop-in) ------------------------------------
PLUGIN_DIR="$WP_DIR/wp-content/plugins/sqlite-database-integration"
if [ ! -d "$PLUGIN_DIR" ]; then
    echo "==> Installing SQLite Database Integration plugin"
    curl -fsSL -o /tmp/sqlite-integration.zip \
        https://downloads.wordpress.org/plugin/sqlite-database-integration.latest-stable.zip
    unzip -oq /tmp/sqlite-integration.zip -d "$WP_DIR/wp-content/plugins"
    rm -f /tmp/sqlite-integration.zip
fi
if [ ! -f "$WP_DIR/wp-content/db.php" ]; then
    echo "==> Installing SQLite db.php drop-in"
    cp "$PLUGIN_DIR/db.copy" "$WP_DIR/wp-content/db.php"
    sed -i "s#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#$PLUGIN_DIR#g" "$WP_DIR/wp-content/db.php"
    sed -i "s#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#g" "$WP_DIR/wp-content/db.php"
fi

# 4. Install the site ---------------------------------------------------------
if ! wp core is-installed 2>/dev/null; then
    echo "==> Installing WordPress site"
    wp core install \
        --url="$WP_URL" \
        --title="Tu Soporte Online" \
        --admin_user=admin \
        --admin_password=admin \
        --admin_email=dev@example.com \
        --skip-email
    wp option update blogdescription "Soporte técnico y tutoriales"
    wp rewrite structure '/%postname%/' --hard
fi

# 5. Link and activate the theme ---------------------------------------------
echo "==> Linking theme -> $WP_DIR/wp-content/themes/$THEME_SLUG"
ln -sfn "$REPO_DIR" "$WP_DIR/wp-content/themes/$THEME_SLUG"
wp theme activate "$THEME_SLUG"

# 6. Seed sample content (once) ----------------------------------------------
POST_COUNT="$(wp post list --post_type=post --format=count 2>/dev/null || echo 0)"
if [ "${POST_COUNT:-0}" -le 1 ]; then
    echo "==> Seeding sample content"
    CAT_TUT="$(wp term create category 'Tutoriales' --slug=tutoriales --porcelain 2>/dev/null || wp term list category --name=Tutoriales --field=term_id | head -1)"
    CAT_NOT="$(wp term create category 'Noticias' --slug=noticias --porcelain 2>/dev/null || wp term list category --name=Noticias --field=term_id | head -1)"

    for i in 1 2 3 4 5; do
        wp post create --post_type=post --post_status=publish \
            --post_title="Artículo de ejemplo $i" \
            --post_content="<p>Contenido de ejemplo del artículo $i. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p><h2>Sección</h2><p>Más texto de prueba para ver la tipografía y el diseño del tema TSO.</p>" \
            --post_category="$CAT_TUT" --porcelain >/dev/null
    done
    wp post create --post_type=post --post_status=publish \
        --post_title="Novedad importante" \
        --post_content="<p>Una noticia de prueba para la categoría Noticias.</p>" \
        --post_category="$CAT_NOT" --porcelain >/dev/null
    wp post create --post_type=page --post_status=publish \
        --post_title="Acerca de" \
        --post_content="<p>Página de ejemplo <strong>Acerca de</strong> para probar page.php.</p>" \
        --porcelain >/dev/null

    # Navigation menu on the theme's "main-menu" location.
    wp menu create "Principal" >/dev/null 2>&1 || true
    MENU_ID="$(wp menu list --fields=term_id,name --format=csv | awk -F, '/Principal/{print $1; exit}')"
    if [ -n "${MENU_ID:-}" ]; then
        wp menu item add-custom "$MENU_ID" "Inicio" "$WP_URL/" >/dev/null 2>&1 || true
        PAGE_ID="$(wp post list --post_type=page --name=acerca-de --field=ID | head -1)"
        [ -n "${PAGE_ID:-}" ] && wp menu item add-post "$MENU_ID" "$PAGE_ID" >/dev/null 2>&1 || true
        [ -n "${CAT_TUT:-}" ] && wp menu item add-term "$MENU_ID" category "$CAT_TUT" >/dev/null 2>&1 || true
        wp menu location assign "$MENU_ID" main-menu >/dev/null 2>&1 || true
    fi

    # Sidebar widgets.
    wp widget add search sidebar-1 >/dev/null 2>&1 || true
    wp widget add recent-posts sidebar-1 >/dev/null 2>&1 || true
fi

echo "==> Install complete. Start the preview server with: bash .cursor/serve.sh"
