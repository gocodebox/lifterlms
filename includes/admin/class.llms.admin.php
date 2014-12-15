<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'LLMS_Admin' ) ) :

/**
* Admin Class
*
* Sets up menus, post types and assets
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Admin {

	/**
	* Constructor
	*
	* executes includes on init
	*/
	public function __construct() {
		add_action( 'init', array( $this, 'include_admin_classes' ) );
	}

	/**
	* Includes
	*
	* Includes Admin classes files
	*
	* @return void
	*/
	public function include_admin_classes() {

		include_once( 'class.llms.admin.menus.php' );
		include_once( 'class.llms.admin.post-types.php' );
		include_once( 'class.llms.admin.assets.php' );
	}
}

endif;

return new LLMS_Admin();
