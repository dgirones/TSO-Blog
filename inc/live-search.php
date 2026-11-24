<?php
/**
 * AJAX live search.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   13. AJAX — LIVE SEARCH
   ============================================================ */
add_action( 'wp_ajax_tsothm_live_search',        'tsothm_live_search_callback' );
add_action( 'wp_ajax_nopriv_tsothm_live_search', 'tsothm_live_search_callback' );

function tsothm_live_search_callback() {
    // Public, read-only search of published post titles: no nonce, because a nonce printed in a page-cached
    // document expires before the cache does and would make the search fail (403) for visitors.
    $term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public read-only query.
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
        $query_args['tsothm_title_only'] = true;
        add_filter( 'posts_search', 'tsothm_live_search_title_only', 10, 2 );
    }

    $query = new WP_Query( $query_args );

    if ( isset( $query_args['tsothm_title_only'] ) ) {
        remove_filter( 'posts_search', 'tsothm_live_search_title_only', 10 );
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
function tsothm_live_search_title_only( $search, $wp_query ) {
    if ( ! $wp_query->get( 'tsothm_title_only' ) ) {
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
