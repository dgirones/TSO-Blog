<?php
/**
 * Template part: related posts (same primary category).
 *
 * @package tso-blog
 */

$tsothm_categories  = get_the_category();
$tsothm_cat_primary = ! empty( $tsothm_categories ) ? $tsothm_categories[0] : null;

if ( ! $tsothm_cat_primary ) {
	return;
}

$tsothm_related_query = new WP_Query(
	array(
		'category__in'           => array( $tsothm_cat_primary->term_id ),
		'post__not_in'           => array( get_the_ID() ),
		'posts_per_page'         => tsothm_related_count(),
		'orderby'                => 'rand',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'ignore_sticky_posts'    => true,
	)
);

if ( ! $tsothm_related_query->have_posts() ) {
	return;
}
?>
<section class="related-posts-box" aria-labelledby="related-heading">
	<h2 id="related-heading" class="related-posts-title"><?php echo tsothm_related_title(); ?></h2>
	<div class="related-posts-grid">
		<?php
		while ( $tsothm_related_query->have_posts() ) :
			$tsothm_related_query->the_post();
			?>
			<article class="related-post-card">
				<?php if ( has_post_thumbnail() ) : ?>
					<a href="<?php the_permalink(); ?>" class="related-thumb-link" tabindex="-1" aria-hidden="true">
						<?php the_post_thumbnail( 'tsothm-related', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
					</a>
				<?php endif; ?>
				<div class="related-post-info">
					<h3 class="related-post-title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h3>
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>" class="related-post-date">
						<?php echo esc_html( get_the_date() ); ?>
					</time>
				</div>
			</article>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</div>
</section>
