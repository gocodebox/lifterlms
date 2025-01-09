<?php
/**
 * LLMS_Controller_Achievements class
 *
 * @package LifterLMS/Controllers/Classes
 *
 * @since 3.18.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles awarded user achievements.
 *
 * @since 3.18.0
 * @since 3.35.0 Sanitize `$_POST` data.
 * @since 6.0.0 Extended from the LLMS_Abstract_Controller_User_Engagements class.
 */
class LLMS_Controller_Achievements extends LLMS_Abstract_Controller_User_Engagements {

	/**
	 * Type of user engagement.
	 *
	 * @since 6.0.0
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
	 * Returns a translated text of the given type.
	 *
	 * @since 6.0.0
	 *
	 * @param int   $text_type One of the LLMS_Abstract_Controller_User_Engagements::TEXT_ constants.
	 * @param array $variables Optional variables that are used in sprintf().
	 * @return string
	 */
	protected function get_text( $text_type, $variables = array() ) {

		switch ( $text_type ) {
			case self::TEXT_SYNC_AWARDED_ENGAGEMENT_INSUFFICIENT_PERMISSIONS:
				return sprintf(
					/* translators: %1$d: awarded achievement ID */
					__( 'Sorry, you are not allowed to edit the awarded achievement #%1$d.', 'lifterlms' ),
					( $variables['engagement_id'] ?? 0 )
				);
			case self::TEXT_SYNC_AWARDED_ENGAGEMENT_INVALID_TEMPLATE:
				return sprintf(
					/* translators: %1$d: awarded achievement ID */
					__( 'Sorry, the awarded achievement #%1$d does not have a valid achievement template.', 'lifterlms' ),
					( $variables['engagement_id'] ?? 0 )
				);
			case self::TEXT_SYNC_AWARDED_ENGAGEMENTS_INSUFFICIENT_PERMISSIONS:
				return __( 'Sorry, you are not allowed to edit awarded achievements.', 'lifterlms' );
			case self::TEXT_SYNC_AWARDED_ENGAGEMENTS_INVALID_NONCE:
				return __( 'Sorry, you are not allowed to sync awarded achievements.', 'lifterlms' );
			case self::TEXT_SYNC_MISSING_AWARDED_ENGAGEMENT_ID:
				return __( 'Sorry, you need to provide a valid awarded achievement ID.', 'lifterlms' );
			case self::TEXT_SYNC_MISSING_ENGAGEMENT_TEMPLATE_ID:
				return __( 'Sorry, you need to provide a valid achievement template ID.', 'lifterlms' );
			default:
				return parent::get_text( $text_type );
		}
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
			if ( ! current_user_can( 'manage_lifterlms' ) ) {
				return;
			}

			$this->delete( llms_filter_input( INPUT_POST, 'achievement_id', FILTER_SANITIZE_NUMBER_INT ) );
		}
	}
}

return new LLMS_Controller_Achievements();
