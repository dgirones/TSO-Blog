<?php
/**
 * Announcement bar: settings, rotation data and output.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize announcement display mode.
 *
 * @param mixed $value Raw value.
 * @return string rotate|single
 */
function tsothm_sanitize_announcement_mode( $value ) {
    $value = sanitize_key( (string) $value );
    return in_array( $value, array( 'rotate', 'single' ), true ) ? $value : 'rotate';
}

/**
 * Sanitize announcement slot index (1–5).
 *
 * @param mixed $value Raw value.
 * @return int
 */
function tsothm_sanitize_announcement_slot( $value ) {
    return max( 1, min( 5, absint( $value ) ) );
}

/**
 * Sanitize rotation interval in seconds.
 *
 * @param mixed $value Raw value.
 * @return int
 */
function tsothm_sanitize_announcement_interval( $value ) {
    return max( 15, min( 600, absint( $value ) ) );
}

/**
 * Announcement movement effect choices.
 *
 * @return array<string, string>
 */
function tsothm_get_announcement_effect_choices() {
    return array(
        'slide' => __( 'Slide from the side', 'tso-blog' ),
        'fade'  => __( 'Fade in / fade out', 'tso-blog' ),
        'rise'  => __( 'Rise from below', 'tso-blog' ),
    );
}

/**
 * Sanitize announcement movement effect.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function tsothm_sanitize_announcement_effect( $value ) {
    $value = sanitize_key( (string) $value );
    return isset( tsothm_get_announcement_effect_choices()[ $value ] ) ? $value : 'slide';
}

/**
 * Current announcement movement effect key.
 *
 * @return string
 */
function tsothm_get_announcement_effect() {
    return tsothm_sanitize_announcement_effect( get_theme_mod( 'tsothm_announcement_effect', 'slide' ) );
}

/**
 * Configured announcements (non-empty text only).
 *
 * @return array<int, array{text: string, url: string, new_tab: bool}>
 */
function tsothm_get_announcements() {
    $items = array();

    for ( $i = 1; $i <= 5; $i++ ) {
        $text = trim( (string) get_theme_mod( 'tsothm_announcement_' . $i . '_text', '' ) );

        // Legacy single-announcement settings → slot 1.
        if ( '' === $text && 1 === $i ) {
            $text = trim( (string) get_theme_mod( 'tsothm_announcement_text', '' ) );
        }

        if ( '' === $text ) {
            continue;
        }

        $url = (string) get_theme_mod( 'tsothm_announcement_' . $i . '_url', '' );
        if ( '' === $url && 1 === $i ) {
            $url = (string) get_theme_mod( 'tsothm_announcement_url', '' );
        }

        $new_tab = (int) get_theme_mod( 'tsothm_announcement_' . $i . '_new_tab', 1 );
        if ( 1 === $i && '' === (string) get_theme_mod( 'tsothm_announcement_1_text', '' ) && '' !== (string) get_theme_mod( 'tsothm_announcement_text', '' ) ) {
            $new_tab = (int) get_theme_mod( 'tsothm_announcement_new_tab', 1 );
        }

        $items[] = array(
            'text'    => $text,
            'url'     => $url,
            'new_tab' => (bool) $new_tab,
            'slot'    => $i,
        );
    }

    return $items;
}

/**
 * Announcements to render according to mode (rotate vs single).
 *
 * @return array<int, array{text: string, url: string, new_tab: bool}>
 */
function tsothm_get_announcements_for_display() {
    $all = tsothm_get_announcements();
    if ( empty( $all ) ) {
        return array();
    }

    $mode = tsothm_sanitize_announcement_mode( get_theme_mod( 'tsothm_announcement_mode', 'rotate' ) );
    if ( 'single' !== $mode ) {
        return $all;
    }

    $slot = tsothm_sanitize_announcement_slot( get_theme_mod( 'tsothm_announcement_single', 1 ) );
    foreach ( $all as $item ) {
        if ( (int) $item['slot'] === $slot ) {
            return array( $item );
        }
    }

    // Chosen slot empty: fall back to first filled announcement.
    return array( $all[0] );
}

/**
 * Whether the header announcement bar should render.
 *
 * @return bool
 */
function tsothm_is_announcement_enabled() {
    if ( ! (int) get_theme_mod( 'tsothm_announcement_enable', 0 ) ) {
        return false;
    }
    return ! empty( tsothm_get_announcements_for_display() );
}

/**
 * Markup for one announcement slide.
 *
 * @param array $item Announcement data.
 * @param bool  $active Whether this slide is the first/active one.
 */
function tsothm_render_announcement_slide( $item, $active = false ) {
    $classes = 'tsothm-announcement-slide';
    if ( $active ) {
        // is-instant: no enter animation on first paint / soft navigations.
        $classes .= ' is-active is-instant';
    }

    echo '<div class="' . esc_attr( $classes ) . '" role="group">';
    if ( ! empty( $item['url'] ) ) {
        printf(
            '<a class="tsothm-announcement-link" href="%1$s"%2$s><span class="tsothm-announcement-text">%3$s</span></a>',
            esc_url( $item['url'] ),
            ! empty( $item['new_tab'] ) ? ' target="_blank" rel="noopener noreferrer"' : '',
            esc_html( $item['text'] )
        );
    } else {
        echo '<span class="tsothm-announcement-text">' . esc_html( $item['text'] ) . '</span>';
    }
    echo '</div>';
}

/**
 * Inner markup for the announcement bar (no Customizer root wrapper).
 */
function tsothm_render_announcement_bar() {
    if ( ! tsothm_is_announcement_enabled() ) {
        return;
    }

    $items    = tsothm_get_announcements_for_display();
    $mode     = tsothm_sanitize_announcement_mode( get_theme_mod( 'tsothm_announcement_mode', 'rotate' ) );
    $interval = tsothm_sanitize_announcement_interval( get_theme_mod( 'tsothm_announcement_interval', 60 ) );
    $effect   = tsothm_get_announcement_effect();
    $rotate   = ( 'rotate' === $mode && count( $items ) > 1 );

    printf(
        '<div class="tsothm-announcement tsothm-announcement--%4$s" role="region" aria-label="%1$s" data-rotate="%2$s" data-interval="%3$d" data-effect="%4$s">',
        esc_attr__( 'Announcement', 'tso-blog' ),
        $rotate ? '1' : '0',
        (int) $interval,
        esc_attr( $effect )
    );
    echo '<div class="tsothm-announcement-viewport">';

    foreach ( $items as $index => $item ) {
        tsothm_render_announcement_slide( $item, 0 === $index );
    }

    echo '</div></div>';
}

/**
 * Print the header announcement / news bar (stable root for Customizer shortcuts).
 */
function tsothm_header_announcement() {
    $empty = ! tsothm_is_announcement_enabled();
    printf(
        '<div id="tsothm-announcement-root"%s>',
        $empty ? ' class="tsothm-announcement-root--empty"' : ''
    );
    tsothm_render_announcement_bar();
    echo '</div>';
}

/**
 * Customizer selective refresh: announcement bar root.
 */
function tsothm_customize_partial_announcement() {
    tsothm_header_announcement();
}

/**
 * Body class when the announcement bar is visible.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function tsothm_announcement_body_class( $classes ) {
    if ( tsothm_is_announcement_enabled() ) {
        $classes[] = 'tsothm-has-announcement';
    }
    return $classes;
}
add_filter( 'body_class', 'tsothm_announcement_body_class' );
