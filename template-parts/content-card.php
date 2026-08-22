<?php
/**
 * Template part: post card for home, archives, and search results.
 *
 * @package tso-theme
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'home-post-card' ); ?> itemscope itemtype="https://schema.org/NewsArticle">
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="post-card-image">
			<a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
				<?php the_post_thumbnail( 'tso-card-thumb', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
			</a>
		</div>
	<?php endif; ?>
	<div class="post-card-body">
		<h2 class="post-card-title" itemprop="headline">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>
		<div class="post-card-excerpt">
					<?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?>
		</div>
	</div>
</article>
