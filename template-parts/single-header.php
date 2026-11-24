<?php
/**
 * Template part: single post header (category badge and title).
 *
 * @package tso-blog
 */

$tsothm_categories  = get_the_category();
$tsothm_cat_primary = ! empty( $tsothm_categories ) ? $tsothm_categories[0] : null;
?>
<header class="single-article-header">
	<?php if ( $tsothm_cat_primary ) : ?>
		<span class="single-category-badge">
			<a href="<?php echo esc_url( get_category_link( $tsothm_cat_primary->term_id ) ); ?>">
				<?php echo esc_html( $tsothm_cat_primary->name ); ?>
			</a>
		</span>
	<?php endif; ?>

	<h1 class="single-title"><?php the_title(); ?></h1>
</header>
