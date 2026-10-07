<?php
/**
 * functions.php — Tu Soporte Online v1.0
 * Tema limpio. Compatible con Jetpack, LiteSpeed Cache, WP Super Cache y W3TC.
 *
 * Theme version is fixed at 1.0 — do not bump the version number.
 */

if ( ! defined( 'TSOTHM_VERSION' ) ) {
    define( 'TSOTHM_VERSION', '1.0.1' );
}

/**
 * One-time migration: copy legacy tso_* theme_mods to tsothm_* keys.
 */
function tsothm_migrate_legacy_theme_mods() {
    if ( get_option( 'tsothm_mods_migrated', false ) ) {
        return;
    }
    $stylesheet = get_option( 'stylesheet' );
    if ( ! is_string( $stylesheet ) || '' === $stylesheet ) {
        update_option( 'tsothm_mods_migrated', 1, false );
        return;
    }
    $option_name = 'theme_mods_' . $stylesheet;
    $mods        = get_option( $option_name, array() );
    if ( ! is_array( $mods ) ) {
        update_option( 'tsothm_mods_migrated', 1, false );
        return;
    }
    $changed = false;
    foreach ( $mods as $key => $value ) {
        if ( ! is_string( $key ) || 0 !== strpos( $key, 'tso_' ) ) {
            continue;
        }
        // Skip already-migrated style keys (none start with tso_ except legacy).
        $new_key = 'tsothm_' . substr( $key, 4 );
        if ( ! array_key_exists( $new_key, $mods ) ) {
            $mods[ $new_key ] = $value;
            $changed          = true;
        }
    }
    if ( $changed ) {
        update_option( $option_name, $mods );
    }
    update_option( 'tsothm_mods_migrated', 1, false );
}
add_action( 'after_setup_theme', 'tsothm_migrate_legacy_theme_mods', 1 );

/* ============================================================
   1. CONFIGURACIÓN DEL TEMA
   ============================================================ */

/**
 * Global content width (Required for theme review / media embeds).
 *
 * @global int $content_width
 */
if ( ! isset( $content_width ) ) {
    $content_width = 800;
}

add_action( 'after_setup_theme', function() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo', array(
        'width'       => 300,
        'height'      => 124,
        'flex-height' => true,
        'flex-width'  => true,
    ) );
    add_theme_support( 'html5', array(
        'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style',
    ) );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'align-wide' );
    add_theme_support( 'customize-selective-refresh-widgets' );
    add_theme_support( 'editor-styles' );
    add_theme_support( 'custom-header', array(
        'default-image'      => '',
        'width'              => 1060,
        'height'             => 200,
        'flex-height'        => true,
        'flex-width'         => true,
        'default-text-color' => '333333',
        'header-text'        => false,
    ) );
    add_theme_support(
        'custom-background',
        array(
            'default-color' => 'd6993a',
            'default-image' => '',
        )
    );

    add_image_size( 'tsothm-card-thumb', 400, 280, true );
    add_image_size( 'tsothm-related', 360, 200, true );
    // Wallpaper / portfolio downloads (hard crop to exact desktop sizes).
    add_image_size( 'tsothm-wall-720', 1280, 720, true );
    add_image_size( 'tsothm-wall-1080', 1920, 1080, true );
    add_image_size( 'tsothm-wall-1440', 2560, 1440, true );
    add_image_size( 'tsothm-wall-4k', 3840, 2160, true );
    add_image_size( 'tsothm-wall-mobile', 1080, 1920, true );

    register_nav_menus( array( 'main-menu' => __( 'Menú Principal', 'tso-blog' ) ) );

    add_editor_style( 'editor-style.css' );

    load_theme_textdomain( 'tso-blog', get_template_directory() . '/languages' );
} );

/* ============================================================
   2. ESTILOS Y SCRIPTS
   ============================================================ */
/**
 * Asset version based on file modification time.
 *
 * @param string $relative_path Path relative to the theme root.
 * @return string
 */
function tsothm_get_asset_version( $relative_path ) {
    $path = get_stylesheet_directory() . '/' . ltrim( $relative_path, '/' );
    return file_exists( $path ) ? (string) filemtime( $path ) : TSOTHM_VERSION;
}

/**
 * Theme color defaults (Customizer + CSS fallbacks).
 *
 * @return array<string, string>
 */
function tsothm_get_theme_color_defaults() {
    return array(
        'accent'          => '#d6993a',
        'primary'         => '#1e73be',
        'body_bg'         => '#d6993a',
        'header_bg'       => '#d6993a',
        'nav_bg'          => '#000000',
        'nav_text'        => '#ffffff',
        'nav_hover'       => '#d6993a',
        'nav_submenu'     => '#222222',
        'footer_bg'       => '#222222',
        'footer_heading'  => '#ffffff',
        'footer_text'     => '#cccccc',
        'footer_muted'    => '#aaaaaa',
        'footer_border'   => '#444444',
        'announce_bg'     => '#d6993a',
        'announce_text'   => '#ffffff',
    );
}

/**
 * Theme colors from Customizer (sanitized).
 *
 * @return array<string, string>
 */
function tsothm_get_theme_colors() {
    $defaults = tsothm_get_theme_color_defaults();
    $colors   = array();

    foreach ( $defaults as $key => $default ) {
        $mod            = sanitize_hex_color( get_theme_mod( 'tsothm_color_' . $key, $default ) );
        $colors[ $key ] = $mod ? $mod : $default;
    }

    return $colors;
}

/**
 * Darken a hex color for hover states.
 *
 * @param string $hex     Hex color.
 * @param float  $factor Multiply RGB (0–1).
 * @return string
 */
function tsothm_darken_hex( $hex, $factor = 0.82 ) {
    $hex = ltrim( (string) $hex, '#' );
    if ( 3 === strlen( $hex ) ) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
        return '#b5782e';
    }
    $r = max( 0, min( 255, (int) round( hexdec( substr( $hex, 0, 2 ) ) * $factor ) ) );
    $g = max( 0, min( 255, (int) round( hexdec( substr( $hex, 2, 2 ) ) * $factor ) ) );
    $b = max( 0, min( 255, (int) round( hexdec( substr( $hex, 4, 2 ) ) * $factor ) ) );
    return sprintf( '#%02x%02x%02x', $r, $g, $b );
}

/**
 * Available font families (system stacks + optional Google Fonts).
 *
 * @return array<string, array{label: string, stack: string, google: string}>
 */
function tsothm_get_font_catalog() {
    return array(
        'arial'         => array(
            'label'  => 'Arial',
            'stack'  => 'Arial, Helvetica, sans-serif',
            'google' => '',
        ),
        'system'        => array(
            'label'  => 'System UI',
            'stack'  => 'system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif',
            'google' => '',
        ),
        'georgia'       => array(
            'label'  => 'Georgia',
            'stack'  => 'Georgia, "Times New Roman", Times, serif',
            'google' => '',
        ),
        'verdana'       => array(
            'label'  => 'Verdana',
            'stack'  => 'Verdana, Geneva, sans-serif',
            'google' => '',
        ),
        'trebuchet'     => array(
            'label'  => 'Trebuchet MS',
            'stack'  => '"Trebuchet MS", Helvetica, sans-serif',
            'google' => '',
        ),
        'roboto'        => array(
            'label'  => 'Roboto (Google)',
            'stack'  => '"Roboto", Arial, Helvetica, sans-serif',
            'google' => 'Roboto:wght@400;500;600;700;800',
        ),
        'open-sans'     => array(
            'label'  => 'Open Sans (Google)',
            'stack'  => '"Open Sans", Arial, Helvetica, sans-serif',
            'google' => 'Open+Sans:wght@400;500;600;700;800',
        ),
        'lato'          => array(
            'label'  => 'Lato (Google)',
            'stack'  => '"Lato", Arial, Helvetica, sans-serif',
            'google' => 'Lato:wght@400;500;600;700;800',
        ),
        'nunito'        => array(
            'label'  => 'Nunito (Google)',
            'stack'  => '"Nunito", Arial, Helvetica, sans-serif',
            'google' => 'Nunito:wght@400;500;600;700;800',
        ),
        'merriweather'  => array(
            'label'  => 'Merriweather (Google)',
            'stack'  => '"Merriweather", Georgia, serif',
            'google' => 'Merriweather:wght@400;700',
        ),
        'roboto-slab'   => array(
            'label'  => 'Roboto Slab (Google)',
            'stack'  => '"Roboto Slab", Georgia, serif',
            'google' => 'Roboto+Slab:wght@400;500;600;700;800',
        ),
        'source-serif'  => array(
            'label'  => 'Source Serif 4 (Google)',
            'stack'  => '"Source Serif 4", Georgia, serif',
            'google' => 'Source+Serif+4:wght@400;500;600;700;800',
        ),
    );
}

/**
 * Sanitize a font catalog key.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function tsothm_sanitize_font_key( $value ) {
    $value    = sanitize_key( (string) $value );
    $catalog  = tsothm_get_font_catalog();
    return isset( $catalog[ $value ] ) ? $value : 'arial';
}

/**
 * Sanitize font size in px (12–48).
 *
 * @param mixed $value Raw value.
 * @return string e.g. 15px
 */
function tsothm_sanitize_font_size_px( $value ) {
    $n = (int) preg_replace( '/[^0-9]/', '', (string) $value );
    $n = max( 12, min( 48, $n ? $n : 15 ) );
    return $n . 'px';
}

/**
 * Sanitize unitless line-height (1–2.5).
 *
 * @param mixed $value Raw value.
 * @return string
 */
function tsothm_sanitize_line_height( $value ) {
    $n = (float) $value;
    if ( $n < 1 || $n > 2.5 ) {
        $n = 1.6;
    }
    return (string) round( $n, 2 );
}

/**
 * Sanitize heading weight.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function tsothm_sanitize_font_weight( $value ) {
    $value = (string) absint( $value );
    $ok    = array( '400', '500', '600', '700', '800' );
    return in_array( $value, $ok, true ) ? $value : '700';
}

/**
 * Sanitize text-transform.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function tsothm_sanitize_text_transform( $value ) {
    $value = sanitize_key( (string) $value );
    $ok    = array( 'none', 'uppercase', 'lowercase', 'capitalize' );
    return in_array( $value, $ok, true ) ? $value : 'none';
}

/**
 * Sanitize logo width in px.
 *
 * @param mixed $value   Raw value.
 * @param int   $default Fallback if empty/invalid.
 * @param int   $min     Minimum px.
 * @param int   $max     Maximum px.
 * @return string e.g. 300px
 */
function tsothm_sanitize_logo_width( $value, $default = 300, $min = 40, $max = 500 ) {
    $n = (int) preg_replace( '/[^0-9]/', '', (string) $value );
    if ( ! $n ) {
        $n = (int) $default;
    }
    $n = max( (int) $min, min( (int) $max, $n ) );
    return $n . 'px';
}

/**
 * Sanitize desktop logo width (Customizer callback).
 *
 * @param mixed $value Raw value.
 * @return string
 */
function tsothm_sanitize_logo_width_desktop( $value ) {
    return tsothm_sanitize_logo_width( $value, 300, 40, 500 );
}

/**
 * Sanitize mobile logo width (Customizer callback).
 *
 * @param mixed $value Raw value.
 * @return string
 */
function tsothm_sanitize_logo_width_mobile( $value ) {
    return tsothm_sanitize_logo_width( $value, 180, 40, 400 );
}

/**
 * Layout width choices for the main content wrapper.
 *
 * @return array<string, string>
 */
function tsothm_get_layout_width_choices() {
    return array(
        '960'  => '960 px',
        '1060' => sprintf(
            /* translators: %s: default label */
            '1060 px (%s)',
            __( 'por defecto', 'tso-blog' )
        ),
        '1200' => '1200 px',
        '1400' => '1400 px',
        '1600' => '1600 px',
        'full' => __( 'Casi pantalla completa', 'tso-blog' ),
    );
}

/**
 * Sanitize layout width key.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function tsothm_sanitize_layout_width( $value ) {
    $value = sanitize_key( (string) $value );
    return isset( tsothm_get_layout_width_choices()[ $value ] ) ? $value : '1060';
}

/**
 * Main menu button / relief style choices.
 *
 * @return array<string, string>
 */
function tsothm_get_nav_button_style_choices() {
    return array(
        'raised' => __( 'Relieve clásico', 'tso-blog' ),
        'carved' => __( 'Relieve tallado', 'tso-blog' ),
        'pill'      => __( 'Píldoras', 'tso-blog' ),
        'underline' => __( 'Subrayado animado', 'tso-blog' ),
        'flat'   => __( 'Plano (sin botones)', 'tso-blog' ),
    );
}

/**
 * Sanitize main menu button style key.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function tsothm_sanitize_nav_button_style( $value ) {
    $value = sanitize_key( (string) $value );
    return isset( tsothm_get_nav_button_style_choices()[ $value ] ) ? $value : 'raised';
}

/**
 * CSS max-width for #page-wrapper.
 *
 * @return string e.g. 1400px or 100%
 */
function tsothm_get_layout_max_css() {
    $key = tsothm_sanitize_layout_width( get_theme_mod( 'tsothm_layout_width', '1060' ) );
    if ( 'full' === $key ) {
        return '100%';
    }
    return absint( $key ) . 'px';
}

/**
 * Resolved typography settings for CSS.
 *
 * @return array<string, string>
 */
function tsothm_get_typography_settings() {
    $catalog = tsothm_get_font_catalog();
    $body_key = tsothm_sanitize_font_key( get_theme_mod( 'tsothm_font_body', 'arial' ) );
    $head_key = tsothm_sanitize_font_key( get_theme_mod( 'tsothm_font_heading', 'arial' ) );

    $body_color = sanitize_hex_color( get_theme_mod( 'tsothm_font_body_color', '#333333' ) );
    $head_color = sanitize_hex_color( get_theme_mod( 'tsothm_font_heading_color', '#111111' ) );

    return array(
        'body_key'       => $body_key,
        'heading_key'    => $head_key,
        'body_stack'     => $catalog[ $body_key ]['stack'],
        'heading_stack'  => $catalog[ $head_key ]['stack'],
        'body_size'      => tsothm_sanitize_font_size_px( get_theme_mod( 'tsothm_font_body_size', '15' ) ),
        'body_line'      => tsothm_sanitize_line_height( get_theme_mod( 'tsothm_font_body_line', '1.6' ) ),
        'body_color'     => $body_color ? $body_color : '#333333',
        'heading_weight' => tsothm_sanitize_font_weight( get_theme_mod( 'tsothm_font_heading_weight', '700' ) ),
        'heading_color'  => $head_color ? $head_color : '#111111',
        'h1_size'              => tsothm_sanitize_font_size_px( get_theme_mod( 'tsothm_font_h1_size', '24' ) ),
        'h1_transform'         => tsothm_sanitize_text_transform( get_theme_mod( 'tsothm_font_h1_transform', 'none' ) ),
        'widget_title_size'    => tsothm_sanitize_widget_font_size( get_theme_mod( 'tsothm_font_widget_title_size', '14' ) ),
        'widget_text_size'     => tsothm_sanitize_widget_font_size( get_theme_mod( 'tsothm_font_widget_text_size', '14' ) ),
    );
}

/**
 * Sanitize sidebar widget font size (12–22 px).
 *
 * @param mixed $value Raw value.
 * @return string e.g. 14px
 */
function tsothm_sanitize_widget_font_size( $value ) {
    $n = (int) preg_replace( '/[^0-9]/', '', (string) $value );
    $n = max( 12, min( 22, $n ? $n : 14 ) );
    return $n . 'px';
}

/**
 * Enqueue Google Fonts only when a Google family is selected.
 */
function tsothm_enqueue_google_fonts() {
    $catalog = tsothm_get_font_catalog();
    $typo    = tsothm_get_typography_settings();
    $families = array();

    foreach ( array( $typo['body_key'], $typo['heading_key'] ) as $key ) {
        if ( ! empty( $catalog[ $key ]['google'] ) ) {
            $families[ $catalog[ $key ]['google'] ] = true;
        }
    }

    if ( empty( $families ) ) {
        return;
    }

    $family_params = array();
    foreach ( array_keys( $families ) as $family ) {
        $family_params[] = 'family=' . $family;
    }
    $url = 'https://fonts.googleapis.com/css2?' . implode( '&', $family_params ) . '&display=swap';

    wp_enqueue_style( 'tsothm-google-fonts', esc_url_raw( $url ), array(), null );
}
add_action( 'wp_enqueue_scripts', 'tsothm_enqueue_google_fonts', 5 );

/**
 * Preconnect to Google Fonts when a Google family is selected.
 *
 * @param array  $urls          URLs to print for resource hints.
 * @param string $relation_type Relation type (dns-prefetch, preconnect, …).
 * @return array
 */
function tsothm_google_fonts_resource_hints( $urls, $relation_type ) {
    if ( 'preconnect' !== $relation_type ) {
        return $urls;
    }

    $catalog = tsothm_get_font_catalog();
    $typo    = tsothm_get_typography_settings();
    $needs   = false;

    foreach ( array( $typo['body_key'], $typo['heading_key'] ) as $key ) {
        if ( ! empty( $catalog[ $key ]['google'] ) ) {
            $needs = true;
            break;
        }
    }

    if ( ! $needs ) {
        return $urls;
    }

    $urls[] = array(
        'href' => 'https://fonts.googleapis.com',
    );
    $urls[] = array(
        'href'        => 'https://fonts.gstatic.com',
        'crossorigin' => 'anonymous',
    );

    return $urls;
}
add_filter( 'wp_resource_hints', 'tsothm_google_fonts_resource_hints', 10, 2 );

/**
 * CSS custom properties for frontend + Customizer overrides.
 *
 * @return string
 */
function tsothm_hex_luminance( $hex ) {
    $hex = ltrim( (string) $hex, '#' );
    if ( 3 === strlen( $hex ) ) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
        return 0.5;
    }
    $r = hexdec( substr( $hex, 0, 2 ) ) / 255;
    $g = hexdec( substr( $hex, 2, 2 ) ) / 255;
    $b = hexdec( substr( $hex, 4, 2 ) ) / 255;
    return ( 0.299 * $r + 0.587 * $g + 0.114 * $b );
}

function tsothm_is_light_hex( $hex, $threshold = 0.65 ) {
    return tsothm_hex_luminance( $hex ) > $threshold;
}

function tsothm_get_contrast_color( $hex ) {
    return tsothm_is_light_hex( $hex, 0.6 ) ? '#1a1a1a' : '#ffffff';
}

function tsothm_get_night_variant_hex( $hex, $dark_target = '#17181c' ) {
    return tsothm_is_light_hex( $hex ) ? $dark_target : $hex;
}

function tsothm_lighten_hex( $hex, $amount = 26 ) {
    $hex = ltrim( (string) $hex, '#' );
    if ( 3 === strlen( $hex ) ) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
        return '#333333';
    }
    $r = max( 0, min( 255, hexdec( substr( $hex, 0, 2 ) ) + $amount ) );
    $g = max( 0, min( 255, hexdec( substr( $hex, 2, 2 ) ) + $amount ) );
    $b = max( 0, min( 255, hexdec( substr( $hex, 4, 2 ) ) + $amount ) );
    return sprintf( '#%02x%02x%02x', $r, $g, $b );
}

/**
 * Night-mode color defaults (Customizer > Colores > Modo noche).
 * They match the dark palette in style.css; an empty header means "automatic".
 *
 * @return array<string, string>
 */
function tsothm_get_night_color_defaults() {
    return array(
        'body_bg'   => '#0b0c0e',
        'surface'   => '#17181c',
        'header_bg' => '',
        'nav_bg'    => '#000000',
        'footer_bg' => '#111113',
        'accent'    => '#e8ac55',
        'link'      => '#8ab4f8',
        'primary'   => '#4d9fff',
        'nav_text'  => '#ffffff',
        'nav_submenu'    => '#141417',
        'footer_heading' => '#ffffff',
        'footer_text'    => '#c7c7cc',
        'footer_muted'   => '#8a8a90',
        'footer_border'  => '#2c2d33',
        'announce_bg'    => '#8a5a20',
        'announce_text'  => '#ffffff',
        'text'      => '#d6d6da',
        'heading'   => '#f5f5f7',
    );
}

/**
 * Night-mode colors from the Customizer (sanitized, defaults when empty).
 *
 * @return array<string, string>
 */
function tsothm_get_night_colors() {
    $colors = array();
    foreach ( tsothm_get_night_color_defaults() as $key => $default ) {
        $mod            = sanitize_hex_color( get_theme_mod( 'tsothm_night_' . $key, $default ) );
        $colors[ $key ] = $mod ? strtolower( $mod ) : $default;
    }
    return $colors;
}

/**
 * CSS custom properties for night mode. Only values changed in the Customizer
 * are emitted, so the dark palette in style.css stays the default.
 *
 * @param array<string, string> $c Day colors (tsothm_get_theme_colors()).
 * @return string
 */
function tsothm_get_night_css_variables( $c ) {
    $night    = tsothm_get_night_colors();
    $defaults = tsothm_get_night_color_defaults();

    $header_bg = '' !== $night['header_bg'] ? $night['header_bg'] : tsothm_get_night_variant_hex( $c['header_bg'] );
    $hover     = ( $header_bg === $c['header_bg'] ) ? tsothm_darken_hex( $header_bg ) : tsothm_lighten_hex( $header_bg );

    $css  = '--tso-color-header-bg:' . $header_bg . ';';
    $css .= '--tso-color-header-bg-hover:' . $hover . ';';
    $css .= '--tso-color-header-contrast:' . tsothm_get_contrast_color( $header_bg ) . ';';

    if ( $night['body_bg'] !== $defaults['body_bg'] ) {
        $css .= '--tso-color-body-bg:' . $night['body_bg'] . ';';
    }
    if ( $night['surface'] !== $defaults['surface'] ) {
        $css .= '--tso-color-surface:' . $night['surface'] . ';';
        $css .= '--tso-color-surface-alt:' . tsothm_lighten_hex( $night['surface'], 8 ) . ';';
        $css .= '--tso-color-surface-sunken:' . tsothm_darken_hex( $night['surface'], 0.7 ) . ';';
    }
    if ( $night['nav_bg'] !== $defaults['nav_bg'] ) {
        $css .= '--tso-color-nav-bg:' . $night['nav_bg'] . ';';
    }
    if ( $night['footer_bg'] !== $defaults['footer_bg'] ) {
        $css .= '--tso-color-footer-bg:' . $night['footer_bg'] . ';';
    }
    if ( $night['accent'] !== $defaults['accent'] ) {
        $css .= '--tso-color-accent:' . $night['accent'] . ';';
        $css .= '--tso-color-accent-hover:' . tsothm_lighten_hex( $night['accent'], 20 ) . ';';
        $css .= '--tso-color-nav-hover:' . $night['accent'] . ';';
        $css .= '--tso-color-accent-contrast:' . tsothm_get_contrast_color( $night['accent'] ) . ';';
    }
    if ( $night['link'] !== $defaults['link'] ) {
        $css .= '--tso-color-link-soft:' . $night['link'] . ';';
        $css .= '--tso-color-link-soft-contrast:' . tsothm_get_contrast_color( $night['link'] ) . ';';
    }

    // Colores simples: variable CSS = valor, solo si se ha cambiado.
    $simple = array(
        'primary'        => '--tso-color-primary',
        'nav_text'       => '--tso-color-nav-text',
        'nav_submenu'    => '--tso-color-nav-submenu',
        'footer_heading' => '--tso-color-footer-heading',
        'footer_text'    => '--tso-color-footer-text',
        'footer_muted'   => '--tso-color-footer-muted',
        'footer_border'  => '--tso-color-footer-border',
        'announce_bg'    => '--tso-color-announce-bg',
        'announce_text'  => '--tso-color-announce-text',
        'text'           => '--tso-color-text',
        'heading'        => '--tso-color-heading',
    );
    foreach ( $simple as $night_key => $css_var ) {
        if ( $night[ $night_key ] !== $defaults[ $night_key ] ) {
            $css .= $css_var . ':' . $night[ $night_key ] . ';';
        }
    }

    return 'html[data-theme="dark"]{' . $css . '}';
}

function tsothm_get_theme_css_variables() {
    $c = tsothm_get_theme_colors();
    $header_contrast        = tsothm_get_contrast_color( $c['header_bg'] );
    $t = tsothm_get_typography_settings();

    return ':root{'
        . '--tso-color-accent:' . $c['accent'] . ';'
        . '--tso-color-accent-contrast:' . tsothm_get_contrast_color( $c['accent'] ) . ';'
        . '--tso-color-primary:' . $c['primary'] . ';'
        . '--tso-color-body-bg:' . $c['body_bg'] . ';'
        . '--tso-color-header-bg:' . $c['header_bg'] . ';'
        . '--tso-color-header-bg-hover:' . tsothm_darken_hex( $c['header_bg'] ) . ';'
        . '--tso-color-header-contrast:' . $header_contrast . ';'
        . '--tso-color-accent-hover:' . tsothm_darken_hex( $c['accent'] ) . ';'
        . '--tso-color-nav-bg:' . $c['nav_bg'] . ';'
        . '--tso-color-nav-text:' . $c['nav_text'] . ';'
        . '--tso-color-nav-hover:' . $c['nav_hover'] . ';'
        . '--tso-color-nav-submenu:' . $c['nav_submenu'] . ';'
        . '--tso-color-footer-bg:' . $c['footer_bg'] . ';'
        . '--tso-color-footer-heading:' . $c['footer_heading'] . ';'
        . '--tso-color-footer-text:' . $c['footer_text'] . ';'
        . '--tso-color-footer-muted:' . $c['footer_muted'] . ';'
        . '--tso-color-footer-border:' . $c['footer_border'] . ';'
        . '--tso-color-announce-bg:' . $c['announce_bg'] . ';'
        . '--tso-color-announce-text:' . $c['announce_text'] . ';'
        . '--tso-color-text:' . $t['body_color'] . ';'
        . '--tso-color-heading:' . $t['heading_color'] . ';'
        . '--tso-color-text-muted:#666666;'
        . '--tso-color-white:#ffffff;'
        . '--tso-color-border:#e0e0e0;'
        . '--tso-color-surface:#ffffff;'
        . '--tso-font-body:' . $t['body_stack'] . ';'
        . '--tso-font-heading:' . $t['heading_stack'] . ';'
        . '--tso-font-body-size:' . $t['body_size'] . ';'
        . '--tso-font-body-line:' . $t['body_line'] . ';'
        . '--tso-font-heading-weight:' . $t['heading_weight'] . ';'
        . '--tso-font-h1-size:' . $t['h1_size'] . ';'
        . '--tso-font-h1-transform:' . $t['h1_transform'] . ';'
        . '--tso-font-widget-title-size:' . $t['widget_title_size'] . ';'
        . '--tso-font-widget-text-size:' . $t['widget_text_size'] . ';'
        . '--tso-logo-width:' . tsothm_sanitize_logo_width_desktop( get_theme_mod( 'tsothm_logo_width', '300' ) ) . ';'
        . '--tso-logo-width-mobile:' . tsothm_sanitize_logo_width_mobile( get_theme_mod( 'tsothm_logo_width_mobile', '180' ) ) . ';'
        . '--tso-layout-max:' . tsothm_get_layout_max_css() . ';'
        . '}'
        . tsothm_get_night_css_variables( $c )
        . 'body{background-color:var(--tso-color-body-bg);}'
        . 'body{font-family:var(--tso-font-body);font-size:var(--tso-font-body-size);line-height:var(--tso-font-body-line);color:var(--tso-color-text);}'
        . 'h1,h2,h3,h4,h5,h6,.single-title,.page-title,.archive-title,.post-card-title,.related-posts-title,.related-post-title,.comments-title,.comment-reply-title,.widget-title{font-family:var(--tso-font-heading);font-weight:var(--tso-font-heading-weight);}'
        . '.single-title,.page-title,.archive-title,.post-card-title,.related-posts-title,.related-post-title,.entry-content h1,.entry-content h2,.entry-content h3,.entry-content h4,.entry-content h5,.entry-content h6{color:var(--tso-color-heading);}'
        . '.single-title,.page-title,.archive-title,.entry-content h1{font-size:var(--tso-font-h1-size);}'
        . '.single-title,.page-title,.archive-title,.post-card-title,.related-post-title,.entry-content h1{text-transform:var(--tso-font-h1-transform);}'
        . '.post-card-title a,.related-post-title a{color:inherit;}'
        . '.logo img,.custom-logo{max-width:var(--tso-logo-width);width:auto;height:auto;}'
        . '@media screen and (max-width:768px){.logo img,.custom-logo{max-width:var(--tso-logo-width-mobile);}}'
        . '#page-wrapper{max-width:var(--tso-layout-max);}'
    ;
}

/**
 * Sync block editor palette with Customizer colors (theme.json).
 *
 * @param WP_Theme_JSON_Data $theme_json Theme JSON data.
 * @return WP_Theme_JSON_Data
 */
function tsothm_sync_theme_json_colors( $theme_json ) {
    if ( ! class_exists( 'WP_Theme_JSON_Data' ) ) {
        return $theme_json;
    }

    $colors  = tsothm_get_theme_colors();
    $data    = $theme_json->get_data();
    $palette = array(
        array(
            'slug'  => 'primary',
            'color' => $colors['primary'],
            'name'  => __( 'Primary', 'tso-blog' ),
        ),
        array(
            'slug'  => 'accent',
            'color' => $colors['accent'],
            'name'  => __( 'Accent', 'tso-blog' ),
        ),
        array(
            'slug'  => 'body-bg',
            'color' => $colors['body_bg'],
            'name'  => __( 'Body background', 'tso-blog' ),
        ),
        array(
            'slug'  => 'white',
            'color' => '#ffffff',
            'name'  => __( 'White', 'tso-blog' ),
        ),
        array(
            'slug'  => 'text',
            'color' => '#333333',
            'name'  => __( 'Text', 'tso-blog' ),
        ),
        array(
            'slug'  => 'text-muted',
            'color' => '#666666',
            'name'  => __( 'Muted text', 'tso-blog' ),
        ),
    );

    if ( ! isset( $data['settings'] ) ) {
        $data['settings'] = array();
    }
    if ( ! isset( $data['settings']['color'] ) ) {
        $data['settings']['color'] = array();
    }

    $data['settings']['color']['palette'] = $palette;

    $layout_max = tsothm_get_layout_max_css();
    if ( ! isset( $data['settings']['layout'] ) || ! is_array( $data['settings']['layout'] ) ) {
        $data['settings']['layout'] = array();
    }
    $data['settings']['layout']['wideSize']    = $layout_max;
    $data['settings']['layout']['contentSize'] = ( '100%' === $layout_max ) ? '780px' : $layout_max;

    return new WP_Theme_JSON_Data( $data, 'theme' );
}
add_filter( 'wp_theme_json_data_theme', 'tsothm_sync_theme_json_colors' );

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'tsothm-style', get_stylesheet_uri(), array(), tsothm_get_asset_version( 'style.css' ) );
    wp_add_inline_style( 'tsothm-style', tsothm_get_theme_css_variables() );

    if ( is_singular() && comments_open() ) {
        wp_enqueue_script( 'comment-reply' );
    }

    $theme_uri = get_stylesheet_directory_uri();

    wp_enqueue_script(
        'tsothm-live-search',
        $theme_uri . '/tso-live-search.js',
        array(),
        tsothm_get_asset_version( 'tso-live-search.js' ),
        true
    );
    wp_localize_script(
        'tsothm-live-search',
        'tsothmSearch',
        array(
            'ajaxurl'          => admin_url( 'admin-ajax.php' ),
            'nonce'            => wp_create_nonce( 'tsothm_live_search_nonce' ),
            'loadMoreCacheKey' => 'tsothm_loadmore_state',
            'i18n'             => array(
                'searching'  => __( 'Buscando…', 'tso-blog' ),
                'noResults'  => __( 'No se encontraron artículos.', 'tso-blog' ),
            ),
        )
    );

    if ( is_home() ) {
        wp_enqueue_script(
            'tsothm-load-more',
            $theme_uri . '/tso-load-more.js',
            array(),
            tsothm_get_asset_version( 'tso-load-more.js' ),
            true
        );
        wp_localize_script(
            'tsothm-load-more',
            'tsothmLoadMore',
            array(
                'cacheKey'    => 'tsothm_loadmore_state',
                'loadText'    => tsothm_load_more_text(),
                'loadingText' => __( 'Cargando...', 'tso-blog' ),
            )
        );
    }

    wp_enqueue_script(
        'tsothm-widget-css-fix',
        $theme_uri . '/tso-widget-css-fix.js',
        array(),
        tsothm_get_asset_version( 'tso-widget-css-fix.js' ),
        true
    );

    if ( tsothm_is_announcement_enabled() ) {
        $announce_js = get_stylesheet_directory() . '/tso-announcement.js';
        if ( file_exists( $announce_js ) ) {
            wp_enqueue_script(
                'tsothm-announcement',
                $theme_uri . '/tso-announcement.js',
                array(),
                tsothm_get_asset_version( 'tso-announcement.js' ),
                true
            );
        }
    }

    if ( is_singular() ) {
        $lightbox = get_stylesheet_directory() . '/tso-lightbox.js';
        if ( file_exists( $lightbox ) ) {
            wp_enqueue_script(
                'tsothm-lightbox',
                $theme_uri . '/tso-lightbox.js',
                array(),
                tsothm_get_asset_version( 'tso-lightbox.js' ),
                true
            );
        }
    }

    if ( is_page() && tsothm_is_contact_page() ) {
        $map_js = get_stylesheet_directory() . '/tso-contact-map.js';
        if ( file_exists( $map_js ) ) {
            wp_enqueue_script(
                'tsothm-contact-map',
                $theme_uri . '/tso-contact-map.js',
                array(),
                tsothm_get_asset_version( 'tso-contact-map.js' ),
                true
            );
        }
    }
} );

/* ============================================================
   3. HEAD CLEANUP
   ============================================================
   Do not remove non-presentational wp_head hooks (oembed, rsd,
   shortlink, generator, emoji) — that is plugin territory for
   WordPress.org theme review. Use a plugin/mu-plugin if needed.
   ============================================================ */

add_filter( 'wp_lazy_loading_enabled', '__return_true' );

/*
 * WP_POST_REVISIONS and DISALLOW_FILE_EDIT belong in wp-config.php
 * (see readme.txt → Installation). Do not define them in the theme.
 */

/* ============================================================
   4. ACCESIBILIDAD — SKIP TO CONTENT
   ============================================================ */
add_action( 'wp_body_open', function() {
    echo '<a class="skip-to-content" href="#primary">' . esc_html__( 'Saltar al contenido', 'tso-blog' ) . '</a>';
} );

/* ============================================================
   6. SIDEBARS
   ============================================================ */
add_action( 'widgets_init', function() {
    $widget_args = array(
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    );

    register_sidebar( array_merge( $widget_args, array(
        'name'        => __( 'Sidebar Noticias', 'tso-blog' ),
        'id'          => 'sidebar-1',
        'description' => __( 'Columna lateral. Se muestra en entradas, páginas (plantilla por defecto), archivos y búsqueda. No aparece en la portada del blog, ni en las plantillas Contacto o Portfolio. Si el personalizador dice que hay un área más sin mostrar, abre una entrada o una página normal en la vista previa.', 'tso-blog' ),
    ) ) );
    register_sidebar( array_merge( $widget_args, array(
        'name'        => __( 'Footer columna 1', 'tso-blog' ),
        'id'          => 'footer-1',
        'description' => __( 'Pie de página (menús, texto…). Si solo usas esta columna y dejas 2 y 3 vacías, los menús salen en horizontal. Con 2 o 3 columnas, layout en columnas iguales.', 'tso-blog' ),
    ) ) );
    register_sidebar( array_merge( $widget_args, array(
        'name'        => __( 'Footer columna 2', 'tso-blog' ),
        'id'          => 'footer-2',
        'description' => __( 'Segunda columna. Imágenes: usa 600×450 px (4:3) o 600×600 (cuadrado). Para que queden parejas con la columna 3, las dos fotos deben tener exactamente la misma medida y proporción.', 'tso-blog' ),
    ) ) );
    register_sidebar( array_merge( $widget_args, array(
        'name'        => __( 'Footer columna 3', 'tso-blog' ),
        'id'          => 'footer-3',
        'description' => __( 'Tercera columna. Imágenes: 600×450 px (4:3) o 600×600 (cuadrado), la misma medida que en la columna 2. Si una es vertical y la otra horizontal, no quedarán alineadas.', 'tso-blog' ),
    ) ) );
} );

/* ============================================================
   6b. PORTFOLIO / WALLPAPER GALLERY (page template)
   ============================================================ */

/**
 * Wallpaper download sizes (Customizer image size key => meta).
 *
 * @return array<string, array{label: string, width: int, height: int}>
 */
function tsothm_get_wallpaper_sizes() {
    return array(
        'tsothm-wall-720'    => array(
            'label'  => __( '1280 × 720 (HD)', 'tso-blog' ),
            'width'  => 1280,
            'height' => 720,
        ),
        'tsothm-wall-1080'   => array(
            'label'  => __( '1920 × 1080 (Full HD)', 'tso-blog' ),
            'width'  => 1920,
            'height' => 1080,
        ),
        'tsothm-wall-1440'   => array(
            'label'  => __( '2560 × 1440 (QHD)', 'tso-blog' ),
            'width'  => 2560,
            'height' => 1440,
        ),
        'tsothm-wall-4k'     => array(
            'label'  => __( '3840 × 2160 (4K)', 'tso-blog' ),
            'width'  => 3840,
            'height' => 2160,
        ),
        'tsothm-wall-mobile' => array(
            'label'  => __( '1080 × 1920 (Móvil)', 'tso-blog' ),
            'width'  => 1080,
            'height' => 1920,
        ),
    );
}

/**
 * Whether the current (or given) page uses the portfolio gallery template.
 *
 * @param int $post_id Optional post ID.
 * @return bool
 */
function tsothm_is_portfolio_gallery_page( $post_id = 0 ) {
    $post_id = $post_id ? (int) $post_id : (int) get_the_ID();
    if ( ! $post_id ) {
        return false;
    }
    return 'page-templates/portfolio-gallery.php' === get_page_template_slug( $post_id );
}

/**
 * Recursively collect image attachment IDs from parsed blocks.
 *
 * @param array $blocks Parsed blocks.
 * @return int[]
 */
function tsothm_extract_image_ids_from_blocks( $blocks ) {
    $ids = array();

    foreach ( (array) $blocks as $block ) {
        if ( empty( $block['blockName'] ) ) {
            if ( ! empty( $block['innerBlocks'] ) ) {
                $ids = array_merge( $ids, tsothm_extract_image_ids_from_blocks( $block['innerBlocks'] ) );
            }
            continue;
        }

        $name = $block['blockName'];
        $attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();

        if ( 'core/image' === $name && ! empty( $attrs['id'] ) ) {
            $ids[] = absint( $attrs['id'] );
        }

        if ( 'core/gallery' === $name ) {
            if ( ! empty( $attrs['ids'] ) && is_array( $attrs['ids'] ) ) {
                foreach ( $attrs['ids'] as $gid ) {
                    $ids[] = absint( $gid );
                }
            }
        }

        if ( ! empty( $block['innerBlocks'] ) ) {
            $ids = array_merge( $ids, tsothm_extract_image_ids_from_blocks( $block['innerBlocks'] ) );
        }
    }

    return $ids;
}

/**
 * Image IDs for a portfolio page (Gallery/Image blocks, then attachments).
 *
 * @param int $post_id Page ID.
 * @return int[]
 */
function tsothm_get_portfolio_image_ids( $post_id ) {
    $post_id = absint( $post_id );
    $post    = get_post( $post_id );
    $ids     = array();

    if ( ! $post ) {
        return array();
    }

    if ( has_blocks( $post->post_content ) ) {
        $ids = tsothm_extract_image_ids_from_blocks( parse_blocks( $post->post_content ) );
    }

    if ( empty( $ids ) && has_shortcode( $post->post_content, 'gallery' ) ) {
        if ( preg_match_all( '/\[gallery[^\]]*(?:ids|include)=["\']([^"\']+)["\']/', $post->post_content, $matches ) ) {
            foreach ( $matches[1] as $list ) {
                foreach ( explode( ',', $list ) as $raw_id ) {
                    $ids[] = absint( $raw_id );
                }
            }
        }
    }

    if ( empty( $ids ) ) {
        $media = get_attached_media( 'image', $post_id );
        foreach ( $media as $attachment ) {
            $ids[] = (int) $attachment->ID;
        }
    }

    $ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );

    /**
     * Filter portfolio gallery image IDs.
     *
     * @param int[] $ids     Attachment IDs.
     * @param int   $post_id Page ID.
     */
    return apply_filters( 'tsothm_portfolio_image_ids', $ids, $post_id );
}

/**
 * Wallpaper crops (tsothm-wall-*) are big, hard-cropped files. They are only
 * generated for images that belong to a portfolio gallery page — uploaded from
 * that page, or referenced by it when it is saved — not for every upload.
 *
 * @param bool|null $set Optional. True/false to force generation; omit to read.
 * @return bool
 */
function tsothm_wallpaper_sizes_forced( $set = null ) {
    static $forced = false;
    if ( null !== $set ) {
        $forced = (bool) $set;
    }
    return $forced;
}

/**
 * Drop wallpaper sizes for images that are not part of a portfolio page.
 *
 * @param array $sizes         Sizes about to be generated.
 * @param array $image_meta    Attachment metadata.
 * @param int   $attachment_id Attachment ID.
 * @return array
 */
function tsothm_limit_wallpaper_sizes( $sizes, $image_meta = array(), $attachment_id = 0 ) {
    // Sin attachment concreto (p. ej. TSO Image Master consultando qué tamaños están registrados) no se filtra nada:
    // si no, los tamaños tsothm-wall-* parecerían huérfanos y se podrían borrar.
    if ( ! $attachment_id || tsothm_wallpaper_sizes_forced() ) {
        return $sizes;
    }
    $parent_id = $attachment_id ? (int) wp_get_post_parent_id( $attachment_id ) : 0;
    if ( $parent_id && tsothm_is_portfolio_gallery_page( $parent_id ) ) {
        return $sizes;
    }
    return array_diff_key( (array) $sizes, tsothm_get_wallpaper_sizes() );
}
add_filter( 'intermediate_image_sizes_advanced', 'tsothm_limit_wallpaper_sizes', 10, 3 );

/**
 * When a portfolio page is saved, create any missing wallpaper crops for the
 * images it uses (covers images uploaded earlier from the Media Library).
 *
 * @param int     $post_id Page ID.
 * @param WP_Post $post    Page object.
 */
function tsothm_generate_portfolio_wallpaper_sizes( $post_id, $post ) {
    if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) || ! tsothm_is_portfolio_gallery_page( $post_id ) ) {
        return;
    }
    if ( ! function_exists( 'wp_update_image_subsizes' ) ) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    tsothm_wallpaper_sizes_forced( true );
    foreach ( tsothm_get_portfolio_image_ids( $post_id ) as $attachment_id ) {
        if ( tsothm_is_portfolio_image_mime( $attachment_id ) ) {
            wp_update_image_subsizes( $attachment_id );
        }
    }
    tsothm_wallpaper_sizes_forced( false );
}
add_action( 'save_post_page', 'tsothm_generate_portfolio_wallpaper_sizes', 20, 2 );

/**
 * Whether an attachment is a supported raster image for the portfolio.
 *
 * @param int $attachment_id Attachment ID.
 * @return bool
 */
function tsothm_is_portfolio_image_mime( $attachment_id ) {
    $mime = get_post_mime_type( $attachment_id );
    $ok   = array( 'image/jpeg', 'image/png', 'image/webp', 'image/gif' );
    return $mime && in_array( $mime, $ok, true );
}

/**
 * Display name under a portfolio image.
 *
 * @param int $attachment_id Attachment ID.
 * @return string
 */
function tsothm_get_portfolio_image_label( $attachment_id ) {
    $title = get_the_title( $attachment_id );
    if ( $title ) {
        return $title;
    }
    $alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
    if ( is_string( $alt ) && '' !== $alt ) {
        return $alt;
    }
    $file = get_attached_file( $attachment_id );
    return $file ? pathinfo( $file, PATHINFO_FILENAME ) : __( 'Imagen', 'tso-blog' );
}

/**
 * Available download URLs for an attachment.
 * Always returns Original + every wallpaper size (unavailable ones marked).
 *
 * @param int $attachment_id Attachment ID.
 * @return array<int, array{key: string, label: string, url: string, available: bool, width: int, height: int, tip: string}>
 */
function tsothm_get_portfolio_downloads( $attachment_id ) {
    $attachment_id = absint( $attachment_id );
    $downloads     = array();
    $full          = wp_get_attachment_image_src( $attachment_id, 'full' );

    $full_w = ( $full && ! empty( $full[1] ) ) ? (int) $full[1] : 0;
    $full_h = ( $full && ! empty( $full[2] ) ) ? (int) $full[2] : 0;

    if ( $full && ! empty( $full[0] ) ) {
        $downloads[] = array(
            'key'       => 'full',
            'label'     => __( 'Original', 'tso-blog' ),
            'url'       => $full[0],
            'available' => true,
            'width'     => $full_w,
            'height'    => $full_h,
            'tip'       => '',
        );
    }

    foreach ( tsothm_get_wallpaper_sizes() as $size_key => $meta ) {
        $url       = '';
        $available = false;
        $data      = image_get_intermediate_size( $attachment_id, $size_key );

        if ( ! empty( $data['url'] ) && (int) $data['width'] === (int) $meta['width'] && (int) $data['height'] === (int) $meta['height'] ) {
            $url       = $data['url'];
            $available = true;
        } elseif ( $full_w >= (int) $meta['width'] && $full_h >= (int) $meta['height'] ) {
            // Source is large enough but crop missing (old upload) — tip to regenerate.
            $tip = __( 'Tamaño aún no generado. Regenera miniaturas o vuelve a subir la imagen.', 'tso-blog' );
        } else {
            $tip = sprintf(
                /* translators: 1: required width, 2: required height, 3: original width, 4: original height */
                __( 'No disponible: hace falta al menos %1$s×%2$s px. Esta imagen mide %3$s×%4$s px.', 'tso-blog' ),
                (int) $meta['width'],
                (int) $meta['height'],
                $full_w,
                $full_h
            );
        }

        $downloads[] = array(
            'key'       => $size_key,
            'label'     => $meta['label'],
            'url'       => $url,
            'available' => $available,
            'width'     => (int) $meta['width'],
            'height'    => (int) $meta['height'],
            'tip'       => isset( $tip ) ? $tip : '',
        );
        unset( $tip );
    }

    return $downloads;
}

/**
 * Strip gallery/image blocks from content (portfolio template intro only).
 *
 * @param string $content Post content.
 * @return string
 */
function tsothm_portfolio_intro_from_content( $content ) {
    if ( ! has_blocks( $content ) ) {
        $content = preg_replace( '/\[gallery[\s\S]*?\]/', '', $content );
        $content = preg_replace( '/\[caption[\s\S]*?\[\/caption\]/', '', $content );
        $content = preg_replace( '/<img\b[^>]*>/i', '', $content );
        return trim( $content );
    }

    $blocks  = parse_blocks( $content );
    $keep    = array();
    $allowed = array( 'core/heading', 'core/paragraph', 'core/list', 'core/list-item', 'core/quote', 'core/spacer', 'core/separator' );

    foreach ( $blocks as $block ) {
        if ( empty( $block['blockName'] ) ) {
            if ( isset( $block['innerHTML'] ) && trim( wp_strip_all_tags( $block['innerHTML'] ) ) !== '' ) {
                $keep[] = $block;
            }
            continue;
        }
        if ( in_array( $block['blockName'], $allowed, true ) ) {
            $keep[] = $block;
        }
    }

    return trim( implode( '', array_map( 'render_block', $keep ) ) );
}

/**
 * Render the portfolio gallery markup.
 *
 * @param int $post_id Page ID.
 */
function tsothm_render_portfolio_gallery( $post_id ) {
    $ids = tsothm_get_portfolio_image_ids( $post_id );
    $ids = array_values(
        array_filter(
            $ids,
            'tsothm_is_portfolio_image_mime'
        )
    );

    if ( empty( $ids ) ) {
        if ( current_user_can( 'edit_post', $post_id ) ) {
            echo '<p class="tso-portfolio-empty">' . esc_html__( 'Añade un bloque Galería o Imagen (JPG, PNG, WebP o GIF) en el editor y publica la página.', 'tso-blog' ) . '</p>';
        }
        return;
    }

    echo '<div class="tso-portfolio-gallery" role="list">';
    foreach ( $ids as $attachment_id ) {
        $label = tsothm_get_portfolio_image_label( $attachment_id );
        $thumb = wp_get_attachment_image(
            $attachment_id,
            'large',
            false,
            array(
                'class'   => 'tso-portfolio-image',
                'loading' => 'lazy',
                'alt'     => $label,
            )
        );
        if ( ! $thumb ) {
            continue;
        }
        $downloads = tsothm_get_portfolio_downloads( $attachment_id );
        $full_src  = wp_get_attachment_image_url( $attachment_id, 'full' );

        echo '<figure class="tso-portfolio-item" role="listitem">';
        if ( $full_src ) {
            printf(
                '<a class="tso-portfolio-thumb" href="%s" data-tso-lightbox="1">%s</a>',
                esc_url( $full_src ),
                $thumb // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image().
            );
        } else {
            echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
        echo '<figcaption class="tso-portfolio-caption">';
        echo '<span class="tso-portfolio-name">' . esc_html( $label ) . '</span>';
        if ( ! empty( $downloads ) ) {
            echo '<div class="tso-portfolio-downloads">';
            echo '<span class="tso-portfolio-downloads-label">' . esc_html__( 'Descargar:', 'tso-blog' ) . '</span> ';
            echo '<ul class="tso-portfolio-download-list">';
            foreach ( $downloads as $dl ) {
                if ( empty( $dl['available'] ) || ( empty( $dl['url'] ) && 'full' !== $dl['key'] ) ) {
                    continue;
                }
                $filename = sanitize_file_name( $label . '-' . $dl['key'] );
                echo '<li>';
                if ( 'full' === $dl['key'] ) {
                    $dim_text = sprintf(
                        /* translators: 1: width in px, 2: height in px */
                        __( '%1$s × %2$s px', 'tso-blog' ),
                        (int) $dl['width'],
                        (int) $dl['height']
                    );
                    echo '<details class="tso-portfolio-original">';
                    echo '<summary class="tso-portfolio-download tso-portfolio-download--original">' . esc_html__( 'Original', 'tso-blog' ) . '</summary>';
                    echo '<div class="tso-portfolio-original-pop" role="dialog" aria-label="' . esc_attr__( 'Medidas de la imagen original', 'tso-blog' ) . '">';
                    echo '<p class="tso-portfolio-original-dims"><strong>' . esc_html__( 'Medidas:', 'tso-blog' ) . '</strong> ' . esc_html( $dim_text ) . '</p>';
                    printf(
                        '<a class="tso-portfolio-download tso-portfolio-download--go" href="%s" download="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                        esc_url( $dl['url'] ),
                        esc_attr( $filename ),
                        esc_html__( 'Descargar original', 'tso-blog' )
                    );
                    echo '</div></details>';
                } else {
                    printf(
                        '<a class="tso-portfolio-download" href="%s" download="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                        esc_url( $dl['url'] ),
                        esc_attr( $filename ),
                        esc_html( $dl['label'] )
                    );
                }
                echo '</li>';
            }
            echo '</ul></div>';
        }
        echo '</figcaption></figure>';
    }
    echo '</div>';
}

/**
 * Limit editor blocks on the Portfolio page template.
 *
 * @param bool|string[]               $allowed         Allowed block types.
 * @param WP_Block_Editor_Context     $editor_context  Editor context.
 * @return bool|string[]
 */
function tsothm_portfolio_allowed_blocks( $allowed, $editor_context ) {
    if ( empty( $editor_context->post ) || 'page' !== $editor_context->post->post_type ) {
        return $allowed;
    }
    if ( ! tsothm_is_portfolio_gallery_page( (int) $editor_context->post->ID ) ) {
        return $allowed;
    }
    return array(
        'core/gallery',
        'core/image',
        'core/heading',
        'core/paragraph',
        'core/list',
        'core/list-item',
        'core/spacer',
        'core/separator',
    );
}
add_filter( 'allowed_block_types_all', 'tsothm_portfolio_allowed_blocks', 10, 2 );

/**
 * Admin notice: how to use the portfolio template + regenerate thumbnails tip.
 */
function tsothm_portfolio_admin_notice() {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        return;
    }
    $user_id = get_current_user_id();
    if ( $user_id && get_user_meta( $user_id, 'tsothm_dismiss_portfolio_notice', true ) ) {
        return;
    }
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen || 'post' !== $screen->base || 'page' !== $screen->post_type ) {
        return;
    }
    $post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen check.
    if ( ! $post_id || ! tsothm_is_portfolio_gallery_page( $post_id ) ) {
        return;
    }
    $dismiss_url = wp_nonce_url(
        add_query_arg( 'tsothm_dismiss_portfolio_notice', '1' ),
        'tsothm_dismiss_portfolio_notice'
    );
    echo '<div class="notice notice-info is-dismissible"><p>';
    echo esc_html__( 'Plantilla Portfolio: usa bloques Galería o Imagen (JPG, PNG, WebP, GIF). En la web se muestra la galería con nombre y descargas (1920×1080 y otros). Si faltan tamaños en fotos antiguas, regenera miniaturas tras activar el tema.', 'tso-blog' );
    echo ' <a href="' . esc_url( $dismiss_url ) . '">' . esc_html__( 'Descartar aviso', 'tso-blog' ) . '</a>';
    echo '</p></div>';
}
add_action( 'admin_notices', 'tsothm_portfolio_admin_notice' );

/**
 * Permanently dismiss the portfolio admin notice for the current user.
 */
function tsothm_handle_dismiss_portfolio_notice() {
    if ( ! isset( $_GET['tsothm_dismiss_portfolio_notice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- verified below.
        return;
    }
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        return;
    }
    check_admin_referer( 'tsothm_dismiss_portfolio_notice' );
    $user_id = get_current_user_id();
    if ( $user_id ) {
        update_user_meta( $user_id, 'tsothm_dismiss_portfolio_notice', 1 );
    }
    $redirect = remove_query_arg( array( 'tsothm_dismiss_portfolio_notice', '_wpnonce' ) );
    wp_safe_redirect( $redirect );
    exit;
}
add_action( 'admin_init', 'tsothm_handle_dismiss_portfolio_notice' );

/**
 * Body class for portfolio template.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function tsothm_portfolio_body_class( $classes ) {
    if ( is_page() && tsothm_is_portfolio_gallery_page() ) {
        $classes[] = 'tso-portfolio-gallery-template';
    }
    return $classes;
}
add_filter( 'body_class', 'tsothm_portfolio_body_class' );

/**
 * Body class for main menu button style (Customizer).
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function tsothm_nav_button_style_body_class( $classes ) {
    $style     = tsothm_sanitize_nav_button_style( get_theme_mod( 'tsothm_nav_button_style', 'raised' ) );
    $classes[] = 'tsothm-nav-style-' . $style;
    return $classes;
}
add_filter( 'body_class', 'tsothm_nav_button_style_body_class' );

/**
 * Sanitize Google Maps embed URL (or pasted embed markup → src).
 *
 * @param mixed $value Raw value.
 * @return string
 */
function tsothm_sanitize_google_maps_embed( $value ) {
    $value = trim( wp_unslash( (string) $value ) );
    if ( '' === $value ) {
        return '';
    }

    if ( preg_match( '/\bsrc\s*=\s*[\'"]([^\'"]+)[\'"]/i', $value, $matches ) ) {
        $value = $matches[1];
    }

    $value = esc_url_raw( $value );
    if ( ! $value ) {
        return '';
    }

    $host = wp_parse_url( $value, PHP_URL_HOST );
    if ( ! is_string( $host ) || ! preg_match( '/(^|\.)google\.[a-z.]+$/i', $host ) ) {
        return '';
    }

    if ( false === strpos( $value, '/maps/embed' ) && false === strpos( $value, 'output=embed' ) ) {
        return '';
    }

    return $value;
}

/**
 * Sanitize a contact form shortcode string (no HTML).
 *
 * @param mixed $value Raw value.
 * @return string
 */
function tsothm_sanitize_contact_form_shortcode( $value ) {
    $value = trim( wp_unslash( (string) $value ) );
    if ( '' === $value ) {
        return '';
    }
    $value = wp_strip_all_tags( $value );
    // Only a single shortcode token, e.g. [contact-form-7 id="123" title="Contact"].
    if ( ! preg_match( '/^\[[a-zA-Z][a-zA-Z0-9_-]*(?:\s[^\[\]]*)?\]$/', $value ) ) {
        return '';
    }
    return $value;
}

/**
 * Contact page settings from Customizer.
 *
 * @return array<string, string>
 */
function tsothm_get_contact_settings() {
    return array(
        'subtitle'      => sanitize_text_field( get_theme_mod( 'tsothm_contact_subtitle', '' ) ),
        'intro'         => sanitize_textarea_field( get_theme_mod( 'tsothm_contact_intro', '' ) ),
        'address'       => sanitize_textarea_field( get_theme_mod( 'tsothm_contact_address', '' ) ),
        'email'         => sanitize_email( get_theme_mod( 'tsothm_contact_email', '' ) ),
        'email_alt'     => sanitize_email( get_theme_mod( 'tsothm_contact_email_alt', '' ) ),
        'phone'         => sanitize_text_field( get_theme_mod( 'tsothm_contact_phone', '' ) ),
        'phone_alt'     => sanitize_text_field( get_theme_mod( 'tsothm_contact_phone_alt', '' ) ),
        'maps_embed'    => tsothm_sanitize_google_maps_embed( get_theme_mod( 'tsothm_contact_maps_embed', '' ) ),
        'form_shortcode'=> tsothm_sanitize_contact_form_shortcode( get_theme_mod( 'tsothm_contact_form_shortcode', '' ) ),
        'show_social'   => (string) absint( get_theme_mod( 'tsothm_contact_show_social', 1 ) ),
    );
}

/**
 * Whether the page uses the Contact template.
 *
 * @param int $post_id Optional post ID.
 * @return bool
 */
function tsothm_is_contact_page( $post_id = 0 ) {
    $post_id = $post_id ? (int) $post_id : (int) get_the_ID();
    if ( ! $post_id ) {
        return false;
    }
    return 'page-templates/contact.php' === get_page_template_slug( $post_id );
}

/**
 * Body class for contact template.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function tsothm_contact_body_class( $classes ) {
    if ( is_page() && tsothm_is_contact_page() ) {
        $classes[] = 'tso-contact-template';
    }
    return $classes;
}
add_filter( 'body_class', 'tsothm_contact_body_class' );

/**
 * Body class when layout is full width.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function tsothm_layout_body_class( $classes ) {
    if ( 'full' === tsothm_sanitize_layout_width( get_theme_mod( 'tsothm_layout_width', '1060' ) ) ) {
        $classes[] = 'tso-layout-full';
    }
    return $classes;
}
add_filter( 'body_class', 'tsothm_layout_body_class' );

/**
 * Render Google Maps embed placeholder if configured (mounted via JS).
 *
 * @param string $embed_url Sanitized embed URL.
 */
function tsothm_render_contact_map( $embed_url ) {
    $embed_url = tsothm_sanitize_google_maps_embed( $embed_url );
    if ( '' === $embed_url ) {
        return;
    }
    printf(
        '<div class="tsothm-contact-map" data-map-src="%1$s" data-map-title="%2$s"></div>',
        esc_url( $embed_url ),
        esc_attr__( 'Mapa de ubicación', 'tso-blog' )
    );
}

/* ============================================================
   7. COMPATIBILIDAD CON PLUGINS DE CACHÉ
   ============================================================ */
add_filter( 'comment_form_default_fields', function( $fields ) {
    if ( isset( $fields['cookies'] ) ) {
        $fields['cookies'] = str_replace( 'checked="checked"', '', $fields['cookies'] );
    }
    return $fields;
} );

/**
 * Spanish labels for the comment form (safe replacements, no attribute corruption).
 */
function tsothm_comment_form_defaults_es( $defaults ) {
    if ( ! is_singular( 'post' ) ) {
        return $defaults;
    }
    $defaults['title_reply']          = __( 'Deja un comentario', 'tso-blog' );
    $defaults['title_reply_to']       = __( 'Responder a %s', 'tso-blog' );
    $defaults['cancel_reply_link']    = __( 'Cancelar respuesta', 'tso-blog' );
    $defaults['label_submit']         = __( 'Publicar comentario', 'tso-blog' );
    $defaults['comment_notes_before'] = '<p class="comment-notes">' . __( 'Tu dirección de correo electrónico no será publicada. Los campos obligatorios están marcados con', 'tso-blog' ) . ' <span aria-hidden="true">*</span></p>';
    $defaults['comment_notes_after']  = '';
    return $defaults;
}
add_filter( 'comment_form_defaults', 'tsothm_comment_form_defaults_es' );

/**
 * Translate comment form field labels without breaking HTML attributes.
 *
 * @param array $fields Comment form fields.
 * @return array
 */
function tsothm_comment_form_fields_es( $fields ) {
    if ( ! is_singular( 'post' ) ) {
        return $fields;
    }
    if ( isset( $fields['author'] ) ) {
        $fields['author'] = preg_replace(
            '/<label for="author">(.*?)<\/label>/',
            '<label for="author">' . esc_html__( 'Nombre', 'tso-blog' ) . ' <span class="required" aria-hidden="true">*</span></label>',
            $fields['author'],
            1
        );
    }
    if ( isset( $fields['email'] ) ) {
        $fields['email'] = preg_replace(
            '/<label for="email">(.*?)<\/label>/',
            '<label for="email">' . esc_html__( 'Correo electrónico', 'tso-blog' ) . ' <span class="required" aria-hidden="true">*</span></label>',
            $fields['email'],
            1
        );
    }
    if ( isset( $fields['url'] ) ) {
        $fields['url'] = preg_replace(
            '/<label for="url">(.*?)<\/label>/',
            '<label for="url">' . esc_html__( 'Sitio web', 'tso-blog' ) . '</label>',
            $fields['url'],
            1
        );
    }
    return $fields;
}
add_filter( 'comment_form_default_fields', 'tsothm_comment_form_fields_es', 20 );

/**
 * Translate the comment textarea label.
 *
 * @param string $field Comment field HTML.
 * @return string
 */
function tsothm_comment_form_field_comment_es( $field ) {
    if ( ! is_singular( 'post' ) ) {
        return $field;
    }
    return preg_replace(
        '/<label for="comment">(.*?)<\/label>/',
        '<label for="comment">' . esc_html__( 'Comentario', 'tso-blog' ) . ' <span class="required" aria-hidden="true">*</span></label>',
        $field,
        1
    );
}
add_filter( 'comment_form_field_comment', 'tsothm_comment_form_field_comment_es' );

/**
 * Open links inside comment body in a new tab (security: noopener noreferrer).
 *
 * Runs after WordPress make_clickable() on comment_text.
 *
 * @param string $text Comment HTML.
 * @return string
 */
function tsothm_comment_links_open_new_tab( $text ) {
    if ( '' === $text || false === stripos( $text, '<a' ) ) {
        return $text;
    }

    $result = preg_replace_callback(
        '/<a\b\s*([^>]*?)>/i',
        function ( $matches ) {
            $attrs = $matches[1];

            if ( ! preg_match( '/\btarget\s*=/i', $attrs ) ) {
                $attrs .= ' target="_blank"';
            }

            if ( preg_match( '/\brel=(["\'])([^"\']*)\1/i', $attrs, $rel_match ) ) {
                $rel_parts = preg_split( '/\s+/', trim( $rel_match[2] ) );
                foreach ( array( 'noopener', 'noreferrer' ) as $flag ) {
                    if ( ! in_array( $flag, $rel_parts, true ) ) {
                        $rel_parts[] = $flag;
                    }
                }
                $new_rel = implode( ' ', array_filter( $rel_parts ) );
                $attrs   = preg_replace(
                    '/\brel=(["\'])[^"\']*\1/i',
                    'rel="' . esc_attr( $new_rel ) . '"',
                    $attrs
                );
            } else {
                $attrs .= ' rel="nofollow ugc noopener noreferrer"';
            }

            return '<a ' . $attrs . '>';
        },
        $text
    );

    return ( null !== $result ) ? $result : $text;
}
add_filter( 'comment_text', 'tsothm_comment_links_open_new_tab', 99 );

/**
 * Open comment author website links in a new tab.
 *
 * @param string $link    Author link HTML.
 * @param string $author  Author name.
 * @param int    $comment_id Comment ID.
 * @return string
 */
function tsothm_comment_author_link_new_tab( $link, $author, $comment_id ) {
    unset( $author, $comment_id );
    if ( '' === $link || false === stripos( $link, '<a' ) ) {
        return $link;
    }
    return tsothm_comment_links_open_new_tab( $link );
}
add_filter( 'get_comment_author_link', 'tsothm_comment_author_link_new_tab', 10, 3 );

add_action( 'comment_post', function() {
    if ( defined( 'LSCWP_V' ) && class_exists( '\LiteSpeed\Purge' ) ) {
        \LiteSpeed\Purge::purge_all();
    }
} );

add_action( 'template_redirect', function() {
    if ( is_singular() && function_exists( 'sharing_display' ) && defined( 'LSCWP_V' ) ) {
        do_action( 'litespeed_nonce', 'sharing_nonce' );
    }
} );

/* ============================================================
   8. JETPACK
   ============================================================ */
add_filter( 'jetpack_sharing_counts', '__return_false' );

/* ============================================================
   9. CUSTOMIZER
   ============================================================ */

/**
 * Supported header social networks (slug => label + SVG path).
 *
 * @return array<string, array{label: string, path: string}>
 */
function tsothm_get_social_networks() {
    return array(
        'youtube'   => array(
            'label' => 'YouTube',
            'path'  => '<path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.6A3 3 0 0 0 .5 6.2C0 8.1 0 12 0 12s0 3.9.5 5.8a3 3 0 0 0 2.1 2.1c1.9.6 9.4.6 9.4.6s7.5 0 9.4-.6a3 3 0 0 0 2.1-2.1C24 15.9 24 12 24 12s0-3.9-.5-5.8zM9.75 15.5v-7l6.5 3.5-6.5 3.5z"/>',
        ),
        'linkedin'  => array(
            'label' => 'LinkedIn',
            'path'  => '<path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.13 1.45-2.13 2.94v5.67H9.37V9h3.41v1.56h.05a3.74 3.74 0 0 1 3.37-1.85c3.6 0 4.27 2.37 4.27 5.45v6.29zM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12zM7.12 20.45H3.56V9h3.56v11.45zM22.22 0H1.77A1.75 1.75 0 0 0 0 1.73v20.54A1.75 1.75 0 0 0 1.77 24h20.45A1.76 1.76 0 0 0 24 22.27V1.73A1.76 1.76 0 0 0 22.22 0z"/>',
        ),
        'facebook'  => array(
            'label' => 'Facebook',
            'path'  => '<path d="M24 12.07C24 5.41 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.04V9.41c0-3.02 1.8-4.7 4.54-4.7 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.95.93-1.95 1.88v2.26h3.32l-.53 3.5h-2.79V24C19.61 23.1 24 18.1 24 12.07z"/>',
        ),
        'twitter'   => array(
            'label' => 'X / Twitter',
            'path'  => '<path d="M18.24 2h3.28L13.9 10.28 22.8 22h-6.91l-5.45-7.14L4.24 22H.95l8.1-9.27L.54 2h7.08l4.93 6.51L18.24 2zm-1.15 18h1.82L7 3.92H5.06L17.09 20z"/>',
        ),
        'instagram' => array(
            'label' => 'Instagram',
            'path'  => '<path d="M12 2.16c3.2 0 3.58.01 4.85.07 3.25.15 4.77 1.69 4.92 4.92.06 1.27.07 1.65.07 4.85 0 3.2-.01 3.58-.07 4.85-.15 3.23-1.66 4.77-4.92 4.92-1.27.06-1.64.07-4.85.07-3.2 0-3.58-.01-4.85-.07-3.26-.15-4.77-1.7-4.92-4.92C2.17 15.58 2.16 15.2 2.16 12c0-3.2.01-3.58.07-4.85C2.38 3.7 3.9 2.16 7.15 2.09 8.42 2.17 8.8 2.16 12 2.16zM12 0C8.74 0 8.33.01 7.05.07 2.7.27.27 2.7.07 7.05.01 8.33 0 8.74 0 12c0 3.26.01 3.67.07 4.95.2 4.36 2.62 6.78 6.98 6.98C8.33 23.99 8.74 24 12 24c3.26 0 3.67-.01 4.95-.07 4.35-.2 6.78-2.62 6.98-6.98.06-1.28.07-1.69.07-4.95 0-3.26-.01-3.67-.07-4.95-.2-4.35-2.62-6.78-6.98-6.98C15.67.01 15.26 0 12 0zm0 5.84a6.16 6.16 0 1 0 0 12.32A6.16 6.16 0 0 0 12 5.84zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.4-11.85a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88z"/>',
        ),
        'tiktok'    => array(
            'label' => 'TikTok',
            'path'  => '<path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/>',
        ),
        'telegram'  => array(
            'label' => 'Telegram',
            'path'  => '<path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>',
        ),
        'whatsapp'  => array(
            'label' => 'WhatsApp',
            'path'  => '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>',
        ),
        'pinterest' => array(
            'label' => 'Pinterest',
            'path'  => '<path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z"/>',
        ),
        'threads'   => array(
            'label' => 'Threads',
            'path'  => '<path d="M16.6 12.2c0 2.4-1.6 4.2-4.1 4.4-1.3.1-2.5-.3-3.4-1.1-.2-.2-.2-.5 0-.6.2-.2.5-.2.6 0 .8.7 1.8 1 2.9.9 2-.1 3.3-1.4 3.3-3.3 0-1.8-1.2-2.9-3.1-2.9-1.6 0-2.8.9-3.1 2.2h2.1c.2 0 .4.2.4.4s-.2.4-.4.4H8.4c-.2 0-.4-.2-.4-.4 0-2 1.7-3.6 3.9-3.6 2.5 0 4.1 1.5 4.1 3.7v.3h.6zm2.9-.2c0 .7 0 1.3-.1 2-.6 4.4-4.4 7.7-8.9 7.7S2.2 18.4 1.6 14c-.1-.7-.1-1.3-.1-2s0-1.3.1-2C2.2 5.6 6 2.3 10.5 2.3s8.3 3.3 8.9 7.7c.1.7.1 1.3.1 2z"/>',
        ),
        'github'    => array(
            'label' => 'GitHub',
            'path'  => '<path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>',
        ),
    );
}

/**
 * Default social order as comma-separated catalog keys.
 *
 * @return string
 */
function tsothm_get_default_social_order_string() {
    return implode( ',', array_keys( tsothm_get_social_networks() ) );
}

/**
 * Sanitize comma-separated social network order list.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function tsothm_sanitize_social_order_list( $value ) {
    $allowed = array_keys( tsothm_get_social_networks() );
    $parts   = array_filter( array_map( 'sanitize_key', explode( ',', (string) $value ) ) );
    $ordered = array();

    foreach ( $parts as $part ) {
        if ( in_array( $part, $allowed, true ) && ! in_array( $part, $ordered, true ) ) {
            $ordered[] = $part;
        }
    }

    foreach ( $allowed as $key ) {
        if ( ! in_array( $key, $ordered, true ) ) {
            $ordered[] = $key;
        }
    }

    return implode( ',', $ordered );
}

/**
 * Ordered social network keys (supports legacy per-network order theme mods).
 *
 * @return string[]
 */
function tsothm_get_social_order_keys() {
    $saved = get_theme_mod( 'tsothm_social_order', '' );

    if ( '' !== $saved && null !== $saved ) {
        return explode( ',', tsothm_sanitize_social_order_list( $saved ) );
    }

    // Legacy: tsothm_social_order_{network} numeric fields (1–99).
    $networks   = tsothm_get_social_networks();
    $with_order = array();
    $index      = 0;
    $has_legacy = false;

    foreach ( $networks as $key => $data ) {
        $index++;
        $legacy = get_theme_mod( 'tsothm_social_order_' . $key, null );
        if ( null !== $legacy && '' !== $legacy ) {
            $has_legacy         = true;
            $with_order[ $key ] = max( 1, min( 99, absint( $legacy ) ) );
        } else {
            $with_order[ $key ] = $index * 10;
        }
    }

    if ( $has_legacy ) {
        asort( $with_order, SORT_NUMERIC );
        return array_keys( $with_order );
    }

    return array_keys( $networks );
}

/**
 * Networks that have a URL, sorted by Customizer drag order.
 *
 * @return array<string, array{label: string, path: string, url: string}>
 */
function tsothm_get_configured_social_icons() {
    $networks = tsothm_get_social_networks();
    $items    = array();

    foreach ( tsothm_get_social_order_keys() as $key ) {
        if ( ! isset( $networks[ $key ] ) ) {
            continue;
        }
        $url = (string) get_theme_mod( 'tsothm_social_' . $key, '' );
        if ( '' === $url ) {
            continue;
        }
        $items[ $key ] = array(
            'label' => $networks[ $key ]['label'],
            'path'  => $networks[ $key ]['path'],
            'url'   => $url,
        );
    }

    return $items;
}

add_action( 'customize_register', function( $wp_customize ) {

    require_once get_template_directory() . '/class-tsotheme-customize-social-order-control.php';
    require_once get_template_directory() . '/class-tsotheme-customize-repeater-control.php';

    /*
     * Orden del Personalizador (sentido común):
     * Identidad → Apariencia → Colores → Tipografía → Footer → Contenido → Integraciones → (core: Menús, Widgets…)
     */
    if ( $wp_customize->get_section( 'title_tagline' ) ) {
        $wp_customize->get_section( 'title_tagline' )->priority = 20;
    }

    $wp_customize->add_panel(
        'tsothm_appearance_panel',
        array(
            'title'       => __( 'Apariencia', 'tso-blog' ),
            'description' => __( 'Logo, ancho del sitio, redes en la cabecera e imagen de cabecera.', 'tso-blog' ),
            'priority'    => 25,
        )
    );

    $wp_customize->add_panel(
        'tsothm_footer_panel',
        array(
            'title'       => __( 'Footer', 'tso-blog' ),
            'description' => __( 'Ayuda de columnas de widgets y textos del pie de página. Los colores del footer están en Colores → Footer.', 'tso-blog' ),
            'priority'    => 40,
        )
    );

    $wp_customize->add_panel(
        'tsothm_content_panel',
        array(
            'title'       => __( 'Plantillas', 'tso-blog' ),
            'description' => __( 'Plantillas de Contacto, Equipo, Precios y Landing, y el anuncio superior.', 'tso-blog' ),
            'priority'    => 45,
        )
    );

    if ( $wp_customize->get_section( 'header_image' ) ) {
        $wp_customize->get_section( 'header_image' )->panel    = 'tsothm_appearance_panel';
        $wp_customize->get_section( 'header_image' )->priority = 40;
    }
    if ( $wp_customize->get_section( 'background_image' ) ) {
        $wp_customize->get_section( 'background_image' )->panel       = 'tsothm_appearance_panel';
        $wp_customize->get_section( 'background_image' )->priority    = 15;
        $wp_customize->get_section( 'background_image' )->description = __( 'Imagen de fondo opcional para toda la web, por detrás del contenido. Puedes subirla y ajustar su posición, tamaño, repetición y desplazamiento (parallax).', 'tso-blog' );
    }
    if ( $wp_customize->get_section( 'colors' ) ) {
        // Core “Colores” vacío: ocultar para no duplicar el panel TSO.
        $wp_customize->remove_section( 'colors' );
    }

    /* Logo — imagen (core) + tamaños */
    $wp_customize->add_section(
        'tsothm_logo_section',
        array(
            'title'       => __( 'Logo', 'tso-blog' ),
            'description' => __( 'Sube el logo y define su ancho en escritorio y móvil. No hay logo «sticky»: la cabecera no queda fija al hacer scroll.', 'tso-blog' ),
            'panel'       => 'tsothm_appearance_panel',
            'priority'    => 10,
        )
    );

    if ( $wp_customize->get_control( 'custom_logo' ) ) {
        $wp_customize->get_control( 'custom_logo' )->section  = 'tsothm_logo_section';
        $wp_customize->get_control( 'custom_logo' )->priority = 5;
        $wp_customize->get_control( 'custom_logo' )->label    = __( 'Logo', 'tso-blog' );
    }

    $wp_customize->add_setting(
        'tsothm_logo_width',
        array(
            'default'           => '300',
            'sanitize_callback' => 'tsothm_sanitize_logo_width_desktop',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_logo_width',
        array(
            'label'       => __( 'Ancho del logo (px)', 'tso-blog' ),
            'description' => __( 'Ancho máximo en escritorio (40–500). La altura se ajusta sola.', 'tso-blog' ),
            'section'     => 'tsothm_logo_section',
            'type'        => 'number',
            'priority'    => 10,
            'input_attrs' => array(
                'min'  => 40,
                'max'  => 500,
                'step' => 1,
            ),
        )
    );

    $wp_customize->add_setting(
        'tsothm_logo_width_mobile',
        array(
            'default'           => '180',
            'sanitize_callback' => 'tsothm_sanitize_logo_width_mobile',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_logo_width_mobile',
        array(
            'label'       => __( 'Ancho del logo en móvil (px)', 'tso-blog' ),
            'description' => __( 'Ancho máximo en pantallas ≤ 768px (40–400).', 'tso-blog' ),
            'section'     => 'tsothm_logo_section',
            'type'        => 'number',
            'priority'    => 20,
            'input_attrs' => array(
                'min'  => 40,
                'max'  => 400,
                'step' => 1,
            ),
        )
    );

    /* Diseño — ancho del sitio */
    $wp_customize->add_section(
        'tsothm_layout_section',
        array(
            'title'       => __( 'Diseño / Ancho', 'tso-blog' ),
            'description' => __( 'Ancho máximo del área blanca central. En móvil siempre ocupa casi toda la pantalla.', 'tso-blog' ),
            'panel'       => 'tsothm_appearance_panel',
            'priority'    => 20,
        )
    );
    $wp_customize->add_setting(
        'tsothm_layout_width',
        array(
            'default'           => '1060',
            'sanitize_callback' => 'tsothm_sanitize_layout_width',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_layout_width',
        array(
            'label'       => __( 'Ancho del contenido', 'tso-blog' ),
            'description' => __( 'Valores más altos aprovechan pantallas grandes. «Casi pantalla completa» usa todo el ancho disponible.', 'tso-blog' ),
            'section'     => 'tsothm_layout_section',
            'type'        => 'select',
            'choices'     => tsothm_get_layout_width_choices(),
        )
    );

    /* Footer — guía de uso (imágenes) */
    $wp_customize->add_section(
        'tsothm_footer_help_section',
        array(
            'title'       => __( 'Ayuda de columnas', 'tso-blog' ),
            'description' => __( 'Hay 3 zonas de widgets: Footer columna 1, 2 y 3 (Personalizar → Widgets).', 'tso-blog' ),
            'panel'       => 'tsothm_footer_panel',
            'priority'    => 10,
        )
    );
    $wp_customize->add_setting(
        'tsothm_footer_help_note',
        array(
            'default'           => 1,
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_footer_help_note',
        array(
            'label'       => __( 'He leído la guía (opcional)', 'tso-blog' ),
            'description' => __( 'Para que las fotos de las columnas 2 y 3 queden parejas: usa la misma medida en ambas. Recomendado 600×450 px (proporción 4:3) o 600×600 px (cuadrado). No mezcles una imagen vertical con una horizontal. Cada columna tiene unos 300 px de ancho en escritorio; 600 px de origen es suficiente. Si solo usas la columna 1 (menú de Contacto / Aviso legal) y dejas 2 y 3 vacías, los enlaces se muestran en horizontal. Nota: el aviso «tu tema tiene 1 área de widget más…» es de WordPress: la «Sidebar Noticias» no se muestra en portada del blog, Contacto ni Portfolio; en la vista previa abre una entrada o una página con plantilla por defecto para editarla. Para editar un widget desde la vista previa: Mayúsculas (Shift) + clic en el widget.', 'tso-blog' ),
            'section'     => 'tsothm_footer_help_section',
            'type'        => 'checkbox',
        )
    );

    /* Redes sociales */
    $wp_customize->add_section(
        'tsothm_social_section',
        array(
            'title'       => __( 'Redes sociales', 'tso-blog' ),
            'description' => __( 'Arrastra las redes con el icono ☰ para cambiar el orden (izquierda → derecha). Debajo, pega la URL completa de cada red. Las que no tengan URL no se muestran.', 'tso-blog' ),
            'panel'       => 'tsothm_appearance_panel',
            'priority'    => 30,
        )
    );

    $wp_customize->add_setting(
        'tsothm_social_order',
        array(
            'default'           => tsothm_get_default_social_order_string(),
            'sanitize_callback' => 'tsothm_sanitize_social_order_list',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        new Tsotheme_Customize_Social_Order_Control(
            $wp_customize,
            'tsothm_social_order',
            array(
                'label'       => __( 'Orden de los iconos', 'tso-blog' ),
                'description' => __( 'Arrastra arriba o abajo. Se guarda al publicar.', 'tso-blog' ),
                'section'     => 'tsothm_social_section',
                'priority'    => 5,
            )
        )
    );

    $tsothm_social_priority = 10;
    foreach ( tsothm_get_social_networks() as $key => $data ) {
        $wp_customize->add_setting(
            'tsothm_social_' . $key,
            array(
                'default'           => '',
                'sanitize_callback' => 'esc_url_raw',
                'transport'         => 'refresh',
            )
        );
        $wp_customize->add_control(
            'tsothm_social_' . $key,
            array(
                /* translators: %s: social network name */
                'label'    => sprintf( __( '%s (URL completa)', 'tso-blog' ), $data['label'] ),
                'section'  => 'tsothm_social_section',
                'type'     => 'url',
                'priority' => $tsothm_social_priority,
            )
        );
        $tsothm_social_priority += 10;
    }

    /* Colores — panel padre con secciones hijas */
    $wp_customize->add_panel(
        'tsothm_colors_panel',
        array(
            'title'       => __( 'Colores', 'tso-blog' ),
            'description' => __( 'Colores generales, menú de navegación y pie de página.', 'tso-blog' ),
            'priority'    => 30,
        )
    );

    $tsothm_color_defaults = tsothm_get_theme_color_defaults();

    $tsothm_color_groups = array(
        'tsothm_colors_general_section' => array(
            'title'       => __( 'Generales', 'tso-blog' ),
            'description' => __( 'Colores globales del tema (acentos, botones y fondo de la página).', 'tso-blog' ),
            'priority'    => 10,
            'controls'    => array(
                'tsothm_color_accent'  => __( 'Acento', 'tso-blog' ),
                'tsothm_color_primary' => __( 'Principal', 'tso-blog' ),
                'tsothm_color_body_bg' => __( 'Fondo exterior', 'tso-blog' ),
            ),
        ),
        'tsothm_colors_header_section'  => array(
            'title'       => __( 'Cabecera', 'tso-blog' ),
            'description' => __( 'Fondo de la cabecera: la franja superior con el logo y el buscador, por encima del menú de navegación.', 'tso-blog' ),
            'priority'    => 15,
            'controls'    => array(
                'tsothm_color_header_bg' => __( 'Fondo', 'tso-blog' ),
            ),
        ),
        'tsothm_colors_nav_section'     => array(
            'title'       => __( 'Menú', 'tso-blog' ),
            'description' => __( 'Barra de navegación principal, estilo de botones y submenús.', 'tso-blog' ),
            'priority'    => 20,
            'controls'    => array(
                'tsothm_color_nav_bg'      => __( 'Fondo', 'tso-blog' ),
                'tsothm_color_nav_text'    => __( 'Texto de los enlaces', 'tso-blog' ),
                'tsothm_color_nav_hover'   => __( 'Texto al pasar el ratón / activo', 'tso-blog' ),
                'tsothm_color_nav_submenu' => __( 'Fondo del submenú', 'tso-blog' ),
            ),
        ),
        'tsothm_colors_footer_section'  => array(
            'title'       => __( 'Footer', 'tso-blog' ),
            'description' => __( 'Pie de página: fondo, títulos de widgets, textos, copyright y líneas separadoras.', 'tso-blog' ),
            'priority'    => 30,
            'controls'    => array(
                'tsothm_color_footer_bg'      => __( 'Fondo', 'tso-blog' ),
                'tsothm_color_footer_heading' => __( 'Títulos de widgets', 'tso-blog' ),
                'tsothm_color_footer_text'    => __( 'Texto y enlaces', 'tso-blog' ),
                'tsothm_color_footer_muted'   => __( 'Copyright / texto secundario', 'tso-blog' ),
                'tsothm_color_footer_border'  => __( 'Líneas y bordes', 'tso-blog' ),
            ),
        ),
        'tsothm_colors_announce_section' => array(
            'title'       => __( 'Anuncio', 'tso-blog' ),
            'description' => __( 'Barra de novedades entre la cabecera y el menú. El texto se configura en «Anuncio / novedades».', 'tso-blog' ),
            'priority'    => 40,
            'controls'    => array(
                'tsothm_color_announce_bg'   => __( 'Fondo', 'tso-blog' ),
                'tsothm_color_announce_text' => __( 'Texto', 'tso-blog' ),
            ),
        ),
    );

    foreach ( $tsothm_color_groups as $section_id => $group ) {
        $wp_customize->add_section(
            $section_id,
            array(
                'title'       => $group['title'],
                'description' => $group['description'],
                'panel'       => 'tsothm_colors_panel',
                'priority'    => $group['priority'],
            )
        );

        foreach ( $group['controls'] as $setting_id => $label ) {
            $key = str_replace( 'tsothm_color_', '', $setting_id );
            $wp_customize->add_setting(
                $setting_id,
                array(
                    'default'           => isset( $tsothm_color_defaults[ $key ] ) ? $tsothm_color_defaults[ $key ] : '#000000',
                    'sanitize_callback' => 'sanitize_hex_color',
                    'transport'         => 'refresh',
                )
            );
            $wp_customize->add_control(
                new WP_Customize_Color_Control(
                    $wp_customize,
                    $setting_id,
                    array(
                        'label'   => $label,
                        'section' => $section_id,
                    )
                )
            );
        }
    }

    $wp_customize->add_setting(
        'tsothm_nav_button_style',
        array(
            'default'           => 'raised',
            'sanitize_callback' => 'tsothm_sanitize_nav_button_style',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_nav_button_style',
        array(
            'label'       => __( 'Estilo de botones del menú', 'tso-blog' ),
            'description' => __( 'Elige el relieve de los títulos del menú principal.', 'tso-blog' ),
            'section'     => 'tsothm_colors_nav_section',
            'type'        => 'select',
            'priority'    => 5,
            'choices'     => tsothm_get_nav_button_style_choices(),
        )
    );

    /* Modo noche: colores propios, independientes del modo día. */
    $tsothm_night_defaults = tsothm_get_night_color_defaults();
    $wp_customize->add_section(
        'tsothm_colors_night_section',
        array(
            'title'       => __( 'Modo noche', 'tso-blog' ),
            'description' => __( 'Colores del modo noche, independientes del modo día: los colores de las otras secciones solo se aplican de día. Si no cambias nada se usa la paleta oscura por defecto.', 'tso-blog' ),
            'panel'       => 'tsothm_colors_panel',
            'priority'    => 50,
        )
    );
    $tsothm_night_controls = array(
        'body_bg'   => array( __( 'Fondo exterior', 'tso-blog' ), '' ),
        'surface'   => array( __( 'Fondo del contenido', 'tso-blog' ), '' ),
        'header_bg' => array( __( 'Fondo de la cabecera', 'tso-blog' ), __( 'Vacío = automático: si la cabecera del modo día es clara, se usa el color del contenido.', 'tso-blog' ) ),
        'nav_bg'    => array( __( 'Fondo del menú', 'tso-blog' ), '' ),
        'footer_bg' => array( __( 'Fondo del pie de página', 'tso-blog' ), '' ),
        'accent'    => array( __( 'Acento', 'tso-blog' ), __( 'Barras de título, botones y enlaces destacados.', 'tso-blog' ) ),
        'link'      => array( __( 'Enlaces y barras azules', 'tso-blog' ), '' ),
        'primary'   => array( __( 'Color principal', 'tso-blog' ), '' ),
        'nav_text'  => array( __( 'Menú: texto de los enlaces', 'tso-blog' ), '' ),
        'nav_submenu'    => array( __( 'Menú: fondo del submenú', 'tso-blog' ), '' ),
        'footer_heading' => array( __( 'Pie: títulos de widgets', 'tso-blog' ), '' ),
        'footer_text'    => array( __( 'Pie: texto y enlaces', 'tso-blog' ), '' ),
        'footer_muted'   => array( __( 'Pie: copyright / texto secundario', 'tso-blog' ), '' ),
        'footer_border'  => array( __( 'Pie: líneas y bordes', 'tso-blog' ), '' ),
        'announce_bg'    => array( __( 'Anuncio: fondo', 'tso-blog' ), '' ),
        'announce_text'  => array( __( 'Anuncio: texto', 'tso-blog' ), '' ),
        'text'      => array( __( 'Texto del contenido', 'tso-blog' ), '' ),
        'heading'   => array( __( 'Títulos del contenido', 'tso-blog' ), '' ),
    );
    foreach ( $tsothm_night_controls as $night_key => $night_control ) {
        $night_setting = 'tsothm_night_' . $night_key;
        $wp_customize->add_setting(
            $night_setting,
            array(
                'default'           => $tsothm_night_defaults[ $night_key ],
                'sanitize_callback' => 'sanitize_hex_color',
                'transport'         => 'refresh',
            )
        );
        $wp_customize->add_control(
            new WP_Customize_Color_Control(
                $wp_customize,
                $night_setting,
                array(
                    'label'       => $night_control[0],
                    'description' => $night_control[1],
                    'section'     => 'tsothm_colors_night_section',
                )
            )
        );
    }

    /* Tipografía */
    $wp_customize->add_panel(
        'tsothm_typography_panel',
        array(
            'title'       => __( 'Tipografía', 'tso-blog' ),
            'description' => __( 'Fuentes del cuerpo y de los títulos. Las fuentes Google solo se cargan si las eliges.', 'tso-blog' ),
            'priority'    => 35,
        )
    );

    $tsothm_font_choices = array();
    foreach ( tsothm_get_font_catalog() as $key => $font ) {
        $tsothm_font_choices[ $key ] = $font['label'];
    }

    $wp_customize->add_section(
        'tsothm_typography_body_section',
        array(
            'title'       => __( 'Texto del cuerpo', 'tso-blog' ),
            'description' => __( 'Tipografía general del sitio (párrafos y contenido).', 'tso-blog' ),
            'panel'       => 'tsothm_typography_panel',
            'priority'    => 10,
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_body',
        array(
            'default'           => 'arial',
            'sanitize_callback' => 'tsothm_sanitize_font_key',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_body',
        array(
            'label'   => __( 'Familia tipográfica', 'tso-blog' ),
            'section' => 'tsothm_typography_body_section',
            'type'    => 'select',
            'choices' => $tsothm_font_choices,
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_body_size',
        array(
            'default'           => '15',
            'sanitize_callback' => 'tsothm_sanitize_font_size_px',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_body_size',
        array(
            'label'       => __( 'Tamaño (px)', 'tso-blog' ),
            'section'     => 'tsothm_typography_body_section',
            'type'        => 'number',
            'input_attrs' => array(
                'min'  => 12,
                'max'  => 24,
                'step' => 1,
            ),
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_body_line',
        array(
            'default'           => '1.6',
            'sanitize_callback' => 'tsothm_sanitize_line_height',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_body_line',
        array(
            'label'       => __( 'Interlineado', 'tso-blog' ),
            'section'     => 'tsothm_typography_body_section',
            'type'        => 'number',
            'input_attrs' => array(
                'min'  => 1,
                'max'  => 2.5,
                'step' => 0.05,
            ),
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_body_color',
        array(
            'default'           => '#333333',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'tsothm_font_body_color',
            array(
                'label'   => __( 'Color', 'tso-blog' ),
                'section' => 'tsothm_typography_body_section',
            )
        )
    );

    $wp_customize->add_section(
        'tsothm_typography_heading_section',
        array(
            'title'       => __( 'Títulos', 'tso-blog' ),
            'description' => __( 'Fuente de encabezados (H1–H6) y títulos de artículos.', 'tso-blog' ),
            'panel'       => 'tsothm_typography_panel',
            'priority'    => 20,
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_heading',
        array(
            'default'           => 'arial',
            'sanitize_callback' => 'tsothm_sanitize_font_key',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_heading',
        array(
            'label'   => __( 'Familia tipográfica', 'tso-blog' ),
            'section' => 'tsothm_typography_heading_section',
            'type'    => 'select',
            'choices' => $tsothm_font_choices,
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_heading_weight',
        array(
            'default'           => '700',
            'sanitize_callback' => 'tsothm_sanitize_font_weight',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_heading_weight',
        array(
            'label'   => __( 'Grosor', 'tso-blog' ),
            'section' => 'tsothm_typography_heading_section',
            'type'    => 'select',
            'choices' => array(
                '400' => '400',
                '500' => '500',
                '600' => '600',
                '700' => '700',
                '800' => '800',
            ),
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_heading_color',
        array(
            'default'           => '#111111',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'tsothm_font_heading_color',
            array(
                'label'   => __( 'Color', 'tso-blog' ),
                'section' => 'tsothm_typography_heading_section',
            )
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_h1_size',
        array(
            'default'           => '24',
            'sanitize_callback' => 'tsothm_sanitize_font_size_px',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_h1_size',
        array(
            'label'       => __( 'Tamaño H1 / título de artículo (px)', 'tso-blog' ),
            'description' => __( 'Afecta al título dentro del artículo o página (no a las tarjetas de la portada).', 'tso-blog' ),
            'section'     => 'tsothm_typography_heading_section',
            'type'        => 'number',
            'input_attrs' => array(
                'min'  => 18,
                'max'  => 48,
                'step' => 1,
            ),
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_h1_transform',
        array(
            'default'           => 'none',
            'sanitize_callback' => 'tsothm_sanitize_text_transform',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_h1_transform',
        array(
            'label'       => __( 'Transformación de títulos', 'tso-blog' ),
            'description' => __( 'Mayúsculas, etc. en H1 y en los títulos de las tarjetas.', 'tso-blog' ),
            'section'     => 'tsothm_typography_heading_section',
            'type'        => 'select',
            'choices'     => array(
                'none'       => __( 'Ninguna', 'tso-blog' ),
                'uppercase'  => __( 'Mayúsculas', 'tso-blog' ),
                'lowercase'  => __( 'Minúsculas', 'tso-blog' ),
                'capitalize' => __( 'Capitalizar', 'tso-blog' ),
            ),
        )
    );

    $wp_customize->add_section(
        'tsothm_typography_widget_section',
        array(
            'title'       => __( 'Sidebar / widgets', 'tso-blog' ),
            'description' => __( 'Tamaño de los títulos con barra azul y del texto de la columna lateral.', 'tso-blog' ),
            'panel'       => 'tsothm_typography_panel',
            'priority'    => 30,
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_widget_title_size',
        array(
            'default'           => '14',
            'sanitize_callback' => 'tsothm_sanitize_widget_font_size',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_widget_title_size',
        array(
            'label'       => __( 'Tamaño título de widget (px)', 'tso-blog' ),
            'description' => __( 'Barra azul del sidebar (Recent Posts, Archives…).', 'tso-blog' ),
            'section'     => 'tsothm_typography_widget_section',
            'type'        => 'number',
            'input_attrs' => array(
                'min'  => 12,
                'max'  => 22,
                'step' => 1,
            ),
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_widget_text_size',
        array(
            'default'           => '14',
            'sanitize_callback' => 'tsothm_sanitize_widget_font_size',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_widget_text_size',
        array(
            'label'       => __( 'Tamaño texto del sidebar (px)', 'tso-blog' ),
            'description' => __( 'Enlaces y listas dentro de los widgets.', 'tso-blog' ),
            'section'     => 'tsothm_typography_widget_section',
            'type'        => 'number',
            'input_attrs' => array(
                'min'  => 12,
                'max'  => 22,
                'step' => 1,
            ),
        )
    );

    /* Textos de interfaz (botones, etc.) */
    $wp_customize->add_section(
        'tsothm_texts_section',
        array(
            'title'       => __( 'Textos de la interfaz', 'tso-blog' ),
            'description' => __( 'Textos generales del tema (botones). El copyright y el texto legal están en Footer.', 'tso-blog' ),
            'panel'       => 'tsothm_appearance_panel',
            'priority'    => 42,
        )
    );
    $wp_customize->add_setting( 'tsothm_text_load_more', array(
        'default'           => 'Cargar más',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'tsothm_text_load_more', array(
        'label'   => __( 'Texto botón «Cargar más»', 'tso-blog' ),
        'section' => 'tsothm_texts_section',
        'type'    => 'text',
    ) );

    /* Footer — textos del pie */
    $wp_customize->add_section(
        'tsothm_footer_texts_section',
        array(
            'title'       => __( 'Textos del pie', 'tso-blog' ),
            'description' => __( 'Copyright y texto legal bajo las columnas de widgets.', 'tso-blog' ),
            'panel'       => 'tsothm_footer_panel',
            'priority'    => 20,
        )
    );
    $wp_customize->add_setting( 'tsothm_footer_copyright', array(
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'tsothm_footer_copyright', array(
        'label'   => __( 'Texto copyright del footer', 'tso-blog' ),
        'section' => 'tsothm_footer_texts_section',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'tsothm_footer_legal', array(
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'tsothm_footer_legal', array(
        'label'   => __( 'Texto legal del footer (HTML permitido)', 'tso-blog' ),
        'section' => 'tsothm_footer_texts_section',
        'type'    => 'textarea',
    ) );

    /* Contacto (plantilla de página) */
    $wp_customize->add_section(
        'tsothm_contact_section',
        array(
            'title'       => __( 'Contacto', 'tso-blog' ),
            'description' => __( 'Datos para la plantilla de página «Contacto». El mapa solo se muestra si pegas una URL de incrustación de Google Maps. El formulario se añade con el shortcode de un plugin (p. ej. Contact Form 7); el tema no envía correos por sí mismo.', 'tso-blog' ),
            'panel'       => 'tsothm_content_panel',
            'priority'    => 20,
        )
    );

    $wp_customize->add_setting(
        'tsothm_contact_subtitle',
        array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_contact_subtitle',
        array(
            'label'   => __( 'Subtítulo del hero', 'tso-blog' ),
            'section' => 'tsothm_contact_section',
            'type'    => 'text',
        )
    );

    $wp_customize->add_setting(
        'tsothm_contact_intro',
        array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_textarea_field',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_contact_intro',
        array(
            'label'   => __( 'Texto introductorio (columna izquierda)', 'tso-blog' ),
            'section' => 'tsothm_contact_section',
            'type'    => 'textarea',
        )
    );

    $wp_customize->add_setting(
        'tsothm_contact_address',
        array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_textarea_field',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_contact_address',
        array(
            'label'   => __( 'Dirección / oficina', 'tso-blog' ),
            'section' => 'tsothm_contact_section',
            'type'    => 'textarea',
        )
    );

    $wp_customize->add_setting(
        'tsothm_contact_email',
        array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_email',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_contact_email',
        array(
            'label'   => __( 'Email', 'tso-blog' ),
            'section' => 'tsothm_contact_section',
            'type'    => 'email',
        )
    );

    $wp_customize->add_setting(
        'tsothm_contact_email_alt',
        array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_email',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_contact_email_alt',
        array(
            'label'   => __( 'Email alternativo (opcional)', 'tso-blog' ),
            'section' => 'tsothm_contact_section',
            'type'    => 'email',
        )
    );

    $wp_customize->add_setting(
        'tsothm_contact_phone',
        array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_contact_phone',
        array(
            'label'   => __( 'Teléfono', 'tso-blog' ),
            'section' => 'tsothm_contact_section',
            'type'    => 'text',
        )
    );

    $wp_customize->add_setting(
        'tsothm_contact_phone_alt',
        array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_contact_phone_alt',
        array(
            'label'   => __( 'Teléfono / fax alternativo (opcional)', 'tso-blog' ),
            'section' => 'tsothm_contact_section',
            'type'    => 'text',
        )
    );

    $wp_customize->add_setting(
        'tsothm_contact_maps_embed',
        array(
            'default'           => '',
            'sanitize_callback' => 'tsothm_sanitize_google_maps_embed',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_contact_maps_embed',
        array(
            'label'       => __( 'Google Maps (URL de incrustación)', 'tso-blog' ),
            'description' => __( 'En Google Maps: Compartir → Insertar un mapa → copia la URL del atributo src (o pega el código de inserción completo). Solo se aceptan URLs de Google Maps embed. Vacío = sin mapa.', 'tso-blog' ),
            'section'     => 'tsothm_contact_section',
            'type'        => 'textarea',
        )
    );

    $wp_customize->add_setting(
        'tsothm_contact_form_shortcode',
        array(
            'default'           => '',
            'sanitize_callback' => 'tsothm_sanitize_contact_form_shortcode',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_contact_form_shortcode',
        array(
            'label'       => __( 'Shortcode del formulario', 'tso-blog' ),
            'description' => __( 'Ejemplo Contact Form 7: [contact-form-7 id="123"]. El tema no procesa ni envía el formulario.', 'tso-blog' ),
            'section'     => 'tsothm_contact_section',
            'type'        => 'text',
        )
    );

    $wp_customize->add_setting(
        'tsothm_contact_show_social',
        array(
            'default'           => 1,
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_contact_show_social',
        array(
            'label'   => __( 'Mostrar iconos de redes sociales en Contacto', 'tso-blog' ),
            'section' => 'tsothm_contact_section',
            'type'    => 'checkbox',
        )
    );

    /* Equipo (plantilla de página) */
    $wp_customize->add_section(
        'tsothm_team_section',
        array(
            'title'       => __( 'Equipo', 'tso-blog' ),
            'description' => __( 'Fichas de equipo para la plantilla de página «Equipo». Si lo dejas vacío, la página mostrará el contenido de bloques (patrón «Equipo (3 personas)»).', 'tso-blog' ),
            'panel'       => 'tsothm_content_panel',
            'priority'    => 22,
        )
    );
    $wp_customize->add_setting(
        'tsothm_team_members',
        array(
            'default'           => '[]',
            'sanitize_callback' => 'tsothm_sanitize_team_members',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        new Tsotheme_Customize_Repeater_Control(
            $wp_customize,
            'tsothm_team_members',
            array(
                'label'      => __( 'Personas', 'tso-blog' ),
                'section'    => 'tsothm_team_section',
                'fields'     => tsothm_get_team_fields(),
                'add_label'  => __( 'Añadir persona', 'tso-blog' ),
            )
        )
    );

    /* Precios (plantilla de página) */
    $wp_customize->add_section(
        'tsothm_pricing_section',
        array(
            'title'       => __( 'Precios', 'tso-blog' ),
            'description' => __( 'Planes para la plantilla de página «Precios». Si lo dejas vacío, la página mostrará el contenido de bloques (patrón «Precios (3 planes)»).', 'tso-blog' ),
            'panel'       => 'tsothm_content_panel',
            'priority'    => 24,
        )
    );
    $wp_customize->add_setting(
        'tsothm_pricing_plans',
        array(
            'default'           => '[]',
            'sanitize_callback' => 'tsothm_sanitize_pricing_plans',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        new Tsotheme_Customize_Repeater_Control(
            $wp_customize,
            'tsothm_pricing_plans',
            array(
                'label'      => __( 'Planes', 'tso-blog' ),
                'section'    => 'tsothm_pricing_section',
                'fields'     => tsothm_get_pricing_fields(),
                'add_label'  => __( 'Añadir plan', 'tso-blog' ),
            )
        )
    );

    /* Landing / Portada (plantilla de página) */
    $wp_customize->add_section(
        'tsothm_landing_section',
        array(
            'title'       => __( 'Landing / Portada', 'tso-blog' ),
            'description' => __( 'Cabecera y destacados para la plantilla de página «Landing / Portada». El título y la imagen de fondo del hero salen del título e imagen destacada de cada página; aquí solo se configuran el texto pequeño, los botones y los destacados.', 'tso-blog' ),
            'panel'       => 'tsothm_content_panel',
            'priority'    => 26,
        )
    );
    $wp_customize->add_setting(
        'tsothm_landing_eyebrow',
        array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_landing_eyebrow',
        array(
            'label'       => __( 'Texto pequeño sobre el título', 'tso-blog' ),
            'description' => __( 'Opcional. Ejemplo: «Novedad» o el nombre de una campaña.', 'tso-blog' ),
            'section'     => 'tsothm_landing_section',
            'type'        => 'text',
        )
    );
    $wp_customize->add_setting(
        'tsothm_landing_cta_text',
        array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_landing_cta_text',
        array(
            'label'   => __( 'Botón principal — texto', 'tso-blog' ),
            'section' => 'tsothm_landing_section',
            'type'    => 'text',
        )
    );
    $wp_customize->add_setting(
        'tsothm_landing_cta_url',
        array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_landing_cta_url',
        array(
            'label'   => __( 'Botón principal — URL', 'tso-blog' ),
            'section' => 'tsothm_landing_section',
            'type'    => 'url',
        )
    );
    $wp_customize->add_setting(
        'tsothm_landing_cta2_text',
        array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_landing_cta2_text',
        array(
            'label'   => __( 'Botón secundario — texto (opcional)', 'tso-blog' ),
            'section' => 'tsothm_landing_section',
            'type'    => 'text',
        )
    );
    $wp_customize->add_setting(
        'tsothm_landing_cta2_url',
        array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_landing_cta2_url',
        array(
            'label'   => __( 'Botón secundario — URL', 'tso-blog' ),
            'section' => 'tsothm_landing_section',
            'type'    => 'url',
        )
    );
    $wp_customize->add_setting(
        'tsothm_landing_highlights',
        array(
            'default'           => '[]',
            'sanitize_callback' => 'tsothm_sanitize_landing_highlights',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        new Tsotheme_Customize_Repeater_Control(
            $wp_customize,
            'tsothm_landing_highlights',
            array(
                'label'       => __( 'Destacados bajo el hero', 'tso-blog' ),
                'description' => __( 'Hasta 3 o 4 puntos breves (icono + título + texto) que aparecen debajo de la cabecera.', 'tso-blog' ),
                'section'     => 'tsothm_landing_section',
                'fields'      => tsothm_get_landing_highlight_fields(),
                'add_label'   => __( 'Añadir destacado', 'tso-blog' ),
            )
        )
    );

    /* Anuncio / novedades (hasta 5 + rotación) */
    $wp_customize->add_section(
        'tsothm_announcement_section',
        array(
            'title'       => __( 'Anuncio / novedades', 'tso-blog' ),
            'description' => __( 'Hasta 5 avisos entre la cabecera y el menú. Puedes mostrar solo uno o rotarlos automáticamente. Colores: Colores → Anuncio.', 'tso-blog' ),
            'panel'       => 'tsothm_content_panel',
            'priority'    => 30,
        )
    );
    $wp_customize->add_setting(
        'tsothm_announcement_enable',
        array(
            'default'           => 0,
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_announcement_enable',
        array(
            'label'   => __( 'Mostrar barra de anuncio', 'tso-blog' ),
            'section' => 'tsothm_announcement_section',
            'type'    => 'checkbox',
        )
    );
    $wp_customize->add_setting(
        'tsothm_announcement_mode',
        array(
            'default'           => 'rotate',
            'sanitize_callback' => 'tsothm_sanitize_announcement_mode',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_announcement_mode',
        array(
            'label'       => __( 'Modo de visualización', 'tso-blog' ),
            'description' => __( 'Rotación: cambia entre los anuncios con texto. Solo uno: muestra únicamente el número elegido.', 'tso-blog' ),
            'section'     => 'tsothm_announcement_section',
            'type'        => 'select',
            'choices'     => array(
                'rotate' => __( 'Rotación automática', 'tso-blog' ),
                'single' => __( 'Solo un anuncio', 'tso-blog' ),
            ),
        )
    );
    $wp_customize->add_setting(
        'tsothm_announcement_single',
        array(
            'default'           => 1,
            'sanitize_callback' => 'tsothm_sanitize_announcement_slot',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_announcement_single',
        array(
            'label'       => __( 'Anuncio a mostrar (modo «Solo uno»)', 'tso-blog' ),
            'section'     => 'tsothm_announcement_section',
            'type'        => 'select',
            'choices'     => array(
                '1' => __( 'Anuncio 1', 'tso-blog' ),
                '2' => __( 'Anuncio 2', 'tso-blog' ),
                '3' => __( 'Anuncio 3', 'tso-blog' ),
                '4' => __( 'Anuncio 4', 'tso-blog' ),
                '5' => __( 'Anuncio 5', 'tso-blog' ),
            ),
        )
    );
    $wp_customize->add_setting(
        'tsothm_announcement_interval',
        array(
            'default'           => 60,
            'sanitize_callback' => 'tsothm_sanitize_announcement_interval',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_announcement_interval',
        array(
            'label'       => __( 'Segundos entre anuncios (rotación)', 'tso-blog' ),
            'description' => __( 'Por defecto 60 (1 minuto). Cada cambio entra con el mismo desplazamiento hacia el centro.', 'tso-blog' ),
            'section'     => 'tsothm_announcement_section',
            'type'        => 'number',
            'input_attrs' => array(
                'min'  => 15,
                'max'  => 600,
                'step' => 5,
            ),
        )
    );

    for ( $i = 1; $i <= 5; $i++ ) {
        $wp_customize->add_setting(
            'tsothm_announcement_' . $i . '_text',
            array(
                'default'           => ( 1 === $i ) ? (string) get_theme_mod( 'tsothm_announcement_text', '' ) : '',
                'sanitize_callback' => 'sanitize_text_field',
                'transport'         => 'refresh',
            )
        );
        $wp_customize->add_control(
            'tsothm_announcement_' . $i . '_text',
            array(
                /* translators: %d: announcement slot number 1–5 */
                'label'   => sprintf( __( 'Anuncio %d — texto', 'tso-blog' ), $i ),
                'section' => 'tsothm_announcement_section',
                'type'    => 'text',
            )
        );
        $wp_customize->add_setting(
            'tsothm_announcement_' . $i . '_url',
            array(
                'default'           => ( 1 === $i ) ? (string) get_theme_mod( 'tsothm_announcement_url', '' ) : '',
                'sanitize_callback' => 'esc_url_raw',
                'transport'         => 'refresh',
            )
        );
        $wp_customize->add_control(
            'tsothm_announcement_' . $i . '_url',
            array(
                /* translators: %d: announcement slot number 1–5 */
                'label'   => sprintf( __( 'Anuncio %d — enlace (opcional)', 'tso-blog' ), $i ),
                'section' => 'tsothm_announcement_section',
                'type'    => 'url',
            )
        );
        $wp_customize->add_setting(
            'tsothm_announcement_' . $i . '_new_tab',
            array(
                'default'           => ( 1 === $i ) ? (int) get_theme_mod( 'tsothm_announcement_new_tab', 1 ) : 1,
                'sanitize_callback' => 'absint',
                'transport'         => 'refresh',
            )
        );
        $wp_customize->add_control(
            'tsothm_announcement_' . $i . '_new_tab',
            array(
                /* translators: %d: announcement slot number 1–5 */
                'label'   => sprintf( __( 'Anuncio %d — abrir enlace en pestaña nueva', 'tso-blog' ), $i ),
                'section' => 'tsothm_announcement_section',
                'type'    => 'checkbox',
            )
        );
    }

    /* Relacionados */
    $wp_customize->add_section(
        'tsothm_related_section',
        array(
            'title'    => __( 'Artículos relacionados', 'tso-blog' ),
            'panel'    => 'tsothm_appearance_panel',
            'priority' => 46,
        )
    );
    $wp_customize->add_setting( 'tsothm_related_count', array(
        'default'           => 3,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'tsothm_related_count', array(
        'label'       => 'Número de artículos relacionados (1–6)',
        'section'     => 'tsothm_related_section',
        'type'        => 'number',
        'input_attrs' => array( 'min' => 1, 'max' => 6, 'step' => 1 ),
    ) );
    $wp_customize->add_setting( 'tsothm_related_title', array(
        'default'           => 'Artículos relacionados',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'tsothm_related_title', array(
        'label'   => __( 'Título sección relacionados', 'tso-blog' ),
        'section' => 'tsothm_related_section',
        'type'    => 'text',
    ) );

    /* Integraciones opcionales (plugins de terceros) */
    $wp_customize->add_section(
        'tsothm_integrations_section',
        array(
            'title'       => __( 'Integraciones', 'tso-blog' ),
            'description' => __( 'Funciones opcionales que requieren un plugin. Desactivadas por defecto.', 'tso-blog' ),
            'priority'    => 50,
        )
    );
    $wp_customize->add_setting(
        'tsothm_enable_gtranslate',
        array(
            'default'           => 0,
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_enable_gtranslate',
        array(
            'label'       => __( 'Mostrar GTranslate en la cabecera', 'tso-blog' ),
            'description' => __( 'Requiere el plugin GTranslate activo. Si está desactivado o el plugin no está instalado, no se muestra nada.', 'tso-blog' ),
            'section'     => 'tsothm_integrations_section',
            'type'        => 'checkbox',
        )
    );

} );

/* ============================================================
   11. REDES SOCIALES — ICONOS CON ESTILOS INLINE (FOUC-proof)
   Los tamaños se fijan en style="" directamente en el HTML.
   Ningún plugin de caché puede diferir o mover atributos HTML.
   ============================================================ */
function tsothm_social_icons() {
    $items = tsothm_get_configured_social_icons();
    if ( empty( $items ) ) {
        return;
    }

    // Estilos inline en cada elemento: imposible diferir para LiteSpeed u otro plugin de caché
    $a_style   = 'display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background-color:var(--tso-color-primary);overflow:hidden;flex-shrink:0;text-decoration:none;';
    $svg_style = 'width:15px;height:15px;fill:#ffffff;display:block;flex-shrink:0;pointer-events:none;';

    echo '<div class="header-social-icons" aria-label="' . esc_attr__( 'Redes sociales', 'tso-blog' ) . '">';
    foreach ( $items as $key => $data ) {
        printf(
            '<a href="%s" class="social-icon social-icon--%s" style="%s" target="_blank" rel="noopener noreferrer" aria-label="%s">'
            . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="%s" aria-hidden="true" focusable="false">%s</svg>'
            . '</a>',
            esc_url( $data['url'] ),
            esc_attr( $key ),
            $a_style,
            esc_attr( $data['label'] ),
            $svg_style,
            $data['path']
        );
    }
    echo '</div>';
}

/* ============================================================
   12. LCP — LOGO CON FETCHPRIORITY HIGH
   ============================================================ */
add_filter( 'wp_get_attachment_image_attributes', function( $attr, $attachment ) {
    $logo_id = get_theme_mod( 'custom_logo' );
    if ( $logo_id && (int) $attachment->ID === (int) $logo_id ) {
        $attr['fetchpriority'] = 'high';
        $attr['loading']       = 'eager';
        $attr['decoding']      = 'sync';
        // Compatibilidad LiteSpeed lazy load
        if ( isset( $attr['data-src'] ) ) {
            $attr['src'] = $attr['data-src'];
            unset( $attr['data-src'] );
        }
        unset( $attr['data-lazyloaded'] );
        $attr['class'] = ( isset( $attr['class'] ) ? $attr['class'] . ' ' : '' ) . 'no-lazy';
    }
    return $attr;
}, 10, 2 );

/* ============================================================
   13. SEO — OPEN GRAPH + META DESCRIPTION
   Solo actúa si no hay plugin SEO activo
   ============================================================ */
add_action( 'wp_head', function() {
    if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEOP_VERSION' ) ) return;
    if ( ! is_singular( 'post' ) ) return;
    $title       = get_the_title();
    $description = wp_strip_all_tags( get_the_excerpt() );
    $url         = get_permalink();
    $site_name   = get_bloginfo( 'name' );
    $thumb       = has_post_thumbnail() ? wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' ) : false;
    echo '<meta property="og:type"        content="article" />' . "\n";
    echo '<meta property="og:title"       content="' . esc_attr( $title ) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr( $description ) . '" />' . "\n";
    echo '<meta property="og:url"         content="' . esc_url( $url ) . '" />' . "\n";
    echo '<meta property="og:site_name"   content="' . esc_attr( $site_name ) . '" />' . "\n";
    echo '<meta name="description"        content="' . esc_attr( $description ) . '" />' . "\n";
    if ( $thumb ) {
        echo '<meta property="og:image"        content="' . esc_url( $thumb[0] ) . '" />' . "\n";
    }
    echo '<meta name="twitter:card"        content="' . ( $thumb ? 'summary_large_image' : 'summary' ) . '" />' . "\n";
    echo '<meta name="twitter:title"       content="' . esc_attr( $title ) . '" />' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '" />' . "\n";
    if ( $thumb ) {
        echo '<meta name="twitter:image"       content="' . esc_url( $thumb[0] ) . '" />' . "\n";
    }
}, 5 );

/* ============================================================
   14. AJAX — BUSCADOR EN VIVO
   ============================================================ */
add_action( 'wp_ajax_tsothm_live_search',        'tsothm_live_search_callback' );
add_action( 'wp_ajax_nopriv_tsothm_live_search', 'tsothm_live_search_callback' );

function tsothm_live_search_callback() {
    check_ajax_referer( 'tsothm_live_search_nonce', 'nonce' );
    $term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
    if ( strlen( $term ) < 2 ) {
        wp_send_json_success( array() );
    }

    $query_args = array(
        's'              => $term,
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 8,
        'no_found_rows'  => true,
        'fields'         => 'ids',
    );
    if ( version_compare( get_bloginfo( 'version' ), '6.2', '>=' ) ) {
        $query_args['search_columns'] = array( 'post_title' );
    } else {
        $query_args['tsothm_title_only'] = true;
        add_filter( 'posts_search', 'tsothm_live_search_title_only', 10, 2 );
    }

    $query = new WP_Query( $query_args );

    if ( isset( $query_args['tsothm_title_only'] ) ) {
        remove_filter( 'posts_search', 'tsothm_live_search_title_only', 10 );
    }
    $results = array();
    foreach ( $query->posts as $post_id ) {
        $results[] = array(
            'title' => get_the_title( $post_id ),
            'url'   => get_permalink( $post_id ),
            'date'  => get_the_date( 'j M Y', $post_id ),
        );
    }
    wp_send_json_success( $results );
}

/**
 * Restrict live search to post titles on WordPress versions before search_columns.
 *
 * @param string   $search   Search SQL clause.
 * @param WP_Query $wp_query Query instance.
 * @return string
 */
function tsothm_live_search_title_only( $search, $wp_query ) {
    if ( ! $wp_query->get( 'tsothm_title_only' ) ) {
        return $search;
    }
    global $wpdb;
    $term = $wp_query->get( 's' );
    if ( '' === $term ) {
        return '';
    }
    $like = '%' . $wpdb->esc_like( $term ) . '%';
    return $wpdb->prepare( " AND ({$wpdb->posts}.post_title LIKE %s)", $like );
}

/* ============================================================
   15. HELPERS
   ============================================================ */
function tsothm_footer_copyright_text() {
    $custom = (string) get_theme_mod( 'tsothm_footer_copyright', '' );
    echo $custom ? wp_kses_post( $custom ) : '&copy; ' . esc_html( current_time( 'Y' ) ) . ' ' . esc_html( get_bloginfo( 'name' ) );
}

function tsothm_footer_legal_text() {
    $legal = (string) get_theme_mod( 'tsothm_footer_legal', '' );
    if ( '' !== $legal ) {
        echo '<div class="footer-legal">' . wp_kses_post( $legal ) . '</div>';
    }
}

function tsothm_load_more_text() {
    return sanitize_text_field( (string) get_theme_mod( 'tsothm_text_load_more', 'Cargar más' ) );
}

function tsothm_related_count() {
    return max( 1, min( 6, (int) get_theme_mod( 'tsothm_related_count', 3 ) ) );
}

function tsothm_related_title() {
    return esc_html( (string) get_theme_mod( 'tsothm_related_title', 'Artículos relacionados' ) );
}

/**
 * Whether the optional GTranslate header slot should render.
 * Off by default; requires Customizer opt-in and a registered shortcode.
 *
 * @return bool
 */
function tsothm_is_gtranslate_header_enabled() {
    if ( ! (int) get_theme_mod( 'tsothm_enable_gtranslate', 0 ) ) {
        return false;
    }
    return shortcode_exists( 'gtranslate' );
}

/**
 * Print the GTranslate header block when enabled and available.
 */
function tsothm_header_gtranslate() {
    if ( ! tsothm_is_gtranslate_header_enabled() ) {
        return;
    }
    echo '<div class="header-gtranslate">';
    echo do_shortcode( '[gtranslate]' );
    echo '</div>';
}

/**
 * Sanitize announcement display mode.
 *
 * @param mixed $value Raw value.
 * @return string rotate|single
 */
function tsothm_sanitize_announcement_mode( $value ) {
    $value = sanitize_key( (string) $value );
    return in_array( $value, array( 'rotate', 'single' ), true ) ? $value : 'rotate';
}

/**
 * Sanitize announcement slot index (1–5).
 *
 * @param mixed $value Raw value.
 * @return int
 */
function tsothm_sanitize_announcement_slot( $value ) {
    return max( 1, min( 5, absint( $value ) ) );
}

/**
 * Sanitize rotation interval in seconds.
 *
 * @param mixed $value Raw value.
 * @return int
 */
function tsothm_sanitize_announcement_interval( $value ) {
    return max( 15, min( 600, absint( $value ) ) );
}

/**
 * Configured announcements (non-empty text only).
 *
 * @return array<int, array{text: string, url: string, new_tab: bool}>
 */
function tsothm_get_announcements() {
    $items = array();

    for ( $i = 1; $i <= 5; $i++ ) {
        $text = trim( (string) get_theme_mod( 'tsothm_announcement_' . $i . '_text', '' ) );

        // Legacy single-announcement settings → slot 1.
        if ( '' === $text && 1 === $i ) {
            $text = trim( (string) get_theme_mod( 'tsothm_announcement_text', '' ) );
        }

        if ( '' === $text ) {
            continue;
        }

        $url = (string) get_theme_mod( 'tsothm_announcement_' . $i . '_url', '' );
        if ( '' === $url && 1 === $i ) {
            $url = (string) get_theme_mod( 'tsothm_announcement_url', '' );
        }

        $new_tab = (int) get_theme_mod( 'tsothm_announcement_' . $i . '_new_tab', 1 );
        if ( 1 === $i && '' === (string) get_theme_mod( 'tsothm_announcement_1_text', '' ) && '' !== (string) get_theme_mod( 'tsothm_announcement_text', '' ) ) {
            $new_tab = (int) get_theme_mod( 'tsothm_announcement_new_tab', 1 );
        }

        $items[] = array(
            'text'    => $text,
            'url'     => $url,
            'new_tab' => (bool) $new_tab,
            'slot'    => $i,
        );
    }

    return $items;
}

/**
 * Announcements to render according to mode (rotate vs single).
 *
 * @return array<int, array{text: string, url: string, new_tab: bool}>
 */
function tsothm_get_announcements_for_display() {
    $all = tsothm_get_announcements();
    if ( empty( $all ) ) {
        return array();
    }

    $mode = tsothm_sanitize_announcement_mode( get_theme_mod( 'tsothm_announcement_mode', 'rotate' ) );
    if ( 'single' !== $mode ) {
        return $all;
    }

    $slot = tsothm_sanitize_announcement_slot( get_theme_mod( 'tsothm_announcement_single', 1 ) );
    foreach ( $all as $item ) {
        if ( (int) $item['slot'] === $slot ) {
            return array( $item );
        }
    }

    // Chosen slot empty: fall back to first filled announcement.
    return array( $all[0] );
}

/**
 * Whether the header announcement bar should render.
 *
 * @return bool
 */
function tsothm_is_announcement_enabled() {
    if ( ! (int) get_theme_mod( 'tsothm_announcement_enable', 0 ) ) {
        return false;
    }
    return ! empty( tsothm_get_announcements_for_display() );
}

/**
 * Markup for one announcement slide.
 *
 * @param array $item Announcement data.
 * @param bool  $active Whether this slide is the first/active one.
 */
function tsothm_render_announcement_slide( $item, $active = false ) {
    $classes = 'tso-announcement-slide';
    if ( $active ) {
        $classes .= ' is-active';
    }

    echo '<div class="' . esc_attr( $classes ) . '" role="group">';
    if ( ! empty( $item['url'] ) ) {
        printf(
            '<a class="tso-announcement-link" href="%1$s"%2$s><span class="tso-announcement-text">%3$s</span></a>',
            esc_url( $item['url'] ),
            ! empty( $item['new_tab'] ) ? ' target="_blank" rel="noopener noreferrer"' : '',
            esc_html( $item['text'] )
        );
    } else {
        echo '<span class="tso-announcement-text">' . esc_html( $item['text'] ) . '</span>';
    }
    echo '</div>';
}

/**
 * Print the header announcement / news bar.
 */
function tsothm_header_announcement() {
    if ( ! tsothm_is_announcement_enabled() ) {
        return;
    }

    $items    = tsothm_get_announcements_for_display();
    $mode     = tsothm_sanitize_announcement_mode( get_theme_mod( 'tsothm_announcement_mode', 'rotate' ) );
    $interval = tsothm_sanitize_announcement_interval( get_theme_mod( 'tsothm_announcement_interval', 60 ) );
    $rotate   = ( 'rotate' === $mode && count( $items ) > 1 );

    printf(
        '<div class="tsothm-announcement" role="region" aria-label="%1$s" data-rotate="%2$s" data-interval="%3$d">',
        esc_attr__( 'Anuncio', 'tso-blog' ),
        $rotate ? '1' : '0',
        (int) $interval
    );
    echo '<div class="tso-announcement-viewport">';

    foreach ( $items as $index => $item ) {
        tsothm_render_announcement_slide( $item, 0 === $index );
    }

    echo '</div></div>';
}

/**
 * Body class when the announcement bar is visible.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function tsothm_announcement_body_class( $classes ) {
    if ( tsothm_is_announcement_enabled() ) {
        $classes[] = 'tso-has-announcement';
    }
    return $classes;
}
add_filter( 'body_class', 'tsothm_announcement_body_class' );

/**
 * Whether Jetpack plugin is active.
 *
 * @return bool
 */
function tsothm_is_jetpack_active() {
    return defined( 'JETPACK__VERSION' ) || class_exists( 'Jetpack' );
}

/* ============================================================
   16. DESACTIVAR Jetpack Carousel completament + fix galeries Gutenberg
   ============================================================
   El Carousel de Jetpack intercepta les galeries (incloent les de
   Gutenberg wp-block-gallery) i les "segrest": mostra la galeria
   0.5s i després la substitueix pel seu propi visor. Hem de
   desactivar-lo per tots els camins possibles per usar el
   lightbox propi (tso-lightbox.js).
   ============================================================ */

// 1. Desactivar els recursos CSS/JS del Carousel
if ( tsothm_is_jetpack_active() ) {
    add_filter( 'jetpack_carousel_enqueue_resources', '__return_false' );

    // 2. Desactivar el mòdul Carousel de Jetpack si està actiu
    add_filter( 'jetpack_active_modules', function( $modules ) {
        if ( is_admin() ) {
            return $modules;
        }
        return array_diff( (array) $modules, array( 'carousel' ) );
    } );

    // 3. Forçar opció carousel a desactivat (només al frontend)
    add_filter( 'pre_option_carousel_display_exif', function( $value ) {
        return is_admin() ? $value : 0;
    } );
    add_filter( 'pre_option_jetpack_carousel_display_exif', function( $value ) {
        return is_admin() ? $value : 0;
    } );

    // 4. Eliminar l'acció que Jetpack usa per renderitzar el carousel en galeries Gutenberg
    add_action( 'wp_enqueue_scripts', function() {
        wp_dequeue_script( 'jetpack-carousel' );
        wp_dequeue_style( 'jetpack-carousel' );
    }, 99 );
}

/* ============================================================
   19. GALERIES GUTENBERG — forçar links directes a imatge
   ============================================================
   Gutenberg per defecte genera <a href="pàgina-adjunt"> en
   comptes de <a href="url-imatge-directa">. Això impedeix que
   el lightbox (tso-lightbox.js) pugui obrir la imatge.

   IMPORTANT: NO afegim classes tiled-gallery ni modifiquem
   l'estructura del bloc — això causava que Jetpack processés
   el DOM i eliminés les imatges (efecte "desapareix").

   Solució: només canviem l'href dels links d'adjunt per la
   URL de la imatge original, sense tocar res més.
   ============================================================ */
add_filter( 'render_block', function( $html, $block ) {
    $block_name = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';
    if ( 'core/gallery' !== $block_name ) {
        return $html;
    }

    $html = (string) $html;
    if ( '' === $html ) {
        return $html;
    }

    $result = preg_replace_callback(
        '/<a(\s[^>]*)href=["\']([^"\']*)["\']([^>]*)>\s*(<img\s[^>]*\bsrc=["\'])([^"\']+)(["\'][^>]*\/?>)\s*<\/a>/iU',
        function( $m ) {
            $href = isset( $m[2] ) ? (string) $m[2] : '';
            $img  = isset( $m[4] ) ? (string) $m[4] . ( isset( $m[5] ) ? $m[5] : '' ) . ( isset( $m[6] ) ? $m[6] : '' ) : '';
            $src  = isset( $m[5] ) ? (string) $m[5] : '';

            // Already points to a media file — keep.
            if ( preg_match( '/\.(jpe?g|png|gif|webp|avif|svg)(\?[^"\']*)?$/i', $href ) ) {
                return $m[0];
            }

            // Prefer full-size attachment URL from data-id / class wp-image-N.
            $attachment_id = 0;
            if ( preg_match( '/\bdata-id=["\'](\d+)["\']/', $img, $id_m ) ) {
                $attachment_id = absint( $id_m[1] );
            } elseif ( preg_match( '/\bwp-image-(\d+)\b/', $img, $id_m ) ) {
                $attachment_id = absint( $id_m[1] );
            }
            $full = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'full' ) : '';
            $new_href = $full ? $full : $src;
            if ( '' === $new_href ) {
                return $m[0];
            }

            return '<a' . $m[1] . 'href="' . esc_url( $new_href ) . '"' . $m[3] . '>' . $m[4] . $src . $m[6] . '</a>';
        },
        $html
    );

    return ( null !== $result ) ? $result : $html;
}, 10, 2 );

/* ============================================================
   20. BLOC IMATGE — sin wrapper forzado
   ============================================================
   Antes se envolvía toda imagen sin enlace en un <a> para el
   lightbox. Eso rompía linkDestination:none y el lightbox nativo
   de WP 6.4+. tso-lightbox.js abre <img> sueltas con
   bindStandaloneImages; no hace falta modificar el HTML del bloque.
   ============================================================ */

/* ============================================================
   24. JETPACK TILED GALLERY — afegir links per al lightbox
   ============================================================
   jetpack/tiled-gallery genera <figure><img data-url="...">
   sense cap <a> — el lightbox no pot interceptar res.
   Afegim un <a href="data-url"> al voltant de cada <img>.
   ============================================================ */
add_filter( 'render_block', function( $html, $block ) {
    if ( ! tsothm_is_jetpack_active() ) {
        return $html;
    }
    $block_name = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';
    if ( 'jetpack/tiled-gallery' !== $block_name ) {
        return $html;
    }

    $html = (string) $html;
    if ( '' === $html ) {
        return $html;
    }

    $result = preg_replace_callback(
        '/(<figure[^>]*\bclass=["\'][^"\']*\btiled-gallery__item\b[^"\']*["\'][^>]*>)\s*(<img\s[^>]*>)\s*(<\/figure>)/iU',
        function( $m ) {
            $img_tag = isset( $m[2] ) ? (string) $m[2] : '';

            $url = '';
            if ( preg_match( '/\bdata-url=["\']([^"\']+)["\']/', $img_tag, $u ) ) {
                $url = $u[1];
            } elseif ( preg_match( '/\bdata-orig-file=["\']([^"\']+)["\']/', $img_tag, $u ) ) {
                $url = $u[1];
            } elseif ( preg_match( '/\bsrc=["\']([^"\']+)["\']/', $img_tag, $u ) ) {
                $url = $u[1];
            }

            if ( '' === $url ) {
                return $m[0];
            }

            $alt = '';
            if ( preg_match( '/\balt=["\']([^"\']*)["\']/', $img_tag, $a ) ) {
                $alt = esc_attr( (string) $a[1] );
            }

            return $m[1]
                . '<a href="' . esc_url( $url ) . '" aria-label="' . $alt . '">'
                . $img_tag
                . '</a>'
                . $m[3];
        },
        $html
    );

    return ( null !== $result ) ? $result : $html;
}, 10, 2 );

/* ============================================================
   23. FIX bug transformació jetpack/tiled-gallery a Gutenberg
   ============================================================
   Carrega un JS fix que intercepta les transformacions del bloc
   i evita el TypeError quan attributes.images és undefined.
   ============================================================ */
add_action( 'enqueue_block_editor_assets', function() {
    if ( ! tsothm_is_jetpack_active() ) {
        return;
    }
    $js = get_stylesheet_directory() . '/tso-tiled-gallery-fix.js';
    if ( ! file_exists( $js ) ) {
        return;
    }
    wp_enqueue_script(
        'tsothm-tiled-gallery-fix',
        get_stylesheet_directory_uri() . '/tso-tiled-gallery-fix.js',
        array( 'wp-blocks', 'wp-dom-ready', 'wp-edit-post' ),
        filemtime( $js ),
        true
    );
} );

/* ============================================================
   25. BLOCK STYLES — estils addicionals per a blocs de Gutenberg
   ============================================================
   Afegeix variants visuals als blocs principals del tema.
   L'usuari pot triar l'estil des del panell lateral de l'editor.
   ============================================================ */
add_action( 'init', function() {

    // Botó — variant destacat amb color d'acent del tema
    register_block_style( 'core/button', array(
        'name'  => 'tso-accent',
        'label' => __( 'Acento TSO', 'tso-blog' ),
    ) );

    // Cita — variant de barra lateral (blockquote amb línia esquerra)
    register_block_style( 'core/quote', array(
        'name'  => 'tso-sidebar-quote',
        'label' => __( 'Barra lateral', 'tso-blog' ),
    ) );

    // Separador — variant d'ombra curta
    register_block_style( 'core/separator', array(
        'name'  => 'tso-short',
        'label' => __( 'Curt centrat', 'tso-blog' ),
    ) );

    // Imatge — variant amb ombra suau
    register_block_style( 'core/image', array(
        'name'  => 'tso-shadow',
        'label' => __( 'Amb ombra', 'tso-blog' ),
    ) );

} );

/* ============================================================
   26. BLOCK PATTERNS — patrons de blocs reutilitzables
   ============================================================
   Patrons predefinits que l'usuari pot inserir des de l'editor
   Gutenberg (/ → Patrons).
   ============================================================ */
add_action( 'init', function() {

    // Registrar la categoria de patrons del tema
    register_block_pattern_category( 'tso-blog', array(
        'label' => __( 'Tu Soporte Online', 'tso-blog' ),
    ) );

    // Patró: crida a l'acció (CTA) centrada
    register_block_pattern( 'tso-blog/cta-centered', array(
        'title'       => __( 'CTA centrada', 'tso-blog' ),
        'description' => __( 'Bloc de crida a l\'acció amb títol, text i botó centrats.', 'tso-blog' ),
        'categories'  => array( 'tso-blog', 'call-to-action' ),
        'content'     => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"40px","bottom":"40px"}}},"backgroundColor":"primary","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-primary-background-color has-background" style="padding-top:40px;padding-bottom:40px">
<!-- wp:heading {"textAlign":"center","level":2} -->
<h2 class="wp-block-heading has-text-align-center">' . esc_html__( '¿Necesitas ayuda?', 'tso-blog' ) . '</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Estamos aquí para ayudarte. Contacta con nosotros y te responderemos en menos de 24 horas.', 'tso-blog' ) . '</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button">' . esc_html__( 'Contactar ahora', 'tso-blog' ) . '</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->',
    ) );

    // Patró: dues columnes d'informació
    register_block_pattern( 'tso-blog/two-columns-info', array(
        'title'       => __( 'Dues columnes informació', 'tso-blog' ),
        'description' => __( 'Dues columnes amb títol i text per comparar serveis o característiques.', 'tso-blog' ),
        'categories'  => array( 'tso-blog', 'columns' ),
        'content'     => '<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Característica 1', 'tso-blog' ) . '</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>' . esc_html__( 'Descripción de la primera característica o servicio destacado.', 'tso-blog' ) . '</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Característica 2', 'tso-blog' ) . '</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>' . esc_html__( 'Descripción de la segunda característica o servicio destacado.', 'tso-blog' ) . '</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->',
    ) );

} );

/* ============================================================
   27. PLANTILLES NOVES — CLASSE DE BODY GENÈRICA
   ============================================================
   Afegeix automàticament body.tso-tpl-{slug} per a qualsevol
   plantilla de /page-templates/, sense haver de crear un filtre
   dedicat per cada una.
   ============================================================ */
add_filter( 'body_class', function( $classes ) {
	if ( ! is_page() ) {
		return $classes;
	}
	$tpl = get_page_template_slug();
	if ( $tpl && 0 === strpos( $tpl, 'page-templates/' ) ) {
		$slug       = str_replace( array( 'page-templates/', '.php' ), '', $tpl );
		$classes[]  = 'tso-tpl-' . sanitize_html_class( $slug );
	}
	return $classes;
} );

/* ============================================================
   28. PLANTILLA "PREGUNTAS FRECUENTES" — ACORDIÓN ACCESIBLE
   ============================================================
   Cada bloque Encabezado (H3) del contenido inicia una pregunta;
   los bloques siguientes, hasta el próximo H3, son la respuesta.
   Se renderiza con <details>/<summary> nativo (sin JS).
   ============================================================ */

/**
 * Render page content as an accessible FAQ accordion.
 *
 * @param int $post_id Page ID.
 */
function tsothm_render_faq_accordion( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post || ! has_blocks( $post->post_content ) ) {
		the_content();
		return;
	}

	$blocks  = parse_blocks( $post->post_content );
	$items   = array();
	$current = null;

	foreach ( $blocks as $block ) {
		$is_empty = empty( $block['blockName'] ) && '' === trim( wp_strip_all_tags( isset( $block['innerHTML'] ) ? $block['innerHTML'] : '' ) );
		if ( $is_empty ) {
			continue;
		}

		if ( 'core/heading' === $block['blockName'] ) {
			if ( null !== $current ) {
				$items[] = $current;
			}
			$current = array(
				'question' => trim( wp_strip_all_tags( render_block( $block ) ) ),
				'answer'   => array(),
			);
			continue;
		}

		if ( null === $current ) {
			echo render_block( $block ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_block() ya sanea vía core.
			continue;
		}

		$current['answer'][] = $block;
	}
	if ( null !== $current ) {
		$items[] = $current;
	}

	if ( empty( $items ) ) {
		if ( current_user_can( 'edit_post', $post_id ) ) {
			echo '<p class="tso-faq-empty">' . esc_html__( 'Añade un bloque Encabezado (H3) por cada pregunta y el texto de la respuesta debajo. Publica la página para ver el acordeón.', 'tso-blog' ) . '</p>';
		}
		return;
	}

	echo '<div class="tso-faq-list">';
	foreach ( $items as $item ) {
		$answer_html = trim( implode( '', array_map( 'render_block', $item['answer'] ) ) );
		echo '<details class="tso-faq-item">';
		echo '<summary class="tso-faq-question">' . esc_html( $item['question'] ) . '</summary>';
		echo '<div class="tso-faq-answer">' . $answer_html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_block() ya sanea vía core.
		echo '</details>';
	}
	echo '</div>';
}

/* ============================================================
   29. PLANTILLA "RECURSOS / DESCARGAS"
   ============================================================
   Lee los bloques Archivo (core/file) del contenido y los
   muestra como una lista de descargas con icono, nombre y peso.
   ============================================================ */

/**
 * Recursively collect core/file block data from parsed blocks.
 *
 * @param array $blocks Parsed blocks.
 * @return array<int, array{id:int, href:string, name:string}>
 */
function tsothm_extract_file_blocks( $blocks ) {
	$files = array();

	foreach ( (array) $blocks as $block ) {
		if ( empty( $block['blockName'] ) ) {
			if ( ! empty( $block['innerBlocks'] ) ) {
				$files = array_merge( $files, tsothm_extract_file_blocks( $block['innerBlocks'] ) );
			}
			continue;
		}

		if ( 'core/file' === $block['blockName'] ) {
			$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
			$href  = isset( $attrs['href'] ) ? esc_url_raw( $attrs['href'] ) : '';
			if ( $href ) {
				$files[] = array(
					'id'   => isset( $attrs['id'] ) ? absint( $attrs['id'] ) : 0,
					'href' => $href,
					'name' => isset( $attrs['fileName'] ) ? sanitize_text_field( $attrs['fileName'] ) : '',
				);
			}
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			$files = array_merge( $files, tsothm_extract_file_blocks( $block['innerBlocks'] ) );
		}
	}

	return $files;
}

/**
 * File entries (core/file blocks) for a Resources page.
 *
 * @param int $post_id Page ID.
 * @return array
 */
function tsothm_get_resource_files( $post_id ) {
	$post_id = absint( $post_id );
	$post    = get_post( $post_id );
	if ( ! $post || ! has_blocks( $post->post_content ) ) {
		return array();
	}
	$files = tsothm_extract_file_blocks( parse_blocks( $post->post_content ) );

	/**
	 * Filter resource files list.
	 *
	 * @param array $files   File entries.
	 * @param int   $post_id Page ID.
	 */
	return apply_filters( 'tsothm_resource_files', $files, $post_id );
}

/**
 * File extension label for a resource entry.
 *
 * @param array $file File entry with 'href'.
 * @return string
 */
function tsothm_get_resource_file_ext( $file ) {
	$path = wp_parse_url( isset( $file['href'] ) ? $file['href'] : '', PHP_URL_PATH );
	$ext  = $path ? strtoupper( pathinfo( $path, PATHINFO_EXTENSION ) ) : '';
	return $ext ? $ext : __( 'Archivo', 'tso-blog' );
}

/**
 * Human-readable file size for a resource entry (solo adjuntos locales).
 *
 * @param array $file File entry with 'id'.
 * @return string
 */
function tsothm_get_resource_file_size( $file ) {
	if ( empty( $file['id'] ) ) {
		return '';
	}
	$path = get_attached_file( (int) $file['id'] );
	if ( ! $path || ! file_exists( $path ) ) {
		return '';
	}
	$bytes = filesize( $path );
	return $bytes ? size_format( $bytes ) : '';
}

/**
 * Render the resources/downloads list.
 *
 * @param int $post_id Page ID.
 */
function tsothm_render_resource_list( $post_id ) {
	$files = tsothm_get_resource_files( $post_id );

	if ( empty( $files ) ) {
		if ( current_user_can( 'edit_post', $post_id ) ) {
			echo '<p class="tso-resources-empty">' . esc_html__( 'Añade uno o más bloques Archivo en el editor y publica la página para que aparezcan aquí.', 'tso-blog' ) . '</p>';
		}
		return;
	}

	echo '<ul class="tso-resources-list">';
	foreach ( $files as $file ) {
		$ext   = tsothm_get_resource_file_ext( $file );
		$size  = tsothm_get_resource_file_size( $file );
		$label = $file['name'] ? $file['name'] : ( $file['id'] ? get_the_title( $file['id'] ) : '' );
		if ( '' === trim( (string) $label ) ) {
			$label = $ext;
		}

		echo '<li class="tso-resource-item">';
		echo '<a class="tso-resource-link" href="' . esc_url( $file['href'] ) . '" download>';
		echo '<span class="tso-resource-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" focusable="false"><path fill="currentColor" d="M6 2c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6H6zm7 7V3.5L18.5 9H13z"/></svg></span>';
		echo '<span class="tso-resource-info">';
		echo '<span class="tso-resource-name">' . esc_html( $label ) . '</span>';
		echo '<span class="tso-resource-meta">' . esc_html( $ext ) . ( $size ? ' · ' . esc_html( $size ) : '' ) . '</span>';
		echo '</span>';
		echo '</a>';
		echo '</li>';
	}
	echo '</ul>';
}

/* ============================================================
   30. PLANTILLA "LEGAL / DOCUMENTO LARGO" — ÍNDICE (TOC)
   ============================================================
   Añade id="" a cada H2/H3 del contenido ya renderizado y
   construye el índice lateral a partir de esos mismos títulos.
   ============================================================ */

/**
 * Add id attributes to H2/H3 headings in rendered HTML and build a matching TOC.
 *
 * @param string $html Rendered content HTML (tras aplicar los filtros de 'the_content').
 * @return array{content: string, toc: array}
 */
function tsothm_add_heading_ids_and_toc( $html ) {
	$toc        = array();
	$used_slugs = array();

	$html = preg_replace_callback(
		'/<h([23])\b([^>]*)>(.*?)<\/h\1>/is',
		function( $m ) use ( &$toc, &$used_slugs ) {
			$level = (int) $m[1];
			$attrs = (string) $m[2];
			$inner = (string) $m[3];
			$text  = trim( wp_strip_all_tags( $inner ) );

			if ( preg_match( '/\bid=["\']([^"\']+)["\']/', $attrs, $id_m ) ) {
				$slug = $id_m[1];
			} else {
				$base = $text ? sanitize_title( $text ) : 'section';
				$slug = $base;
				$i    = 2;
				while ( isset( $used_slugs[ $slug ] ) ) {
					$slug = $base . '-' . $i;
					++$i;
				}
				$attrs .= ' id="' . esc_attr( $slug ) . '"';
			}
			$used_slugs[ $slug ] = true;

			if ( $text ) {
				$toc[] = array(
					'level' => $level,
					'slug'  => $slug,
					'text'  => $text,
				);
			}

			return '<h' . $level . $attrs . '>' . $inner . '</h' . $level . '>';
		},
		$html
	);

	return array(
		'content' => (string) $html,
		'toc'     => $toc,
	);
}

/**
 * Render the TOC nav for the Legal template.
 *
 * @param array $toc Entries from tsothm_add_heading_ids_and_toc().
 */
function tsothm_render_legal_toc( $toc ) {
	if ( empty( $toc ) ) {
		return;
	}
	echo '<nav class="tso-legal-toc" aria-label="' . esc_attr__( 'Índice del documento', 'tso-blog' ) . '">';
	echo '<strong class="tso-legal-toc-title">' . esc_html__( 'Índice', 'tso-blog' ) . '</strong>';
	echo '<ul>';
	foreach ( $toc as $item ) {
		echo '<li class="tso-legal-toc-item tso-legal-toc-level-' . (int) $item['level'] . '">';
		echo '<a href="#' . esc_attr( $item['slug'] ) . '">' . esc_html( $item['text'] ) . '</a>';
		echo '</li>';
	}
	echo '</ul>';
	echo '</nav>';
}

/* ============================================================
   31. PLANTILLA "SOBRE MÍ" — TARJETA DE CONTACTO OPCIONAL
   ============================================================
   Reutiliza los mismos ajustes del Customizer que la plantilla
   Contacto (email, teléfono, shortcode de formulario, redes).
   ============================================================ */

/**
 * Render an optional "get in touch" card for the About Me template.
 *
 * @param int $post_id Page ID (para el aviso de edición).
 */
function tsothm_render_about_me_contact_card( $post_id ) {
	$contact    = tsothm_get_contact_settings();
	$has_direct = ( $contact['email'] || $contact['phone'] );

	if ( ! $contact['form_shortcode'] && ! $has_direct ) {
		if ( current_user_can( 'edit_post', $post_id ) && current_user_can( 'edit_theme_options' ) ) {
			echo '<p class="tso-about-contact-hint">' . esc_html__( 'Configura un email, teléfono o el shortcode de un formulario en Apariencia → Personalizar → Contacto para mostrar aquí una forma de contacto.', 'tso-blog' ) . '</p>';
		}
		return;
	}

	echo '<div class="tso-about-contact-card">';
	echo '<h2 class="tso-about-contact-title">' . esc_html__( 'Ponte en contacto', 'tso-blog' ) . '</h2>';

	if ( $has_direct ) {
		echo '<p class="tso-about-contact-direct">';
		if ( $contact['email'] ) {
			echo '<a href="' . esc_url( 'mailto:' . $contact['email'] ) . '">' . esc_html( $contact['email'] ) . '</a>';
		}
		if ( $contact['email'] && $contact['phone'] ) {
			echo ' &middot; ';
		}
		if ( $contact['phone'] ) {
			echo '<a href="' . esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $contact['phone'] ) ) . '">' . esc_html( $contact['phone'] ) . '</a>';
		}
		echo '</p>';
	}

	if ( $contact['form_shortcode'] ) {
		echo '<div class="tso-about-contact-form">' . do_shortcode( $contact['form_shortcode'] ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- do_shortcode() output, shortcode ya validado en tsothm_sanitize_contact_form_shortcode().
	}

	if ( ! empty( $contact['show_social'] ) ) {
		tsothm_social_icons();
	}

	echo '</div>';
}

/* ============================================================
   32. BLOCK PATTERNS — PLANTILLAS NUEVAS
   ============================================================
   Patrones de arranque para las plantillas de Equipo, Servicios,
   Precios, Testimonios, Cronología y Enlaces.
   ============================================================ */
add_action( 'init', function() {

	register_block_pattern( 'tso-blog/team-grid', array(
		'title'       => __( 'Equipo (3 personas)', 'tso-blog' ),
		'description' => __( 'Cuadrícula de 3 fichas de equipo con foto, nombre y cargo.', 'tso-blog' ),
		'categories'  => array( 'tso-blog', 'team' ),
		'content'     => '<!-- wp:columns {"className":"tso-team-grid"} -->
<div class="wp-block-columns tso-team-grid">
<!-- wp:column {"className":"tso-team-member"} -->
<div class="wp-block-column tso-team-member">
<!-- wp:image {"align":"center","width":140,"height":140,"scale":"cover","style":{"border":{"radius":"999px"}}} -->
<figure class="wp-block-image aligncenter is-resized has-custom-border"><img style="border-radius:999px;object-fit:cover;width:140px;height:140px" alt=""/></figure>
<!-- /wp:image -->
<!-- wp:heading {"textAlign":"center","level":3} -->
<h3 class="wp-block-heading has-text-align-center">' . esc_html__( 'Nombre Apellido', 'tso-blog' ) . '</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Cargo o rol', 'tso-blog' ) . '</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column {"className":"tso-team-member"} -->
<div class="wp-block-column tso-team-member">
<!-- wp:image {"align":"center","width":140,"height":140,"scale":"cover","style":{"border":{"radius":"999px"}}} -->
<figure class="wp-block-image aligncenter is-resized has-custom-border"><img style="border-radius:999px;object-fit:cover;width:140px;height:140px" alt=""/></figure>
<!-- /wp:image -->
<!-- wp:heading {"textAlign":"center","level":3} -->
<h3 class="wp-block-heading has-text-align-center">' . esc_html__( 'Nombre Apellido', 'tso-blog' ) . '</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Cargo o rol', 'tso-blog' ) . '</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column {"className":"tso-team-member"} -->
<div class="wp-block-column tso-team-member">
<!-- wp:image {"align":"center","width":140,"height":140,"scale":"cover","style":{"border":{"radius":"999px"}}} -->
<figure class="wp-block-image aligncenter is-resized has-custom-border"><img style="border-radius:999px;object-fit:cover;width:140px;height:140px" alt=""/></figure>
<!-- /wp:image -->
<!-- wp:heading {"textAlign":"center","level":3} -->
<h3 class="wp-block-heading has-text-align-center">' . esc_html__( 'Nombre Apellido', 'tso-blog' ) . '</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Cargo o rol', 'tso-blog' ) . '</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->',
	) );

} );

add_action( 'init', function() {

	register_block_pattern( 'tso-blog/service-grid', array(
		'title'       => __( 'Servicios (3 tarjetas)', 'tso-blog' ),
		'description' => __( 'Cuadrícula de 3 tarjetas de servicio con título y texto.', 'tso-blog' ),
		'categories'  => array( 'tso-blog', 'columns' ),
		'content'     => '<!-- wp:columns {"className":"tso-service-grid"} -->
<div class="wp-block-columns tso-service-grid">
<!-- wp:column {"className":"tso-service-card"} -->
<div class="wp-block-column tso-service-card">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Servicio 1', 'tso-blog' ) . '</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>' . esc_html__( 'Breve descripción de este servicio y de a quién va dirigido.', 'tso-blog' ) . '</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column {"className":"tso-service-card"} -->
<div class="wp-block-column tso-service-card">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Servicio 2', 'tso-blog' ) . '</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>' . esc_html__( 'Breve descripción de este servicio y de a quién va dirigido.', 'tso-blog' ) . '</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column {"className":"tso-service-card"} -->
<div class="wp-block-column tso-service-card">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Servicio 3', 'tso-blog' ) . '</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>' . esc_html__( 'Breve descripción de este servicio y de a quién va dirigido.', 'tso-blog' ) . '</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->',
	) );

} );

add_action( 'init', function() {

	register_block_pattern( 'tso-blog/pricing-table', array(
		'title'       => __( 'Precios (3 planes)', 'tso-blog' ),
		'description' => __( 'Tabla comparativa de 3 planes o tarifas.', 'tso-blog' ),
		'categories'  => array( 'tso-blog', 'columns' ),
		'content'     => '<!-- wp:columns {"className":"tso-pricing-grid"} -->
<div class="wp-block-columns tso-pricing-grid">
<!-- wp:column {"className":"tso-pricing-card"} -->
<div class="wp-block-column tso-pricing-card">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Plan básico', 'tso-blog' ) . '</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"tso-pricing-price"} -->
<p class="tso-pricing-price">9,99 €<span>' . esc_html__( '/mes', 'tso-blog' ) . '</span></p>
<!-- /wp:paragraph -->
<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item --><li>' . esc_html__( 'Característica incluida', 'tso-blog' ) . '</li><!-- /wp:list-item --><!-- wp:list-item --><li>' . esc_html__( 'Característica incluida', 'tso-blog' ) . '</li><!-- /wp:list-item --></ul>
<!-- /wp:list -->
<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button">' . esc_html__( 'Elegir plan', 'tso-blog' ) . '</a></div><!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->
<!-- wp:column {"className":"tso-pricing-card tso-pricing-card--featured"} -->
<div class="wp-block-column tso-pricing-card tso-pricing-card--featured">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Plan recomendado', 'tso-blog' ) . '</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"tso-pricing-price"} -->
<p class="tso-pricing-price">19,99 €<span>' . esc_html__( '/mes', 'tso-blog' ) . '</span></p>
<!-- /wp:paragraph -->
<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item --><li>' . esc_html__( 'Todo lo del plan básico', 'tso-blog' ) . '</li><!-- /wp:list-item --><!-- wp:list-item --><li>' . esc_html__( 'Característica adicional', 'tso-blog' ) . '</li><!-- /wp:list-item --></ul>
<!-- /wp:list -->
<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button">' . esc_html__( 'Elegir plan', 'tso-blog' ) . '</a></div><!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->
<!-- wp:column {"className":"tso-pricing-card"} -->
<div class="wp-block-column tso-pricing-card">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Plan avanzado', 'tso-blog' ) . '</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"tso-pricing-price"} -->
<p class="tso-pricing-price">39,99 €<span>' . esc_html__( '/mes', 'tso-blog' ) . '</span></p>
<!-- /wp:paragraph -->
<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item --><li>' . esc_html__( 'Todo lo del plan recomendado', 'tso-blog' ) . '</li><!-- /wp:list-item --><!-- wp:list-item --><li>' . esc_html__( 'Soporte prioritario', 'tso-blog' ) . '</li><!-- /wp:list-item --></ul>
<!-- /wp:list -->
<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button">' . esc_html__( 'Elegir plan', 'tso-blog' ) . '</a></div><!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->',
	) );

} );

add_action( 'init', function() {

	register_block_pattern( 'tso-blog/testimonial-grid', array(
		'title'       => __( 'Testimonios (2 tarjetas)', 'tso-blog' ),
		'description' => __( 'Dos tarjetas de testimonio con cita y autor.', 'tso-blog' ),
		'categories'  => array( 'tso-blog', 'columns' ),
		'content'     => '<!-- wp:columns {"className":"tso-testimonial-grid"} -->
<div class="wp-block-columns tso-testimonial-grid">
<!-- wp:column {"className":"tso-testimonial-card"} -->
<div class="wp-block-column tso-testimonial-card">
<!-- wp:quote -->
<blockquote class="wp-block-quote"><!-- wp:paragraph --><p>' . esc_html__( '“Un testimonio breve sobre la experiencia con el servicio.”', 'tso-blog' ) . '</p><!-- /wp:paragraph --><cite>' . esc_html__( 'Nombre del cliente', 'tso-blog' ) . '</cite></blockquote>
<!-- /wp:quote -->
</div>
<!-- /wp:column -->
<!-- wp:column {"className":"tso-testimonial-card"} -->
<div class="wp-block-column tso-testimonial-card">
<!-- wp:quote -->
<blockquote class="wp-block-quote"><!-- wp:paragraph --><p>' . esc_html__( '“Un testimonio breve sobre la experiencia con el servicio.”', 'tso-blog' ) . '</p><!-- /wp:paragraph --><cite>' . esc_html__( 'Nombre del cliente', 'tso-blog' ) . '</cite></blockquote>
<!-- /wp:quote -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->',
	) );

} );

add_action( 'init', function() {

	register_block_pattern( 'tso-blog/timeline', array(
		'title'       => __( 'Cronología (3 hitos)', 'tso-blog' ),
		'description' => __( 'Línea de tiempo vertical con 3 hitos (fecha, título y texto).', 'tso-blog' ),
		'categories'  => array( 'tso-blog' ),
		'content'     => '<!-- wp:group {"className":"tso-timeline","layout":{"type":"constrained"}} -->
<div class="wp-block-group tso-timeline">
<!-- wp:group {"className":"tso-timeline-item","layout":{"type":"constrained"}} -->
<div class="wp-block-group tso-timeline-item">
<!-- wp:paragraph {"className":"tso-timeline-date"} -->
<p class="tso-timeline-date">2024</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Título del hito', 'tso-blog' ) . '</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>' . esc_html__( 'Descripción breve de qué ocurrió en este momento.', 'tso-blog' ) . '</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:group {"className":"tso-timeline-item","layout":{"type":"constrained"}} -->
<div class="wp-block-group tso-timeline-item">
<!-- wp:paragraph {"className":"tso-timeline-date"} -->
<p class="tso-timeline-date">2025</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Título del hito', 'tso-blog' ) . '</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>' . esc_html__( 'Descripción breve de qué ocurrió en este momento.', 'tso-blog' ) . '</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:group {"className":"tso-timeline-item","layout":{"type":"constrained"}} -->
<div class="wp-block-group tso-timeline-item">
<!-- wp:paragraph {"className":"tso-timeline-date"} -->
<p class="tso-timeline-date">2026</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Título del hito', 'tso-blog' ) . '</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>' . esc_html__( 'Descripción breve de qué ocurrió en este momento.', 'tso-blog' ) . '</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->',
	) );

} );

add_action( 'init', function() {

	register_block_pattern( 'tso-blog/links-list', array(
		'title'       => __( 'Lista de enlaces', 'tso-blog' ),
		'description' => __( 'Botones apilados a ancho completo, estilo "linktree".', 'tso-blog' ),
		'categories'  => array( 'tso-blog', 'buttons' ),
		'content'     => '<!-- wp:buttons {"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-buttons">
<!-- wp:button {"width":100} -->
<div class="wp-block-button has-custom-width wp-block-button__width-100"><a class="wp-block-button__link wp-element-button">' . esc_html__( 'Mi web', 'tso-blog' ) . '</a></div>
<!-- /wp:button -->
<!-- wp:button {"width":100} -->
<div class="wp-block-button has-custom-width wp-block-button__width-100"><a class="wp-block-button__link wp-element-button">' . esc_html__( 'Instagram', 'tso-blog' ) . '</a></div>
<!-- /wp:button -->
<!-- wp:button {"width":100} -->
<div class="wp-block-button has-custom-width wp-block-button__width-100"><a class="wp-block-button__link wp-element-button">' . esc_html__( 'Contacto', 'tso-blog' ) . '</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->',
	) );

} );

/* ============================================================
   33. REPETIDORES DEL CUSTOMIZER — EQUIPO, PRECIOS, LANDING
   ============================================================
   Esquemas de campos + saneado + getters + render para las tres
   listas configurables desde Apariencia → Personalizar.
   ============================================================ */

/**
 * Field schema for the "Equipo" repeater.
 *
 * @return array
 */
function tsothm_get_team_fields() {
	return array(
		array(
			'key'   => 'photo',
			'type'  => 'image',
			'label' => __( 'Foto', 'tso-blog' ),
		),
		array(
			'key'   => 'name',
			'type'  => 'text',
			'label' => __( 'Nombre', 'tso-blog' ),
		),
		array(
			'key'   => 'role',
			'type'  => 'text',
			'label' => __( 'Cargo / rol', 'tso-blog' ),
		),
		array(
			'key'   => 'link',
			'type'  => 'text',
			'label' => __( 'Enlace (web, LinkedIn…) — opcional', 'tso-blog' ),
		),
	);
}

/**
 * Field schema for the "Precios" repeater.
 *
 * @return array
 */
function tsothm_get_pricing_fields() {
	return array(
		array(
			'key'   => 'title',
			'type'  => 'text',
			'label' => __( 'Nombre del plan', 'tso-blog' ),
		),
		array(
			'key'   => 'price',
			'type'  => 'text',
			'label' => __( 'Precio (p. ej. 19,99 €)', 'tso-blog' ),
		),
		array(
			'key'   => 'period',
			'type'  => 'text',
			'label' => __( 'Periodo (p. ej. /mes) — opcional', 'tso-blog' ),
		),
		array(
			'key'   => 'features',
			'type'  => 'textarea',
			'label' => __( 'Características (una por línea)', 'tso-blog' ),
		),
		array(
			'key'   => 'button_text',
			'type'  => 'text',
			'label' => __( 'Texto del botón', 'tso-blog' ),
		),
		array(
			'key'   => 'button_url',
			'type'  => 'text',
			'label' => __( 'URL del botón', 'tso-blog' ),
		),
		array(
			'key'   => 'featured',
			'type'  => 'checkbox',
			'label' => __( 'Destacar este plan', 'tso-blog' ),
		),
	);
}

/**
 * Field schema for the "Landing" highlights repeater.
 *
 * @return array
 */
function tsothm_get_landing_highlight_fields() {
	return array(
		array(
			'key'   => 'icon',
			'type'  => 'text',
			'label' => __( 'Icono (un emoji, p. ej. 🚀)', 'tso-blog' ),
		),
		array(
			'key'   => 'title',
			'type'  => 'text',
			'label' => __( 'Título breve', 'tso-blog' ),
		),
		array(
			'key'   => 'text',
			'type'  => 'textarea',
			'label' => __( 'Texto breve', 'tso-blog' ),
		),
	);
}

/**
 * Sanitize a single field value according to its schema type.
 *
 * @param mixed $value Raw value.
 * @param array $field Field schema (key/type).
 * @return mixed
 */
function tsothm_sanitize_repeater_field_value( $value, $field ) {
	switch ( $field['type'] ) {
		case 'textarea':
			return sanitize_textarea_field( is_string( $value ) ? $value : '' );
		case 'checkbox':
			return (bool) $value;
		case 'image':
			return absint( $value );
		case 'text':
		default:
			$text = is_string( $value ) ? $value : '';
			// Los campos de texto que parecen URL (link, button_url) se sanean como URL.
			if ( isset( $field['key'] ) && false !== strpos( $field['key'], 'url' ) ) {
				return esc_url_raw( $text );
			}
			if ( isset( $field['key'] ) && 'link' === $field['key'] ) {
				return esc_url_raw( $text );
			}
			return sanitize_text_field( $text );
	}
}

/**
 * Generic repeater sanitizer: decodes JSON, keeps only known fields,
 * sanitizes each value by type, re-encodes to JSON. Caps at 50 rows.
 *
 * @param string $value  Raw JSON value from the Customizer control.
 * @param array  $fields Field schema (tsothm_get_*_fields()).
 * @return string Sanitized JSON.
 */
function tsothm_sanitize_repeater_json( $value, $fields ) {
	$rows = json_decode( is_string( $value ) ? $value : '', true );
	if ( ! is_array( $rows ) ) {
		return wp_json_encode( array() );
	}

	$clean = array();
	foreach ( array_slice( $rows, 0, 50 ) as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$clean_row = array();
		foreach ( $fields as $field ) {
			$key                = $field['key'];
			$clean_row[ $key ] = tsothm_sanitize_repeater_field_value(
				isset( $row[ $key ] ) ? $row[ $key ] : '',
				$field
			);
		}
		$clean[] = $clean_row;
	}

	return wp_json_encode( $clean );
}

/**
 * Sanitize callback: Equipo.
 *
 * @param string $value Raw value.
 * @return string
 */
function tsothm_sanitize_team_members( $value ) {
	return tsothm_sanitize_repeater_json( $value, tsothm_get_team_fields() );
}

/**
 * Sanitize callback: Precios.
 *
 * @param string $value Raw value.
 * @return string
 */
function tsothm_sanitize_pricing_plans( $value ) {
	return tsothm_sanitize_repeater_json( $value, tsothm_get_pricing_fields() );
}

/**
 * Sanitize callback: Landing (destacados).
 *
 * @param string $value Raw value.
 * @return string
 */
function tsothm_sanitize_landing_highlights( $value ) {
	return tsothm_sanitize_repeater_json( $value, tsothm_get_landing_highlight_fields() );
}

/**
 * Decode + defensively re-sanitize a repeater theme_mod.
 *
 * @param string $mod_name Theme mod name.
 * @param array  $fields   Field schema.
 * @return array
 */
function tsothm_get_repeater_rows( $mod_name, $fields ) {
	$raw  = get_theme_mod( $mod_name, '[]' );
	$json = tsothm_sanitize_repeater_json( $raw, $fields );
	$rows = json_decode( $json, true );
	return is_array( $rows ) ? $rows : array();
}

/**
 * Team members configured in the Customizer.
 *
 * @return array
 */
function tsothm_get_team_members() {
	$rows = tsothm_get_repeater_rows( 'tsothm_team_members', tsothm_get_team_fields() );
	return array_values(
		array_filter(
			$rows,
			function( $row ) {
				return ! empty( $row['name'] );
			}
		)
	);
}

/**
 * Pricing plans configured in the Customizer.
 *
 * @return array
 */
function tsothm_get_pricing_plans() {
	$rows = tsothm_get_repeater_rows( 'tsothm_pricing_plans', tsothm_get_pricing_fields() );
	return array_values(
		array_filter(
			$rows,
			function( $row ) {
				return ! empty( $row['title'] );
			}
		)
	);
}

/**
 * Landing highlights configured in the Customizer.
 *
 * @return array
 */
function tsothm_get_landing_highlights() {
	$rows = tsothm_get_repeater_rows( 'tsothm_landing_highlights', tsothm_get_landing_highlight_fields() );
	return array_values(
		array_filter(
			$rows,
			function( $row ) {
				return ! empty( $row['title'] ) || ! empty( $row['text'] );
			}
		)
	);
}

/**
 * Render the Team grid from Customizer data. Falls back to false so the
 * template can show the block content (pattern) instead.
 *
 * @param int $post_id Page ID (para el aviso de edición).
 * @return bool True si se ha pintado algo.
 */
function tsothm_render_team_grid( $post_id ) {
	$members = tsothm_get_team_members();

	if ( empty( $members ) ) {
		return false;
	}

	echo '<div class="tso-team-grid tso-team-grid--dynamic">';
	foreach ( $members as $member ) {
		echo '<div class="tso-team-member">';
		if ( ! empty( $member['photo'] ) ) {
			echo wp_get_attachment_image(
				(int) $member['photo'],
				'medium',
				false,
				array( 'class' => 'tso-team-photo' )
			);
		} else {
			echo '<div class="tso-team-photo tso-team-photo--placeholder" aria-hidden="true">' . esc_html( mb_substr( (string) $member['name'], 0, 1 ) ) . '</div>';
		}
		echo '<h3 class="tso-team-name">';
		if ( ! empty( $member['link'] ) ) {
			echo '<a href="' . esc_url( $member['link'] ) . '">' . esc_html( $member['name'] ) . '</a>';
		} else {
			echo esc_html( $member['name'] );
		}
		echo '</h3>';
		if ( ! empty( $member['role'] ) ) {
			echo '<p class="tso-team-role">' . esc_html( $member['role'] ) . '</p>';
		}
		echo '</div>';
	}
	echo '</div>';

	return true;
}

/**
 * Render the Pricing table from Customizer data.
 *
 * @param int $post_id Page ID.
 * @return bool True si se ha pintado algo.
 */
function tsothm_render_pricing_table( $post_id ) {
	$plans = tsothm_get_pricing_plans();

	if ( empty( $plans ) ) {
		return false;
	}

	echo '<div class="tso-pricing-grid tso-pricing-grid--dynamic">';
	foreach ( $plans as $plan ) {
		$featured = ! empty( $plan['featured'] );
		echo '<div class="tso-pricing-card' . ( $featured ? ' tso-pricing-card--featured' : '' ) . '">';
		if ( $featured ) {
			echo '<span class="tso-pricing-badge">' . esc_html__( 'Recomendado', 'tso-blog' ) . '</span>';
		}
		echo '<h3 class="tso-pricing-title">' . esc_html( $plan['title'] ) . '</h3>';
		if ( ! empty( $plan['price'] ) ) {
			echo '<p class="tso-pricing-price">' . esc_html( $plan['price'] );
			if ( ! empty( $plan['period'] ) ) {
				echo '<span>' . esc_html( $plan['period'] ) . '</span>';
			}
			echo '</p>';
		}
		if ( ! empty( $plan['features'] ) ) {
			echo '<ul class="tso-pricing-features">';
			foreach ( preg_split( '/\r\n|\r|\n/', (string) $plan['features'] ) as $line ) {
				$line = trim( $line );
				if ( '' === $line ) {
					continue;
				}
				echo '<li>' . esc_html( $line ) . '</li>';
			}
			echo '</ul>';
		}
		if ( ! empty( $plan['button_text'] ) && ! empty( $plan['button_url'] ) ) {
			echo '<a class="tso-pricing-button" href="' . esc_url( $plan['button_url'] ) . '">' . esc_html( $plan['button_text'] ) . '</a>';
		}
		echo '</div>';
	}
	echo '</div>';

	return true;
}

/**
 * Render the Landing highlights row from Customizer data.
 *
 * @return bool True si se ha pintado algo.
 */
function tsothm_render_landing_highlights() {
	$items = tsothm_get_landing_highlights();

	if ( empty( $items ) ) {
		return false;
	}

	echo '<div class="tso-landing-highlights">';
	foreach ( $items as $item ) {
		echo '<div class="tso-landing-highlight">';
		if ( ! empty( $item['icon'] ) ) {
			echo '<span class="tso-landing-highlight-icon" aria-hidden="true">' . esc_html( $item['icon'] ) . '</span>';
		}
		if ( ! empty( $item['title'] ) ) {
			echo '<h3 class="tso-landing-highlight-title">' . esc_html( $item['title'] ) . '</h3>';
		}
		if ( ! empty( $item['text'] ) ) {
			echo '<p class="tso-landing-highlight-text">' . esc_html( $item['text'] ) . '</p>';
		}
		echo '</div>';
	}
	echo '</div>';

	return true;
}

/**
 * Landing hero settings (eyebrow + botones), Customizer.
 *
 * @return array<string, string>
 */
function tsothm_get_landing_hero_settings() {
	return array(
		'eyebrow'     => sanitize_text_field( get_theme_mod( 'tsothm_landing_eyebrow', '' ) ),
		'cta_text'    => sanitize_text_field( get_theme_mod( 'tsothm_landing_cta_text', '' ) ),
		'cta_url'     => esc_url_raw( get_theme_mod( 'tsothm_landing_cta_url', '' ) ),
		'cta2_text'   => sanitize_text_field( get_theme_mod( 'tsothm_landing_cta2_text', '' ) ),
		'cta2_url'    => esc_url_raw( get_theme_mod( 'tsothm_landing_cta2_url', '' ) ),
	);
}

/* ============================================================
   34. MODE DIA / NIT / AUTOMÀTIC — SELECTOR DE COLOR
   ============================================================
   Afegeix un interruptor flotant (dia / nit / automàtic).
   El mode "automàtic" segueix la preferència del navegador
   (prefers-color-scheme) i es manté sincronitzat si l'usuari
   la canvia mentre navega. El mode "dia" no es toca: només
   s'afegeixen variables i regles noves per al mode "nit".
   ============================================================ */

/**
 * Encola (com a script inline) el selector de mode de color.
 */
function tsothm_enqueue_color_mode_script() {
    wp_register_script( 'tsothm-color-mode', false, array(), wp_get_theme()->get( 'Version' ), true );
    wp_enqueue_script( 'tsothm-color-mode' );
    wp_add_inline_script( 'tsothm-color-mode', tsothm_get_color_mode_js() );
}
add_action( 'wp_enqueue_scripts', 'tsothm_enqueue_color_mode_script' );

/**
 * JS del selector de mode de color (dia / nit / automàtic).
 *
 * @return string
 */
function tsothm_get_color_mode_js() {
    return <<<'JS'
( function () {
    'use strict';

    var STORAGE_KEY = 'tsothmColorMode';
    var GEO_KEY = 'tsothmGeo';
    var root = document.documentElement;
    var media = window.matchMedia( '(prefers-color-scheme: dark)' );
    var buttons = [];
    var flipTimer = null;

    var TZ_COORDS = {
        'Europe/Madrid': [40.4, -3.7], 'Europe/London': [51.5, -0.1], 'Europe/Paris': [48.9, 2.3],
        'Europe/Berlin': [52.5, 13.4], 'Europe/Rome': [41.9, 12.5], 'Europe/Lisbon': [38.7, -9.1],
        'Europe/Amsterdam': [52.4, 4.9], 'Europe/Brussels': [50.8, 4.4], 'Europe/Vienna': [48.2, 16.4],
        'Europe/Zurich': [47.4, 8.5], 'Europe/Warsaw': [52.2, 21.0], 'Europe/Athens': [38.0, 23.7],
        'Europe/Moscow': [55.8, 37.6], 'Europe/Stockholm': [59.3, 18.1], 'Europe/Oslo': [59.9, 10.7],
        'Europe/Helsinki': [60.2, 24.9], 'Europe/Dublin': [53.3, -6.3], 'Europe/Budapest': [47.5, 19.0],
        'Europe/Bucharest': [44.4, 26.1], 'Europe/Kiev': [50.5, 30.5],
        'America/New_York': [40.7, -74.0], 'America/Chicago': [41.9, -87.6], 'America/Denver': [39.7, -105.0],
        'America/Los_Angeles': [34.1, -118.2], 'America/Mexico_City': [19.4, -99.1], 'America/Bogota': [4.7, -74.1],
        'America/Sao_Paulo': [-23.6, -46.6], 'America/Argentina/Buenos_Aires': [-34.6, -58.4], 'America/Santiago': [-33.5, -70.6],
        'America/Lima': [-12.0, -77.0], 'America/Toronto': [43.7, -79.4],
        'Asia/Tokyo': [35.7, 139.7], 'Asia/Shanghai': [31.2, 121.5], 'Asia/Hong_Kong': [22.3, 114.2],
        'Asia/Singapore': [1.35, 103.8], 'Asia/Dubai': [25.2, 55.3], 'Asia/Kolkata': [19.1, 72.9],
        'Asia/Bangkok': [13.8, 100.5], 'Asia/Seoul': [37.6, 127.0], 'Asia/Jakarta': [-6.2, 106.8],
        'Asia/Manila': [14.6, 121.0],
        'Australia/Sydney': [-33.9, 151.2], 'Australia/Melbourne': [-37.8, 144.9], 'Pacific/Auckland': [-36.8, 174.8],
        'Africa/Cairo': [30.0, 31.2], 'Africa/Johannesburg': [-26.2, 28.0], 'Africa/Lagos': [6.5, 3.4],
        'Africa/Nairobi': [-1.3, 36.8]
    };

    function getStoredMode() {
        try {
            var m = window.localStorage.getItem( STORAGE_KEY );
            if ( m === 'light' || m === 'dark' || m === 'auto' ) {
                return m;
            }
        } catch ( e ) {}
        return 'auto';
    }

    function getApproxCoords() {
        var tz = null;
        try {
            tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
        } catch ( e ) {}
        if ( tz && TZ_COORDS[ tz ] ) {
            return TZ_COORDS[ tz ];
        }
        var offsetMin = -( new Date() ).getTimezoneOffset();
        var lon = offsetMin / 4;
        if ( lon > 180 ) { lon -= 360; }
        if ( lon < -180 ) { lon += 360; }
        return [ 20, lon ];
    }

    function getSunTimes( date, lat, lon ) {
        var rad = Math.PI / 180;
        var dayMs = 86400000;
        var J1970 = 2440588;
        var J2000 = 2451545;
        var e = rad * 23.4397;

        function toJulian( d ) { return d.valueOf() / dayMs - 0.5 + J1970; }
        function fromJulian( j ) { return new Date( ( j + 0.5 - J1970 ) * dayMs ); }
        function toDays( d ) { return toJulian( d ) - J2000; }
        function solarMeanAnomaly( d ) { return rad * ( 357.5291 + 0.98560028 * d ); }
        function eclipticLongitude( M ) {
            var C = rad * ( 1.9148 * Math.sin( M ) + 0.02 * Math.sin( 2 * M ) + 0.0003 * Math.sin( 3 * M ) );
            var P = rad * 102.9372;
            return M + C + P + Math.PI;
        }
        function declination( L ) { return Math.asin( Math.sin( L ) * Math.sin( e ) ); }
        function julianCycle( d, lw ) { return Math.round( d - 0.0009 - lw / ( 2 * Math.PI ) ); }
        function approxTransit( Ht, lw, n ) { return 0.0009 + ( Ht + lw ) / ( 2 * Math.PI ) + n; }
        function solarTransitJ( ds, M, L ) { return J2000 + ds + 0.0053 * Math.sin( M ) - 0.0069 * Math.sin( 2 * L ); }
        function hourAngle( h, phi, d ) {
            var cosH = ( Math.sin( h ) - Math.sin( phi ) * Math.sin( d ) ) / ( Math.cos( phi ) * Math.cos( d ) );
            cosH = Math.max( -1, Math.min( 1, cosH ) );
            return Math.acos( cosH );
        }

        var lw = rad * -lon;
        var phi = rad * lat;
        var d = toDays( date );
        var n = julianCycle( d, lw );
        var ds = approxTransit( 0, lw, n );
        var M = solarMeanAnomaly( ds );
        var L = eclipticLongitude( M );
        var dec = declination( L );
        var Jnoon = solarTransitJ( ds, M, L );
        var h0 = rad * -0.833;
        var w0 = hourAngle( h0, phi, dec );
        var a = approxTransit( w0, lw, n );
        var Jset = solarTransitJ( a, M, L );
        var Jrise = Jnoon - ( Jset - Jnoon );

        return { sunrise: fromJulian( Jrise ), sunset: fromJulian( Jset ) };
    }

    function isDaytime( lat, lon ) {
        var times = getSunTimes( new Date(), lat, lon );
        var now = new Date();
        return now >= times.sunrise && now < times.sunset;
    }

    function readGeoCache() {
        try {
            return JSON.parse( window.localStorage.getItem( GEO_KEY ) || 'null' );
        } catch ( e ) {
            return null;
        }
    }

    function writeGeoCache( lat, lon ) {
        try {
            window.localStorage.setItem( GEO_KEY, JSON.stringify( { lat: lat, lon: lon, date: ( new Date() ).toDateString() } ) );
        } catch ( e ) {}
    }

    function applyMode( mode ) {
        var effective;
        if ( mode === 'auto' ) {
            var cached = readGeoCache();
            if ( cached && typeof cached.lat === 'number' && typeof cached.lon === 'number' ) {
                effective = isDaytime( cached.lat, cached.lon ) ? 'light' : 'dark';
            } else {
                effective = media.matches ? 'dark' : 'light';
            }
        } else {
            effective = mode;
        }
        root.setAttribute( 'data-theme', effective );
    }

    function updateActiveButton( mode ) {
        buttons.forEach( function ( btn ) {
            var isActive = btn.getAttribute( 'data-mode' ) === mode;
            btn.classList.toggle( 'is-active', isActive );
            btn.setAttribute( 'aria-pressed', isActive ? 'true' : 'false' );
        } );
    }

    function setMode( mode ) {
        try {
            window.localStorage.setItem( STORAGE_KEY, mode );
        } catch ( e ) {}
        applyMode( mode );
        updateActiveButton( mode );
        if ( mode === 'auto' ) {
            resolveCoordsAndSchedule();
        } else if ( flipTimer ) {
            clearTimeout( flipTimer );
            flipTimer = null;
        }
    }

    function scheduleNextFlip( lat, lon ) {
        if ( flipTimer ) {
            clearTimeout( flipTimer );
        }
        var now = new Date();
        var times = getSunTimes( now, lat, lon );
        var next;
        if ( now < times.sunrise ) {
            next = times.sunrise;
        } else if ( now < times.sunset ) {
            next = times.sunset;
        } else {
            next = getSunTimes( new Date( now.getTime() + 86400000 ), lat, lon ).sunrise;
        }
        var delay = Math.min( Math.max( 60000, next.getTime() - now.getTime() ), 3600000 * 6 );
        flipTimer = setTimeout( function () {
            if ( getStoredMode() === 'auto' ) {
                applyMode( 'auto' );
            }
            scheduleNextFlip( lat, lon );
        }, delay );
    }

    function resolveCoordsAndSchedule() {
        var todayStr = ( new Date() ).toDateString();
        var cached = readGeoCache();
        if ( cached && cached.date === todayStr && typeof cached.lat === 'number' ) {
            if ( getStoredMode() === 'auto' ) {
                applyMode( 'auto' );
            }
            scheduleNextFlip( cached.lat, cached.lon );
            return;
        }

        var approx = getApproxCoords();

        function finish( lat, lon ) {
            writeGeoCache( lat, lon );
            if ( getStoredMode() === 'auto' ) {
                applyMode( 'auto' );
                updateActiveButton( 'auto' );
            }
            scheduleNextFlip( lat, lon );
        }

        if ( navigator.geolocation && navigator.permissions && navigator.permissions.query ) {
            navigator.permissions.query( { name: 'geolocation' } ).then( function ( status ) {
                if ( status.state === 'granted' ) {
                    navigator.geolocation.getCurrentPosition(
                        function ( pos ) { finish( pos.coords.latitude, pos.coords.longitude ); },
                        function () { finish( approx[ 0 ], approx[ 1 ] ); },
                        { timeout: 5000, maximumAge: 3600000 }
                    );
                } else {
                    finish( approx[ 0 ], approx[ 1 ] );
                }
            } ).catch( function () { finish( approx[ 0 ], approx[ 1 ] ); } );
        } else {
            finish( approx[ 0 ], approx[ 1 ] );
        }
    }

    applyMode( getStoredMode() );
    resolveCoordsAndSchedule();

    var onMediaChange = function () {
        if ( getStoredMode() === 'auto' && ! readGeoCache() ) {
            applyMode( 'auto' );
        }
    };
    if ( media.addEventListener ) {
        media.addEventListener( 'change', onMediaChange );
    } else if ( media.addListener ) {
        media.addListener( onMediaChange );
    }

    function wireSwitch() {
        var wrap = document.querySelector( '.tso-theme-switch' );
        if ( ! wrap ) {
            return;
        }
        buttons = Array.prototype.slice.call( wrap.querySelectorAll( '.tso-theme-switch-btn' ) );
        buttons.forEach( function ( btn ) {
            btn.addEventListener( 'click', function () {
                setMode( btn.getAttribute( 'data-mode' ) );
            } );
        } );
        updateActiveButton( getStoredMode() );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', wireSwitch );
    } else {
        wireSwitch();
    }
} )();
JS;
}

/**
 * Imprimeix un petit script inline al <head>, el més aviat possible,
 * per fixar l'atribut data-theme abans de pintar la pàgina i evitar
 * el "flash" del mode incorrecte.
 */
function tsothm_print_color_mode_boot_script() {
    ?>
<script>
(function () {
    try {
        var m = window.localStorage.getItem( 'tsothmColorMode' );
        var effective = null;
        if ( m === 'light' || m === 'dark' ) {
            effective = m;
        } else {
            try {
                var geo = JSON.parse( window.localStorage.getItem( 'tsothmGeo' ) || 'null' );
                if ( geo && geo.date === ( new Date() ).toDateString() && typeof geo.lat === 'number' ) {
                    var rad = Math.PI / 180, dayMs = 86400000, J1970 = 2440588, J2000 = 2451545, e = rad * 23.4397;
                    var toJulian = function ( d ) { return d.valueOf() / dayMs - 0.5 + J1970; };
                    var toDays = function ( d ) { return toJulian( d ) - J2000; };
                    var d0 = toDays( new Date() );
                    var M = rad * ( 357.5291 + 0.98560028 * d0 );
                    var C = rad * ( 1.9148 * Math.sin( M ) + 0.02 * Math.sin( 2 * M ) + 0.0003 * Math.sin( 3 * M ) );
                    var L = M + C + rad * 102.9372 + Math.PI;
                    var dec = Math.asin( Math.sin( L ) * Math.sin( e ) );
                    var lw = rad * -geo.lon, phi = rad * geo.lat;
                    var n = Math.round( d0 - 0.0009 - lw / ( 2 * Math.PI ) );
                    var ds = 0.0009 + lw / ( 2 * Math.PI ) + n;
                    var Jnoon = J2000 + ds + 0.0053 * Math.sin( M ) - 0.0069 * Math.sin( 2 * L );
                    var h0 = rad * -0.833;
                    var cosH = Math.max( -1, Math.min( 1, ( Math.sin( h0 ) - Math.sin( phi ) * Math.sin( dec ) ) / ( Math.cos( phi ) * Math.cos( dec ) ) ) );
                    var w0 = Math.acos( cosH );
                    var a = 0.0009 + ( w0 + lw ) / ( 2 * Math.PI ) + n;
                    var Jset = J2000 + a + 0.0053 * Math.sin( M ) - 0.0069 * Math.sin( 2 * L );
                    var Jrise = Jnoon - ( Jset - Jnoon );
                    var sunrise = new Date( ( Jrise + 0.5 - J1970 ) * dayMs );
                    var sunset = new Date( ( Jset + 0.5 - J1970 ) * dayMs );
                    var now = new Date();
                    effective = ( now >= sunrise && now < sunset ) ? 'light' : 'dark';
                }
            } catch ( e3 ) {}
            if ( ! effective ) {
                effective = window.matchMedia( '(prefers-color-scheme: dark)' ).matches ? 'dark' : 'light';
            }
        }
        document.documentElement.setAttribute( 'data-theme', effective );
    } catch ( e ) {}
})();
</script>
    <?php
}
add_action( 'wp_head', 'tsothm_print_color_mode_boot_script', 1 );

/* ============================================================
   34b. MODE NIT — COLORS DE TEXT EN LÍNIA DEL CONTINGUT ANTIC
   ============================================================
   El contingut importat (Blogger, TinyMCE, etc.) porta colors escrits
   en línia (style="color:#000000", <font color="…">) que guanyen sobre
   el color del tema i deixen el text il·legible sobre fons fosc.
   En mode nit, aquest script aclareix només els colors que no arriben
   a un contrast mínim (WCAG AA 4.5:1) contra el fons real, mantenint
   el to; els que ja es llegeixen bé no es toquen. En mode dia es
   restaura el color original.
   ============================================================ */

/**
 * Encola (com a script inline) el corrector de contrast del mode nit.
 */
function tsothm_enqueue_dark_content_colors_script() {
    if ( is_admin() ) {
        return;
    }
    wp_register_script( 'tsothm-dark-content-colors', false, array(), wp_get_theme()->get( 'Version' ), true );
    wp_enqueue_script( 'tsothm-dark-content-colors' );
    wp_add_inline_script( 'tsothm-dark-content-colors', tsothm_get_dark_content_colors_js() );
}
add_action( 'wp_enqueue_scripts', 'tsothm_enqueue_dark_content_colors_script' );

/**
 * JS que corregeix el contrast dels colors de text en línia en mode nit.
 *
 * @return string
 */
function tsothm_get_dark_content_colors_js() {
    return <<<'JS'
( function () {
    var root = document.documentElement;
    var SELECTOR = '.entry-content [style*="color"], .entry-content font[color], .comment-content [style*="color"], .comment-content font[color]';
    var ATTR = 'data-tso-orig-color';
    var MIN_CONTRAST = 4.5;
    var FALLBACK_BG = { r: 23, g: 24, b: 28, a: 1 };
    var timer = null;

    function parseRgb( str ) {
        var m = String( str || '' ).match( /rgba?\(\s*([\d.]+)[,\s]+([\d.]+)[,\s]+([\d.]+)(?:[,\s\/]+([\d.]+%?))?\s*\)/ );
        var a = 1;
        if ( ! m ) {
            return null;
        }
        if ( m[ 4 ] !== undefined ) {
            a = m[ 4 ].indexOf( '%' ) > -1 ? parseFloat( m[ 4 ] ) / 100 : parseFloat( m[ 4 ] );
        }
        return { r: parseFloat( m[ 1 ] ), g: parseFloat( m[ 2 ] ), b: parseFloat( m[ 3 ] ), a: a };
    }

    function lin( v ) {
        v = v / 255;
        return v <= 0.03928 ? v / 12.92 : Math.pow( ( v + 0.055 ) / 1.055, 2.4 );
    }

    function luminance( c ) {
        return 0.2126 * lin( c.r ) + 0.7152 * lin( c.g ) + 0.0722 * lin( c.b );
    }

    function contrast( a, b ) {
        var l1 = luminance( a );
        var l2 = luminance( b );
        return ( Math.max( l1, l2 ) + 0.05 ) / ( Math.min( l1, l2 ) + 0.05 );
    }

    function rgbToHsl( c ) {
        var r = c.r / 255, g = c.g / 255, b = c.b / 255;
        var max = Math.max( r, g, b ), min = Math.min( r, g, b );
        var l = ( max + min ) / 2, h = 0, s = 0, d = max - min;
        if ( d !== 0 ) {
            s = l > 0.5 ? d / ( 2 - max - min ) : d / ( max + min );
            if ( max === r ) {
                h = ( g - b ) / d + ( g < b ? 6 : 0 );
            } else if ( max === g ) {
                h = ( b - r ) / d + 2;
            } else {
                h = ( r - g ) / d + 4;
            }
            h = h / 6;
        }
        return { h: h, s: s, l: l };
    }

    function hslToRgb( h, s, l ) {
        function hue( p, q, t ) {
            if ( t < 0 ) { t += 1; }
            if ( t > 1 ) { t -= 1; }
            if ( t < 1 / 6 ) { return p + ( q - p ) * 6 * t; }
            if ( t < 1 / 2 ) { return q; }
            if ( t < 2 / 3 ) { return p + ( q - p ) * ( 2 / 3 - t ) * 6; }
            return p;
        }
        if ( s === 0 ) {
            var v = Math.round( l * 255 );
            return { r: v, g: v, b: v, a: 1 };
        }
        var q = l < 0.5 ? l * ( 1 + s ) : l + s - l * s;
        var p = 2 * l - q;
        return {
            r: Math.round( hue( p, q, h + 1 / 3 ) * 255 ),
            g: Math.round( hue( p, q, h ) * 255 ),
            b: Math.round( hue( p, q, h - 1 / 3 ) * 255 ),
            a: 1
        };
    }

    function effectiveBackground( el ) {
        var node = el;
        while ( node && node.nodeType === 1 ) {
            var c = parseRgb( window.getComputedStyle( node ).backgroundColor );
            if ( c && c.a >= 0.5 ) {
                return c;
            }
            node = node.parentElement;
        }
        return FALLBACK_BG;
    }

    function restore( el ) {
        var orig = el.getAttribute( ATTR );
        if ( orig === null ) {
            return;
        }
        if ( orig === '' ) {
            el.style.removeProperty( 'color' );
        } else {
            el.style.setProperty( 'color', orig );
        }
    }

    function adjust( el ) {
        var bg, fg, hsl, out, l, s, dir, i, inherited;
        restore( el );
        bg = effectiveBackground( el );
        fg = parseRgb( window.getComputedStyle( el ).color );
        if ( ! fg || contrast( fg, bg ) >= MIN_CONTRAST ) {
            return;
        }
        hsl = rgbToHsl( fg );

        // Colors neutres (negre, grisos): millor heretar el color de text del tema.
        if ( hsl.s < 0.2 && el.parentElement ) {
            inherited = parseRgb( window.getComputedStyle( el.parentElement ).color );
            if ( inherited && contrast( inherited, bg ) >= MIN_CONTRAST ) {
                el.style.setProperty( 'color', 'rgb(' + inherited.r + ', ' + inherited.g + ', ' + inherited.b + ')' );
                return;
            }
        }

        // Altres tons: es manté el to i es puja (o baixa) la lluminositat.
        dir = luminance( bg ) < 0.5 ? 1 : -1;
        l = hsl.l;
        s = Math.min( hsl.s, 0.9 );
        for ( i = 0; i < 40; i++ ) {
            l = Math.max( 0.03, Math.min( 0.97, l + dir * 0.03 ) );
            out = hslToRgb( hsl.h, s, l );
            if ( contrast( out, bg ) >= MIN_CONTRAST ) {
                break;
            }
        }
        el.style.setProperty( 'color', 'rgb(' + out.r + ', ' + out.g + ', ' + out.b + ')' );
    }

    function run() {
        var list, i, el;
        if ( root.getAttribute( 'data-theme' ) !== 'dark' ) {
            list = document.querySelectorAll( '[' + ATTR + ']' );
            for ( i = 0; i < list.length; i++ ) {
                restore( list[ i ] );
            }
            return;
        }
        list = document.querySelectorAll( SELECTOR );
        for ( i = 0; i < list.length; i++ ) {
            el = list[ i ];
            if ( el.getAttribute( ATTR ) === null ) {
                if ( ! el.style.color && ! ( el.tagName === 'FONT' && el.getAttribute( 'color' ) ) ) {
                    continue;
                }
                el.setAttribute( ATTR, el.style.color || '' );
            }
            adjust( el );
        }
    }

    function schedule() {
        if ( timer ) {
            window.clearTimeout( timer );
        }
        timer = window.setTimeout( run, 250 );
    }

    function init() {
        run();
        if ( window.MutationObserver ) {
            // Canvi de mode dia / nit.
            new MutationObserver( run ).observe( root, { attributes: true, attributeFilter: [ 'data-theme' ] } );
            // Contingut afegit dinàmicament (càrrega de més entrades, etc.).
            new MutationObserver( function () {
                if ( root.getAttribute( 'data-theme' ) === 'dark' ) {
                    schedule();
                }
            } ).observe( document.body, { childList: true, subtree: true } );
        }
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }
} )();
JS;
}

/* ============================================================
   35. SELECTOR DE MODE DE COLOR — OPCIÓ AL CUSTOMIZER
   ============================================================
   Permet mostrar/ocultar el botó de la capçalera des de
   Personalizar. Activat per defecte. El JS de la secció 34
   només afegeix els esdeveniments; el marcat es genera aquí.
   ============================================================ */

/**
 * Registra la opción del Customizer para mostrar/ocultar el
 * interruptor de color en la cabecera (activado por defecto).
 *
 * @param WP_Customize_Manager $wp_customize Instancia del Customizer.
 */
function tsothm_customize_register_color_mode( $wp_customize ) {
    $wp_customize->add_section(
        'tsothm_color_mode_section',
        array(
            'title'       => __( 'Modo día/noche', 'tso-blog' ),
            'description' => __( 'Interruptor de dia / noche / automático que aparece en la cabecera, junto al buscador.', 'tso-blog' ),
            'panel'       => 'tsothm_appearance_panel',
            'priority'    => 35,
        )
    );

    $wp_customize->add_setting(
        'tsothm_color_mode_switch_enabled',
        array(
            'default'           => 1,
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_color_mode_switch_enabled',
        array(
            'label'       => __( 'Mostrar el interruptor de día / noche', 'tso-blog' ),
            'description' => __( 'Si lo desactivas, el sitio seguirá respetando el modo del sistema del visitante, pero no se mostrará el botón para cambiarlo a mano.', 'tso-blog' ),
            'section'     => 'tsothm_color_mode_section',
            'type'        => 'checkbox',
        )
    );
}
add_action( 'customize_register', 'tsothm_customize_register_color_mode' );

/**
 * Marcado del interruptor día / noche / automático.
 * Se imprime en la cabecera; el JS (sección 34) añade los
 * eventos de clic y el estado inicial.
 */
function tsothm_render_color_mode_switch() {
    if ( ! get_theme_mod( 'tsothm_color_mode_switch_enabled', 1 ) ) {
        return;
    }

    $modes = array(
        'light' => array(
            'label' => __( 'Modo día', 'tso-blog' ),
            'icon'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"></circle><path d="M12 2.5v2.5M12 19v2.5M4.2 4.2l1.8 1.8M18 18l1.8 1.8M1.5 12h2.5M20 12h2.5M4.2 19.8l1.8-1.8M18 6l1.8-1.8"/></svg>',
        ),
        'dark'  => array(
            'label' => __( 'Modo noche', 'tso-blog' ),
            'icon'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 14.7A8.5 8.5 0 0 1 9.3 3.5a8.5 8.5 0 1 0 11.2 11.2z"/></svg>',
        ),
        'auto'  => array(
            'label' => __( 'Modo automático', 'tso-blog' ),
            'icon'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"></circle><path d="M12 3.5a8.5 8.5 0 0 1 0 17z" fill="currentColor" stroke="none"></path></svg>',
        ),
    );

    echo '<div class="tso-theme-switch" role="group" aria-label="' . esc_attr__( 'Modo de color', 'tso-blog' ) . '">';
    foreach ( $modes as $mode => $data ) {
        echo '<button type="button" class="tso-theme-switch-btn" data-mode="' . esc_attr( $mode ) . '" aria-label="' . esc_attr( $data['label'] ) . '" title="' . esc_attr( $data['label'] ) . '">' . $data['icon'] . '</button>';
    }
    echo '</div>';
}


/**
 * SECCIÓN 36 — CUENTAGOTAS (EYEDROPPER) EN LOS SELECTORES DE COLOR DEL PERSONALIZADOR
 *
 * Añade un botón junto a cada control de color del Personalizador que, en
 * navegadores compatibles (Chrome/Edge), permite elegir cualquier color de
 * la pantalla con el ratón usando la API nativa EyeDropper.
 */
function tsothm_get_customize_eyedropper_js() {
    return <<<'JS'
( function ( $ ) {
    'use strict';

    function addEyedropperButton( control ) {
        if ( ! window.EyeDropper ) {
            return;
        }
        var container = control.container.find( '.wp-picker-container' );
        if ( ! container.length || container.find( '.tsothm-eyedropper-btn' ).length ) {
            return;
        }
        var swatch = container.find( '.wp-color-result' );
        if ( ! swatch.length ) {
            return;
        }
        var btn = $( '<button type="button" class="button tsothm-eyedropper-btn" aria-label="Elegir color de la pantalla"></button>' );
        btn.html( '<svg width="16" height="16" viewBox="0 0 24 24" style="vertical-align:middle;" aria-hidden="true"><path fill="currentColor" d="M19.2 3.2a2.7 2.7 0 0 1 3.8 3.8l-2 2 1 1-1.4 1.4-1-1-8.9 8.9-3.6 1-1.4-1.4 1-3.6 8.9-8.9-1-1L16 3.2l1 1 2.2-2.2Zm-1.4 4.6-8.6 8.6-.4 1.4 1.4-.4 8.6-8.6-1-1Z"/></svg>' );
        btn.css( { marginLeft: '6px', verticalAlign: 'middle', padding: '0 6px', lineHeight: '28px', height: '30px' } );
        swatch.after( btn );

        btn.on( 'click', function ( e ) {
            e.preventDefault();
            try {
                var eyeDropper = new window.EyeDropper();
                eyeDropper.open().then( function ( result ) {
                    var hex = result.sRGBHex;
                    var picker = container.find( '.wp-color-picker' );
                    if ( picker.length && picker.wpColorPicker ) {
                        picker.wpColorPicker( 'color', hex );
                    }
                    control.setting.set( hex );
                } ).catch( function () {} );
            } catch ( err ) {}
        } );
    }

    function hookControl( control ) {
        if ( control.params.type !== 'color' ) {
            return;
        }
        control.deferred.embedded.done( function () {
            _.delay( function () {
                addEyedropperButton( control );
            }, 100 );
        } );
    }

    wp.customize.control.each( hookControl );
    wp.customize.control.bind( 'add', hookControl );
} )( jQuery );
JS;
}

function tsothm_enqueue_customize_eyedropper() {
    wp_register_script( 'tsothm-customize-eyedropper', false, array( 'jquery', 'wp-color-picker', 'customize-controls', 'underscore' ), TSOTHM_VERSION, true );
    wp_enqueue_script( 'tsothm-customize-eyedropper' );
    wp_add_inline_script( 'tsothm-customize-eyedropper', tsothm_get_customize_eyedropper_js() );
}
add_action( 'customize_controls_enqueue_scripts', 'tsothm_enqueue_customize_eyedropper' );
