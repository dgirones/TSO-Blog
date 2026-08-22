<?php
/**
 * Template part: single post breadcrumb.
 *
 * @package tso-theme
 */

$tso_categories  = get_the_category();
$tso_cat_primary = ! empty( $tso_categories ) ? $tso_categories[0] : null;
$tso_post_title  = get_the_title();
?>
<nav class="single-breadcrumb" aria-label="<?php esc_attr_e( 'Ruta de navegación', 'tso-theme' ); ?>" itemscope itemtype="https://schema.org/BreadcrumbList">
	<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
		<a itemprop="item" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<span itemprop="name"><?php esc_html_e( 'Inicio', 'tso-theme' ); ?></span>
		</a>
		<meta itemprop="position" content="1" />
	</span>
	<?php if ( $tso_cat_primary ) : ?>
		&rsaquo;
		<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
			<span itemprop="name" class="breadcrumb-cat"><?php echo esc_html( $tso_cat_primary->name ); ?></span>
			<meta itemprop="item" content="<?php echo esc_url( get_category_link( $tso_cat_primary->term_id ) ); ?>" />
			<meta itemprop="position" content="2" />
		</span>
		&rsaquo;
		<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
			<span itemprop="name"><?php echo esc_html( $tso_post_title ); ?></span>
			<meta itemprop="position" content="3" />
		</span>
	<?php else : ?>
		&rsaquo; <span><?php echo esc_html( $tso_post_title ); ?></span>
	<?php endif; ?>
</nav>
