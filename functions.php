<?php
/**
 * functions.php — TSO Blog (tso-blog).
 * Compatible with Jetpack, LiteSpeed Cache, WP Super Cache and W3TC.
 * Version: keep TSOTHM_VERSION, style.css Version and readme Stable tag in sync.
 *
 * This file only bootstraps the theme. The code lives in /inc, one file per area.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'TSOTHM_VERSION' ) ) {
    define( 'TSOTHM_VERSION', '1.1.1' );
}

require_once get_template_directory() . '/tsothm-mobile-menu.php';

/*
 * Theme code, one file per area. Keep this order: some files register hooks
 * whose order matters, and later files use helpers defined in earlier ones.
 */
require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/settings.php';
require_once get_template_directory() . '/inc/fonts.php';
require_once get_template_directory() . '/inc/colors.php';
require_once get_template_directory() . '/inc/enqueue.php';
require_once get_template_directory() . '/inc/template-setup.php';
require_once get_template_directory() . '/inc/portfolio-gallery.php';
require_once get_template_directory() . '/inc/layout-contact.php';
require_once get_template_directory() . '/inc/comments.php';
require_once get_template_directory() . '/inc/social.php';
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/live-search.php';
require_once get_template_directory() . '/inc/template-tags.php';
require_once get_template_directory() . '/inc/announcement.php';
require_once get_template_directory() . '/inc/jetpack-galleries.php';
require_once get_template_directory() . '/inc/blocks.php';
require_once get_template_directory() . '/inc/page-templates.php';
require_once get_template_directory() . '/inc/customizer-repeaters.php';
require_once get_template_directory() . '/inc/color-mode.php';
require_once get_template_directory() . '/inc/color-mode-content.php';
require_once get_template_directory() . '/inc/color-mode-customizer.php';
