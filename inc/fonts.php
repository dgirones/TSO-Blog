<?php
/**
 * Bundled web fonts (assets/fonts): enqueued only for the families selected in the Customizer.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue the bundled web fonts (assets/fonts) only for the families selected in the Customizer.
 * No remote requests: every font file ships with the theme.
 */
function tsothm_enqueue_local_fonts() {
    $catalog = tsothm_get_font_catalog();
    $typo    = tsothm_get_typography_settings();
    $done    = array();

    foreach ( array( $typo['body_key'], $typo['heading_key'], $typo['announce_key'], $typo['nav_key'] ) as $key ) {
        if ( empty( $catalog[ $key ]['local'] ) || isset( $done[ $key ] ) ) {
            continue;
        }
        $done[ $key ] = true;
        $file         = sanitize_file_name( $catalog[ $key ]['local'] ) . '.css';
        wp_enqueue_style(
            'tsothm-font-' . sanitize_key( $key ),
            get_template_directory_uri() . '/assets/fonts/' . $file,
            array(),
            TSOTHM_VERSION
        );
    }
}
add_action( 'wp_enqueue_scripts', 'tsothm_enqueue_local_fonts', 5 );
