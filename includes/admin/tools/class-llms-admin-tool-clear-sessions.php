<?php
/**
 * Admin tool to delete pending batches created by a background processor
 *
 * @package LifterLMS/Admin/Tools/Classes
 *
 * @since 4.0.0
 * @version 4.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Tool_Clear_Sessions
 *
 * @since 4.0.0
 */
class LLMS_Admin_Tool_Clear_Sessions extends LLMS_Abstract_Admin_Tool {

	/**
	 * Tool ID.
	 *
	 * @var string
	 */
	protected $id = 'clear-sessions';

	/**
	 * Retrieve a description of the tool
	 *
	 * This is displayed on the right side of the tool's list before the button.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function get_description() {
		return __( 'LifterLMS user sessions store temporary data related to error messages and order information during payment processing. Stale sessions are automatically deleted. This tool can be used to delete all existing user sessions.', 'lifterlms' );
	}

	/**
	 * Retrieve the tool's label
	 *
	 * The label is the tool's title. It's displayed in the left column on the tool's list.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function get_label() {
		return __( 'User Sessions', 'lifterlms' );
	}

	/**
	 * Retrieve the tool's button text
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function get_text() {
		return __( 'Clear All User Sessions', 'lifterlms' );
	}

	/**
	 * Process the tool.
	 *
	 * This method should do whatever the tool actually does.
	 *
	 * By the time this tool is called a nonce and the user's capabilities have already been checked.
	 *
	 * @since 4.0.0
	 *
	 * @return mixed
	 */
	protected function handle() {

		do_action( 'llms_delete_expired_session_data', false );
		return true;

	}

}

return new LLMS_Admin_Tool_Clear_Sessions();
