<?php
/**
 * Template Name: Recursos / Descargas
 * Template Post Type: page
 * Description: Página sin barra lateral que muestra como lista de descargas los bloques Archivo añadidos al contenido (nombre, tipo y peso).
 *
 * @package tso-blog
 */

get_header();
?>

<div class="main-container tso-resources-page">
	<main id="primary" role="main">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1 class="page-title"><?php the_title(); ?></h1>
				<?php
				$tsothm_intro = tsothm_portfolio_intro_from_content( get_post()->post_content );
				if ( $tsothm_intro ) :
					?>
					<div class="entry-content tso-resources-intro">
						<?php echo $tsothm_intro; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_block / kses vía bloques. ?>
					</div>
				<?php endif; ?>
				<?php tsothm_render_resource_list( get_the_ID() ); ?>
			</article>
			<?php
		endwhile;
		?>
	</main>
</div>

<?php
get_footer();
