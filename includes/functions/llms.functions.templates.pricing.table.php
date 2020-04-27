<?php
/**
 * @since 3.23.0
 *
 * Template functions for pricing tables
 *
 */
defined( 'ABSPATH' ) || exit;

/**
 * Retrieve a list of CSS classes for a single access plan element
 *
 * @since 3.23.0
 *
 * @param LLMS_Access_Plan $plan Access plan object.
 * @return string
 */
function llms_get_access_plan_classes( $plan ) {
	$classes = array(
		'llms-access-plan',
		sprintf( 'llms-access-plan-%d', $plan->get( 'id' ) ),
	);
	if ( $plan->is_featured() ) {
		$classes[] = 'featured';
	}
	if ( $plan->is_on_sale() ) {
		$classes[] = 'on-sale';
	}
	return implode( ' ', apply_filters( 'llms_access_plan_classes', $classes, $plan ) );
}

if ( ! function_exists( 'llms_template_access_plan' ) ) {
	/**
	 * Include single access plan template within the pricing table
	 *
	 * @since 3.23.0
	 *
	 * @param LLMS_Access_Plan $plan Access plan object.
	 * @return void
	 */
	function llms_template_access_plan( $plan ) {
		llms_get_template(
			'product/access-plan.php',
			compact( 'plan' )
		);
	}
}

if ( ! function_exists( 'llms_template_access_plan_button' ) ) {
	/**
	 * Include Single Access Plan Button Template
	 *
	 * @since 3.23.0
	 *
	 * @param LLMS_Access_Plan $plan Access plan object.
	 * @return void
	 */
	function llms_template_access_plan_button( $plan ) {
		llms_get_template(
			'product/access-plan-button.php',
			compact( 'plan' )
		);
	}
}

if ( ! function_exists( 'llms_template_access_plan_description' ) ) {
	/**
	 * Include Single Access Plan Description Template
	 *
	 * @since 3.23.0
	 *
	 * @param LLMS_Access_Plan $plan Access plan object.
	 * @return void
	 */
	function llms_template_access_plan_description( $plan ) {
		llms_get_template(
			'product/access-plan-description.php',
			compact( 'plan' )
		);
	}
}

if ( ! function_exists( 'llms_template_access_plan_feature' ) ) {
	/**
	 * Include Single Access Plan Featured Template
	 *
	 * @since 3.23.0
	 *
	 * @param LLMS_Access_Plan $plan Access plan object.
	 * @return void
	 */
	function llms_template_access_plan_feature( $plan ) {
		llms_get_template(
			'product/access-plan-feature.php',
			compact( 'plan' )
		);
	}
}

if ( ! function_exists( 'llms_template_access_plan_pricing' ) ) {
	/**
	 * Include Single Access Plan pricing Template
	 *
	 * @since 3.23.0
	 *
	 * @param LLMS_Access_Plan $plan Access plan object.
	 * @return void
	 */
	function llms_template_access_plan_pricing( $plan ) {
		llms_get_template(
			'product/access-plan-pricing.php',
			compact( 'plan' )
		);
	}
}

if ( ! function_exists( 'llms_template_access_plan_restrictions' ) ) {
	/**
	 * Include Single Access Plan restrictions Template
	 *
	 * @since 3.23.0
	 *
	 * @param LLMS_Access_Plan $plan Access plan object.
	 * @return void
	 */
	function llms_template_access_plan_restrictions( $plan ) {
		llms_get_template(
			'product/access-plan-restrictions.php',
			compact( 'plan' )
		);
	}
}

if ( ! function_exists( 'llms_template_access_plan_title' ) ) {
	/**
	 * Include Single Access Plan title Template
	 *
	 * @since 3.23.0
	 *
	 * @param LLMS_Access_Plan $plan Access plan object.
	 * @return void
	 */
	function llms_template_access_plan_title( $plan ) {
		llms_get_template(
			'product/access-plan-title.php',
			compact( 'plan' )
		);
	}
}

if ( ! function_exists( 'llms_template_access_plan_trial' ) ) {
	/**
	 * Include Single Access Plan trial Template
	 *
	 * @since 3.23.0
	 *
	 * @param LLMS_Access_Plan $plan Access plan object.
	 * @return void
	 */
	function llms_template_access_plan_trial( $plan ) {
		llms_get_template(
			'product/access-plan-trial.php',
			compact( 'plan' )
		);
	}
}

if ( ! function_exists( 'lifterlms_template_pricing_table' ) ) {
	/**
	 * Include pricing table for a LifterLMS Product (course or membership)
	 *
	 * @since 3.0.0
	 *
	 * @param int $post_id WP Post ID of the product.
	 * @return void
	 */
	function lifterlms_template_pricing_table( $post_id = null ) {

		$post_id = $post_id ? $post_id : get_the_ID();

		$product = new LLMS_Product( $post_id );

		$is_enrolled  = llms_is_user_enrolled( get_current_user_id(), $product->get( 'id' ) );
		$purchaseable = $product->is_purchasable();
		$has_free     = $product->has_free_access_plan();

		llms_get_template(
			'product/pricing-table.php',
			compact( 'product', 'is_enrolled', 'purchaseable', 'has_free' )
		);

	}
}
