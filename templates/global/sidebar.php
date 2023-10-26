<?php
/**
 * Retrieve sidebar
 *
 * @package LifterLMS/Templates
 *
 * @since Unknown
 * @since 7.3.0 Don't include WordPress default sidebar.php template when using a block theme.
 * @version 7.3.0
 */

defined( 'ABSPATH' ) || exit;

$core_fallback     = ABSPATH . WPINC . '/theme-compat/sidebar.php';
$sidebar_templates = array( 'sidebar-llms_shop.php', 'sidebar.php' );

// Return early if using block theme with no sidebar template.
if ( wp_is_block_theme() && locate_template( $sidebar_templates ) === $core_fallback ) {
	return;
}

get_sidebar( 'llms_shop' );
