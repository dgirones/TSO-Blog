<?php
/**
 * Color helpers (contrast, night palette), CSS custom properties and theme.json palette sync.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Perceived brightness of a hex color (0 = black, 1 = white).
 *
 * @param string $hex Hex color.
 * @return float
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

/**
 * Whether a hex color is light (perceived brightness above the threshold).
 *
 * @param string $hex       Hex color.
 * @param float  $threshold Brightness threshold (0 to 1).
 * @return bool
 */
function tsothm_is_light_hex( $hex, $threshold = 0.65 ) {
    return tsothm_hex_luminance( $hex ) > $threshold;
}

/**
 * WCAG relative luminance of a hex color (0 = black, 1 = white).
 *
 * @param string $hex Hex color.
 * @return float
 */
function tsothm_relative_luminance( $hex ) {
    $hex = ltrim( (string) $hex, '#' );
    if ( 3 === strlen( $hex ) ) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
        return 0.5;
    }
    $channels = array();
    foreach ( array( 0, 2, 4 ) as $offset ) {
        $value      = hexdec( substr( $hex, $offset, 2 ) ) / 255;
        $channels[] = ( $value <= 0.03928 ) ? $value / 12.92 : pow( ( $value + 0.055 ) / 1.055, 2.4 );
    }
    return ( 0.2126 * $channels[0] ) + ( 0.7152 * $channels[1] ) + ( 0.0722 * $channels[2] );
}

/**
 * WCAG contrast ratio between two hex colors (1 to 21).
 *
 * @param string $hex_a First color.
 * @param string $hex_b Second color.
 * @return float
 */
function tsothm_contrast_ratio( $hex_a, $hex_b ) {
    $a = tsothm_relative_luminance( $hex_a );
    $b = tsothm_relative_luminance( $hex_b );
    return ( max( $a, $b ) + 0.05 ) / ( min( $a, $b ) + 0.05 );
}

/**
 * Text color (dark or white) with the best contrast over a background.
 *
 * @param string $hex Background hex color.
 * @return string
 */
function tsothm_get_contrast_color( $hex ) {
    return ( tsothm_contrast_ratio( '#1a1a1a', $hex ) >= tsothm_contrast_ratio( '#ffffff', $hex ) ) ? '#1a1a1a' : '#ffffff';
}

/**
 * Darken a color until it is readable as text over a light background.
 *
 * @param string $hex Text hex color.
 * @param string $bg  Background hex color.
 * @param float  $min Minimum WCAG contrast ratio.
 * @return string
 */
function tsothm_get_readable_color( $hex, $bg = '#ffffff', $min = 5.2 ) {
    $color = sanitize_hex_color( $hex );
    if ( ! $color ) {
        return '#333333';
    }
    for ( $i = 0; $i < 40 && tsothm_contrast_ratio( $color, $bg ) < $min; $i++ ) {
        $color = tsothm_darken_hex( $color, 0.95 );
    }
    return $color;
}

/**
 * Night variant of a color: light colors become the dark target, dark ones are kept.
 *
 * @param string $hex         Hex color.
 * @param string $dark_target Dark replacement.
 * @return string
 */
function tsothm_get_night_variant_hex( $hex, $dark_target = '#17181c' ) {
    return tsothm_is_light_hex( $hex ) ? $dark_target : $hex;
}

/**
 * Lighten a hex color by adding to each RGB channel.
 *
 * @param string $hex    Hex color.
 * @param int    $amount Amount added per channel (0 to 255).
 * @return string
 */
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

    $sidebar_night_bg = tsothm_get_sidebar_bg_night_css();
    if ( 'transparent' === $sidebar_night_bg ) {
        $css .= '--tso-sidebar-bg:transparent;';
    } elseif ( '' !== $sidebar_night_bg ) {
        $css .= '--tso-sidebar-bg:' . $sidebar_night_bg . ';';
    } elseif ( tsothm_sidebar_has_background() ) {
        $css .= '--tso-sidebar-bg:var(--tso-color-surface-alt);';
    }

    return 'html[data-theme="dark"]{' . $css . '}';
}

/**
 * CSS custom properties for the front end and Customizer overrides.
 *
 * @return string
 */
function tsothm_get_theme_css_variables() {
    $c = tsothm_get_theme_colors();
    $header_contrast        = tsothm_get_contrast_color( $c['header_bg'] );
    $t = tsothm_get_typography_settings();
    $sb = tsothm_get_sidebar_settings();
    $accent_text            = tsothm_get_readable_color( $c['accent'] );

    return ':root{'
        . '--tso-color-accent:' . $c['accent'] . ';'
        . '--tso-color-accent-contrast:' . tsothm_get_contrast_color( $c['accent'] ) . ';'
        . '--tso-color-accent-text:' . $accent_text . ';'
        . '--tso-color-accent-text-hover:' . tsothm_darken_hex( $accent_text ) . ';'
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
        . '--tso-announce-text-shadow:' . ( tsothm_relative_luminance( $c['announce_text'] ) > 0.5 ? '0 1px 2px rgba(0,0,0,0.25)' : 'none' ) . ';'
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
        . '--tso-font-announce:' . $t['announce_stack'] . ';'
        . '--tso-font-announce-size:' . $t['announce_size'] . ';'
        . '--tso-font-announce-weight:' . $t['announce_weight'] . ';'
        . '--tso-font-announce-transform:' . $t['announce_transform'] . ';'
        . '--tso-font-announce-tracking:' . $t['announce_tracking'] . ';'
        . '--tso-font-nav:' . $t['nav_stack'] . ';'
        . '--tso-font-nav-size:' . $t['nav_size'] . ';'
        . '--tso-font-nav-weight:' . $t['nav_weight'] . ';'
        . '--tso-font-nav-transform:' . $t['nav_transform'] . ';'
        . '--tso-font-nav-tracking:' . $t['nav_tracking'] . ';'
        . '--tso-font-card-title-size:' . $t['card_title_size'] . ';'
        . '--tso-font-footer-text-size:' . $t['footer_text_size'] . ';'
        . '--tso-logo-width:' . tsothm_sanitize_logo_width_desktop( get_theme_mod( 'tsothm_logo_width', '300' ) ) . 'px;'
        . '--tso-logo-width-mobile:' . tsothm_sanitize_logo_width_mobile( get_theme_mod( 'tsothm_logo_width_mobile', '180' ) ) . 'px;'
        . '--tso-layout-max:' . tsothm_get_layout_max_css() . ';'
        . '--tso-sidebar-width:' . $sb['width'] . 'px;'
        . '--tso-sidebar-gap:' . $sb['gap'] . 'px;'
        . '--tso-sidebar-radius:' . $sb['radius'] . 'px;'
        . '--tso-sidebar-bg:' . tsothm_get_sidebar_bg_css() . ';'
        . '--tso-sidebar-widget-bg:' . tsothm_get_sidebar_widget_bg_css() . ';'
        . '--tso-sidebar-title-bg:' . tsothm_get_sidebar_title_bg_css( $c['primary'] ) . ';'
        . '}'
        . tsothm_get_night_css_variables( $c )
        . 'body{background-color:var(--tso-color-body-bg);}'
        . 'body{font-family:var(--tso-font-body);font-size:var(--tso-font-body-size);line-height:var(--tso-font-body-line);color:var(--tso-color-text);}'
        . 'h1,h2,h3,h4,h5,h6,.single-title,.page-title,.archive-title,.post-card-title,.related-posts-title,.related-post-title,.comments-title,.comment-reply-title,.widget-title{font-family:var(--tso-font-heading);font-weight:var(--tso-font-heading-weight);}'
        . '.single-title,.page-title,.archive-title,.post-card-title,.related-post-title,.entry-content h1,.entry-content h2,.entry-content h3,.entry-content h4,.entry-content h5,.entry-content h6{color:var(--tso-color-heading);}'
        . '.single-title,.page-title,.archive-title,.entry-content h1{font-size:var(--tso-font-h1-size);}'
        . '.single-title,.page-title,.archive-title,.post-card-title,.related-post-title,.entry-content h1{text-transform:var(--tso-font-h1-transform);}'
        . '.post-card-title a,.related-post-title a{color:inherit;}'
        . '.logo img,.custom-logo{width:var(--tso-logo-width);max-width:100%;height:auto;}'
        . '@media screen and (max-width:768px){.logo img,.custom-logo{width:var(--tso-logo-width-mobile);max-width:100%;}}'
        . '#page-wrapper{max-width:var(--tso-layout-max);}'
        . '.widget-area{--tso-sidebar-pad-x:16px;width:calc(var(--tso-sidebar-width) + (2 * var(--tso-sidebar-pad-x)));flex:0 0 calc(var(--tso-sidebar-width) + (2 * var(--tso-sidebar-pad-x)));max-width:100%;box-sizing:border-box;background-color:var(--tso-sidebar-bg);border-radius:var(--tso-sidebar-radius);}'
        . '.widget-area .widget{background:var(--tso-sidebar-widget-bg);}'
        . '.widget-area .widget-title,.widget-area .widget .widget-title,.widget-area .wp-block-heading,.widget-area .widget .wp-block-heading,.widget-area .widget h1,.widget-area .widget h2,.widget-area .widget h3,.widget-area .widget h4,.widget-area .widget h5,.widget-area .widget h6,.widget-area h1,.widget-area h2,.widget-area h3,.widget-area h4,.widget-area h5,.widget-area h6{background:var(--tso-sidebar-title-bg)!important;}'
        . '.single-layout,.archive-layout,.main-container{gap:var(--tso-sidebar-gap);}'
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
