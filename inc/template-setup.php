<?php
/**
 * Head cleanup, skip link, widget areas and logo loading priority.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   3. HEAD CLEANUP
   ============================================================
   Do not remove non-presentational wp_head hooks (oembed, rsd,
   shortlink, generator, emoji) — that is plugin territory for
   WordPress.org theme review. Use a plugin/mu-plugin if needed.
   ============================================================ */

/*
 * WP_POST_REVISIONS and DISALLOW_FILE_EDIT belong in wp-config.php
 * (see readme.txt → Installation). Do not define them in the theme.
 */

/* ============================================================
   4. ACCESSIBILITY — SKIP TO CONTENT
   ============================================================ */
add_action( 'wp_body_open', function() {
    echo '<a class="skip-to-content" href="#primary">' . esc_html__( 'Skip to content', 'tso-blog' ) . '</a>';
} );

/* ============================================================
   6. SIDEBARS
   ============================================================ */
add_action( 'widgets_init', function() {
    $widget_args = array(
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    );

    register_sidebar( array_merge( $widget_args, array(
        'name'        => __( 'News Sidebar', 'tso-blog' ),
        'id'          => 'sidebar-1',
        'description' => __( 'Sidebar column. Shown on posts, pages (default template), archives and search. Optionally also on the blog home, Contact and Portfolio (Appearance → Sidebar). If the Customizer says another area is not displayed, open a view that shows the sidebar.', 'tso-blog' ),
    ) ) );
    register_sidebar( array_merge( $widget_args, array(
        'name'        => __( 'Footer column 1', 'tso-blog' ),
        'id'          => 'footer-1',
        'description' => __( 'Footer (menus, text…). If you only use this column and leave 2 and 3 empty, menus display horizontally. With 2 or 3 columns, equal-column layout.', 'tso-blog' ),
    ) ) );
    register_sidebar( array_merge( $widget_args, array(
        'name'        => __( 'Footer column 2', 'tso-blog' ),
        'id'          => 'footer-2',
        'description' => __( 'Second column. Images: use 600×450 px (4:3) or 600×600 (square). To match column 3, both photos must have exactly the same size and aspect ratio.', 'tso-blog' ),
    ) ) );
    register_sidebar( array_merge( $widget_args, array(
        'name'        => __( 'Footer column 3', 'tso-blog' ),
        'id'          => 'footer-3',
        'description' => __( 'Third column. Images: 600×450 px (4:3) or 600×600 (square), the same size as column 2. If one is portrait and the other landscape, they will not align.', 'tso-blog' ),
    ) ) );
} );

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
