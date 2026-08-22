<?php
/**
 * home.php — Blog posts index (front page posts or Posts page).
 *
 * @package tso-theme
 */
get_header();
global $wp_query;
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

	<div id="load-more-wrapper" class="load-more-wrapper">
		<?php if ( $wp_query->max_num_pages > 1 ) : ?>
			<button
				id="load-more-btn"
				class="btn-load-more"
				type="button"
				data-next-page="<?php echo esc_url( next_posts( $wp_query->max_num_pages, false ) ); ?>"
				data-loading-text="<?php esc_attr_e( 'Cargando...', 'tso-theme' ); ?>"
				data-load-text="<?php echo esc_attr( wp_strip_all_tags( tso_load_more_text() ) ); ?>"
			>
				<?php echo tso_load_more_text(); ?>
			</button>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
