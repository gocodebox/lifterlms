<?php
/**
 * LifterLMS Order Class
 *
 * Handle all data related to an Order
 *
 * @package     LifterLMS/Classes
 * @category    Class
 * @author      LifterLMS
 *
 * @property   $billing_address_1
 * @property   $billing_address_2
 * @property   $billing_city
 * @property   $billing_country
 * @property   $billing_cycle
 * @property   $billing_email
 * @property   $billing_first_name
 * @property   $billing_frequency
 * @property   $billing_last_name
 * @property   $billing_period
 * @property   $billing_start_date
 * @property   $billing_state
 * @property   $billing_zip
 * @property   $coupon_amount           			Single order coupon discount amount as defined by the coupon (5.00 or 5%)
 * @property   $coupon_code             			Coupon Code Used
 * @property   $coupon_first_payment_amount
 * @property   $coupon_first_payment_value
 * @property   $coupon_id              				WP Post ID of the LifterLMS Coupon Used
 * @property   $coupon_recurring_payment_amount
 * @property   $coupon_recurring_payment_value
 * @property   $coupon_type            				Type of coupon used, either percent or dollar
 * @property   $coupon_value            			Single order actual value of the coupon (original_total - total)
 * @property   $currency                			Order currency
 * @property   $discount_type 						Get the type of discount for a discounted order (sale or coupon)
 * @property   $first_payment_orignal_total 		Recurring order first payment amount (before discounts if coupon used)
 * @property   $first_payment_total 				Recurring order first payment amount (discount adjusted if coupon used)
 * @property   $order_key 							A unique identifer for the order that can be passed safely in URLs
 * @property   $original_total          			Single order amount before discounts
 * @property   $payment_gateway         			Payment Gateway Used
 * @property   $payment_type            			Credit Card, PayPal, WooCommerce, Etc...
 * @property   $product_id              			WP Post ID of the LifterLMS Product purchased
 * @property   $product_sku             			SKU of the purchased item (optional) at time of purchase
 * @property   $product_title           			WP Post Title of the LifterLMS Product at time of purchase
 * @property   $product_type            			course or membership
 * @property   $post                    			WP_Post object of the associated WordPress post for the order
 * @property   $recurring_payment_original_total
 * @property   $recurring_payment_total
 * @property   $sale_value 							Value of a sale (original total - total)
 * @property   $status
 * @property   $subscription_id 					Gateway's unique ID for the subscription
 * @property   $subscription_last_sync				Timestamp of the last time LifterLMS retrieved a status update from the gateway
 * @property   $total
 * @property   $transaction_api_mode 				API Mode of the gateway when the transaction was made
 * @property   $transaction_customer_id 			Gateway's unique ID for the customer who placed the order
 * @property   $transaction_id 						Gateway's unique ID for the transaction
 * @property   $type                    			Order type (single, recurring)
 * @property   $user_id 							User ID of purchasing user
 * @property   $user_ip_address                     IP Address of the user who purchased the product
 *
 * @since  3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Order {

	private $id = null;

	private $prefix = '_llms_';

	/**
	 * Constructor
	 *
	 * @param mixed  Order Post ID, Instance of LLMS_Order, Instance of WP_Post
	 *
	 * @return  void
	 *
	 * @since  3.0.0
	 */
	public function __construct( $order = null ) {

		if ( is_numeric( $order ) ) {

			$this->id   = absint( $order );
			$this->post = get_post( $this->id );

		} elseif ( $order instanceof LLMS_Order ) {

			$this->id   = absint( $order->id );
			$this->post = $order->post;

		} elseif ( isset( $order->ID ) ) {

			$this->id   = absint( $order->ID );
			$this->post = $order;

		}

	}

	/**
	 * Getter
	 * @param  string $key key to retrieve
	 * @return mixed
	 */
	public function __get( $key ) {

		// can't update the ID
		if ( 'id' === $key ) {

			$value = $this->id;

		} else {

			$value = get_post_meta( $this->id, $this->prefix . $key, true );

		}

		return apply_filters( 'llms_get_order_' . $key, $value, $this );

	}

	/**
	 * Isset
	 * @param  string  $key  check if a key exists in the database
	 * @return boolean
	 */
	public function __isset( $key ) {
		return metadata_exists( 'post', $this->id, $this->prefix . $key );
	}


	/**
	 * Setter
	 *
	 * If an ID exists, will store data in the postmeta table
	 *
	 * @param string $key name of the property
	 * @param mixed  $val value of the property
	 *
	 * @return  void
	 */
	public function __set( $key, $val ) {

		// can't update ID
		if ( 'id' === $key ) {

			return;

		} else {

			$val = apply_filters( 'llms_set_order_' . $key, $val, $this );
			$this->$key = $val;

			// if we have an id, sync to the database
			if ( 'post' !== $key && $this->get_id() ) {

				if ( 'status' === $key ) {

					$this->update_status( $status );

				} else {

					update_post_meta( $this->id, $this->prefix . $key, apply_filters( 'llms_set_order_' . $key, $val, $this ) );

				}

			}

		}

	}

	/**
	 * Get the Order ID
	 * @return int
	 */
	public function get_id() {
		return absint( $this->id );
	}

	/**
	 * Creates a new Order Post in the Database
	 * And stores all existing properties in the postmeta table
	 *
	 * @return void
	 */
	public function create() {

		// can't create an order when it already exists!
		if ( $this->get_id() ) {

			return;

		}

		$data = apply_filters( 'lifterlms_new_order', array(
			'post_type' 	 => 'llms_order',
			'post_title' 	 => sprintf( __( 'Order - %s', 'lifterlms' ), strftime( _x( '%b %d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'lifterlms' ) ) ),
			'post_status' 	 => 'llms-pending',
			'comment_status' => 'closed',
			'ping_status'	 => 'closed',
			'post_author' 	 => 1,
			'post_password'	 => uniqid( 'order_' ),
		) );

		// reconstruct the class
		$this->__construct( wp_insert_post( $data ) );

		// exclude a few keys from the object
		$exclude = array( 'id', 'post', 'prefix' );

		// add a random key that can be passed in the URL and whatever
		$this->order_key = apply_filters( 'lifterlms_generate_order_key', uniqid( 'llms_order_' ) );

		// look through all properties and save them to the database
		foreach ( $this as $key => $val ) {

			if ( ! empty( $val ) && ! in_array( $key, $exclude ) ) {

				$this->__set( $key, $val );

			}

		}

		do_action( 'lifterlms_order_status_pending', $this );

	}






	/*
		  /$$$$$$              /$$     /$$
		 /$$__  $$            | $$    | $$
		| $$  \__/  /$$$$$$  /$$$$$$ /$$$$$$    /$$$$$$   /$$$$$$   /$$$$$$$
		| $$ /$$$$ /$$__  $$|_  $$_/|_  $$_/   /$$__  $$ /$$__  $$ /$$_____/
		| $$|_  $$| $$$$$$$$  | $$    | $$    | $$$$$$$$| $$  \__/|  $$$$$$
		| $$  \ $$| $$_____/  | $$ /$$| $$ /$$| $$_____/| $$       \____  $$
		|  $$$$$$/|  $$$$$$$  |  $$$$/|  $$$$/|  $$$$$$$| $$       /$$$$$$$/
		 \______/  \_______/   \___/   \___/   \_______/|__/      |_______/
	*/


	/**
	 * Get the billing_address_1
	 * @return string
	 */
	public function get_billing_address_1() {
		return $this->billing_address_1;
	}

	/**
	 * Get the billing_address_2
	 * @return string
	 */
	public function get_billing_address_2() {
		return $this->billing_address_2;
	}

	/**
	 * Get the billing_city
	 * @return string
	 */
	public function get_billing_city() {
		return $this->billing_city;
	}

	/**
	 * Get the billing_country
	 * @return string
	 */
	public function get_billing_country() {
		return $this->billing_country;
	}

	/**
	 * Get billing cycle
	 * @return int
	 */
	public function get_billing_cycle() {
		return $this->billing_cycle;
	}

	/**
	 * Get the email of the customer
	 * @return string
	 */
	public function get_billing_email() {
		$email = '';
		/**
		 * Prior to 2.8.0 billing information was not stored on the order
		 */
		if ( ! isset( $this->billing_email ) ) {
			$user = $this->get_user();
			if ( $user ) {
				$email = $user->user_email;
			}
		} else {
			$email = $this->billing_email;
		}
		return $email;
	}

	/**
	 * Get the first name of the customer
	 * @return string
	 */
	public function get_billing_first_name() {
		$name = '';
		/**
		 * Prior to 2.8.0 billing information was not stored on the order
		 */
		if ( ! isset( $this->billing_first_name ) ) {
			$user = $this->get_user();
			if ( $user ) {
				$name = $user->first_name;
			}
		} else {
			$name = $this->billing_first_name;
		}
		return $name;
	}

	/**
	 * Get billing freq
	 * @return int
	 */
	public function get_billing_frequency() {
		return $this->billing_frequency;
	}

	/**
	 * Get the first name of the customer
	 * @return string
	 */
	public function get_billing_last_name() {
		$name = '';
		/**
		 * Prior to 2.8.0 billing information was not stored on the order
		 */
		if ( ! isset( $this->billing_last_name ) ) {
			$user = $this->get_user();
			if ( $user ) {
				$name = $user->last_name;
			}
		} else {
			$name = $this->billing_last_name;
		}
		return $name;
	}

	/**
	 * Get the full name (first and last) of the customer
	 * @return string
	 */
	public function get_billing_name() {
		$name = $this->get_billing_first_name() . ' ' . $this->get_billing_last_name();
		return $name;
	}

	/**
	 * Get billing period
	 * eg: month, week, year
	 * @return string
	 */
	public function get_billing_period() {
		return $this->billing_period;
	}

	/**
	 * Get billing start date
	 * @return string
	 */
	public function get_billing_start_date( $format = 'Y-m-d' ) {
		return date( $format, strtotime( $this->billing_start_date ) );
	}

	/**
	 * Get the billing_state
	 * @return string
	 */
	public function get_billing_state() {
		return $this->billing_state;
	}

	/**
	 * Get the billing_zip
	 * @return string
	 */
	public function get_billing_zip() {
		return $this->billing_zip;
	}

	/**
	 * Get coupon amount
	 * @return int
	 */
	public function get_coupon_amount() {
		return $this->coupon_amount;
	}

	/**
	 * Get used coupon code
	 * @return string
	 */
	public function get_coupon_code() {
		return $this->coupon_code;
	}

	/**
	 * Get the amount of the coupon used applied to the first payment of a recurring order
	 * EG: 5% or 5.00
	 * @return string
	 */
	public function get_coupon_first_payment_amount() {
		return $this->coupon_first_payment_amount;
	}

	/**
	 * Get the applied value of discount applied to the first payment of a recurring order
	 * This is the calculated amount of $this->first_payment_original_total - $this->first_payment_total
	 * @return float
	 */
	public function get_coupon_first_payment_value() {
		return $this->coupon_first_payment_value;
	}

	/**
	 * Get used LifterLMS Coupon Post ID
	 * @return int
	 */
	public function get_coupon_id() {
		return absint( $this->coupon_id );
	}

	/**
	 * Get the amount of the coupon used applied to the recurring payments of a recurring order
	 * EG: 5% or 5.00
	 * @return string
	 */
	public function get_coupon_recurring_payment_amount() {
		return $this->coupon_recurring_payment_amount;
	}

	/**
	 * Get the actual value of a recurring payment discount
	 * calculated by $this->recurring_payment_original_total - $this->recurring_payment_total
	 * EG: 5.00
	 * @return float
	 */
	public function get_coupon_recurring_payment_value() {
		return $this->coupon_recurring_payment_value;
	}

	/**
	 * Get the type of coupon used
	 * Eg: perecent or dollar
	 * @return string
	 */
	public function get_coupon_type() {
		return $this->coupon_type;
	}

	/**
	 * Get the value of the used coupon
	 * This will be the calculated amount discounted based on
	 * the coupon amount, coupon type, and product amount
	 * @return float
	 */
	public function get_coupon_value() {
		return $this->coupon_value;
	}

	/**
	 * Get the discount type of a discounted order
	 * either "sale" or "coupon"
	 * @return string
	 */
	public function get_discount_type() {
		return $this->discount_type;
	}

	/**
	 * Get the discount value for a discounted order depending on the discount type
	 * @return float
	 */
	public function get_discount_value() {
		$type = $this->get_discount_type();
		if ( 'coupon' === $type ) {
			return $this->get_coupon_value();
		} elseif ( 'sale' === $type ) {
			return $this->get_sale_value();
		}
		return 0;
	}

	/**
	 * Get currency of transaction
	 * @return string
	 */
	public function get_currency() {
		return $this->currency;
	}

	/**
	 * Get the date the order was placed
	 * @param  string $format any date format that can be passed to php date()
	 * @return string
	 */
	public function get_date( $format = 'Y-m-d' ) {
		return date( $format, strtotime( $this->post->post_date ) );
	}

	/**
	 * Get the original (pre discount) total of the first payment for a recurring order
	 * @return float
	 */
	public function get_first_payment_original_total() {
		return $this->first_payment_orignal_total;
	}
	/**
	 * Get the total (adjusted for discounts) of the first payment for a recurring order
	 * @return float
	 */
	public function get_first_payment_total() {
		return $this->first_payment_total;
	}

	/**
	 * Get the formatted coupon amount
	 * includes the percentage or currency symbol depending on the type of coupon
	 *
	 * @param string $payment   the type of payment to format, either "single", "first", or "recurring"
	 *
	 * @return string
	 */
	public function get_formatted_coupon_amount( $payment = 'single' ) {

		switch ( $payment ) {
			case 'single':
					$amount = $this->get_coupon_amount();
			break;

			case 'first':
				$amount = $this->get_coupon_first_payment_amount();
			break;

			case 'recurring':
				$amount = $this->get_coupon_recurring_payment_amount();
			break;
		}

		$type = $this->get_coupon_type();
		if ( 'percent' === $type ) {
			$amount = $amount . '%';
		} elseif ( 'dollar' === $type ) {
			$amount = $amount;
		}
		return $amount;
	}

	/**
	 * Get the order key
	 * Order Key is a randomly generated ID that can be used to mask the actual
	 * order ID and be safely passed in the URL and elsewhere
	 * @return string
	 */
	public function get_order_key() {
		return $this->order_key;
	}

	/**
	 * Get the total before coupon or sale adjustments
	 * @return float
	 */
	public function get_original_total() {
		return $this->original_total;
	}

	/**
	 * Get the payment method
	 * Eg: gateway id (paypal, stripe)
	 * @return string
	 */
	public function get_payment_gateway() {
		return $this->payment_gateway;
	}

	/**
	 * Retreive the LifterLMS Payment Gateway Instance for the order's payment gateway
	 * @return false|obj
	 */
	public function get_payment_gateway_instance() {
		$id = $this->get_payment_gateway();
		$gateway = LLMS()->payment_gateways()->get_gateway_by_id( $id );
		return $gateway;
	}

	/**
	 * Retrieve the formatted Payment Gateway Title
	 * @return string
	 */
	public function get_payment_gateway_title() {
		$gateway = $this->get_payment_gateway_instance();
		if ( $gateway ) {
			return $gateway->get_title();
		} else {
			return $gateway;
		}
	}

	/**
	 * Get the payment type
	 * Eg: credit card, paypal, WooCommerce
	 * @return string
	 */
	public function get_payment_type() {
		return $this->payment_type;
	}

	/**
	 * Get the post ID of the LifterLMS Product (course or membership)
	 * @return int
	 */
	public function get_product_id() {
		return absint( $this->product_id );
	}

	/**
	 * Get the SKU of the LifterLMS Product (course or membership)
	 * @return string
	 */
	public function get_product_sku() {
		return $this->product_sku;
	}

	/**
	 * Get an array of subscription data that can be passed to various LLMS_Product methods
	 * @param bool $discounted   if truthy, return the discount-adjusted totals, otherwise return the original totals (before discounts)
	 * @return array
	 */
	public function get_product_subscription_array( $discounted = true ) {
		return array(
			'billing_cycle' => $this->get_billing_cycle(),
			'billing_freq' => $this->get_billing_frequency(),
			'billing_period' => $this->get_billing_period(),
			'total_price' => null, // @todo cleanup; this was originally calculated wrong isn't used I don't think
			'sub_price' => ( $discounted || ! $discounted && ! $this->get_coupon_id() ) ? $this->get_recurring_payment_total() : $this->get_recurring_payment_original_total(),
			'first_payment' => ( $discounted || ! $discounted && ! $this->get_coupon_id() ) ? $this->get_first_payment_total() : $this->get_first_payment_original_total(),
		);
	}

	/**
	 * Get the title of the LifterLMS Product (course or membership)
	 * @return string
	 */
	public function get_product_title() {
		return $this->product_title;
	}

	/**
	 * Get the type of LifterLMS Product
	 * Eg: course or membership
	 * @return string
	 */
	public function get_product_type() {
		if ( ! isset( $this->product_type ) ) {
			return get_post_type( $this->get_product_id() );
		}
		return $this->product_type;
	}

	/**
	 * Get the WP_Post object for the related Order post
	 * @return obj   Instance of WP_Post
	 */
	public function get_post_data() {
		return $this->post;
	}

	/**
	 * Get the pre discount total of recurring payments on a recurring order
	 * @return float
	 */
	public function get_recurring_payment_original_total() {
		return $this->recurring_payment_original_total;
	}

	/**
	 * Get the discount-adjusted total of recurring payments on a recurring order
	 * @return float
	 */
	public function get_recurring_payment_total() {
		return $this->recurring_payment_total;
	}

	/**
	 * Get the value of a sale discount for a single payment order on sale
	 * calculated by $this->get_original_total - $this->get_total
	 * @return float
	 */
	public function get_sale_value() {
		return $this->sale_value;
	}

	/**
	 * Get the current status of the order
	 * @return string
	 */
	public function get_status() {
		return $this->post->post_status;
	}

	/**
	 * Get the gateway's unique identifier for the order's recurring subscription
	 * @return string
	 */
	public function get_subscription_id() {
		return $this->subscription_id;
	}

	/**
	 * Get the date of the last order status sync with the gateway
	 * @param  string $format formatting string that can be passed to PHP date()
	 * @return string
	 */
	public function get_subscription_last_sync( $format = 'U' ) {
		return date( $format, $this->subscription_last_sync );
	}

	/**
	 * Get the Gateway's api mode for the transaction
	 * @return string    live or test
	 */
	public function get_transaction_api_mode() {
		return $this->transaction_api_mode;
	}

	/**
	 * Get the gateway's customer ID for the customer who placed the order
	 * @return string
	 */
	public function get_transaction_customer_id() {
		return $this->transaction_customer_id;
	}

	/**
	 * Get the gateway's transaction ID for the order's transaction
	 * @return string
	 */
	public function get_transaction_id() {
		return $this->transaction_id;
	}

	/**
	 * Get the actual order total after adjustments
	 * @return float
	 */
	public function get_total() {
		return $this->total;
	}

	/**
	 * Get the order type
	 * Eg: recurring, single
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Get the associated WordPress User
	 * @return false|obj      false if no user found or an instance of WP_User
	 */
	public function get_user() {
		return $this->get_user_id() ? get_user_by( 'id', $this->get_user_id() ) : false;
	}

	/**
	 * Get user id
	 * @return int
	 */
	public function get_user_id() {
		return absint( $this->user_id );
	}

	/**
	 * Get the user ip address at time of purchase
	 * @return string
	 */
	public function get_user_ip_address() {
		return $this->user_ip_address;
	}


	/*
		  /$$$$$$              /$$     /$$
		 /$$__  $$            | $$    | $$
		| $$  \__/  /$$$$$$  /$$$$$$ /$$$$$$    /$$$$$$   /$$$$$$   /$$$$$$$
		|  $$$$$$  /$$__  $$|_  $$_/|_  $$_/   /$$__  $$ /$$__  $$ /$$_____/
		 \____  $$| $$$$$$$$  | $$    | $$    | $$$$$$$$| $$  \__/|  $$$$$$
		 /$$  \ $$| $$_____/  | $$ /$$| $$ /$$| $$_____/| $$       \____  $$
		|  $$$$$$/|  $$$$$$$  |  $$$$/|  $$$$/|  $$$$$$$| $$       /$$$$$$$/
		 \______/  \_______/   \___/   \___/   \_______/|__/      |_______/
	*/

	/**
	 * Set the status of the order
	 * @param string $status   new status, eg "llms-pending"
	 *                         the string will be automatically prefixed with "llms-" if it's not included
	 * @return  boolean
	 */
	public function update_status( $status ) {

		// can't update status without an id
		if ( ! $this->get_id() ) {
			return false;
		}

		// auto prefix
		if ( strpos( $status, 'llms-' ) !== 0 ) {
			$status = 'llms-' . $status;
		}

		// verify this is a real order status before updating
		if ( ! in_array( $status, array_keys( llms_get_order_statuses() ) ) ) {
			return false;
		} else {

			$old = str_replace( 'llms-', '', $this->get_status() ); // for transition action later

			$update = wp_update_post( array(
				'ID' => $this->get_id(),
				'post_status' => $status,
			) );

			if ( $update ) {

				$new = str_replace( 'llms-', '', $status ); // unprefixed for actions
				do_action( 'lifterlms_order_status_' . $old . '_to_' . $new, $this );
				do_action( 'lifterlms_order_status_' . $new, $this );
				return true;

			}

			return false;
		}

	}




	public function format_price( $price, $dp = 2, $currency = true ) {

		if ( $currency ) {
			$currency = get_lifterlms_currency_symbol( $this->get_currency() );
		}

		return llms_price( $price, array(
			'decimal_places' => $dp,
			'with_currency'  => $currency,
		) );

	}


}
