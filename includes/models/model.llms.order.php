<?php
/**
 * LifterLMS Order Model
 * @since  3.0.0
 * @version  3.0.0
 *
 * @property   $access_expiration  (string)  Expiration type [lifetime|limited-period|limited-date]
 * @property   $access_expires  (string)  Date access expires in m/d/Y format. Only applicable when $access_expiration is "limited-date"
 * @property   $access_length  (int)  Length of access from time of purchase, combine with $access_period. Only applicable when $access_expiration is "limited-period"
 * @property   $access_period  (string)  Time period of access from time of purchase, combine with $access_length. Only applicable when $access_expiration is "limited-period" [year|month|week|day]
 *
 * @property   $billing_address_1  (string)  customer billing address line 1
 * @property   $billing_address_2  (string)  customer billing address line 2
 * @property   $billing_city  (string)  customer billing city
 * @property   $billing_country  (string)  customer billing country, 2-digit ISO code
 * @property   $billing_email  (string)  customer email address
 * @property   $billing_first_name  (string)  customer first name
 * @property   $billing_last_name  (string)  customer last name
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
 *
 * @property   $currency  (string)  Transaction's currency code
 *
 * @property   $gateway_api_mode  (string)  API Mode of the gateway when the transaction was made [test|live]
 * @property   $gateway_customer_id  (string)  Gateway's unique ID for the customer who placed the order
 * @property   $gateway_source_id  (string)  Gateway's unique ID for the card or source to be used for recurring subscriptions (if recurring is supported)
 * @property   $gateway_subscription_id  (string)  Gateway's unique ID for the recurring subscription (if recurring is supported)
 *
 * @property   $id  (int)  WP Post ID of the order
 *
 * @property   $on_sale  (string)  Whether or not sale pricing was used for the plan [yes|no]
 * @property   $order_key  (string) A unique identifer for the order that can be passed safely in URLs
 * @property   $order_type  (string)  Single or recurring order [single|recurring]
 * @property   $original_total  (float)  Price of the order before applicable sale and coupon adjustments
 *
 * @property   $payment_gateway  (string)  LifterLMS Payment Gateway ID (eg "paypal" or "stripe")
 *
 * @property   $plan_id  (int)  WP Post ID of the purchased access plan
 * @property   $plan_sku   (string)  SKU of the purchased access plan
 * @property   $plan_title  (string)  Title / Name of the purchased access plan
 * @property   $product_id  (int)  WP Post ID of the purchased product
 * @property   $product_sku   (string)  SKU of the purchased product
 * @proptery   $product_title  (string)  Title / Name of the purchased product
 * @property   $product_type  (string)  Type of product purchased (course or membership)
 *
 * @property   $sale_price  (float)  Sale price before coupon adjustments
 * @property   $sale_value  (float)  $original_total - $sale_price
 *
 * @property   $start_date  (string)  date when access was initially granted; this is used to determine when access expires
 *
 * @property   $title  (string)  Post Title
 * @property   $total  (float)  Actual price of the order, after applicable sale & coupon adjustments
 *
 *
 * @property   $trial_length  (int)  Length of the trial. Combined with $trial_period to determine the actual length of the trial.
 * @property   $trial_offer  (string)  Whether or not there was a trial offer applied to the order [yes|no]
 * @property   $trial_original_total  (float)  Total price of the trial before applicable coupon adjustments
 * @property   $trial_period  (string)  Period for the trial period. [year|month|week|day]
 * @property   $trial_total  (float)  Total price of the trial after applicable coupon adjustments
 *
 * @property   $user_id   (int)  customer WP User ID
 * @property   $user_ip_address  (string)  customer's IP address at time of purchase
 */


if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Order extends LLMS_Post_Model {

	protected $db_post_type = 'llms_order'; // maybe fix this
	protected $model_post_type = 'order';

	/**
	 * Add an admin-only note to the order visible on the admin panel
	 * notes are recorded using the wp comments API & DB
	 *
	 * @param    string     $note           note content
	 * @param    boolean    $added_by_user  if this is an admin-submitted note adds user info to note meta
	 * @return   null|int                   null on error or WP_Comment ID of the note
	 * @since    3.0.0
	 * @version  3.0.0
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

		} // added by the system during a transaction or scheduled action
		else {

			$user_id = 0;
			$author = _x( 'LifterLMS', 'default order note author', 'lifterlms' );
			$author_email = strtolower( _x( 'LifterLms', 'default order note author', 'woocommerce' ) ) . '@';
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
	 * @version  3.0.0
	 */
	public function get_access_expiration_date( $format = 'Y-m-d' ) {

		$type = $this->get( 'access_expiration' );

		switch ( $type ) {
			case 'lifetime':
				$r = __( 'Lifetime Access', 'lifterlms' );
			break;

			case 'limited-date':
				$r = $this->get_date( 'access_expires', $format );
			break;

			case 'limited-period':
				if ( $this->get( 'start_date' ) ) {
					$r = date_i18n( $format, strtotime( '+' . $this->get( 'access_length' ) . ' ' . $this->get( 'access_period' ), $this->get_date( 'start_date', 'U' ) ) );
				} else {
					$r = __( 'To be Determined', 'lifterlms' );
				}
			break;

			default:
				$r = apply_filters( 'llms_order_' . $type . '_access_expiration_date', $type, $this, $format );

		}

		return apply_filters( 'llms_order_get_access_expiration_date', $r, $this, $format );

	}

	/**
	 * Get the current status of a student's access based on the access plan data
	 * stored on the order at the time of purchase
	 * @return   string        'inactive' if the order is refunded, failed, pending, etc...
	 *                         'expired'  if access has expired according to $this->get_access_expiration_date()
	 *                         'active'   otherwise
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_access_status() {

		$statuses = apply_filters( 'llms_order_allow_access_stasuses', array(
			'llms-active',
			'llms-completed',
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

			$now = current_time( 'timestamp' );

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
	 * @version  3.0.0
	 */
	public function get_customer_name() {
		return trim( $this->get( 'billing_first_name' ) . ' ' . $this->get( 'billing_last_name' ) );
	}

	/**
	 * An array of default arguments to pass to $this->create()
	 * when creating a new post
	 * @param  string  $title   Title to create the post with
	 * @return array
	 * @since  3.0.0
	 * @version  3.0.0
	 */
	protected function get_creation_args( $title = '' ) {

		if ( empty( $title ) ) {
			$title = sprintf( __( 'Order &ndash; %s', 'lifterlms' ), strftime( _x( '%1$b %2$d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'lifterlms' ), current_time( 'timestamp' ) ) );
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
			'approve' => 'approve',
			'number'  => $number,
			'offset'  => ( $page - 1 ) * $number,
			'post_id' => $this->get( 'id' ),
			'type'    => '',
		) );

		return $comments;

	}

	/**
	 * Get a property's data type for scrubbing
	 * used by $this->scrub() to determine how to scrub the property
	 * @param  string $key  property key
	 * @since  3.0.0
	 * @version  3.0.0
	 * @return string
	 */
	protected function get_property_type( $key ) {

		switch ( $key ) {

			case 'coupon_amount':
			case 'coupon_amout_trial':
			case 'coupon_value':
			case 'coupon_value_trial':
			case 'original_total':
			case 'sale_price':
			case 'sale_value':
			case 'total':
			case 'trial_original_total':
			case 'trial_total':
				$type = 'float';
			break;

			case 'access_length':
			case 'billing_frequency':
			case 'billing_length':
			case 'coupon_id':
			case 'id':
			case 'plan_id':
			case 'product_id':
			case 'trial_length':
			case 'user_id':
				$type = 'absint';
			break;

			case 'access_expiration':
			case 'access_expires':
			case 'access_period':
			case 'billing_address_1':
			case 'billing_address_2':
			case 'billing_city':
			case 'billing_country':
			case 'billing_email':
			case 'billing_first_name':
			case 'billing_last_name':
			case 'billing_state':
			case 'billing_zip':
			case 'billing_period':
			case 'coupon_code':
			case 'coupon_type':
			case 'coupon_used':
			case 'currency':
			case 'on_sale':
			case 'order_key':
			case 'order_type':
			case 'payment_gateway':
			case 'plan_sku':
			case 'plan_title':
			case 'product_sku':
			case 'product_type':
			case 'title':
			case 'gateway_api_mode':
			case 'gateway_customer_id':
			case 'trial_offer':
			case 'trial_period':
			case 'user_ip_address':
			default:
				$type = 'text';

		}

		return $type;

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
	 * @version  3.2.5
	 */
	public function get_next_payment_due_date( $format = 'Y-m-d H:i:s' ) {

		// single payments will never have a next payment date
		if ( ! $this->is_recurring() ) {
			return new WP_Error( 'not-recurring', __( 'Order is not recurring', 'lifterlms' ) );
		} // only active, failed, or pending subscriptions can have a next payment date
		elseif ( ! in_array( $this->get( 'status' ), array( 'llms-active', 'llms-failed', 'llms-pending' ) ) ) {
			return new WP_Error( 'invalid-status', __( 'Invalid order status', 'lifterlms' ), $this->get( 'status' ) );
		}

		// check the number of recurring payments made
		// if we've reached that number in successful txns
		// return false b/c no more payments are due
		$num_payments = $this->get( 'billing_length' );
		if ( $num_payments ) {
			$txns = $this->get_transactions( array(
				'per_page' => -1,
				'status' => 'llms-txn-succeeded',
				'type' => 'recurring',
			) );

			// billing has completed
			if ( $txns['count'] >= $num_payments ) {
				return new WP_Error( 'completed', __( 'All recurring transactions completed', 'lifterlms' ) );
			}
		}

		// if were on a trial and the trial hasn't ended yet next payment date is the date the trial ends
		if ( $this->has_trial() && ! $this->has_trial_ended() ) {

			$next = $this->get_trial_end_date( 'U' );

		} else {

			// get the date of the last successful txn
			$last_date = strtotime( $this->get_last_transaction_date( 'llms-txn-succeeded', 'recurring' ) );

			// if there's no transaction, use now for calculation
			if ( ! $last_date ) {
				$last_date = current_time( 'timestamp' );
			}

			$period = $this->get( 'billing_period' );
			$frequency = $this->get( 'billing_frequency' );

			$next = strtotime( '+' . $frequency . ' ' . $period, $last_date );

		}

		return date_i18n( $format, apply_filters( 'llms_order_get_next_payment_due_date', $next, $this ) );

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
	 * @version  3.0.0
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
		if ( 'any' !== $statuses  ) {

			// if status is a string, ensure it's a valid status
			if ( is_string( $status ) && in_array( $status, $statuses ) ) {
				$statuses = array( $status );
			} elseif ( is_array( $status ) ) {
				$temp = array();
				foreach ( $status as $s ) {
					if ( in_array( $s, $statuses ) ) {
						$temp[] = $s;
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
			$transactions[ $post->ID ] = new LLMS_Transaction( $post );
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
	 * @return   bool|string         returns false if order has no trial or the date string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_trial_end_date( $format = 'Y-m-d H:i:s' ) {

		if ( ! $this->has_trial() ) {

			$r = false;

		} else {

			$start = $this->get_start_date( 'U' );

			$length = $this->get( 'trial_length' );
			$period = $this->get( 'trial_period' );

			$end = strtotime( '+' . $length . ' ' . $period, $start );

			$r = date_i18n( $format, $end );

		}

		return apply_filters( 'llms_order_get_trial_end_date', $r, $this );

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
	 * Checks permissions, only the purchasing viewer or an admin should be able to view
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_view_link() {

		$link = '';

		if ( current_user_can( apply_filters( 'llms_order_get_view_link_permission', 'manage_options' ) ) || get_current_user_id() === $this->get( 'user_id' ) ) {

			$link = llms_get_endpoint_url( 'orders', $this->get( 'id' ), llms_get_page_url( 'myaccount' ) );

		}

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
	 * @version  3.0.0
	 */
	public function has_trial_ended() {
		return ( current_time( 'timestamp' ) >= $this->get_trial_end_date( 'U' ) );
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
	 * Schedules the next payment due on a recurring order
	 * Can be called witnout consequence on a single payment order
	 * Will always unschedule the scheduled action (if one exists) before scheduling anothes
	 * @return   void
	 * @since    3.0.0
	 * @version  3.1.7
	 */
	public function maybe_schedule_payment() {

		if ( ! $this->is_recurring() ) {
			return;
		}

		$date = $this->get_next_payment_due_date( 'U' );

		if ( $date && ! is_wp_error( $date ) ) {

			// unschedule the next action (does nothing if no action scheduled)
			$this->unschedule_recurring_payment();

			// convert our date to UTC before passing to the scheduler
			$date = $date - ( HOUR_IN_SECONDS * get_option( 'gmt_offset' ) );

			// schedule the payment
			wc_schedule_single_action( $date, 'llms_charge_recurring_payment', array( 'order_id' => $this->get( 'id' ) ) );

		}

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
	 * Record the start date of the access plan and schedule expiration
	 * if expiration is required in the future
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function start_access() {

		// only start access if access isn't already started
		$date = $this->get( 'start_date' );
		if ( ! $date ) {

			// set the start date to now
			$date = current_time( 'mysql' );
			$this->set( 'start_date', $date );

			// get epiration date based on setting
			$expires = $this->get_access_expiration_date( 'U' );

			// will return a timestamp or "Lifetime Access as a string"
			if ( is_numeric( $expires ) ) {
				wc_schedule_single_action( $expires, 'llms_access_plan_expiration', array( 'order_id' => $this->get( 'id' ) ) );
			}

		}

	}

	/**
	 * Cancels a scheduled recurring payment action
	 * does nothing if no payments are scheduled
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function unschedule_recurring_payment() {
		if ( wc_next_scheduled_action( 'llms_charge_recurring_payment', array( 'order_id' => $this->get( 'id' ) ) ) ) {
			wc_unschedule_action( 'llms_charge_recurring_payment', array( 'order_id' => $this->get( 'id' ) ) );
		}
	}

}

