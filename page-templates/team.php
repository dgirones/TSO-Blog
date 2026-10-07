<?php
/**
 * Template Name: Equipo
 * Template Post Type: page
 * Description: Página sin barra lateral pensada para mostrar al equipo. Usa el patrón de bloques "Equipo (3 personas)" como punto de partida.
 *
 * @package tso-blog
 */

get_header();
?>

<div class="main-container tso-team-page">
	<main id="primary" role="main">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1 class="page-title"><?php the_title(); ?></h1>
				<div class="entry-content">
					<?php
					// Si hay personas configuradas en Personalizar → Equipo, se muestran esas;
					// si no, se muestra el contenido de bloques (patrón "Equipo (3 personas)").
					if ( ! tsothm_render_team_grid( get_the_ID() ) ) {
						the_content();
					}
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
