<?php
/**
 * LifterLMS General Settings
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

if ( ! class_exists( 'LLMS_Settings_General' ) ) :

/**
 * LLMS_Admin_Settings Class
 */
class LLMS_Settings_General extends LLMS_Settings_Page {

	/**
	 * LLMS_Settings_General Constructor.
	 * @access public
	 * @return LLMS_Settings_General
	 */
	public function __construct() {
		$this->id    = 'general';
		$this->label = __( 'General', 'lifterlms' );

		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );
	}

	/**
	 * to-do: Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
	}
	
}

endif;

return new LLMS_Settings_General();