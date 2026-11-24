<?php
/**
 * Front-end styles and scripts.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'tsothm-style', get_stylesheet_uri(), array(), tsothm_get_asset_version( 'style.css' ) );
    wp_add_inline_style( 'tsothm-style', tsothm_get_theme_css_variables() );

    if ( is_singular() && comments_open() ) {
        wp_enqueue_script( 'comment-reply' );
    }

    $theme_uri = get_stylesheet_directory_uri();

    wp_enqueue_script(
        'tsothm-live-search',
        $theme_uri . '/tso-live-search.js',
        array(),
        tsothm_get_asset_version( 'tso-live-search.js' ),
        true
    );
    wp_localize_script(
        'tsothm-live-search',
        'tsothmSearch',
        array(
            'ajaxurl'          => admin_url( 'admin-ajax.php' ),
            'nonce'            => wp_create_nonce( 'tsothm_live_search_nonce' ),
            'loadMoreCacheKey' => 'tsothm_loadmore_state',
            'i18n'             => array(
                'searching'  => __( 'Searching…', 'tso-blog' ),
                'noResults'  => __( 'No posts found.', 'tso-blog' ),
            ),
        )
    );

    if ( is_home() ) {
        wp_enqueue_script(
            'tsothm-load-more',
            $theme_uri . '/tso-load-more.js',
            array(),
            tsothm_get_asset_version( 'tso-load-more.js' ),
            true
        );
        wp_localize_script(
            'tsothm-load-more',
            'tsothmLoadMore',
            array(
                'cacheKey'    => 'tsothm_loadmore_state',
                'loadText'    => tsothm_load_more_text(),
                'loadingText' => __( 'Loading...', 'tso-blog' ),
            )
        );
    }

    wp_enqueue_script(
        'tsothm-widget-css-fix',
        $theme_uri . '/tso-widget-css-fix.js',
        array(),
        tsothm_get_asset_version( 'tso-widget-css-fix.js' ),
        true
    );

    if ( tsothm_is_announcement_enabled() ) {
        $announce_js = get_stylesheet_directory() . '/tsothm-announcement.js';
        if ( file_exists( $announce_js ) ) {
            wp_enqueue_script(
                'tsothm-announcement',
                $theme_uri . '/tsothm-announcement.js',
                array(),
                tsothm_get_asset_version( 'tsothm-announcement.js' ),
                true
            );
        }
    }

    if ( is_singular() ) {
        $lightbox = get_stylesheet_directory() . '/tso-lightbox.js';
        if ( file_exists( $lightbox ) ) {
            wp_enqueue_script(
                'tsothm-lightbox',
                $theme_uri . '/tso-lightbox.js',
                array(),
                tsothm_get_asset_version( 'tso-lightbox.js' ),
                true
            );
            wp_localize_script(
                'tsothm-lightbox',
                'tsothmLightbox',
                array(
                    'viewer' => __( 'Image viewer', 'tso-blog' ),
                    'close'  => __( 'Close', 'tso-blog' ),
                    'prev'   => __( 'Previous image', 'tso-blog' ),
                    'next'   => __( 'Next image', 'tso-blog' ),
                )
            );
        }
    }

    if ( is_page() && tsothm_is_contact_page() ) {
        $map_js = get_stylesheet_directory() . '/tso-contact-map.js';
        if ( file_exists( $map_js ) ) {
            wp_enqueue_script(
                'tsothm-contact-map',
                $theme_uri . '/tso-contact-map.js',
                array(),
                tsothm_get_asset_version( 'tso-contact-map.js' ),
                true
            );
        }
    }
} );
