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
<nav class="single-breadcrumb" aria-label="<?php esc_attr_e( 'Ruta de navegación', 'tso-blog' ); ?>" itemscope itemtype="https://schema.org/BreadcrumbList">
	<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
		<a itemprop="item" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<span itemprop="name"><?php esc_html_e( 'Inicio', 'tso-blog' ); ?></span>
		</a>
		<meta itemprop="position" content="1" />
	</span>
	<?php if ( $tsothm_cat_primary ) : ?>
		&rsaquo;
		<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
			<a itemprop="item" href="<?php echo esc_url( get_category_link( $tsothm_cat_primary->term_id ) ); ?>">
				<span itemprop="name" class="breadcrumb-cat"><?php echo esc_html( $tsothm_cat_primary->name ); ?></span>
			</a>
			<meta itemprop="position" content="2" />
		</span>
		&rsaquo;
		<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
			<span itemprop="name"><?php echo esc_html( $tsothm_post_title ); ?></span>
			<meta itemprop="position" content="3" />
		</span>
	<?php else : ?>
		&rsaquo; <span><?php echo esc_html( $tsothm_post_title ); ?></span>
	<?php endif; ?>
</nav>
