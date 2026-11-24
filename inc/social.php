<?php
/**
 * Header social networks: catalog, order and icon output.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Supported header social networks (slug => label + SVG path).
 *
 * @return array<string, array{label: string, path: string}>
 */
function tsothm_get_social_networks() {
    return array(
        'youtube'   => array(
            'label' => 'YouTube',
            'path'  => '<path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.6A3 3 0 0 0 .5 6.2C0 8.1 0 12 0 12s0 3.9.5 5.8a3 3 0 0 0 2.1 2.1c1.9.6 9.4.6 9.4.6s7.5 0 9.4-.6a3 3 0 0 0 2.1-2.1C24 15.9 24 12 24 12s0-3.9-.5-5.8zM9.75 15.5v-7l6.5 3.5-6.5 3.5z"/>',
        ),
        'linkedin'  => array(
            'label' => 'LinkedIn',
            'path'  => '<path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.13 1.45-2.13 2.94v5.67H9.37V9h3.41v1.56h.05a3.74 3.74 0 0 1 3.37-1.85c3.6 0 4.27 2.37 4.27 5.45v6.29zM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12zM7.12 20.45H3.56V9h3.56v11.45zM22.22 0H1.77A1.75 1.75 0 0 0 0 1.73v20.54A1.75 1.75 0 0 0 1.77 24h20.45A1.76 1.76 0 0 0 24 22.27V1.73A1.76 1.76 0 0 0 22.22 0z"/>',
        ),
        'facebook'  => array(
            'label' => 'Facebook',
            'path'  => '<path d="M24 12.07C24 5.41 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.04V9.41c0-3.02 1.8-4.7 4.54-4.7 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.95.93-1.95 1.88v2.26h3.32l-.53 3.5h-2.79V24C19.61 23.1 24 18.1 24 12.07z"/>',
        ),
        'twitter'   => array(
            'label' => 'X / Twitter',
            'path'  => '<path d="M18.24 2h3.28L13.9 10.28 22.8 22h-6.91l-5.45-7.14L4.24 22H.95l8.1-9.27L.54 2h7.08l4.93 6.51L18.24 2zm-1.15 18h1.82L7 3.92H5.06L17.09 20z"/>',
        ),
        'instagram' => array(
            'label' => 'Instagram',
            'path'  => '<path d="M12 2.16c3.2 0 3.58.01 4.85.07 3.25.15 4.77 1.69 4.92 4.92.06 1.27.07 1.65.07 4.85 0 3.2-.01 3.58-.07 4.85-.15 3.23-1.66 4.77-4.92 4.92-1.27.06-1.64.07-4.85.07-3.2 0-3.58-.01-4.85-.07-3.26-.15-4.77-1.7-4.92-4.92C2.17 15.58 2.16 15.2 2.16 12c0-3.2.01-3.58.07-4.85C2.38 3.7 3.9 2.16 7.15 2.09 8.42 2.17 8.8 2.16 12 2.16zM12 0C8.74 0 8.33.01 7.05.07 2.7.27.27 2.7.07 7.05.01 8.33 0 8.74 0 12c0 3.26.01 3.67.07 4.95.2 4.36 2.62 6.78 6.98 6.98C8.33 23.99 8.74 24 12 24c3.26 0 3.67-.01 4.95-.07 4.35-.2 6.78-2.62 6.98-6.98.06-1.28.07-1.69.07-4.95 0-3.26-.01-3.67-.07-4.95-.2-4.35-2.62-6.78-6.98-6.98C15.67.01 15.26 0 12 0zm0 5.84a6.16 6.16 0 1 0 0 12.32A6.16 6.16 0 0 0 12 5.84zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.4-11.85a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88z"/>',
        ),
        'tiktok'    => array(
            'label' => 'TikTok',
            'path'  => '<path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/>',
        ),
        'telegram'  => array(
            'label' => 'Telegram',
            'path'  => '<path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>',
        ),
        'whatsapp'  => array(
            'label' => 'WhatsApp',
            'path'  => '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>',
        ),
        'pinterest' => array(
            'label' => 'Pinterest',
            'path'  => '<path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z"/>',
        ),
        'threads'   => array(
            'label' => 'Threads',
            'path'  => '<path d="M16.6 12.2c0 2.4-1.6 4.2-4.1 4.4-1.3.1-2.5-.3-3.4-1.1-.2-.2-.2-.5 0-.6.2-.2.5-.2.6 0 .8.7 1.8 1 2.9.9 2-.1 3.3-1.4 3.3-3.3 0-1.8-1.2-2.9-3.1-2.9-1.6 0-2.8.9-3.1 2.2h2.1c.2 0 .4.2.4.4s-.2.4-.4.4H8.4c-.2 0-.4-.2-.4-.4 0-2 1.7-3.6 3.9-3.6 2.5 0 4.1 1.5 4.1 3.7v.3h.6zm2.9-.2c0 .7 0 1.3-.1 2-.6 4.4-4.4 7.7-8.9 7.7S2.2 18.4 1.6 14c-.1-.7-.1-1.3-.1-2s0-1.3.1-2C2.2 5.6 6 2.3 10.5 2.3s8.3 3.3 8.9 7.7c.1.7.1 1.3.1 2z"/>',
        ),
        'github'    => array(
            'label' => 'GitHub',
            'path'  => '<path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>',
        ),
    );
}

/**
 * Default social order as comma-separated catalog keys.
 *
 * @return string
 */
function tsothm_get_default_social_order_string() {
    return implode( ',', array_keys( tsothm_get_social_networks() ) );
}

/**
 * Sanitize comma-separated social network order list.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function tsothm_sanitize_social_order_list( $value ) {
    $allowed = array_keys( tsothm_get_social_networks() );
    $parts   = array_filter( array_map( 'sanitize_key', explode( ',', (string) $value ) ) );
    $ordered = array();

    foreach ( $parts as $part ) {
        if ( in_array( $part, $allowed, true ) && ! in_array( $part, $ordered, true ) ) {
            $ordered[] = $part;
        }
    }

    foreach ( $allowed as $key ) {
        if ( ! in_array( $key, $ordered, true ) ) {
            $ordered[] = $key;
        }
    }

    return implode( ',', $ordered );
}

/**
 * Ordered social network keys (supports legacy per-network order theme mods).
 *
 * @return string[]
 */
function tsothm_get_social_order_keys() {
    $saved = get_theme_mod( 'tsothm_social_order', '' );

    if ( '' !== $saved && null !== $saved ) {
        return explode( ',', tsothm_sanitize_social_order_list( $saved ) );
    }

    // Legacy: tsothm_social_order_{network} numeric fields (1–99).
    $networks   = tsothm_get_social_networks();
    $with_order = array();
    $index      = 0;
    $has_legacy = false;

    foreach ( $networks as $key => $data ) {
        ++$index;
        $legacy = get_theme_mod( 'tsothm_social_order_' . $key, null );
        if ( null !== $legacy && '' !== $legacy ) {
            $has_legacy         = true;
            $with_order[ $key ] = max( 1, min( 99, absint( $legacy ) ) );
        } else {
            $with_order[ $key ] = $index * 10;
        }
    }

    if ( $has_legacy ) {
        asort( $with_order, SORT_NUMERIC );
        return array_keys( $with_order );
    }

    return array_keys( $networks );
}

/**
 * Networks that have a URL, sorted by Customizer drag order.
 *
 * @return array<string, array{label: string, path: string, url: string}>
 */
function tsothm_get_configured_social_icons() {
    $networks = tsothm_get_social_networks();
    $items    = array();

    foreach ( tsothm_get_social_order_keys() as $key ) {
        if ( ! isset( $networks[ $key ] ) ) {
            continue;
        }
        $url = (string) get_theme_mod( 'tsothm_social_' . $key, '' );
        if ( '' === $url ) {
            continue;
        }
        $items[ $key ] = array(
            'label' => $networks[ $key ]['label'],
            'path'  => $networks[ $key ]['path'],
            'url'   => $url,
        );
    }

    return $items;
}

/* ============================================================
   11. REDES SOCIALES — ICONOS CON ESTILOS INLINE (FOUC-proof)
   Los tamaños se fijan en style="" directamente en el HTML.
   Ningún plugin de caché puede diferir o mover atributos HTML.
   ============================================================ */
function tsothm_social_icons() {
    $items = tsothm_get_configured_social_icons();
    if ( empty( $items ) ) {
        return;
    }

    // Estilos inline en cada elemento: imposible diferir para LiteSpeed u otro plugin de caché
    $a_style   = 'display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background-color:var(--tso-color-primary);overflow:hidden;flex-shrink:0;text-decoration:none;';
    $svg_style = 'width:15px;height:15px;fill:#ffffff;display:block;flex-shrink:0;pointer-events:none;';

    echo '<div class="header-social-icons" aria-label="' . esc_attr__( 'Social networks', 'tso-blog' ) . '">';
    foreach ( $items as $key => $data ) {
        printf(
            '<a href="%s" class="social-icon social-icon--%s" style="%s" target="_blank" rel="noopener noreferrer" aria-label="%s">'
            . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="%s" aria-hidden="true" focusable="false">%s</svg>'
            . '</a>',
            esc_url( $data['url'] ),
            esc_attr( $key ),
            esc_attr( $a_style ),
            esc_attr( $data['label'] ),
            esc_attr( $svg_style ),
            $data['path'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG path markup defined by the theme.
        );
    }
    echo '</div>';
}
