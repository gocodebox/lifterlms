<?php
/**
 * Admin Settings Page: REST API
 *
 * @package LifterLMS_REST/Admin/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Settings Page: REST API
 *
 * @since 1.0.0-beta.1
 */
class LLMS_Rest_Admin_Settings_Page extends LLMS_Settings_Page {

	/**
	 * Constructor
	 *
	 * @since 1.0.0-beta.1
	 */
	public function __construct() {

		require_once 'class-llms-rest-admin-settings-api-keys.php';
		require_once 'class-llms-rest-admin-settings-webhooks.php';

		$this->id    = 'rest-api';
		$this->label = __( 'REST API', 'lifterlms' );

		// Output Stuff.
		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'lifterlms_sections_' . $this->id, array( $this, 'output_sections_nav' ) );
		add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );

		// Maybe Save API Keys.
		add_action( 'lifterlms_settings_save_' . $this->id, array( 'LLMS_Rest_Admin_Settings_API_Keys', 'save' ) );

		// Disable the default page's save button.
		add_filter( 'llms_settings_rest-api_has_save_button', '__return_false' );

		add_filter( 'llms_table_get_table_classes', array( $this, 'get_table_classes' ), 10, 2 );
		add_action( 'lifterlms_admin_field_title-with-html', array( $this, 'output_title_field' ), 10 );

	}

	/**
	 * Retrieve the id of the current tab/section
	 *
	 * Overrides parent function to set "keys" as the default section instead of the nonexistant "main".
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return string
	 */
	protected function get_current_section() {

		$current = parent::get_current_section();
		if ( 'main' === $current ) {
			$all     = array_keys( $this->get_sections() );
			$current = $all ? $all[0] : 'main';
		}
		return $current;

	}

	/**
	 * Get the page sections
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function get_sections() {

		$sections = array();

		if ( current_user_can( 'manage_lifterlms_api_keys' ) ) {
			$sections['keys'] = __( 'API Keys', 'lifterlms' );
		}

		if ( current_user_can( 'manage_lifterlms_webhooks' ) ) {
			$sections['webhooks'] = __( 'Webhooks', 'lifterlms' );
		}

		/**
		 * Modify the available tabs on the REST API settings screen.
		 *
		 * @since 1.0.0-beta.1
		 *
		 * @param array $sections Array of settings page tabs.
		 */
		return apply_filters( 'llms_rest_api_settings_sections', $sections );

	}

	/**
	 * Get settings array
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function get_settings() {

		$curr_section = $this->get_current_section();

		$settings = array();
		if ( current_user_can( 'manage_lifterlms_api_keys' ) && 'keys' === $curr_section ) {
			$settings = LLMS_Rest_Admin_Settings_API_Keys::get_fields();
		} elseif ( current_user_can( 'manage_lifterlms_webhooks' ) && 'webhooks' === $curr_section ) {
			$settings = LLMS_Rest_Admin_Settings_Webhooks::get_fields();
		}

		return apply_filters( 'llms_rest_api_settings_' . $curr_section, $settings );

	}

	/**
	 * Add CSS classes to the API Keys Table.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param string[] $classes Array of css class names.
	 * @param string   $id Table ID.
	 * @return string[]
	 */
	public function get_table_classes( $classes, $id ) {

		if ( in_array( $id, array( 'rest-api-keys', 'rest-webhooks' ), true ) ) {
			$classes[] = 'text-left';
		}
		return $classes;

	}

	/**
	 * Outputs a custom "title" field with HTML content as the settings section title.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $field Settings field arguments.
	 * @return void
	 */
	public function output_title_field( $field ) {

		echo '<p class="llms-label">' . esc_html( $field['title'] ) . ' ' . $field['html'] . '</p>';
		echo '<table class="form-table">';

	}

}

return new LLMS_Rest_Admin_Settings_Page();
