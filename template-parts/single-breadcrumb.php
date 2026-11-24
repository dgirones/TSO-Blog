<?php
/**
 * Template part: single post breadcrumb.
 *
 * @package tso-blog
 */

$tsothm_categories  = get_the_category();
$tsothm_cat_primary = ! empty( $tsothm_categories ) ? $tsothm_categories[0] : null;
$tsothm_post_title  = get_the_title();
?>
<nav class="single-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'tso-blog' ); ?>">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<?php esc_html_e( 'Home', 'tso-blog' ); ?>
	</a>
	<?php if ( $tsothm_cat_primary ) : ?>
		&rsaquo;
		<a href="<?php echo esc_url( get_category_link( $tsothm_cat_primary->term_id ) ); ?>">
			<span class="breadcrumb-cat"><?php echo esc_html( $tsothm_cat_primary->name ); ?></span>
		</a>
		&rsaquo;
		<span><?php echo esc_html( $tsothm_post_title ); ?></span>
	<?php else : ?>
		&rsaquo; <span><?php echo esc_html( $tsothm_post_title ); ?></span>
	<?php endif; ?>
</nav>
