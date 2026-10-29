<?php
/**
 * News sidebar.
 *
 * @package TSO_Blog
 */

if ( ! tsothm_is_sidebar_visible() ) {
	return;
}

if ( is_active_sidebar( 'sidebar-1' ) ) :
	?>
	<aside id="secondary" class="widget-area" role="complementary" aria-label="<?php echo esc_attr__( 'Sidebar', 'tso-blog' ); ?>">
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	</aside>
	<?php
endif;
