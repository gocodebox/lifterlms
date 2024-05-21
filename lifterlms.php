<?php
/**
 * Main LifterLMS plugin file
 *
 * @package LifterLMS/Main
 *
 * @since 1.0.0
 * @version 5.3.0
 *
 * Plugin Name: LifterLMS
 * Plugin URI: https://lifterlms.com/
 * Description: Complete e-learning platform to sell online courses, protect lessons, offer memberships, and quiz students.
 * Version: 7.6.1
 * Author: LifterLMS
 * Author URI: https://lifterlms.com/
 * Text Domain: lifterlms
 * Domain Path: /languages
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 5.9
 * Tested up to: 6.5
 * Requires PHP: 7.4
 *
 * * * * * * * * * * * * * * * * * * * * * *
 *                                         *
 * Reporting a Security Vulnerability      *
 *                                         *
 * Please disclose any security issues or  *
 * vulnerabilities to team@lifterlms.com   *
 *                                         *
 * See our full Security Policy at         *
 * https://lifterlms.com/security-policy   *
 *                                         *
 * * * * * * * * * * * * * * * * * * * * * *
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'LLMS_PLUGIN_FILE' ) ) {
	define( 'LLMS_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'LLMS_PLUGIN_DIR' ) ) {
	define( 'LLMS_PLUGIN_DIR', __DIR__ . '/' );
}

// Autoloader.
require_once LLMS_PLUGIN_DIR . 'vendor/autoload.php';
require_once LLMS_PLUGIN_DIR . 'includes/class-llms-loader.php';

if ( ! class_exists( 'LifterLMS' ) ) {
	require_once LLMS_PLUGIN_DIR . 'class-lifterlms.php';
}

register_activation_hook( __FILE__, array( 'LLMS_Install', 'install' ) );

// TODO: Move to media protector class
function llms_change_media_upload_directory( $params ) {
	if ( isset( $_REQUEST['llms'] ) && '1' === $_REQUEST['llms'] ) {
		$params['path']   = $params['basedir'] . '/lifterlms/' . date( 'Y/m' );
		$params['url']    = $params['baseurl'] . '/lifterlms/' . date( 'Y/m' );
		$params['subdir'] = '/lifterlms/' . date( 'Y/m' );
	}

	return $params;
}
add_filter( 'upload_dir', 'llms_change_media_upload_directory', 10, 1 );

// function llms_add_attachment_meta_on_upload( $data, $postarr, $unsanitized_postarr, $update ) {
// error_log( print_r( $data, true ) );
// error_log( print_r( $postarr, true ) );
//
// if ( false === $update && isset( $_REQUEST['llms'] ) && '1' === $_REQUEST['llms'] ) {
// ['meta_input'][ self::AUTHORIZATION_FILTER_KEY ] = $hook_name;
// $data['meta_input'][ LLMS_Media_Protector::AUTHORIZATION_FILTER_KEY ] = 'llms_attachment_is_access_allowed';
// }
// return $data;
// }
// add_filter( 'wp_insert_attachment_data', 'llms_add_attachment_meta_on_upload', 10, 4 );

function llms_add_authorization_meta_on_attachment_add( $post_id ) {
	$attachment = get_post( $post_id );
	if ( $attachment && 'attachment' === $attachment->post_type && isset( $_REQUEST['llms'] ) && '1' === $_REQUEST['llms'] ) {
		error_log( 'adding attachment meta!' );
		update_post_meta( $post_id, LLMS_Media_Protector::AUTHORIZATION_FILTER_KEY, 'llms_attachment_is_access_allowed' );
	}
}
add_action( 'add_attachment', 'llms_add_authorization_meta_on_attachment_add' );

/**
 * Returns the main instance of LifterLMS
 *
 * @since 4.0.0
 *
 * @return LifterLMS
 */
function llms() {
	return LifterLMS::instance();
}
return llms();
