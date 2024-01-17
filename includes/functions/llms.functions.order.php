<?php
/**
 * Functions for LifterLMS Orders.
 *
 * @package LifterLMS/Functions
 *
 * @since 3.29.0
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Determine if a gateway can be used for a given LLMS_Access_Plan.
 *
 * @since 3.29.0
 * @since 7.0.0 Updated to utilize llms_can_gateway_be_used_for_plan_or_order().
 *
 * @param string           $gateway_id LLMS_Payment_Gateway ID.
 * @param LLMS_Access_Plan $plan       The access plan.
 * @return WP_Error|bool WP_Error on error, true on success.
 */
function llms_can_gateway_be_used_for_plan( $gateway_id, $plan ) {

	$can_be_used = llms_can_gateway_be_used_for_plan_or_order( $gateway_id, $plan, true, ( 'manual' !== $gateway_id ) );

	/**
	 * Filters whether or not a gateway can be used for a given access plan.
	 *
	 * @since 3.29.0
	 * @since 7.0.0 The filter now runs on all possible return values instead of running only when the gateway can be used.
	 *
	 * @param boolean|WP_Error $can_be_used Whether or not the gateway can be used for the plan. This value will be `true`
	 *                                      when the gateway can be used an an error object when it cannot.
	 * @param string           $gateway_id  The LLMS_Payment_Gateway ID.
	 * @param LLMS_Access_plan $plan        The access plan object.
	 */
	return apply_filters( 'llms_can_gateway_be_used_for_plan', $can_be_used, $gateway_id, $plan );

}

/**
 * Determines if a payment gateway can be used to process transactions for an LLMS_Order or an LLMS_Access_Plan.
 *
 *   + The plan/order must exist
 *   + The gateway must exist.
 *   + The gateway must be enabled unless `$enabled_only` is `false`.
 *   + The gateway must support the order/plan's type (recurring or single).
 *
 * @since 7.0.0
 * @since 7.5.0 Added check on whether a gateway can process a plan.
 *
 * @param string                          $gateway_id    Payment gateway ID.
 * @param LLMS_Order|LLMS_Access_Plan|int $plan_or_order The `WP_Post` id of a plan or order, a plan object, or an order object.
 * @param boolean                         $wp_err        Determines the return type when the gateway cannot be used.
 * @param boolean                         $enabled_only  If `true` requires the specified gateway to be enabled for use. This property
 *                                                       exists to ensure the manual payment gateway can be used to record free transactions
 *                                                       regardless of the gateway's status.
 * @return boolean|WP_Error Returns `true` if the gateway can be used. If the gateway cannot be used, returns `false` if `$wp_error` is
 *                          `false` and a `WP_Error` if `$wp_err` is `true`.
 */
function llms_can_gateway_be_used_for_plan_or_order( $gateway_id, $plan_or_order, $wp_err = false, $enabled_only = true ) {

	$can_use = true;

	$plan_or_order = is_numeric( $plan_or_order ) ? llms_get_post( $plan_or_order ) : $plan_or_order;
	$err_data      = compact( 'gateway_id', 'plan_or_order' );
	$order         = is_a( $plan_or_order, 'LLMS_Order' ) ? $plan_or_order : null;
	$plan          = ! $order && is_a( $plan_or_order, 'LLMS_Access_Plan' ) ? $plan_or_order : null;

	if ( is_null( $order ) && is_null( $plan ) ) {
		$can_use = new WP_Error( 'post-invalid', __( 'A valid order or access plan must be supplied.', 'lifterlms' ), $err_data );
	} else {

		$gateway = llms()->payment_gateways()->get_gateway_by_id( $gateway_id );
		if ( ! $gateway ) {
			$can_use = new WP_Error( 'gateway-invalid', __( 'The selected payment gateway is not valid.', 'lifterlms' ), $err_data );
		} elseif ( $enabled_only && ! $gateway->is_enabled() ) {
			$can_use = new WP_Error( 'gateway-disabled', __( 'The selected payment gateway is not available.', 'lifterlms' ), $err_data );
		} elseif ( ! $gateway->can_process_access_plan( $plan, $order ) ) {
			// Check whether the gateway can process the plan or the order's plan (which is the plan at the moment of the order's creation).
			$can_use = new WP_Error( 'gateway-support-plan', __( 'The selected payment gateway is not available for the given plan.', 'lifterlms' ), $err_data );
		} elseif ( $plan_or_order->is_recurring() && ! $gateway->supports( 'recurring_payments' ) ) {
			$can_use = new WP_Error( 'gateway-support-recurring', __( 'The selected payment gateway does not support recurring payments.', 'lifterlms' ), $err_data );
		} elseif ( ! $plan_or_order->is_recurring() && ! $gateway->supports( 'single_payments' ) ) {
			$can_use = new WP_Error( 'gateway-support-single', __( 'The selected payment gateway does not support one-time payments.', 'lifterlms' ), $err_data );
		}
	}

	/**
	 * Filters whether or not a gateway can be used for a given plan or order.
	 *
	 * @since 7.0.0
	 *
	 * @param boolean|WP_Error $can_be_used Whether or not the gateway can be used for the plan. This value will be `true`
	 *                                      when the gateway can be used an an error object when it cannot.
	 * @param string           $gateway_id  The LLMS_Payment_Gateway ID.
	 * @param LLMS_Access_plan $plan        The access plan object.
	 */
	$can_use = apply_filters( 'llms_can_gateway_be_used_for_plan_or_order', $can_use, $gateway_id, $plan_or_order );

	return is_wp_error( $can_use ) && ! $wp_err ? false : $can_use;

}

/**
 * Retrieve an LLMS Order ID by the associated order_key.
 *
 * @since 3.0.0
 * @since 3.30.1 Return `null` instead of `false` when requesting an `LLMS_Order` return and no order could be found.
 * @since 3.30.1 Return a real `int` (instead of a numeric string).
 *
 * @param string $key    The order key.
 * @param string $return Type of return, "order" for an instance of the LLMS_Order or "id" to return only the order ID.
 * @return mixed `null` when no order found, LLMS_Order when `$return` = 'order', or the WP_Post ID as an `int`.
 */
function llms_get_order_by_key( $key, $return = 'order' ) {

	global $wpdb;

	$id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '_llms_order_key' AND meta_value = %s", $key ) ); // no-cache ok.

	if ( $id && 'order' === $return ) {
		return new LLMS_Order( $id );
	}

	// Return an int not a numeric string.
	return $id ? absint( $id ) : $id;

}

/**
 * Get the human readable status for a LifterLMS status.
 *
 * @since 3.0.0
 * @since 3.6.0 Unknown.
 *
 * @param string $status LifterLMS Order Status.
 * @return string
 */
function llms_get_order_status_name( $status ) {
	$statuses = llms_get_order_statuses();
	if ( is_array( $statuses ) && isset( $statuses[ $status ] ) ) {
		$status = $statuses[ $status ];
	}
	return apply_filters( 'lifterlms_get_order_status_name', $status );
}

/**
 * Retrieve an array of registered and available LifterLMS Order Post Statuses.
 *
 * @since 3.0.0
 * @since 3.19.0 Unknown.
 *
 * @param string $order_type Filter statuses which are specific to the supplied order type, defaults to any statuses.
 * @return array[]
 */
function llms_get_order_statuses( $order_type = 'any' ) {

	$statuses = wp_list_pluck( LLMS_Post_Types::get_order_statuses(), 'label' );

	// Remove types depending on order type.
	switch ( $order_type ) {
		case 'recurring':
			unset( $statuses['llms-completed'] );
			break;

		case 'single':
			unset( $statuses['llms-active'] );
			unset( $statuses['llms-expired'] );
			unset( $statuses['llms-on-hold'] );
			unset( $statuses['llms-pending-cancel'] );
			break;
	}

	/**
	 * Filters the order statuses.
	 *
	 * @since Unknown.
	 *
	 * @param array[] $statuses   Array of order post status arrays.
	 * @param string  $order_type The type of the order.
	 */
	return apply_filters( 'llms_get_order_statuses', $statuses, $order_type );

}

/**
 * Get the possible statuses of a given order.
 *
 * @since 5.4.0
 *
 * @param LLMS_Order $order The LLMS_Order instance.
 * @return array[]
 */
function llms_get_possible_order_statuses( $order ) {

	$is_recurring = $order->is_recurring();
	$statuses     = llms_get_order_statuses( $is_recurring ? 'recurring' : 'single' );

	// Limit the possible status for recurring orders whose product ID doesn't exist anymore.
	if ( $is_recurring && ! llms_get_post( $order->get( 'product_id' ) ) ) {
		unset( $statuses['llms-active'] );
		unset( $statuses['llms-on-hold'] );
		unset( $statuses['llms-pending'] );
		unset( $statuses['llms-pending-cancel'] );
	}

	return $statuses;

}

/**
 * Locates an order by email address and access plan ID.
 *
 * Used during AJAX checkout order creation when users are not created until the gateway confirms success.
 *
 * Ensures that only a single pending order for a given plan and email address will exist at any given time.
 *
 * @since 7.0.0
 *
 * @param string $email   An email address.
 * @param int    $plan_id Access plan WP_Post ID.
 * @return null|int Returns the post id if found, otherwise returns `null`.
 */
function llms_locate_order_for_email_and_plan( $email, $plan_id ) {

	$query = new WP_Query(
		array(
			'post_type'      => 'llms_order',
			'post_status'    => 'llms-pending',
			'fields'         => 'ids',
			'posts_per_page' => 1,
			'no_found_rows'  => true,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'   => '_llms_billing_email',
					'value' => $email,
				),
				array(
					'key'   => '_llms_plan_id',
					'value' => $plan_id,
				),
			),
		)
	);

	return $query->posts[0] ?? null;

}

/**
 * Find an existing order for a given plan by a given user.
 *
 * @since 3.30.1
 *
 * @param int $user_id The WP_User ID.
 * @param int $plan_id The Access Plan post ID.
 * @return mixed null if no order found, WP_Post ID as an int if found
 */
function llms_locate_order_for_user_and_plan( $user_id, $plan_id ) {

	global $wpdb;

	// Query.
	$id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->prefix}posts AS p
			 JOIN {$wpdb->prefix}postmeta AS pm_user ON pm_user.post_id = p.ID AND pm_user.meta_key = '_llms_user_id'
			 JOIN {$wpdb->prefix}postmeta AS pm_plan ON pm_plan.post_id = p.ID AND pm_plan.meta_key = '_llms_plan_id'
			 WHERE p.post_type = 'llms_order'
			   AND pm_user.meta_value = %d
			   AND pm_plan.meta_value = %d
			;",
			$user_id,
			$plan_id
		)
	); // db-cache ok.

	// Return an int not a numeric string.
	return $id ? absint( $id ) : $id;

}

/**
 * Setup a pending order which can be passed to an LLMS_Payment_Gateway for processing.
 *
 * @since 3.29.0
 * @since 4.2.0 Prevent double displaying a notice to already enrolled students in the product being purchased.
 * @since 4.21.1 Sanitize coupon code prior to outputting it in error messages.
 * @since 5.0.0 Use `llms_update_user()` instead of deprecated `LLMS_Person_Handler::update()`.
 *
 * @param array $data {
 *     Data used to create a pending order.
 *
 *     @type int    plan_id         (Required) LLMS_Access_Plan ID.
 *     @type array  customer        (Required). Array of customer information formatted to be passed to `LLMS_Person_Handler::update()` or `llms_register_user()`
 *     @type string agree_to_terms  (Required if `llms_are_terms_and_conditions_required()` are required) If terms & conditions are required this should be "yes" for agreement.
 *     @type string payment_gateway (Optional) ID of a registered LLMS_Payment_Gateway which will be used to process the order.
 *     @type string coupon_code     (Optional) Coupon code to be applied to the order.
 * }
 * @return array
 */
function llms_setup_pending_order( $data = array() ) {

	/**
	 * Filters the order data before setting up the pending order.
	 *
	 * @since Unknown.
	 *
	 * @param array $data Array of input data from a checkout form.
	 */
	$data = apply_filters( 'llms_before_setup_pending_order', $data );

	// Request keys that can be submitted with or without the `llms_` prefix.
	$keys = array(
		'llms_agree_to_terms',
		'llms_coupon_code',
		'llms_plan_id',
	);
	foreach ( $keys as $key ) {
		if ( isset( $data[ $key ] ) ) {
			$data[ str_replace( 'llms_', '', $key ) ] = $data[ $key ];
		}
	}

	$err = new WP_Error();

	// Check t & c if configured.
	if ( llms_are_terms_and_conditions_required() ) {
		if ( ! isset( $data['agree_to_terms'] ) || ! llms_parse_bool( $data['agree_to_terms'] ) ) {
			$err->add( 'terms-violation', sprintf( __( 'You must agree to the %s to continue.', 'lifterlms' ), get_the_title( get_option( 'lifterlms_terms_page_id' ) ) ) );
			return $err;
		}
	}

	// We must have a plan_id to proceed.
	if ( empty( $data['plan_id'] ) ) {
		$err->add( 'missing-plan-id', __( 'Missing an Access Plan ID.', 'lifterlms' ) );
		return $err;
	}

	// Validate the plan is a real plan.
	$plan = llms_get_post( absint( $data['plan_id'] ) );
	if ( ! $plan || 'llms_access_plan' !== $plan->get( 'type' ) ) {
		$err->add( 'invalid-plan-id', __( 'Invalid Access Plan ID.', 'lifterlms' ) );
		return $err;
	}

	// Used later.
	$coupon_id = null;
	$coupon    = false;

	// If a coupon is being used, validate it.
	if ( ! empty( $data['coupon_code'] ) ) {

		$data['coupon_code'] = sanitize_text_field( $data['coupon_code'] );

		$coupon_id = llms_find_coupon( $data['coupon_code'] );

		// Coupon couldn't be found.
		if ( ! $coupon_id ) {
			$err->add( 'coupon-not-found', sprintf( __( 'Coupon code "%s" not found.', 'lifterlms' ), $data['coupon_code'] ) );
			return $err;
		}

		// Coupon is real, make sure it's valid for the current plan.
		$coupon = llms_get_post( $coupon_id );
		$valid  = $coupon->is_valid( $data['plan_id'] );

		// If the coupon has a validation error, return an error message.
		if ( is_wp_error( $valid ) ) {
			$err->add( 'invalid-coupon', $valid->get_error_message() );
			return $err;
		}
	}

	// If payment is required, verify we have a gateway.
	if ( $plan->requires_payment( $coupon_id ) && empty( $data['payment_gateway'] ) ) {
		$err->add( 'missing-gateway-id', __( 'No payment method selected.', 'lifterlms' ) );
		return $err;
	}

	$gateway_id    = empty( $data['payment_gateway'] ) ? 'manual' : $data['payment_gateway'];
	$gateway_error = llms_can_gateway_be_used_for_plan( $gateway_id, $plan );
	if ( is_wp_error( $gateway_error ) ) {
		return $gateway_error;
	}

	if ( empty( $data['customer'] ) ) {
		$err->add( 'missing-customer', __( 'Missing customer information.', 'lifterlms' ) );
		return $err;
	}

	// Update the customer.
	if ( ! empty( $data['customer']['user_id'] ) ) {
		$person_id = llms_update_user( $data['customer'], 'checkout', compact( 'plan' ) );
	} else {
		$person_id = llms_register_user( $data['customer'], 'checkout', true, compact( 'plan' ) );
	}

	// Validation or registration issues.
	if ( is_wp_error( $person_id ) ) {
		return $person_id;
	}

	// This will likely never actually happen unless there's something very strange afoot.
	if ( ! is_numeric( $person_id ) ) {

		$err->add( 'account-creation', __( 'An unknown error occurred when attempting to create an account, please try again.', 'lifterlms' ) );
		return $err;

	}

	// Ensure the new user isn't enrolled in the product being purchased.
	if ( llms_is_user_enrolled( $person_id, $plan->get( 'product_id' ) ) ) {

		$product = $plan->get_product();
		$err->add(
			'already-enrolled',
			sprintf(
				// Translators: %2$s = The product type (course/membership); %1$s = product permalink.
				__( 'You already have access to this %2$s! Visit your dashboard <a href="%1$s">here.</a>', 'lifterlms' ),
				llms_get_page_url( 'myaccount' ),
				$product->get_post_type_label()
			)
		);
		// Prevent double displaying a notice to already enrolled students in the product being purchased.
		add_filter( 'llms_display_checkout_form_enrolled_students_notice', '__return_false' );

		return $err;
	}

	$person  = llms_get_student( $person_id );
	$gateway = llms()->payment_gateways()->get_gateway_by_id( $gateway_id );

	/**
	 * Filter the return of pending order setup data.
	 *
	 * @since 3.30.1
	 *
	 * @param array $setup {
	 *     Data used to create the pending order.
	 *
	 *     @type LLMS_Student $person Student object.
	 *     @type LLMS_Access_Plan $plan Access plan object.
	 *     @type LLMS_Payment_Gateway $gateway Instance of the selected gateway.
	 *     @type LLMS_Coupon|false $coupon Coupon object or false if none used.
	 * }
	 * @param array $data Array of input data from a checkout form.
	 */
	return apply_filters( 'llms_after_setup_pending_order', compact( 'person', 'plan', 'gateway', 'coupon' ), $data );

}
