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

		add_action( 'admin_init', array( $this, 'gateway_notice' ) );
		add_action( 'lifterlms_hide_no-gateways__warning_notice', array( $this, 'gateway_transient' ) );

	}

	/**
	 * Check for gateways and output gateway notice
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function gateway_notice() {
		$id = 'no-gateways__warning';
		if ( ! LLMS()->payment_gateways()->has_gateways( true ) && ! get_transient( 'llms_no-gateways__warning_hidden' ) ) {
			$html = __( 'No LifterLMS Payment Gateways are currently enabled. Students will only be able to enroll in courses or memberships with free access plans.', 'lifterlms' ) . '<br><br>';
			$html .= sprintf( __( 'For starters you can configure manual payments on the %sCheckout Settings tab%s. Be sure to check out all the available %sLifterLMS Payment Gateways%s and install one later so that you can start selling your courses and memberships.', 'lifterlms' ), '<a href="' . add_query_arg( array( 'page' => 'llms-settings', 'tab' => 'checkout' ), admin_url( 'admin.php' ) ) . '">', '</a>', '<a href="https://lifterlms.com/product-category/plugins/payment-gateways/" target="_blank">', '</a>' );
			LLMS_Admin_Notices::add_notice( $id, $html );
		} elseif ( LLMS_Admin_Notices::has_notice( $id ) ) {
			LLMS_Admin_Notices::delete_notice( $id );
		}
	}

	/**
	 * When gateway notice is dismissed, don't display it again for at least 28 days
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function gateway_transient() {
		set_transient( 'llms_no-gateways__warning_hidden', 'yes', DAY_IN_SECONDS * 28 );
	}

}

return new LLMS_Admin_Notices_Core();
