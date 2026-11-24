<?php
/**
 * home.php — Blog posts index (front page posts or Posts page).
 *
 * @package tso-blog
 */
get_header();
global $wp_query;

$tsothm_blog_layout = tsothm_get_blog_layout();
$tsothm_featured    = ( 'magazine' === $tsothm_blog_layout );
$tsothm_show_sb     = tsothm_is_sidebar_visible();
?>

<?php if ( $tsothm_show_sb ) : ?>
<div class="main-container">
<?php endif; ?>

<main id="primary" class="main-content-area" role="main">
	<div
		class="posts-grid-container posts-layout-<?php echo esc_attr( $tsothm_blog_layout ); ?>"
		id="posts-container"
	>
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				if ( $tsothm_featured ) {
					set_query_var( 'tsothm_card_featured', true );
					get_template_part( 'template-parts/content', 'card' );
					set_query_var( 'tsothm_card_featured', false );
					$tsothm_featured = false;
					continue;
				}
				get_template_part( 'template-parts/content', 'card' );
			endwhile;
		else :
			?>
			<p class="posts-empty"><?php esc_html_e( 'No posts published yet.', 'tso-blog' ); ?></p>
			<?php
		endif;
		?>
	</div>

	<div id="tsothm-load-more-root" class="load-more-wrapper">
		<?php tsothm_render_load_more_button(); ?>
	</div>
</main>

<?php if ( $tsothm_show_sb ) : ?>
	<?php get_sidebar(); ?>
</div>
<?php endif; ?>

<?php
get_footer();
