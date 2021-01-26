<?php
/**
 * Admin tool to reset the status of the recurring payments site feature
 *
 * @package LifterLMS/Admin/Tools/Classes
 *
 * @since 4.13.0
 * @version 4.13.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Tool_Reset_Automatic_Payments
 *
 * @since 4.13.0
 */
class LLMS_Admin_Tool_Reset_Automatic_Payments extends LLMS_Abstract_Admin_Tool {

	/**
	 * Tool ID.
	 *
	 * @var string
	 */
	protected $id = 'automatic-payments';

	/**
	 * Tool Load Priority
	 *
	 * To preserve the "original" tool order, load this before unclassed core tools.
	 *
	 * @var integer
	 */
	protected $priority = 4;

	/**
	 * Retrieve a description of the tool
	 *
	 * This is displayed on the right side of the tool's list before the button.
	 *
	 * @since 4.13.0
	 *
	 * @return string
	 */
	protected function get_description() {
		return __( 'Allows you to choose to enable or disable automatic recurring payments which may be disabled on a staging site.', 'lifterlms' );
	}

	/**
	 * Retrieve the tool's label
	 *
	 * The label is the tool's title. It's displayed in the left column on the tool's list.
	 *
	 * @since 4.13.0
	 *
	 * @return string
	 */
	protected function get_label() {
		return __( 'Reset Automatic Payments Status', 'lifterlms' );
	}

	/**
	 * Retrieve the tool's button text
	 *
	 * @since 4.13.0
	 *
	 * @return string
	 */
	protected function get_text() {
		return __( 'Reset Automatic Payments Status', 'lifterlms' );
	}

	/**
	 * Process the tool.
	 *
	 * This method should do whatever the tool actually does.
	 *
	 * By the time this tool is called a nonce and the user's capabilities have already been checked.
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	protected function handle() {

		LLMS_Site::clear_lock_url();
		update_option( 'llms_site_url_ignore', 'no' );
		LLMS_Site::check_status();
		llms_redirect_and_exit( esc_url_raw( admin_url( 'admin.php?page=llms-status&tab=tools' ) ) );

	}

	/**
	 * Conditionally load the tool
	 *
	 * This tool should only load if the recurring payments site feature constant and the site clone status
	 * constant are both NOT set.
	 *
	 * @since 4.13.0
	 *
	 * @return boolean Return `true` to load the tool and `false` to not load it.
	 */
	protected function should_load() {

		return ! defined( 'LLMS_SITE_FEATURE_RECURRING_PAYMENTS' ) && ! defined( 'LLMS_SITE_IS_CLONE' );

	}

}

return new LLMS_Admin_Tool_Reset_Automatic_Payments();
