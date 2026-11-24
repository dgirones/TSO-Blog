<?php
/**
 * Title: Team (3 people)
 * Slug: tso-blog/team-grid
 * Description: Grid of 3 team cards with photo, name and role.
 * Categories: tso-blog, team
 *
 * @package tso-blog
 */

?>
<!-- wp:columns {"className":"tso-team-grid"} -->
<div class="wp-block-columns tso-team-grid">
<!-- wp:column {"className":"tso-team-member"} -->
<div class="wp-block-column tso-team-member">
<!-- wp:image {"align":"center","width":140,"height":140,"scale":"cover","style":{"border":{"radius":"999px"}}} -->
<figure class="wp-block-image aligncenter is-resized has-custom-border"><img style="border-radius:999px;object-fit:cover;width:140px;height:140px" alt=""/></figure>
<!-- /wp:image -->
<!-- wp:heading {"textAlign":"center","level":3} -->
<h3 class="wp-block-heading has-text-align-center"><?php esc_html_e( 'First Last', 'tso-blog' ); ?></h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><?php esc_html_e( 'Position or role', 'tso-blog' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column {"className":"tso-team-member"} -->
<div class="wp-block-column tso-team-member">
<!-- wp:image {"align":"center","width":140,"height":140,"scale":"cover","style":{"border":{"radius":"999px"}}} -->
<figure class="wp-block-image aligncenter is-resized has-custom-border"><img style="border-radius:999px;object-fit:cover;width:140px;height:140px" alt=""/></figure>
<!-- /wp:image -->
<!-- wp:heading {"textAlign":"center","level":3} -->
<h3 class="wp-block-heading has-text-align-center"><?php esc_html_e( 'First Last', 'tso-blog' ); ?></h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><?php esc_html_e( 'Position or role', 'tso-blog' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column {"className":"tso-team-member"} -->
<div class="wp-block-column tso-team-member">
<!-- wp:image {"align":"center","width":140,"height":140,"scale":"cover","style":{"border":{"radius":"999px"}}} -->
<figure class="wp-block-image aligncenter is-resized has-custom-border"><img style="border-radius:999px;object-fit:cover;width:140px;height:140px" alt=""/></figure>
<!-- /wp:image -->
<!-- wp:heading {"textAlign":"center","level":3} -->
<h3 class="wp-block-heading has-text-align-center"><?php esc_html_e( 'First Last', 'tso-blog' ); ?></h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><?php esc_html_e( 'Position or role', 'tso-blog' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
