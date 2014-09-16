<?php
/**
 * LifterLMS Admin.
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

if ( ! class_exists( 'LLMS_Admin' ) ) :

/**
 * LLMS_Admin Class
 */
class LLMS_Admin {

	/**
	 * LLMS_Admin_Menus Constructor.
	 * @access public
	 * @return LLMS_Admin
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
	}

	/**
	 * Include required admin classes
	 */
	public function includes () {

		include_once( 'class.llms.admin.menus.php');
		include_once( 'class.llms.admin.post-types.php' );
		include_once( 'class.llms.admin.assets.php' );
	}
}

endif;

return new LLMS_Admin();