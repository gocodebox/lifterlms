<?php
/**
 * LLMS_Order class/model file
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 3.0.0
 * @version 7.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS order model.
 *
 * Provides CRUD operations for the `llms_order` post type.
 *
 * @property string $access_expiration       Access expiration type, accepts: lifetime (default), limited-period, or limited-date.
 * @property string $access_expires          Date on which access expires in `m/d/Y` format. Only applicable when the `$access_expiration` property is set to "limited-date".
 * @property int    $access_length           Length of access from time of purchase, combine with the `$access_period`. Only applicable when the `$access_expiration` property is set to "limited-period".
 * @property string $access_period           Time period of access from time of purchase, combine with `$access_length`. Only applicable when the `$access_expiration` property is set to "limited-period". Accepts: year, month, week, or day.
 * @property string $anonymized              Determines if the order has been anonymized due to a personal information erasure request. Accepts "yes" or "no".
 * @property string $billing_address_1       Customer billing address line 1.
 * @property string $billing_address_2       Customer billing address line 2.
 * @property string $billing_city            Customer billing city.
 * @property string $billing_country         Customer billing country, two character ISO code.
 * @property string $billing_email           Customer email address.
 * @property string $billing_first_name      Customer first name.
 * @property string $billing_last_name       Customer last name.
 * @property string $billing_phone           Customer phone number.
 * @property string $billing_state           Customer billing state.
 * @property string $billing_zip             Customer billing zip/postal code.
 * @property int    $billing_frequency       The billing frequency interval. A value of `0` indicates a one-time payment. Accepts integers <= 6.
 * @property int    $billing_length          Number of intervals to run payment for, combine with `$billing_period` & `$billing_frequency`. A value of `0` indicates that recurring payments run indefinitely (until cancelled). Only applicable if `$billing_frequency` is not 0.
 * @property string $billing_period          The billing period. Combine with `$length`. Only applicable if `$billing_frequency` is not 0. Accepts: year, month, week, or day.
 * @property float  $coupon_amount           Amount of the coupon (flat/percentage) in relation to the plan amount.
 * @property float  $coupon_amout_trial      Amount of the coupon (flat/percentage) in relation to the plan trial amount where applicable.
 * @property string $coupon_code             Coupon code applied to the order.
 * @property int    $coupon_id               The WP_Post ID of the used coupon.
 * @property string $coupon_type             Type of coupon used, either percent or dollar.
 * @property string $coupon_used             Whether or not a coupon was used for the order. Accepts yes or no.
 * @property float  $coupon_value            Value of the coupon. When on sale, `$sale_price` minus `$total`; when not on sale `$original_total` minus `$total`.
 * @property float  $coupon_value_trial      Value of the coupon applied to the trial. The `$trial_original_total` minus `$trial_total`.
 * @property string $currency                Transaction's currency code.
 * @property string $date_access_expires     Date when access should expire as a datetime string: `Y-m-d H:i:s`.
 * @property string $date_next_payment       Date when the next recurring payment is due as a datemtime string: `Y-m-d H:i:s`. Use function LLMS_Order::get_next_payment_due_date() instead of accessing directly!
 * @property string $date_trial_end          Date when the trial ends for orders with a trial as a datemtime string: `Y-m-d H:i:s`. Use function LLMS_Order::get_trial_end_date() instead of accessing directly!
 * @property string $gateway_api_mode        API Mode of the gateway when the transaction was made, either "test" or "live".
 * @property string $gateway_customer_id     Gateway's unique ID for the customer who placed the order (if supported by the gateway).
 * @property string $gateway_source_id       Gateway's unique ID for the card or source to be used for recurring subscriptions (if supported by gateway).
 * @property string $gateway_subscription_id Gateway's unique ID for the recurring subscription (if supported by the gateway).
 * @property int    $id                      The WP_Post ID of the order.
 * @property int    $last_retry_rule         Rule number for current retry step for the order.
 * @property string $on_sale                 Whether or not sale pricing was used for the plan, either "yes" or "no".
 * @property string $order_key               A unique identifier for the order that can be passed safely in URLs.
 * @property string $order_type              Single or recurring order, either "single" or "recurring".
 * @property float  $original_total          Price of the order before applicable sale and coupon adjustments.
 * @property string $payment_gateway         LifterLMS Payment Gateway ID (eg "paypal" or "stripe").
 * @property int    $plan_id                 WP_Post ID of the purchased access plan.
 * @property string $plan_sku                SKU of the purchased access plan.
 * @property string $plan_title              Title / Name of the purchased access plan.
 * @property string $plan_ended              Whether or not the payment plan has ended. Only applicable when the plan is not "unlimited". Accepts "yes" or "no".
 * @property int    $product_id              WP_Post ID of the purchased course or membership product.
 * @property string $product_sku             SKU of the purchased product.
 * @property string $product_title           Title / Name of the purchased product.
 * @property string $product_type            Type of product purchased (course or membership).
 * @property float  $sale_price              Sale price before coupon adjustments.
 * @property float  $sale_value              The value of the sale, `$original_total` - `$sale_price`.
 * @property string $start_date              Date when access was initially granted; this is used to determine when access expires.
 * @property array  $temp_gateway_ids        {
 *     An associative array containing gateway ids. The gateway IDs are cached in this meta property while the source is being
 *     switched. Any gateway running actions when a source is switched may need to know the previous source IDs which might be
 *     cleared or overwritten by other gateways during the switch.
 *
 *     @type string customer     The value of the `gateway_customer_id` property when the source switch starts.
 *     @type string source       The value of the `gateway_source_id` property when the source switch starts.
 *     @type string subscription The value of the `gateway_subscription_id` property when the source switch starts.
 * }
 * @property float  $total                   Actual price of the order, after applicable sale & coupon adjustments.
 * @property int    $trial_length            Length of the trial. Combined with $trial_period to determine the actual length of the trial.
 * @property string $trial_offer             Whether or not there was a trial offer applied to the order, either yes or no.
 * @property float  $trial_original_total    Total price of the trial before applicable coupon adjustments.
 * @property string $trial_period            Period for the trial period. Accepts: year, month, week, or day.
 * @property float  $trial_total             Total price of the trial after applicable coupon adjustments/
 * @property int    $user_id                 Customer WP_User ID.
 * @property string $user_ip_address         Customer's IP address at time of purchase.
 *
 * @since 3.0.0
 * @since 3.32.0 Update to use latest action-scheduler functions.
 * @since 3.35.0 Prepare transaction revenue SQL query properly; Sanitize $_SERVER data.
 * @since 4.7.0 Added `plan_ended` meta property.
 * @since 5.3.0 Removed usage of the meta property `date_billing_end` and removed private method `calculate_billing_end_date()`.
 */
class LLMS_Order extends LLMS_Post_Model {

	/**
	 * Database post type.
	 *
	 * @var string
	 */
	protected $db_post_type = 'llms_order';

	/**
	 * Model post type.
	 *
	 * @var string
	 */
	protected $model_post_type = 'order';

	/**
	 * Meta properties.
	 *
	 * @var array
	 */
	protected $properties = array(

		'anonymized'           => 'yesno',
		'coupon_amount'        => 'float',
		'coupon_amout_trial'   => 'float',
		'coupon_value'         => 'float',
		'coupon_value_trial'   => 'float',
		'original_total'       => 'float',
		'sale_price'           => 'float',
		'sale_value'           => 'float',
		'total'                => 'float',
		'trial_original_total' => 'float',
		'trial_total'          => 'float',

		'access_length'        => 'absint',
		'billing_frequency'    => 'absint',
		'billing_length'       => 'absint',
		'coupon_id'            => 'absint',
		'plan_id'              => 'absint',
		'product_id'           => 'absint',
		'trial_length'         => 'absint',
		'user_id'              => 'absint',

		'access_expiration'    => 'text',
		'access_expires'       => 'text',
		'access_period'        => 'text',
		'billing_address_1'    => 'text',
		'billing_address_2'    => 'text',
		'billing_city'         => 'text',
		'billing_country'      => 'text',
		'billing_email'        => 'text',
		'billing_first_name'   => 'text',
		'billing_last_name'    => 'text',
		'billing_state'        => 'text',
		'billing_zip'          => 'text',
		'billing_period'       => 'text',
		'coupon_code'          => 'text',
		'coupon_type'          => 'text',
		'coupon_used'          => 'text',
		'currency'             => 'text',
		'on_sale'              => 'text',
		'order_key'            => 'text',
		'order_type'           => 'text',
		'payment_gateway'      => 'text',
		'plan_ended'           => 'yesno',
		'plan_sku'             => 'text',
		'plan_title'           => 'text',
		'product_sku'          => 'text',
		'product_type'         => 'text',
		'title'                => 'text',
		'gateway_api_mode'     => 'text',
		'gateway_customer_id'  => 'text',
		'trial_offer'          => 'text',
		'trial_period'         => 'text',
		'user_ip_address'      => 'text',

		'date_access_expires'  => 'text',
		'date_next_payment'    => 'text',
		'date_trial_end'       => 'text',

		'temp_gateway_ids'     => 'array',

	);

	/**
	 * Add an admin-only note to the order visible on the admin panel
	 * notes are recorded using the wp comments API & DB
	 *
	 * @since 3.0.0
	 * @since 3.35.0 Sanitize $_SERVER data.
	 *
	 * @param string  $note          Note content.
	 * @param boolean $added_by_user Optional. If this is an admin-submitted note adds user info to note meta. Default is false.
	 * @return null|int Null on error or WP_Comment ID of the note.
	 */
	public function add_note( $note, $added_by_user = false ) {

		if ( ! $note ) {
			return;
		}

		// Added by a user from the admin panel.
		if ( $added_by_user && is_user_logged_in() && current_user_can( apply_filters( 'lifterlms_admin_order_access', 'manage_options' ) ) ) {

			$user_id      = get_current_user_id();
			$user         = get_user_by( 'id', $user_id );
			$author       = $user->display_name;
			$author_email = $user->user_email;

		} else {

			$user_id       = 0;
			$author        = _x( 'LifterLMS', 'default order note author', 'lifterlms' );
			$author_email  = strtolower( _x( 'LifterLms', 'default order note author', 'lifterlms' ) ) . '@';
			$author_email .= isset( $_SERVER['HTTP_HOST'] ) ? str_replace( 'www.', '', sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) : 'noreply.com';
			$author_email  = sanitize_email( $author_email );

		}

		$note_id = wp_insert_comment(
			apply_filters(
				'llms_add_order_note_content',
				array(
					'comment_post_ID'      => $this->get( 'id' ),
					'comment_author'       => $author,
					'comment_author_email' => $author_email,
					'comment_author_url'   => '',
					'comment_content'      => $note,
					'comment_type'         => 'llms_order_note',
					'comment_parent'       => 0,
					'user_id'              => $user_id,
					'comment_approved'     => 1,
					'comment_agent'        => 'LifterLMS',
					'comment_date'         => current_time( 'mysql' ),
				)
			)
		);

		do_action( 'llms_new_order_note_added', $note_id, $this );

		return $note_id;
	}

	/**
	 * Called after inserting a new order into the database
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	protected function after_create() {
		// Add a random key that can be passed in the URL and whatever.
		$this->set( 'order_key', $this->generate_order_key() );
	}

	/**
	 * Calculate the next payment due date
	 *
	 * @since 3.10.0
	 * @since 3.12.0 Unknown.
	 * @since 3.37.6 Now uses the last successful transaction time to calculate from when the previously
	 *               stored next payment date is in the future.
	 * @since 4.9.0 Fix comparison for PHP8 compat.
	 * @since 5.3.0 Determine if a limited order has ended based on number of remaining payments in favor of current date/time.
	 *
	 * @param string $format PHP date format used to format the returned date string.
	 * @return string The formatted next payment due date or an empty string when there is no next payment.
	 */
	private function calculate_next_payment_date( $format = 'Y-m-d H:i:s' ) {

		// If the limited plan has already ended return early.
		$remaining = $this->get_remaining_payments();
		if ( 0 === $remaining ) {
			// This filter is documented below.
			return apply_filters( 'llms_order_calculate_next_payment_date', '', $format, $this );
		}

		$start_time        = $this->get_date( 'date', 'U' );
		$next_payment_time = $this->get_date( 'date_next_payment', 'U' );
		$last_txn_time     = $this->get_last_transaction_date( 'llms-txn-succeeded', 'recurring', 'U' );

		// If were on a trial and the trial hasn't ended yet next payment date is the date the trial ends.
		if ( $this->has_trial() && ! $this->has_trial_ended() ) {

			$next_payment_time = $this->get_trial_end_date( 'U' );

		} else {

			/**
			 * Calculate next payment date from the saved `date_next_payment` calculated during
			 * the previous recurring transaction or during order initialization.
			 *
			 * This condition will be encountered during the 2nd, 3rd, 4th, etc... recurring payments.
			 */
			if ( $next_payment_time && $next_payment_time < llms_current_time( 'timestamp' ) ) {

				$from_time = $next_payment_time;

				/**
				 * Use the order's last successful transaction date.
				 *
				 * This will be encountered when any amount of "chaos" is
				 * introduced causing the previously stored `date_next_payment`
				 * to be GREATER than the current time.
				 *
				 * Orders created
				 */
			} elseif ( $last_txn_time && $last_txn_time > $start_time ) {

				$from_time = $last_txn_time;

				/**
				 * Use the order's creation time.
				 *
				 * This condition will be encountered for the 1st recurring payment only.
				 */
			} else {

				$from_time = $start_time;

			}

			$period            = $this->get( 'billing_period' );
			$frequency         = $this->get( 'billing_frequency' );
			$next_payment_time = strtotime( '+' . $frequency . ' ' . $period, $from_time );

			/**
			 * Make sure the next payment is more than 2 hours in the future
			 *
			 * This ensures changes to the site's timezone because of daylight savings
			 * will never cause a 2nd renewal payment to be processed on the same day.
			 */
			$i = 1;
			while ( $next_payment_time < ( llms_current_time( 'timestamp', true ) + 2 * HOUR_IN_SECONDS ) && $i < 3000 ) {
				$next_payment_time = strtotime( '+' . $frequency . ' ' . $period, $next_payment_time );
				++$i;
			}
		}

		/**
		 * Filter the calculated next payment date
		 *
		 * @since 3.10.0
		 *
		 * @param string     $ret    The formatted next payment due date or an empty string when there is no next payment.
		 * @param string     $format The requested date format.
		 * @param LLMS_Order $order  The order object.
		 */
		return apply_filters( 'llms_order_calculate_next_payment_date', date( $format, $next_payment_time ), $format, $this );
	}

	/**
	 * Calculate the end date of the trial
	 *
	 * @since 3.10.0
	 *
	 * @param string $format Optional. Desired return format of the date. Defalt is 'Y-m-d H:i:s'.
	 * @return string
	 */
	private function calculate_trial_end_date( $format = 'Y-m-d H:i:s' ) {

		$start = $this->get_date( 'date', 'U' ); // Start with the date the order was initially created.

		$length = $this->get( 'trial_length' );
		$period = $this->get( 'trial_period' );

		$end = strtotime( '+' . $length . ' ' . $period, $start );

		$ret = date_i18n( $format, $end );

		return apply_filters( 'llms_order_calculate_trial_end_date', $ret, $format, $this );
	}

	/**
	 * Determines if an order can be confirmed.
	 *
	 * An order can be confirmed only when the order's status is pending.
	 *
	 * Additional requirements can be introduced via the filter `llms_order_can_be_confirmed`.
	 *
	 * @since 7.0.0
	 *
	 * @return boolean
	 */
	public function can_be_confirmed() {

		/**
		 * Determine if the order can be confirmed.
		 *
		 * @since 3.34.4
		 *
		 * @param boolean    $can_be_confirmed Whether or not the order can be confirmed.
		 * @param LLMS_Order $order            Order object.
		 * @param string     $gateway_id       Payment gateway ID.
		 */
		return apply_filters(
			'llms_order_can_be_confirmed',
			( 'llms-pending' === $this->get( 'status' ) ),
			$this,
			$this->get( 'payment_gateway' )
		);
	}

	/**
	 * Determine if the order can be retried for recurring payments
	 *
	 * @since 3.10.0
	 * @since 5.2.0 Use strict type comparison.
	 * @since 5.2.1 Combine conditions that return `false`.
	 *
	 * @return boolean
	 */
	public function can_be_retried() {

		$can_retry = true;

		if (
			// Only recurring orders can be retried.
			! $this->is_recurring() ||
			// Recurring rety feature is disabled.
			! llms_parse_bool( get_option( 'lifterlms_recurring_payment_retry', 'yes' ) ) ||
			// Only active & on-hold orders qualify for a retry.
			! in_array( $this->get( 'status' ), array( 'llms-active', 'llms-on-hold' ), true )
		) {
			$can_retry = false;
		} else {

			// If the gateway isn't active or the gateway doesn't support recurring retries.
			$gateway = $this->get_gateway();
			if ( is_wp_error( $gateway ) || ! $gateway->supports( 'recurring_retry' ) ) {
				$can_retry = false;
			}
		}

		/**
		 * Filters whether or not a recurring order can be retried
		 *
		 * @since 5.2.1
		 *
		 * @param boolean    $can_retry Whether or not the order can be retried.
		 * @param LLMS_Order $order     Order object.
		 */
		return apply_filters( 'llms_order_can_be_retried', $can_retry, $this );
	}

	/**
	 * Determines if the order can be resubscribed to.
	 *
	 * @since 3.19.0
	 * @since 5.2.0 Use strict type comparison.
	 *
	 * @return bool
	 */
	public function can_resubscribe() {

		$can_resubscribe = false;

		if ( $this->is_recurring() ) {

			/**
			 * Filters the order statuses from which an order can be reactivated.
			 *
			 * @since 7.0.0
			 *
			 * @param string[] $allowed_statuses The list of allowed order statuses.
			 */
			$allowed_statuses = apply_filters(
				'llms_order_status_can_resubscribe_from',
				array(
					'llms-on-hold',
					'llms-pending',
					'llms-pending-cancel',
				)
			);
			$can_resubscribe  = in_array( $this->get( 'status' ), $allowed_statuses, true );

		}

		/**
		 * Determines whether or not a user can resubscribe to an inactive recurring payment order.
		 *
		 * @since 3.19.0
		 *
		 * @param boolean    $can_resubscribe Whether or not a user can resubscribe.
		 * @param LLMS_Order $order           The order object.
		 */
		return apply_filters( 'llms_order_can_resubscribe', $can_resubscribe, $this );
	}

	/**
	 * Determines if the order's payment source can be changed.
	 *
	 * @since 7.0.0
	 *
	 * @return boolean
	 */
	public function can_switch_source() {

		$can_switch = 'llms-active' === $this->get( 'status' ) || $this->can_resubscribe();

		/**
		 * Filters whether or not the order's payment source can be changed.
		 *
		 * @since 7.0.0
		 *
		 * @param boolean    $can_switch Whether or not the order's source can be switched.
		 * @param LLMS_Order $order      The order object.
		 */
		return apply_filters( 'llms_order_can_switch_source', $can_switch, $this );
	}

	/**
	 * Generate an order key for the order
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function generate_order_key() {
		/**
		 * Modify the generated order key for the order.
		 *
		 * @since 3.0.0
		 * @since 5.2.1 Added the `$order` parameter.
		 *
		 * @param string     $order_key The generated order key.
		 * @param LLMS_Order $order_key Order object.
		 */
		return apply_filters( 'lifterlms_generate_order_key', uniqid( 'order-' ), $this );
	}

	/**
	 * Determine the date when access will expire
	 *
	 * Based on the access settings of the access plan
	 * at the `$start_date` of access.
	 *
	 * @since 3.0.0
	 * @since 3.19.0 Unknown.
	 *
	 * @param string $format Optional. Date format. Default is 'Y-m-d'.
	 * @return string Date string.
	 *                "Lifetime Access" for plans with lifetime access.
	 *                "To be Determined" for limited date when access hasn't started yet.
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
						$ret  = date_i18n( $format, $time );
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
	 * Get the current status of a student's access
	 *
	 * Based on the access plan data stored on the order at the time of purchase.
	 *
	 * @since 3.0.0
	 * @since 3.19.0 Unknown.
	 * @since 5.2.0 Use stric type comparison.
	 *
	 * @return string 'inactive' If the order is refunded, failed, pending, etc...
	 *                'expired'  If access has expired according to $this->get_access_expiration_date()
	 *                'active'   Otherwise.
	 */
	public function get_access_status() {

		$statuses = apply_filters(
			'llms_order_allow_access_stasuses',
			array(
				'llms-active',
				'llms-completed',
				'llms-pending-cancel',
				/**
				 * Recurring orders can expire but still grant access
				 * eg: 3monthly payments grants 1 year of access
				 * on the 4th month the order will be marked as expired
				 * but the access has not yet expired based on the data below.
				 */
				'llms-expired',
			)
		);

		// If the order doesn't have one of the allowed statuses.
		// Return 'inactive' and don't bother checking expiration data.
		if ( ! in_array( $this->get( 'status' ), $statuses, true ) ) {

			return 'inactive';

		}

		// Get the expiration date as a timestamp.
		$expires = $this->get_access_expiration_date( 'U' );

		/**
		 * A translated non-numeric string will be returned for lifetime access
		 * so if we have a timestamp we should compare it against the current time
		 * to determine if access has expired.
		 */
		if ( is_numeric( $expires ) ) {

			$now = llms_current_time( 'timestamp' );

			// Expiration date is in the past
			// eg: the access has already expired.
			if ( $expires < $now ) {

				return 'expired';

			}
		}

		// We're active.
		return 'active';
	}

	/**
	 * Retrieve arguments passed to order-related events processed by the action scheduler
	 *
	 * @since 3.19.0
	 *
	 * @return array
	 */
	protected function get_action_args() {
		return array(
			'order_id' => $this->get( 'id' ),
		);
	}

	/**
	 * Get the formatted coupon amount with a currency symbol or percentage
	 *
	 * @since 3.0.0
	 *
	 * @param string $payment Coupon discount type, either 'regular' or 'trial'.
	 * @return string
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
	 * Retrieve the customer's full name
	 *
	 * @since 3.0.0
	 * @since 3.18.0 Unknown.
	 *
	 * @return string
	 */
	public function get_customer_name() {
		if ( 'yes' === $this->get( 'anonymized' ) ) {
			return __( 'Anonymous', 'lifterlms' );
		}
		return trim( $this->get( 'billing_first_name' ) . ' ' . $this->get( 'billing_last_name' ) );
	}

	/**
	 * Retrieve the customer's full billing address
	 *
	 * @since 5.2.0
	 *
	 * @return string
	 */
	public function get_customer_full_address() {

		$billing_address_1 = $this->get( 'billing_address_1' );
		if ( empty( $billing_address_1 ) ) {
			return '';
		}

		$address   = array(
			trim( $billing_address_1 . ' ' . $this->get( 'billing_address_2' ) ),
		);
		$address[] = trim( $this->get( 'billing_city' ) . ' ' . $this->get( 'billing_state' ) );
		$address[] = $this->get( 'billing_zip' );
		$address[] = llms_get_country_name( $this->get( 'billing_country' ) );

		return implode( ', ', array_filter( $address ) );
	}

	/**
	 * An array of default arguments to pass to $this->create() when creating a new post
	 *
	 * @since 3.0.0
	 * @since 3.10.0 Unknown.
	 * @since 5.3.1 Set the `post_date` property using `llms_current_time()`.
	 * @since 5.9.0 Remove usage of deprecated `strftime()`.
	 *
	 * @param string $title Title to create the post with.
	 * @return array
	 */
	protected function get_creation_args( $title = '' ) {

		$date = llms_current_time( 'mysql' );

		if ( empty( $title ) ) {

			$title = sprintf(
				// Translators: %1$s = Transaction creation date.
				__( 'Order &ndash; %1$s', 'lifterlms' ),
				date_format( date_create( $date ), 'M d, Y @ h:i A' )
			);

		}

		return apply_filters(
			"llms_{$this->model_post_type}_get_creation_args",
			array(
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_author'    => 1,
				'post_content'   => '',
				'post_date'      => $date,
				'post_excerpt'   => '',
				'post_password'  => uniqid( 'order_' ),
				'post_status'    => 'llms-' . apply_filters( 'llms_default_order_status', 'pending' ),
				'post_title'     => $title,
				'post_type'      => $this->get( 'db_post_type' ),
			),
			$this
		);
	}

	/**
	 * Retrieve the payment gateway instance for the order's selected payment gateway
	 *
	 * @since 1.0.0
	 *
	 * @return LLMS_Payment_Gateway|WP_Error Instance of the LLMS_Payment_Gateway extending class used for the payment.
	 *                                       WP_Error if the gateway cannot be located, e.g. because it's no longer enabled.
	 */
	public function get_gateway() {
		$gateways = llms()->payment_gateways();
		$gateway  = $gateways->get_gateway_by_id( $this->get( 'payment_gateway' ) );
		if ( $gateway && ( $gateway->is_enabled() || is_admin() ) ) {
			return $gateway;
		} else {
			return new WP_Error( 'error', sprintf( __( 'Payment gateway %s could not be located or is no longer enabled', 'lifterlms' ), $this->get( 'payment_gateway' ) ) );
		}
	}

	/**
	 * Get the initial payment amount due on checkout
	 *
	 * This will always be the value of "total" except when the product has a trial.
	 *
	 * @since 3.0.0
	 *
	 * @return mixed
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
	 *
	 * Each note is actually a WordPress comment.
	 *
	 * @since 3.0.0
	 *
	 * @param integer $number Number of comments to return.
	 * @param integer $page   Page number for pagination.
	 * @return array
	 */
	public function get_notes( $number = 10, $page = 1 ) {

		$comments = get_comments(
			array(
				'status'  => 'approve',
				'number'  => $number,
				'offset'  => ( $page - 1 ) * $number,
				'post_id' => $this->get( 'id' ),
			)
		);

		return $comments;
	}

	/**
	 * Retrieve an LLMS_Post_Model object for the associated product
	 *
	 * @since 3.8.0
	 *
	 * @return LLMS_Post_Model|WP_Post|null|false LLMS_Post_Model extended object (LLMS_Course|LLMS_Membership),
	 *                                            null if WP get_post() fails,
	 *                                            false if LLMS_Post_Model extended class isn't found.
	 */
	public function get_product() {
		return llms_get_post( $this->get( 'product_id' ) );
	}

	/**
	 * Retrieve the last (most recent) transaction processed for the order.
	 *
	 * @since 3.0.0
	 * @since 7.1.0 Skip counting the total rows found when retrieving the last transaction.
	 *
	 * @param array|string $status Filter by status (see transaction statuses). By default looks for any status.
	 * @param array|string $type   Filter by type [recurring|single|trial]. By default looks for any type.
	 * @return LLMS_Transaction|false instance of the LLMS_Transaction or false if none found
	 */
	public function get_last_transaction( $status = 'any', $type = 'any' ) {
		$txns = $this->get_transactions(
			array(
				'per_page'      => 1,
				'status'        => $status,
				'type'          => $type,
				'no_found_rows' => true,
			)
		);
		if ( $txns['count'] ) {
			return array_pop( $txns['transactions'] );
		}
		return false;
	}

	/**
	 * Retrieve the date of the last (most recent) transaction
	 *
	 * @since 3.0.0
	 *
	 * @param array|string $status Optional. Filter by status (see transaction statuses). Default is 'llms-txn-succeeded'.
	 * @param array|string $type   Optional. Filter by type [recurring|single|trial]. By default looks for any type.
	 * @param string       $format Optional. Date format of the return. Default is 'Y-m-d H:i:s'.
	 * @return string|false Date or false if none found.
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
	 * Retrieve the due date of the next payment according to access plan terms
	 *
	 * @since 3.0.0
	 * @since 3.19.0 Unknown.
	 * @since 5.2.0 Use stric type comparisons.
	 *
	 * @param string $format Optional. Date return format. Default is 'Y-m-d H:i:s'.
	 * @return string
	 */
	public function get_next_payment_due_date( $format = 'Y-m-d H:i:s' ) {

		// Single payments will never have a next payment date.
		if ( ! $this->is_recurring() ) {
			return new WP_Error( 'not-recurring', __( 'Order is not recurring', 'lifterlms' ) );
		} elseif ( ! in_array( $this->get( 'status' ), array( 'llms-active', 'llms-failed', 'llms-on-hold', 'llms-pending', 'llms-pending-cancel' ), true ) ) {
			return new WP_Error( 'invalid-status', __( 'Invalid order status', 'lifterlms' ), $this->get( 'status' ) );
		}

		// Retrieve the saved due date.
		$next_payment_time = $this->get_date( 'date_next_payment', 'U' );
		// Calculate it if not saved.
		if ( ! $next_payment_time ) {
			$next_payment_time = $this->calculate_next_payment_date( 'U' );
			if ( ! $next_payment_time ) {
				return new WP_Error( 'plan-ended', __( 'No more payments due', 'lifterlms' ) );
			}
		}

		/**
		 * Filter the next payment due date.
		 *
		 * A timestamp should always be returned as the conversion to the requested format
		 * will be performed on the returned value.
		 *
		 * @since 3.0.0
		 *
		 * @param int        $next_payment_time Unix timestamp for the next payment due date.
		 * @param LLMS_Order $order             Order object.
		 * @param string     $format            Requested date format.
		 */
		$next_payment_time = apply_filters( 'llms_order_get_next_payment_due_date', $next_payment_time, $this, $format );

		return date_i18n( $format, $next_payment_time );
	}

	/**
	 * Retrieve the timestamp of the next scheduled event for a given action
	 *
	 * @since 4.6.0
	 *
	 * @param string $action Action hook ID. Core actions are "llms_charge_recurring_payment", "llms_access_plan_expiration".
	 * @return int|false Returns the timestamp of the next action as an integer or `false` when no action exist.
	 */
	public function get_next_scheduled_action_time( $action ) {
		return as_next_scheduled_action( $action, $this->get_action_args() );
	}

	/**
	 * Retrieves the number of payments remaining for a recurring plan with a limited number of payments
	 *
	 * @since 5.3.0
	 *
	 * @return bool|int Returns `false` for invalid order types (single-payment orders or recurring orders
	 *                  without a billing length). Otherwise returns the number of remaining payments as an integer.
	 */
	public function get_remaining_payments() {

		$remaining = false;

		if ( $this->has_plan_expiration() ) {
			$len  = $this->get( 'billing_length' );
			$txns = $this->get_transactions(
				array(
					'status'   => array( 'llms-txn-succeeded', 'llms-txn-refunded' ),
					'per_page' => 1,
					'type'     => array( 'recurring', 'single' ), // If a manual payment is recorded it's counted a single payment and that should count.
				)
			);

			$remaining = $len - $txns['total'];
		}

		/**
		 * Filters the number of payments remaining for a recurring plan with a limited number of payments.
		 *
		 * @since 5.3.0
		 *
		 * @param bool|int   $remaining Number of remaining payments or `false` when called against invalid order types.
		 * @param LLMS_Order $order     Order object.
		 */
		return apply_filters( 'llms_order_remaining_payments', $remaining, $this );
	}

	/**
	 * Get configured payment retry rules
	 *
	 * @since 3.10.0
	 *
	 * @return array[] {
	 *     An array of retry rule arrays.
	 *
	 *     @type int    $delay         The number of seconds to delay to use when scheduling the retry attempt.
	 *     @type string $status        The status of the order while awaiting the next retry.
	 *     @type bool   $notifications Whether or not to trigger notifications to the student/user.
	 * }
	 */
	private function get_retry_rules() {

		$rules = array(
			array(
				'delay'         => HOUR_IN_SECONDS * 12,
				'status'        => 'on-hold',
				'notifications' => false,
			),
			array(
				'delay'         => DAY_IN_SECONDS,
				'status'        => 'on-hold',
				'notifications' => true,
			),
			array(
				'delay'         => DAY_IN_SECONDS * 2,
				'status'        => 'on-hold',
				'notifications' => true,
			),
			array(
				'delay'         => DAY_IN_SECONDS * 3,
				'status'        => 'on-hold',
				'notifications' => true,
			),
		);

		/**
		 * Filters the automatic payment recurring retry rules.
		 *
		 * @since 7.0.0
		 *
		 * @param array      $rules Array of retry rule arrays {@see LLMS_Order::get_retry_rules()}.
		 * @param LLMS_Order $rules The order object.
		 */
		return apply_filters( 'llms_order_automatic_retry_rules', $rules, $this );
	}

	/**
	 * SQL query to retrieve total amounts for transactions by type
	 *
	 * @since 3.0.0
	 * @since 3.35.0 Prepare SQL query properly.
	 * @since 7.7.0 Caching results to avoid duplicate queries.
	 *
	 * @param string $type Optional. Type can be 'amount' or 'refund_amount'. Default is 'amount'.
	 * @return float
	 */
	public function get_transaction_total( $type = 'amount' ) {

		// Check the cache.
		static $cache = array();
		if ( isset( $cache[ $this->get( 'id' ) ] )
			&& isset( $cache[ $this->get( 'id' ) ][ $type ] ) ) {
			return $cache[ $this->get( 'id' ) ][ $type ];
		}

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
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $post_statuses is prepared above.
		$grosse = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM( m2.meta_value )
			 FROM $wpdb->posts AS p
			 LEFT JOIN $wpdb->postmeta AS m1 ON m1.post_id = p.ID -- Join for the ID.
			 LEFT JOIN $wpdb->postmeta AS m2 ON m2.post_id = p.ID -- Get the actual amounts.
			 WHERE p.post_type = 'llms_transaction'
			   AND ( $post_statuses )
			   AND m1.meta_key = %s
			   AND m1.meta_value = %d
			   AND m2.meta_key = %s
			;",
				array(
					"{$this->meta_prefix}order_id",
					$this->get( 'id' ),
					"{$this->meta_prefix}{$type}",
				)
			)
		); // db call ok; no-cache ok.
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// Save to cache.
		if ( ! isset( $cache[ $this->get( 'id' ) ] ) ) {
			$cache[ $this->get( 'id' ) ] = array();
		}
		$cache[ $this->get( 'id' ) ][ $type ] = floatval( $grosse );

		return $cache[ $this->get( 'id' ) ][ $type ];
	}

	/**
	 * Get the start date for the order.
	 *
	 * Gets the date of the first initially successful transaction
	 * if none found, uses the created date of the order.
	 *
	 * @since 3.0.0
	 * @since 7.1.0 Skip counting the total rows found when retrieving the first transaction.
	 *
	 * @param string $format Desired return format of the date.
	 * @return string
	 */
	public function get_start_date( $format = 'Y-m-d H:i:s' ) {
		/**
		 * Get the first recorded transaction.
		 * Refunds are okay b/c that would have initially given the user access.
		 */
		$txns = $this->get_transactions(
			array(
				'order'         => 'ASC',
				'orderby'       => 'date',
				'per_page'      => 1,
				'status'        => array( 'llms-txn-succeeded', 'llms-txn-refunded' ),
				'type'          => 'any',
				'no_found_rows' => true,
			)
		);
		if ( $txns['count'] ) {
			$txn  = array_pop( $txns['transactions'] );
			$date = $txn->get_date( 'date', $format );
		} else {
			$date = $this->get_date( 'date', $format );
		}

		/**
		 * Filter the order start date.
		 *
		 * @since 3.0.0
		 * @since 7.1.0 Added the `$format` parameter.
		 *
		 * @param string     $date   The formatted start date for the order.
		 * @param LLMS_Order $order  The order object.
		 * @param string     $format The requested format of the date.
		 */
		return apply_filters( 'llms_order_get_start_date', $date, $this, $format );
	}

	/**
	 * Retrieves the user action required when changing the order's payment source.
	 *
	 * @since 7.0.0
	 *
	 * @return null|string Returns `switch` when the payment source can be switched and `pay` when payment on the new source
	 *                     is required before switching. A `null` return indicates that the order's payment source cannot be switched.
	 */
	public function get_switch_source_action() {

		$action = null;
		if ( $this->can_switch_source() ) {
			$action = in_array( $this->get( 'status' ), array( 'llms-active', 'llms-pending-cancel' ), true ) ? 'switch' : 'pay';
		}

		/**
		 * Filters the required user action for the order when switching the order's payment source.
		 *
		 * @since 7.0.0
		 *
		 * @param null|string $action The switch action ID or `null` when the payment source cannot be switched.
		 * @param LLMS_Order  $order  The order object.
		 */
		return apply_filters( 'llms_order_switch_source_action', $action, $this );
	}

	/**
	 * Retrieve an array of transactions associated with the order according to supplied arguments.
	 *
	 * @since 3.0.0
	 * @since 3.10.0 Unknown.
	 * @since 3.37.6 Add additional return property, `total`, which returns the total number of found transactions.
	 * @since 5.2.0 Use stric type comparisons.
	 * @since 7.1.0 Added `no_found_rows` parameter.
	 *
	 * @param array $args {
	 *     Hash of query argument data, ultimately passed to a WP_Query.
	 *
	 *     @type string|string[] $status        Transaction post status or array of transaction post status. Defaults to "any".
	 *     @type string|string[] $type          Transaction types or array of transaction types. Defaults to "any".
	 *                                          Accepts "recurring", "single", or "trial".
	 *     @type int             $per_page      Number of transactions to include in the return. Default `50`.
	 *     @type int             $paged         Result set page number.
	 *     @type string          $order         Result set order. Default "DESC". Accepts "DESC" or "ASC".
	 *     @type string          $orderby       Result set ordering field. Default "date".
	 *     @type bool            $no_found_rows Whether to skip counting the total rows found. Enabling can improve
	 *                                          performance. Default `false`.
	 * }
	 * @return array
	 */
	public function get_transactions( $args = array() ) {

		extract(
			wp_parse_args(
				$args,
				array(
					'status'        => 'any', // String or array or post statuses.
					'type'          => 'any', // String or array of transaction types [recurring|single|trial].
					'per_page'      => 50, // Int, number of transactions to return.
					'paged'         => 1, // Int, page number of transactions to return.
					'order'         => 'DESC',
					'orderby'       => 'date', // Field to order results by.
					'no_found_rows' => false,
				)
			)
		);

		// Assume any and use this to check for valid statuses.
		$statuses = llms_get_transaction_statuses();

		// Check statuses.
		if ( 'any' !== $statuses ) {

			// If status is a string, ensure it's a valid status.
			if ( is_string( $status ) && in_array( $status, $statuses, true ) ) {
				$statuses = array( $status );
			} elseif ( is_array( $status ) ) {
				$temp = array();
				foreach ( $status as $stat ) {
					if ( in_array( (string) $stat, $statuses, true ) ) {
						$temp[] = $stat;
					}
				}
				$statuses = $temp;
			}
		}

		// Setup type meta query.
		$types = array(
			'relation' => 'OR',
		);

		if ( 'any' === $type ) {
			$types[] = array(
				'key'   => $this->meta_prefix . 'payment_type',
				'value' => 'recurring',
			);
			$types[] = array(
				'key'   => $this->meta_prefix . 'payment_type',
				'value' => 'single',
			);
			$types[] = array(
				'key'   => $this->meta_prefix . 'payment_type',
				'value' => 'trial',
			);
		} elseif ( is_string( $type ) ) {
			$types[] = array(
				'key'   => $this->meta_prefix . 'payment_type',
				'value' => $type,
			);
		} elseif ( is_array( $type ) ) {
			foreach ( $type as $t ) {
				$types[] = array(
					'key'   => $this->meta_prefix . 'payment_type',
					'value' => $t,
				);
			}
		}

		// Execute the query.
		$query = new WP_Query(
			/**
			 * Filters the order's transactions query aguments.
			 *
			 * @since 3.0.0
			 * @since 7.1.0 Added `$no_found_rows` arg.
			 *
			 * @param array $query_args {
			 *     Hash of query argument data passed to a WP_Query.
			 *
			 *     @type string|string[] $status        Transaction post status or array of transaction post status.
			 *                                          Defaults to "any".
			 *     @type string|string[] $type          Transaction types or array of transaction types.
			 *                                          Defaults to "any".
			 *                                          Accepts "recurring", "single", or "trial".
			 *     @type int             $per_page      Number of transactions to include in the return. Default `50`.
			 *     @type int             $paged         Result set page number.
			 *     @type string          $order         Result set order. Default "DESC". Accepts "DESC" or "ASC".
			 *     @type string          $orderby       Result set ordering field. Default "date".
			 *     @type bool            $no_found_rows Whether to skip counting the total rows found.
			 *                                          Enabling can improve performance. Default false.
			 * }
			 */
			apply_filters(
				'llms_order_get_transactions_query',
				array(
					'meta_query'     => array(
						'relation' => 'AND',
						array(
							'key'   => $this->meta_prefix . 'order_id',
							'value' => $this->get( 'id' ),
						),
						$types,
					),
					'order'          => $order,
					'orderby'        => $orderby,
					'post_status'    => $statuses,
					'post_type'      => 'llms_transaction',
					'posts_per_page' => $per_page,
					'paged'          => $paged,
					'no_found_rows'  => $no_found_rows,
				)
			),
			$this,
			$status
		);

		$transactions = array();

		foreach ( $query->posts as $post ) {
			$transactions[ $post->ID ] = llms_get_post( $post );
		}

		return array(
			'total'        => $query->found_posts,
			'count'        => $query->post_count,
			'page'         => $paged,
			'pages'        => $query->max_num_pages,
			'transactions' => $transactions,
		);
	}

	/**
	 * Retrieve the date when a trial will end
	 *
	 * @since 3.0.0
	 *
	 * @param string $format Optional. Date return format. Default is 'Y-m-d H:i:s'.
	 * @return string
	 */
	public function get_trial_end_date( $format = 'Y-m-d H:i:s' ) {

		if ( ! $this->has_trial() ) {

			$trial_end_date = '';

		} else {

			// Retrieve the saved end date.
			$trial_end_date = $this->get_date( 'date_trial_end', $format );

			// If not saved, calculate it.
			if ( ! $trial_end_date ) {

				$trial_end_date = $this->calculate_trial_end_date( $format );

			}
		}

		return apply_filters( 'llms_order_get_trial_end_date', $trial_end_date, $this );
	}

	/**
	 * Gets the total revenue of an order
	 *
	 * @since 3.0.0
	 * @since 3.1.3 Handle legacy orders.
	 *
	 * @param string $type Optional. Revenue type [grosse|net]. Default is 'net'.
	 * @return float
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

		return apply_filters( 'llms_order_get_revenue', $amount, $type, $this );
	}

	/**
	 * Get a link to view the order on the student dashboard
	 *
	 * @since 3.0.0
	 * @since 3.8.0 Unknown.
	 *
	 * @return string
	 */
	public function get_view_link() {

		$link = llms_get_endpoint_url( 'orders', $this->get( 'id' ), llms_get_page_url( 'myaccount' ) );
		return apply_filters( 'llms_order_get_view_link', $link, $this );
	}

	/**
	 * Determine if the student associated with this order has access
	 *
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	public function has_access() {
		return ( 'active' === $this->get_access_status() ) ? true : false;
	}

	/**
	 * Determine if a coupon was used
	 *
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	public function has_coupon() {
		return ( 'yes' === $this->get( 'coupon_used' ) );
	}

	/**
	 * Determine if there was a discount applied to this order via either a sale or a coupon
	 *
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	public function has_discount() {
		return ( $this->has_coupon() || $this->has_sale() );
	}

	/**
	 * Determine if a recurring order has a limited number of payments
	 *
	 * @since 5.3.0
	 *
	 * @return boolean Returns `true` for recurring orders with a billing length and `false` otherwise.
	 */
	public function has_plan_expiration() {
		return ( $this->is_recurring() && ( $this->get( 'billing_length' ) > 0 ) );
	}

	/**
	 * Determine if the access plan was on sale during the purchase
	 *
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	public function has_sale() {
		return ( 'yes' === $this->get( 'on_sale' ) );
	}

	/**
	 * Determine if there's a payment scheduled for the order
	 *
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	public function has_scheduled_payment() {
		$date = $this->get_next_payment_due_date();
		return is_wp_error( $date ) ? false : true;
	}

	/**
	 * Determine if the order has a trial
	 *
	 * @since 3.0.0
	 *
	 * @return boolean True if has a trial, false if it doesn't.
	 */
	public function has_trial() {
		return ( $this->is_recurring() && 'yes' === $this->get( 'trial_offer' ) );
	}

	/**
	 * Determine if the trial period has ended for the order
	 *
	 * @since 3.0.0
	 * @since 3.10.0 Unknown.
	 *
	 * @return boolean True if ended, false if not ended.
	 */
	public function has_trial_ended() {
		return ( llms_current_time( 'timestamp' ) >= $this->get_trial_end_date( 'U' ) );
	}

	/**
	 * Initializes a new order with user, plan, gateway, and coupon metadata.
	 *
	 * Assumes all data passed in has already been validated.
	 *
	 * @since 3.8.0
	 * @since 3.10.0 Unknown.
	 * @since 5.3.0 Don't set unused legacy property `date_billing_end`.
	 * @since 7.0.0 Use `LLMS_Order::set_user_data()` to update user data.
	 *
	 * @param array|LLMS_Student|WP_User|integer $user_data User info for the person placing the order. See
	 *                                                      {@see LLMS_Order::set_user_data()} for more info.
	 * @param LLMS_Access_Plan                   $plan      The purchase access plan.
	 * @param LLMS_Payment_Gateway               $gateway   Gateway being used.
	 * @param LLMS_Coupon                        $coupon    Coupon object or `false` if no coupon used.
	 * @return LLMS_Order
	 */
	public function init( $user_data, $plan, $gateway, $coupon = false ) {

		$this->set_user_data( $user_data );

		// Access plan data.
		$this->set( 'plan_id', $plan->get( 'id' ) );
		$this->set( 'plan_title', $plan->get( 'title' ) );
		$this->set( 'plan_sku', $plan->get( 'sku' ) );

		// Product data.
		$product = $plan->get_product();
		$this->set( 'product_id', $product->get( 'id' ) );
		$this->set( 'product_title', $product->get( 'title' ) );
		$this->set( 'product_sku', $product->get( 'sku' ) );
		$this->set( 'product_type', $plan->get_product_type() );

		$this->set( 'payment_gateway', $gateway->get_id() );
		$this->set( 'gateway_api_mode', $gateway->get_api_mode() );

		// Trial data.
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

		// Price data.
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

		// Store original total before any discounts.
		$this->set( 'original_total', $price );

		// Get the actual total due after discounts if any are applicable.
		$total = $coupon ? $plan->get_price_with_coupon( $price_key, $coupon, array(), 'float' ) : $$price_key;
		$this->set( 'total', $total );

		// Coupon data.
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

		// Get all billing schedule related information.
		$this->set( 'billing_frequency', $plan->get( 'frequency' ) );
		if ( $plan->is_recurring() ) {
			$this->set( 'billing_length', $plan->get( 'length' ) );
			$this->set( 'billing_period', $plan->get( 'period' ) );
			$this->set( 'order_type', 'recurring' );
			$this->set( 'date_next_payment', $this->calculate_next_payment_date() );
		} else {
			$this->set( 'order_type', 'single' );
		}

		$this->set( 'access_expiration', $plan->get( 'access_expiration' ) );

		// Get access related data so when payment is complete we can calculate the actual expiration date.
		if ( $plan->can_expire() ) {
			$this->set( 'access_expires', $plan->get( 'access_expires' ) );
			$this->set( 'access_length', $plan->get( 'access_length' ) );
			$this->set( 'access_period', $plan->get( 'access_period' ) );
		}

		/**
		 * Action triggered after the order is initialized.
		 *
		 * @since Unknown.
		 * @since 7.0.0 Added `$user_data` parameter.
		 *                 The `$student` parameter returns an "empty" student object
		 *                 if the method's input data is an array instead of an existing
		 *                 user object.
		 *
		 * @param LLMS_Order                         $order     The order object.
		 * @param LLMS_Student                       $student   The student object. If an array of data is passed
		 *                                                      to `LLMS_Order::init()` then an empty student object
		 *                                                      will be passed.
		 * @param array|LLMS_Student|WP_User|integer $user_data User data.
		 */
		do_action(
			'lifterlms_new_pending_order',
			$this,
			is_array( $user_data ) ? new LLMS_Student( null, false ) : llms_get_student( $user_data ),
			$user_data
		);

		return $this;
	}

	/**
	 * Determine if the order is a legacy order migrated from 2.x
	 *
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	public function is_legacy() {
		return ( 'publish' === $this->get( 'status' ) );
	}

	/**
	 * Determine if the order is recurring or singular
	 *
	 * @since 3.0.0
	 *
	 * @return boolean True if recurring, false if not.
	 */
	public function is_recurring() {
		return $this->get( 'order_type' ) === 'recurring';
	}

	/**
	 * Schedule access expiration
	 *
	 * @since 3.19.0
	 * @since 3.32.0 Update to use latest action-scheduler functions.
	 *
	 * @return void
	 */
	public function maybe_schedule_expiration() {

		// Get expiration date based on setting.
		$expires = $this->get_access_expiration_date( 'U' );

		// Will return a timestamp or "Lifetime Access as a string".
		if ( is_numeric( $expires ) ) {
			$this->unschedule_expiration();
			as_schedule_single_action( $expires, 'llms_access_plan_expiration', $this->get_action_args() );
		}
	}

	/**
	 * Schedules the next payment due on a recurring order
	 *
	 * Can be called without consequence on a single payment order.
	 * Will always unschedule the scheduled action (if one exists) before scheduling another.
	 *
	 * @since 3.0.0
	 * @since 3.32.0 Update to use latest action-scheduler functions.
	 * @since 4.7.0 Add `plan_ended` metadata when a plan ends.
	 * @since 5.2.0 Move scheduling recurring payment into a proper method.
	 *
	 * @return void
	 */
	public function maybe_schedule_payment( $recalc = true ) {

		if ( ! $this->is_recurring() ) {
			return;
		}

		if ( $recalc ) {
			$this->set( 'date_next_payment', $this->calculate_next_payment_date() );
		}

		$date = $this->get_next_payment_due_date();

		// Unschedule and reschedule.
		if ( $date && ! is_wp_error( $date ) ) {

			$this->schedule_recurring_payment( $date );

		} elseif ( is_wp_error( $date ) ) {

			if ( 'plan-ended' === $date->get_error_code() ) {

				// Unschedule the next action (does nothing if no action scheduled).
				$this->unschedule_recurring_payment();

				// Add a note that the plan has completed.
				$this->add_note( __( 'Order payment plan completed.', 'lifterlms' ) );
				$this->set( 'plan_ended', 'yes' );

			}
		}
	}

	/**
	 * Handles scheduling recurring payment retries when the gateway supports them
	 *
	 * @since 3.10.0
	 * @since 7.0.0 Added return value.
	 *
	 * @return null|boolean Returns `null` if the order cannot be retried, `false` when all retry rules have been tried (or none exist), and `true`
	 *                      when a retry is scheduled.
	 */
	public function maybe_schedule_retry() {

		if ( ! $this->can_be_retried() ) {
			return null;
		}

		// Get the index of the rule to use for this retry.
		$current_rule_index = $this->get( 'last_retry_rule' );
		if ( '' === $current_rule_index ) {
			$current_rule_index = 0;
		} else {
			++$current_rule_index;
		}

		$rules        = $this->get_retry_rules();
		$current_rule = $rules[ $current_rule_index ] ?? false;

		// No rule to run.
		if ( ! $current_rule ) {

			$this->set_status( 'failed' );
			$this->set( 'last_retry_rule', '' );

			$this->add_note( esc_html__( 'Maximum retry attempts reached.', 'lifterlms' ) );

			/**
			 * Action triggered when there are not more recurring payment retry rules.
			 *
			 * @since 3.10.0
			 *
			 * @param LLMS_Order $order The order object.
			 */
			do_action( 'llms_automatic_payment_maximum_retries_reached', $this );

			return false;

		}

		$timestamp = current_time( 'timestamp' ) + $current_rule['delay'];

		$this->set_date( 'next_payment', date_i18n( 'Y-m-d H:i:s', $timestamp ) );
		$this->set_status( $current_rule['status'] );
		$this->set( 'last_retry_rule', $current_rule_index );

		$this->add_note(
			sprintf(
				// Translators: %s = next attempt date.
				esc_html__( 'Automatic retry attempt scheduled for %s', 'lifterlms' ),
				date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp )
			)
		);

		// If notifications should be sent, trigger them.
		if ( $current_rule['notifications'] ) {
			/**
			 * Triggers the "Payment Retry Scheduled" notification.
			 *
			 * @since 3.10.0
			 *
			 * @param LLMS_Order $order The order object.
			 */
			do_action( 'llms_send_automatic_payment_retry_notification', $this );
		}

		/**
		 * Action triggered after a recurring payment retry is successfully scheduled.
		 *
		 * @since 3.10.0
		 *
		 * @param LLMS_Order $order The order object.
		 */
		do_action( 'llms_automatic_payment_retry_scheduled', $this );

		return true;
	}

	/**
	 * Record a transaction for the order
	 *
	 * @since 3.0.0
	 *
	 * @param array $data Optional array of additional data to store for the transaction.
	 * @return LLMS_Transaction Instance of LLMS_Transaction for the created transaction.
	 */
	public function record_transaction( $data = array() ) {

		extract(
			array_merge(
				array(
					'amount'             => 0,
					'completed_date'     => current_time( 'mysql' ),
					'customer_id'        => '',
					'fee_amount'         => 0,
					'source_id'          => '',
					'source_description' => '',
					'transaction_id'     => '',
					'status'             => 'llms-txn-succeeded',
					'payment_gateway'    => $this->get( 'payment_gateway' ),
					'payment_type'       => 'single',
				),
				$data
			)
		);

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
	 *
	 * This is mainly used to allow updating dates which are editable from the admin panel which
	 * should trigger additional actions when updated.
	 *
	 * Settable dates: date_next_payment, date_trial_end, date_access_expires.
	 *
	 * @since 3.10.0
	 * @since 3.19.0 Unknown.
	 *
	 * @param string $date_key Date field to set.
	 * @param string $date_val Date string or a unix time stamp.
	 */
	public function set_date( $date_key, $date_val ) {

		// Convert to timestamp if not already a timestamp.
		if ( ! is_numeric( $date_val ) ) {
			$date_val = strtotime( $date_val );
		}

		$this->set( 'date_' . $date_key, date( 'Y-m-d H:i:s', $date_val ) );

		switch ( $date_key ) {

			// Reschedule access expiration.
			case 'access_expires':
				$this->maybe_schedule_expiration();
				break;

			// Additionally update the next payment date & don't break because we want to reschedule payments too.
			case 'trial_end':
				$this->set_date( 'next_payment', $this->calculate_next_payment_date( 'U' ) );

				// Everything else reschedule's payments.
			default:
				$this->maybe_schedule_payment( false );

		}
	}

	/**
	 * Update the status of an order
	 *
	 * @since 3.8.0
	 * @since 3.10.0 Unknown.
	 * @since 5.2.0 Prefer `array_key_exists( $key, $keys )` over `in_array( $key, array_keys( $assoc_array ) )`.
	 *
	 * @param string $status Status name, accepts unprefixed statuses.
	 * @return void
	 */
	public function set_status( $status ) {

		if ( false === strpos( $status, 'llms-' ) ) {
			$status = 'llms-' . $status;
		}

		if ( array_key_exists( $status, llms_get_order_statuses( $this->get( 'order_type' ) ) ) ) {
			$this->set( 'status', $status );
		}
	}

	/**
	 * Sets user-related metadata for the order.
	 *
	 * @since 7.0.0
	 *
	 * @param array|LLMS_Student|WP_User|integer $user_or_data Accepts a raw array user meta-data or
	 *                                                         an input string accepted by `llms_get_student()`.
	 *                                                         When passing an existing user the data will be pulled
	 *                                                         from the user metadata and saved to the order.
	 * @return array {
	 *     Returns an associative array representing the user metadata that was stored on the order.
	 *
	 *     @type integer $user_id            User's WP_User id.
	 *     @type string  $user_ip_address    User's ip address.
	 *     @type string  $billing_email      User's email.
	 *     @type string  $billing_first_name User's first name.
	 *     @type string  $billing_last_name  User's last name.
	 *     @type string  $billing_address_1  User's address line 1.
	 *     @type string  $billing_address_2  User's address line 2.
	 *     @type string  $billing_city       User's city.
	 *     @type string  $billing_state      User's state.
	 *     @type string  $billing_zip        User's zip.
	 *     @type string  $billing_country    User's country.
	 *     @type string  $billing_phone      User's phone.
	 * }
	 */
	public function set_user_data( $user_or_data ) {

		$to_set = array(
			'user_id'            => '',
			'billing_email'      => '',
			'billing_first_name' => '',
			'billing_last_name'  => '',
			'billing_address_1'  => '',
			'billing_address_2'  => '',
			'billing_city'       => '',
			'billing_state'      => '',
			'billing_zip'        => '',
			'billing_country'    => '',
			'billing_phone'      => '',
		);

		$user = ! is_array( $user_or_data ) ? llms_get_student( $user_or_data ) : false;
		if ( $user ) {

			$user_or_data = array();

			$map = array(
				'user_id'            => 'id',
				'billing_email'      => 'user_email',
				'billing_phone'      => 'phone',
				'billing_first_name' => 'first_name',
				'billing_last_name'  => 'last_name',
			);

			foreach ( array_keys( $to_set ) as $order_key ) {
				$to_set[ $order_key ] = $user->get( $map[ $order_key ] ?? $order_key );
			}
		}

		// Only use the default IP address if it wasn't specified in the input array.
		$to_set['user_ip_address'] = $user_or_data['user_ip_address'] ?? llms_get_ip_address();

		// Merge the data and remove excess keys.
		$to_set = array_intersect_key(
			array_merge( $to_set, $user_or_data ),
			$to_set
		);

		$this->set_bulk( $to_set );
		return $to_set;
	}

	/**
	 * Record the start date of the access plan and schedule expiration if expiration is required in the future
	 *
	 * @since 3.0.0
	 * @since 3.19.0 Unknown.
	 * @since 5.2.0 Use strict type comparision.
	 *
	 * @return void
	 */
	public function start_access() {

		// Only start access if access isn't already started.
		$date = $this->get( 'start_date' );
		if ( ! $date ) {

			// Set the start date to now.
			$date = llms_current_time( 'mysql' );
			$this->set( 'start_date', $date );

		}

		$this->unschedule_expiration();

		// Setup expiration.
		if ( in_array( $this->get( 'access_expiration' ), array( 'limited-date', 'limited-period' ), true ) ) {

			$expires_date = $this->get_access_expiration_date( 'Y-m-d H:i:s' );
			$this->set( 'date_access_expires', $expires_date );
			$this->maybe_schedule_expiration();

		}
	}

	/**
	 * Cancels a scheduled expiration action
	 *
	 * Does nothing if no expiration is scheduled
	 *
	 * @since 3.19.0
	 * @since 3.32.0 Update to use latest action-scheduler functions.
	 * @since 4.6.0 Use `$this->get_next_scheduled_action_time()` to determine if the action is currently scheduled.
	 *
	 * @return void
	 */
	public function unschedule_expiration() {

		if ( $this->get_next_scheduled_action_time( 'llms_access_plan_expiration' ) ) {
			as_unschedule_action( 'llms_access_plan_expiration', $this->get_action_args() );
		}
	}

	/**
	 * Cancels a scheduled recurring payment action
	 *
	 * Does nothing if no payments are scheduled
	 *
	 * @since 3.0.0
	 * @since 3.32.0 Update to use latest action-scheduler functions.
	 * @since 4.6.0 Use `$this->get_next_scheduled_action_time()` to determine if the action is currently scheduled.
	 *
	 * @return void
	 */
	public function unschedule_recurring_payment() {

		if ( $this->get_next_scheduled_action_time( 'llms_charge_recurring_payment' ) ) {

			$action_args = $this->get_action_args();

			as_unschedule_action( 'llms_charge_recurring_payment', $action_args );

			/**
			 * Fired after a recurring payment is unscheduled
			 *
			 * @since 5.2.0
			 *
			 * @param LLMS_Order $order       LLMS_Order instance.
			 * @param int        $date        Timestamp of the recurring payment date UTC.
			 * @param array      $action_args Arguments passed to the scheduler.
			 */
			do_action( 'llms_charge_recurring_payment_unscheduled', $this, $action_args );

		}
	}

	/**
	 * Schedule recurring payment
	 *
	 * It will unschedule the next recurring payment action, if any, before scheduling.
	 *
	 * @since 5.2.0
	 *
	 * @param string  $next_payment_date Optional. Next payment date. If not provided it'll be retrieved using `$this->get_next_payment_due_date()`.
	 * @param boolean $gmt               Optional. Whether the provided `$next_payment_date` date is gmt. Default is `false`.
	 *                                   Only applies when the `$next_payment_date` is provided.
	 * @return WP_Error|integer WP_Error if the plan ended. Otherwise returns the return value of `as_schedule_single_action`: the action's ID.
	 */
	public function schedule_recurring_payment( $next_payment_date = false, $gmt = false ) {

		// Unschedule the next action (does nothing if no action scheduled).
		$this->unschedule_recurring_payment();

		$date = $this->get_recurring_payment_due_date_for_scheduler( $next_payment_date, $gmt );

		if ( is_wp_error( $date ) ) {
			return $date;
		}

		$action_args = $this->get_action_args();

		// Schedule the payment.
		$action_id = as_schedule_single_action(
			$date,
			'llms_charge_recurring_payment',
			$action_args
		);

		/**
		 * Fired after a recurring payment is scheduled
		 *
		 * @since 5.2.0
		 *
		 * @param LLMS_Order $order       LLMS_Order instance.
		 * @param integer    $date        Timestamp of the recurring payment date UTC.
		 * @param array      $action_args Arguments passed to the scheduler.
		 * @param integer    $action_id   Scheduled action ID.
		 */
		do_action( 'llms_charge_recurring_payment_scheduled', $this, $date, $action_args, $action_id );

		return $action_id;
	}

	/**
	 * Returns the recurring payment due date in a suitable format for the scheduler.
	 *
	 * @since 5.2.0
	 *
	 * @param string  $next_payment_date Optional. Next payment date. If not provided it'll be retrieved using `$this->get_next_payment_due_date()`.
	 * @param boolean $gmt               Optional. Whether the provided `$next_payment_date` date is gmt. Default is `false`.
	 *                                   Only applies when the `$next_payment_date` is provided.
	 * @return WP_Error|integer
	 */
	public function get_recurring_payment_due_date_for_scheduler( $next_payment_date = false, $gmt = false ) {

		$date = false === $next_payment_date ? $this->get_next_payment_due_date() : $next_payment_date;

		if ( ! $date ) {
			return new WP_Error( 'invalid-recurring-payment-date', __( 'Next recurring payment due date is not valid', 'lifterlms' ) );
		}
		if ( is_wp_error( $date ) ) {
			return $date;
		}

		// Convert our date to Unix time and UTC before passing to the scheduler.
		// No date parameter passed, or passed date parameter was not in gmt.
		if ( ! $next_payment_date || ( $next_payment_date && ! $gmt ) ) {
			$date = get_gmt_from_date( $date, 'U' );
		} else {
			// Get timestamp.
			$date = date_format( date_create( $date ), 'U' );
		}

		return (int) $date;
	}

	/**
	 * Determine whether the recurring payment for this order can be modified.
	 *
	 * Depends on whether the order's gateway supports.
	 *
	 * @since 7.0.0
	 *
	 * @return bool
	 */
	public function supports_modify_recurring_payments() {
		$gateway = $this->get_gateway();
		return is_wp_error( $gateway ) ? false : $gateway->supports( 'modify_recurring_payments', $this );
	}
}
