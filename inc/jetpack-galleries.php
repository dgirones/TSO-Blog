<?php
/**
 * Jetpack compatibility and gallery / image block output for the built-in lightbox.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   8. JETPACK
   ============================================================ */
add_filter( 'jetpack_sharing_counts', '__return_false' );

/**
 * Whether Jetpack plugin is active.
 *
 * @return bool
 */
function tsothm_is_jetpack_active() {
    return defined( 'JETPACK__VERSION' ) || class_exists( 'Jetpack' );
}

/* ============================================================
   16. DESACTIVAR Jetpack Carousel completament + fix galeries Gutenberg
   ============================================================
   El Carousel de Jetpack intercepta les galeries (incloent les de
   Gutenberg wp-block-gallery) i les "segrest": mostra la galeria
   0.5s i després la substitueix pel seu propi visor. Hem de
   desactivar-lo per tots els camins possibles per usar el
   lightbox propi (tso-lightbox.js).
   ============================================================ */

// 1. Desactivar els recursos CSS/JS del Carousel
if ( tsothm_is_jetpack_active() ) {
    add_filter( 'jetpack_carousel_enqueue_resources', '__return_false' );

    // 2. Desactivar el mòdul Carousel de Jetpack si està actiu
    add_filter( 'jetpack_active_modules', function( $modules ) {
        if ( is_admin() ) {
            return $modules;
        }
        return array_diff( (array) $modules, array( 'carousel' ) );
    } );

    // 3. Forçar opció carousel a desactivat (només al frontend)
    add_filter( 'pre_option_carousel_display_exif', function( $value ) {
        return is_admin() ? $value : 0;
    } );
    add_filter( 'pre_option_jetpack_carousel_display_exif', function( $value ) {
        return is_admin() ? $value : 0;
    } );

    // 4. Eliminar l'acció que Jetpack usa per renderitzar el carousel en galeries Gutenberg
    add_action( 'wp_enqueue_scripts', function() {
        wp_dequeue_script( 'jetpack-carousel' );
        wp_dequeue_style( 'jetpack-carousel' );
    }, 99 );
}

/* ============================================================
   19. GALERIES GUTENBERG — forçar links directes a imatge
   ============================================================
   Gutenberg per defecte genera <a href="pàgina-adjunt"> en
   comptes de <a href="url-imatge-directa">. Això impedeix que
   el lightbox (tso-lightbox.js) pugui obrir la imatge.

   IMPORTANT: NO afegim classes tiled-gallery ni modifiquem
   l'estructura del bloc — això causava que Jetpack processés
   el DOM i eliminés les imatges (efecte "desapareix").

   Solució: només canviem l'href dels links d'adjunt per la
   URL de la imatge original, sense tocar res més.
   ============================================================ */
add_filter( 'render_block', function( $html, $block ) {
    $block_name = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';
    if ( 'core/gallery' !== $block_name ) {
        return $html;
    }

    $html = (string) $html;
    if ( '' === $html ) {
        return $html;
    }

    $result = preg_replace_callback(
        '/<a(\s[^>]*)href=["\']([^"\']*)["\']([^>]*)>\s*(<img\s[^>]*\bsrc=["\'])([^"\']+)(["\'][^>]*\/?>)\s*<\/a>/iU',
        function( $m ) {
            $href = isset( $m[2] ) ? (string) $m[2] : '';
            $img  = isset( $m[4] ) ? (string) $m[4] . ( isset( $m[5] ) ? $m[5] : '' ) . ( isset( $m[6] ) ? $m[6] : '' ) : '';
            $src  = isset( $m[5] ) ? (string) $m[5] : '';

            // Already points to a media file — keep.
            if ( preg_match( '/\.(jpe?g|png|gif|webp|avif|svg)(\?[^"\']*)?$/i', $href ) ) {
                return $m[0];
            }

            // Prefer full-size attachment URL from data-id / class wp-image-N.
            $attachment_id = 0;
            if ( preg_match( '/\bdata-id=["\'](\d+)["\']/', $img, $id_m ) ) {
                $attachment_id = absint( $id_m[1] );
            } elseif ( preg_match( '/\bwp-image-(\d+)\b/', $img, $id_m ) ) {
                $attachment_id = absint( $id_m[1] );
            }
            $full = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'full' ) : '';
            $new_href = $full ? $full : $src;
            if ( '' === $new_href ) {
                return $m[0];
            }

            return '<a' . $m[1] . 'href="' . esc_url( $new_href ) . '"' . $m[3] . '>' . $m[4] . $src . $m[6] . '</a>';
        },
        $html
    );

    return ( null !== $result ) ? $result : $html;
}, 10, 2 );

/* ============================================================
   20. BLOC IMATGE — sin wrapper forzado
   ============================================================
   Antes se envolvía toda imagen sin enlace en un <a> para el
   lightbox. Eso rompía linkDestination:none y el lightbox nativo
   de WP 6.4+. tso-lightbox.js abre <img> sueltas con
   bindStandaloneImages; no hace falta modificar el HTML del bloque.
   ============================================================ */

/* ============================================================
   24. JETPACK TILED GALLERY — afegir links per al lightbox
   ============================================================
   jetpack/tiled-gallery genera <figure><img data-url="...">
   sense cap <a> — el lightbox no pot interceptar res.
   Afegim un <a href="data-url"> al voltant de cada <img>.
   ============================================================ */
add_filter( 'render_block', function( $html, $block ) {
    if ( ! tsothm_is_jetpack_active() ) {
        return $html;
    }
    $block_name = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';
    if ( 'jetpack/tiled-gallery' !== $block_name ) {
        return $html;
    }

    $html = (string) $html;
    if ( '' === $html ) {
        return $html;
    }

    $result = preg_replace_callback(
        '/(<figure[^>]*\bclass=["\'][^"\']*\btiled-gallery__item\b[^"\']*["\'][^>]*>)\s*(<img\s[^>]*>)\s*(<\/figure>)/iU',
        function( $m ) {
            $img_tag = isset( $m[2] ) ? (string) $m[2] : '';

            $url = '';
            if ( preg_match( '/\bdata-url=["\']([^"\']+)["\']/', $img_tag, $u ) ) {
                $url = $u[1];
            } elseif ( preg_match( '/\bdata-orig-file=["\']([^"\']+)["\']/', $img_tag, $u ) ) {
                $url = $u[1];
            } elseif ( preg_match( '/\bsrc=["\']([^"\']+)["\']/', $img_tag, $u ) ) {
                $url = $u[1];
            }

            if ( '' === $url ) {
                return $m[0];
            }

            $alt = '';
            if ( preg_match( '/\balt=["\']([^"\']*)["\']/', $img_tag, $a ) ) {
                $alt = esc_attr( (string) $a[1] );
            }

            return $m[1]
                . '<a href="' . esc_url( $url ) . '" aria-label="' . $alt . '">'
                . $img_tag
                . '</a>'
                . $m[3];
        },
        $html
    );

    return ( null !== $result ) ? $result : $html;
}, 10, 2 );

/* ============================================================
   23. FIX bug transformació jetpack/tiled-gallery a Gutenberg
   ============================================================
   Carrega un JS fix que intercepta les transformacions del bloc
   i evita el TypeError quan attributes.images és undefined.
   ============================================================ */
add_action( 'enqueue_block_editor_assets', function() {
    if ( ! tsothm_is_jetpack_active() ) {
        return;
    }
    // Depends on wp-edit-post (which pulls wp-editor): load only in the post editor,
    // never on the widgets screen (wp-edit-widgets cannot coexist with wp-editor).
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen || 'post' !== $screen->base ) {
        return;
    }
    $js = get_stylesheet_directory() . '/tso-tiled-gallery-fix.js';
    if ( ! file_exists( $js ) ) {
        return;
    }
    wp_enqueue_script(
        'tsothm-tiled-gallery-fix',
        get_stylesheet_directory_uri() . '/tso-tiled-gallery-fix.js',
        array( 'wp-blocks', 'wp-dom-ready', 'wp-edit-post' ),
        filemtime( $js ),
        true
    );
} );
