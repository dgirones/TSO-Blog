<?php
/**
 * index.php — Ultimate fallback template.
 *
 * @package tso-theme
 */
get_header();
?>

<main id="primary" class="main-content-area" role="main">
	<div class="posts-grid-container" id="posts-container">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', 'card' );
			endwhile;
		else :
			?>
			<p class="posts-empty"><?php esc_html_e( 'No hay artículos publicados todavía.', 'tso-theme' ); ?></p>
			<?php
		endif;
		?>
	</div>

	<?php if ( $GLOBALS['wp_query']->max_num_pages > 1 ) : ?>
		<div class="posts-pagination">
			<?php
			the_posts_pagination(
				array(
					'mid_size'  => 2,
					'prev_text' => esc_html__( '&laquo; Anterior', 'tso-theme' ),
					'next_text' => esc_html__( 'Siguiente &raquo;', 'tso-theme' ),
				)
			);
			?>
		</div>
	<?php endif; ?>
</main>

<?php
get_footer();
