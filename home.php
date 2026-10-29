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

	<div id="load-more-wrapper" class="load-more-wrapper">
		<?php if ( $wp_query->max_num_pages > 1 ) : ?>
			<button
				id="load-more-btn"
				class="btn-load-more"
				type="button"
				data-next-page="<?php echo esc_url( next_posts( $wp_query->max_num_pages, false ) ); ?>"
				data-loading-text="<?php esc_attr_e( 'Loading...', 'tso-blog' ); ?>"
				data-load-text="<?php echo esc_attr( tsothm_load_more_text() ); ?>"
			>
				<?php echo esc_html( tsothm_load_more_text() ); ?>
			</button>
		<?php endif; ?>
	</div>
</main>

<?php if ( $tsothm_show_sb ) : ?>
	<?php get_sidebar(); ?>
</div>
<?php endif; ?>

<?php
get_footer();
