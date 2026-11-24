<?php
/**
 * Layout body classes and Contact page template settings (details, form shortcode, map).
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
 * Body class for blog / home posts layout.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function tsothm_blog_layout_body_class( $classes ) {
    if ( is_home() || is_archive() || is_search() ) {
        $classes[] = 'tsothm-blog-layout-' . tsothm_get_blog_layout();
    }
    return $classes;
}
add_filter( 'body_class', 'tsothm_blog_layout_body_class' );

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
 * Body classes for sidebar visibility and background.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function tsothm_sidebar_body_class( $classes ) {
    $s = tsothm_get_sidebar_settings();

    if ( ! tsothm_is_sidebar_visible() ) {
        $classes[] = 'tso-no-sidebar';
    }
    if ( ! $s['bg_enabled'] ) {
        $classes[] = 'tso-sidebar-no-bg';
    }
    if ( 'left' === $s['position'] ) {
        $classes[] = 'tso-sidebar-left';
    }
    if ( $s['sticky'] ) {
        $classes[] = 'tso-sidebar-sticky';
    }
    if ( $s['border'] ) {
        $classes[] = 'tso-sidebar-border';
    }
    if ( $s['shadow'] ) {
        $classes[] = 'tso-sidebar-shadow';
    }

    return $classes;
}
add_filter( 'body_class', 'tsothm_sidebar_body_class' );

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
        esc_attr__( 'Location map', 'tso-blog' )
    );
}
