<?php
/**
 * Title: Services (3 cards)
 * Slug: tso-blog/service-grid
 * Description: Grid of 3 service cards with title and text.
 * Categories: tso-blog, columns
 *
 * @package tso-blog
 */

?>
<!-- wp:columns {"className":"tso-service-grid"} -->
<div class="wp-block-columns tso-service-grid">
<!-- wp:column {"className":"tso-service-card"} -->
<div class="wp-block-column tso-service-card">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'Service 1', 'tso-blog' ); ?></h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'Short description of this service and who it is for.', 'tso-blog' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column {"className":"tso-service-card"} -->
<div class="wp-block-column tso-service-card">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'Service 2', 'tso-blog' ); ?></h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'Short description of this service and who it is for.', 'tso-blog' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column {"className":"tso-service-card"} -->
<div class="wp-block-column tso-service-card">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'Service 3', 'tso-blog' ); ?></h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'Short description of this service and who it is for.', 'tso-blog' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
