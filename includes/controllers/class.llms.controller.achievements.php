<?php
/**
 * LLMS_Controller_Achievements class
 *
 * @package LifterLMS/Controllers/Classes
 *
 * @since 3.18.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles awarded user achievements.
 *
 * @since 3.18.0
 * @since 3.35.0 Sanitize `$_POST` data.
 * @since [version] Extended from the LLMS_Abstract_Controller_User_Engagements class.
 */
class LLMS_Controller_Achievements extends LLMS_Abstract_Controller_User_Engagements {

	/**
	 * Type of user engagement.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $engagement_type = 'achievement';

	/**
	 * Constructor
	 *
	 * @since 3.18.0
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
		add_action( 'init', array( $this, 'maybe_handle_reporting_actions' ) );
	}

	/**
	 * Handle achievement form actions to download (for students and admins) and to delete (admins only)
	 *
	 * @since 3.18.0
	 * @since 3.35.0 Sanitize `$_POST` data.
	 *
	 * @return void
	 */
	public function maybe_handle_reporting_actions() {

		if ( ! llms_verify_nonce( '_llms_achievement_actions_nonce', 'llms-achievement-actions' ) ) {
			return;
		}

		if ( isset( $_POST['llms_delete_achievement'] ) ) {
			$this->delete( llms_filter_input( INPUT_POST, 'achievement_id', FILTER_SANITIZE_NUMBER_INT ) );
		}

	}
}

return new LLMS_Controller_Achievements();
