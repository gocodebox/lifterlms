<?php
/**
 * Checkout Shortcode
 * Sets functionality associated with shortcode [llms_checkout]
 * @since    1.0.0
 * @version  3.7.7
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Shortcode_Checkout {

	public static $uid;

	/**
	 * Renders the checkout template
	 * @param    array    $atts  shortocde attributes
	 * @return   void
	 * @since    1.0.0
	 * @version  3.0.0
	 */
	private static function checkout( $atts ) {

		// if theres membership restrictions, check the user is in at least one membership
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
			$user = get_userdata( self::$uid );
			llms_print_notice( sprintf( __( 'You are currently logged in as <em>%1$s</em>. <a href="%2$s">Click here to logout</a>', 'lifterlms' ), $user->user_email, wp_logout_url( $atts['plan']->get_checkout_url() ) ), 'notice' );
		} else {
			llms_get_login_form( sprintf( __( 'Already have an account? <a href="%s">Click here to login</a>', 'lifterlms' ), '#llms-show-login' ), $atts['plan']->get_checkout_url() );
		}

		llms_get_template( 'checkout/form-checkout.php', $atts );

	}

	/**
	 * Renders the confirm payment checkout template
	 * @param    array    $atts  shortocde attributes
	 * @return   void
	 * @since    1.0.0
	 * @version  3.0.0
	 */
	private static function confirm_payment( $atts ) {

		llms_get_template( 'checkout/form-confirm-payment.php', $atts );

	}

	/**
	 * Output error messages when they're encountered
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 *
	 */
	private static function error( $message ) {

		echo apply_filters( 'llms_checkout_error_output', $message );

	}

	/**
	 * Retrieve the shortcode content
	 * @param    array     $atts  array of shortcode attributes
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public static function get( $atts ) {

		return LLMS_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );

	}

	/**
	 * Gather a bunch of information and output the actual content for the shortcode
	 * @param    array   $atts  shortcode atts from originating shortcode
	 * @return   void
	 * @since    1.0.0
	 * @version  3.7.7
	 */
	public static function output( $atts ) {

		global $wp;

		$atts = $atts ? $atts : array();

		$atts['cols'] = isset( $atts['cols'] ) ? $atts['cols'] : 2;

		self::$uid = get_current_user_id();

		$atts['gateways'] = LLMS()->payment_gateways()->get_enabled_payment_gateways();
		$atts['selected_gateway'] = LLMS()->payment_gateways()->get_default_gateway();

		$atts['order_key'] = '';

		$atts['field_data'] = array();
		if ( isset( $_POST ) && isset( $_POST['action'] ) && 'create_pending_order' === $_POST['action'] ) {
			$atts['field_data'] = $_POST;
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

			// Only retrieve if plan is a llms_access_plan and is published
			if ( 0 === strcmp( get_post_status( $_GET['plan'] ), 'publish' ) && 0 === strcmp( get_post_type( $_GET['plan'] ), 'llms_access_plan' ) ) {

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

				if ( isset( $_POST['llms_order_key'] ) ) {
					$atts['order_key'] = sanitize_text_field( $_POST['llms_order_key'] );
				}

				$atts['plan'] = new LLMS_Access_Plan( $_GET['plan'] );
				$atts['product'] = $atts['plan']->get_product();

				self::checkout( $atts );

			} else {

				self::error( __( 'Invalid access plan.', 'lifterlms' ) );

			}
		} elseif ( isset( $wp->query_vars['confirm-payment'] ) ) {

			// $atts['plan'] = new LLMS_Access_Plan( $_GET['plan'] );

			if ( ! isset( $_GET['order'] ) ) {

				return self::error( __( 'Could not locate an order to confirm.', 'lifterlms' ) );

			}

			$order = llms_get_order_by_key( $_GET['order'] );
			$atts['plan'] = new LLMS_Access_Plan( $order->get( 'plan_id' ) );
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
