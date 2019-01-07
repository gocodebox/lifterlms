<?php
/**
 * LifterLMS Coupon Model
 *
 * @package  LifterLMS/Models
 * @since    3.0.0
 * @version  3.24.0
 *
 * @property  $coupon_amount  (float)  Amount to subtract from the price when using the coupon. Used with $discount_type to determine the type of discount
 * @property  $coupon_courses  (array)  Array of Course IDs the coupon can be used against
 * @property  $coupon_membership  (array) Array of Membership IDs the coupon can be used against
 * @property  $description  (string)  A string of text. Used as an internal note field  .
 * @property  $discount_type  (string)  Determines the discount type [dollar|percent]
 * @property  $enable_trial_discount  (yes/no)  Enables an optional additional amount field to apply to the Trial Price of access plans with a trial [yes|no]
 * @property  $expiration_date  (string)  Date String describing a date after which the coupon can no longer be used. Format: m/d/Y
 * @property  $plan_type  (string)  Determine the type of plans the coupon can be used with . [any|one-time|recurring]
 * @property  $title  (string)  Coupon Code / Post Title
 * @property  $trial_amount  (float)  Amount to subtract from the trial price when using the coupon. Used with $discount_type to determine the type of discount
 * @property  $usage_limit  (int)  Amount of times the coupon can be used
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Coupon model.
 */
class LLMS_Coupon extends LLMS_Post_Model {

	protected $properties = array(
		'coupon_amount' => 'float',
		'coupon_courses' => 'array',
		'coupon_membership' => 'array',
		'description' => 'string',
		'discount_type' => 'string',
		'enable_trial_discount' => 'yesno',
		'expiration_date' => 'string',
		'plan_type' => 'string',
		'trial_amount' => 'float',
		'usage_limit' => 'absint',
	);

	protected $db_post_type = 'llms_coupon'; // maybe fix this
	protected $model_post_type = 'coupon';

	/**
	 * Determine if the coupon can be applied to an access plan
	 * @since    3.0.0
	 * @version  3.0.0
	 * @param    int|obj    $plan_id  WP Post ID of the LLMS Access Plan or an instance of LLMS_Access_Plan
	 * @return  bool
	 */
	public function applies_to_plan( $plan_id ) {

		if ( $plan_id instanceof LLMS_Access_Plan ) {
			$plan = $plan_id;
		} else {
			$plan = new LLMS_Access_Plan( $plan_id );
		}

		// check if it can be applied to the plan's product first
		if ( ! $this->applies_to_product( $plan->get( 'product_id' ) ) ) {
			return false;
		}

		// if the coupon can only be used with one-time plans and the plan is recurring
		if ( 'one-time' === $this->get( 'plan_type' ) && $plan->is_recurring() ) {
			return false;
		}

		return true;
	}

	/**
	 * Determine if a coupon can be applied to a specific product
	 * @since  3.0.0
	 * @version  3.0.0
	 * @param  int  $product_id  WP Post ID of a LLMS Course or Membership
	 * @return boolean     true if it can be applied, false otherwise
	 */
	public function applies_to_product( $product_id ) {
		$products = $this->get_products();
		// no product restrictions
		if ( empty( $products ) ) {
			return true;
		} // End if().
		else {
			return in_array( $product_id, $products );
		}
	}

	/**
	 * Retrieve the timestamp of a coupon expiration date
	 * Transforms the expiration date to a timestamp and adds 23 hours 59 minutes and 59 seconds to the date
	 * Coupons expire end of day on the expiration date (EG: 2015-12-01 @ 23:59:59)
	 * @return   false|int
	 * @since    3.19.0
	 * @version  3.19.0
	 */
	public function get_expiration_time() {
		$expires = $this->get_date( 'expiration_date', 'U' );
		if ( ! $expires ) {
			return false;
		}
		return ( (int) $expires + DAY_IN_SECONDS - 1 );
	}

	/**
	 * Get the discount type for human reading and allow translation
	 *
	 * @since   3.0.0
	 * @version 3.24.0
	 * @return  string
	 */
	public function get_formatted_discount_type() {
		switch ( $this->get_discount_type() ) {
			case 'percent':
				return __( 'Percentage Discount', 'lifterlms' );
			break;
			case 'dollar':
				return sprintf( _x( '%s Discount', 'flat rate coupon discount', 'lifterlms' ), get_lifterlms_currency_symbol() );
			break;
		}
	}

	/**
	 * Get the formatted coupon amount with currency symbol and/or percentage symbol
	 * @since 3.0.0
	 * @version 3.0.0
	 * @param   string $amount key for the amount to format
	 * @return  string
	 */
	public function get_formatted_amount( $amount = 'coupon_amount' ) {
		$amount = $this->get( $amount );
		switch ( $this->get( 'discount_type' ) ) {
			case 'percent':
				$amount .= '%';
			break;
			case 'dollar':
				$amount = llms_price( $amount );
			break;
		}
		return $amount;
	}

	/**
	 * Get an array of all products the coupon can be used with
	 * Combines $this->coupon_courses & $this->coupon_membership
	 * @since 3.0.0
	 * @version 3.0.0
	 * @return  array
	 */
	public function get_products() {
		return array_merge( $this->get_array( 'coupon_courses' ), $this->get_array( 'coupon_membership' ) );
	}

	/**
	 * Get the number of remaining uses
	 * calculated by substracting # of uses from the usage limit
	 * @since  3.0.0
	 * @version 3.0.0
	 * @return string|int
	 */
	public function get_remaining_uses() {

		$limit = $this->get( 'usage_limit' );

		// if usage is unlimited
		if ( ! $limit ) {

			return _x( 'Unlimited', 'Remaining coupon uses', 'lifterlms' );

		} else {

			return $limit - $this->get_uses();

		}

	}

	/**
	 * Get the number of times the coupon has been used
	 * @return   int
	 * @since    3.0.0
	 * @version  3.19.0
	 */
	public function get_uses() {

		$query = new WP_Query( array(
			'meta_query' => array(
				array(
					'key' => $this->meta_prefix . 'coupon_code',
					'value' => $this->get( 'title' ),
				),
			),
			'post_status' => 'any',
			'post_type' => 'llms_order',
			'posts_per_page' => -1,
		) );

		return $query->post_count;

	}

	/**
	 * Determine if the main (non-trial) price is discounted by this coupon
	 * @return   bool
	 * @since    3.21.1
	 * @version  3.21.1
	 */
	public function has_main_discount() {
		return ( $this->get( 'coupon_amount' ) > 0 );
	}

	/**
	 * Determine if a coupon has uses remaining
	 * @return boolean   true if uses are remaining, false otherwise
	 */
	public function has_remaining_uses() {
		$uses = $this->get_remaining_uses();
		if ( is_numeric( $uses ) ) {
			return ( $uses >= 1 ) ? true : false;
		}
		return true;
	}

	/**
	 * Determine if trial amount discount is enabled for the coupon
	 * @return  boolean
	 * @since   3.0.0
	 * @version 3.21.1
	 */
	public function has_trial_discount() {
		return llms_parse_bool( $this->get( 'enable_trial_discount' ) );
	}

	/**
	 * Determine if a coupon is expired
	 * @return  boolean   true if expired, false otherwise
	 * @since   3.0.0
	 * @version 3.19.0
	 */
	public function is_expired() {
		$expires = $this->get_expiration_time();
		// no expiration date, can't expire
		if ( ! $expires ) {
			return false;
		} else {
			return $expires < llms_current_time( 'timestamp' );
		}
	}

	/**
	 * Perform all available validations and return a success or error message
	 * @param    int            $plan_id  WP Post ID of an LLMS Access Plan
	 * @return   WP_Error|true            If true, the coupon is valid, if WP_Error, there was an error
	 * @since    3.0.0
	 * @version  3.19.0
	 */
	public function is_valid( $plan_id ) {

		$msg = false;

		$plan = new LLMS_Access_Plan( $plan_id );

		if ( ! $this->has_remaining_uses() ) {

			$msg = __( 'This coupon has reached its usage limit and can no longer be used.', 'lifterlms' );

		} elseif ( $this->is_expired() ) {

			$msg = sprintf( __( 'This coupon expired on %s and can no longer be used.', 'lifterlms' ), $this->get_date( 'expiration_date', 'F d, Y' ) );

		} elseif ( ! $this->applies_to_product( $plan->get( 'product_id' ) ) ) {

			$msg = sprintf( __( 'This coupon cannot be used to purchase "%s".', 'lifterlms' ), get_the_title( $plan->get( 'product_id' ) ) );

		} elseif ( ! $this->applies_to_plan( $plan ) ) {

			$msg = sprintf( __( 'This coupon cannot be used to purchase "%s".', 'lifterlms' ), $plan->get( 'title' ) );

		}

		// error encountered
		if ( $msg ) {

			$ret = new WP_Error();
			$ret->add( 'error', apply_filters( 'lifterlms_coupon_validation_error_message', $msg, $this ) );

		} else {

			$ret = true;

		}

		return apply_filters( 'llms_coupon_is_valid', $ret, $plan, $this );

	}

}
