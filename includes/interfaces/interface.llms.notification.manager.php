<?php
/**
 * LifterLMS Notification Interface
 *
 * @package LifterLMS/Interfaces
 *
 * @since Unknown
 * @version Unknown
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Interface_Notification_Manager
 *
 * @since Unknown
 */
interface LLMS_Interface_Notification_Manager {

	/**
	 * Characters added before merge codes
	 *
	 * @var string
	 */
	const MERGE_CODE_PREFIX = '{{';

	/**
	 * Characters added after merge codes
	 *
	 * @var string
	 */
	const MERGE_CODE_SUFFIX = '}}';

	/**
	 * Callback function for notifications
	 *
	 * Depending on the action that triggers this callback there will be a variable number of parameters
	 *
	 * @since Unknown
	 *
	 * @return void
	 */
	public function callback();

}
