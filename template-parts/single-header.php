<?php
/**
 * Template part: single post header (category badge, title, schema meta).
 *
 * @package tso-blog
 */

$tsothm_categories  = get_the_category();
$tsothm_cat_primary = ! empty( $tsothm_categories ) ? $tsothm_categories[0] : null;
$tsothm_post_author = get_the_author();
?>
<header class="single-article-header">
	<?php if ( $tsothm_cat_primary ) : ?>
		<span class="single-category-badge">
			<a href="<?php echo esc_url( get_category_link( $tsothm_cat_primary->term_id ) ); ?>">
				<?php echo esc_html( $tsothm_cat_primary->name ); ?>
			</a>
		</span>
	<?php endif; ?>

	<h1 class="single-title" itemprop="headline"><?php the_title(); ?></h1>

	<meta itemprop="datePublished" content="<?php echo esc_attr( get_the_date( 'c' ) ); ?>" />
	<meta itemprop="dateModified" content="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>" />
	<?php if ( $tsothm_post_author ) : ?>
		<span itemprop="author" itemscope itemtype="https://schema.org/Person" class="screen-reader-text">
			<span itemprop="name"><?php echo esc_html( $tsothm_post_author ); ?></span>
		</span>
	<?php endif; ?>
</header>
