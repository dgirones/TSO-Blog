<?php
/**
 * Customizer: panels, sections, settings and controls.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   9. CUSTOMIZER
   ============================================================ */

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
    // Keep Site Title and Tagline after the Site Logo width controls (priorities 9 and 10).
    if ( $wp_customize->get_control( 'blogname' ) ) {
        $wp_customize->get_control( 'blogname' )->priority = 20;
    }
    if ( $wp_customize->get_control( 'blogdescription' ) ) {
        $wp_customize->get_control( 'blogdescription' )->priority = 30;
    }

    /*
     * Logo widths live next to the core Site Logo control (Site Identity).
     * Do not move or replace custom_logo: that is the WordPress Site Logo feature.
     */
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
            'label'       => __( 'Logo width (px)', 'tso-blog' ),
            'description' => __( 'Desktop display width of the Site Logo. Theme maximum: 500 px (minimum 40). Height adjusts automatically. For a sharp result, use a logo file at least this wide.', 'tso-blog' ),
            'section'     => 'title_tagline',
            'type'        => 'number',
            'priority'    => 9,
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
            'label'       => __( 'Logo width on mobile (px)', 'tso-blog' ),
            'description' => __( 'Mobile display width of the Site Logo (screens ≤ 768 px). Theme maximum: 500 px (minimum 40). Use the Customizer mobile preview to see it. For a sharp result, use a logo file at least this wide.', 'tso-blog' ),
            'section'     => 'title_tagline',
            'type'        => 'number',
            'priority'    => 10,
            'input_attrs' => array(
                'min'  => 40,
                'max'  => 500,
                'step' => 1,
            ),
        )
    );

    $wp_customize->add_panel(
        'tsothm_appearance_panel',
        array(
            'title'       => __( 'Appearance', 'tso-blog' ),
            'description' => __( 'Site width, sidebar, header social links and header image. The site logo is set under Site Identity.', 'tso-blog' ),
            'priority'    => 25,
        )
    );

    $wp_customize->add_panel(
        'tsothm_footer_panel',
        array(
            'title'       => __( 'Footer', 'tso-blog' ),
            'description' => __( 'Help for widget columns and footer texts. Footer colors are under Colors → Footer.', 'tso-blog' ),
            'priority'    => 40,
        )
    );

    $wp_customize->add_panel(
        'tsothm_content_panel',
        array(
            'title'       => __( 'Templates', 'tso-blog' ),
            'description' => __( 'Contact, Team, Pricing and Landing templates, and the top announcement.', 'tso-blog' ),
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
        $wp_customize->get_section( 'background_image' )->description = __( 'Optional background image for the whole site, behind the content. You can upload it and adjust its position, size, repeat and scrolling (parallax).', 'tso-blog' );
    }
    if ( $wp_customize->get_section( 'colors' ) ) {
        // Core “Colores” vacío: ocultar para no duplicar el panel TSO.
        $wp_customize->remove_section( 'colors' );
    }

    /* Diseño — ancho del sitio */
    $wp_customize->add_section(
        'tsothm_layout_section',
        array(
            'title'       => __( 'Layout / Width', 'tso-blog' ),
            'description' => __( 'Maximum width of the central white area. Theme options: 960–1600 px, or nearly full screen. The news sidebar widgets use 300 px of content width (plus sidebar padding); the main column uses the remaining space. On mobile the sidebar is hidden and the content uses almost the full screen.', 'tso-blog' ),
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
            'label'       => __( 'Content width', 'tso-blog' ),
            'description' => __( 'Theme maximum fixed width: 1600 px. «Nearly full screen» uses the full available width. Sidebar widgets get 300 px content width on desktop. Values wider than your screen look the same until you use a larger display.', 'tso-blog' ),
            'section'     => 'tsothm_layout_section',
            'type'        => 'select',
            'choices'     => tsothm_get_layout_width_choices(),
        )
    );

    $wp_customize->add_setting(
        'tsothm_blog_layout',
        array(
            'default'           => 'grid',
            'sanitize_callback' => 'tsothm_sanitize_blog_layout',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_blog_layout',
        array(
            'label'       => __( 'Blog / home layout', 'tso-blog' ),
            'description' => __( 'How posts are shown on the posts front page (home) and the Posts page.', 'tso-blog' ),
            'section'     => 'tsothm_layout_section',
            'type'        => 'select',
            'choices'     => tsothm_get_blog_layout_choices(),
        )
    );

    /* Sidebar — layout / behavior (Appearance) */
    $wp_customize->add_section(
        'tsothm_sidebar_section',
        array(
            'title'       => __( 'Sidebar', 'tso-blog' ),
            'description' => __( 'News sidebar column: visibility, position, width and extras. Background on/off and colors are under Colors → Sidebar.', 'tso-blog' ),
            'panel'       => 'tsothm_appearance_panel',
            'priority'    => 22,
        )
    );
    $wp_customize->add_setting(
        'tsothm_sidebar_visible',
        array(
            'default'           => 1,
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_sidebar_visible',
        array(
            'label'       => __( 'Show the news sidebar', 'tso-blog' ),
            'description' => __( 'Master switch. If off, the main column uses the full content width on desktop. Widgets stay under Appearance → Widgets.', 'tso-blog' ),
            'section'     => 'tsothm_sidebar_section',
            'type'        => 'checkbox',
            'priority'    => 5,
        )
    );
    $wp_customize->add_setting(
        'tsothm_sidebar_position',
        array(
            'default'           => 'right',
            'sanitize_callback' => 'tsothm_sanitize_sidebar_position',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_sidebar_position',
        array(
            'label'    => __( 'Position', 'tso-blog' ),
            'section'  => 'tsothm_sidebar_section',
            'type'     => 'select',
            'priority' => 10,
            'choices'  => array(
                'right' => __( 'Right', 'tso-blog' ),
                'left'  => __( 'Left', 'tso-blog' ),
            ),
        )
    );
    $wp_customize->add_setting(
        'tsothm_sidebar_width',
        array(
            'default'           => '300',
            'sanitize_callback' => 'tsothm_sanitize_sidebar_width',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_sidebar_width',
        array(
            'label'       => __( 'Content width (px)', 'tso-blog' ),
            'description' => __( 'Useful width for widgets (260–360). Horizontal padding is added outside this value.', 'tso-blog' ),
            'section'     => 'tsothm_sidebar_section',
            'type'        => 'number',
            'priority'    => 20,
            'input_attrs' => array(
                'min'  => 260,
                'max'  => 360,
                'step' => 4,
            ),
        )
    );
    $wp_customize->add_setting(
        'tsothm_sidebar_gap',
        array(
            'default'           => '30',
            'sanitize_callback' => 'tsothm_sanitize_sidebar_gap',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_sidebar_gap',
        array(
            'label'       => __( 'Gap from content (px)', 'tso-blog' ),
            'description' => __( 'Space between the main column and the sidebar (10–60).', 'tso-blog' ),
            'section'     => 'tsothm_sidebar_section',
            'type'        => 'number',
            'priority'    => 30,
            'input_attrs' => array(
                'min'  => 10,
                'max'  => 60,
                'step' => 2,
            ),
        )
    );
    $wp_customize->add_setting(
        'tsothm_sidebar_radius',
        array(
            'default'           => '6',
            'sanitize_callback' => 'tsothm_sanitize_sidebar_radius',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_sidebar_radius',
        array(
            'label'       => __( 'Corner radius (px)', 'tso-blog' ),
            'section'     => 'tsothm_sidebar_section',
            'type'        => 'number',
            'priority'    => 40,
            'input_attrs' => array(
                'min'  => 0,
                'max'  => 24,
                'step' => 1,
            ),
        )
    );
    $wp_customize->add_setting(
        'tsothm_sidebar_sticky',
        array(
            'default'           => 0,
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_sidebar_sticky',
        array(
            'label'       => __( 'Sticky sidebar while scrolling', 'tso-blog' ),
            'description' => __( 'Keeps the sidebar in view on desktop while you scroll the article.', 'tso-blog' ),
            'section'     => 'tsothm_sidebar_section',
            'type'        => 'checkbox',
            'priority'    => 50,
        )
    );
    $wp_customize->add_setting(
        'tsothm_sidebar_border',
        array(
            'default'           => 0,
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_sidebar_border',
        array(
            'label'    => __( 'Thin border around the column', 'tso-blog' ),
            'section'  => 'tsothm_sidebar_section',
            'type'     => 'checkbox',
            'priority' => 60,
        )
    );
    $wp_customize->add_setting(
        'tsothm_sidebar_shadow',
        array(
            'default'           => 0,
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_sidebar_shadow',
        array(
            'label'    => __( 'Soft shadow on the column', 'tso-blog' ),
            'section'  => 'tsothm_sidebar_section',
            'type'     => 'checkbox',
            'priority' => 70,
        )
    );
    $wp_customize->add_setting(
        'tsothm_sidebar_on_home',
        array(
            'default'           => 0,
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_sidebar_on_home',
        array(
            'label'       => __( 'Also on the blog home', 'tso-blog' ),
            'description' => __( 'Posts index (front page of posts or the Posts page).', 'tso-blog' ),
            'section'     => 'tsothm_sidebar_section',
            'type'        => 'checkbox',
            'priority'    => 80,
        )
    );
    $wp_customize->add_setting(
        'tsothm_sidebar_on_contact',
        array(
            'default'           => 0,
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_sidebar_on_contact',
        array(
            'label'    => __( 'Also on Contact template', 'tso-blog' ),
            'section'  => 'tsothm_sidebar_section',
            'type'     => 'checkbox',
            'priority' => 90,
        )
    );
    $wp_customize->add_setting(
        'tsothm_sidebar_on_portfolio',
        array(
            'default'           => 0,
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_sidebar_on_portfolio',
        array(
            'label'    => __( 'Also on Portfolio template', 'tso-blog' ),
            'section'  => 'tsothm_sidebar_section',
            'type'     => 'checkbox',
            'priority' => 100,
        )
    );

    /* Footer — guía de uso (imágenes) */
    $wp_customize->add_section(
        'tsothm_footer_help_section',
        array(
            'title'       => __( 'Column help', 'tso-blog' ),
            'description' => __( 'There are 3 widget areas: Footer column 1, 2 and 3 (Customize → Widgets).', 'tso-blog' ) . ' ' . __( 'So photos in columns 2 and 3 match: use the same size for both. Recommended 600×450 px (4:3) or 600×600 px (square). Do not mix portrait with landscape. Each column is about 300 px wide on desktop; 600 px source is enough. If you only use column 1 (Contact / Legal menu) and leave 2 and 3 empty, links show horizontally. Note: the «your theme has 1 more widget area…» notice is from WordPress: «News Sidebar» is not shown on the blog front page, Contact or Portfolio; in the preview open a post or a default-template page to edit it. To edit a widget from the preview: Shift + click the widget.', 'tso-blog' ),
            'panel'       => 'tsothm_footer_panel',
            'priority'    => 10,
        )
    );
    /* Redes sociales */
    $wp_customize->add_section(
        'tsothm_social_section',
        array(
            'title'       => __( 'Social networks', 'tso-blog' ),
            'description' => __( 'Drag the networks with the ☰ icon to change order (left → right). Below, paste the full URL for each network. Those without a URL are hidden.', 'tso-blog' ),
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
                'label'       => __( 'Icon order', 'tso-blog' ),
                'description' => __( 'Drag up or down. Saved when you publish.', 'tso-blog' ),
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
                'label'    => sprintf( __( '%s (full URL)', 'tso-blog' ), $data['label'] ),
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
            'title'       => __( 'Colors', 'tso-blog' ),
            'description' => __( 'Day colors (including content text and headings), menu, sidebar, footer, announcement and night mode.', 'tso-blog' ),
            'priority'    => 30,
        )
    );

    $tsothm_color_defaults = tsothm_get_theme_color_defaults();

    $tsothm_color_groups = array(
        'tsothm_colors_general_section' => array(
            'title'       => __( 'General', 'tso-blog' ),
            'description' => __( 'Global theme colors: accents, buttons, page background and day-mode content text. Night content text is under Night mode.', 'tso-blog' ),
            'priority'    => 10,
            'controls'    => array(
                'tsothm_color_accent'  => __( 'Accent', 'tso-blog' ),
                'tsothm_color_primary' => __( 'Primary', 'tso-blog' ),
                'tsothm_color_body_bg' => __( 'Outer background', 'tso-blog' ),
            ),
        ),
        'tsothm_colors_header_section'  => array(
            'title'       => __( 'Header', 'tso-blog' ),
            'description' => __( 'Header background: the top strip with the logo and search, above the navigation menu.', 'tso-blog' ),
            'priority'    => 15,
            'controls'    => array(
                'tsothm_color_header_bg' => __( 'Background', 'tso-blog' ),
            ),
        ),
        'tsothm_colors_nav_section'     => array(
            'title'       => __( 'Menu', 'tso-blog' ),
            'description' => __( 'Main navigation bar, button style, and submenus.', 'tso-blog' ),
            'priority'    => 20,
            'controls'    => array(
                'tsothm_color_nav_bg'      => __( 'Background', 'tso-blog' ),
                'tsothm_color_nav_text'    => __( 'Link text', 'tso-blog' ),
                'tsothm_color_nav_hover'   => __( 'Hover / active text', 'tso-blog' ),
                'tsothm_color_nav_submenu' => __( 'Submenu background', 'tso-blog' ),
            ),
        ),
        'tsothm_colors_footer_section'  => array(
            'title'       => __( 'Footer', 'tso-blog' ),
            'description' => __( 'Footer: background, widget titles, texts, copyright and separators.', 'tso-blog' ),
            'priority'    => 30,
            'controls'    => array(
                'tsothm_color_footer_bg'      => __( 'Background', 'tso-blog' ),
                'tsothm_color_footer_heading' => __( 'Widget titles', 'tso-blog' ),
                'tsothm_color_footer_text'    => __( 'Text and links', 'tso-blog' ),
                'tsothm_color_footer_muted'   => __( 'Copyright / secondary text', 'tso-blog' ),
                'tsothm_color_footer_border'  => __( 'Lines and borders', 'tso-blog' ),
            ),
        ),
        'tsothm_colors_announce_section' => array(
            'title'       => __( 'Announcement', 'tso-blog' ),
            'description' => __( 'News bar between the header and the menu. Text is configured under «Announcement / news».', 'tso-blog' ),
            'priority'    => 45,
            'controls'    => array(
                'tsothm_color_announce_bg'   => __( 'Background', 'tso-blog' ),
                'tsothm_color_announce_text' => __( 'Text', 'tso-blog' ),
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

    /* Day-mode content text (same theme_mods as before; live under Colors → General). */
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
                'label'       => __( 'Content text', 'tso-blog' ),
                'description' => __( 'Day mode. Night mode uses Colors → Night mode → Content text.', 'tso-blog' ),
                'section'     => 'tsothm_colors_general_section',
                'priority'    => 20,
            )
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
                'label'       => __( 'Content headings', 'tso-blog' ),
                'description' => __( 'Day mode. Night mode uses Colors → Night mode → Content headings.', 'tso-blog' ),
                'section'     => 'tsothm_colors_general_section',
                'priority'    => 21,
            )
        )
    );

    /* Colors → Sidebar */
    $wp_customize->add_section(
        'tsothm_colors_sidebar_section',
        array(
            'title'       => __( 'Sidebar', 'tso-blog' ),
            'description' => __( 'Column background, widget cards and title bar. Layout options are under Appearance → Sidebar.', 'tso-blog' ),
            'panel'       => 'tsothm_colors_panel',
            'priority'    => 35,
        )
    );
    $wp_customize->add_setting(
        'tsothm_sidebar_bg_enabled',
        array(
            'default'           => 1,
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_sidebar_bg_enabled',
        array(
            'label'       => __( 'Use a sidebar background', 'tso-blog' ),
            'description' => __( 'Turn off to remove the column fill (transparent).', 'tso-blog' ),
            'section'     => 'tsothm_colors_sidebar_section',
            'type'        => 'checkbox',
            'priority'    => 10,
        )
    );
    $wp_customize->add_setting(
        'tsothm_color_sidebar_bg',
        array(
            'default'           => '#f1f1f1',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'tsothm_color_sidebar_bg',
            array(
                'label'           => __( 'Column background (day)', 'tso-blog' ),
                'section'         => 'tsothm_colors_sidebar_section',
                'priority'        => 20,
                'active_callback' => 'tsothm_customize_sidebar_bg_color_active',
            )
        )
    );
    $wp_customize->add_setting(
        'tsothm_color_sidebar_bg_night',
        array(
            'default'           => '',
            'sanitize_callback' => 'tsothm_sanitize_optional_hex_color',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'tsothm_color_sidebar_bg_night',
            array(
                'label'           => __( 'Column background (night)', 'tso-blog' ),
                'description'     => __( 'Optional. If empty, night mode uses the dark surface color.', 'tso-blog' ),
                'section'         => 'tsothm_colors_sidebar_section',
                'priority'        => 30,
                'active_callback' => 'tsothm_customize_sidebar_bg_color_active',
            )
        )
    );
    $wp_customize->add_setting(
        'tsothm_color_sidebar_widget_bg',
        array(
            'default'           => '#ffffff',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'tsothm_color_sidebar_widget_bg',
            array(
                'label'    => __( 'Widget card background', 'tso-blog' ),
                'section'  => 'tsothm_colors_sidebar_section',
                'priority' => 40,
            )
        )
    );
    $wp_customize->add_setting(
        'tsothm_color_sidebar_title_bg',
        array(
            'default'           => '',
            'sanitize_callback' => 'tsothm_sanitize_optional_hex_color',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'tsothm_color_sidebar_title_bg',
            array(
                'label'       => __( 'Widget title bar', 'tso-blog' ),
                'description' => __( 'Blue bar behind widget titles. Empty = theme Primary color.', 'tso-blog' ),
                'section'     => 'tsothm_colors_sidebar_section',
                'priority'    => 50,
            )
        )
    );

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
            'label'       => __( 'Menu button style', 'tso-blog' ),
            'description' => __( 'Choose the relief style for the main menu titles.', 'tso-blog' ),
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
            'title'       => __( 'Night mode', 'tso-blog' ),
            'description' => __( 'Night mode colors, independent from day mode: colors in the other sections only apply in day mode. If you change nothing, the default dark palette is used.', 'tso-blog' ),
            'panel'       => 'tsothm_colors_panel',
            'priority'    => 50,
        )
    );
    $tsothm_night_controls = array(
        'body_bg'   => array( __( 'Outer background', 'tso-blog' ), '' ),
        'surface'   => array( __( 'Content background', 'tso-blog' ), '' ),
        'header_bg' => array( __( 'Header background', 'tso-blog' ), __( 'Empty = automatic: if the day-mode header is light, the content color is used.', 'tso-blog' ) ),
        'nav_bg'    => array( __( 'Menu background', 'tso-blog' ), '' ),
        'footer_bg' => array( __( 'Footer background', 'tso-blog' ), '' ),
        'accent'    => array( __( 'Accent', 'tso-blog' ), __( 'Title bars, buttons and highlighted links.', 'tso-blog' ) ),
        'link'      => array( __( 'Links and blue bars', 'tso-blog' ), '' ),
        'primary'   => array( __( 'Primary color', 'tso-blog' ), '' ),
        'nav_text'  => array( __( 'Menu: link text', 'tso-blog' ), '' ),
        'nav_submenu'    => array( __( 'Menu: submenu background', 'tso-blog' ), '' ),
        'footer_heading' => array( __( 'Footer: widget titles', 'tso-blog' ), '' ),
        'footer_text'    => array( __( 'Footer: text and links', 'tso-blog' ), '' ),
        'footer_muted'   => array( __( 'Footer: copyright / secondary text', 'tso-blog' ), '' ),
        'footer_border'  => array( __( 'Footer: lines and borders', 'tso-blog' ), '' ),
        'announce_bg'    => array( __( 'Announcement: background', 'tso-blog' ), '' ),
        'announce_text'  => array( __( 'Announcement: text', 'tso-blog' ), '' ),
        'text'      => array( __( 'Content text', 'tso-blog' ), __( 'Night mode only. Day mode is under Colors → General.', 'tso-blog' ) ),
        'heading'   => array( __( 'Content headings', 'tso-blog' ), __( 'Night mode only. Day mode is under Colors → General.', 'tso-blog' ) ),
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
            'title'       => __( 'Typography', 'tso-blog' ),
            'description' => __( 'Body and titles (including cards and widget titles), plus announcement bar and menu. Content text colors are under Colors → General (day) and Colors → Night mode. Footer text size is under Footer → Footer texts. Bundled web fonts load only if you select them.', 'tso-blog' ),
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
            'title'       => __( 'Body text', 'tso-blog' ),
            'description' => __( 'Font family, size and line height for paragraphs and content, plus widget list text (sidebar and footer columns). Text color is under Colors → General. Widget titles are under Titles.', 'tso-blog' ),
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
            'label'   => __( 'Font family', 'tso-blog' ),
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
            'label'       => __( 'Size (px)', 'tso-blog' ),
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
            'label'       => __( 'Line height', 'tso-blog' ),
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
            'label'       => __( 'Widget text size (px)', 'tso-blog' ),
            'description' => __( 'Links and lists in sidebar and footer widget columns. Uses the body font family.', 'tso-blog' ),
            'section'     => 'tsothm_typography_body_section',
            'type'        => 'number',
            'input_attrs' => array(
                'min'  => 12,
                'max'  => 22,
                'step' => 1,
            ),
        )
    );

    $wp_customize->add_section(
        'tsothm_typography_heading_section',
        array(
            'title'       => __( 'Titles', 'tso-blog' ),
            'description' => __( 'Shared font for headings, post titles, home cards, related-posts heading and widget titles (sidebar and footer). Sizes below are separate; family and weight apply to all of them. Heading color is under Colors → General.', 'tso-blog' ),
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
            'label'   => __( 'Font family', 'tso-blog' ),
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
            'label'   => __( 'Weight', 'tso-blog' ),
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
            'label'       => __( 'H1 / single post title size (px)', 'tso-blog' ),
            'description' => __( 'Title inside a post or page. Card titles on the home / archive use the size below.', 'tso-blog' ),
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
        'tsothm_font_card_title_size',
        array(
            'default'           => '16',
            'sanitize_callback' => 'tsothm_sanitize_card_title_size',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_card_title_size',
        array(
            'label'       => __( 'Home / archive card title size (px)', 'tso-blog' ),
            'description' => __( 'List and magazine layouts scale from this base. Uses the same font family, weight and transform as titles.', 'tso-blog' ),
            'section'     => 'tsothm_typography_heading_section',
            'type'        => 'number',
            'input_attrs' => array(
                'min'  => 14,
                'max'  => 28,
                'step' => 1,
            ),
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
            'label'       => __( 'Widget title size (px)', 'tso-blog' ),
            'description' => __( 'Sidebar blue bars and footer column titles. Same family and weight as titles; size only here.', 'tso-blog' ),
            'section'     => 'tsothm_typography_heading_section',
            'type'        => 'number',
            'input_attrs' => array(
                'min'  => 12,
                'max'  => 22,
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
            'label'       => __( 'Title transform', 'tso-blog' ),
            'description' => __( 'Uppercase, etc. on H1 and card titles (not sidebar widget bars).', 'tso-blog' ),
            'section'     => 'tsothm_typography_heading_section',
            'type'        => 'select',
            'choices'     => array(
                'none'       => __( 'None', 'tso-blog' ),
                'uppercase'  => __( 'Uppercase', 'tso-blog' ),
                'lowercase'  => __( 'Lowercase', 'tso-blog' ),
                'capitalize' => __( 'Capitalize', 'tso-blog' ),
            ),
        )
    );

    $tsothm_transform_choices = array(
        'none'       => __( 'None', 'tso-blog' ),
        'uppercase'  => __( 'Uppercase', 'tso-blog' ),
        'lowercase'  => __( 'Lowercase', 'tso-blog' ),
        'capitalize' => __( 'Capitalize', 'tso-blog' ),
    );
    $tsothm_weight_choices = array(
        '400' => '400',
        '500' => '500',
        '600' => '600',
        '700' => '700',
        '800' => '800',
    );

    $wp_customize->add_section(
        'tsothm_typography_announce_section',
        array(
            'title'       => __( 'Announcement bar', 'tso-blog' ),
            'description' => __( 'Font family and text style for the news / announcement bar between the header and the menu. Colors are under Colors → Announcement.', 'tso-blog' ),
            'panel'       => 'tsothm_typography_panel',
            'priority'    => 35,
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_announce',
        array(
            'default'           => 'arial',
            'sanitize_callback' => 'tsothm_sanitize_font_key',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_announce',
        array(
            'label'   => __( 'Font family', 'tso-blog' ),
            'section' => 'tsothm_typography_announce_section',
            'type'    => 'select',
            'choices' => $tsothm_font_choices,
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_announce_size',
        array(
            'default'           => '16',
            'sanitize_callback' => static function ( $value ) {
                return tsothm_sanitize_ui_font_size( $value, 16 );
            },
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_announce_size',
        array(
            'label'       => __( 'Size (px)', 'tso-blog' ),
            'section'     => 'tsothm_typography_announce_section',
            'type'        => 'number',
            'input_attrs' => array(
                'min'  => 12,
                'max'  => 28,
                'step' => 1,
            ),
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_announce_weight',
        array(
            'default'           => '800',
            'sanitize_callback' => 'tsothm_sanitize_font_weight',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_announce_weight',
        array(
            'label'   => __( 'Weight', 'tso-blog' ),
            'section' => 'tsothm_typography_announce_section',
            'type'    => 'select',
            'choices' => $tsothm_weight_choices,
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_announce_transform',
        array(
            'default'           => 'uppercase',
            'sanitize_callback' => 'tsothm_sanitize_text_transform',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_announce_transform',
        array(
            'label'   => __( 'Text transform', 'tso-blog' ),
            'section' => 'tsothm_typography_announce_section',
            'type'    => 'select',
            'choices' => $tsothm_transform_choices,
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_announce_tracking',
        array(
            'default'           => '0.03',
            'sanitize_callback' => 'tsothm_sanitize_letter_spacing_em_value',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_announce_tracking',
        array(
            'label'       => __( 'Letter spacing (em)', 'tso-blog' ),
            'description' => __( 'Default 0.03. Range 0–0.2.', 'tso-blog' ),
            'section'     => 'tsothm_typography_announce_section',
            'type'        => 'number',
            'input_attrs' => array(
                'min'  => 0,
                'max'  => 0.2,
                'step' => 0.01,
            ),
        )
    );

    $wp_customize->add_section(
        'tsothm_typography_nav_section',
        array(
            'title'       => __( 'Navigation menu', 'tso-blog' ),
            'description' => __( 'Font family and link text for the main menu (desktop bar). Colors are under Colors → Menu.', 'tso-blog' ),
            'panel'       => 'tsothm_typography_panel',
            'priority'    => 40,
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_nav',
        array(
            'default'           => 'arial',
            'sanitize_callback' => 'tsothm_sanitize_font_key',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_nav',
        array(
            'label'   => __( 'Font family', 'tso-blog' ),
            'section' => 'tsothm_typography_nav_section',
            'type'    => 'select',
            'choices' => $tsothm_font_choices,
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_nav_size',
        array(
            'default'           => '15',
            'sanitize_callback' => static function ( $value ) {
                return tsothm_sanitize_ui_font_size( $value, 15 );
            },
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_nav_size',
        array(
            'label'       => __( 'Size (px)', 'tso-blog' ),
            'section'     => 'tsothm_typography_nav_section',
            'type'        => 'number',
            'input_attrs' => array(
                'min'  => 12,
                'max'  => 28,
                'step' => 1,
            ),
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_nav_weight',
        array(
            'default'           => '700',
            'sanitize_callback' => 'tsothm_sanitize_font_weight',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_nav_weight',
        array(
            'label'   => __( 'Weight', 'tso-blog' ),
            'section' => 'tsothm_typography_nav_section',
            'type'    => 'select',
            'choices' => $tsothm_weight_choices,
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_nav_transform',
        array(
            'default'           => 'uppercase',
            'sanitize_callback' => 'tsothm_sanitize_text_transform',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_nav_transform',
        array(
            'label'   => __( 'Text transform', 'tso-blog' ),
            'section' => 'tsothm_typography_nav_section',
            'type'    => 'select',
            'choices' => $tsothm_transform_choices,
        )
    );
    $wp_customize->add_setting(
        'tsothm_font_nav_tracking',
        array(
            'default'           => '0.5',
            'sanitize_callback' => 'tsothm_sanitize_letter_spacing_px_value',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_nav_tracking',
        array(
            'label'       => __( 'Letter spacing (px)', 'tso-blog' ),
            'description' => __( 'Default 0.5. Range 0–3.', 'tso-blog' ),
            'section'     => 'tsothm_typography_nav_section',
            'type'        => 'number',
            'input_attrs' => array(
                'min'  => 0,
                'max'  => 3,
                'step' => 0.1,
            ),
        )
    );

    /* Textos de interfaz (botones, etc.) */
    $wp_customize->add_section(
        'tsothm_texts_section',
        array(
            'title'       => __( 'Interface texts', 'tso-blog' ),
            'description' => __( 'General theme texts (buttons). Copyright and legal text are under Footer.', 'tso-blog' ),
            'panel'       => 'tsothm_appearance_panel',
            'priority'    => 42,
        )
    );
    $wp_customize->add_setting( 'tsothm_text_load_more', array(
        'default'           => 'Load more',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'tsothm_text_load_more', array(
        'label'   => __( '«Load more» button text', 'tso-blog' ),
        'section' => 'tsothm_texts_section',
        'type'    => 'text',
    ) );

    /* Footer — textos del pie */
    $wp_customize->add_section(
        'tsothm_footer_texts_section',
        array(
            'title'       => __( 'Footer texts', 'tso-blog' ),
            'description' => __( 'Copyright and legal text under the widget columns, plus their font size. Leave copyright empty to show an automatic «© {year} {site name}» from the site settings.', 'tso-blog' ),
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
        'label'       => __( 'Footer copyright text', 'tso-blog' ),
        'description' => __( 'Leave empty for automatic text (current year + site title). Or write your own and use {year}, {site_name}, {home_url}.', 'tso-blog' ),
        'section'     => 'tsothm_footer_texts_section',
        'type'        => 'text',
    ) );

    $wp_customize->add_setting( 'tsothm_footer_legal', array(
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'tsothm_footer_legal', array(
        'label'       => __( 'Footer legal text (HTML allowed)', 'tso-blog' ),
        'description' => __( 'Optional. Leave empty to hide. Placeholders: {year}, {site_name}, {home_url}, {privacy_url} (URL of the page set in Settings → Privacy; blank if none). To add a link, paste HTML in this field, for example: a href="{privacy_url}" with the label Privacy policy.', 'tso-blog' ),
        'section'     => 'tsothm_footer_texts_section',
        'type'        => 'textarea',
    ) );
    $wp_customize->add_setting(
        'tsothm_font_footer_text_size',
        array(
            'default'           => '12',
            'sanitize_callback' => 'tsothm_sanitize_widget_font_size',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'tsothm_font_footer_text_size',
        array(
            'label'       => __( 'Copyright / legal size (px)', 'tso-blog' ),
            'description' => __( 'Font size for the copyright and legal lines only (not footer widget columns).', 'tso-blog' ),
            'section'     => 'tsothm_footer_texts_section',
            'type'        => 'number',
            'input_attrs' => array(
                'min'  => 12,
                'max'  => 22,
                'step' => 1,
            ),
        )
    );

    /* Contacto (plantilla de página) */
    $wp_customize->add_section(
        'tsothm_contact_section',
        array(
            'title'       => __( 'Contact', 'tso-blog' ),
            'description' => __( 'Data for the «Contact» page template. The map only shows if you paste a Google Maps embed URL. The form is added with a plugin shortcode (e.g. Contact Form 7); the theme does not send email by itself.', 'tso-blog' ),
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
            'label'   => __( 'Hero subtitle', 'tso-blog' ),
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
            'label'   => __( 'Introductory text (left column)', 'tso-blog' ),
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
            'label'   => __( 'Address / office', 'tso-blog' ),
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
            'label'   => __( 'Alternative email (optional)', 'tso-blog' ),
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
            'label'   => __( 'Phone', 'tso-blog' ),
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
            'label'   => __( 'Alternative phone / fax (optional)', 'tso-blog' ),
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
            'label'       => __( 'Google Maps (embed URL)', 'tso-blog' ),
            'description' => __( 'In Google Maps: Share → Embed a map → copy the src attribute URL (or paste the full embed code). Only Google Maps embed URLs are accepted. Empty = no map.', 'tso-blog' ),
            'section'     => 'tsothm_contact_section',
            'type'        => 'text',
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
            'label'       => __( 'Form shortcode', 'tso-blog' ),
            'description' => __( 'Contact Form 7 example: [contact-form-7 id="123"]. The theme does not process or send the form.', 'tso-blog' ),
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
            'label'   => __( 'Show social icons on Contact', 'tso-blog' ),
            'section' => 'tsothm_contact_section',
            'type'    => 'checkbox',
        )
    );

    /* Equipo (plantilla de página) */
    $wp_customize->add_section(
        'tsothm_team_section',
        array(
            'title'       => __( 'Team', 'tso-blog' ),
            'description' => __( 'Team cards for the "Team" page template. If left empty, the page will show the block content ("Team (3 people)" pattern).', 'tso-blog' ),
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
                'label'      => __( 'People', 'tso-blog' ),
                'section'    => 'tsothm_team_section',
                'fields'     => tsothm_get_team_fields(),
                'add_label'  => __( 'Add person', 'tso-blog' ),
            )
        )
    );

    /* Precios (plantilla de página) */
    $wp_customize->add_section(
        'tsothm_pricing_section',
        array(
            'title'       => __( 'Pricing', 'tso-blog' ),
            'description' => __( 'Up to 5 plans for the "Pricing" page template. If left empty, the page shows the block content ("Pricing (3 plans)" pattern).', 'tso-blog' ),
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
                'label'       => __( 'Plans', 'tso-blog' ),
                'description' => __( 'Maximum 5 plans. Separate features with a vertical bar, for example: Support | Updates | Backups.', 'tso-blog' ),
                'section'     => 'tsothm_pricing_section',
                'fields'      => tsothm_get_pricing_fields(),
                'add_label'   => __( 'Add plan', 'tso-blog' ),
                'max_items'   => 5,
            )
        )
    );

    /* Landing / Portada (plantilla de página) */
    $wp_customize->add_section(
        'tsothm_landing_section',
        array(
            'title'       => __( 'Landing / Cover', 'tso-blog' ),
            'description' => __( 'Header and highlights for the "Landing / Cover" page template. The hero title and background image come from each page\'s title and featured image; here you only set the small text, the buttons and the highlights.', 'tso-blog' ),
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
            'label'       => __( 'Small text above the title', 'tso-blog' ),
            'description' => __( 'Optional. Example: "New" or a campaign name.', 'tso-blog' ),
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
            'label'   => __( 'Primary button — text', 'tso-blog' ),
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
            'label'   => __( 'Primary button — URL', 'tso-blog' ),
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
            'label'   => __( 'Secondary button — text (optional)', 'tso-blog' ),
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
            'label'   => __( 'Secondary button — URL', 'tso-blog' ),
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
                'label'       => __( 'Highlights below the hero', 'tso-blog' ),
                'description' => __( 'Up to 3 or 4 short points (icon + title + text) shown below the header.', 'tso-blog' ),
                'section'     => 'tsothm_landing_section',
                'fields'      => tsothm_get_landing_highlight_fields(),
                'add_label'   => __( 'Add highlight', 'tso-blog' ),
            )
        )
    );

    /* Anuncio / novedades (hasta 5 + rotación) */
    $wp_customize->add_section(
        'tsothm_announcement_section',
        array(
            'title'       => __( 'Announcement / news', 'tso-blog' ),
            'description' => __( 'Up to 5 notices between the header and the menu. You can show only one or rotate them automatically. Colors: Colors → Announcement.', 'tso-blog' ),
            'panel'       => 'tsothm_content_panel',
            'priority'    => 30,
        )
    );
    $wp_customize->add_setting(
        'tsothm_announcement_enable',
        array(
            'default'           => 0,
            'sanitize_callback' => 'absint',
            'transport'         => 'postMessage',
        )
    );
    $wp_customize->add_control(
        'tsothm_announcement_enable',
        array(
            'label'   => __( 'Show announcement bar', 'tso-blog' ),
            'section' => 'tsothm_announcement_section',
            'type'    => 'checkbox',
        )
    );
    $wp_customize->add_setting(
        'tsothm_announcement_mode',
        array(
            'default'           => 'rotate',
            'sanitize_callback' => 'tsothm_sanitize_announcement_mode',
            'transport'         => 'postMessage',
        )
    );
    $wp_customize->add_control(
        'tsothm_announcement_mode',
        array(
            'label'       => __( 'Display mode', 'tso-blog' ),
            'description' => __( 'Rotation: cycles through announcements with text. Single: shows only the chosen number.', 'tso-blog' ),
            'section'     => 'tsothm_announcement_section',
            'type'        => 'select',
            'choices'     => array(
                'rotate' => __( 'Automatic rotation', 'tso-blog' ),
                'single' => __( 'Single announcement', 'tso-blog' ),
            ),
        )
    );
    $wp_customize->add_setting(
        'tsothm_announcement_single',
        array(
            'default'           => 1,
            'sanitize_callback' => 'tsothm_sanitize_announcement_slot',
            'transport'         => 'postMessage',
        )
    );
    $wp_customize->add_control(
        'tsothm_announcement_single',
        array(
            'label'       => __( 'Announcement to show («Single» mode)', 'tso-blog' ),
            'section'     => 'tsothm_announcement_section',
            'type'        => 'select',
            'choices'     => array(
                '1' => __( 'Announcement 1', 'tso-blog' ),
                '2' => __( 'Announcement 2', 'tso-blog' ),
                '3' => __( 'Announcement 3', 'tso-blog' ),
                '4' => __( 'Announcement 4', 'tso-blog' ),
                '5' => __( 'Announcement 5', 'tso-blog' ),
            ),
        )
    );
    $wp_customize->add_setting(
        'tsothm_announcement_interval',
        array(
            'default'           => 60,
            'sanitize_callback' => 'tsothm_sanitize_announcement_interval',
            'transport'         => 'postMessage',
        )
    );
    $wp_customize->add_control(
        'tsothm_announcement_interval',
        array(
            'label'       => __( 'Seconds between announcements (rotation)', 'tso-blog' ),
            'description' => __( 'Default 60 (1 minute). How long each announcement stays before the next movement.', 'tso-blog' ),
            'section'     => 'tsothm_announcement_section',
            'type'        => 'number',
            'input_attrs' => array(
                'min'  => 15,
                'max'  => 600,
                'step' => 5,
            ),
        )
    );
    $wp_customize->add_setting(
        'tsothm_announcement_effect',
        array(
            'default'           => 'slide',
            'sanitize_callback' => 'tsothm_sanitize_announcement_effect',
            'transport'         => 'postMessage',
        )
    );
    $wp_customize->add_control(
        'tsothm_announcement_effect',
        array(
            'label'       => __( 'Movement effect', 'tso-blog' ),
            'description' => __( 'How each announcement enters and leaves the bar.', 'tso-blog' ),
            'section'     => 'tsothm_announcement_section',
            'type'        => 'select',
            'choices'     => tsothm_get_announcement_effect_choices(),
        )
    );

    $announcement_partial_settings = array(
        'tsothm_announcement_enable',
        'tsothm_announcement_mode',
        'tsothm_announcement_single',
        'tsothm_announcement_interval',
        'tsothm_announcement_effect',
    );

    for ( $i = 1; $i <= 5; $i++ ) {
        $wp_customize->add_setting(
            'tsothm_announcement_' . $i . '_text',
            array(
                'default'           => ( 1 === $i ) ? (string) get_theme_mod( 'tsothm_announcement_text', '' ) : '',
                'sanitize_callback' => 'sanitize_text_field',
                'transport'         => 'postMessage',
            )
        );
        $wp_customize->add_control(
            'tsothm_announcement_' . $i . '_text',
            array(
                /* translators: %d: announcement slot number 1–5 */
                'label'   => sprintf( __( 'Announcement %d — text', 'tso-blog' ), $i ),
                'section' => 'tsothm_announcement_section',
                'type'    => 'text',
            )
        );
        $wp_customize->add_setting(
            'tsothm_announcement_' . $i . '_url',
            array(
                'default'           => ( 1 === $i ) ? (string) get_theme_mod( 'tsothm_announcement_url', '' ) : '',
                'sanitize_callback' => 'esc_url_raw',
                'transport'         => 'postMessage',
            )
        );
        $wp_customize->add_control(
            'tsothm_announcement_' . $i . '_url',
            array(
                /* translators: %d: announcement slot number 1–5 */
                'label'   => sprintf( __( 'Announcement %d — link (optional)', 'tso-blog' ), $i ),
                'section' => 'tsothm_announcement_section',
                'type'    => 'url',
            )
        );
        $wp_customize->add_setting(
            'tsothm_announcement_' . $i . '_new_tab',
            array(
                'default'           => ( 1 === $i ) ? (int) get_theme_mod( 'tsothm_announcement_new_tab', 1 ) : 1,
                'sanitize_callback' => 'absint',
                'transport'         => 'postMessage',
            )
        );
        $wp_customize->add_control(
            'tsothm_announcement_' . $i . '_new_tab',
            array(
                /* translators: %d: announcement slot number 1–5 */
                'label'   => sprintf( __( 'Announcement %d — open link in a new tab', 'tso-blog' ), $i ),
                'section' => 'tsothm_announcement_section',
                'type'    => 'checkbox',
            )
        );
        $announcement_partial_settings[] = 'tsothm_announcement_' . $i . '_text';
        $announcement_partial_settings[] = 'tsothm_announcement_' . $i . '_url';
        $announcement_partial_settings[] = 'tsothm_announcement_' . $i . '_new_tab';
    }

    if ( isset( $wp_customize->selective_refresh ) ) {
        $wp_customize->selective_refresh->add_partial(
            'tsothm_announcement',
            array(
                'selector'            => '#tsothm-announcement-root',
                'settings'            => $announcement_partial_settings,
                'container_inclusive' => true,
                'render_callback'     => 'tsothm_customize_partial_announcement',
                'fallback_refresh'    => true,
            )
        );
    }

    /* Relacionados */
    $wp_customize->add_section(
        'tsothm_related_section',
        array(
            'title'    => __( 'Related posts', 'tso-blog' ),
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
        'label'       => __( 'Number of related posts (1–6)', 'tso-blog' ),
        'section'     => 'tsothm_related_section',
        'type'        => 'number',
        'input_attrs' => array( 'min' => 1, 'max' => 6, 'step' => 1 ),
    ) );
    $wp_customize->add_setting( 'tsothm_related_title', array(
        'default'           => 'Related posts',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'tsothm_related_title', array(
        'label'   => __( 'Related section title', 'tso-blog' ),
        'section' => 'tsothm_related_section',
        'type'    => 'text',
    ) );

    /* Integraciones opcionales (plugins de terceros) */
    $wp_customize->add_section(
        'tsothm_integrations_section',
        array(
            'title'       => __( 'Integrations', 'tso-blog' ),
            'description' => __( 'Optional features that require a plugin. Disabled by default.', 'tso-blog' ),
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
            'label'       => __( 'Show GTranslate in the header', 'tso-blog' ),
            'description' => __( 'Requires the GTranslate plugin to be active. If it is disabled or not installed, nothing is shown.', 'tso-blog' ),
            'section'     => 'tsothm_integrations_section',
            'type'        => 'checkbox',
        )
    );

} );
