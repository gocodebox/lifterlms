<?php
/**
 * LifterLMS Order Model
 *
 * @package  LifterLMS/Models
 * @since    3.0.0
 * @version  3.24.0
 *
 * @property   $access_expiration  (string)  Expiration type [lifetime|limited-period|limited-date]
 * @property   $access_expires  (string)  Date access expires in m/d/Y format. Only applicable when $access_expiration is "limited-date"
 * @property   $access_length  (int)  Length of access from time of purchase, combine with $access_period. Only applicable when $access_expiration is "limited-period"
 * @property   $access_period  (string)  Time period of access from time of purchase, combine with $access_length. Only applicable when $access_expiration is "limited-period" [year|month|week|day]
 * @property   $anonymized  (string)  Determines if the order has been anonymized due to a personal information erasure request (yes|no)
 * @property   $billing_address_1  (string)  customer billing address line 1
 * @property   $billing_address_2  (string)  customer billing address line 2
 * @property   $billing_city  (string)  customer billing city
 * @property   $billing_country  (string)  customer billing country, 2-digit ISO code
 * @property   $billing_email  (string)  customer email address
 * @property   $billing_first_name  (string)  customer first name
 * @property   $billing_last_name  (string)  customer last name
 * @property   $billing_phone  (string)  customer phone number
 * @property   $billing_state  (string)  customer billing state
 * @property   $billing_zip  (string)  customer billing zip/postal code
 * @property   $billing_frequency  (int)  Frequency of billing. 0 = a one-time payment [0-6]
 * @property   $billing_length  (int)  Number of intervals to run payment for, combine with $billing_period & $billing_frequency. 0 = forever / until cancelled. Only applicable if $billing_frequency is not 0.
 * @property   $billing_period  (string)  Interval period, combine with $length. Only applicable if $billing_frequency is not 0.  [year|month|week|day]
 * @property   $coupon_amount  (float)  Amount of the coupon (flat/percentage) in relation to the plan amount
 * @property   $coupon_amout_trial  (float)  Amount of the coupon (flat/percentage) in relation to the plan trial amount where applicable
 * @property   $coupon_code  (string)  Coupon Code Used
 * @property   $coupon_id  (int)  WP Post ID of the LifterLMS Coupon Used
 * @property   $coupon_type  (string)  Type of coupon used, either percent or dollar
 * @property   $coupon_used  (string)  Whether or not a coupon was used for the order [yes|no]
 * @property   $coupon_value  (float)  When on sale, $sale_price - $total; when not on sale $original_total - $total
 * @property   $coupon_value_trial  (float)  $trial_original_total - $trial_total
 * @property   $currency  (string)  Transaction's currency code
 * @property   $date_access_expires  (string)  Date when access should expire [format (datetime) Y-m-d H:i:s]
 * @property   $date_billing_end  (string)  Date when billing should cease, only when $billing_length is greater than 0 [format (datetime) Y-m-d H:i:s]
 * @property   $date_next_payment  (string)  Date when the next recurring payment is due use function get_next_payment_due_date() instead of accessing directly! [format (datetime) Y-m-d H:i:s]
 * @property   $date_trial_end  (string)  Date when the trial ends for orders with a trial, use function get_trial_end_date() instead of accessing directly! [format (datetime) Y-m-d H:i:s]
 * @property   $gateway_api_mode  (string)  API Mode of the gateway when the transaction was made [test|live]
 * @property   $gateway_customer_id  (string)  Gateway's unique ID for the customer who placed the order
 * @property   $gateway_source_id  (string)  Gateway's unique ID for the card or source to be used for recurring subscriptions (if recurring is supported)
 * @property   $gateway_subscription_id  (string)  Gateway's unique ID for the recurring subscription (if recurring is supported)
 * @property   $id  (int)  WP Post ID of the order
 * @property   $last_retry_rule  (int)  Rule number for current retry step for the order
 * @property   $on_sale  (string)  Whether or not sale pricing was used for the plan [yes|no]
 * @property   $order_key  (string) A unique identifer for the order that can be passed safely in URLs
 * @property   $order_type  (string)  Single or recurring order [single|recurring]
 * @property   $original_total  (float)  Price of the order before applicable sale and coupon adjustments
 * @property   $payment_gateway  (string)  LifterLMS Payment Gateway ID (eg "paypal" or "stripe")
 * @property   $plan_id  (int)  WP Post ID of the purchased access plan
 * @property   $plan_sku   (string)  SKU of the purchased access plan
 * @property   $plan_title  (string)  Title / Name of the purchased access plan
 * @property   $product_id  (int)  WP Post ID of the purchased product
 * @property   $product_sku   (string)  SKU of the purchased product
 * @property   $product_title  (string)  Title / Name of the purchased product
 * @property   $product_type  (string)  Type of product purchased (course or membership)
 * @property   $sale_price  (float)  Sale price before coupon adjustments
 * @property   $sale_value  (float)  $original_total - $sale_price
 * @property   $start_date  (string)  date when access was initially granted; this is used to determine when access expires
 * @property   $title  (string)  Post Title
 * @property   $total  (float)  Actual price of the order, after applicable sale & coupon adjustments
 * @property   $trial_length  (int)  Length of the trial. Combined with $trial_period to determine the actual length of the trial.
 * @property   $trial_offer  (string)  Whether or not there was a trial offer applied to the order [yes|no]
 * @property   $trial_original_total  (float)  Total price of the trial before applicable coupon adjustments
 * @property   $trial_period  (string)  Period for the trial period. [year|month|week|day]
 * @property   $trial_total  (float)  Total price of the trial after applicable coupon adjustments
 * @property   $user_id   (int)  customer WP User ID
 * @property   $user_ip_address  (string)  customer's IP address at time of purchase
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Order model.
 */
class LLMS_Order extends LLMS_Post_Model {

	protected $db_post_type = 'llms_order';
	protected $model_post_type = 'order';

	protected $properties = array(

		'anonymized' => 'yesno',
		'coupon_amount' => 'float',
		'coupon_amout_trial' => 'float',
		'coupon_value' => 'float',
		'coupon_value_trial' => 'float',
		'original_total' => 'float',
		'sale_price' => 'float',
		'sale_value' => 'float',
		'total' => 'float',
		'trial_original_total' => 'float',
		'trial_total' => 'float',

		'access_length' => 'absint',
		'billing_frequency' => 'absint',
		'billing_length' => 'absint',
		'coupon_id' => 'absint',
		'plan_id' => 'absint',
		'product_id' => 'absint',
		'trial_length' => 'absint',
		'user_id' => 'absint',

		'access_expiration' => 'text',
		'access_expires' => 'text',
		'access_period' => 'text',
		'billing_address_1' => 'text',
		'billing_address_2' => 'text',
		'billing_city' => 'text',
		'billing_country' => 'text',
		'billing_email' => 'text',
		'billing_first_name' => 'text',
		'billing_last_name' => 'text',
		'billing_state' => 'text',
		'billing_zip' => 'text',
		'billing_period' => 'text',
		'coupon_code' => 'text',
		'coupon_type' => 'text',
		'coupon_used' => 'text',
		'currency' => 'text',
		'on_sale' => 'text',
		'order_key' => 'text',
		'order_type' => 'text',
		'payment_gateway' => 'text',
		'plan_sku' => 'text',
		'plan_title' => 'text',
		'product_sku' => 'text',
		'product_type' => 'text',
		'title' => 'text',
		'gateway_api_mode' => 'text',
		'gateway_customer_id' => 'text',
		'trial_offer' => 'text',
		'trial_period' => 'text',
		'user_ip_address' => 'text',

		'date_access_expires' => 'text',
		'date_billing_end' => 'text',
		'date_next_payment' => 'text',
		'date_trial_end' => 'text',

	);

	/**
	 * Add an admin-only note to the order visible on the admin panel
	 * notes are recorded using the wp comments API & DB
	 *
	 * @param    string     $note           note content
	 * @param    boolean    $added_by_user  if this is an admin-submitted note adds user info to note meta
	 * @return   null|int                   null on error or WP_Comment ID of the note
	 * @since    3.0.0
	 * @version  3.24.0
	 */
	public function add_note( $note, $added_by_user = false ) {

		if ( ! $note ) {
			return;
		}

		// added by a user from the admin panel
		if ( $added_by_user && is_user_logged_in() && current_user_can( apply_filters( 'lifterlms_admin_order_access', 'manage_options' ) ) ) {

			$user_id = get_current_user_id();
			$user = get_user_by( 'id', $user_id );
			$author = $user->display_name;
			$author_email = $user->user_email;

		} else {

			$user_id = 0;
			$author = _x( 'LifterLMS', 'default order note author', 'lifterlms' );
			$author_email = strtolower( _x( 'LifterLms', 'default order note author', 'lifterlms' ) ) . '@';
			$author_email .= isset( $_SERVER['HTTP_HOST'] ) ? str_replace( 'www.', '', $_SERVER['HTTP_HOST'] ) : 'noreply.com';
			$author_email = sanitize_email( $author_email );

		}

		$note_id = wp_insert_comment( apply_filters( 'llms_add_order_note_content', array(
			'comment_post_ID' => $this->get( 'id' ),
			'comment_author' => $author,
			'comment_author_email' => $author_email,
			'comment_author_url' => '',
			'comment_content' => $note,
			'comment_type' => 'llms_order_note',
			'comment_parent' => 0,
			'user_id' => $user_id,
			'comment_approved' => 1,
			'comment_agent' => 'LifterLMS',
			'comment_date' => current_time( 'mysql' ),
		) ) );

		do_action( 'llms_new_order_note_added', $note_id, $this );

		return $note_id;

	}

	/**
	 * Called after inserting a new order into the database
	 * @return  void
	 * @since   3.0.0
	 * @version 3.0.0
	 */
	protected function after_create() {
		// add a random key that can be passed in the URL and whatever
		$this->set( 'order_key', $this->generate_order_key() );
	}

	/**
	 * Calculate the date when billing should
	 * applicable to orders created from plans with a set # of billing intervals
	 * @return   int
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	private function calculate_billing_end_date() {

		$end = 0;

		$num_payments = $this->get( 'billing_length' );
		if ( $num_payments ) {

			$start = $this->get_date( 'date', 'U' );

			$period = $this->get( 'billing_period' );
			$frequency = $this->get( 'billing_frequency' );

			$end = $start;

			$i = 0;
			while ( $i < $num_payments ) {
				$end = strtotime( '+' . $frequency . ' ' . $period, $end );
				$i++;
			}
		}

		return apply_filters( 'llms_order_calculate_billing_end_date', $end, $this );

	}

	/**
	 * Calculate the next payment due date
	 * @param    string     $format  return format
	 * @return   string
	 * @since    3.10.0
	 * @version  3.12.0
	 */
	private function calculate_next_payment_date( $format = 'Y-m-d H:i:s' ) {

		$start_time = $this->get_date( 'date', 'U' );
		$end_time = $this->get_date( 'date_billing_end', 'U' );
		if ( ! $end_time && $this->get( 'billing_length' ) ) {
			$end_time = $this->calculate_billing_end_date();
			$this->set( 'date_billing_end', date_i18n( 'Y-m-d H:i:s', $end_time ) );
		}
		$next_payment_time = $this->get_date( 'date_next_payment', 'U' );

		// if were on a trial and the trial hasn't ended yet next payment date is the date the trial ends
		if ( $this->has_trial() && ! $this->has_trial_ended() ) {

			$next_payment_time = $this->get_trial_end_date( 'U' );

		} else {

			// assume we'll start from the order start date
			$from_time = $start_time;

			if ( $next_payment_time && $next_payment_time < llms_current_time( 'timestamp' ) ) {
				// if we have a saved next payment that's old we can calculate from there

				$from_time = $next_payment_time;

			} else {

				// check previous transactions and get the date from there
				// this will be true of orders created prior to 3.10 when no payment dates were saved
				$last_txn = $this->get_last_transaction( array( 'llms-txn-succeeded', 'llms-txn-refunded' ), 'recurring' );
				$last_txn_time = $last_txn ? $last_txn->get_date( 'date', 'U' ) : 0;
				if ( $last_txn_time && $last_txn_time < llms_current_time( 'timestamp' ) ) {
					$from_time = $last_txn_time;
				}
			}

			$period = $this->get( 'billing_period' );
			$frequency = $this->get( 'billing_frequency' );
			$next_payment_time = strtotime( '+' . $frequency . ' ' . $period, $from_time );

			// Make sure the next payment is more than 2 hours in the future
			// this ensures changes to the site's timezone because of daylight savings will never cause a 2nd renewal payment to be processed on the same day
			// thanks WooCommerce Subscriptions <3
			$i = 1;
			while ( $next_payment_time < ( llms_current_time( 'timestamp', true ) + 2 * HOUR_IN_SECONDS ) && $i < 3000 ) {
				$next_payment_time = strtotime( '+' . $frequency . ' ' . $period, $next_payment_time );
				$i++;
			}
		}// End if().

		// if the next payment is after the end time (where applicaple)
		if ( 0 != $end_time && ( $next_payment_time + 23 * HOUR_IN_SECONDS ) > $end_time ) {
			$ret = '';
		} elseif ( $next_payment_time > 0 ) {
			$ret = date_i18n( $format, $next_payment_time );
		}

		return apply_filters( 'llms_order_calculate_next_payment_date', $ret, $format, $this );

	}

	/**
	 * Calculate the end date of the trial
	 * @param    string     $format  desired return format of the date
	 * @return   string
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	private function calculate_trial_end_date( $format = 'Y-m-d H:i:s' ) {

		$start = $this->get_date( 'date', 'U' ); // start with the date the order was initially created

		$length = $this->get( 'trial_length' );
		$period = $this->get( 'trial_period' );

		$end = strtotime( '+' . $length . ' ' . $period, $start );

		$ret = date_i18n( $format, $end );

		return apply_filters( 'llms_order_calculate_trial_end_date', $ret, $format, $this );

	}

	/**
	 * Determine if the order can be retried for recurring payments
	 * @return   boolean
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	public function can_be_retried() {

		// only recurring orders can be retried
		if ( ! $this->is_recurring() ) {
			return false;
		}

		if ( 'yes' !== get_option( 'lifterlms_recurring_payment_retry', 'yes' ) ) {
			return false;
		}

		// only active & on-hold orders qualify for a retry
		if ( ! in_array( $this->get( 'status' ), array( 'llms-active', 'llms-on-hold' ) ) ) {
			return false;
		}

		// if the gateway isn't active or the gateway doesn't support recurring retries
		$gateway = $this->get_gateway();
		if ( is_wp_error( $gateway ) || ! $gateway->supports( 'recurring_retry' ) ) {
			return false;
		}

		// if we're here, we can retry
		return true;

	}

	/**
	 * Determine if an order can be resubscribed to
	 * @return   bool
	 * @since    3.19.0
	 * @version  3.19.0
	 */
	public function can_resubscribe() {

		$ret = false;

		if ( $this->is_recurring() ) {

			$allowed_statuses = apply_filters( 'llms_order_status_can_resubscribe_from', array(
				'llms-on-hold',
				'llms-pending',
				'llms-pending-cancel',
			) );
			$ret = in_array( $this->get( 'status' ), $allowed_statuses );

		}

		return apply_filters( 'llms_order_can_resubscribe', $ret, $this );

	}

	/**
	 * Generate an order key for the order
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function generate_order_key() {
		return apply_filters( 'lifterlms_generate_order_key', uniqid( 'order-' ) );
	}

	/**
	 * Determine the date when access will expire
	 * based on the access settings of the access plan
	 * at the $start_date of acess
	 *
	 * @param    string     $format  date format
	 * @return   string              date string
	 *                               "Lifetime Access" for plans with lifetime access
	 *                               "To be Determined" for limited date when access hasn't started yet
	 * @since    3.0.0
	 * @version  3.19.0
	 */
	public function get_access_expiration_date( $format = 'Y-m-d' ) {

		$type = $this->get( 'access_expiration' );

		$ret = $this->get_date( 'date_access_expires', $format );
		if ( ! $ret ) {

			switch ( $type ) {
				case 'lifetime':
					$ret = __( 'Lifetime Access', 'lifterlms' );
				break;

				case 'limited-date':
					$ret = date_i18n( $format, ( $this->get_date( 'access_expires', 'U' ) + ( DAY_IN_SECONDS - 1 ) ) );
				break;

				case 'limited-period':
					if ( $this->get( 'start_date' ) ) {
						$time = strtotime( '+' . $this->get( 'access_length' ) . ' ' . $this->get( 'access_period' ), $this->get_date( 'start_date', 'U' ) ) + ( DAY_IN_SECONDS - 1 );
						$ret = date_i18n( $format, $time );
					} else {
						$ret = __( 'To be Determined', 'lifterlms' );
					}
				break;

				default:
					$ret = apply_filters( 'llms_order_' . $type . '_access_expiration_date', $type, $this, $format );

			}
		}

		return apply_filters( 'llms_order_get_access_expiration_date', $ret, $this, $format );

	}

	/**
	 * Get the current status of a student's access based on the access plan data
	 * stored on the order at the time of purchase
	 * @return   string        'inactive' if the order is refunded, failed, pending, etc...
	 *                         'expired'  if access has expired according to $this->get_access_expiration_date()
	 *                         'active'   otherwise
	 * @since    3.0.0
	 * @version  3.19.0
	 */
	public function get_access_status() {

		$statuses = apply_filters( 'llms_order_allow_access_stasuses', array(
			'llms-active',
			'llms-completed',
			'llms-pending-cancel',
			// recurring orders can expire but still grant access
			// eg: 3monthly payments grants 1 year of access
			// on the 4th month the order will be marked as expired
			// but the access has not yet expired based on the data below
			'llms-expired',
		) );

		// if the order doesn't have one of the allowed statuses
		// return 'inactive' and don't bother checking expiration data
		if ( ! in_array( $this->get( 'status' ), $statuses ) ) {

			return 'inactive';

		}

		// get the expiration date as a timestamp
		$expires = $this->get_access_expiration_date( 'U' );

		// a translated non-numeric string will be returned for lifetime access
		// so if we have a timestamp we should compare it against the current time
		// to determine if access has expired
		if ( is_numeric( $expires ) ) {

			$now = llms_current_time( 'timestamp' );

			// expiration date is in the past
			// eg: the access has already expired
			if ( $expires < $now ) {

				return 'expired';

			}
		}

		// we're active
		return 'active';

	}

	/**
	 * Retrieve arguments passed to order-related events processed by the action scheduler
	 * @return   array
	 * @since    3.19.0
	 * @version  3.19.0
	 */
	protected function get_action_args() {
		return array(
			'order_id' => $this->get( 'id' ),
		);
	}

	/**
	 * Get the formatted coupon amount with a currency symbol or percentage
	 * @param    string     $payment  coupon discount type, either 'regular' or 'trial'
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_coupon_amount( $payment = 'regular' ) {

		if ( 'regular' === $payment ) {
			$amount = $this->get( 'coupon_amount' );
		} elseif ( 'trial' === $payment ) {
			$amount = $this->get( 'coupon_amount_trial' );
		}

		$type = $this->get( 'coupon_type' );
		if ( 'percent' === $type ) {
			$amount = $amount . '%';
		} elseif ( 'dollar' === $type ) {
			$amount = llms_price( $amount );
		}
		return $amount;

	}

	/**
	 * Retreive the customer's full name
	 * @return   string
	 * @since    3.0.0
	 * @version  3.18.0
	 */
	public function get_customer_name() {
		if ( 'yes' === $this->get( 'anonymized' ) ) {
			return __( 'Anonymous', 'lifterlms' );
		}
		return trim( $this->get( 'billing_first_name' ) . ' ' . $this->get( 'billing_last_name' ) );
	}

	/**
	 * An array of default arguments to pass to $this->create()
	 * when creating a new post
	 * @param    string  $title   Title to create the post with
	 * @return   array
	 * @since    3.0.0
	 * @version  3.10.0
	 */
	protected function get_creation_args( $title = '' ) {

		if ( empty( $title ) ) {
			$title = sprintf( __( 'Order &ndash; %s', 'lifterlms' ), strftime( _x( '%b %d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'lifterlms' ), current_time( 'timestamp' ) ) );
		}

		return apply_filters( 'llms_' . $this->model_post_type . '_get_creation_args', array(
			'comment_status' => 'closed',
			'ping_status'	 => 'closed',
			'post_author' 	 => 1,
			'post_content'   => '',
			'post_excerpt'   => '',
			'post_password'	 => uniqid( 'order_' ),
			'post_status' 	 => 'llms-' . apply_filters( 'llms_default_order_status', 'pending' ),
			'post_title'     => $title,
			'post_type' 	 => $this->get( 'db_post_type' ),
		), $this );
	}

	/**
	 * Retreive the payment gateway instance for the order's selected payment gateway
	 * @return   instance of an LLMS_Gateway
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function get_gateway() {
		$gateways = LLMS()->payment_gateways();
		$gateway = $gateways->get_gateway_by_id( $this->get( 'payment_gateway' ) );
		if ( $gateway && ( $gateway->is_enabled() || is_admin() ) ) {
			return $gateway;
		} else {
			return new WP_Error( 'error', sprintf( __( 'Payment gateway %s could not be located or is no longer enabled', 'lifterlms' ), $this->get( 'payment_gateway' ) ) );
		}
	}

	/**
	 * Get the initial payment amount due on checkout
	 * This will always be the value of "total" except when the product has a trial
	 * @return   mixed
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_initial_price( $price_args = array(), $format = 'html' ) {

		if ( $this->has_trial() ) {
			$price = 'trial_total';
		} else {
			$price = 'total';
		}

		return $this->get_price( $price, $price_args, $format );
	}


	/**
	 * Get an array of the order notes
	 * Each note is actually a WordPress comment
	 * @param    integer    $number  number of comments to return
	 * @param    integer    $page    page number for pagination
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_notes( $number = 10, $page = 1 ) {

		$comments = get_comments( array(
			'status' => 'approve',
			'number'  => $number,
			'offset'  => ( $page - 1 ) * $number,
			'post_id' => $this->get( 'id' ),
		) );

		return $comments;

	}

	/**
	 * Retrieve an LLMS_Post_Model object for the associated product
	 * @return   obj       LLMS_Course / LLMS_Membership instance
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_product() {
		return llms_get_post( $this->get( 'product_id' ) );
	}

	/**
	 * Retrieve the last (most recent) transaction processed for the order
	 * @param    array|string  $status  filter by status (see transaction statuses)
	 * @param    array|string  $type    filter by type [recurring|single|trial]
	 * @return   obj|false              instance of the LLMS_Transaction or false if none found
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_last_transaction( $status = 'any', $type = 'any' ) {
		$txns = $this->get_transactions( array(
			'per_page' => 1,
			'status' => $status,
			'type' => $type,
		) );
		if ( $txns['count'] ) {
			return array_pop( $txns['transactions'] );
		}
		return false;
	}

	/**
	 * Retrieve the date of the last (most recent) transaction
	 * @param    array|string  $status  filter by status (see transaction statuses)
	 * @param    array|string  $type    filter by type [recurring|single|trial]
	 * @param    string        $format  date format of the return
	 * @return   string|false           date or false if none found
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_last_transaction_date( $status = 'llms-txn-succeeded', $type = 'any', $format = 'Y-m-d H:i:s' ) {
		$txn = $this->get_last_transaction( $status, $type );
		if ( $txn ) {
			return $txn->get_date( 'date', $format );
		} else {
			return false;
		}
	}

	/**
	 * Retrive the due date of the next payment according to access plan terms
	 * @param    string     $format  date format to return the date in (see php date())
	 * @return   string
	 * @since    3.0.0
	 * @version  3.19.0
	 */
	public function get_next_payment_due_date( $format = 'Y-m-d H:i:s' ) {

		// single payments will never have a next payment date
		if ( ! $this->is_recurring() ) {
			return new WP_Error( 'not-recurring', __( 'Order is not recurring', 'lifterlms' ) );
		} elseif ( ! in_array( $this->get( 'status' ), array( 'llms-active', 'llms-failed', 'llms-on-hold', 'llms-pending', 'llms-pending-cancel' ) ) ) {
			return new WP_Error( 'invalid-status', __( 'Invalid order status', 'lifterlms' ), $this->get( 'status' ) );
		}

		// retrieve the saved due date
		$next_payment_date = $this->get_date( 'date_next_payment', 'U' );

		// calculate it if not saved
		if ( ! $next_payment_date ) {
			$next_payment_date = $this->calculate_next_payment_date( 'U' );
			if ( ! $next_payment_date ) {
				return new WP_Error( 'plan-ended', __( 'No more payments due', 'lifterlms' ) );
			}
		}

		return date_i18n( $format, apply_filters( 'llms_order_get_next_payment_due_date', $next_payment_date, $this, $format ) );

	}

	/**
	 * Get configured payment retry rules
	 * @return   array
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	private function get_retry_rules() {

		$rules = array(
			array(
				'delay' => HOUR_IN_SECONDS * 12,
				'status' => 'on-hold',
				'notifications' => false,
			),
			array(
				'delay' => DAY_IN_SECONDS,
				'status' => 'on-hold',
				'notifications' => true,
			),
			array(
				'delay' => DAY_IN_SECONDS * 2,
				'status' => 'on-hold',
				'notifications' => true,
			),
			array(
				'delay' => DAY_IN_SECONDS * 3,
				'status' => 'on-hold',
				'notifications' => true,
			),
		);

		return apply_filters( 'llms_order_automatic_retry_rules', $rules, $this );

	}

	/**
	 * SQL query to retrieve total amounts for transactions by type
	 * @param    stirng  $type  'amount' or 'refund_amount'
	 * @return   float
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_transaction_total( $type = 'amount' ) {

		$statuses = array( 'llms-txn-refunded' );

		if ( 'amount' === $type ) {
			$statuses[] = 'llms-txn-succeeded';
		}

		$post_statuses = '';
		foreach ( $statuses as $i => $status ) {
			$post_statuses .= " p.post_status = '$status'";
			if ( $i + 1 < count( $statuses ) ) {
				$post_statuses .= 'OR';
			}
		}

		global $wpdb;
		$grosse = $wpdb->get_var( $wpdb->prepare(
			"SELECT SUM( m2.meta_value )
			 FROM $wpdb->posts AS p
			 LEFT JOIN $wpdb->postmeta AS m1 ON m1.post_id = p.ID -- join for the ID
			 LEFT JOIN $wpdb->postmeta AS m2 ON m2.post_id = p.ID -- get the actual amounts
			 WHERE p.post_type = 'llms_transaction'
			   AND ( $post_statuses )
			   AND m1.meta_key = '{$this->meta_prefix}order_id'
			   AND m1.meta_value = %d
			   AND m2.meta_key = '{$this->meta_prefix}{$type}'
			;"
		, array( $this->get( 'id' ) ) ) );

		return floatval( $grosse );
	}

	/**
	 * Get the start date for the order
	 * gets the date of the first initially successful transaction
	 * if none found, uses the created date of the order
	 * @param    string     $format  desired return format of the date
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_start_date( $format = 'Y-m-d H:i:s' ) {
		// get the first recorded transaction
		// refunds are okay b/c that would have initially given the user access
		$txns = $this->get_transactions( array(
			'order' => 'ASC',
			'orderby' => 'date',
			'per_page' => 1,
			'status' => array( 'llms-txn-succeeded', 'llms-txn-refunded' ),
			'type' => 'any',
		) );
		if ( $txns['count'] ) {
			$txn = array_pop( $txns['transactions'] );
			$date = $txn->get_date( 'date', $format );
		} else {
			$date = $this->get_date( 'date', $format );
		}
		return apply_filters( 'llms_order_get_start_date', $date, $this );
	}

	/**
	 * Retrieve an array of transactions associated with the order according to supplied arguments
	 * @param    array      $args  array of query argument data, see example of arguments below
	 * @return   array
	 * @since    3.0.0
	 * @version  3.10.0
	 */
	public function get_transactions( $args = array() ) {

		extract( wp_parse_args( $args, array(
			'status' => 'any', // string or array or post statuses
			'type' => 'any',   // string or array of transaction types [recurring|single|trial]
			'per_page' => 50,  // int, number of transactions to return
			'paged' => 1,      // int, page number of transactions to return
			'order' => 'DESC',    //
			'orderby' => 'date',  // field to order results by
		) ) );

		// assume any and use this to check for valid statuses
		$statuses = llms_get_transaction_statuses();

		// check statuses
		if ( 'any' !== $statuses ) {

			// if status is a string, ensure it's a valid status
			if ( is_string( $status ) && in_array( $status, $statuses ) ) {
				$statuses = array( $status );
			} elseif ( is_array( $status ) ) {
				$temp = array();
				foreach ( $status as $stat ) {
					if ( in_array( $stat, $statuses ) ) {
						$temp[] = $stat;
					}
				}
				$statuses = $temp;
			}
		}

		// setup type meta query
		$types = array(
			'relation' => 'OR',
		);

		if ( 'any' === $type ) {
			$types[] = array(
				'key' => $this->meta_prefix . 'payment_type',
				'value' => 'recurring',
			);
			$types[] = array(
				'key' => $this->meta_prefix . 'payment_type',
				'value' => 'single',
			);
			$types[] = array(
				'key' => $this->meta_prefix . 'payment_type',
				'value' => 'trial',
			);
		} elseif ( is_string( $type ) ) {
			$types[] = array(
				'key' => $this->meta_prefix . 'payment_type',
				'value' => $type,
			);
		} elseif ( is_array( $type ) ) {
			foreach ( $type as $t ) {
				$types[] = array(
					'key' => $this->meta_prefix . 'payment_type',
					'value' => $t,
				);
			}
		}

		// execute the query
		$query = new WP_Query( apply_filters( 'llms_order_get_transactions_query', array(
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => $this->meta_prefix . 'order_id',
					'value' => $this->get( 'id' ),
				),
				$types,
			),
			'order' => $order,
			'orderby' => $orderby,
			'post_status' => $statuses,
			'post_type' => 'llms_transaction',
			'posts_per_page' => $per_page,
			'paged' => $paged,
		) ), $this, $status );

		$transactions = array();

		foreach ( $query->posts as $post ) {
			$transactions[ $post->ID ] = llms_get_post( $post );
		}

		return array(
			'count' => count( $query->posts ),
			'page' => $paged,
			'pages' => $query->max_num_pages,
			'transactions' => $transactions,
		);

	}

	/**
	 * Retrieve the date when a trial will end
	 * @param    string     $format  date return format
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_trial_end_date( $format = 'Y-m-d H:i:s' ) {

		if ( ! $this->has_trial() ) {

			$trial_end_date = '';

		} else {

			// retrieve the saved end date
			$trial_end_date = $this->get_date( 'date_trial_end', $format );

			// if not saved, calculate it
			if ( ! $trial_end_date ) {

				$trial_end_date = $this->calculate_trial_end_date( $format );

			}
		}

		return apply_filters( 'llms_order_get_trial_end_date', $trial_end_date, $this );

	}

	/**
	 * Gets the total revenue of an order
	 * @param    string     $type    revenue type [grosse|net]
	 * @return   float
	 * @since    3.0.0
	 * @version  3.1.3 - handle legacy orders
	 */
	public function get_revenue( $type = 'net' ) {

		if ( $this->is_legacy() ) {

			$amount = $this->get( 'total' );

		} else {

			$amount = $this->get_transaction_total( 'amount' );

			if ( 'net' === $type ) {

				$refunds = $this->get_transaction_total( 'refund_amount' );

				$amount = $amount - $refunds;

			}
		}

		return apply_filters( 'llms_order_get_revenue' , $amount, $type, $this );

	}

	/**
	 * Get a link to view the order on the student dashboard
	 * @return   string
	 * @since    3.0.0
	 * @version  3.8.0
	 */
	public function get_view_link() {

		$link = llms_get_endpoint_url( 'orders', $this->get( 'id' ), llms_get_page_url( 'myaccount' ) );
		return apply_filters( 'llms_order_get_view_link', $link, $this );

	}

	/**
	 * Determine if the student associated with this order has access
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function has_access() {
		return ( 'active' === $this->get_access_status() ) ? true : false;
	}

	/**
	 * Determine if a coupon was used
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function has_coupon() {
		return ( 'yes' === $this->get( 'coupon_used' ) );
	}

	/**
	 * Determine if there was a discount applied to this order
	 * via either a sale or a coupon
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function has_discount() {
		return ( $this->has_coupon() || $this->has_sale() );
	}

	/**
	 * Determine if the access plan was on sale during the purchase
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function has_sale() {
		return ( 'yes' === $this->get( 'on_sale' ) );
	}

	/**
	 * Determine if theres a payment scheduled for the order
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function has_scheduled_payment() {
		$date = $this->get_next_payment_due_date();
		return is_wp_error( $date ) ? false : true;
	}

	/**
	 * Determine if the order has a trial
	 * @return   boolean     true if has a trial, false if it doesn't
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function has_trial() {
		return ( $this->is_recurring() && 'yes' === $this->get( 'trial_offer' ) );
	}

	/**
	 * Determine if the trial period has ended for the order
	 * @return   boolean     true if ended, false if not ended
	 * @since    3.0.0
	 * @version  3.10.0
	 */
	public function has_trial_ended() {
		return ( llms_current_time( 'timestamp' ) >= $this->get_trial_end_date( 'U' ) );
	}

	/**
	 * Initialize a pending order
	 * Used during checkout
	 * Assumes all data passed in has already been validated
	 * @param    obj     $person   LLMS_Student
	 * @param    obj     $plan     LLMS_Access_Plan
	 * @param    obj     $gateway  LLMS_Gateway
	 * @param    mixed   $coupon   LLMS_Coupon or false
	 * @return   obj               $this
	 * @since    3.8.0
	 * @version  3.10.0
	 */
	public function init( $person, $plan, $gateway, $coupon = false ) {

		// user related information
		$this->set( 'user_id', $person->get_id() );
		$this->set( 'user_ip_address', llms_get_ip_address() );
		$this->set( 'billing_address_1', $person->get( 'billing_address_1' ) );
		$this->set( 'billing_address_2', $person->get( 'billing_address_2' ) );
		$this->set( 'billing_city', $person->get( 'billing_city' ) );
		$this->set( 'billing_country', $person->get( 'billing_country' ) );
		$this->set( 'billing_email', $person->get( 'user_email' ) );
		$this->set( 'billing_first_name', $person->get( 'first_name' ) );
		$this->set( 'billing_last_name', $person->get( 'last_name' ) );
		$this->set( 'billing_state', $person->get( 'billing_state' ) );
		$this->set( 'billing_zip', $person->get( 'billing_zip' ) );
		$this->set( 'billing_phone', $person->get( 'phone' ) );

		// access plan data
		$this->set( 'plan_id', $plan->get( 'id' ) );
		$this->set( 'plan_title', $plan->get( 'title' ) );
		$this->set( 'plan_sku', $plan->get( 'sku' ) );

		// product data
		$product = $plan->get_product();
		$this->set( 'product_id', $product->get( 'id' ) );
		$this->set( 'product_title', $product->get( 'title' ) );
		$this->set( 'product_sku', $product->get( 'sku' ) );
		$this->set( 'product_type', $plan->get_product_type() );

		$this->set( 'payment_gateway', $gateway->get_id() );
		$this->set( 'gateway_api_mode', $gateway->get_api_mode() );

		// trial data
		if ( $plan->has_trial() ) {
			$this->set( 'trial_offer', 'yes' );
			$this->set( 'trial_length', $plan->get( 'trial_length' ) );
			$this->set( 'trial_period', $plan->get( 'trial_period' ) );
			$trial_price = $plan->get_price( 'trial_price', array(), 'float' );
			$this->set( 'trial_original_total', $trial_price );
			$trial_total = $coupon ? $plan->get_price_with_coupon( 'trial_price', $coupon, array(), 'float' ) : $trial_price;
			$this->set( 'trial_total', $trial_total );
			$this->set( 'date_trial_end', $this->calculate_trial_end_date() );
		} else {
			$this->set( 'trial_offer', 'no' );
		}

		$price = $plan->get_price( 'price', array(), 'float' );
		$this->set( 'currency', get_lifterlms_currency() );

		// price data
		if ( $plan->is_on_sale() ) {
			$price_key = 'sale_price';
			$this->set( 'on_sale', 'yes' );
			$sale_price = $plan->get( 'sale_price', array(), 'float' );
			$this->set( 'sale_price', $sale_price );
			$this->set( 'sale_value', $price - $sale_price );
		} else {
			$price_key = 'price';
			$this->set( 'on_sale', 'no' );
		}

		// store original total before any discounts
		$this->set( 'original_total', $price );

		// get the actual total due after discounts if any are applicable
		$total = $coupon ? $plan->get_price_with_coupon( $price_key, $coupon, array(), 'float' ) : $$price_key;
		$this->set( 'total', $total );

		// coupon data
		if ( $coupon ) {
			$this->set( 'coupon_id', $coupon->get( 'id' ) );
			$this->set( 'coupon_amount', $coupon->get( 'coupon_amount' ) );
			$this->set( 'coupon_code', $coupon->get( 'title' ) );
			$this->set( 'coupon_type', $coupon->get( 'discount_type' ) );
			$this->set( 'coupon_used', 'yes' );
			$this->set( 'coupon_value', $$price_key - $total );
			if ( $plan->has_trial() && $coupon->has_trial_discount() ) {
				$this->set( 'coupon_amount_trial', $coupon->get( 'trial_amount' ) );
				$this->set( 'coupon_value_trial', $trial_price - $trial_total );
			}
		} else {
			$this->set( 'coupon_used', 'no' );
		}

		// get all billing schedule related information
		$this->set( 'billing_frequency', $plan->get( 'frequency' ) );
		if ( $plan->is_recurring() ) {
			$this->set( 'billing_length', $plan->get( 'length' ) );
			$this->set( 'billing_period', $plan->get( 'period' ) );
			$this->set( 'order_type', 'recurring' );
			if ( $plan->get( 'length' ) ) {
				$this->set( 'date_billing_end', date_i18n( 'Y-m-d H:i:s', $this->calculate_billing_end_date() ) );
			}
			$this->set( 'date_next_payment', $this->calculate_next_payment_date() );
		} else {
			$this->set( 'order_type', 'single' );
		}

		$this->set( 'access_expiration', $plan->get( 'access_expiration' ) );

		// get access related data so when payment is complete we can calculate the actual expiration date
		if ( $plan->can_expire() ) {
			$this->set( 'access_expires', $plan->get( 'access_expires' ) );
			$this->set( 'access_length', $plan->get( 'access_length' ) );
			$this->set( 'access_period', $plan->get( 'access_period' ) );
		}

		do_action( 'lifterlms_new_pending_order', $this, $person );

		return $this;

	}

	/**
	 * Determine if the order is a legacy order migrated from 2.x
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function is_legacy() {
		return ( 'publish' === $this->get( 'status' ) );
	}

	/**
	 * Determine if the order is recurring or singular
	 * @return   boolean      true if recurring, false if not
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function is_recurring() {
		return $this->get( 'order_type' ) === 'recurring';
	}

	/**
	 * Schedule access expiration
	 * @return   void
	 * @since    3.19.0
	 * @version  3.19.0
	 */
	public function maybe_schedule_expiration() {

		// get epiration date based on setting
		$expires = $this->get_access_expiration_date( 'U' );

		// will return a timestamp or "Lifetime Access as a string"
		if ( is_numeric( $expires ) ) {

			$this->unschedule_expiration();
			wc_schedule_single_action( $expires, 'llms_access_plan_expiration', $this->get_action_args() );

		}
	}

	/**
	 * Schedules the next payment due on a recurring order
	 * Can be called witnout consequence on a single payment order
	 * Will always unschedule the scheduled action (if one exists) before scheduling another
	 * @return   void
	 * @since    3.0.0
	 * @version  3.12.0
	 */
	public function maybe_schedule_payment( $recalc = true ) {

		if ( ! $this->is_recurring() ) {
			return;
		}

		if ( $recalc ) {
			$this->set( 'date_next_payment', $this->calculate_next_payment_date() );
		}

		$date = $this->get_next_payment_due_date( 'U' );

		// unschedule and reschedule
		if ( $date && ! is_wp_error( $date ) ) {

			// unschedule the next action (does nothing if no action scheduled)
			$this->unschedule_recurring_payment();

			// convert our date to UTC before passing to the scheduler
			$date = $date - ( HOUR_IN_SECONDS * get_option( 'gmt_offset' ) );

			// schedule the payment
			wc_schedule_single_action( $date, 'llms_charge_recurring_payment', array(
				'order_id' => $this->get( 'id' ),
			) );

		} elseif ( is_wp_error( $date ) ) {

			if ( 'plan-ended' === $date->get_error_code() ) {

				// unschedule the next action (does nothing if no action scheduled)
				$this->unschedule_recurring_payment();

				// add a note that the plan has completed
				$this->add_note( __( 'Order payment plan completed.', 'lifterlms' ) );

			}
		}

	}

	/**
	 * Handles scheduling recurring payment retries when the gateway supports them
	 * @return   void
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	public function maybe_schedule_retry() {

		if ( ! $this->can_be_retried() ) {
			return;
		}

		$current_rule = $this->get( 'last_retry_rule' );
		if ( '' === $current_rule ) {
			$current_rule = 0;
		} else {
			$current_rule = $current_rule + 1;
		}
		$rules = $this->get_retry_rules();

		if ( isset( $rules[ $current_rule ] ) ) {

			$rule = $rules[ $current_rule ];

			$next_payment_time = current_time( 'timestamp' ) + $rule['delay'];

			// update the status
			$this->set_status( $rule['status'] );

			// set the next payment date based on the rule's delay
			$this->set_date( 'next_payment', date_i18n( 'Y-m-d H:i:s', $next_payment_time ) );

			// save the rule for reference on potential future retries
			$this->set( 'last_retry_rule', $current_rule );

			// if notifications should be sent, trigger them
			if ( $rule['notifications'] ) {
				do_action( 'llms_send_automatic_payment_retry_notification', $this );
			}

			$this->add_note( sprintf( esc_html__( 'Automatic retry attempt scheduled for %s', 'lifterlms' ), date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $next_payment_time ) ) );

			// generic action
			do_action( 'llms_automatic_payment_retry_scheduled', $this );

			// we are out of rules, fail the order, move on with our lives
		} else {

			$this->set_status( 'failed' );
			$this->set( 'last_retry_rule', '' );

			$this->add_note( esc_html__( 'Maximum retry attempts reached.', 'lifterlms' ) );

			do_action( 'llms_automatic_payment_maximum_retries_reached', $this );

		}// End if().

	}

	/**
	 * Record a transaction for the order
	 * @param    array      $data    optional array of additional data to store for the transcation
	 * @return   obj        instance of LLMS_Transaction for the created transaction
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function record_transaction( $data = array() ) {

		extract( array_merge(
			array(
				'amount' => 0,
				'completed_date' => current_time( 'mysql' ),
				'customer_id' => '',
				'fee_amount' => 0,
				'source_id' => '',
				'source_description' => '',
				'transaction_id' => '',
				'status' => 'llms-txn-succeeded',
				'payment_gateway' => $this->get( 'payment_gateway' ),
				'payment_type' => 'single',
			),
			$data
		) );

		$txn = new LLMS_Transaction( 'new', $this->get( 'id' ) );

		$txn->set( 'api_mode', $this->get( 'gateway_api_mode' ) );
		$txn->set( 'amount', $amount );
		$txn->set( 'currency', $this->get( 'currency' ) );
		$txn->set( 'gateway_completed_date', date_i18n( 'Y-m-d h:i:s', strtotime( $completed_date ) ) );
		$txn->set( 'gateway_customer_id', $customer_id );
		$txn->set( 'gateway_fee_amount', $fee_amount );
		$txn->set( 'gateway_source_id', $source_id );
		$txn->set( 'gateway_source_description', $source_description );
		$txn->set( 'gateway_transaction_id', $transaction_id );
		$txn->set( 'order_id', $this->get( 'id' ) );
		$txn->set( 'payment_gateway', $payment_gateway );
		$txn->set( 'payment_type', $payment_type );
		$txn->set( 'status', $status );

		return $txn;

	}

	/**
	 * Date field setter for date fields that require things to be updated when their value changes
	 * This is mainly used to allow updating dates which are editable from the admin panel which
	 * should trigger additional actions when updated
	 *
	 * Settable dates: date_next_payment, date_trial_end, date_access_expires
	 *
	 * @param    string     $date_key  date field to set
	 * @param    string     $date_val  date string or a unix time stamp
	 * @since    3.10.0
	 * @version  3.19.0
	 */
	public function set_date( $date_key, $date_val ) {

		// convert to timestamp if not already a timestamp
		if ( ! is_numeric( $date_val ) ) {
			$date_val = strtotime( $date_val );
		}

		$this->set( 'date_' . $date_key, date( 'Y-m-d H:i:s', $date_val ) );

		switch ( $date_key ) {

			// reschedule access expiration
			case 'access_expires':
				$this->maybe_schedule_expiration();
			break;

			// additionally update the next payment date
			// & don't break because we want to reschedule payments too
			case 'trial_end':
				$this->set_date( 'next_payment', $this->calculate_next_payment_date( 'U' ) );

				// everything else reschedule's payments
			default:
				$this->maybe_schedule_payment( false );

		}

	}

	/**
	 * Update the status of an order
	 * @param    string     $status  status name, accepts unprefixed statuses
	 * @return   void
	 * @since    3.8.0
	 * @version  3.10.0
	 */
	public function set_status( $status ) {

		if ( false === strpos( $status, 'llms-' ) ) {
			$status = 'llms-' . $status;
		}

		$statuses = array_keys( llms_get_order_statuses( $this->get( 'order_type' ) ) );

		if ( in_array( $status, $statuses ) ) {
			$this->set( 'status', $status );
		}

	}

	/**
	 * Record the start date of the access plan and schedule expiration
	 * if expiration is required in the future
	 * @return   void
	 * @since    3.0.0
	 * @version  3.19.0
	 */
	public function start_access() {

		// only start access if access isn't already started
		$date = $this->get( 'start_date' );
		if ( ! $date ) {

			// set the start date to now
			$date = llms_current_time( 'mysql' );
			$this->set( 'start_date', $date );

		}

		$this->unschedule_expiration();

		// setup expiration
		if ( in_array( $this->get( 'access_expiration' ), array( 'limited-date', 'limited-period' ) ) ) {

			$expires_date = $this->get_access_expiration_date( 'Y-m-d H:i:s' );
			$this->set( 'date_access_expires', $expires_date );
			$this->maybe_schedule_expiration();

		}

	}

	/**
	 * Cancels a scheduled expiration action
	 * does nothing if no expiration is scheduled
	 * @return   void
	 * @since    3.19.0
	 * @version  3.19.0
	 */
	public function unschedule_expiration() {

		if ( wc_next_scheduled_action( 'llms_access_plan_expiration', $this->get_action_args() ) ) {
			wc_unschedule_action( 'llms_access_plan_expiration', $this->get_action_args() );
		}

	}

	/**
	 * Cancels a scheduled recurring payment action
	 * does nothing if no payments are scheduled
	 * @return   void
	 * @since    3.0.0
	 * @version  3.19.0
	 */
	public function unschedule_recurring_payment() {

		if ( wc_next_scheduled_action( 'llms_charge_recurring_payment', $this->get_action_args() ) ) {
			wc_unschedule_action( 'llms_charge_recurring_payment', $this->get_action_args() );
		}

	}

}
