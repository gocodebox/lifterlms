<?php
/**
 * LLMS_Abstract_Controller_User_Engagements class
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 6.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Base class that handles awarded user engagements (achievements and certificates).
 *
 * @since 6.0.0
 */
abstract class LLMS_Abstract_Controller_User_Engagements {

	use LLMS_Trait_User_Engagement_Type;

	/**
	 * A text type for a sync operation error message when the user can not edit an awarded engagement.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	protected const TEXT_SYNC_AWARDED_ENGAGEMENT_INSUFFICIENT_PERMISSIONS = 0;

	/**
	 * A text type for a sync operation error message about an awarded engagement not having a valid engagement template.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	protected const TEXT_SYNC_AWARDED_ENGAGEMENT_INVALID_TEMPLATE = 1;

	/**
	 * A text type for a sync operation error message about the user not being able to edit awarded engagements.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	protected const TEXT_SYNC_AWARDED_ENGAGEMENTS_INSUFFICIENT_PERMISSIONS = 2;

	/**
	 * A text type for a sync operation error message about an invalid nonce.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	protected const TEXT_SYNC_AWARDED_ENGAGEMENTS_INVALID_NONCE = 3;

	/**
	 * A text type for a sync operation error message about a missing awarded engagement ID.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	protected const TEXT_SYNC_MISSING_AWARDED_ENGAGEMENT_ID = 4;

	/**
	 * A text type for a sync operation error message about a missing engagement template ID.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	protected const TEXT_SYNC_MISSING_ENGAGEMENT_TEMPLATE_ID = 5;

	/**
	 * Constructor.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'maybe_handle_awarded_engagement_sync_actions' ) );
	}

	/**
	 * Delete an awarded user engagement.
	 *
	 * @since 3.18.0
	 * @since 6.0.0 Permanently delete user engagement via wp_delete_post().
	 *              Refactored from LLMS_Controller_Achievements::delete() and LLMS_Controller_Certificates::delete().
	 *
	 * @param int $post_id WP Post ID of the awarded engagement.
	 * @return void
	 */
	protected function delete( $post_id ) {

		// Only allow LLMS admins to delete. is_admin() check also makes sure call is made from the dashboard.
		if ( ! is_admin() || ! current_user_can( 'manage_lifterlms' ) ) {
			return;
		}

		$post = get_post( $post_id );
		if ( ! $post || ! in_array( $post->post_type, array( 'llms_my_achievement', 'llms_my_certificate' ), true ) ) {
			return;
		}

		wp_delete_post( $post_id, true );
	}

	/**
	 * Returns a translated text of the given type.
	 *
	 * @since 6.0.0
	 *
	 * @param int   $text_type One of the LLMS_Abstract_Controller_User_Engagements::TEXT_ constants.
	 * @param array $variables Optional variables that are used in sprintf().
	 * @return string
	 */
	protected function get_text( $text_type, $variables = array() ) {

		return __( 'Invalid text type.', 'lifterlms' );
	}

	/**
	 * Handle awarded engagement sync actions.
	 *
	 * Errors are added to {@see LLMS_Admin_Metabox::add_error()} to be displayed as an admin notice
	 * and also returned for unit tests.
	 *
	 * If the sync is successful, the {@see llms_redirect_and_exit()} function is called and this method does not return.
	 *
	 * @since 6.0.0
	 *
	 * @return null|WP_Error
	 */
	public function maybe_handle_awarded_engagement_sync_actions() {

		// Validate action.
		// Invalid actions return a WP_Error for testing purposes and are not displayed to the user.
		$actions = array(
			'sync_one'  => "sync_awarded_{$this->engagement_type}",
			'sync_many' => "sync_awarded_{$this->engagement_type}s",
		);
		$action  = llms_filter_input( INPUT_GET, 'action' );
		if ( ! $action ) {
			return new WP_Error(
				"llms-sync-awarded-{$this->engagement_type}s-missing-action",
				__( 'Sorry, you have not provided any actions.', 'lifterlms' )
			);
		} elseif ( ! in_array( $action, $actions, true ) ) {
			return new WP_Error(
				"llms-sync-awarded-{$this->engagement_type}s-invalid-action",
				__( "You're trying to perform an invalid action.", 'lifterlms' )
			);
		}

		// Verify nonce.
		if ( ! llms_verify_nonce(
			"_llms_{$this->engagement_type}_sync_actions_nonce",
			"llms-{$this->engagement_type}-sync-actions",
			'GET'
		) ) {
			$result = new WP_Error(
				"llms-sync-awarded-{$this->engagement_type}s-invalid-nonce",
				$this->get_text( self::TEXT_SYNC_AWARDED_ENGAGEMENTS_INVALID_NONCE )
			);
			( new LLMS_Meta_Box_Award_Engagement_Submit() )->add_error( $result );

			return $result;
		}

		$engagement_id  = llms_filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
		$is_syncing_one = $action === $actions['sync_one'];

		if ( empty( $engagement_id ) ) {
			if ( $is_syncing_one ) {
				$code    = "llms-sync-missing-awarded-{$this->engagement_type}-id";
				$message = $this->get_text( self::TEXT_SYNC_MISSING_AWARDED_ENGAGEMENT_ID );
			} else {
				$code    = "llms-sync-missing-{$this->engagement_type}-template-id";
				$message = $this->get_text( self::TEXT_SYNC_MISSING_ENGAGEMENT_TEMPLATE_ID );
			}
			$result = new WP_Error( $code, $message );
		} elseif ( $is_syncing_one ) {
			$result = $this->sync_awarded_engagement( $engagement_id );
		} else {
			$result = $this->sync_awarded_engagements( $engagement_id );
		}

		if ( is_wp_error( $result ) ) {
			( new LLMS_Meta_Box_Award_Engagement_Submit() )->add_error( $result );
		}

		return $result;
	}

	/**
	 * Sync an awarded engagement with its template.
	 *
	 * If the sync is successful, the {@see llms_redirect_and_exit()} function is called and this method does not return.
	 *
	 * @since 6.0.0
	 *
	 * @param int $engagement_id Awarded engagement id.
	 * @return void|WP_Error
	 */
	private function sync_awarded_engagement( $engagement_id ) {

		if ( ! current_user_can( 'edit_post', $engagement_id ) ) {
			$variables = compact( 'engagement_id' );
			return new WP_Error(
				"llms-sync-awarded-{$this->engagement_type}-insufficient-permissions",
				$this->get_text( self::TEXT_SYNC_AWARDED_ENGAGEMENT_INSUFFICIENT_PERMISSIONS, $variables ),
				$variables
			);
		}

		$sync = $this->get_user_engagement( $engagement_id, true )->sync();
		if ( ! $sync ) {
			$variables = compact( 'engagement_id' );
			return new WP_Error(
				"llms-sync-awarded-{$this->engagement_type}-invalid-template",
				$this->get_text( self::TEXT_SYNC_AWARDED_ENGAGEMENT_INVALID_TEMPLATE, $variables ),
				$variables
			);
		}

		$redirect_url = get_edit_post_link( $engagement_id, 'raw' );
		$redirect_url = add_query_arg( 'message', 1, $redirect_url );
		llms_redirect_and_exit( $redirect_url );
	}

	/**
	 * Sync all the awarded engagements with their template.
	 *
	 * If the preflight checks are successful, the {@see llms_redirect_and_exit()} function is called after the sync is
	 * triggered and this method does not return.
	 *
	 * @since 6.0.0
	 *
	 * @param int $user_engagement_template_id User engagement template ID.
	 * @return void|WP_Error
	 */
	private function sync_awarded_engagements( $user_engagement_template_id ) {

		if ( ! current_user_can( get_post_type_object( "llms_my_{$this->engagement_type}" )->cap->edit_posts ) ) {
			return new WP_Error(
				"llms-sync-awarded-{$this->engagement_type}s-insufficient-permissions",
				$this->get_text( self::TEXT_SYNC_AWARDED_ENGAGEMENTS_INSUFFICIENT_PERMISSIONS )
			);
		}

		/**
		 * Fires an action to trigger the bulk sync of awarded engagements.
		 *
		 * The dynamic portion of this hook, `{$this->engagement_type}`, refers to the type of awarded engagement,
		 * either "achievement" or "certificate".
		 *
		 * @since 6.0.0
		 *
		 * @see LLMS_Processor_Certificate_Sync.
		 *
		 * @param int $user_engagement_template_id The user engagement template post ID.
		 */
		do_action( "llms_do_awarded_{$this->engagement_type}s_bulk_sync", $user_engagement_template_id );

		if ( empty( $_SERVER['HTTP_REFERER'] ) ) {
			llms_redirect_and_exit( get_edit_post_link( $user_engagement_template_id, 'raw' ) );
		} else {
			llms_redirect_and_exit( sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) );
		}
	}
}
