<?php
/**
 * Manage core admin notices
 *
 * @since 3.0.0
 * @version  3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Notices_Core {

	/**
	 * Costructor
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'gateways' ) );

		// theme sidebar support
		add_action( 'switch_theme', array( $this, 'sidebar_support' ) );
		add_action( 'lifterlms_updated', array( $this, 'sidebar_support' ) );

	}

	/**
	 * Check for gateways and output gateway notice
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function gateways() {
		$id = 'no-gateways';
		if ( ! LLMS()->payment_gateways()->has_gateways( true ) ) {
			$html = __( 'No LifterLMS Payment Gateways are currently enabled. Students will only be able to enroll in courses or memberships with free access plans.', 'lifterlms' ) . '<br><br>';
			$html .= sprintf( __( 'For starters you can configure manual payments on the %sCheckout Settings tab%s. Be sure to check out all the available %sLifterLMS Payment Gateways%s and install one later so that you can start selling your courses and memberships.', 'lifterlms' ), '<a href="' . add_query_arg( array( 'page' => 'llms-settings', 'tab' => 'checkout' ), admin_url( 'admin.php' ) ) . '">', '</a>', '<a href="https://lifterlms.com/product-category/plugins/payment-gateways/" target="_blank">', '</a>' );
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
	 * Check theme support for LifterLMS Sidebars
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function sidebar_support() {

		$id = 'sidebars';

		if ( ! current_theme_supports( 'lifterlms-sidebars' ) && ! in_array( get_option( 'template' ), llms_get_core_supported_themes() ) ) {

			$msg = sprintf(
				__( '<strong>Your theme does not declare support for LifterLMS Sidebars.</strong> Please see our %sintegration guide%s or check out our %sLaunchPad%s theme which is designed specifically for use with LifterLMS', 'lifterlms' ),
				'<a href="https://lifterlms.com/docs/lifterlms-sidebar-support/?utm_source=notice&utm_medium=product&utm_content=sidebarsupport&utm_campaign=lifterlmsplugin" target="_blank">', '</a>',
				'<a href="https://lifterlms.com/product/launchpad/?utm_source=notice&utm_medium=product&utm_content=launchpad&utm_campaign=lifterlmsplugin" target="_blank">', '</a>'
			);

			LLMS_Admin_Notices::add_notice( $id, $msg, array(
				'dismissible' => true,
				'dismiss_for_days' => 365,
				'remindable' => false,
				'type' => 'warning',
			) );

		} elseif ( LLMS_Admin_Notices::has_notice( $id ) ) {

			LLMS_Admin_Notices::delete_notice( $id );

		}

	}

}

return new LLMS_Admin_Notices_Core();
