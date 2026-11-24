<?php
/**
 * Portfolio / Wallpaper gallery page template.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   6b. PORTFOLIO / WALLPAPER GALLERY (page template)
   ============================================================ */

/**
 * Wallpaper download sizes (Customizer image size key => meta).
 *
 * @return array<string, array{label: string, width: int, height: int}>
 */
function tsothm_get_wallpaper_sizes() {
    return array(
        'tsothm-wall-720'    => array(
            'label'  => __( '1280 × 720 (HD)', 'tso-blog' ),
            'width'  => 1280,
            'height' => 720,
        ),
        'tsothm-wall-1080'   => array(
            'label'  => __( '1920 × 1080 (Full HD)', 'tso-blog' ),
            'width'  => 1920,
            'height' => 1080,
        ),
        'tsothm-wall-1440'   => array(
            'label'  => __( '2560 × 1440 (QHD)', 'tso-blog' ),
            'width'  => 2560,
            'height' => 1440,
        ),
        'tsothm-wall-4k'     => array(
            'label'  => __( '3840 × 2160 (4K)', 'tso-blog' ),
            'width'  => 3840,
            'height' => 2160,
        ),
        'tsothm-wall-mobile' => array(
            'label'  => __( '1080 × 1920 (Mobile)', 'tso-blog' ),
            'width'  => 1080,
            'height' => 1920,
        ),
    );
}

/**
 * Whether the current (or given) page uses the portfolio gallery template.
 *
 * @param int $post_id Optional post ID.
 * @return bool
 */
function tsothm_is_portfolio_gallery_page( $post_id = 0 ) {
    $post_id = $post_id ? (int) $post_id : (int) get_the_ID();
    if ( ! $post_id ) {
        return false;
    }
    return 'page-templates/portfolio-gallery.php' === get_page_template_slug( $post_id );
}

/**
 * Recursively collect image attachment IDs from parsed blocks.
 *
 * @param array $blocks Parsed blocks.
 * @return int[]
 */
function tsothm_extract_image_ids_from_blocks( $blocks ) {
    $ids = array();

    foreach ( (array) $blocks as $block ) {
        if ( empty( $block['blockName'] ) ) {
            if ( ! empty( $block['innerBlocks'] ) ) {
                $ids = array_merge( $ids, tsothm_extract_image_ids_from_blocks( $block['innerBlocks'] ) );
            }
            continue;
        }

        $name = $block['blockName'];
        $attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();

        if ( 'core/image' === $name && ! empty( $attrs['id'] ) ) {
            $ids[] = absint( $attrs['id'] );
        }

        if ( 'core/gallery' === $name ) {
            if ( ! empty( $attrs['ids'] ) && is_array( $attrs['ids'] ) ) {
                foreach ( $attrs['ids'] as $gid ) {
                    $ids[] = absint( $gid );
                }
            }
        }

        if ( ! empty( $block['innerBlocks'] ) ) {
            $ids = array_merge( $ids, tsothm_extract_image_ids_from_blocks( $block['innerBlocks'] ) );
        }
    }

    return $ids;
}

/**
 * Image IDs for a portfolio page (Gallery/Image blocks, then attachments).
 *
 * @param int $post_id Page ID.
 * @return int[]
 */
function tsothm_get_portfolio_image_ids( $post_id ) {
    $post_id = absint( $post_id );
    $post    = get_post( $post_id );
    $ids     = array();

    if ( ! $post ) {
        return array();
    }

    if ( has_blocks( $post->post_content ) ) {
        $ids = tsothm_extract_image_ids_from_blocks( parse_blocks( $post->post_content ) );
    }

    if ( empty( $ids ) && has_shortcode( $post->post_content, 'gallery' ) ) {
        if ( preg_match_all( '/\[gallery[^\]]*(?:ids|include)=["\']([^"\']+)["\']/', $post->post_content, $matches ) ) {
            foreach ( $matches[1] as $list ) {
                foreach ( explode( ',', $list ) as $raw_id ) {
                    $ids[] = absint( $raw_id );
                }
            }
        }
    }

    if ( empty( $ids ) ) {
        $media = get_attached_media( 'image', $post_id );
        foreach ( $media as $attachment ) {
            $ids[] = (int) $attachment->ID;
        }
    }

    $ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );

    /**
     * Filter portfolio gallery image IDs.
     *
     * @param int[] $ids     Attachment IDs.
     * @param int   $post_id Page ID.
     */
    return apply_filters( 'tsothm_portfolio_image_ids', $ids, $post_id );
}

/**
 * Wallpaper crops (tsothm-wall-*) are big, hard-cropped files. They are only
 * generated for images that belong to a portfolio gallery page — uploaded from
 * that page, or referenced by it when it is saved — not for every upload.
 *
 * @param bool|null $set Optional. True/false to force generation; omit to read.
 * @return bool
 */
function tsothm_wallpaper_sizes_forced( $set = null ) {
    static $forced = false;
    if ( null !== $set ) {
        $forced = (bool) $set;
    }
    return $forced;
}

/**
 * Drop wallpaper sizes for images that are not part of a portfolio page.
 *
 * @param array $sizes         Sizes about to be generated.
 * @param array $image_meta    Attachment metadata.
 * @param int   $attachment_id Attachment ID.
 * @return array
 */
function tsothm_limit_wallpaper_sizes( $sizes, $image_meta = array(), $attachment_id = 0 ) {
    // Sin attachment concreto (p. ej. TSO Image Master consultando qué tamaños están registrados) no se filtra nada:
    // si no, los tamaños tsothm-wall-* parecerían huérfanos y se podrían borrar.
    if ( ! $attachment_id || tsothm_wallpaper_sizes_forced() ) {
        return $sizes;
    }
    $parent_id = $attachment_id ? (int) wp_get_post_parent_id( $attachment_id ) : 0;
    if ( $parent_id && tsothm_is_portfolio_gallery_page( $parent_id ) ) {
        return $sizes;
    }
    return array_diff_key( (array) $sizes, tsothm_get_wallpaper_sizes() );
}
add_filter( 'intermediate_image_sizes_advanced', 'tsothm_limit_wallpaper_sizes', 10, 3 );

/**
 * When a portfolio page is saved, create any missing wallpaper crops for the
 * images it uses (covers images uploaded earlier from the Media Library).
 *
 * @param int     $post_id Page ID.
 * @param WP_Post $post    Page object.
 */
function tsothm_generate_portfolio_wallpaper_sizes( $post_id, $post ) {
    if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) || ! tsothm_is_portfolio_gallery_page( $post_id ) ) {
        return;
    }
    if ( ! function_exists( 'wp_update_image_subsizes' ) ) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    tsothm_wallpaper_sizes_forced( true );
    foreach ( tsothm_get_portfolio_image_ids( $post_id ) as $attachment_id ) {
        if ( tsothm_is_portfolio_image_mime( $attachment_id ) ) {
            wp_update_image_subsizes( $attachment_id );
        }
    }
    tsothm_wallpaper_sizes_forced( false );
}
add_action( 'save_post_page', 'tsothm_generate_portfolio_wallpaper_sizes', 20, 2 );

/**
 * Whether an attachment is a supported raster image for the portfolio.
 *
 * @param int $attachment_id Attachment ID.
 * @return bool
 */
function tsothm_is_portfolio_image_mime( $attachment_id ) {
    $mime = get_post_mime_type( $attachment_id );
    $ok   = array( 'image/jpeg', 'image/png', 'image/webp', 'image/gif' );
    return $mime && in_array( $mime, $ok, true );
}

/**
 * Display name under a portfolio image.
 *
 * @param int $attachment_id Attachment ID.
 * @return string
 */
function tsothm_get_portfolio_image_label( $attachment_id ) {
    $title = get_the_title( $attachment_id );
    if ( $title ) {
        return $title;
    }
    $alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
    if ( is_string( $alt ) && '' !== $alt ) {
        return $alt;
    }
    $file = get_attached_file( $attachment_id );
    return $file ? pathinfo( $file, PATHINFO_FILENAME ) : __( 'Image', 'tso-blog' );
}

/**
 * Available download URLs for an attachment.
 * Always returns Original + every wallpaper size (unavailable ones marked).
 *
 * @param int $attachment_id Attachment ID.
 * @return array<int, array{key: string, label: string, url: string, available: bool, width: int, height: int, tip: string}>
 */
function tsothm_get_portfolio_downloads( $attachment_id ) {
    $attachment_id = absint( $attachment_id );
    $downloads     = array();
    $full          = wp_get_attachment_image_src( $attachment_id, 'full' );

    $full_w = ( $full && ! empty( $full[1] ) ) ? (int) $full[1] : 0;
    $full_h = ( $full && ! empty( $full[2] ) ) ? (int) $full[2] : 0;

    if ( $full && ! empty( $full[0] ) ) {
        $downloads[] = array(
            'key'       => 'full',
            'label'     => __( 'Original', 'tso-blog' ),
            'url'       => $full[0],
            'available' => true,
            'width'     => $full_w,
            'height'    => $full_h,
            'tip'       => '',
        );
    }

    foreach ( tsothm_get_wallpaper_sizes() as $size_key => $meta ) {
        $url       = '';
        $available = false;
        $data      = image_get_intermediate_size( $attachment_id, $size_key );

        if ( ! empty( $data['url'] ) && (int) $data['width'] === (int) $meta['width'] && (int) $data['height'] === (int) $meta['height'] ) {
            $url       = $data['url'];
            $available = true;
        } elseif ( $full_w >= (int) $meta['width'] && $full_h >= (int) $meta['height'] ) {
            // Source is large enough but crop missing (old upload) — tip to regenerate.
            $tip = __( 'Size not generated yet. Regenerate thumbnails or re-upload the image.', 'tso-blog' );
        } else {
            $tip = sprintf(
                /* translators: 1: required width, 2: required height, 3: original width, 4: original height */
                __( 'Unavailable: at least %1$s×%2$s px required. This image is %3$s×%4$s px.', 'tso-blog' ),
                (int) $meta['width'],
                (int) $meta['height'],
                $full_w,
                $full_h
            );
        }

        $downloads[] = array(
            'key'       => $size_key,
            'label'     => $meta['label'],
            'url'       => $url,
            'available' => $available,
            'width'     => (int) $meta['width'],
            'height'    => (int) $meta['height'],
            'tip'       => isset( $tip ) ? $tip : '',
        );
        unset( $tip );
    }

    return $downloads;
}

/**
 * Strip gallery/image blocks from content (portfolio template intro only).
 *
 * @param string $content Post content.
 * @return string
 */
function tsothm_portfolio_intro_from_content( $content ) {
    if ( ! has_blocks( $content ) ) {
        $content = preg_replace( '/\[gallery[\s\S]*?\]/', '', $content );
        $content = preg_replace( '/\[caption[\s\S]*?\[\/caption\]/', '', $content );
        $content = preg_replace( '/<img\b[^>]*>/i', '', $content );
        return trim( $content );
    }

    $blocks  = parse_blocks( $content );
    $keep    = array();
    $allowed = array( 'core/heading', 'core/paragraph', 'core/list', 'core/list-item', 'core/quote', 'core/spacer', 'core/separator' );

    foreach ( $blocks as $block ) {
        if ( empty( $block['blockName'] ) ) {
            if ( isset( $block['innerHTML'] ) && trim( wp_strip_all_tags( $block['innerHTML'] ) ) !== '' ) {
                $keep[] = $block;
            }
            continue;
        }
        if ( in_array( $block['blockName'], $allowed, true ) ) {
            $keep[] = $block;
        }
    }

    return trim( implode( '', array_map( 'render_block', $keep ) ) );
}

/**
 * Render the portfolio gallery markup.
 *
 * @param int $post_id Page ID.
 */
function tsothm_render_portfolio_gallery( $post_id ) {
    $ids = tsothm_get_portfolio_image_ids( $post_id );
    $ids = array_values(
        array_filter(
            $ids,
            'tsothm_is_portfolio_image_mime'
        )
    );

    if ( empty( $ids ) ) {
        if ( current_user_can( 'edit_post', $post_id ) ) {
            echo '<p class="tso-portfolio-empty">' . esc_html__( 'Add a Gallery or Image block (JPG, PNG, WebP or GIF) in the editor and publish the page.', 'tso-blog' ) . '</p>';
        }
        return;
    }

    echo '<div class="tso-portfolio-gallery" role="list">';
    foreach ( $ids as $attachment_id ) {
        $label = tsothm_get_portfolio_image_label( $attachment_id );
        $thumb = wp_get_attachment_image(
            $attachment_id,
            'large',
            false,
            array(
                'class'   => 'tso-portfolio-image',
                'loading' => 'lazy',
                'alt'     => $label,
            )
        );
        if ( ! $thumb ) {
            continue;
        }
        $downloads = tsothm_get_portfolio_downloads( $attachment_id );
        $full_src  = wp_get_attachment_image_url( $attachment_id, 'full' );

        echo '<figure class="tso-portfolio-item" role="listitem">';
        if ( $full_src ) {
            printf(
                '<a class="tso-portfolio-thumb" href="%s" data-tso-lightbox="1">%s</a>',
                esc_url( $full_src ),
                $thumb // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image().
            );
        } else {
            echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
        echo '<figcaption class="tso-portfolio-caption">';
        echo '<span class="tso-portfolio-name">' . esc_html( $label ) . '</span>';
        if ( ! empty( $downloads ) ) {
            echo '<div class="tso-portfolio-downloads">';
            echo '<span class="tso-portfolio-downloads-label">' . esc_html__( 'Download:', 'tso-blog' ) . '</span> ';
            echo '<ul class="tso-portfolio-download-list">';
            foreach ( $downloads as $dl ) {
                if ( empty( $dl['available'] ) || ( empty( $dl['url'] ) && 'full' !== $dl['key'] ) ) {
                    continue;
                }
                $filename = sanitize_file_name( $label . '-' . $dl['key'] );
                echo '<li>';
                if ( 'full' === $dl['key'] ) {
                    $dim_text = sprintf(
                        /* translators: 1: width in px, 2: height in px */
                        __( '%1$s × %2$s px', 'tso-blog' ),
                        (int) $dl['width'],
                        (int) $dl['height']
                    );
                    echo '<details class="tso-portfolio-original">';
                    echo '<summary class="tso-portfolio-download tso-portfolio-download--original">' . esc_html__( 'Original', 'tso-blog' ) . '</summary>';
                    echo '<div class="tso-portfolio-original-pop" role="dialog" aria-label="' . esc_attr__( 'Original image dimensions', 'tso-blog' ) . '">';
                    echo '<p class="tso-portfolio-original-dims"><strong>' . esc_html__( 'Dimensions:', 'tso-blog' ) . '</strong> ' . esc_html( $dim_text ) . '</p>';
                    printf(
                        '<a class="tso-portfolio-download tso-portfolio-download--go" href="%s" download="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                        esc_url( $dl['url'] ),
                        esc_attr( $filename ),
                        esc_html__( 'Download original', 'tso-blog' )
                    );
                    echo '</div></details>';
                } else {
                    printf(
                        '<a class="tso-portfolio-download" href="%s" download="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                        esc_url( $dl['url'] ),
                        esc_attr( $filename ),
                        esc_html( $dl['label'] )
                    );
                }
                echo '</li>';
            }
            echo '</ul></div>';
        }
        echo '</figcaption></figure>';
    }
    echo '</div>';
}

/**
 * Limit editor blocks on the Portfolio page template.
 *
 * @param bool|string[]               $allowed         Allowed block types.
 * @param WP_Block_Editor_Context     $editor_context  Editor context.
 * @return bool|string[]
 */
function tsothm_portfolio_allowed_blocks( $allowed, $editor_context ) {
    if ( empty( $editor_context->post ) || 'page' !== $editor_context->post->post_type ) {
        return $allowed;
    }
    if ( ! tsothm_is_portfolio_gallery_page( (int) $editor_context->post->ID ) ) {
        return $allowed;
    }
    return array(
        'core/gallery',
        'core/image',
        'core/heading',
        'core/paragraph',
        'core/list',
        'core/list-item',
        'core/spacer',
        'core/separator',
    );
}
add_filter( 'allowed_block_types_all', 'tsothm_portfolio_allowed_blocks', 10, 2 );

/**
 * Admin notice: how to use the portfolio template + regenerate thumbnails tip.
 */
function tsothm_portfolio_admin_notice() {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        return;
    }
    $user_id = get_current_user_id();
    if ( $user_id && get_user_meta( $user_id, 'tsothm_dismiss_portfolio_notice', true ) ) {
        return;
    }
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen || 'post' !== $screen->base || 'page' !== $screen->post_type ) {
        return;
    }
    $post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen check.
    if ( ! $post_id || ! tsothm_is_portfolio_gallery_page( $post_id ) ) {
        return;
    }
    $dismiss_url = wp_nonce_url(
        add_query_arg( 'tsothm_dismiss_portfolio_notice', '1' ),
        'tsothm_dismiss_portfolio_notice'
    );
    echo '<div class="notice notice-info is-dismissible"><p>';
    echo esc_html__( 'Portfolio template: use Gallery or Image blocks (JPG, PNG, WebP, GIF). On the site the gallery shows the name and downloads (1920×1080 and others). If old photos are missing sizes, regenerate thumbnails after activating the theme.', 'tso-blog' );
    echo ' <a href="' . esc_url( $dismiss_url ) . '">' . esc_html__( 'Dismiss notice', 'tso-blog' ) . '</a>';
    echo '</p></div>';
}
add_action( 'admin_notices', 'tsothm_portfolio_admin_notice' );

/**
 * Permanently dismiss the portfolio admin notice for the current user.
 */
function tsothm_handle_dismiss_portfolio_notice() {
    if ( ! isset( $_GET['tsothm_dismiss_portfolio_notice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- verified below.
        return;
    }
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        return;
    }
    check_admin_referer( 'tsothm_dismiss_portfolio_notice' );
    $user_id = get_current_user_id();
    if ( $user_id ) {
        update_user_meta( $user_id, 'tsothm_dismiss_portfolio_notice', 1 );
    }
    $redirect = remove_query_arg( array( 'tsothm_dismiss_portfolio_notice', '_wpnonce' ) );
    wp_safe_redirect( $redirect );
    exit;
}
add_action( 'admin_init', 'tsothm_handle_dismiss_portfolio_notice' );

/**
 * Body class for portfolio template.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function tsothm_portfolio_body_class( $classes ) {
    if ( is_page() && tsothm_is_portfolio_gallery_page() ) {
        $classes[] = 'tso-portfolio-gallery-template';
    }
    return $classes;
}
add_filter( 'body_class', 'tsothm_portfolio_body_class' );
