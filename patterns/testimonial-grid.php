<?php
/**
 * Title: Testimonials (2 cards)
 * Slug: tso-blog/testimonial-grid
 * Description: Two testimonial cards with quote and author.
 * Categories: tso-blog, columns
 *
 * @package tso-blog
 */

?>
<!-- wp:columns {"className":"tso-testimonial-grid"} -->
<div class="wp-block-columns tso-testimonial-grid">
<!-- wp:column {"className":"tso-testimonial-card"} -->
<div class="wp-block-column tso-testimonial-card">
<!-- wp:quote -->
<blockquote class="wp-block-quote"><!-- wp:paragraph --><p><?php esc_html_e( '"A short testimonial about the experience with the service."', 'tso-blog' ); ?></p><!-- /wp:paragraph --><cite><?php esc_html_e( 'Client name', 'tso-blog' ); ?></cite></blockquote>
<!-- /wp:quote -->
</div>
<!-- /wp:column -->
<!-- wp:column {"className":"tso-testimonial-card"} -->
<div class="wp-block-column tso-testimonial-card">
<!-- wp:quote -->
<blockquote class="wp-block-quote"><!-- wp:paragraph --><p><?php esc_html_e( '"A short testimonial about the experience with the service."', 'tso-blog' ); ?></p><!-- /wp:paragraph --><cite><?php esc_html_e( 'Client name', 'tso-blog' ); ?></cite></blockquote>
<!-- /wp:quote -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
