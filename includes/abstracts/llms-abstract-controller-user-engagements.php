<?php
/**
 * LLMS_Abstract_Controller_User_Engagements class
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Base class that handles awarded user engagements (achievements and certificates).
 *
 * @since [version]
 */
abstract class LLMS_Abstract_Controller_User_Engagements
	implements LLMS_Interface_User_Engagement_Type {

	use LLMS_Trait_User_Engagement_Type;

	/**
	 * Constructor.
	 *
	 * @since [version]
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
	 * @since [version] Permanently delete user engagement via wp_delete_post().
	 *              Refactored from LLMS_Controller_Achievements::delete() and LLMS_Controller_Certificates::delete().
	 *
	 * @param int $post_id WP Post ID of the awarded engagement.
	 * @return void
	 */
	protected function delete( $post_id ) {

		if ( ! is_admin() ) {
			return;
		}

		wp_delete_post( $post_id, true );
	}

	/**
	 * Handle awarded engagement sync actions.
	 *
	 * Errors are added to {@see LLMS_Admin_Metabox::add_error()} to be displayed as an admin notice
	 * and also returned for unit tests.
	 *
	 * If the sync is successful, the {@see llms_redirect_and_exit()} function is called and this method does not return.
	 *
	 * @since [version]
	 *
	 * @return null|WP_Error
	 */
	public function maybe_handle_awarded_engagement_sync_actions() {

		// Validate action.
		// Invalid actions return a WP_Error for testing purposes and are not displayed to the user.
		$actions = array(
			'sync_one'  => "sync_awarded_$this->engagement_type",
			'sync_many' => "sync_awarded_{$this->engagement_type}s",
		);
		if ( ! isset( $_GET['action'] ) ) {
			return new WP_Error(
				"llms-sync-awarded-{$this->engagement_type}s-missing-action",
				__( 'Sorry, you have not provided any actions.', 'lifterlms' )
			);
		} elseif ( ! in_array( $_GET['action'], $actions ) ) {
			return new WP_Error(
				"llms-sync-awarded-{$this->engagement_type}s-invalid-action",
				__( 'You\'re trying to perform an invalid action.', 'lifterlms' )
			);
		}

		// Verify nonce.
		if ( ! llms_verify_nonce(
			"_llms_{$this->engagement_type}_sync_actions_nonce",
			"llms-$this->engagement_type-sync-actions",
			'GET'
		) ) {
			$result = new WP_Error(
				"llms-sync-awarded-{$this->engagement_type}s-invalid-nonce",
				sprintf(
					/* translators: 1: plural lowercase awarded engagement type, 2: plural uppercase first letter awarded engagement type */
					__( 'Sorry, you are not allowed to sync %1$s.', 'lifterlms' ),
					$this->get_engagement_type_name( self::PLURAL, LLMS_Case::LOWER, self::AWARDED ),
					$this->get_engagement_type_name( self::PLURAL, LLMS_Case::UPPER_FIRST, self::AWARDED )
				)
			);
			( new LLMS_Meta_Box_Award_Engagement_Submit() )->add_error( $result );

			return $result;
		}

		$engagement_id = llms_filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );

		if ( $_GET['action'] === $actions['sync_one'] ) {

			if ( empty( $engagement_id ) ) {
				$result = new WP_Error(
					"llms-sync-missing-awarded-$this->engagement_type-id",
					sprintf(
						/* translators: 1: singular lowercase awarded engagement type, 2: singular uppercase first letter awarded engagement type */
						_x( 'Sorry, you need to provide a valid %1$s ID.', 'awarded', 'lifterlms' ),
						$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::LOWER, self::AWARDED ),
						$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::UPPER_FIRST, self::AWARDED )
					)
				);
			} else {
				$result = $this->sync_awarded_engagement( $engagement_id );
			}
		} elseif ( $_GET['action'] === $actions['sync_many'] ) {

			if ( empty( $engagement_id ) ) {
				$result = new WP_Error(
					"llms-sync-missing-{$this->engagement_type}-template-id",
					sprintf(
						/* translators: 1: singular lowercase engagement template type, 2: singular uppercase first letter engagement template type */
						_x( 'Sorry, you need to provide a valid %1$s ID.', 'template', 'lifterlms' ),
						$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::LOWER, self::TEMPLATE ),
						$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::UPPER_FIRST, self::TEMPLATE )
					)
				);
			} else {
				$result = $this->sync_awarded_engagements( $engagement_id );
			}
		} else {
			$result = null;
		}

		if ( is_object( $result ) && 'WP_Error' === get_class( $result ) ) {
			( new LLMS_Meta_Box_Award_Engagement_Submit() )->add_error( $result );
		}

		return $result;
	}

	/**
	 * Sync an awarded engagement with its template.
	 *
	 * If the sync is successful, the {@see llms_redirect_and_exit()} function is called and this method does not return.
	 *
	 * @since [version]
	 *
	 * @param int $engagement_id Awarded engagement id.
	 * @return void|WP_Error
	 */
	private function sync_awarded_engagement( $engagement_id ) {

		if ( ! current_user_can( 'edit_post', $engagement_id ) ) {
			return new WP_Error(
				"llms-sync-awarded-$this->engagement_type-insufficient-permissions",
				sprintf(
					/* translators: 1: singular lowercase awarded engagement type, 2: awarded engagement ID, 3: singular uppercase first letter awarded engagement type */
					__( 'Sorry, you are not allowed to edit the awarded %1$s #%2$d.', 'lifterlms' ),
					$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::LOWER, self::AWARDED ),
					$engagement_id,
					$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::UPPER_FIRST, self::AWARDED )
				),
				compact( 'engagement_id' )
			);
		}

		$sync = $this->get_awarded_engagement( $engagement_id )->sync();
		if ( ! $sync ) {
			return new WP_Error(
				"llms-sync-awarded-$this->engagement_type-invalid-template",
				sprintf(
					/* translators: 1: singular lowercase awarded engagement type, 2: awarded engagement ID, 3: singular lowercase engagement template type, 4: singular uppercase first letter awarded engagement type, 5: singular uppercase first letter engagement template type */
					__( 'Sorry, the %1$s #%2$d does not have a valid %3$s.', 'lifterlms' ),
					$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::LOWER, self::AWARDED ),
					$engagement_id,
					$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::LOWER, self::TEMPLATE ),
					$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::UPPER_FIRST, self::AWARDED ),
					$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::UPPER_FIRST, self::TEMPLATE )
				),
				compact( 'engagement_id' )
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
	 * @since [version]
	 *
	 * @param int $user_engagement_template_id User engagement template ID.
	 * @return void|WP_Error
	 */
	private function sync_awarded_engagements( $user_engagement_template_id ) {

		if ( ! current_user_can( get_post_type_object( "llms_my_$this->engagement_type" )->cap->edit_posts ) ) {
			return new WP_Error(
				"llms-sync-awarded-{$this->engagement_type}s-insufficient-permissions",
				sprintf(
					/* translators: 1: plural lowercase awarded engagement type, 2: plural uppercase first letter awarded engagement type */
					__( 'Sorry, you are not allowed to edit %1$s.', 'lifterlms' ),
					$this->get_engagement_type_name( self::PLURAL, LLMS_Case::LOWER, self::AWARDED ),
					$this->get_engagement_type_name( self::PLURAL, LLMS_Case::UPPER_FIRST, self::AWARDED )
				)
			);
		}

		/**
		 * Fires an action to trigger the bulk sync of awarded engagements.
		 *
		 * @since [version]
		 *
		 * @see   LLMS_Processor_Certificate_Sync.
		 *
		 * @param int $user_engagement_template_id The user engagement template post ID.
		 */
		do_action( "llms_do_awarded_{$this->engagement_type}s_bulk_sync", $user_engagement_template_id );
		llms_redirect_and_exit( get_edit_post_link( $user_engagement_template_id, 'raw' ) );
	}
}
