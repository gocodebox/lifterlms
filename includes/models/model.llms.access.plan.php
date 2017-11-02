<?php
/**
 * LifterLMS Access Plan Model
 * @since    3.0.0
 * @version  3.9.1
 *
 * @property  $access_expiration  (string)  Expiration type [lifetime|limited-period|limited-date]
 * @property  $access_expires  (string)  Date access expires in m/d/Y format. Only applicable when $access_expiration is "limited-date"
 * @property  $access_length  (int)  Length of access from time of purchase, combine with $access_period. Only applicable when $access_expiration is "limited-period"
 * @property  $access_period  (string)  Time period of access from time of purchase, combine with $access_length. Only applicable when $access_expiration is "limited-period" [year|month|week|day]
 * @property  $availability  (string)  Determine if this access plan is available to anyone or to members only. Use with $availability_restrictions to determine if the member can use the access plan. [open|members]
 * @property  $availability_restrictions (array)  Indexed array of LifterLMS Membership IDs a user must belong to to use the access plan. Only applicable if $availability is "members".
 * @property  $content  (string)  Plan description (post_content)
 * @property  $enroll_text  (string)  Text to display on buy buttons
 * @property  $frequency  (int)  Frequency of billing. 0 = a one-time payment [0-6]
 * @property  $id  (int)  Post ID
 * @property  $is_free  (string)  Whether or not the plan requires payment [yes|no]
 * @property  $length  (int)  Number of intervals to run payment for, combine with $period & $frequency. 0 = forever / until cancelled. Only applicable if $frequency is not 0.
 * @property  $menu_order  (int)  Order to display access plans in when listing them. Displayed in ascending order.
 * @property  $on_sale  (string)  Enable or disable plan sale pricing [yes|no]
 * @property  $period  (string)  Interval period, combine with $length. Only applicable if $frequency is not 0.  [year|month|week|day]
 * @property  $price  (float)  Price per charge
 * @property  $product_id  (int)  WP Post ID of the related LifterLMS Product (course or membership)
 * @property  $sale_end  (string)  Date when the sale pricing ends
 * @property  $sale_start (string)  Date when the sale pricing begins
 * @property  $sale_price (float)  Sale price
 * @property  $sku  (string)  Short user-created plan identifier
 * @property  $title  (string)  Plan title
 * @property  $trial_length  (int)  length of the trial period. Only applicable if $trial_offer is "yes"
 * @property  $trial_offer  (string)  Enable or disable a plan trial perid. [yes|no]
 * @property  $trial_period  (string)  Period for the trial period. Only applicable if $trial_offer is "yes". [year|month|week|day]
 * @property  $trial_price  (float)  Price for the trial period. Can be 0 for a free trial period
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Access_Plan extends LLMS_Post_Model {

	protected $properties = array(
		'access_expiration' => 'string',
		'access_expires' => 'string',
		'access_length' => 'absint',
		'access_period' => 'string',
		'availability' => 'string',
		'availability_restrictions' => 'array',
		'content' => 'string',
		'enroll_text' => 'string',
		'frequency' => 'absint',
		'is_free' => 'yesno',
		'length' => 'absint',
		'menu_order' => 'absint',
		'on_sale' => 'yesno',
		'period' => 'string',
		'price' => 'float',
		'product_id' => 'absint',
		'sale_end' => 'string',
		'sale_start' => 'string',
		'sale_price' => 'float',
		'sku' => 'string',
		'title' => 'string',
		'trial_length' => 'absint',
		'trial_offer' => 'yesno',
		'trial_period' => 'string',
		'trial_price' => 'float',
	);

	protected $db_post_type = 'llms_access_plan';
	protected $model_post_type = 'access_plan';

	/**
	 * Determine if the access plan has expiration settings
	 * @since   3.0.0
	 * @version 3.0.0
	 * @return  boolean     true if it can expire, false if it's for lifetime access
	 */
	public function can_expire() {
		return ( 'lifetime' !== $this->get( 'access_expiration' ) );
	}

	/**
	 * Get the translated and pluralized name of the plan's access period
	 * @param    string     $period  (optional) untranslated access period, if not supplied uses stored value for the plan
	 * @param    int        $length  (optional) access length (for plurailzation), if not supplied uses stored value for the plan
	 * @return   string
	 * @since    3.4.6
	 * @version  3.4.8
	 */
	public function get_access_period_name( $period = null, $length = null ) {

		$period = $period ? $period : $this->get( 'access_period' );
		$length = $length ? $length : $this->get( 'access_length' );

		switch ( $period ) {

			case 'year':
				$period = _nx( 'year', 'years', $length, 'Access plan period', 'lifterlms' );
			break;

			case 'month':
				$period = _nx( 'month', 'months', $length, 'Access plan period', 'lifterlms' );
			break;

			case 'week':
				$period = _nx( 'week', 'weeks', $length, 'Access plan period', 'lifterlms' );
			break;

			case 'day':
				$period = _nx( 'day', 'days', $length, 'Access plan period', 'lifterlms' );
			break;

		}

		return $period;

	}


	/**
	 * Default arguments for creating a new post
	 * @param  string  $title   Title to create the post with
	 * @return array
	 * @since  3.0.0
	 * @version  3.0.0
	 */
	protected function get_creation_args( $title = '' ) {

		return array_merge( parent::get_creation_args( $title ), array(
			'post_status' 	 => 'publish',
		) );

	}

	/**
	 * Retrieve the full URL to the checkout screen for the plan
	 * @return   string
	 * @since    3.0.0
	 * @version  3.9.1
	 */
	public function get_checkout_url( $check_availability = true ) {
		if ( ! $check_availability || $this->is_available_to_user( get_current_user_id() ) ) {
			return llms_get_page_url( 'checkout', array(
				'plan' => $this->get( 'id' ),
			) );
		} else {
			return '#llms-plan-locked';
		}
	}

	/**
	 * Get a string to use for 0 dollar amount prices rather than 0
	 * @param   string $format format to display the price in
	 * @return  string
	 * @since 3.0.0
	 * @version 3.0.0
	 */
	public function get_free_pricing_text( $format = 'html' ) {
		$text = __( 'FREE', 'lifterlms' );

		if ( 'html' === $format ) {
			$text = '<span class="lifterlms-price">' . $text . '</span>';
		} elseif ( 'float' === $format ) {
			$text = 0.00;
		}

		return apply_filters( 'llms_get_free_' . $this->model_post_type . '_pricing_text', $text, $this );
	}

	/**
	 * Getter for price strings with optional formatting options
	 * @param  string $key         property key
	 * @param  array  $price_args  optional array of arguments that can be passed to llms_price()
	 * @param  string $format      optional format conversion method [html|raw|float]
	 * @return mixed
	 * @since  3.0.0
	 * @version  3.0.0
	 */
	public function get_price( $key, $price_args = array(), $format = 'html' ) {

		$price = $this->get( $key );

		if ( $price <= 0 ) {

			$r = $this->get_free_pricing_text( $format );

		} else {

			$r = parent::get_price( $key, $price_args, $format );

		}

		return $r;
	}

	/**
	 * Apply a coupon to a price
	 * @param   string $key        price to retrieve, "price", "sale_price", or "trial_price"
	 * @param   int    $coupon_id  LifterLMS Coupon Post ID
	 * @param   array  $price_args optional arguments to be passed to llms_price()
	 * @param   string $format     optionl return format as passed to llms_price()
	 * @return  mixed
	 * @since   3.0.0
	 * @version 3.7.0
	 */
	public function get_price_with_coupon( $key, $coupon_id, $price_args = array(), $format = 'html' ) {

		// allow id or instance to be passed for $coupon_id
		if ( $coupon_id instanceof LLMS_Coupon ) {
			$coupon = $coupon_id;
		} else {
			$coupon = new LLMS_Coupon( $coupon_id );
		}

		$price = $this->get( $key );

		// ensure the coupon *can* be applied to this plan
		if ( ! $coupon->is_valid( $this ) ) {
			return $price;
		}

		$discount_type = $coupon->get( 'discount_type' );

		// price and sale price are calculated of coupon amount
		if ( 'price' === $key || 'sale_price' === $key ) {

			$coupon_amount = $coupon->get( 'coupon_amount' );

		} elseif ( 'trial_price' === $key && $coupon->has_trial_discount() && $this->has_trial() ) {

			$coupon_amount = $coupon->get( 'trial_amount' );

		} else {

			$coupon_amount = 0;

		}

		if ( $coupon_amount ) {

			// simple subtraction
			if ( 'dollar' === $discount_type ) {
				$price = $price - $coupon_amount;
			} // End if().
			elseif ( 'percent' === $discount_type ) {
				$price = $price - ( $price * ( $coupon_amount / 100 ) );
			}
		}

		// if price is less than 0 return the pricing text
		if ( $price <= 0 ) {

			$price = $this->get_free_pricing_text( $format );

		} else {

			if ( 'html' == $format || 'raw' === $format ) {
				$price = llms_price( $price, $price_args );
				if ( 'raw' === $format ) {
					$price = strip_tags( $price );
				}
			} elseif ( 'float' === $format ) {
				$price = floatval( number_format( $price, get_lifterlms_decimals(), '.', '' ) );
			} else {
				$price = apply_filters( 'llms_get_' . $this->model_post_type . '_' . $key . '_' . $format . '_with_coupon', $price, $key, $price_args, $format, $this );
			}
		}

		return apply_filters( 'llms_get_' . $this->model_post_type . '_' . $key . '_price_with_coupon', $price, $key, $price_args, $format, $this );

	}

	/**
	 * Retrieve an instance of the associated LLMS_Product
	 * @return obj
	 * @since  3.0.0
	 * @version  3.0.0
	 */
	public function get_product() {
		return new LLMS_Product( $this->get( 'product_id' ) );
	}

	/**
	 * Retrieve the product type (course or membership) for the associated product
	 * @return string
	 * @since  3.0.0
	 * @version  3.0.0
	 */
	public function get_product_type() {
		$product = $this->get_product();
		return str_replace( 'llms_', '', $product->get( 'type' ) );
	}

	/**
	 * Retrieve the text displayed on "Buy" buttons
	 * Uses optional user submitted text and falls back to LifterLMS defaults if none is supplied
	 * @return string
	 * @since 3.0.0
	 * @version  3.0.0
	 */
	public function get_enroll_text() {

		// user submitted text
		$text = $this->get( 'enroll_text' );

		if ( ! $text ) {

			switch ( $this->get_product_type() ) {
				case 'course':
					$text = apply_filters( 'llms_course_enroll_button_text', __( 'Enroll', 'lifterlms' ), $this );
				break;

				case 'membership':
					$text = apply_filters( 'llms_membership_enroll_button_text', __( 'Join', 'lifterlms' ), $this );
				break;

				default:
					$text = apply_filters( 'llms_default_enroll_button_text', __( 'Buy', 'lifterlms' ), $this );
			}
		}
		return $text;
	}

	/**
	 * Get a sentence explaining plan expiration details
	 * @return string
	 * @since  3.0.0
	 * @version  3.4.6
	 */
	public function get_expiration_details() {

		$r = '';

		switch ( $this->get( 'access_expiration' ) ) {

			case 'limited-date':

				$r = sprintf( _x( 'access until %s', 'Access expiration date', 'lifterlms' ), $this->get_date( 'access_expires', 'n/j/y' ) );

			break;

			case 'limited-period':

				$r = sprintf( _x( '%1$d %2$s of access', 'Access period description', 'lifterlms' ), $this->get( 'access_length' ), $this->get_access_period_name() );

			break;

		}

		return apply_filters( 'llms_get_product_expiration_details', $r, $this );
	}

	/**
	 * Get a sentence explaining the plan's payment schedule
	 * @return string
	 * @since 3.0.0
	 * @version  3.5.3
	 */
	public function get_schedule_details() {

		$r = '';

		$period = $this->get( 'period' );
		$frequency = $this->get( 'frequency' );
		$length = $this->get( 'length' );

		// one-time payments don't display anything here unless filtered
		if ( $frequency > 0 ) {

			// setup billing frequency sentence
			switch ( $frequency ) {

				case 1:
					$r = sprintf( _x( 'per %s', 'subscription schedule', 'lifterlms' ), $this->get_access_period_name( $period, $frequency ) );
				break;

				case 2:
				case 3:
				case 4:
				case 5:
				case 6:
					$r = sprintf( _x( 'every %1$d %2$s', 'subscription schedule', 'lifterlms' ), $frequency, $this->get_access_period_name( $period, $frequency ) );
				break;

			}

			// add length sentence if applicable
			if ( $length > 0 ) {

				$r .= ' ';
				$r .= sprintf( _x( 'for %1$d %2$s', 'subscription # of payments', 'lifterlms' ), $length, $this->get_access_period_name( $period, $length ) );

			}
		}

		return apply_filters( 'llms_get_product_schedule_details', sprintf( $r, $this->get( 'period' ), $frequency, $length ), $this );

	}

	/**
	 * Get a sentence explaining the plan's trial offer
	 * @return   string
	 * @since    3.0.0
	 * @version  3.4.8
	 */
	public function get_trial_details() {

		$details = '';

		if ( $this->has_trial() ) {

			$length = $this->get( 'trial_length' );
			$period = $this->get( 'trial_period' );
			$details = sprintf( _x( 'for %1$d %2$s', 'trial offer description', 'lifterlms' ), $length, $this->get_access_period_name( $period, $length ) );

		}

		return apply_filters( 'llms_get_product_trial_details', $details, $this );
	}

	/**
	 * Get the access plans visibility setting
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_visibility() {
		$term = $this->get_terms( 'llms_access_plan_visibility', true );
		$ret = ( $term && $term->name ) ? $term->name : 'visible';
		return apply_filters( 'llms_get_access_plan_visibility', $ret, $this );
	}

	/**
	 * Determine if the plan has availability restrictions
	 * Related product must be a COURSE
	 * Availability must be set to "members" and at least one membership must be selected
	 * @return boolean
	 * @since 3.0.0
	 * @version  3.0.0
	 */
	public function has_availability_restrictions() {
		return ( 'course' === $this->get_product_type() && 'members' === $this->get( 'availability' ) && $this->get_array( 'availability_restrictions' ) );
	}

	/**
	 * Determine if the free checkout process & interface should be used for this access plan
	 * @return   boolean
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	public function has_free_checkout() {
		return ( $this->is_free() && apply_filters( 'llms_has_free_checkout', true, $this ) );
	}

	/**
	 * Determine if the plan has a trial offer
	 * One-time payments can't have a trial, so the plan must have a frequency greater than 0
	 * @return boolean
	 * @since 3.0.0
	 * @version  3.0.0
	 */
	public function has_trial() {
		if ( $this->get( 'frequency' ) > 0 ) {
			return 'yes' === $this->get( 'trial_offer' ) ? true : false;
		}
		return false;
	}

	/**
	 * Determine if the plan is available to a user based on configured availability restrictions
	 * @param    int     $user_id  (optional) WP User ID, if not supplied get_current_user_id() will be used
	 * @return   boolean
	 * @since    3.4.4
	 * @version  3.4.4
	 */
	public function is_available_to_user( $user_id = null ) {

		$user_id = empty( $user_id ) ? $user_id : get_current_user_id();

		$access = true;

		// if theres membership restrictions, check the user is in at least one membership
		if ( $this->has_availability_restrictions() ) {
			$access = false;
			foreach ( $this->get_array( 'availability_restrictions' ) as $mid ) {

				// once we find a membership, exit
				if ( llms_is_user_enrolled( $user_id, $mid ) ) {
					$access = true;
					break;
				}
			}
		}

		return $access;

	}

	/**
	 * Determine if the plan is marked as "featured"
	 * @return boolean
	 * @since 3.0.0
	 * @version  3.8.0
	 */
	public function is_featured() {
		return ( 'featured' === $this->get_visibility() );
	}

	/**
	 * Determines if a plan is marked ar free
	 * This only returns the value of the setting and should not
	 * be used to check if payment is required (when using a coupon for example)
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function is_free() {
		return ( 'yes' === $this->get( 'is_free' ) );
	}

	/**
	 * Determine if a plan is *currently* on sale
	 * @return boolean
	 * @since  3.0.0
	 * @version  3.0.0
	 */
	public function is_on_sale() {

		if ( 'yes' === $this->get( 'on_sale' ) ) {

			$now = current_time( 'timestamp' );

			$start = $this->get( 'sale_start' );
			$end = $this->get( 'sale_end' );

			// no dates, the product is indefinitely on sale
			if ( ! $start && ! $end ) {
				return true;
			}

			$start = ( $start ) ? strtotime( $start . ' 00:00:00' ) : $start;
			$end = ( $end ) ? strtotime( $end . ' 23:23:59' ) : $end;

			// start and end
			if ( $start && $end ) {

				return ( $now < $end && $now > $start );

			} // End if().
			elseif ( $start && ! $end ) {

				return ( $now > $start );

			} // only end
			elseif ( ! $start && $end ) {

				return ( $now < $end );

			}
		}

		return false;

	}

	/**
	 * Determine if the plan is visible
	 * Both featured and visible access plans are considered visible
	 * @return   boolean
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function is_visible() {
		return ( 'hidden' !== $this->get_visibility() );
	}

	/**
	 * Determine if the Access Plan has recurring payments
	 * @return  boolean   true if it is recurring, false otherwise
	 * @since   3.0.0
	 * @version 3.0.0
	 */
	public function is_recurring() {
		return ( 0 !== $this->get( 'frequency' ) );
	}

	/**
	 * Determine if the access plan requires payment
	 * accounts for coupons and whether the plan is marked as free
	 * @param    int     $coupon_id  WP_Post ID of an LLMS_Coupon
	 * @return   boolean             true if payment required, false otherwise
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function requires_payment( $coupon_id = null ) {

		if ( $this->is_free() ) {
			return false;
		}

		if ( $coupon_id ) {

			if ( $this->has_trial() && $this->get_price_with_coupon( 'trial_price', $coupon_id, array(), 'float' ) > 0 ) {
				return true;
			} elseif ( $this->is_on_sale() && $this->get_price_with_coupon( 'sale_price', $coupon_id, array(), 'float' ) > 0 ) {
				return true;
			} elseif ( $this->get_price_with_coupon( 'price', $coupon_id, array(), 'float' ) ) {
				return true;
			}
		} else {

			if ( $this->has_trial() && $this->get_price( 'trial_price', array(), 'float' ) > 0 ) {
				return true;
			} elseif ( $this->is_on_sale() && $this->get_price( 'sale_price', array(), 'float' ) > 0 ) {
				return true;
			} elseif ( $this->get_price( 'price', array(), 'float' ) > 0 ) {
				return true;
			}
		}

	}

	/**
	 * Update the visibility term for the access plan
	 * @param    string     $visibility  access plan name
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function set_visibility( $visibility ) {
		return $this->set_terms( array( $visibility ), 'llms_access_plan_visibility', false );
	}

}
