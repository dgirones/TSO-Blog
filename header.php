<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page-wrapper">
    <header class="site-header">
        <div class="header-main">
            <div class="logo">
                <?php if ( has_custom_logo() ) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                        <span class="site-title-text"><?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?></span>
                    </a>
                <?php endif; ?>
            </div>
            <div class="header-right">
                <div id="tsothm-social-root"<?php echo empty( tsothm_get_configured_social_icons() ) ? ' class="tsothm-social-root--empty"' : ''; ?>>
                    <?php tsothm_social_icons(); ?>
                </div>
                <?php tsothm_header_gtranslate(); ?>
                <div class="header-search">
                    <?php get_search_form(); ?>
                </div>
                <?php
                $tsothm_color_mode_enabled = (bool) absint( get_theme_mod( 'tsothm_color_mode_switch_enabled', 1 ) );
                $tsothm_color_mode_class  = $tsothm_color_mode_enabled ? '' : 'tsothm-color-mode-root--empty';
                ?>
                <div id="tsothm-color-mode-root"<?php echo $tsothm_color_mode_class ? ' class="' . esc_attr( $tsothm_color_mode_class ) . '"' : ''; ?>>
                    <?php tsothm_render_color_mode_switch(); ?>
                </div>
            </div>
        </div>

        <div id="tsothm-header-image-root"<?php echo get_header_image() ? '' : ' class="tsothm-header-image-root--empty"'; ?>>
            <?php if ( get_header_image() ) : ?>
                <div class="header-image">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                        <?php
                        the_header_image_tag(
                            array(
                                'alt' => get_bloginfo( 'name', 'display' ),
                            )
                        );
                        ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php tsothm_header_announcement(); ?>

        <nav id="site-navigation" class="main-navigation" aria-label="Menú principal">
            <?php wp_nav_menu( array(
                'theme_location' => 'main-menu',
                'menu_id'        => 'primary-menu',
                'container'      => false,
                'fallback_cb'    => false,
            ) ); ?>
        </nav>
    </header>

    <div id="content" class="site-content">
