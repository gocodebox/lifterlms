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
	
}

endif;