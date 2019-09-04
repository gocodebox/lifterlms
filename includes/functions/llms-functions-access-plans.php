<?php
/**
 * Functions for LifterLMS Access Plans
 *
 * @package LifterLMS/Functions/Access_Plans
 *
 * @since 3.29.0
 * @version 3.30.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Create or update an access plan.
 *
 * If $props has an "ID" parameter, that plan will be updated, otherwise a new plan will be created.
 *
 * @see LLMS_Access_Plan
 *
 * @since 3.29.0
 * @since 3.30.0 Added checkout redirect options.
 * @since 3.30.3 Fixed spelling errors.
 *
 * @param array $props {
 *     An array of of properties that make up the plan to create or update.
 *
 *     @type int $product_id Required) WP Post ID of the related LifterLMS Product (course or membership).
 *     @type int $id WP Post ID of the Access Plan, if omitted a new plan is created, if supplied, that plan is updated.
 *     @type string $access_expiration Expiration type [lifetime|limited-period|limited-date].
 *     @type string $access_expires Date access expires in m/d/Y format. Only applicable when $access_expiration is "limited-date".
 *     @type int $access_length Length of access from time of purchase, combine with $access_period. Only applicable when $access_expiration is "limited-period".
 *     @type string $access_period Time period of access from time of purchase, combine with $access_length. Only applicable when $access_expiration is "limited-period" [year|month|week|day].
 *     @type string $availability Determine if this access plan is available to anyone or to members only. Use with $availability_restrictions to determine if the member can use the access plan. [open|members].
 *     @type array $availability_restrictions Indexed array of LifterLMS Membership IDs a user must belong to to use the access plan. Only applicable if $availability is "members".
 *     @type string $checkout_redirect_type Type of checkout redirection [self|page|url].
 *     @type string $content Plan description (post_content).
 *     @type string $enroll_text Text to display on buy buttons.
 *     @type int $frequency Frequency of billing. 0 = a one-time payment [0-6].
 *     @type string $is_free Whether or not the plan requires payment [yes|no].
 *     @type int $length Number of intervals to run payment for, combine with $period & $frequency. 0 = forever / until cancelled. Only applicable if $frequency is not 0.
 *     @type int $menu_order Order to display access plans in when listing them. Displayed in ascending order.
 *     @type string $on_sale Enable or disable plan sale pricing [yes|no].
 *     @type string $period Interval period, combine with $length. Only applicable if $frequency is not 0.  [year|month|week|day].
 *     @type float $price Price per charge/
 *     @type string $sale_end Date when the sale pricing ends.
 *     @type string $sale_start Date when the sale pricing begins.
 *     @type float $sale_price Sale price.
 *     @type string $sku Short user-created plan identifier.
 *     @type string $title Plan title.
 *     @type int $trial_length length of the trial period. Only applicable if $trial_offer is "yes".
 *     @type string $trial_offer Enable or disable a plan trial period. [yes|no].
 *     @type string $trial_period Period for the trial period. Only applicable if $trial_offer is "yes". [year|month|week|day].
 *     @type float $trial_price Price for the trial period. Can be 0 for a free trial period.
 * }
 * @return obj `LLMS_Access_Plan` on success, `WP_Error` on failure.
 */
function llms_insert_access_plan( $props = array() ) {

	$action = 'create';

	if ( ! empty( $props['id'] ) ) {

		$action = 'update';
		$plan   = llms_get_post( $props['id'] );
		if ( ! $plan || ! is_a( $plan, 'LLMS_Access_Plan' ) ) {
			// Translators: %s = The invalid access plan ID.
			return new WP_Error( 'invalid-plan', sprintf( __( 'Access Plan ID "%s" is not valid.', 'lifterlms' ), $props['id'] ) );
		}
		unset( $props['id'] );
		$props = wp_parse_args( $props, $plan->toArray() );

	}

	// Merge in default properties.
	$props = wp_parse_args(
		$props,
		apply_filters(
			'llms_access_plan_default_properties',
			array(
				'access_expiration'      => 'lifetime',
				'access_length'          => 1,
				'access_period'          => 'year',
				'availability'           => 'open',
				'checkout_redirect_type' => 'self',
				'frequency'              => 0,
				'is_free'                => 'yes',
				'length'                 => 0,
				'on_sale'                => 'no',
				'period'                 => 'year',
				'price'                  => 0,
				'sale_price'             => 0,
				'title'                  => __( 'Access Plan', 'lifterlms' ),
				'trial_length'           => 1,
				'trial_offer'            => 'no',
				'trial_period'           => 'year',
				'trial_price'            => 0,
				'visibility'             => 'visible',
			)
		)
	);

	/**
	 * Modify the properties passed into `llms_insert_access_plan()`.
	 *
	 * Either `llms_access_plan_before_create` for new plans or `llms_access_plan_before_update` for updates.
	 *
	 * @since    3.29.0
	 * @version  3.29.0
	 *
	 * @param  array $props Properties used to create/update the access plan.
	 */
	$props = apply_filters( 'llms_access_plan_before_' . $action, $props );

	// Cannot create an access plan without a product.
	if ( empty( $props['product_id'] ) || ! is_numeric( $props['product_id'] ) ) {
		// Translators: %s = property key ('product_id').
		return new WP_Error( 'missing-product-id', sprintf( __( 'Missing required property: "%s".', 'lifterlms' ), 'product_id' ) );
	}

	// Paid plan.
	if ( $props['price'] > 0 ) {

		$props['is_free'] = 'no';

		// One-time (no trial)
		if ( 0 === $props['frequency'] ) {
			$props['trial_offer'] = 'no';
		}
	} else {

		$props['is_free']     = 'yes';
		$props['price']       = 0;
		$props['frequency']   = 0;
		$props['on_sale']     = 'no';
		$props['trial_offer'] = 'no';

	}

	// Unset recurring props when it's a 1-time payment.
	if ( 0 === $props['frequency'] ) {
		unset( $props['length'], $props['period'] );
	}

	// Unset trial props when no trial enabled.
	if ( ! llms_parse_bool( $props['trial_offer'] ) ) {
		unset( $props['trial_price'], $props['trial_length'], $props['trial_period'] );
	}

	// Unset sale props when no sale enabled.
	if ( ! llms_parse_bool( $props['on_sale'] ) ) {
		unset( $props['sale_price'], $props['sale_end'], $props['sale_start'] );
	}

	// Unset expiration props based on expiration settings.
	if ( 'lifetime' === $props['access_expiration'] ) {
		unset( $props['access_expires'], $props['access_length'], $props['access_period'] );
	} elseif ( 'limited-date' === $props['access_expiration'] ) {
		unset( $props['access_length'], $props['access_period'] );
	} elseif ( 'limited-period' === $props['access_expiration'] ) {
		unset( $props['access_expires'] );
	}

	// Ensure visibility setting is valid.
	if ( ! in_array( $props['visibility'], array_keys( llms_get_access_plan_visibility_options() ), true ) ) {
		// Translators: %s = supplied visibility setting.
		return new WP_Error( 'invalid-visibility', sprintf( __( 'Invalid access plan visibility: "%s"', 'lifterlms' ), $props['visibility'] ) );
	}

	// Ensure all periods are valid.
	$valid_periods = array_keys( llms_get_access_plan_period_options() );
	foreach ( array( 'period', 'access_period', 'trial_period' ) as $key ) {
		if ( ! empty( $props[ $key ] ) && ! in_array( $props[ $key ], $valid_periods, true ) ) {
			// Translators: %1$s = plan period key name; %2$s = the invalid period.
			return new WP_Error( 'invalid-' . $key, sprintf( __( 'Invalid access plan %1$s: "%2$s"', 'lifterlms' ), $key, $props[ $key ] ) );
		}
	}

	$checkout_redirect_type = $props['checkout_redirect_type'];

	// Ensure that the checkout redirection type is valid.
	if ( ! in_array( $checkout_redirect_type, array_keys( llms_get_checkout_redirection_types() ), true ) ) {
		// Translators: %s = supplied checkout redirect type.
		return new WP_Error( 'invalid-checkout-redirect-type', sprintf( __( 'Invalid checkout redirect type: "%s"', 'lifterlms' ), $checkout_redirect_type ) );
		// Ensure that the correct checkout redirection value is set if the type is page.
	} elseif ( 'page' === $checkout_redirect_type && empty( get_post( $props['checkout_redirect_page'] ) ) ) {
		// Translators: %d = supplied checkout redirect page ID.
		return new WP_Error( 'invalid-checkout-redirect-page', sprintf( __( 'Invalid checkout redirect page ID: "%d"', 'lifterlms' ), $props['checkout_redirect_page'] ) );
		// Ensure that the correct checkout redirection value is set if the type is url.
	} elseif ( 'url' === $checkout_redirect_type && ! filter_var( $props['checkout_redirect_url'], FILTER_VALIDATE_URL ) ) {
		// Translators: %s = supplied checkout redirect page URL.
		return new WP_Error( 'invalid-checkout-redirect-url', sprintf( __( 'Invalid checkout redirect URL: "%s"', 'lifterlms' ), $props['checkout_redirect_url'] ) );

	}

	if ( 'create' === $action ) {
		$plan = new LLMS_Access_Plan( 'new' );
		if ( ! $plan ) {
			return new WP_Error( 'plan-creation', __( 'An error was encountered while creating the access plan', 'lifterlms' ) );
		}
	}

	// Set visibility.
	$plan->set_visibility( $props['visibility'] );

	// Set all valid properties.
	$valid_props = array_keys( $plan->get_properties() );
	foreach ( $props as $prop_key => $prop_val ) {
		if ( in_array( $prop_key, $valid_props, true ) ) {
			$plan->set( $prop_key, $prop_val );
		}
	}

	/**
	 * Do something with an access plan immediately after the access plan is created/updated.
	 *
	 * Either  `llms_access_plan_after_create` during creation or  `llms_access_plan_after_update` during an update.
	 *
	 * @since    3.29.0
	 * @version  3.29.0
	 *
	 * @param  LLMS_Access_Plan $props Access plan instance.
	 * @param  array $props Properties used to create/update the access plan.
	 */
	do_action( 'llms_access_plan_after_' . $action, $plan, $props );

	return $plan;

}

/**
 * Retrieve available options for access plan periods
 *
 * @return  array
 * @since   3.29.0
 * @version 3.29.0
 */
function llms_get_access_plan_period_options() {
	return apply_filters(
		'llms_get_access_plan_period_options',
		array(
			'year'  => __( 'Year', 'lifterlms' ),
			'month' => __( 'Month', 'lifterlms' ),
			'week'  => __( 'Week', 'lifterlms' ),
			'day'   => __( 'Day', 'lifterlms' ),
		)
	);
}

/**
 * Get a list of available access plan visibility options
 *
 * @return   array
 * @since    3.8.0
 * @version  3.8.0
 */
function llms_get_access_plan_visibility_options() {
	return apply_filters(
		'lifterlms_access_plan_visibility_options',
		array(
			'visible'  => __( 'Visible', 'lifterlms' ),
			'hidden'   => __( 'Hidden', 'lifterlms' ),
			'featured' => __( 'Featured', 'lifterlms' ),
		)
	);
}
