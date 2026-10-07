<?php
/**
 * Template Name: Portfolio / Galería wallpapers
 * Template Post Type: page
 * Description: Galería de fotos con nombre y descargas en tamaños de fondo de pantalla.
 *
 * @package tso-blog
 */

get_header();
?>

<div class="main-container tso-portfolio-layout">
	<main id="primary" class="tso-portfolio-main" role="main">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="page-<?php the_ID(); ?>" <?php post_class( 'tso-portfolio-page' ); ?>>
				<h1 class="page-title"><?php the_title(); ?></h1>
				<?php
				$tsothm_intro = tsothm_portfolio_intro_from_content( get_post()->post_content );
				if ( $tsothm_intro ) :
					?>
					<div class="entry-content tso-portfolio-intro">
						<?php echo $tsothm_intro; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_block / kses via blocks. ?>
					</div>
				<?php endif; ?>
				<?php tsothm_render_portfolio_gallery( get_the_ID() ); ?>
			</article>
			<?php
		endwhile;
		?>
	</main>
</div>

<?php
get_footer();
