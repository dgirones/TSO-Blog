<?php
/**
 * Theme settings: color defaults, font catalog, layout, typography and sidebar options, with their sanitizers.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
        'announce_text'   => '#1a1a1a',
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

    // Announcement text never customized: follow the bar background so the text stays readable.
    if ( '' === (string) get_theme_mod( 'tsothm_color_announce_text', '' ) ) {
        $colors['announce_text'] = tsothm_get_contrast_color( $colors['announce_bg'] );
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
 * Available font families (system stacks + bundled web fonts in assets/fonts).
 *
 * 'local' is the CSS file name (without .css) inside assets/fonts; empty for system fonts.
 *
 * @return array<string, array{label: string, stack: string, local: string}>
 */
function tsothm_get_font_catalog() {
    return array(
        'arial'         => array(
            'label'  => 'Arial',
            'stack'  => 'Arial, Helvetica, sans-serif',
            'local'  => '',
        ),
        'system'        => array(
            'label'  => 'System UI',
            'stack'  => 'system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif',
            'local'  => '',
        ),
        'georgia'       => array(
            'label'  => 'Georgia',
            'stack'  => 'Georgia, "Times New Roman", Times, serif',
            'local'  => '',
        ),
        'verdana'       => array(
            'label'  => 'Verdana',
            'stack'  => 'Verdana, Geneva, sans-serif',
            'local'  => '',
        ),
        'trebuchet'     => array(
            'label'  => 'Trebuchet MS',
            'stack'  => '"Trebuchet MS", Helvetica, sans-serif',
            'local'  => '',
        ),
        'roboto'        => array(
            'label'  => 'Roboto',
            'stack'  => '"Roboto", Arial, Helvetica, sans-serif',
            'local'  => 'roboto',
        ),
        'open-sans'     => array(
            'label'  => 'Open Sans',
            'stack'  => '"Open Sans", Arial, Helvetica, sans-serif',
            'local'  => 'open-sans',
        ),
        'lato'          => array(
            'label'  => 'Lato',
            'stack'  => '"Lato", Arial, Helvetica, sans-serif',
            'local'  => 'lato',
        ),
        'nunito'        => array(
            'label'  => 'Nunito',
            'stack'  => '"Nunito", Arial, Helvetica, sans-serif',
            'local'  => 'nunito',
        ),
        'merriweather'  => array(
            'label'  => 'Merriweather',
            'stack'  => '"Merriweather", Georgia, serif',
            'local'  => 'merriweather',
        ),
        'roboto-slab'   => array(
            'label'  => 'Roboto Slab',
            'stack'  => '"Roboto Slab", Georgia, serif',
            'local'  => 'roboto-slab',
        ),
        'source-serif'  => array(
            'label'  => 'Source Serif 4',
            'stack'  => '"Source Serif 4", Georgia, serif',
            'local'  => 'source-serif-4',
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
 * Sanitize letter-spacing value for em (0–0.2), unitless for Customizer number controls.
 *
 * @param mixed $value Raw value.
 * @return string e.g. 0.03
 */
function tsothm_sanitize_letter_spacing_em_value( $value ) {
    $n = (float) preg_replace( '/[^0-9.]/', '', (string) $value );
    if ( $n < 0 || $n > 0.2 ) {
        $n = 0.03;
    }
    return (string) round( $n, 3 );
}

/**
 * Sanitize letter-spacing value for px (0–3), unitless for Customizer number controls.
 *
 * @param mixed $value Raw value.
 * @return string e.g. 0.5
 */
function tsothm_sanitize_letter_spacing_px_value( $value ) {
    $n = (float) preg_replace( '/[^0-9.]/', '', (string) $value );
    if ( $n < 0 || $n > 3 ) {
        $n = 0.5;
    }
    return (string) round( $n, 2 );
}

/**
 * Sanitize announcement / nav font size (12–28 px).
 *
 * @param mixed $value   Raw value.
 * @param int   $default Fallback.
 * @return string e.g. 16px
 */
function tsothm_sanitize_ui_font_size( $value, $default = 16 ) {
    $n = (int) preg_replace( '/[^0-9]/', '', (string) $value );
    $n = max( 12, min( 28, $n ? $n : (int) $default ) );
    return $n . 'px';
}

/**
 * Sanitize card title size (14–28 px).
 *
 * @param mixed $value Raw value.
 * @return string e.g. 16px
 */
function tsothm_sanitize_card_title_size( $value ) {
    $n = (int) preg_replace( '/[^0-9]/', '', (string) $value );
    $n = max( 14, min( 28, $n ? $n : 16 ) );
    return $n . 'px';
}

/**
 * Sanitize logo width in px.
 *
 * @param mixed $value   Raw value.
 * @param int   $default Fallback if empty/invalid.
 * @param int   $min     Minimum px.
 * @param int   $max     Maximum px.
 * @return string Digits only (e.g. 300) for number controls; append px in CSS.
 */
function tsothm_sanitize_logo_width( $value, $default = 300, $min = 40, $max = 500 ) {
    $n = (int) preg_replace( '/[^0-9]/', '', (string) $value );
    if ( ! $n ) {
        $n = (int) $default;
    }
    $n = max( (int) $min, min( (int) $max, $n ) );
    return (string) $n;
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
    return tsothm_sanitize_logo_width( $value, 180, 40, 500 );
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
            __( 'default', 'tso-blog' )
        ),
        '1200' => '1200 px',
        '1400' => '1400 px',
        '1600' => '1600 px',
        'full' => __( 'Nearly full screen', 'tso-blog' ),
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
        'raised' => __( 'Classic raised', 'tso-blog' ),
        'carved' => __( 'Carved relief', 'tso-blog' ),
        'pill'      => __( 'Pills', 'tso-blog' ),
        'underline' => __( 'Animated underline', 'tso-blog' ),
        'flat'   => __( 'Flat (no buttons)', 'tso-blog' ),
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
 * Blog / home posts layout choices.
 *
 * @return array<string, string>
 */
function tsothm_get_blog_layout_choices() {
    return array(
        'grid'     => __( 'Grid (2 columns)', 'tso-blog' ),
        'list'     => __( 'List (image + text)', 'tso-blog' ),
        'magazine' => __( 'Magazine (featured + grid)', 'tso-blog' ),
    );
}

/**
 * Sanitize blog layout key.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function tsothm_sanitize_blog_layout( $value ) {
    $value = sanitize_key( (string) $value );
    return isset( tsothm_get_blog_layout_choices()[ $value ] ) ? $value : 'grid';
}

/**
 * Current blog layout key.
 *
 * @return string
 */
function tsothm_get_blog_layout() {
    return tsothm_sanitize_blog_layout( get_theme_mod( 'tsothm_blog_layout', 'grid' ) );
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
    $body_key     = tsothm_sanitize_font_key( get_theme_mod( 'tsothm_font_body', 'arial' ) );
    $head_key     = tsothm_sanitize_font_key( get_theme_mod( 'tsothm_font_heading', 'arial' ) );
    $announce_key = tsothm_sanitize_font_key( get_theme_mod( 'tsothm_font_announce', 'arial' ) );
    $nav_key      = tsothm_sanitize_font_key( get_theme_mod( 'tsothm_font_nav', 'arial' ) );

    $body_color = sanitize_hex_color( get_theme_mod( 'tsothm_font_body_color', '#333333' ) );
    $head_color = sanitize_hex_color( get_theme_mod( 'tsothm_font_heading_color', '#111111' ) );

    return array(
        'body_key'       => $body_key,
        'heading_key'    => $head_key,
        'announce_key'   => $announce_key,
        'nav_key'        => $nav_key,
        'body_stack'     => $catalog[ $body_key ]['stack'],
        'heading_stack'  => $catalog[ $head_key ]['stack'],
        'announce_stack' => $catalog[ $announce_key ]['stack'],
        'nav_stack'      => $catalog[ $nav_key ]['stack'],
        'body_size'      => tsothm_sanitize_font_size_px( get_theme_mod( 'tsothm_font_body_size', '15' ) ),
        'body_line'      => tsothm_sanitize_line_height( get_theme_mod( 'tsothm_font_body_line', '1.6' ) ),
        'body_color'     => $body_color ? $body_color : '#333333',
        'heading_weight' => tsothm_sanitize_font_weight( get_theme_mod( 'tsothm_font_heading_weight', '700' ) ),
        'heading_color'  => $head_color ? $head_color : '#111111',
        'h1_size'              => tsothm_sanitize_font_size_px( get_theme_mod( 'tsothm_font_h1_size', '24' ) ),
        'h1_transform'         => tsothm_sanitize_text_transform( get_theme_mod( 'tsothm_font_h1_transform', 'none' ) ),
        'widget_title_size'    => tsothm_sanitize_widget_font_size( get_theme_mod( 'tsothm_font_widget_title_size', '14' ) ),
        'widget_text_size'     => tsothm_sanitize_widget_font_size( get_theme_mod( 'tsothm_font_widget_text_size', '14' ) ),
        'announce_size'        => tsothm_sanitize_ui_font_size( get_theme_mod( 'tsothm_font_announce_size', '16' ), 16 ),
        'announce_weight'      => tsothm_sanitize_font_weight( get_theme_mod( 'tsothm_font_announce_weight', '800' ) ),
        'announce_transform'   => tsothm_sanitize_text_transform( get_theme_mod( 'tsothm_font_announce_transform', 'uppercase' ) ),
        'announce_tracking'    => tsothm_sanitize_letter_spacing_em_value( get_theme_mod( 'tsothm_font_announce_tracking', '0.03' ) ) . 'em',
        'nav_size'             => tsothm_sanitize_ui_font_size( get_theme_mod( 'tsothm_font_nav_size', '15' ), 15 ),
        'nav_weight'           => tsothm_sanitize_font_weight( get_theme_mod( 'tsothm_font_nav_weight', '700' ) ),
        'nav_transform'        => tsothm_sanitize_text_transform( get_theme_mod( 'tsothm_font_nav_transform', 'uppercase' ) ),
        'nav_tracking'         => tsothm_sanitize_letter_spacing_px_value( get_theme_mod( 'tsothm_font_nav_tracking', '0.5' ) ) . 'px',
        'card_title_size'      => tsothm_sanitize_card_title_size( get_theme_mod( 'tsothm_font_card_title_size', '16' ) ),
        'footer_text_size'     => tsothm_sanitize_widget_font_size( get_theme_mod( 'tsothm_font_footer_text_size', '12' ) ),
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
 * Sanitize sidebar content width (260–360 px).
 *
 * @param mixed $value Raw value.
 * @return string Digits only.
 */
function tsothm_sanitize_sidebar_width( $value ) {
    $n = (int) preg_replace( '/[^0-9]/', '', (string) $value );
    if ( ! $n ) {
        $n = 300;
    }
    return (string) max( 260, min( 360, $n ) );
}

/**
 * Sanitize sidebar gap (10–60 px).
 *
 * @param mixed $value Raw value.
 * @return string Digits only.
 */
function tsothm_sanitize_sidebar_gap( $value ) {
    $n = (int) preg_replace( '/[^0-9]/', '', (string) $value );
    if ( ! $n ) {
        $n = 30;
    }
    return (string) max( 10, min( 60, $n ) );
}

/**
 * Sanitize sidebar border radius (0–24 px).
 *
 * @param mixed $value Raw value.
 * @return string Digits only.
 */
function tsothm_sanitize_sidebar_radius( $value ) {
    $n = (int) preg_replace( '/[^0-9]/', '', (string) $value );
    return (string) max( 0, min( 24, $n ) );
}

/**
 * Sanitize sidebar position (right|left).
 *
 * @param mixed $value Raw value.
 * @return string
 */
function tsothm_sanitize_sidebar_position( $value ) {
    $value = sanitize_key( (string) $value );
    return ( 'left' === $value ) ? 'left' : 'right';
}

/**
 * Resolved sidebar layout / behavior settings.
 *
 * @return array<string, mixed>
 */
function tsothm_get_sidebar_settings() {
    return array(
        'visible'      => (bool) absint( get_theme_mod( 'tsothm_sidebar_visible', 1 ) ),
        'position'     => tsothm_sanitize_sidebar_position( get_theme_mod( 'tsothm_sidebar_position', 'right' ) ),
        'width'        => tsothm_sanitize_sidebar_width( get_theme_mod( 'tsothm_sidebar_width', '300' ) ),
        'gap'          => tsothm_sanitize_sidebar_gap( get_theme_mod( 'tsothm_sidebar_gap', '30' ) ),
        'radius'       => tsothm_sanitize_sidebar_radius( get_theme_mod( 'tsothm_sidebar_radius', '6' ) ),
        'sticky'       => (bool) absint( get_theme_mod( 'tsothm_sidebar_sticky', 0 ) ),
        'border'       => (bool) absint( get_theme_mod( 'tsothm_sidebar_border', 0 ) ),
        'shadow'       => (bool) absint( get_theme_mod( 'tsothm_sidebar_shadow', 0 ) ),
        'on_home'      => (bool) absint( get_theme_mod( 'tsothm_sidebar_on_home', 0 ) ),
        'on_contact'   => (bool) absint( get_theme_mod( 'tsothm_sidebar_on_contact', 0 ) ),
        'on_portfolio' => (bool) absint( get_theme_mod( 'tsothm_sidebar_on_portfolio', 0 ) ),
        'bg_enabled'   => (bool) absint( get_theme_mod( 'tsothm_sidebar_bg_enabled', 1 ) ),
    );
}

/**
 * Whether the news sidebar should render on the current view.
 *
 * @return bool
 */
function tsothm_is_sidebar_visible() {
    $s = tsothm_get_sidebar_settings();
    if ( ! $s['visible'] ) {
        return false;
    }

    if ( is_home() ) {
        return $s['on_home'];
    }

    if ( is_page() && tsothm_is_contact_page() ) {
        return $s['on_contact'];
    }

    if ( is_page() && tsothm_is_portfolio_gallery_page() ) {
        return $s['on_portfolio'];
    }

    return true;
}

/**
 * Whether the sidebar column uses a background fill.
 *
 * @return bool
 */
function tsothm_sidebar_has_background() {
    return (bool) absint( get_theme_mod( 'tsothm_sidebar_bg_enabled', 1 ) );
}

/**
 * Sidebar column background for CSS (hex or transparent). Day mode.
 *
 * @return string
 */
function tsothm_get_sidebar_bg_css() {
    if ( ! tsothm_sidebar_has_background() ) {
        return 'transparent';
    }
    $default = '#f1f1f1';
    $mod     = sanitize_hex_color( get_theme_mod( 'tsothm_color_sidebar_bg', $default ) );
    return $mod ? $mod : $default;
}

/**
 * Night-mode sidebar column background (hex), or empty to use surface-alt.
 *
 * @return string
 */
function tsothm_get_sidebar_bg_night_css() {
    if ( ! tsothm_sidebar_has_background() ) {
        return 'transparent';
    }
    $mod = tsothm_sanitize_optional_hex_color( get_theme_mod( 'tsothm_color_sidebar_bg_night', '' ) );
    return $mod ? $mod : '';
}

/**
 * Widget card background inside the sidebar.
 *
 * @return string
 */
function tsothm_get_sidebar_widget_bg_css() {
    $default = '#ffffff';
    $mod     = sanitize_hex_color( get_theme_mod( 'tsothm_color_sidebar_widget_bg', $default ) );
    return $mod ? $mod : $default;
}

/**
 * Widget title bar background (defaults to theme primary).
 *
 * @param string $primary Primary color hex.
 * @return string
 */
function tsothm_get_sidebar_title_bg_css( $primary = '' ) {
    $mod = tsothm_sanitize_optional_hex_color( get_theme_mod( 'tsothm_color_sidebar_title_bg', '' ) );
    if ( $mod ) {
        return $mod;
    }
    $primary = sanitize_hex_color( $primary );
    return $primary ? $primary : '#1e73be';
}

/**
 * Sanitize optional hex color (empty allowed).
 *
 * @param mixed $value Raw value.
 * @return string Hex or empty string.
 */
function tsothm_sanitize_optional_hex_color( $value ) {
    if ( null === $value || '' === $value ) {
        return '';
    }
    $hex = sanitize_hex_color( $value );
    return $hex ? $hex : '';
}

/**
 * Customizer active_callback: sidebar background color control.
 *
 * @return bool
 */
function tsothm_customize_sidebar_bg_color_active() {
    return tsothm_sidebar_has_background();
}
