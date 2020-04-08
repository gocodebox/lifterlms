<?php
/**
 * LifterLMS Notification Controller Interface
 *
 * @package LifterLMS/Interfaces
 *
 * @since Unknown
 * @version Unknown
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Interface_Notification_Controller
 *
 * @since Unknown
 */
interface LLMS_Interface_Notification_Controller {

	/**
	 * Callback function for sending notifications
	 *
	 * Depending on the action that triggers this callback there will be a variable number of parameters
	 *
	 * @since Unknown
	 *
	 * @return void
	 */
	public function action_callback();

}
