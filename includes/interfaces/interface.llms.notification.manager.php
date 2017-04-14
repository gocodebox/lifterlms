<?php
/**
 * LifterLMS Notification Interface
 * @since    ??
 * @version  ??
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

interface LLMS_Interface_Notification_Manager {

	/**
	 * Characters added before merge codes
	 */
	const MERGE_CODE_PREFIX = '{{';

	/**
	 * Characters added after merge codes
	 */
	const MERGE_CODE_SUFFIX = '}}';

	/**
	 * Callback function for notifications
	 * Depending on the action that triggers this callback there will be a variable number of parameters
	 * @return   void
	 * @since    ??
	 * @version  ??
	 */
	public function callback();

}
