<?php
/**
 * Template Name: Ancho completo
 * Template Post Type: page
 * Description: Página sin barra lateral y con ancho ampliado, pensada para landing pages o contenido construido a base de bloques.
 *
 * @package tso-blog
 */

get_header();
?>

<div class="main-container tso-full-width-page">
	<main id="primary" role="main">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1 class="page-title"><?php the_title(); ?></h1>
				<div class="entry-content">
					<?php
					the_content();
					wp_link_pages(
						array(
							'before'      => '<div class="page-links">' . esc_html__( 'Páginas:', 'tso-blog' ),
							'after'       => '</div>',
							'link_before' => '<span class="page-number">',
							'link_after'  => '</span>',
						)
					);
					?>
				</div>
			</article>
			<?php
		endwhile;
		?>
	</main>
</div>

<?php
get_footer();
