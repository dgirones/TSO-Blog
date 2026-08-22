<?php
/**
 * Template part: related posts (same primary category).
 *
 * @package tso-theme
 */

$tso_categories  = get_the_category();
$tso_cat_primary = ! empty( $tso_categories ) ? $tso_categories[0] : null;

if ( ! $tso_cat_primary ) {
	return;
}

$tso_related_query = new WP_Query(
	array(
		'category__in'           => array( $tso_cat_primary->term_id ),
		'post__not_in'           => array( get_the_ID() ),
		'posts_per_page'         => tso_related_count(),
		'orderby'                => 'rand',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'ignore_sticky_posts'    => true,
	)
);

if ( ! $tso_related_query->have_posts() ) {
	return;
}
?>
<section class="related-posts-box" aria-labelledby="related-heading">
	<h2 id="related-heading" class="related-posts-title"><?php echo tso_related_title(); ?></h2>
	<div class="related-posts-grid">
		<?php
		while ( $tso_related_query->have_posts() ) :
			$tso_related_query->the_post();
			?>
			<article class="related-post-card">
				<?php if ( has_post_thumbnail() ) : ?>
					<a href="<?php the_permalink(); ?>" class="related-thumb-link" tabindex="-1" aria-hidden="true">
						<?php the_post_thumbnail( 'related-thumb', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
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
