<?php
/**
 * Color mode switch: Customizer option, selective refresh partials, markup and color picker eyedropper.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
            'title'       => __( 'Day/night mode', 'tso-blog' ),
            'description' => __( 'Day / night / auto switch shown in the header, next to the search.', 'tso-blog' ),
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
            'label'       => __( 'Show the day / night switch', 'tso-blog' ),
            'description' => __( 'If you turn it off, the site will still follow the visitor\'s system mode, but the button to change it manually will not be shown.', 'tso-blog' ),
            'section'     => 'tsothm_color_mode_section',
            'type'        => 'checkbox',
        )
    );
}
add_action( 'customize_register', 'tsothm_customize_register_color_mode' );

/**
 * Selective refresh partials for front-end Customizer edit shortcuts (pencil).
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function tsothm_customize_register_selective_refresh( $wp_customize ) {
    if ( ! isset( $wp_customize->selective_refresh ) ) {
        return;
    }

    $set_post_message = static function ( $id ) use ( $wp_customize ) {
        $setting = $wp_customize->get_setting( $id );
        if ( $setting ) {
            $setting->transport = 'postMessage';
        }
    };

    // Site title (shown when Site Identity has no Site Logo).
    $set_post_message( 'blogname' );
    $wp_customize->selective_refresh->add_partial(
        'blogname',
        array(
            'selector'            => '.site-title-text',
            'settings'            => array( 'blogname' ),
            'container_inclusive' => false,
            'render_callback'     => static function () {
                echo esc_html( get_bloginfo( 'name', 'display' ) );
            },
            'fallback_refresh'    => false,
        )
    );

    // Header social icons.
    $social_settings = array( 'tsothm_social_order' );
    $set_post_message( 'tsothm_social_order' );
    foreach ( array_keys( tsothm_get_social_networks() ) as $key ) {
        $sid = 'tsothm_social_' . $key;
        $social_settings[] = $sid;
        $set_post_message( $sid );
    }
    $wp_customize->selective_refresh->add_partial(
        'tsothm_social_icons',
        array(
            'selector'            => '#tsothm-social-root',
            'settings'            => $social_settings,
            'container_inclusive' => true,
            'render_callback'     => 'tsothm_customize_partial_social_icons',
            'fallback_refresh'    => true,
        )
    );

    // Day / night switch.
    $set_post_message( 'tsothm_color_mode_switch_enabled' );
    $wp_customize->selective_refresh->add_partial(
        'tsothm_color_mode_switch',
        array(
            'selector'            => '#tsothm-color-mode-root',
            'settings'            => array( 'tsothm_color_mode_switch_enabled' ),
            'container_inclusive' => true,
            'render_callback'     => 'tsothm_customize_partial_color_mode_switch',
            'fallback_refresh'    => true,
        )
    );

    // Custom header image.
    foreach ( array( 'header_image', 'header_image_data' ) as $header_setting ) {
        $set_post_message( $header_setting );
    }
    $wp_customize->selective_refresh->add_partial(
        'tsothm_header_image',
        array(
            'selector'            => '#tsothm-header-image-root',
            'settings'            => array( 'header_image', 'header_image_data' ),
            'container_inclusive' => true,
            'render_callback'     => 'tsothm_customize_partial_header_image',
            'fallback_refresh'    => true,
        )
    );

    // Footer copyright + legal.
    $set_post_message( 'tsothm_footer_copyright' );
    $set_post_message( 'tsothm_footer_legal' );
    $wp_customize->selective_refresh->add_partial(
        'tsothm_footer_texts',
        array(
            'selector'            => '#tsothm-footer-texts-root',
            'settings'            => array( 'tsothm_footer_copyright', 'tsothm_footer_legal', 'tsothm_font_footer_text_size' ),
            'container_inclusive' => true,
            'render_callback'     => 'tsothm_customize_partial_footer_texts',
            'fallback_refresh'    => true,
        )
    );

    // Related posts section title (singles only).
    $set_post_message( 'tsothm_related_title' );
    $wp_customize->selective_refresh->add_partial(
        'tsothm_related_title',
        array(
            'selector'            => '.related-posts-title',
            'settings'            => array( 'tsothm_related_title' ),
            'container_inclusive' => false,
            'render_callback'     => static function () {
                echo esc_html( tsothm_related_title() );
            },
            'fallback_refresh'    => false,
        )
    );

    // «Load more» button (home). Wrapper root so Shift+click is not eaten by the <button>.
    $set_post_message( 'tsothm_text_load_more' );
    $wp_customize->selective_refresh->add_partial(
        'tsothm_text_load_more',
        array(
            'selector'            => '#tsothm-load-more-root',
            'settings'            => array( 'tsothm_text_load_more' ),
            'container_inclusive' => true,
            'render_callback'     => 'tsothm_customize_partial_load_more',
            'fallback_refresh'    => true,
        )
    );
}
add_action( 'customize_register', 'tsothm_customize_register_selective_refresh', 20 );

/**
 * Partial: header social icons root.
 */
function tsothm_customize_partial_social_icons() {
    $empty = empty( tsothm_get_configured_social_icons() );
    printf(
        '<div id="tsothm-social-root"%s>',
        $empty ? ' class="tsothm-social-root--empty"' : ''
    );
    tsothm_social_icons();
    echo '</div>';
}

/**
 * Partial: home Load more button root (Customizer edit shortcut).
 */
function tsothm_customize_partial_load_more() {
    echo '<div id="tsothm-load-more-root" class="load-more-wrapper">';
    tsothm_render_load_more_button();
    echo '</div>';
}

/**
 * Partial: day/night switch root.
 */
function tsothm_customize_partial_color_mode_switch() {
    $enabled = (bool) get_theme_mod( 'tsothm_color_mode_switch_enabled', 1 );
    printf(
        '<div id="tsothm-color-mode-root"%s>',
        $enabled ? '' : ' class="tsothm-color-mode-root--empty"'
    );
    tsothm_render_color_mode_switch();
    echo '</div>';
}

/**
 * Partial: custom header image root.
 */
function tsothm_customize_partial_header_image() {
    $has = (bool) get_header_image();
    printf(
        '<div id="tsothm-header-image-root"%s>',
        $has ? '' : ' class="tsothm-header-image-root--empty"'
    );
    if ( $has ) {
        echo '<div class="header-image">';
        echo '<a href="' . esc_url( home_url( '/' ) ) . '" rel="home">';
        the_header_image_tag(
            array(
                'alt' => get_bloginfo( 'name', 'display' ),
            )
        );
        echo '</a></div>';
    }
    echo '</div>';
}

/**
 * Partial: footer copyright + legal block.
 */
function tsothm_customize_partial_footer_texts() {
    echo '<div id="tsothm-footer-texts-root" class="footer-bottom">';
    echo '<p>';
    tsothm_footer_copyright_text();
    echo '</p>';
    tsothm_footer_legal_text();
    echo '</div>';
}

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
            'label' => __( 'Day mode', 'tso-blog' ),
            'icon'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"></circle><path d="M12 2.5v2.5M12 19v2.5M4.2 4.2l1.8 1.8M18 18l1.8 1.8M1.5 12h2.5M20 12h2.5M4.2 19.8l1.8-1.8M18 6l1.8-1.8"/></svg>',
        ),
        'dark'  => array(
            'label' => __( 'Night mode', 'tso-blog' ),
            'icon'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 14.7A8.5 8.5 0 0 1 9.3 3.5a8.5 8.5 0 1 0 11.2 11.2z"/></svg>',
        ),
        'auto'  => array(
            'label' => __( 'Auto mode', 'tso-blog' ),
            'icon'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"></circle><path d="M12 3.5a8.5 8.5 0 0 1 0 17z" fill="currentColor" stroke="none"></path></svg>',
        ),
    );

    echo '<div class="tso-theme-switch" role="group" aria-label="' . esc_attr__( 'Color mode', 'tso-blog' ) . '">';
    foreach ( $modes as $mode => $data ) {
        echo '<button type="button" class="tso-theme-switch-btn" data-mode="' . esc_attr( $mode ) . '" aria-label="' . esc_attr( $data['label'] ) . '" title="' . esc_attr( $data['label'] ) . '">' . $data['icon'] . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG icon markup defined by the theme.
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
        var label = ( window.tsothmEyedropper && tsothmEyedropper.pickColor ) ? tsothmEyedropper.pickColor : 'Pick a color from the screen';
        var btn = $( '<button type="button" class="button tsothm-eyedropper-btn"></button>' );
        btn.attr( 'aria-label', label );
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
    wp_localize_script(
        'tsothm-customize-eyedropper',
        'tsothmEyedropper',
        array(
            'pickColor' => __( 'Pick a color from the screen', 'tso-blog' ),
        )
    );
    wp_add_inline_script( 'tsothm-customize-eyedropper', tsothm_get_customize_eyedropper_js() );
}
add_action( 'customize_controls_enqueue_scripts', 'tsothm_enqueue_customize_eyedropper' );
