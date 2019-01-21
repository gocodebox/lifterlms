<?php
/**
 * Functions for LifterLMS Orders
 * @since    3.27.0
 * @version  3.27.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * [llms_validate_order_data description]
 * @param    array      $data  [description]
 *                             Required:
 *                             		plan_id (int) WP Post ID of the Access plan being purchased
 *                                  customer (array)  Array of customer information
 *
 *                             Conditionally Required:
 *                             		agree_to_terms (string) [yes|no] If terms & conditions are required this should be "yes" for agreement
 *                               	payment_gateway (string)  ID of the payment gateway to process payments through
 *
 * 							   Optional:
 *                             		coupon_code (string) Coupon Code to be applied to the order
 * @return   [type]
 * @since    3.27.0
 * @version  3.27.0
 */
function llms_setup_pending_order( $data = array() ) {

	/**
	 * @filter llms_before_setup_pending_order
	 */
	$data = apply_filters( 'llms_before_setup_pending_order', $data );

	$err = new WP_Error();

	// check t & c if configured
	if ( llms_are_terms_and_conditions_required() ) {
		if ( ! isset( $data['agree_to_terms'] ) || ! llms_parse_bool( $data['agree_to_terms'] ) ) {
			$err->add( 'terms-violation', sprintf( __( 'You must agree to the %s to continue.', 'lifterlms' ), get_the_title( get_option( 'lifterlms_terms_page_id' ) ) ) );
			return $err;
		}
	}

	// we must have a plan_id to proceed
	if ( empty( $data['plan_id'] ) ) {
		$err->add( 'missing-plan-id', __( 'Missing an Access Plan ID.', 'lifterlms' ) );
		return $err;
	}

	// validate the plan is a real plan
	$plan = llms_get_post( absint( $data['plan_id'] ) );
	if ( ! $plan || 'llms_access_plan' !== $plan->get( 'type' ) ) {
		$err->add( 'invalid-plan-id', __( 'Invalid Access Plan ID.', 'lifterlms' ) );
		return $err;
	}

	// used later
	$coupon_id = null;
	$coupon = false;

	// if a coupon is being used, validate it
	if ( ! empty( $data['coupon_code'] ) ) {

		$coupon_id = llms_find_coupon( $data['coupon_code'] );

		// coupon couldn't be found
		if ( ! $coupon_id ) {
			$err->add( 'coupon-not-found', sprintf( __( 'Coupon code "%s" not found.', 'lifterlms' ), $data['coupon_code'] ) );
			return $err;
		}

		// coupon is real, make sure it's valid for the current plan
		$coupon = llms_get_post( $coupon_id );
		$valid = $coupon->is_valid( $data['plan_id'] );

		// if the coupon has a validation error, return an error message
		if ( is_wp_error( $valid ) ) {
			$err->add( 'invalid-coupon', $valid->get_error_message() );
			return $err;
		}
	}

	// if payment is required, verify we have a gateway
	if ( $plan->requires_payment( $coupon_id ) && empty( $data['payment_gateway'] ) ) {
		$err->add( 'missing-gateway-id', __( 'No payment method selected.', 'lifterlms' ) );
		return $err;
	}

	$gateway_id = empty( $data['payment_gateway'] ) ? 'manual' : $data['payment_gateway'];
	$gateway_error = llms_can_gateway_be_used_for_plan( $gateway_id, $plan );
	if ( is_wp_error( $gateway_error ) ) {
		return $gateway_error;
	}

	if ( empty( $data['customer'] ) ) {
		$err->add( 'missing-customer', __( 'Missing customer information.', 'lifterlms' ) );
		return $err;
	}

	// update the customer
	if ( ! empty( $data['customer']['user_id'] ) ) {
		$person_id = LLMS_Person_Handler::update( $data['customer'], 'checkout' );
	} else {
		$person_id = llms_register_user( $data['customer'], 'checkout', true );
	}

	// validation or registration issues
	if ( is_wp_error( $person_id ) ) {
		return $person_id;
	}

	// this will likely never actually happen unless there's something very strange afoot
	if ( ! is_numeric( $person_id ) ) {

		$err->add( 'account-creation', __( 'An unknown error occurred when attempting to create an account, please try again.', 'lifterlms' ) );
		return $err;

	}

	// ensure the new user isn't enrolled in the product being purchased
	if ( llms_is_user_enrolled( $person_id, $plan->get( 'product_id' ) ) ) {

		$product = $plan->get_product();
		$err->add( 'already-enrolled', sprintf(
			__( 'You already have access to this %2$s! Visit your dashboard <a href="%s">here.</a>', 'lifterlms' ),
			llms_get_page_url( 'myaccount' ), $product->get_post_type_label()
		) );
		return $err;
	}

	$person = llms_get_student( $person_id );
	$gateway = LLMS()->payment_gateways()->get_gateway_by_id( $gateway_id );

	/**
	 * @filter llms_after_setup_pending_order
	 */
	return apply_filters( 'llms_after_setup_pending_order', compact( 'person', 'plan', 'gateway', 'coupon' ), $data );

}


function llms_can_gateway_be_used_for_plan( $gateway_id, $plan ) {

	$gateway = LLMS()->payment_gateways()->get_gateway_by_id( $gateway_id );
	$err = new WP_Error();

	// valid gateway
	if ( is_subclass_of( $gateway, 'LLMS_Payment_Gateway' ) ) {

		// gateway not enabled
		if ( 'manual' !== $gateway->get_id() && ! $gateway->is_enabled() ) {

			$err->add( 'gateway-error', __( 'The selected payment gateway is not currently enabled.', 'lifterlms' ) );
			return $err;

			// it's a recurring plan and the gateway doesn't support recurring
		} elseif ( $plan->is_recurring() && ! $gateway->supports( 'recurring_payments' ) ) {

			$err->add( 'gateway-error', sprintf( __( '%s does not support recurring payments and cannot process this transaction.', 'lifterlms' ), $gateway->get_title() ) );
			return $err;

			// not recurring and the gateway doesn't support single payments
		} elseif ( ! $plan->is_recurring() && ! $gateway->supports( 'single_payments' ) ) {

			$err->add( 'gateway-error', sprintf( __( '%s does not support single payments and cannot process this transaction.', 'lifterlms' ), $gateway->get_title() ) );
			return $err;

		}
	} else {

		$err->add( 'invalid-gateway', __( 'An invalid payment method was selected.', 'lifterlms' ) );
		return $err;

	}

	return true;

}
