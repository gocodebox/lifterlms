<?php
/**
 * Manage core admin notices
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 3.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Manage core admin notices class
 *
 * @since 3.0.0
 * @since 6.0.0 Removed the deprecated `LLMS_Admin_Notices_Core::check_staging()` method.
 */
class LLMS_Admin_Notices_Core {

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 * @since 3.14.8 Add handler for removing dismissed notices.
	 *
	 * @return void
	 */
	public static function init() {

		add_action( 'admin_head', array( __CLASS__, 'maybe_hide_notices' ), 1 );
		add_action( 'current_screen', array( __CLASS__, 'maybe_hide_notices' ), 999 );

		add_action( 'current_screen', array( __CLASS__, 'add_init_actions' ) );

	}

	/**
	 * Add actions on different hooks depending on the current screen
	 *
	 * Adds later for LLMS Settings screens to accommodate for settings that are updated later in the load cycle.
	 *
	 * @since 3.0.0
	 * @since 4.12.0 Remove hook for deprecated `check_staging()` notice.
	 *
	 * @return void
	 */
	public static function add_init_actions() {

		$screen = get_current_screen();
		if ( ! empty( $screen->base ) && 'lifterlms_page_llms-settings' === $screen->base ) {
			$action   = 'lifterlms_settings_notices';
			$priority = 5;
		} else {
			$action   = 'current_screen';
			$priority = 77;
		}

		add_action( $action, array( __CLASS__, 'gateways' ), $priority );

	}

	/**
	 * Check for gateways and output gateway notice
	 *
	 * @since 3.0.0
	 * @since 3.13.0 Unknown.
	 * @since 4.5.0 Dismiss notice for 2 years instead of 7 days.
	 *
	 * @return void
	 */
	public static function gateways() {
		$id = 'no-gateways';

		if ( ! apply_filters( 'llms_admin_notice_no_payment_gateways', llms()->payment_gateways()->has_gateways( true ) ) ) {
			$html  = __( 'No LifterLMS Payment Gateways are currently enabled. Students will only be able to enroll in courses or memberships with free access plans.', 'lifterlms' ) . '<br><br>';
			$html .= sprintf(
				__( 'For starters you can configure manual payments on the %1$sCheckout Settings tab%2$s. Be sure to check out all the available %3$sLifterLMS Payment Gateways%4$s and install one later so that you can start selling your courses and memberships.', 'lifterlms' ),
				'<a href="' . add_query_arg(
					array(
						'page' => 'llms-settings',
						'tab'  => 'checkout',
					),
					admin_url( 'admin.php' )
				) . '">',
				'</a>',
				'<a href="https://lifterlms.com/product-category/plugins/payment-gateways/" target="_blank">',
				'</a>'
			);
			LLMS_Admin_Notices::add_notice(
				$id,
				$html,
				array(
					'type'             => 'warning',
					'dismiss_for_days' => 730, // @TODO: there should be a "forever" setting here.
					'remindable'       => true,
				)
			);
		} elseif ( LLMS_Admin_Notices::has_notice( $id ) ) {
			LLMS_Admin_Notices::delete_notice( $id );
		}
	}

	/**
	 * Don't display notices on specific pages
	 *
	 * @since 3.14.8
	 * @since 3.16.14 Unknown.
	 *
	 * @return void
	 */
	public static function maybe_hide_notices() {

		$screen = get_current_screen();

		if ( $screen && 'admin_page_llms-course-builder' === $screen->id ) {

			remove_all_actions( 'admin_notices' ); // 3rd party notices.
			remove_action( 'admin_print_styles', array( 'LLMS_Admin_Notices', 'output_notices' ) ); // Notices output by LifterLMS.

		}

	}

}

LLMS_Admin_Notices_Core::init();
