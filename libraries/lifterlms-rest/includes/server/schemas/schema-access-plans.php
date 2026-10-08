<?php
/**
 * Access plan schema definition
 *
 * This schema contains all properties of the access plan that are not inherited from the parent schema.
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0-beta.18
 * @version 1.0.0-beta-24
 *
 * @see LLMS_REST_Access_Plans_Controller::get_item_schema()
 */

defined( 'ABSPATH' ) || exit;

return array(
	'price'                     => array(
		'description' => __( 'Access plan price.', 'lifterlms' ),
		'type'        => 'number',
		'required'    => true,
		'context'     => array( 'view', 'edit' ),
		'arg_options' => array(
			'validate_callback' => 'llms_rest_validate_positive_float_w_zero',
		),
	),
	'access_expiration'         => array(
		'description' => __( 'Access expiration type. `lifetime` provides access until cancelled or until a recurring payment fails. `limited-period` provides access for a limited period as specified by `access_length` and `access_period` `limited-date` provides access until the date specified by access_expires_date`.', 'lifterlms' ),
		'type'        => 'string',
		'default'     => 'lifetime',
		'enum'        => array(
			'lifetime',
			'limited-period',
			'limited-date',
		),
		'context'     => array( 'view', 'edit' ),
	),
	'access_expires'            => array(
		'description' => __( 'Date when access expires. Only applicable when `access_expiration` is `limited-date`. `Format: Y-m-d H:i:s`.', 'lifterlms' ),
		'type'        => 'string',
		'context'     => array( 'view', 'edit' ),
	),
	'access_length'             => array(
		'description' => __( 'Determine the length of access from time of purchase. Only applicable when `access_expiration` is `limited-period`.', 'lifterlms' ),
		'type'        => 'integer',
		'context'     => array( 'view', 'edit' ),
		'default'     => 1,
		'arg_options' => array(
			'validate_callback' => 'llms_rest_validate_strictly_positive_int',
			'sanitize_callback' => 'absint',
		),
	),
	'access_period'             => array(
		'description' => __( 'Determine the length of access from time of purchase. Only applicable when `access_expiration` is `limited-period`', 'lifterlms' ),
		'type'        => 'string',
		'default'     => 'year',
		'enum'        => array_keys( llms_get_access_plan_period_options() ),
		'context'     => array( 'view', 'edit' ),
	),
	'availability_restrictions' => array(
		'description' => __( 'Restrict usage of this access plan to students enrolled in at least one of the specified memberships. Applicable only when `post_id` refers to a Course.', 'lifterlms' ),
		'type'        => 'array',
		'items'       => array(
			'type' => 'integer',
		),
		'context'     => array( 'view', 'edit' ),
		'arg_options' => array(
			'validate_callback' => static function ( $val ) {
				return llms_rest_validate_memberships( $val, true ); // Allow empty to unset.
			},
		),

	),
	'enroll_text'               => array(
		'description' => __( 'Text of the "Purchase" button', 'lifterlms' ),
		'type'        => 'string',
		'default'     => __( 'Buy Now', 'lifterlms' ),
		'context'     => array( 'view', 'edit' ),
	),
	'frequency'                 => array(
		'description' => __( 'Billing frequency [0-6]. `0` denotes a one-time payment. `>= 1` denotes a recurring plan.', 'lifterlms' ),
		'type'        => 'integer',
		'default'     => 0,
		'context'     => array( 'view', 'edit' ),
		'arg_options' => array(
			'validate_callback' => static function ( $val ) {
				return in_array( $val, range( 0, 6 ), true ) ? true : new WP_Error(
					'rest_invalid_param',
					__( 'Must be an integer in the range 0-6', 'lifterlms' )
				);
			},
			'sanitize_callback' => 'absint',
		),
	),
	'length'                    => array(
		'description' => __( 'For recurring plans only. Determines the number of intervals a plan should run for. `0` denotes the plan should run until cancelled.', 'lifterlms' ),
		'type'        => 'integer',
		'default'     => 0,
		'context'     => array( 'view', 'edit' ),
		'arg_options' => array(
			'sanitize_callback' => 'absint',
		),
	),
	'period'                    => array(
		'description' => __( 'For recurring plans only. Determines the interval of recurring payments.', 'lifterlms' ),
		'type'        => 'string',
		'default'     => 'year',
		'enum'        => array_keys( llms_get_access_plan_period_options() ),
		'context'     => array( 'view', 'edit' ),
	),
	'post_id'                   => array(
		'description' => __( 'Determines the course or membership which can be accessed through the plan.', 'lifterlms' ),
		'type'        => 'integer',
		'context'     => array( 'view', 'edit' ),
		'required'    => true,
		'arg_options' => array(
			'validate_callback' => static function ( $val ) {
				return llms_rest_validate_products( $val ) ? true : new WP_Error(
					'rest_invalid_param',
					__( 'Must be a valid course or membership ID', 'lifterlms' )
				);
			},
			'sanitize_callback' => 'absint',
		),
	),
	'redirect_forced'           => array(
		'description' => __( "Use this plans's redirect settings when purchasing a Membership this plan is restricted to. Applicable only when `availability_restrictions` exist for the plan", 'lifterlms' ),
		'type'        => 'boolean',
		'default'     => false,
		'context'     => array( 'view', 'edit' ),
	),
	'redirect_page'             => array(
		'description' => __( 'WordPress page ID to use for checkout success redirection. Applicable only when `redirect_type` is page.', 'lifterlms' ),
		'type'        => 'integer',
		'context'     => array( 'view', 'edit' ),
		'arg_options' => array(
			'validate_callback' => 'llms_rest_validate_strictly_positive_int',
			'sanitize_callback' => 'absint',
		),
	),
	'redirect_type'             => array(
		'description' => __( "Determines the redirection behavior of the user's browser upon successful checkout or registration through the plan. `self`: Redirect to the permalink of the specified `post_id`. `page`: Redirect to the permalink of the WordPress page specified by `redirect_page_id`. `url`: Redirect to the URL specified by `redirect_url`.", 'lifterlms' ),
		'type'        => 'string',
		'default'     => 'self',
		'enum'        => array(
			'self',
			'page',
			'url',
		),
		'context'     => array( 'view', 'edit' ),
	),
	'redirect_url'              => array(
		'description' => __( 'URL to use for checkout success redirection. Applicable only when `redirect_type` is `url`.', 'lifterlms' ),
		'type'        => 'string',
		'context'     => array( 'view', 'edit' ),
		'format'      => 'uri',
		'arg_options' => array(
			'sanitize_callback' => 'esc_url_raw',
		),
	),
	'sale_date_end'             => array(
		'description' => __( 'Used to automatically end a scheduled sale. If empty, the plan remains on sale indefinitely. Only applies when `sale_enabled` is `true`. Format: `Y-m-d H:i:s`.', 'lifterlms' ),
		'type'        => 'string',
		'context'     => array( 'view', 'edit' ),
	),
	'sale_date_start'           => array(
		'description' => __( 'Used to automatically start a scheduled sale. If empty, the plan is on sale immediately. Only applies when `sale_enabled` is `true`. Format: `Y-m-d H:i:s`.', 'lifterlms' ),
		'type'        => 'string',
		'context'     => array( 'view', 'edit' ),
	),
	'sale_enabled'              => array(
		'description' => __( 'Mark the plan as "On Sale" allowing for temporary price adjustments.', 'lifterlms' ),
		'type'        => 'boolean',
		'default'     => false,
		'context'     => array( 'view', 'edit' ),
	),
	'sale_price'                => array(
		'description' => __( 'Sale price. Only applies when `sale_enabled` is `true`.', 'lifterlms' ),
		'type'        => 'number',
		'context'     => array( 'view', 'edit' ),
		'arg_options' => array(
			'validate_callback' => 'llms_rest_validate_positive_float_w_zero',
		),
	),
	'sku'                       => array(
		'description' => __( 'External identifier', 'lifterlms' ),
		'type'        => 'string',
		'context'     => array( 'view', 'edit' ),
	),
	'trial_enabled'             => array(
		'description' => __( 'Enable a trial period for a recurring access plan.', 'lifterlms' ),
		'type'        => 'boolean',
		'default'     => false,
		'context'     => array( 'view', 'edit' ),
	),
	'trial_length'              => array(
		'description' => __( 'Determines the length of trial access. Only applies when `trial_enabled` is `true`.', 'lifterlms' ),
		'type'        => 'integer',
		'default'     => 1,
		'context'     => array( 'view', 'edit' ),
		'arg_options' => array(
			'validate_callback' => 'llms_rest_validate_strictly_positive_int',
			'sanitize_callback' => 'absint',
		),
	),
	'trial_period'              => array(
		'description' => __( 'Determines the length of trial access. Only applies when `trial_enabled` is `true`.', 'lifterlms' ),
		'type'        => 'string',
		'default'     => 'week',
		'enum'        => array(
			'year',
			'month',
			'week',
			'day',
		),
		'context'     => array( 'view', 'edit' ),
	),
	'trial_price'               => array(
		'description' => __( 'Determines the price of the trial period. Only applies when `trial_enabled` is `true`.', 'lifterlms' ),
		'type'        => 'number',
		'default'     => 0,
		'context'     => array( 'view', 'edit' ),
		'arg_options' => array(
			'validate_callback' => 'llms_rest_validate_positive_float_w_zero',
		),
	),
	'visibility'                => array(
		'description' => __( 'Access plan visibility.', 'lifterlms' ),
		'type'        => 'string',
		'default'     => 'visible',
		'enum'        => array_keys( llms_get_access_plan_visibility_options() ),
		'context'     => array( 'view', 'edit' ),
	),
);
