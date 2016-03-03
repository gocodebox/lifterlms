<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

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
		add_action( 'init', array( $this, 'skip_first_time_setup' ) );
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

	public function skip_first_time_setup() {

		if ( empty( $_POST['action'] ) || ( 'llms-skip-setup' !== $_POST['action'] ) || empty( $_POST['_wpnonce'] ) ) {

			return;
		}

		update_option( 'lifterlms_first_time_setup', 'yes' );

	}

}

return new LLMS_Admin();
