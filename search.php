<?php
/**
 * search.php — Search results (same card grid as the home page).
 *
 * @package tso-blog
 */
get_header();
global $wp_query;
?>

<main id="primary" class="main-content-area" role="main">

	<header class="search-header">
		<h1 class="search-title">
			<?php esc_html_e( 'Resultados de búsqueda para:', 'tso-blog' ); ?>
			<span class="search-query"><?php echo esc_html( get_search_query() ); ?></span>
		</h1>
		<?php if ( have_posts() ) : ?>
			<p class="search-results-count">
				<?php
				printf(
					/* translators: %s: number of results */
					esc_html( _n( '%s artículo encontrado', '%s artículos encontrados', (int) $wp_query->found_posts, 'tso-blog' ) ),
					number_format_i18n( (int) $wp_query->found_posts )
				);
				?>
			</p>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>

		<div class="posts-grid-container" id="posts-container">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', 'card' );
			endwhile;
			?>
		</div>

		<div class="posts-pagination" id="search-pagination">
			<?php
			the_posts_pagination(
				array(
					'mid_size'  => 2,
					'prev_text' => esc_html__( '&laquo; Anterior', 'tso-blog' ),
					'next_text' => esc_html__( 'Siguiente &raquo;', 'tso-blog' ),
				)
			);
			?>
		</div>

	<?php else : ?>

		<div class="search-no-results">
			<p><?php esc_html_e( 'No se encontraron artículos. Prueba con otras palabras.', 'tso-blog' ); ?></p>
			<?php get_search_form(); ?>
		</div>

	<?php endif; ?>

</main>

<?php
get_footer();
