<?php
/**
 * Template Name: Legal / Documento largo
 * Template Post Type: page
 * Description: Página sin barra lateral pensada para textos largos (aviso legal, privacidad, convenios). Genera automáticamente un índice lateral a partir de los encabezados H2/H3 del contenido.
 *
 * @package tso-blog
 */

get_header();
?>

<div class="main-container tso-legal-page">
	<main id="primary" role="main">
		<?php
		while ( have_posts() ) :
			the_post();

			$tsothm_legal = tsothm_add_heading_ids_and_toc( apply_filters( 'the_content', get_the_content() ) );
			?>
			<article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1 class="page-title"><?php the_title(); ?></h1>

				<?php if ( ! empty( $tsothm_legal['toc'] ) ) : ?>
					<div class="tso-legal-layout">
						<?php tsothm_render_legal_toc( $tsothm_legal['toc'] ); ?>
						<div class="entry-content tso-legal-content">
							<?php echo $tsothm_legal['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- salida de apply_filters( 'the_content', ... ) con ids añadidos por tsothm_add_heading_ids_and_toc(). ?>
						</div>
					</div>
				<?php else : ?>
					<div class="entry-content">
						<?php echo $tsothm_legal['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- salida de apply_filters( 'the_content', ... ). ?>
					</div>
				<?php endif; ?>
			</article>
			<?php
		endwhile;
		?>
	</main>
</div>

<?php
get_footer();
