<?php
/**
 * Retrieve sidebar
 *
 * @package LifterLMS/Templates
 *
 * @since Unknown
 * @since [version] Fix warning for themes without sidebar.php template.
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

if ( ABSPATH . WPINC . '/theme-compat/sidebar.php' !== locate_template( array( 'sidebar-llms_shop', 'sidebar.php' ) ) ) {
	get_sidebar( 'llms_shop' );
}
