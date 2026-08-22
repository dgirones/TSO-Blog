<?php
/**
 * functions.php — Tu Soporte Online v1.0
 * Tema limpio. Compatible con Jetpack, LiteSpeed Cache, WP Super Cache y W3TC.
 *
 * Theme version is fixed at 1.0 — do not bump the version number.
 */

if ( ! defined( 'TSO_THEME_VERSION' ) ) {
    define( 'TSO_THEME_VERSION', '1.0' );
}

/* ============================================================
   1. CONFIGURACIÓN DEL TEMA
   ============================================================ */
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
    add_theme_support( 'custom-header', array(
        'default-image'      => '',
        'width'              => 1060,
        'height'             => 200,
        'flex-height'        => true,
        'flex-width'         => true,
        'default-text-color' => '333333',
        'header-text'        => false,
    ) );

    add_image_size( 'tso-card-thumb', 400, 280, true );
    add_image_size( 'related-thumb',   360, 200, true );

    register_nav_menus( array( 'main-menu' => 'Menú Principal' ) );

    add_editor_style( 'editor-style.css' );
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
function tso_get_asset_version( $relative_path ) {
    $path = get_stylesheet_directory() . '/' . ltrim( $relative_path, '/' );
    return file_exists( $path ) ? (string) filemtime( $path ) : TSO_THEME_VERSION;
}

/**
 * Theme color defaults and Customizer values.
 *
 * @return array<string, string>
 */
function tso_get_theme_colors() {
    $defaults = array(
        'accent'  => '#d6993a',
        'primary' => '#1e73be',
        'body_bg' => '#d6993a',
    );

    return array(
        'accent'  => sanitize_hex_color( get_theme_mod( 'tso_color_accent', $defaults['accent'] ) ) ?: $defaults['accent'],
        'primary' => sanitize_hex_color( get_theme_mod( 'tso_color_primary', $defaults['primary'] ) ) ?: $defaults['primary'],
        'body_bg' => sanitize_hex_color( get_theme_mod( 'tso_color_body_bg', $defaults['body_bg'] ) ) ?: $defaults['body_bg'],
    );
}

/**
 * CSS custom properties for frontend + Customizer overrides.
 *
 * @return string
 */
function tso_get_theme_css_variables() {
    $colors = tso_get_theme_colors();

    return ':root{'
        . '--tso-color-accent:' . $colors['accent'] . ';'
        . '--tso-color-primary:' . $colors['primary'] . ';'
        . '--tso-color-body-bg:' . $colors['body_bg'] . ';'
        . '--tso-color-accent-hover:#b5782e;'
        . '--tso-color-text:#333333;'
        . '--tso-color-text-muted:#666666;'
        . '--tso-color-white:#ffffff;'
        . '--tso-color-border:#e0e0e0;'
        . '--tso-color-surface:#ffffff;'
        . '--tso-layout-max:1060px;'
        . '}'
        . 'body{background-color:var(--tso-color-body-bg);}';
}

/**
 * Sync block editor palette with Customizer colors (theme.json).
 *
 * @param WP_Theme_JSON_Data $theme_json Theme JSON data.
 * @return WP_Theme_JSON_Data
 */
function tso_sync_theme_json_colors( $theme_json ) {
    if ( ! class_exists( 'WP_Theme_JSON_Data' ) ) {
        return $theme_json;
    }

    $colors  = tso_get_theme_colors();
    $data    = $theme_json->get_data();
    $palette = array(
        array(
            'slug'  => 'primary',
            'color' => $colors['primary'],
            'name'  => __( 'Primary', 'tso-theme' ),
        ),
        array(
            'slug'  => 'accent',
            'color' => $colors['accent'],
            'name'  => __( 'Accent', 'tso-theme' ),
        ),
        array(
            'slug'  => 'body-bg',
            'color' => $colors['body_bg'],
            'name'  => __( 'Body background', 'tso-theme' ),
        ),
        array(
            'slug'  => 'white',
            'color' => '#ffffff',
            'name'  => __( 'White', 'tso-theme' ),
        ),
        array(
            'slug'  => 'text',
            'color' => '#333333',
            'name'  => __( 'Text', 'tso-theme' ),
        ),
        array(
            'slug'  => 'text-muted',
            'color' => '#666666',
            'name'  => __( 'Muted text', 'tso-theme' ),
        ),
    );

    if ( ! isset( $data['settings'] ) ) {
        $data['settings'] = array();
    }
    if ( ! isset( $data['settings']['color'] ) ) {
        $data['settings']['color'] = array();
    }

    $data['settings']['color']['palette'] = $palette;

    return new WP_Theme_JSON_Data( $data, 'theme' );
}
add_filter( 'wp_theme_json_data_theme', 'tso_sync_theme_json_colors' );

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'tso-style', get_stylesheet_uri(), array(), tso_get_asset_version( 'style.css' ) );
    wp_add_inline_style( 'tso-style', tso_get_theme_css_variables() );

    if ( is_singular() && comments_open() ) {
        wp_enqueue_script( 'comment-reply' );
    }

    $theme_uri = get_stylesheet_directory_uri();

    wp_enqueue_script(
        'tso-live-search',
        $theme_uri . '/tso-live-search.js',
        array(),
        tso_get_asset_version( 'tso-live-search.js' ),
        true
    );
    wp_localize_script(
        'tso-live-search',
        'tsoSearch',
        array(
            'ajaxurl'          => admin_url( 'admin-ajax.php' ),
            'nonce'            => wp_create_nonce( 'tso_live_search_nonce' ),
            'loadMoreCacheKey' => 'tso_loadmore_state',
        )
    );

    if ( is_home() ) {
        wp_enqueue_script(
            'tso-load-more',
            $theme_uri . '/tso-load-more.js',
            array(),
            tso_get_asset_version( 'tso-load-more.js' ),
            true
        );
        wp_localize_script(
            'tso-load-more',
            'tsoLoadMore',
            array(
                'cacheKey'    => 'tso_loadmore_state',
                'loadText'    => tso_load_more_text(),
                'loadingText' => __( 'Cargando...', 'tso-theme' ),
            )
        );
    }

    wp_enqueue_script(
        'tso-widget-css-fix',
        $theme_uri . '/tso-widget-css-fix.js',
        array(),
        tso_get_asset_version( 'tso-widget-css-fix.js' ),
        true
    );

    if ( is_singular() ) {
        $lightbox = get_stylesheet_directory() . '/tso-lightbox.js';
        if ( file_exists( $lightbox ) ) {
            wp_enqueue_script(
                'tso-lightbox',
                $theme_uri . '/tso-lightbox.js',
                array(),
                tso_get_asset_version( 'tso-lightbox.js' ),
                true
            );
        }
    }
} );

/* ============================================================
   3. LIMPIEZA DEL <HEAD>
   ============================================================
   NOTA: remove_action sobre hooks de wp_head (oembed, rsd,
   shortlink, generator) és territori de plugins per als estàndards
   de WordPress.org. Mou-les a un mu-plugin si les necessites.
   ============================================================ */
remove_action( 'wp_head',             'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles',     'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles',  'print_emoji_styles' );
add_filter(    'the_generator',       '__return_false' );

add_action( 'wp_enqueue_scripts', function() {
    if ( ! is_user_logged_in() ) {
        wp_deregister_style( 'dashicons' );
    }
} );

add_filter( 'wp_lazy_loading_enabled', '__return_true' );

/*
 * WP_POST_REVISIONS and DISALLOW_FILE_EDIT belong in wp-config.php
 * (see readme.txt → Installation). Do not define them in the theme.
 */

/* ============================================================
   4. SEGURIDAD
   ============================================================ */
add_action( 'send_headers', function() {
    if ( ! is_admin() ) {
        header( 'X-Content-Type-Options: nosniff' );
        header( 'X-Frame-Options: SAMEORIGIN' );
        header( 'Referrer-Policy: strict-origin-when-cross-origin' );
    }
} );

/* ============================================================
   5. ACCESIBILIDAD — SKIP TO CONTENT
   ============================================================ */
add_action( 'wp_body_open', function() {
    echo '<a class="skip-to-content" href="#primary">' . esc_html__( 'Saltar al contenido', 'tso-theme' ) . '</a>';
} );

/* ============================================================
   6. SIDEBARS
   ============================================================ */
add_action( 'widgets_init', function() {
    $widget_args = array(
        'before_widget' => '<div class="widget" id="%1$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    );

    register_sidebar( array_merge( $widget_args, array(
        'name' => 'Sidebar Noticias',
        'id'   => 'sidebar-1',
    ) ) );
    register_sidebar( array_merge( $widget_args, array(
        'name' => 'Footer Izquierda',
        'id'   => 'footer-1',
    ) ) );
    register_sidebar( array_merge( $widget_args, array(
        'name' => 'Footer Centro',
        'id'   => 'footer-2',
    ) ) );
    register_sidebar( array_merge( $widget_args, array(
        'name' => 'Footer Derecha',
        'id'   => 'footer-3',
    ) ) );
} );

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
function tso_comment_form_defaults_es( $defaults ) {
    if ( ! is_singular( 'post' ) ) {
        return $defaults;
    }
    $defaults['title_reply']          = __( 'Deja un comentario', 'tso-theme' );
    $defaults['title_reply_to']       = __( 'Responder a %s', 'tso-theme' );
    $defaults['cancel_reply_link']    = __( 'Cancelar respuesta', 'tso-theme' );
    $defaults['label_submit']         = __( 'Publicar comentario', 'tso-theme' );
    $defaults['comment_notes_before'] = '<p class="comment-notes">' . __( 'Tu dirección de correo electrónico no será publicada. Los campos obligatorios están marcados con', 'tso-theme' ) . ' <span aria-hidden="true">*</span></p>';
    $defaults['comment_notes_after']  = '';
    return $defaults;
}
add_filter( 'comment_form_defaults', 'tso_comment_form_defaults_es' );

/**
 * Translate comment form field labels without breaking HTML attributes.
 *
 * @param array $fields Comment form fields.
 * @return array
 */
function tso_comment_form_fields_es( $fields ) {
    if ( ! is_singular( 'post' ) ) {
        return $fields;
    }
    if ( isset( $fields['author'] ) ) {
        $fields['author'] = preg_replace(
            '/<label for="author">(.*?)<\/label>/',
            '<label for="author">' . esc_html__( 'Nombre', 'tso-theme' ) . ' <span class="required" aria-hidden="true">*</span></label>',
            $fields['author'],
            1
        );
    }
    if ( isset( $fields['email'] ) ) {
        $fields['email'] = preg_replace(
            '/<label for="email">(.*?)<\/label>/',
            '<label for="email">' . esc_html__( 'Correo electrónico', 'tso-theme' ) . ' <span class="required" aria-hidden="true">*</span></label>',
            $fields['email'],
            1
        );
    }
    if ( isset( $fields['url'] ) ) {
        $fields['url'] = preg_replace(
            '/<label for="url">(.*?)<\/label>/',
            '<label for="url">' . esc_html__( 'Sitio web', 'tso-theme' ) . '</label>',
            $fields['url'],
            1
        );
    }
    return $fields;
}
add_filter( 'comment_form_default_fields', 'tso_comment_form_fields_es', 20 );

/**
 * Translate the comment textarea label.
 *
 * @param string $field Comment field HTML.
 * @return string
 */
function tso_comment_form_field_comment_es( $field ) {
    if ( ! is_singular( 'post' ) ) {
        return $field;
    }
    return preg_replace(
        '/<label for="comment">(.*?)<\/label>/',
        '<label for="comment">' . esc_html__( 'Comentario', 'tso-theme' ) . ' <span class="required" aria-hidden="true">*</span></label>',
        $field,
        1
    );
}
add_filter( 'comment_form_field_comment', 'tso_comment_form_field_comment_es' );

/**
 * Open links inside comment body in a new tab (security: noopener noreferrer).
 *
 * Runs after WordPress make_clickable() on comment_text.
 *
 * @param string $text Comment HTML.
 * @return string
 */
function tso_comment_links_open_new_tab( $text ) {
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
add_filter( 'comment_text', 'tso_comment_links_open_new_tab', 99 );

/**
 * Open comment author website links in a new tab.
 *
 * @param string $link    Author link HTML.
 * @param string $author  Author name.
 * @param int    $comment_id Comment ID.
 * @return string
 */
function tso_comment_author_link_new_tab( $link, $author, $comment_id ) {
    unset( $author, $comment_id );
    if ( '' === $link || false === stripos( $link, '<a' ) ) {
        return $link;
    }
    return tso_comment_links_open_new_tab( $link );
}
add_filter( 'get_comment_author_link', 'tso_comment_author_link_new_tab', 10, 3 );

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
add_action( 'customize_register', function( $wp_customize ) {

    /* Redes sociales */
    $wp_customize->add_section( 'tso_social_section', array(
        'title'    => '🔗 Redes Sociales (iconos header)',
        'priority' => 30,
    ) );
    foreach ( array( 'youtube' => 'YouTube', 'linkedin' => 'LinkedIn', 'facebook' => 'Facebook', 'twitter' => 'X / Twitter', 'instagram' => 'Instagram' ) as $key => $label ) {
        $wp_customize->add_setting( 'tso_social_' . $key, array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'refresh',
        ) );
        $wp_customize->add_control( 'tso_social_' . $key, array(
            'label'   => $label . ' (URL completa)',
            'section' => 'tso_social_section',
            'type'    => 'url',
        ) );
    }

    /* Colores */
    $wp_customize->add_section( 'tso_colors_section', array(
        'title'    => '🎨 Colores del tema',
        'priority' => 35,
    ) );
    foreach ( array(
        'tso_color_accent'   => array( 'default' => '#d6993a', 'label' => 'Color de acento (naranja)' ),
        'tso_color_primary'  => array( 'default' => '#1e73be', 'label' => 'Color principal (azul)' ),
        'tso_color_body_bg'  => array( 'default' => '#d6993a', 'label' => 'Color fondo exterior' ),
    ) as $setting_id => $config ) {
        $wp_customize->add_setting( $setting_id, array(
            'default'           => $config['default'],
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'refresh',
        ) );
        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $setting_id, array(
            'label'   => $config['label'],
            'section' => 'tso_colors_section',
        ) ) );
    }

    /* Textos */
    $wp_customize->add_section( 'tso_texts_section', array(
        'title'    => '✏️ Textos del tema',
        'priority' => 40,
    ) );
    $wp_customize->add_setting( 'tso_text_load_more', array(
        'default'           => 'Cargar más',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'tso_text_load_more', array(
        'label'   => 'Texto botón "Cargar más"',
        'section' => 'tso_texts_section',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'tso_footer_copyright', array(
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'tso_footer_copyright', array(
        'label'   => 'Texto copyright del footer',
        'section' => 'tso_texts_section',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'tso_footer_legal', array(
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'tso_footer_legal', array(
        'label'   => 'Texto legal del footer (HTML permitido)',
        'section' => 'tso_texts_section',
        'type'    => 'textarea',
    ) );

    /* Relacionados */
    $wp_customize->add_section( 'tso_related_section', array(
        'title'    => '📰 Artículos relacionados',
        'priority' => 45,
    ) );
    $wp_customize->add_setting( 'tso_related_count', array(
        'default'           => 3,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'tso_related_count', array(
        'label'       => 'Número de artículos relacionados (1–6)',
        'section'     => 'tso_related_section',
        'type'        => 'number',
        'input_attrs' => array( 'min' => 1, 'max' => 6, 'step' => 1 ),
    ) );
    $wp_customize->add_setting( 'tso_related_title', array(
        'default'           => 'Artículos relacionados',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'tso_related_title', array(
        'label'   => 'Título sección relacionados',
        'section' => 'tso_related_section',
        'type'    => 'text',
    ) );

    /* Rendimiento */
    $wp_customize->add_section( 'tso_performance_section', array(
        'title'    => '⚡ Rendimiento',
        'priority' => 50,
    ) );
    $wp_customize->add_setting( 'tso_disable_xmlrpc', array(
        'default'           => 0,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'tso_disable_xmlrpc', array(
        'label'       => 'Deshabilitar XML-RPC',
        'description' => '⚠️ Solo si NO usas Jetpack.',
        'section'     => 'tso_performance_section',
        'type'        => 'checkbox',
    ) );
} );

add_action( 'init', function() {
    if ( get_theme_mod( 'tso_disable_xmlrpc', 0 ) ) {
        add_filter( 'xmlrpc_enabled', '__return_false' );
    }
}, 5 );

/* ============================================================
   11. REDES SOCIALES — ICONOS CON ESTILOS INLINE (FOUC-proof)
   Los tamaños se fijan en style="" directamente en el HTML.
   Ningún plugin de caché puede diferir o mover atributos HTML.
   ============================================================ */
function tso_social_icons() {
    static $svgs = array(
        'youtube'   => array( 'label' => 'YouTube',     'path' => '<path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.6A3 3 0 0 0 .5 6.2C0 8.1 0 12 0 12s0 3.9.5 5.8a3 3 0 0 0 2.1 2.1c1.9.6 9.4.6 9.4.6s7.5 0 9.4-.6a3 3 0 0 0 2.1-2.1C24 15.9 24 12 24 12s0-3.9-.5-5.8zM9.75 15.5v-7l6.5 3.5-6.5 3.5z"/>' ),
        'linkedin'  => array( 'label' => 'LinkedIn',    'path' => '<path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.13 1.45-2.13 2.94v5.67H9.37V9h3.41v1.56h.05a3.74 3.74 0 0 1 3.37-1.85c3.6 0 4.27 2.37 4.27 5.45v6.29zM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12zM7.12 20.45H3.56V9h3.56v11.45zM22.22 0H1.77A1.75 1.75 0 0 0 0 1.73v20.54A1.75 1.75 0 0 0 1.77 24h20.45A1.76 1.76 0 0 0 24 22.27V1.73A1.76 1.76 0 0 0 22.22 0z"/>' ),
        'facebook'  => array( 'label' => 'Facebook',    'path' => '<path d="M24 12.07C24 5.41 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.04V9.41c0-3.02 1.8-4.7 4.54-4.7 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.95.93-1.95 1.88v2.26h3.32l-.53 3.5h-2.79V24C19.61 23.1 24 18.1 24 12.07z"/>' ),
        'twitter'   => array( 'label' => 'X / Twitter', 'path' => '<path d="M18.24 2h3.28L13.9 10.28 22.8 22h-6.91l-5.45-7.14L4.24 22H.95l8.1-9.27L.54 2h7.08l4.93 6.51L18.24 2zm-1.15 18h1.82L7 3.92H5.06L17.09 20z"/>' ),
        'instagram' => array( 'label' => 'Instagram',   'path' => '<path d="M12 2.16c3.2 0 3.58.01 4.85.07 3.25.15 4.77 1.69 4.92 4.92.06 1.27.07 1.65.07 4.85 0 3.2-.01 3.58-.07 4.85-.15 3.23-1.66 4.77-4.92 4.92-1.27.06-1.64.07-4.85.07-3.2 0-3.58-.01-4.85-.07-3.26-.15-4.77-1.7-4.92-4.92C2.17 15.58 2.16 15.2 2.16 12c0-3.2.01-3.58.07-4.85C2.38 3.7 3.9 2.16 7.15 2.09 8.42 2.17 8.8 2.16 12 2.16zM12 0C8.74 0 8.33.01 7.05.07 2.7.27.27 2.7.07 7.05.01 8.33 0 8.74 0 12c0 3.26.01 3.67.07 4.95.2 4.36 2.62 6.78 6.98 6.98C8.33 23.99 8.74 24 12 24c3.26 0 3.67-.01 4.95-.07 4.35-.2 6.78-2.62 6.98-6.98.06-1.28.07-1.69.07-4.95 0-3.26-.01-3.67-.07-4.95-.2-4.35-2.62-6.78-6.98-6.98C15.67.01 15.26 0 12 0zm0 5.84a6.16 6.16 0 1 0 0 12.32A6.16 6.16 0 0 0 12 5.84zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.4-11.85a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88z"/>' ),
    );

    $has_any = false;
    foreach ( array_keys( $svgs ) as $key ) {
        if ( get_theme_mod( 'tso_social_' . $key, '' ) ) { $has_any = true; break; }
    }
    if ( ! $has_any ) return;

    // Estilos inline en cada elemento: imposible diferir para LiteSpeed u otro plugin de caché
    $a_style   = 'display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background-color:var(--tso-color-primary);overflow:hidden;flex-shrink:0;text-decoration:none;';
    $svg_style = 'width:15px;height:15px;fill:#ffffff;display:block;flex-shrink:0;pointer-events:none;';

    echo '<div class="header-social-icons" aria-label="Redes sociales">';
    foreach ( $svgs as $key => $data ) {
        $url = get_theme_mod( 'tso_social_' . $key, '' );
        if ( ! $url ) continue;
        printf(
            '<a href="%s" class="social-icon social-icon--%s" style="%s" target="_blank" rel="noopener noreferrer" aria-label="%s">'
            . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="%s" aria-hidden="true" focusable="false">%s</svg>'
            . '</a>',
            esc_url( $url ),
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
    echo '<meta property="og:type"        content="article" />' . "\n";
    echo '<meta property="og:title"       content="' . esc_attr( $title ) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr( $description ) . '" />' . "\n";
    echo '<meta property="og:url"         content="' . esc_url( $url ) . '" />' . "\n";
    echo '<meta property="og:site_name"   content="' . esc_attr( $site_name ) . '" />' . "\n";
    echo '<meta name="description"        content="' . esc_attr( $description ) . '" />' . "\n";
    if ( has_post_thumbnail() ) {
        $thumb = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
        if ( $thumb ) {
            echo '<meta property="og:image"        content="' . esc_url( $thumb[0] ) . '" />' . "\n";
        }
    }
}, 5 );

/* ============================================================
   14. AJAX — BUSCADOR EN VIVO
   ============================================================ */
add_action( 'wp_ajax_tso_live_search',        'tso_live_search_callback' );
add_action( 'wp_ajax_nopriv_tso_live_search', 'tso_live_search_callback' );

function tso_live_search_callback() {
    check_ajax_referer( 'tso_live_search_nonce', 'nonce' );
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
        $query_args['tso_title_only'] = true;
        add_filter( 'posts_search', 'tso_live_search_title_only', 10, 2 );
    }

    $query = new WP_Query( $query_args );

    if ( isset( $query_args['tso_title_only'] ) ) {
        remove_filter( 'posts_search', 'tso_live_search_title_only', 10 );
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
function tso_live_search_title_only( $search, $wp_query ) {
    if ( ! $wp_query->get( 'tso_title_only' ) ) {
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
function tso_footer_copyright_text() {
    $custom = (string) get_theme_mod( 'tso_footer_copyright', '' );
    echo $custom ? wp_kses_post( $custom ) : '&copy; ' . esc_html( current_time( 'Y' ) ) . ' ' . esc_html( get_bloginfo( 'name' ) );
}

function tso_footer_legal_text() {
    $legal = (string) get_theme_mod( 'tso_footer_legal', '' );
    if ( '' !== $legal ) {
        echo '<div class="footer-legal">' . wp_kses_post( $legal ) . '</div>';
    }
}

function tso_load_more_text() {
    return esc_html( (string) get_theme_mod( 'tso_text_load_more', 'Cargar más' ) );
}

function tso_related_count() {
    return max( 1, min( 6, (int) get_theme_mod( 'tso_related_count', 3 ) ) );
}

function tso_related_title() {
    return esc_html( (string) get_theme_mod( 'tso_related_title', 'Artículos relacionados' ) );
}

/**
 * Whether Jetpack plugin is active.
 *
 * @return bool
 */
function tso_is_jetpack_active() {
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
if ( tso_is_jetpack_active() ) {
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
            $src  = isset( $m[5] ) ? (string) $m[5] : '';

            if ( preg_match( '/\.(jpe?g|png|gif|webp|avif|svg)(\?[^"\']*)?$/i', $href ) ) {
                return $m[0];
            }

            $new_href = esc_url( $src );
            return '<a' . $m[1] . 'href="' . $new_href . '"' . $m[3] . '>' . $m[4] . $src . $m[6] . '</a>';
        },
        $html
    );

    return ( null !== $result ) ? $result : $html;
}, 10, 2 );

/* ============================================================
   20. BLOC IMATGE — enllaç a mida completa per al lightbox
   ============================================================
   Quan el bloc Imatge no té enllaç (linkDestination: none),
   Gutenberg renderitza només <img> sense <a>. El lightbox
   necessita un enllaç o un clic directe sobre la imatge.
   ============================================================ */
add_filter( 'render_block', function( $html, $block ) {
    $block_name = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';
    if ( 'core/image' !== $block_name ) {
        return $html;
    }

    $html = (string) $html;
    if ( '' === $html || false !== stripos( $html, '<a ' ) ) {
        return $html;
    }

    $full_url = '';
    $attachment_id = isset( $block['attrs']['id'] ) ? absint( $block['attrs']['id'] ) : 0;
    if ( $attachment_id ) {
        $src = wp_get_attachment_image_src( $attachment_id, 'full' );
        if ( is_array( $src ) && ! empty( $src[0] ) ) {
            $full_url = $src[0];
        }
    }

    $result = preg_replace_callback(
        '/(<figure[^>]*\bwp-block-image\b[^>]*>)\s*(<img\s[^>]+\/?>)/iU',
        function( $matches ) use ( $full_url ) {
            $img_tag  = $matches[2];
            $link_url = $full_url;

            if ( '' === $link_url && preg_match( '/\bsrc=["\']([^"\']+)["\']/', $img_tag, $src_match ) ) {
                $link_url = $src_match[1];
            }

            if ( '' === $link_url ) {
                return $matches[0];
            }

            return $matches[1] . '<a href="' . esc_url( $link_url ) . '">' . $img_tag . '</a>';
        },
        $html
    );

    return ( null !== $result && $result !== $html ) ? $result : $html;
}, 10, 2 );

/* ============================================================
   24. JETPACK TILED GALLERY — afegir links per al lightbox
   ============================================================
   jetpack/tiled-gallery genera <figure><img data-url="...">
   sense cap <a> — el lightbox no pot interceptar res.
   Afegim un <a href="data-url"> al voltant de cada <img>.
   ============================================================ */
add_filter( 'render_block', function( $html, $block ) {
    if ( ! tso_is_jetpack_active() ) {
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
        '/(<figure[^>]*class="tiled-gallery__item"[^>]*>)\s*(<img\s[^>]*>)\s*(<\/figure>)/iU',
        function( $m ) {
            $img_tag = isset( $m[2] ) ? (string) $m[2] : '';

            $url = '';
            if ( preg_match( '/\bdata-url=["\']([^"\']+)["\']/', $img_tag, $u ) ) {
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
    if ( ! tso_is_jetpack_active() ) {
        return;
    }
    $js = get_stylesheet_directory() . '/tso-tiled-gallery-fix.js';
    if ( ! file_exists( $js ) ) {
        return;
    }
    wp_enqueue_script(
        'tso-tiled-gallery-fix',
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
        'label' => __( 'Acento TSO', 'tso-theme' ),
    ) );

    // Cita — variant de barra lateral (blockquote amb línia esquerra)
    register_block_style( 'core/quote', array(
        'name'  => 'tso-sidebar-quote',
        'label' => __( 'Barra lateral', 'tso-theme' ),
    ) );

    // Separador — variant d'ombra curta
    register_block_style( 'core/separator', array(
        'name'  => 'tso-short',
        'label' => __( 'Curt centrat', 'tso-theme' ),
    ) );

    // Imatge — variant amb ombra suau
    register_block_style( 'core/image', array(
        'name'  => 'tso-shadow',
        'label' => __( 'Amb ombra', 'tso-theme' ),
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
    register_block_pattern_category( 'tso-theme', array(
        'label' => __( 'Tu Soporte Online', 'tso-theme' ),
    ) );

    // Patró: crida a l'acció (CTA) centrada
    register_block_pattern( 'tso-theme/cta-centered', array(
        'title'       => __( 'CTA centrada', 'tso-theme' ),
        'description' => __( 'Bloc de crida a l\'acció amb títol, text i botó centrats.', 'tso-theme' ),
        'categories'  => array( 'tso-theme', 'call-to-action' ),
        'content'     => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"40px","bottom":"40px"}}},"backgroundColor":"primary","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-primary-background-color has-background" style="padding-top:40px;padding-bottom:40px">
<!-- wp:heading {"textAlign":"center","level":2} -->
<h2 class="wp-block-heading has-text-align-center">' . esc_html__( '¿Necesitas ayuda?', 'tso-theme' ) . '</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Estamos aquí para ayudarte. Contacta con nosotros y te responderemos en menos de 24 horas.', 'tso-theme' ) . '</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button">' . esc_html__( 'Contactar ahora', 'tso-theme' ) . '</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->',
    ) );

    // Patró: dues columnes d'informació
    register_block_pattern( 'tso-theme/two-columns-info', array(
        'title'       => __( 'Dues columnes informació', 'tso-theme' ),
        'description' => __( 'Dues columnes amb títol i text per comparar serveis o característiques.', 'tso-theme' ),
        'categories'  => array( 'tso-theme', 'columns' ),
        'content'     => '<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Característica 1', 'tso-theme' ) . '</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>' . esc_html__( 'Descripción de la primera característica o servicio destacado.', 'tso-theme' ) . '</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Característica 2', 'tso-theme' ) . '</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>' . esc_html__( 'Descripción de la segunda característica o servicio destacado.', 'tso-theme' ) . '</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->',
    ) );

} );
