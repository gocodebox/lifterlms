<?php
/**
 * Functions for LifterLMS Access Plans
 *
 * @package   LifterLMS/Functions/Access_Plans
 * @since    [version]
 * @version  [version]
 */

defined( 'ABSPATH' ) || exit;

function llms_create_access_plan( $props = array() ) {

	$props = apply_filters( 'llms_access_plan_before_create', $props );

	// Cannot create an access plan without a product.
	if ( empty( $props['product_id'] ) || ! is_numeric( $props['product_id'] ) ) {
		// Translators: %s = property key ('product_id').
		return new WP_Error( 'missing-product-id', sprintf( __( 'Missing required property: "%s".', 'lifterlms' ), 'product_id' ) );
	}

	// Merge in default property settings.
	$props = wp_parse_args( $props, apply_filters( 'llms_create_access_plan_default_props', array(
		'access_expiration' => 'lifetime',
		'access_length' => 1,
		'access_period' => 'year',
		'availability' => 'open',
		'frequency' => 0,
		'is_free' => 'yes',
		'length' => 0,
		'on_sale' => 'no',
		'period' => 'year',
		'price' => 0,
		'sale_price' => 0,
		'title' => sprintf( __( 'Access Plan for %s', 'lifterlms' ), get_the_title( $props['product_id'] ) ),
		'trial_length' => 1,
		'trial_offer' => 'no',
		'trial_period' => 'year',
		'trial_price' => 0,
		'visibility' => 'visible',
	) ) );

	// Paid plan.
	if ( $props['price'] > 0 ) {

		$props['is_free'] = 'no';

		// One-time (no trial)
		if ( 0 === $props['frequency'] ) {
			$props['trial_offer'] = 'no';
		}

	// Free plan (no frequency, no sale, no trial).
	} else {

		$props['is_free'] = 'yes';
		$props['price'] = 0;
		$props['frequency'] = 0;
		$props['on_sale'] = 'no';
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
	if ( 'liftetime' === $props['access_expiration'] ) {
		unset( $props['acces_expires'], $props['access_length'], $props['access_period'] );
	} elseif ( 'limited-date' === $props['access_expiration'] ) {
		unset( $props['access_length'], $props['access_period'] );
	} elseif ( 'limited-period' === $props['access_expiration'] ) {
		unset( $props['acces_expires'] );
	}

	// Ensure visibility setting is valid.
	if ( ! in_array( $props['visibility'], array_keys( llms_get_access_plan_visibility_options() ), true ) ) {
		// Translators: %s = supplied visibility setting.
		return new WP_Error( 'invalid-visibility', sprintf( __( 'Invalid access plan visibilty: "%s"', 'lifterlms' ), $props['visibility'] ) );
	}

	// Ensure all periods are valid.
	$valid_periods = array_keys( llms_get_access_plan_period_options() );
	foreach ( array( 'period', 'access_period', 'trial_period' ) as $key ) {
		if ( isset( $props[ $key ] ) && ! in_array( $props[ $key ], $valid_periods, true ) ) {
			// Translators: %1$s = plan period key name; %2$s = the invalid period.
			return new WP_Error( 'invalid-' . $key, sprintf( __( 'Invalid access plan %1$s: "%2$s"', 'lifterlms' ), $key, $props[ $key ] ) );
		}
	}

	$plan = new LLMS_Access_Plan( 'new' );

	// Set visibility.
	$plan->set_visibility( $props['visibility'] );

	// Set all valid properties.
	$valid_props = array_keys( $plan->get_properties() );
	foreach ( $props as $prop_key => $prop_val ) {
		if ( in_array( $prop_key, $valid_props, true ) ) {
			$plan->set( $prop_key, $prop_val );
		}
	}

	do_action( 'llms_access_plan_created', $plan, $props );

	return $plan;

}

/**
 * Retrieve available options for access plan periods
 *
 * @return  array
 * @since   [version]
 * @version [version]
 */
function llms_get_access_plan_period_options() {
	return apply_filters( 'llms_get_access_plan_period_options', array(
		'year' => __( 'Year', 'lifterlms' ),
		'month' => __( 'Month', 'lifterlms' ),
		'week' => __( 'Week', 'lifterlms' ),
		'day' => __( 'Day', 'lifterlms' ),
	) );
}

/**
 * Get a list of available access plan visibility options
 *
 * @return   array
 * @since    3.8.0
 * @version  3.8.0
 */
function llms_get_access_plan_visibility_options() {
	return apply_filters( 'lifterlms_access_plan_visibility_options', array(
		'visible' => __( 'Visible', 'lifterlms' ),
		'hidden' => __( 'Hidden', 'lifterlms' ),
		'featured' => __( 'Featured', 'lifterlms' ),
	) );
}
