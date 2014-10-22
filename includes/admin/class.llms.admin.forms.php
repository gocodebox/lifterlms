<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Front End Forms Class
*
* Class used managing front end facing forms.
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Admin_Forms {

	/**
	* Constructor
	*
	* initializes the forms methods
	*/
	public function __construct() {

		add_filter('redirect',  array( $this, 'updateAdminCourse'));

	}

	public function updateAdminCourse() {
		LLMS_log('updateAdminCourse');
	}

}

new LLMS_Admin_Forms();
