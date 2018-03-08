<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage core admin notices
 *
 * @since    3.0.0
 * @version  3.16.14
 */
class LLMS_Admin_Notices_Core {

	/**
	 * Costructor
	 * @since    3.0.0
	 * @version  3.14.8
	 */
	public static function init() {

		add_action( 'admin_head', array( __CLASS__, 'maybe_hide_notices' ), 1 );
		add_action( 'current_screen', array( __CLASS__, 'maybe_hide_notices' ), 999 );

		add_action( 'current_screen', array( __CLASS__, 'add_init_actions' ) );
		add_action( 'switch_theme', array( __CLASS__, 'clear_sidebar_notice' ) );

	}

	/**
	 * Add actions on different hooks depending on the current screen
	 * Adds later for LLMS Settings screens to accommodate for settings that are updated later in the load cycle
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function add_init_actions() {

		$screen = get_current_screen();
		if ( ! empty( $screen->base ) && 'lifterlms_page_llms-settings' === $screen->base ) {
			$action = 'lifterlms_settings_notices';
			$priority = 5;
		} else {
			$action = 'current_screen';
			$priority = 77;
		}

		add_action( $action, array( __CLASS__, 'sidebar_support' ), $priority );
		add_action( $action, array( __CLASS__, 'gateways' ), $priority );
		add_action( $action, array( __CLASS__, 'check_staging' ), $priority );

	}

	/**
	 * Outputs a notice that allows users to enable or disable automated recurring payments
	 * appears when we identify that the url has changed or when an admin resets the settings
	 * from the button on the general settings tab
	 * @return   void
	 * @since    3.0.0
	 * @version  3.7.4
	 */
	public static function check_staging() {

		$id = 'maybe-staging';

		if ( isset( $_GET['llms-staging-status'] ) && isset( $_GET['_llms_staging_nonce'] ) ) {

			if ( ! wp_verify_nonce( $_GET['_llms_staging_nonce'], 'llms_staging_status' ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'lifterlms' ) );
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'Cheatin&#8217; huh?', 'lifterlms' ) );
			}

			if ( 'enable' === $_GET['llms-staging-status'] ) {
				LLMS_Site::set_lock_url();
				LLMS_Site::update_feature( 'recurring_payments', true );
			} elseif ( 'disable' === $_GET['llms-staging-status'] ) {
				LLMS_Site::clear_lock_url();
				LLMS_Site::update_feature( 'recurring_payments', false );
				update_option( 'llms_site_url_ignore', 'yes' );
			}

			LLMS_Admin_Notices::delete_notice( $id );

		}

		if ( ! LLMS_Site::is_clone_ignored() && ! LLMS_Admin_Notices::has_notice( $id ) && LLMS_Site::is_clone() ) {

			do_action( 'llms_site_clone_detected' );

			// disable recurring payments immediately
			LLMS_Site::update_feature( 'recurring_payments', false );

			LLMS_Admin_Notices::add_notice( $id, array(
				'type' => 'info',
				'dismissible' => false,
				'remindable' => false,
				'template' => 'admin/notices/staging.php',
			) );

		}

	}

	/**
	 * Check for gateways and output gateway notice
	 * @return   void
	 * @since    3.0.0
	 * @version  3.13.0
	 */
	public static function gateways() {
		$id = 'no-gateways';

		if ( ! apply_filters( 'llms_admin_notice_no_payment_gateways', LLMS()->payment_gateways()->has_gateways( true ) ) ) {
			$html = __( 'No LifterLMS Payment Gateways are currently enabled. Students will only be able to enroll in courses or memberships with free access plans.', 'lifterlms' ) . '<br><br>';
			$html .= sprintf( __( 'For starters you can configure manual payments on the %1$sCheckout Settings tab%2$s. Be sure to check out all the available %3$sLifterLMS Payment Gateways%4$s and install one later so that you can start selling your courses and memberships.', 'lifterlms' ), '<a href="' . add_query_arg( array(
				'page' => 'llms-settings',
				'tab' => 'checkout',
			), admin_url( 'admin.php' ) ) . '">', '</a>', '<a href="https://lifterlms.com/product-category/plugins/payment-gateways/" target="_blank">', '</a>' );
			LLMS_Admin_Notices::add_notice( $id, $html, array(
				'type' => 'warning',
				'dismiss_for_days' => 7,
				'remindable' => true,
			) );
		} elseif ( LLMS_Admin_Notices::has_notice( $id ) ) {
			LLMS_Admin_Notices::delete_notice( $id );
		}
	}

	/**
	 * Don't display notices on specific pages
	 * @return   void
	 * @since    3.14.8
	 * @version  3.16.14
	 */
	public static function maybe_hide_notices() {

		$screen = get_current_screen();

		if ( $screen && 'admin_page_llms-course-builder' === $screen->id ) {

			remove_all_actions( 'admin_notices' ); // 3rd party notices
			remove_action( 'admin_print_styles', array( 'LLMS_Admin_Notices', 'output_notices' ) ); // notices output by LifterLMS

		}

	}

	/**
	 * Check theme support for LifterLMS Sidebars
	 * @return   void
	 * @since    3.0.0
	 * @version  3.7.4
	 */
	public static function sidebar_support() {

		$theme = wp_get_theme();

		$id = 'sidebars';

		if ( ! current_theme_supports( 'lifterlms-sidebars' ) && ! in_array( $theme->get_template(), llms_get_core_supported_themes() ) ) {

			$msg = sprintf(
				__( '<strong>The current theme, %1$s, does not declare support for LifterLMS Sidebars.</strong> Course and Lesson sidebars may not work as expected. Please see our %2$sintegration guide%3$s or check out our %4$sLaunchPad%5$s theme which is designed specifically for use with LifterLMS.', 'lifterlms' ),
				$theme->get( 'Name' ),
				'<a href="https://lifterlms.com/docs/lifterlms-sidebar-support/?utm_source=notice&utm_medium=product&utm_content=sidebarsupport&utm_campaign=lifterlmsplugin" target="_blank">', '</a>',
				'<a href="https://lifterlms.com/product/launchpad/?utm_source=notice&utm_medium=product&utm_content=launchpad&utm_campaign=lifterlmsplugin" target="_blank">', '</a>'
			);

			LLMS_Admin_Notices::add_notice( $id, $msg, array(
				'dismissible' => true,
				'dismiss_for_days' => 730, // @todo there should be a "forever" setting
				'remindable' => false,
				'type' => 'warning',
			) );

		} elseif ( LLMS_Admin_Notices::has_notice( $id ) ) {

			LLMS_Admin_Notices::delete_notice( $id );

		}

	}

	/**
	 * Removes the current sidebar notice (if present) and clears notice delay transients
	 * Called when theme is switched
	 * @return   void
	 * @since    3.14.7
	 * @version  3.14.7
	 */
	public static function clear_sidebar_notice() {
		if ( LLMS_Admin_Notices::has_notice( 'sidebars' ) ) {
			LLMS_Admin_Notices::delete_notice( 'sidebars' );
		} else {
			delete_transient( 'llms_admin_notice_sidebars_delay' );
		}
	}

}

LLMS_Admin_Notices_Core::init();
