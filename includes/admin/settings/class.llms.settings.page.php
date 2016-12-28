<?php
/**
* Admin Settings Page Base Class
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Settings_Page {

	/**
	 * Allow settings page to determine if a rewrite flush is required
	 * @var      boolean
	 * @since    3.0.4
	 * @version  3.0.4
	 */
	protected $flush = false;

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
	 * Flushes rewrite rules when necessary
	 * @return   void
	 * @since    3.0.4
	 * @version  3.0.4
	 */
	public function flush_rewrite_rules() {

		// add the updated endpoints
		$q = new LLMS_Query();
		$q->add_endpoints();

		// flush rewrite rules
		flush_rewrite_rules();

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
	 * @return   void
	 * @since    1.0.0
	 * @version  3.0.4
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings();
		LLMS_Admin_Settings::save_fields( $settings );

		if ( $current_section ) {
	    	do_action( 'lifterlms_update_options_' . $this->id . '_' . $current_section );
	    }

	    if ( $this->flush ) {

	    	add_action( 'shutdown', array( $this, 'flush_rewrite_rules' ) );

	    }

	}

}
