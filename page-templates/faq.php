<?php
/**
 * Template Name: Preguntas frecuentes
 * Template Post Type: page
 * Description: Página sin barra lateral que convierte el contenido en un acordeón accesible: cada bloque Encabezado (H3) es una pregunta y el contenido siguiente, la respuesta.
 *
 * @package tso-blog
 */

get_header();
?>

<div class="main-container tso-faq-page">
	<main id="primary" role="main">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1 class="page-title"><?php the_title(); ?></h1>
				<div class="entry-content">
					<?php tsothm_render_faq_accordion( get_the_ID() ); ?>
				</div>
			</article>
			<?php
		endwhile;
		?>
	</main>
</div>

<?php
get_footer();
