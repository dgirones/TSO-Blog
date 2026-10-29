<?php
/**
 * comments.php — Comment list and form template.
 * Form labels are filtered in functions.php.
 *
 * @package tso-blog
 */

// Evitar acceso directo al fichero.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// No mostrar nada si el post está protegido con contraseña.
if ( post_password_required() ) {
    return;
}
?>

<div id="comments" class="comments-area">

    <?php if ( have_comments() ) : ?>

        <h2 class="comments-title">
            <?php
            $tsothm_comment_count = (int) get_comments_number();
            if ( 1 === $tsothm_comment_count ) {
                esc_html_e( 'One comment', 'tso-blog' );
            } else {
                printf(
                    /* translators: %s: número de comentarios */
                    esc_html__( '%s comments', 'tso-blog' ),
                    esc_html( number_format_i18n( $tsothm_comment_count ) )
                );
            }
            ?>
        </h2><!-- .comments-title -->

        <ol class="comment-list">
            <?php
            wp_list_comments( array(
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 42,
            ) );
            ?>
        </ol><!-- .comment-list -->

        <?php
        the_comments_pagination( array(
            'prev_text' => '&laquo; ' . esc_html__( 'Previous', 'tso-blog' ),
            'next_text' => esc_html__( 'Next', 'tso-blog' ) . ' &raquo;',
        ) );
        ?>

    <?php endif; // have_comments() ?>

    <?php
    // Form labels come from comment_form_* filters in functions.php.
    comment_form();
    ?>

</div><!-- #comments -->
