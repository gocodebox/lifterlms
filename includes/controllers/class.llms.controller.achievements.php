<?php
/**
 * Achievement controller
 *
 * @package LifterLMS/Controllers/Classes
 *
 * @since 3.18.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Controller_Achievements
 *
 * @since 3.18.0
 * @since 3.35.0 Sanitize `$_POST` data.
 */
class LLMS_Controller_Achievements {

	/**
	 * Constructor
	 *
	 * @since 3.18.0
	 *
	 * @return void
	 */
	public function __construct() {
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

	/**
	 * Delete an achievement.
	 *
	 * @since 3.18.0
	 * @since [version] Permanently delete achievement via wp_delete_post().
	 *
	 * @param int $achievement_id WP Post ID of the llms_my_achievement.
	 * @return void
	 */
	private function delete( $achievement_id ) {

		if ( ! is_admin() ) {
			return;
		}

		wp_delete_post( $achievement_id, true );

	}

}

return new LLMS_Controller_Achievements();
