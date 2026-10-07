<?php
/**
 * Template Name: Enlaces
 * Template Post Type: page
 * Description: Página minimalista sin barra lateral ni comentarios, con avatar centrado y botones a ancho completo — el clásico formato "link in bio". Usa el patrón de bloques "Lista de enlaces" como punto de partida.
 *
 * @package tso-blog
 */

get_header();
?>

<div class="main-container tso-links-page">
	<main id="primary" role="main">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php if ( has_post_thumbnail() ) : ?>
					<?php echo get_the_post_thumbnail( get_the_ID(), 'thumbnail', array( 'class' => 'tso-links-avatar' ) ); ?>
				<?php endif; ?>
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
