<?php
/**
 * Manage core admin notices
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 3.0.0
 * @version 7.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Manage core admin notices class.
 *
 * @since 3.0.0
 * @since 6.0.0 Removed the deprecated `LLMS_Admin_Notices_Core::check_staging()` method.
 */
class LLMS_Admin_Notices_Core {

	/**
	 * Init.
	 *
	 * @since 3.0.0
	 * @since 3.14.8 Add handler for removing dismissed notices.
	 * @since 7.1.0 Do not add a callback to remove sidebar notice on `switch_theme` anymore.
	 *
	 * @return void
	 */
	public static function init() {

		add_action( 'admin_head', array( __CLASS__, 'maybe_hide_notices' ), 1 );
		add_action( 'current_screen', array( __CLASS__, 'maybe_hide_notices' ), 999 );

		add_action( 'current_screen', array( __CLASS__, 'add_init_actions' ) );
	}

	/**
	 * Add actions on different hooks depending on the current screen.
	 *
	 * Adds later for LLMS Settings screens to accommodate for settings that are updated later in the load cycle.
	 *
	 * @since 3.0.0
	 * @since 4.12.0 Remove hook for deprecated `check_staging()` notice.
	 * @since 7.1.0 Do not add a callback to show the missing sidebar support anymore.
	 * @since 7.7.0 Add notice for media protection on certain hosting servers.
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
		add_action( $action, array( __CLASS__, 'media_protection' ), $priority );
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
	 * Check for Nginx and output a notice about media protection.
	 *
	 * @since 7.7.0
	 *
	 * @return void
	 */
	public static function media_protection() {
		$id = 'using-nginx';
		if (
			apply_filters(
				'llms_admin_notice_using_nginx',
				( ! empty( $GLOBALS['is_nginx'] && $GLOBALS['is_nginx'] ) )
				||
				( function_exists( 'is_wpe' ) && is_wpe() )
			) ) {
			$html = sprintf(
				/* translators: 1. opening link tag; 2. closing link tag */
				__( 'For the best protection for your media files, you should use this doc to add this %1$sNGINX redirect rule%2$s.', 'lifterlms' ),
				'<a href="https://lifterlms.com/docs/protected-media-files-on-nginx/" target="_blank">',
				'</a>'
			);
			$html .= '<br><br>' . __( 'If you have already reviewed these instructions you may dismiss this notice.', 'lifterlms' );

			LLMS_Admin_Notices::add_notice(
				$id,
				$html,
				array(
					'type'             => 'warning',
					'dismiss_for_days' => 10000,
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

	/**
	 * Check theme support for LifterLMS Sidebars.
	 *
	 * @since 3.0.0
	 * @since 3.7.4 Unknown.
	 * @since 4.5.0 Use strict comparison for `in_array()`.
	 * @deprecated 7.1.0
	 *
	 * @return void
	 */
	public static function sidebar_support() {

		_deprecated_function( __METHOD__, '7.1.0' );

		$theme = wp_get_theme();

		$id = 'sidebars';

		if ( ! current_theme_supports( 'lifterlms-sidebars' ) && ! in_array( $theme->get_template(), llms_get_core_supported_themes(), true ) ) {

			$msg = sprintf(
				__( '<strong>The current theme, %1$s, does not declare support for LifterLMS Sidebars.</strong> Course and Lesson sidebars may not work as expected. Please see our %2$sintegration guide%3$s or check out our %4$sLaunchPad%5$s theme which is designed specifically for use with LifterLMS.', 'lifterlms' ),
				$theme->get( 'Name' ),
				'<a href="https://lifterlms.com/docs/lifterlms-sidebar-support/?utm_source=notice&utm_medium=product&utm_content=sidebarsupport&utm_campaign=lifterlmsplugin" target="_blank">',
				'</a>',
				'<a href="https://lifterlms.com/product/launchpad/?utm_source=notice&utm_medium=product&utm_content=launchpad&utm_campaign=lifterlmsplugin" target="_blank">',
				'</a>'
			);

			LLMS_Admin_Notices::add_notice(
				$id,
				$msg,
				array(
					'dismissible'      => true,
					'dismiss_for_days' => 730, // @TODO: there should be a "forever" setting here.
					'remindable'       => false,
					'type'             => 'warning',
				)
			);

		} elseif ( LLMS_Admin_Notices::has_notice( $id ) ) {

			LLMS_Admin_Notices::delete_notice( $id );

		}
	}

	/**
	 * Removes the current sidebar notice (if present) and clears notice delay transients.
	 *
	 * Called when theme is switched.
	 *
	 * @since 3.14.7
	 * @deprecated 7.1.0
	 *
	 * @return void
	 */
	public static function clear_sidebar_notice() {

		_deprecated_function( __METHOD__, '7.1.0' );

		if ( LLMS_Admin_Notices::has_notice( 'sidebars' ) ) {
			LLMS_Admin_Notices::delete_notice( 'sidebars' );
		} else {
			delete_transient( 'llms_admin_notice_sidebars_delay' );
		}
	}
}

LLMS_Admin_Notices_Core::init();
