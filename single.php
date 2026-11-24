<?php
/**
 * single.php — Single post template.
 * Related posts by category, comments.
 *
 * @package tso-blog
 */
get_header();

while ( have_posts() ) :
	the_post();
	?>

	<?php get_template_part( 'template-parts/single', 'breadcrumb' ); ?>

<div class="single-layout">

	<main id="primary" class="single-content-area" role="main">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<?php get_template_part( 'template-parts/single', 'header' ); ?>

			<div class="entry-content">
				<?php
				the_content();
				wp_link_pages(
					array(
						'before'      => '<div class="page-links">' . esc_html__( 'Pages:', 'tso-blog' ),
						'after'       => '</div>',
						'link_before' => '<span class="page-number">',
						'link_after'  => '</span>',
					)
				);
				?>
			</div>

			<?php get_template_part( 'template-parts/single', 'tags' ); ?>

		</article>

		<?php get_template_part( 'template-parts/single', 'related' ); ?>

		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>

	</main>

	<?php get_sidebar(); ?>

</div>

	<?php
endwhile;

get_footer();
