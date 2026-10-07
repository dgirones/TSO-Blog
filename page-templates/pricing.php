<?php
/**
 * Template Name: Precios
 * Template Post Type: page
 * Description: Página sin barra lateral pensada para mostrar planes o tarifas. Usa el patrón de bloques "Precios (3 planes)" como punto de partida.
 *
 * @package tso-blog
 */

get_header();
?>

<div class="main-container tso-pricing-page">
	<main id="primary" role="main">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1 class="page-title"><?php the_title(); ?></h1>
				<div class="entry-content">
					<?php
					// Si hay planes configurados en Personalizar → Precios, se muestran esos;
					// si no, se muestra el contenido de bloques (patrón "Precios (3 planes)").
					if ( ! tsothm_render_pricing_table( get_the_ID() ) ) {
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
