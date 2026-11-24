<?php
/**
 * Theme setup: legacy theme-mod migration, content width, translations, theme supports, image sizes and menus.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
 * Matches the main column under the default ~1200px layout + 300px sidebar,
 * and aligns with WordPress “large” so content images can pick sharper srcset candidates.
 *
 * @global int $content_width
 */
if ( ! isset( $content_width ) ) {
    $content_width = 1024;
}

/**
 * Load bundled CA/ES catalogs before any __() for tso-blog.
 * On WP 6.5+, an early gettext triggers JIT load; if that fails, the domain
 * stays empty and load_theme_textdomain later is ignored → English Customizer.
 *
 * Bundled files: tso-blog-ca.mo / ca.mo and tso-blog-es_ES.mo / es_ES.mo.
 * English is the PHP source (no en_US catalog).
 */
function tsothm_load_textdomain() {
    $domain = 'tso-blog';
    $locale = determine_locale();
    $path   = get_template_directory() . '/languages';

    if ( is_textdomain_loaded( $domain ) ) {
        unload_textdomain( $domain, true );
    }

    $locales = array( $locale );
    if ( false !== strpos( $locale, '_' ) ) {
        $locales[] = strtok( $locale, '_' );
    } elseif ( 'es' === $locale ) {
        $locales[] = 'es_ES';
    }

    $candidates = array();
    foreach ( array_unique( $locales ) as $loc ) {
        $candidates[] = $path . '/' . $domain . '-' . $loc . '.mo';
        $candidates[] = $path . '/' . $loc . '.mo';
    }

    $loaded = false;
    foreach ( array_unique( $candidates ) as $mofile ) {
        if ( is_readable( $mofile ) && load_textdomain( $domain, $mofile ) ) {
            $loaded = true;
            break;
        }
    }

    if ( ! $loaded ) {
        load_theme_textdomain( $domain, $path );
    }
}
add_action( 'after_setup_theme', 'tsothm_load_textdomain', 0 );

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

    register_nav_menus( array( 'main-menu' => __( 'Primary Menu', 'tso-blog' ) ) );

    add_editor_style( 'editor-style.css' );
} );

/**
 * Prefer sharper srcset candidates for in-content images on desktop / HiDPI.
 * Mobile keeps 100vw. Older posts often ship sizes=800px from the previous $content_width.
 *
 * @param string           $sizes Sizes attribute value.
 * @param int[]|string     $size  Requested width/height or named size.
 * @return string
 */
function tsothm_content_image_sizes( $sizes, $size ) {
    if ( is_admin() ) {
        return $sizes;
    }
    $width = 0;
    if ( is_array( $size ) ) {
        $width = isset( $size[0] ) ? (int) $size[0] : 0;
    }
    if ( $width < 600 ) {
        return $sizes;
    }
    return '(max-width: 768px) 100vw, 1024px';
}
add_filter( 'wp_calculate_image_sizes', 'tsothm_content_image_sizes', 10, 2 );
