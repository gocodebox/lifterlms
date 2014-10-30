<?php
if ( ! defined( 'ABSPATH' ) ) exit;



if ( ! class_exists( 'LLMS_Update' ) ) :

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
		// LLMS_log('update construct called');

		// //if ( is_admin() ) {
		// 	add_action( 'plugins_loaded', array( $this,'update_check' ) );
		//}

	}

	public function update_check() {
		
		
		// $update_obj = PucFactory::buildUpdateChecker( 'http://w-shadow.com/files/external-update-example/info.json', __FILE__ );
		// LLMS_log($update_file);
		// $update_file->checkPeriod = 0;

		// $update_file = file_get_contents($update_obj->metadataUrl);

		// if ($update_file) {

		// 	//$this->update_file = json_decode($update_file,true);

		// 	$this->update=json_decode($update_file,true);
		// 	$this->check_version();
		// }



	}

	public function check_version() {
		LLMS_log($update);

		llms_log($this->update['version']);
		$current_version = get_option('lifterlms_current_version');
		$this->available_version = $this->update['version'];
		
		if ( version_compare( $current_version, $this->available_version, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'update_alert' ));
		}
	}

	public function update_alert(){

     	global $current_screen;
         	echo '<div class="update-nag"><p>';
         	echo '<a href="' . $this->update['homepage']. '">lifterLMS</a> ' . $this->available_version . ' is available. <a href="' . $this->update['download_url']  . '">Please update now</a></p>';
         	echo '<p class="llms-update-alert">' . $this->update['upgrade_notice'] . '</p>';
         	echo '</div>';
	}

}

endif;

return new LLMS_Update();
