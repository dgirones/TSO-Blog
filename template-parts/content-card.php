<?php
/**
 * Template part: post card for home, archives, and search results.
 *
 * @package tso-blog
 */

$tsothm_is_featured = (bool) get_query_var( 'tsothm_card_featured' );
$tsothm_card_class  = $tsothm_is_featured ? array( 'home-post-card', 'is-featured' ) : array( 'home-post-card' );
$tsothm_excerpt_n   = $tsothm_is_featured ? 36 : 20;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( $tsothm_card_class ); ?> itemscope itemtype="https://schema.org/NewsArticle">
	<?php global $wp_query; ?>
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="post-card-image">
			<a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
				<?php
				// First card image is usually the LCP element: load it eagerly with high priority.
				$tsothm_is_first = $tsothm_is_featured || ( isset( $wp_query->current_post ) && 0 === (int) $wp_query->current_post );
				$tsothm_img_attr = $tsothm_is_first
					? array( 'loading' => 'eager', 'decoding' => 'async', 'fetchpriority' => 'high', 'class' => 'attachment-tsothm-card-thumb size-tsothm-card-thumb wp-post-image skip-lazy no-lazy' )
					: array( 'loading' => 'lazy', 'decoding' => 'async' );
				the_post_thumbnail( 'tsothm-card-thumb', $tsothm_img_attr );
				?>
			</a>
		</div>
	<?php endif; ?>
	<div class="post-card-body">
		<?php if ( $tsothm_is_featured ) : ?>
			<p class="post-card-kicker"><?php esc_html_e( 'Featured', 'tso-blog' ); ?></p>
		<?php endif; ?>
		<h2 class="post-card-title" itemprop="headline">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>
		<div class="post-card-excerpt">
			<?php echo esc_html( wp_trim_words( get_the_excerpt(), $tsothm_excerpt_n ) ); ?>
		</div>
	</div>
</article>
