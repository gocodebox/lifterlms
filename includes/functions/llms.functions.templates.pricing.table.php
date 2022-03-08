<?php
/**
 * Template functions for pricing tables
 *
 * @package LifterLMS/Functions
 *
 * @since 3.23.0
 * @version 3.38.0
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

if ( ! function_exists( 'llms_template_product_not_purchasable' ) ) {
	/**
	 * Include template for products that aren't purchasable
	 *
	 * @since 3.38.0
	 *
	 * @param int $post_id Optional. WP Post ID of the product. Default is ID of the global $post.
	 * @return void
	 */
	function llms_template_product_not_purchasable( $post_id = null ) {

		$post_id = $post_id ? $post_id : get_the_ID();
		$product = new LLMS_Product( $post_id );

		llms_get_template(
			'product/not-purchasable.php',
			compact( 'product' )
		);

	}
}


if ( ! function_exists( 'lifterlms_template_pricing_table' ) ) {
	/**
	 * Include pricing table for a LifterLMS Product (course or membership)
	 *
	 * @since 3.0.0
	 * @since 3.38.0 Fixed spelling error in variable passed to template.
	 * @since 6.0.0 Removed the deprecated and misspelled `$purchaseable` global variable.
	 *
	 * @param int $post_id Optional. WP Post ID of the product. Default is ID of the global $post.
	 * @return void
	 */
	function lifterlms_template_pricing_table( $post_id = null ) {

		$post_id = $post_id ? $post_id : get_the_ID();
		$product = new LLMS_Product( $post_id );

		/**
		 * Filter current user's enrollment status
		 *
		 * This filter is used to customize the output behavior of the pricing table.
		 * It does not modify the user's enrollment status.
		 *
		 * @since Unknown
		 *
		 * @param boolean $is_enrolled User's current enrollment status.
		 */
		$is_enrolled = apply_filters(
			'llms_product_pricing_table_enrollment_status',
			llms_is_user_enrolled( get_current_user_id(), $product->get( 'id' ) )
		);

		$purchasable      = $product->is_purchasable();
		$has_free         = $product->has_free_access_plan();
		$has_restrictions = $product->has_restrictions();

		llms_get_template(
			'product/pricing-table.php',
			compact( 'product', 'is_enrolled', 'purchasable', 'has_free', 'has_restrictions' )
		);

	}
}
