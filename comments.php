<?php
/**
 * comments.php — Comment list and form template.
 * Form labels are filtered in functions.php.
 *
 * @package tso-theme
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
            $tso_comment_count = (int) get_comments_number();
            if ( 1 === $tso_comment_count ) {
                esc_html_e( 'Un comentario', 'tso-theme' );
            } else {
                printf(
                    /* translators: %s: número de comentarios */
                    esc_html__( '%s comentarios', 'tso-theme' ),
                    number_format_i18n( $tso_comment_count )
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
            'prev_text' => '&laquo; ' . esc_html__( 'Anteriores', 'tso-theme' ),
            'next_text' => esc_html__( 'Siguientes', 'tso-theme' ) . ' &raquo;',
        ) );
        ?>

    <?php endif; // have_comments() ?>

    <?php
    // Form labels come from comment_form_* filters in functions.php.
    comment_form();
    ?>

</div><!-- #comments -->
