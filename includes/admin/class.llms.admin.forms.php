<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Front End Forms Class
*
* Class used managing front end facing forms.
*
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

	}

}

new LLMS_Admin_Forms();
