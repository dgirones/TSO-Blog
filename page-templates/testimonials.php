<?php
/**
 * Template Name: Testimonios
 * Template Post Type: page
 * Description: Página sin barra lateral pensada para mostrar opiniones de clientes o usuarios. Usa el patrón de bloques "Testimonios (2 tarjetas)" como punto de partida.
 *
 * @package tso-blog
 */

get_header();
?>

<div class="main-container tso-testimonials-page">
	<main id="primary" role="main">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1 class="page-title"><?php the_title(); ?></h1>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</article>
			<?php
		endwhile;
		?>
	</main>
</div>

<?php
get_footer();
