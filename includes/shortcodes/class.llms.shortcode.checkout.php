<?php
/**
 * LifterLMS Checkout Page Shortcode
 *
 * Controls functionality associated with shortcode [llms_checkout].
 *
 * @package LifterLMS/Shortcodes
 *
 * @since 1.0.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Checkout Page Shortcode
 *
 * Controls functionality associated with shortcode [llms_checkout].
 *
 * @package LifterLMS/Shortcodes
 *
 * @since 1.0.0
 * @since 3.30.1 Added check via llms_locate_order_for_user_and_plan() to automatically resume an existing pending order for logged in users if one exists.
 * @since 3.33.0 Checkout form not displayed to users already enrolled in the product being purchased, a notice informing them of that is displayed instead.
 * @since 3.35.0 Sanitize input data.
 */
class LLMS_Shortcode_Checkout {

	/**
	 * Current User ID.
	 *
	 * @var int
	 */
	public static $uid;

	/**
	 * Renders the checkout template
	 *
	 * @since 1.0.0
	 * @since 3.33.0 Do not display the checkout form but a notice to a logged in user enrolled in the product being purchased.
	 *
	 * @param array $atts Shortcode attributes array.
	 * @return void
	 */
	private static function checkout( $atts ) {

		// if there are membership restrictions, check the user is in at least one membership
		// this is to combat CHEATERS
		if ( $atts['plan']->has_availability_restrictions() ) {
			$access = false;
			foreach ( $atts['plan']->get_array( 'availability_restrictions' ) as $mid ) {

				// once we find a membership, exit
				if ( llms_is_user_enrolled( self::$uid, $mid ) ) {
					$access = true;
					break;
				}
			}
			if ( ! $access ) {
				llms_print_notice( 'You must be a member in order to purchase this access plan.', 'error' );
				return;
			}
		}

		if ( self::$uid ) {
			// ensure the user isn't enrolled in the product being purchased.
			if ( isset( $atts['product'] ) && llms_is_user_enrolled( self::$uid, $atts['product']->get( 'id' ) ) ) {

				llms_print_notice(
					sprintf(
						// Translators: 2$s = The product type (course/membership); %1$s = product permalink.
						__( 'You already have access to this %2$s! Visit your dashboard <a href="%1$s">here.</a>', 'lifterlms' ),
						llms_get_page_url( 'myaccount' ),
						$atts['product']->get_post_type_label()
					),
					'notice'
				);
				return;
			}

			$user = get_userdata( self::$uid );
			llms_print_notice( sprintf( __( 'You are currently logged in as <em>%1$s</em>. <a href="%2$s">Click here to logout</a>', 'lifterlms' ), $user->user_email, wp_logout_url( $atts['plan']->get_checkout_url() ) ), 'notice' );
		} else {
			llms_get_login_form( sprintf( __( 'Already have an account? <a href="%s">Click here to login</a>', 'lifterlms' ), '#llms-show-login' ), $atts['plan']->get_checkout_url() );
		}

		llms_get_template( 'checkout/form-checkout.php', $atts );

	}

	/**
	 * Renders the confirm payment checkout template
	 *
	 * @since 1.0.0
	 * @version 3.0.0
	 *
	 * @param array $atts shortcode attributes.
	 * @return void
	 */
	private static function confirm_payment( $atts ) {

		llms_get_template( 'checkout/form-confirm-payment.php', $atts );

	}

	/**
	 * Output error messages when they're encountered
	 *
	 * @since 3.0.0
	 * @version 3.0.0
	 *
	 * @return   void
	 */
	private static function error( $message ) {

		echo apply_filters( 'llms_checkout_error_output', $message );

	}

	/**
	 * Retrieve the shortcode content
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param array $atts shortcode attributes.
	 * @return string
	 */
	public static function get( $atts ) {

		return LLMS_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );

	}

	/**
	 * Gather a bunch of information and output the actual content for the shortcode
	 *
	 * @since 1.0.0
	 * @since 3.30.1 Added check via llms_locate_order_for_user_and_plan() to automatically resume an existing pending order for logged in users if one exists.
	 * @since 3.35.0 Sanitize input data.
	 *
	 * @param array $atts shortcode atts from originating shortcode
	 * @return void
	 */
	public static function output( $atts ) {

		global $wp;

		$atts = $atts ? $atts : array();

		$atts['cols'] = isset( $atts['cols'] ) ? $atts['cols'] : 2;

		self::$uid = get_current_user_id();

		$atts['gateways']         = LLMS()->payment_gateways()->get_enabled_payment_gateways();
		$atts['selected_gateway'] = LLMS()->payment_gateways()->get_default_gateway();

		$atts['order_key'] = '';

		$atts['field_data'] = array();
		if ( isset( $_POST ) && isset( $_POST['action'] ) && 'create_pending_order' === $_POST['action'] ) { // phpcs:disable WordPress.Security.NonceVerification.Missing
			$atts['field_data'] = wp_unslash( $_POST ); // phpcs:disable WordPress.Security.NonceVerification.Missing
		} elseif ( self::$uid ) {
			$atts['field_data'] = get_current_user_id();
		}

		echo '<div class="llms-checkout-wrapper">';

		// allow gateways to throw errors before outputting anything else
		// useful if you need to check for extra session or query string data
		$err = apply_filters( 'lifterlms_pre_checkout_error', false );
		if ( $err ) {
			return self::error( $err );
		}

		llms_print_notices();

		// purchase step 1
		if ( isset( $_GET['plan'] ) && is_numeric( $_GET['plan'] ) ) {

			$plan_id = llms_filter_input( INPUT_GET, 'plan', FILTER_SANITIZE_NUMBER_INT );

			// Only retrieve if plan is a llms_access_plan and is published
			if ( 0 === strcmp( get_post_status( $plan_id ), 'publish' ) && 0 === strcmp( get_post_type( $plan_id ), 'llms_access_plan' ) ) {

				$coupon = LLMS()->session->get( 'llms_coupon' );

				if ( isset( $coupon['coupon_id'] ) && isset( $coupon['plan_id'] ) ) {
					if ( $coupon['plan_id'] == $_GET['plan'] ) {
						$atts['coupon'] = new LLMS_Coupon( $coupon['coupon_id'] );
					} else {
						LLMS()->session->set( 'llms_coupon', false );
						$atts['coupon'] = false;
					}
				} else {
					$atts['coupon'] = false;
				}

				// Use posted order key to resume a pending order.
				if ( isset( $_POST['llms_order_key'] ) ) { // phpcs:disable WordPress.Security.NonceVerification.Missing
					$atts['order_key'] = llms_filter_input( INPUT_POST, 'llms_order_key', FILTER_SANITIZE_STRING );

					// Attempt to located a pending order.
				} elseif ( self::$uid ) {
					$pending_order = llms_locate_order_for_user_and_plan( self::$uid, $plan_id );
					if ( $pending_order ) {
						$order             = llms_get_post( $pending_order );
						$atts['order_key'] = ( 'llms-pending' === $order->get( 'status' ) ) ? $order->get( 'order_key' ) : '';
					}
				}

				$atts['plan']    = new LLMS_Access_Plan( $plan_id );
				$atts['product'] = $atts['plan']->get_product();

				self::checkout( $atts );

			} else {

				self::error( __( 'Invalid access plan.', 'lifterlms' ) );

			}
		} elseif ( isset( $wp->query_vars['confirm-payment'] ) ) {

			if ( ! isset( $_GET['order'] ) ) {

				return self::error( __( 'Could not locate an order to confirm.', 'lifterlms' ) );

			}

			$order           = llms_get_order_by_key( llms_filter_input( INPUT_GET, 'order', FILTER_SANITIZE_STRING ) );
			$atts['plan']    = new LLMS_Access_Plan( $order->get( 'plan_id' ) );
			$atts['product'] = $atts['plan']->get_product();

			if ( $order->get( 'coupon_id' ) ) {
				$atts['coupon'] = new LLMS_Coupon( $order->get( 'coupon_id' ) );
			} else {
				$atts['coupon'] = false;
			}

			$atts['selected_gateway'] = LLMS()->payment_gateways()->get_gateway_by_id( $order->get( 'payment_gateway' ) );

			self::confirm_payment( $atts );

		} else {

			return self::error( sprintf( __( 'Your cart is currently empty. Click <a href="%s">here</a> to get started.', 'lifterlms' ), llms_get_page_url( 'courses' ) ) );

		}// End if().

		echo '</div><!-- .llms-checkout-wrapper -->';

	}

}
