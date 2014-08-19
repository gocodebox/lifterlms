<?php
/**
 * LifterLMS Settings Page
 *
 * @author 		codeBOX
 * @category 	Admin
 * @package 	LifterLMS/Admin
 * @version     0.1
 */

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'LLMS_Settings_Page' ) ) :

/**
 * LLMS_Admin_Settings Class
 */
class LLMS_Settings_Page {

	/**
	 * Add page to settings.
	 */
	public function add_settings_page( $pages ) {
		$pages[ $this->id ] = $this->label;

		return $pages;
	}

	public function get_sections() {
		return array();
	}

	/**
	 * Output settings sections as tabs
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
	 * Output the settings
	 */
	public function output() {
		$settings = $this->get_settings();

		LLMS_Admin_Settings::output_fields( $settings );
	}
	
}

endif;