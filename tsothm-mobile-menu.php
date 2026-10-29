<?php
/**
 * Menú móvil moderno (solo móvil) con 2 versiones elegibles en el Personalizador.
 *
 * A) Botón flotante + panel inferior con tarjetas.
 * B) Pantalla completa con botón flotante redondo.
 * C) Barra de pestañas inferior (estilo app) + «Más».
 * D) Panel lateral con pestaña en el borde y enlaces numerados.
 * Clásico = barra horizontal con scroll de siempre (menú móvil nuevo desactivado).
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Mobile menu style choices.
 *
 * @return array<string, string>
 */
function tsothm_get_mobile_menu_style_choices() {
    return array(
        'dock'       => __( 'A · Floating button + bottom panel', 'tso-blog' ),
        'fullscreen' => __( 'B · Full screen', 'tso-blog' ),
        'tabbar'     => __( 'C · Bottom tab bar', 'tso-blog' ),
        'drawer'     => __( 'D · Side panel with tab', 'tso-blog' ),
        'classic'    => __( 'Classic (horizontal bar with scroll)', 'tso-blog' ),
    );
}

/**
 * Sanitize mobile menu style key.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function tsothm_sanitize_mobile_menu_style( $value ) {
    $value = sanitize_key( (string) $value );
    return isset( tsothm_get_mobile_menu_style_choices()[ $value ] ) ? $value : 'dock';
}

/**
 * Current mobile menu style.
 *
 * @return string dock|fullscreen|tabbar|drawer|classic
 */
function tsothm_get_mobile_menu_style() {
    return tsothm_sanitize_mobile_menu_style( get_theme_mod( 'tsothm_mobile_menu_style', 'dock' ) );
}

/**
 * Whether the new mobile menu must be output.
 *
 * @return bool
 */
function tsothm_mobile_menu_is_active() {
    // The widgets screen renders each legacy widget in a preview iframe that loads the theme;
    // the floating menu button must not appear inside those previews.
    if ( function_exists( 'tsothm_is_legacy_widget_preview' ) && tsothm_is_legacy_widget_preview() ) {
        return false;
    }
    return 'classic' !== tsothm_get_mobile_menu_style() && has_nav_menu( 'main-menu' );
}

/**
 * Customizer: section inside Apariencia.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function tsothm_mobile_menu_customize_register( $wp_customize ) {
    $wp_customize->add_section(
        'tsothm_mobile_menu_section',
        array(
            'title'       => __( 'Mobile menu', 'tso-blog' ),
            'description' => __( 'Only affects screens up to 768 px. On desktop the menu does not change.', 'tso-blog' ),
            'panel'       => 'tsothm_appearance_panel',
            'priority'    => 15,
        )
    );

    $wp_customize->add_setting(
        'tsothm_mobile_menu_style',
        array(
            'default'           => 'dock',
            'sanitize_callback' => 'tsothm_sanitize_mobile_menu_style',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_mobile_menu_style',
        array(
            'label'       => __( 'Mobile menu version', 'tso-blog' ),
            'description' => __( 'Uses the menu colors from Colors → Menu (and night mode).', 'tso-blog' ),
            'section'     => 'tsothm_mobile_menu_section',
            'type'        => 'select',
            'choices'     => tsothm_get_mobile_menu_style_choices(),
        )
    );

    $wp_customize->add_setting(
        'tsothm_mobile_menu_label',
        array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_mobile_menu_label',
        array(
            'label'       => __( 'Button label (versions A and C)', 'tso-blog' ),
            'description' => __( 'Leave empty to use the default text (“Menu” or “More”).', 'tso-blog' ),
            'section'     => 'tsothm_mobile_menu_section',
            'type'        => 'text',
        )
    );
}
add_action( 'customize_register', 'tsothm_mobile_menu_customize_register' );

/**
 * Body classes: style + flag used by the CSS to hide the old mobile bar.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function tsothm_mobile_menu_body_class( $classes ) {
    if ( tsothm_mobile_menu_is_active() ) {
        $classes[] = 'tsothm-mm-on';
        $classes[] = 'tsothm-mm-style-' . tsothm_get_mobile_menu_style();
    }
    return $classes;
}
add_filter( 'body_class', 'tsothm_mobile_menu_body_class' );

/**
 * Enqueue CSS/JS only when the new mobile menu is active.
 */
function tsothm_mobile_menu_enqueue() {
    if ( ! tsothm_mobile_menu_is_active() ) {
        return;
    }

    $theme_uri = get_stylesheet_directory_uri();

    wp_enqueue_style(
        'tsothm-mobile-menu',
        $theme_uri . '/tso-mobile-menu.css',
        array( 'tsothm-style' ),
        tsothm_get_asset_version( 'tso-mobile-menu.css' )
    );

    wp_enqueue_script(
        'tsothm-mobile-menu',
        $theme_uri . '/tso-mobile-menu.js',
        array(),
        tsothm_get_asset_version( 'tso-mobile-menu.js' ),
        true
    );
}
add_action( 'wp_enqueue_scripts', 'tsothm_mobile_menu_enqueue' );

/**
 * Render the mobile menu markup in the footer.
 */
function tsothm_mobile_menu_render() {
    if ( ! tsothm_mobile_menu_is_active() ) {
        return;
    }

    $style = tsothm_get_mobile_menu_style();
    $label = trim( (string) get_theme_mod( 'tsothm_mobile_menu_label', '' ) );
    if ( '' === $label ) {
        $label = ( 'tabbar' === $style ) ? __( 'More', 'tso-blog' ) : __( 'Menu', 'tso-blog' );
    }
    ?>
    <div class="tsothm-mm tsothm-mm--<?php echo esc_attr( $style ); ?>" data-sub-label="<?php esc_attr_e( 'Show or hide submenu', 'tso-blog' ); ?>">
        <?php if ( 'tabbar' === $style ) : ?>
            <div class="tsothm-mm__tabs"></div>
        <?php endif; ?>
        <button type="button" class="tsothm-mm__toggle" aria-expanded="false" aria-controls="tsothm-mm-panel" aria-label="<?php echo esc_attr( $label ); ?>">
            <span class="tsothm-mm__burger" aria-hidden="true"><i></i><i></i><i></i></span>
            <span class="tsothm-mm__toggle-label"><?php echo esc_html( $label ); ?></span>
        </button>
        <div class="tsothm-mm__backdrop" hidden></div>
        <nav class="tsothm-mm__panel" id="tsothm-mm-panel" aria-label="<?php esc_attr_e( 'Mobile menu', 'tso-blog' ); ?>">
            <button type="button" class="tsothm-mm__close" aria-label="<?php esc_attr_e( 'Close menu', 'tso-blog' ); ?>">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M5 5l14 14M19 5L5 19"/></svg>
            </button>
            <?php
            // The desktop nav already prints the same items: avoid duplicate li IDs.
            add_filter( 'nav_menu_item_id', '__return_empty_string' );
            wp_nav_menu(
                array(
                    'theme_location' => 'main-menu',
                    'menu_id'        => 'tsothm-mm-menu',
                    'menu_class'     => 'tsothm-mm__list',
                    'container'      => false,
                    'fallback_cb'    => false,
                    'depth'          => 2,
                )
            );
            remove_filter( 'nav_menu_item_id', '__return_empty_string' );
            ?>
        </nav>
    </div>
    <?php
}
add_action( 'wp_footer', 'tsothm_mobile_menu_render', 5 );
