<?php
/**
 * Template Name: Sobre mí
 * Template Post Type: page
 * Description: Página sin barra lateral con avatar y biografía, y al final una tarjeta de contacto opcional (email, teléfono, redes o un formulario) usando los mismos ajustes de Apariencia → Personalizar → Contacto.
 *
 * @package tso-blog
 */

get_header();
?>

<div class="main-container tso-about-me-page">
	<main id="primary" role="main">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>

				<div class="tso-about-hero">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php echo get_the_post_thumbnail( get_the_ID(), 'medium', array( 'class' => 'tso-about-avatar' ) ); ?>
					<?php endif; ?>
					<h1 class="page-title"><?php the_title(); ?></h1>
				</div>

				<div class="entry-content">
					<?php the_content(); ?>
				</div>

				<?php tsothm_render_about_me_contact_card( get_the_ID() ); ?>

			</article>
			<?php
		endwhile;
		?>
	</main>
</div>

<?php
get_footer();
