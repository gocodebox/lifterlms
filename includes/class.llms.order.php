<?php
/**
 * LifterLMS Order Class
 *
 * Handle all data related to an Order
 *
 * @package     LifterLMS/Classes
 * @category    Class
 * @author      LifterLMS
 * @since  2.7.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Order {

	private $prefix = '_llms_';

	/**
	 * Constructor
	 *
	 * @param mixed  Order Post ID, Instance of LLMS_Order, Instance of WP_Post
	 *
	 * @return  void
	 *
	 * @since  2.7.0
	 */
	public function __construct( $order ) {

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

		$value = get_post_meta( $this->id, $this->prefix . $key, true );

		return $value;

	}

	public function __isset( $key ) {
		return metadata_exists( 'post', $this->id, $this->prefix . $key );
	}

	/**
	 * Get billing cycle
	 * @return int
	 */
	public function get_billing_cycle() {
		return apply_filters( 'lifterlms_order_billing_cycle', $this->billing_cycle, $this );
	}

	/**
	 * Retrieve the email of the customer
	 * @return string
	 */
	public function get_billing_email() {
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
		return apply_filters( 'lifterlms_order_billing_email', $email );
	}

	/**
	 * Retrieve the first name of the customer
	 * @return string
	 */
	public function get_billing_first_name() {
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
		return apply_filters( 'lifterlms_order_billing_first_name', $name );
	}

	/**
	 * Get billing freq
	 * @return int
	 */
	public function get_billing_frequency() {
		return apply_filters( 'lifterlms_order_billing_frequency', $this->billing_frequency, $this );
	}

	/**
	 * Retrieve the first name of the customer
	 * @return string
	 */
	public function get_billing_last_name() {
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
		return apply_filters( 'lifterlms_order_billing_last_name', $name );
	}

	/**
	 * Get the full name (first and last) of the customer
	 * @return string
	 */
	public function get_billing_name() {
		$name = $this->get_billing_first_name() . ' ' . $this->get_billing_last_name();
		return apply_filters( 'lifterlms_order_billing_name', $name );
	}

	/**
	 * Get billing period
	 * eg: month, week, year
	 * @return string
	 */
	public function get_billing_period() {
		return apply_filters( 'lifterlms_order_billing_period', $this->billing_period, $this );
	}

	/**
	 * Get billing start date
	 * @return string
	 */
	public function get_billing_start_date() {
		return apply_filters( 'lifterlms_order_billing_start_date', $this->billing_start_date, $this );
	}

	/**
	 * Get coupon amount
	 * @return int
	 */
	public function get_coupon_amount() {
		return apply_filters( 'lifterlms_order_coupon_amount', $this->coupon_amount, $this );
	}

	/**
	 * Get used coupon code
	 * @return string
	 */
	public function get_coupon_code() {
		return apply_filters( 'lifterlms_order_coupon_code', $this->coupon_code, $this );
	}

	/**
	 * Get used LifterLMS Coupon Post ID
	 * @return int
	 */
	public function get_coupon_id() {
		return absint( $this->coupon_id );
	}

	/**
	 * Get remaining coupon limit at time of order
	 * @return int|string
	 */
	public function get_coupon_limit() {
		return apply_filters( 'lifterlms_order_coupon_limit', $this->coupon_limit, $this );
	}

	/**
	 * Get the type of coupon used
	 * Eg: perecent or dollar
	 * @return string
	 */
	public function get_coupon_type() {
		return apply_filters( 'lifterlms_order_coupon_type', $this->coupon_type, $this );
	}

	/**
	 * Get the value of the used coupon
	 * This will be the calculated amount discounted based on
	 * the coupon amount, coupon type, and product amount
	 * @return float
	 */
	public function get_coupon_value() {
		return apply_filters( 'lifterlms_order_coupon_value', $this->coupon_value, $this );
	}

	/**
	 * Get currency of transaction
	 * @return string
	 */
	public function get_currency() {
		return apply_filters( 'lifterlms_order_currency', $this->currency, $this );
	}

	/**
	 * Get the date the order was placed
	 * @return string
	 */
	public function get_date() {
		return apply_filters( 'lifterlms_order_date', $this->date, $this );
	}

	/**
	 * Get the amount of the first payment
	 * Applies to recurring transactions only
	 * @return float
	 */
	public function get_first_payment() {
		return apply_filters( 'lifterlms_order_first_payment', $this->first_payment, $this );
	}

	/**
	 * Retrieve the formatted coupon amount
	 * includes the percentage or currency symbol depending on the type of coupon
	 * @return string
	 */
	public function get_formatted_coupon_amount() {
		$amount = $this->get_coupon_amount();
		if ( 'percent' === $this->get_coupon_type() ) {
			$amount = $amount . '%';
		} elseif ( 'dollar' === $coupon_type ) {
			$amount = get_lifterlms_currency_symbol( $this->get_currency() ) . $amount;
		}
		return apply_filters( 'lifterlms_order_formatted_coupon_amount', $amount, $this );
	}

	/**
	 * Get the Order ID
	 * @return int
	 */
	public function get_id() {
		return absint( $this->id );
	}

	/**
	 * Get the total before coupon adjustments
	 * @return float
	 */
	public function get_original_total() {
		return apply_filters( 'lifterlms_order_original_total', $this->original_total, $this );
	}

	/**
	 * Get the payment method
	 * Eg: gateway id (paypal, stripe)
	 * @return string
	 */
	public function get_payment_method() {
		return apply_filters( 'lifterlms_order_payment_method', $this->payment_method, $this );
	}

	/**
	 * Get the payment type
	 * Eg: credit card, paypal, WooCommerce
	 * @return string
	 */
	public function get_payment_type() {
		return apply_filters( 'lifterlms_order_payment_type', $this->payment_type, $this );
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
		return apply_filters( 'lifterlms_order_product_sku', $this->product_sku, $this );
	}

	/**
	 * Get the price of the LifterLMS Product (course or membership)
	 * @return float
	 */
	public function get_product_price() {
		return apply_filters( 'lifterlms_order_product_price', $this->product_price, $this );
	}

	/**
	 * Get the title of the LifterLMS Product (course or membership)
	 * @return string
	 */
	public function get_product_title() {
		return apply_filters( 'lifterlms_order_product_title', $this->product_title, $this );
	}

	/**
	 * Get the type of LifterLMS Product
	 * Eg: course or membership
	 * @return string
	 */
	public function get_product_type() {
		return apply_filters( 'lifterlms_order_product_type', $this->product_type, $this );
	}

	/**
	 * Get the WP_Post object for the related Order post
	 * @return obj   Instance of WP_Post
	 */
	public function get_post_data() {
		return $this->post;
	}

	/**
	 * Get the recurring payments price
	 * Applies only to recurring orders
	 * @return float
	 */
	public function get_recurring_price() {
		return apply_filters( 'lifterlms_order_recurring_price', $this->recurring_price, $this );
	}

	/**
	 * Get the actual order total after adjustments
	 * @return float
	 */
	public function get_total() {
		return apply_filters( 'lifterlms_order_total', $this->total, $this );
	}

	/**
	 * Get the order type
	 * Eg: recurring, single
	 * @return string
	 */
	public function get_type() {
		return apply_filters( 'lifterlms_order_type', $this->type, $this );
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

}
