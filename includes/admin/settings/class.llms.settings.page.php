<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Admin Settings Page Base Class
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Settings_Page {

	/**
	 * Add the settings page
	 *
	 * @return array
	 */
	public function add_settings_page( $pages ) {
		$pages[ $this->id ] = $this->label;

		return $pages;
	}

	/**
	 * Get the page sections
	 *
	 * @return array
	 */
	public function get_sections() {
		return array();
	}

	/**
	 * Output settings sections as tabs and set post href
	 *
	 * @return array
	 */
	public function output_sections() {
		global $current_section;

		$sections = $this->get_sections();

		if ( empty( $sections ) ) {
			return;
		}

		echo '<ul>';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . admin_url( 'admin.php?page=' . $this->id . '&section=' . sanitize_title( $id ) )
			. '"class="' . ($current_section == $id ? 'current' : '' ) . '">' . ( end( $array_keys ) == $id ? '' : '|' ) . '</li>';

			echo '</ul><br class="clear" />';
		}

	}

	/**
	 * Output the settings fields
	 *
	 * @return LLMS_Admin_Settings::output_fields
	 */
	public function output() {
		$settings = $this->get_settings();

		LLMS_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save the settings field values
	 *
	 * @return void
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings();
		LLMS_Admin_Settings::save_fields( $settings );

		if ( $current_section ) {
	    	do_action( 'lifterlms_update_options_' . $this->id . '_' . $current_section ); }

	}

}
