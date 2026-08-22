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
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <?php
                        $tso_logo = get_stylesheet_directory() . '/images/logo.png';
                        if ( file_exists( $tso_logo ) ) {
                            echo '<img src="' . esc_url( get_stylesheet_directory_uri() . '/images/logo.png' ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" width="300" height="124">';
                        } else {
                            echo '<span class="site-title-text">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
                        }
                        ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="header-right">
                <?php tso_social_icons(); ?>
                <div class="header-gtranslate">
                    <?php echo do_shortcode('[gtranslate]'); ?>
                </div>
                <div class="header-search">
                    <?php get_search_form(); ?>
                </div>
            </div>
        </div>

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
