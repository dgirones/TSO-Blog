<?php
/**
 * archive.php — Category / tag / author archives (same cards as home).
 *
 * @package tso-theme
 */
get_header();
?>

<div class="archive-layout">
	<main id="primary" class="main-content" role="main">

		<header class="archive-header">
			<h1 class="archive-title"><?php the_archive_title(); ?></h1>
			<?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
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

			<div class="posts-pagination pagination">
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

		<?php else : ?>

			<p><?php esc_html_e( 'No se han encontrado artículos en esta categoría.', 'tso-theme' ); ?></p>

		<?php endif; ?>

	</main>

	<?php get_sidebar(); ?>
</div>

<?php
get_footer();
