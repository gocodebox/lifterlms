<?php
/**
 * Retrieve sidebar
 *
 * @package LifterLMS/Templates
 *
 * @since Unknown
 * @since [version] Don't include WordPress default sidebar.php template when using a block theme.
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

$core_fallback     = ABSPATH . WPINC . '/theme-compat/sidebar.php';
$sidebar_templates = array( 'sidebar-llms_shop.php', 'sidebar.php' );

// Return early if using block theme with no sidebar template.
if ( wp_is_block_theme() && $core_fallback === locate_template( $sidebar_templates ) ) {
	return;
}

get_sidebar( 'llms_shop' );
