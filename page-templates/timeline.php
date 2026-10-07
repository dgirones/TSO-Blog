<?php
/**
 * Template Name: Cronología / Historia
 * Template Post Type: page
 * Description: Página sin barra lateral pensada para una línea de tiempo (p. ej. "nuestra historia"). Usa el patrón de bloques "Cronología (3 hitos)" como punto de partida.
 *
 * @package tso-blog
 */

get_header();
?>

<div class="main-container tso-timeline-page">
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
