<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Update base class.
*
* Handles query objects
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Update {

	public $available_version;
	public $update_file = array();

	/**
	 * Constructor for the update class.
	 *
	 * @access public
	 */
	public function __construct() {

	}

	public function update_check() {

	}

	public function check_version() {
		$current_version = get_option( 'lifterlms_current_version' );
		$this->available_version = $this->update['version'];

		if ( version_compare( $current_version, $this->available_version, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'update_alert' ) );
		}
	}

	public function update_alert() {

	 	global $current_screen;
		 	echo '<div class="update-nag"><p>';
		 	echo '<a href="' . $this->update['homepage']. '">lifterLMS</a> ' . $this->available_version . ' is available. <a href="' . $this->update['download_url']  . '">Please update now</a></p>';
		 	echo '<p class="llms-update-alert">' . $this->update['upgrade_notice'] . '</p>';
		 	echo '</div>';
	}

}

return new LLMS_Update();
