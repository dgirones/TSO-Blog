<?php
/**
 * Title: Link list
 * Slug: tso-blog/links-list
 * Description: Full-width stacked buttons, "linktree" style.
 * Categories: tso-blog, buttons
 *
 * @package tso-blog
 */

?>
<!-- wp:buttons {"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-buttons">
<!-- wp:button {"width":100} -->
<div class="wp-block-button has-custom-width wp-block-button__width-100"><a class="wp-block-button__link wp-element-button"><?php esc_html_e( 'My website', 'tso-blog' ); ?></a></div>
<!-- /wp:button -->
<!-- wp:button {"width":100} -->
<div class="wp-block-button has-custom-width wp-block-button__width-100"><a class="wp-block-button__link wp-element-button"><?php esc_html_e( 'Instagram', 'tso-blog' ); ?></a></div>
<!-- /wp:button -->
<!-- wp:button {"width":100} -->
<div class="wp-block-button has-custom-width wp-block-button__width-100"><a class="wp-block-button__link wp-element-button"><?php esc_html_e( 'Contact', 'tso-blog' ); ?></a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
