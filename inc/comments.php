<?php
/**
 * Comment form texts, comment links and cache-plugin compatibility for comments.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
function tsothm_comment_form_defaults_es( $defaults ) {
    if ( ! is_singular( 'post' ) ) {
        return $defaults;
    }
    $defaults['title_reply']          = __( 'Leave a comment', 'tso-blog' );
    /* translators: %s: name of the author being replied to */
    $defaults['title_reply_to']       = __( 'Reply to %s', 'tso-blog' );
    $defaults['cancel_reply_link']    = __( 'Cancel reply', 'tso-blog' );
    $defaults['label_submit']         = __( 'Post comment', 'tso-blog' );
    $defaults['comment_notes_before'] = '<p class="comment-notes">' . __( 'Your email address will not be published. Required fields are marked with', 'tso-blog' ) . ' <span aria-hidden="true">*</span></p>';
    $defaults['comment_notes_after']  = '';
    return $defaults;
}
add_filter( 'comment_form_defaults', 'tsothm_comment_form_defaults_es' );

/**
 * Translate comment form field labels without breaking HTML attributes.
 *
 * @param array $fields Comment form fields.
 * @return array
 */
function tsothm_comment_form_fields_es( $fields ) {
    if ( ! is_singular( 'post' ) ) {
        return $fields;
    }
    if ( isset( $fields['author'] ) ) {
        $fields['author'] = preg_replace(
            '/<label for="author">(.*?)<\/label>/',
            '<label for="author">' . esc_html__( 'Name', 'tso-blog' ) . ' <span class="required" aria-hidden="true">*</span></label>',
            $fields['author'],
            1
        );
    }
    if ( isset( $fields['email'] ) ) {
        $fields['email'] = preg_replace(
            '/<label for="email">(.*?)<\/label>/',
            '<label for="email">' . esc_html__( 'Email', 'tso-blog' ) . ' <span class="required" aria-hidden="true">*</span></label>',
            $fields['email'],
            1
        );
    }
    if ( isset( $fields['url'] ) ) {
        $fields['url'] = preg_replace(
            '/<label for="url">(.*?)<\/label>/',
            '<label for="url">' . esc_html__( 'Website', 'tso-blog' ) . '</label>',
            $fields['url'],
            1
        );
    }
    return $fields;
}
add_filter( 'comment_form_default_fields', 'tsothm_comment_form_fields_es', 20 );

/**
 * Translate the comment textarea label.
 *
 * @param string $field Comment field HTML.
 * @return string
 */
function tsothm_comment_form_field_comment_es( $field ) {
    if ( ! is_singular( 'post' ) ) {
        return $field;
    }
    return preg_replace(
        '/<label for="comment">(.*?)<\/label>/',
        '<label for="comment">' . esc_html__( 'Comment', 'tso-blog' ) . ' <span class="required" aria-hidden="true">*</span></label>',
        $field,
        1
    );
}
add_filter( 'comment_form_field_comment', 'tsothm_comment_form_field_comment_es' );

/**
 * Open links inside comment body in a new tab (security: noopener noreferrer).
 *
 * Runs after WordPress make_clickable() on comment_text.
 *
 * @param string $text Comment HTML.
 * @return string
 */
function tsothm_comment_links_open_new_tab( $text ) {
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
add_filter( 'comment_text', 'tsothm_comment_links_open_new_tab', 99 );

/**
 * Open comment author website links in a new tab.
 *
 * @param string $link    Author link HTML.
 * @param string $author  Author name.
 * @param int    $comment_id Comment ID.
 * @return string
 */
function tsothm_comment_author_link_new_tab( $link, $author, $comment_id ) {
    unset( $author, $comment_id );
    if ( '' === $link || false === stripos( $link, '<a' ) ) {
        return $link;
    }
    return tsothm_comment_links_open_new_tab( $link );
}
add_filter( 'get_comment_author_link', 'tsothm_comment_author_link_new_tab', 10, 3 );

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
