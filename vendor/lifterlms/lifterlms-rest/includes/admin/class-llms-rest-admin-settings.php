<?php
/**
 * Manage admin settings pages.
 *
 * @package  LifterLMS_REST/Admin/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Manage admin settings pages.
 *
 * @since 1.0.0-beta.1
 */
class LLMS_REST_Admin_Settings {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'lifterlms_get_settings_pages', array( $this, 'add_pages' ) );

	}

	/**
	 * Register the REST API settings page with the LifterLMS Core.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $pages Array of settings page classes.
	 * @return array
	 */
	public function add_pages( $pages ) {

		$pages[] = include 'class-llms-rest-admin-settings-page.php';

		return $pages;

	}

}

return new LLMS_REST_Admin_Settings();
