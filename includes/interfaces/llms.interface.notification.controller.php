<?php
/**
 * LifterLMS Notification Controller Interface
 * @since    ??
 * @version  ??
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

interface LLMS_Interface_Notification_Controller {

	/**
	 * Callback function for sending notifications
	 * Depending on the action that triggers this callback there will be a variable number of parameters
	 * @return   void
	 * @since    ??
	 * @version  ??
	 */
	public function action_callback();

}
