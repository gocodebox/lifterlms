<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Base Product Class
*
* Class used for instantiating Product object
*/
class LLMS_Product {

	/**
	* ID
	* @access public
	* @var int
	*/
	public $id;


	/**
	* Post Object
	* @access public
	* @var array
	*/
	public $post;


	/**
	* Constructor
	*
	* initializes the product object based on post data
	*/
	public function __construct( $product ) {

		if ( is_numeric( $product ) ) {

			$this->id   = absint( $product );
			$this->post = get_post( $this->id );

		} elseif ( $product instanceof LLMS_Product ) {

			$this->id   = absint( $product->id );
			$this->post = $product;

		} elseif ( isset( $product->ID ) ) {

			$this->id   = absint( $product->ID );
			$this->post = $product;

		}

	}

	/**
	* __isset function
	*
	* checks if metadata exists
	*
	* @param string $item
	*/
	public function __isset( $item ) {

		return metadata_exists( 'post', $this->id, '_' . $item );

	}

	/**
	* __get function
	*
	* initializes the product object based on post data
	*
	* @param string $item
	* @return string $value
	*/
	public function __get( $item ) {

		$value = get_post_meta( $this->id, '_' . $item, true );

		return $value;
	}

	/**
	 * Get the Product ID
	 * @return int
	 *
	 * @since  3.0.0
	 */
	public function get_id() {
		return absint( $this->id );
	}

	/**
	 * Get SKU
	 *
	 * @return string
	 */
	public function get_sku() {
		return $this->sku;
	}

	/**
	 * Get the product type (course or membership)
	 * @return string
	 *
	 * @since  3.0.0
	 */
	public function get_type() {
		return $this->post->post_type;
	}

	/**
	 * Get product payment options
	 * @return array $options [available payment option ids]
	 */
	public function get_payment_options() {

		$options = array();

		if ( $this->is_free() ) {
			array_push( $options, 'free' );
		}

		if ( $this->get_regular_price() ) {
			array_push( $options, 'single' );
		}

		if ( $this->is_recurring() ) {
			array_push( $options, 'recurring' );
		}

		return apply_filters( 'lifterlms_product_get_payment_options', $options, $this );
	}

	/**
	 * Get subscriptions
	 * @return array [array of subscription ids]
	 */
	public function get_subscriptions() {
		return $this->llms_subscriptions;
	}

	/**
	 * Get billing period
	 * @param  int $sub [id of the subscription]
	 * @return int [billing period id]
	 */
	public function get_billing_period( $sub ) {
		return $sub['billing_period'];
	}

	/**
	 * Get billing frequency
	 * @param  int $sub [id of subscription]
	 * @return int [billing frequency]
	 */
	public function get_billing_freq( $sub ) {
		return $sub['billing_freq'];
	}

	/**
	 * Get billing cycles
	 * @param  int $sub [sub id]
	 * @return int [billing cycles]
	 */
	public function get_billing_cycle( $sub ) {
		return $sub['billing_cycle'];
	}

	/**
	 * Get subscription total price
	 * Total price of the subscription
	 *
	 * @param  int $sub [sub id]
	 * @return int [total price]
	 */
	public function get_subscription_total_price( $sub ) {
		return $sub['total_price'];
	}

	/**
	 * Get sub first payment
	 * @param  int $sub [sub id]
	 * @return int [first payment amount]
	 */
	public function get_subscription_first_payment( $sub ) {
		return $sub['first_payment'];
	}

	/**
	 * Get subscription payment price
	 * @param int $sub [sub id]
	 * @return int     [recurring sub price amount]
	 */
	public function get_subscription_payment_price( $sub ) {
		return $sub['sub_price'];
	}

	/**
	 * Get subscription html output
	 * @param  int $sub [sub id]
	 * @return string [formatted dollar amount]
	 */
	public function get_subscription_price_html( $sub, $coupon_id = null ) {

		$price = '';

		$sub_price = $this->get_subscription_payment_price( $sub );
		$sub_first_payment = $this->get_subscription_first_payment( $sub );

		if ( $coupon_id ) {

			$adjusted_sub_price = $this->get_coupon_adjusted_price( $sub_price, $coupon_id, 'recurring' );
			$adjusted_sub_first_payment = $this->get_coupon_adjusted_price( $sub_first_payment, $coupon_id, 'first' );

			if ( $adjusted_sub_price != $sub_price ) {
				$sub_price = $this->get_adjusted_price_html( $sub_price, $adjusted_sub_price );
			} else {
				$sub_price = $this->get_formatted_price( $sub_price );
			}

			if ( $adjusted_sub_first_payment != $sub_first_payment ) {
				$sub_first_payment = $this->get_adjusted_price_html( $sub_first_payment, $adjusted_sub_first_payment );
			} else {
				$sub_first_payment = $this->get_formatted_price( $sub_first_payment );
			}

		} else {

			$sub_price = $this->get_formatted_price( $sub_price );
			$sub_first_payment = $this->get_formatted_price( $sub_first_payment );

		}

		$billing_period = $this->get_billing_period( $sub );
		$billing_freq = $this->get_billing_freq( $sub );
		$billing_cycle = $this->get_billing_cycle( $sub );

		// display billing period based on frequency
		if ($billing_freq > 1) {
			$billing_period_html = sprintf( _x( 'every %s %ss', 'billing recurrence interval', 'lifterlms' ), $billing_freq, $billing_period );
		} else {
			$billing_period_html = sprintf( _x( 'per %s', 'billing frequency interval', 'lifterlms' ), $billing_period );
		}

		// if first payment is different from recurring payment display first payment.
		if ( $sub_first_payment != $sub_price) {

			if ( $sub_first_payment ) {

				$price = sprintf( _x( '%s then ', 'billing first payment', 'lifterlms' ), $sub_first_payment );

			} else {

				$price = sprintf( _x( 'First %s free then ', 'billing first payment', 'lifterlms' ), $billing_period );

			}

		}

		if ( $billing_cycle == 0 ) {
			$price .= ($sub_price . ' ' . $billing_period_html);
		} elseif ( $billing_cycle > 1 ) {
			$price .= sprintf( _x( '%s %s for %s %ss', 'billing cycle', 'lifterlms' ), $sub_price, $billing_period_html, $billing_cycle, $billing_period );
		} else {
			$price .= sprintf( _x( '%s %s for %s %s', 'billing without cycle', 'lifterlms' ), $sub_price, $billing_period_html, $billing_cycle, $billing_period );
		}

		return apply_filters( 'lifterlms_recurring_price_html', $price, $this );;
	}

	/**
	 * Get checkout url
	 *
	 * @return string
	 */
	public function get_checkout_url() {

		$memberships_required = get_post_meta( $this->id, '_llms_restricted_levels', true );

		if ( $memberships_required ) {

			//if there is more than 1 membership that can view the content then redirect to memberships page
			if ( count( $memberships_required ) > 1) {
				return get_permalink( llms_get_page_id( 'memberships' ) );
			} //if only 1 membership level is assigned take visitor to the membership page
			else {
				return get_permalink( $memberships_required[0] );
			}
		} else {

			if ( get_option( 'lifterlms_secondary_checkout_process', false ) === 'yes' || is_user_logged_in() ) {
				$checkout_page_id = llms_get_page_id( 'checkout' );
			} else {
				$checkout_page_id = llms_get_page_id( 'myaccount' );
			}

			$account_url = get_permalink( $checkout_page_id );

			return add_query_arg( 'product-id', $this->id, $account_url );
		}
	}


	/**
	 * Retrive the HTML for a single purchase of a product
	 * optionally adjusted by a coupon discount
	 *
	 * @param  int   $coupon_id  (optional) WP Post ID of a LifterLMS Coupon
	 *                           If submitted, will adjust the price by settings
	 *                           Defined by the Coupon
	 *
	 * @return string
	 *
	 * @since    2.2.0
	 * @version  3.0.0
	 */
	public function get_single_price_html( $coupon_id = null ) {

		if ( $this->is_custom_single_price() ) {

			return $this->get_custom_single_price_html();

		}

		$regular_price = $this->get_regular_price();
		$sale_price = $this->get_sale_price();
		$currency_symbol = get_lifterlms_currency_symbol();
		$adjusted_price = false;

		if ( $regular_price > 0 ) {

			// make coupon adjustments
			if ( $coupon_id && $regular_price ) {

				$adjusted_price = apply_filters( 'lifterlms_coupon_price_html', $this->get_coupon_adjusted_price( $this->get_regular_price(), $coupon_id ), $this );

			} // Check if price is on sale and base price exists
			elseif ( $this->is_on_sale() && $regular_price ) {

				$adjusted_price = apply_filters( 'lifterlms_sale_price_html', $this->get_sale_price(), $this );

			}

			if ( false !== $adjusted_price && $regular_price != $adjusted_price ) {

				$price = $this->get_adjusted_price_html( $regular_price, $adjusted_price );

			} else {

				$price = $regular_price;

			}

		} elseif ( ! $regular_price ) { // empty or 0

			$price = $this->set_price_html_as_free();

		} else {

			$price = $regular_price;

		}

		// add display text prefix
		if ( ! $this->is_free() ) {

			$price = __( 'single payment of', 'lifterlms' ) . ' ' . $price;

		}

		return apply_filters( 'lifterlms_get_single_price_html', $price, $this );

	}

	/**
	 * Get price in html format
	 *
	 * @todo  eventually deprecate this in favor of get_single_price_html()
	 *
	 * @return string
	 */
	public function get_price_html( $price = '' ) {

		return $this->get_single_price_html();

	}

	public function get_recurring_price() {
		return apply_filters( 'lifterlms_get_recurring_price', $this->llms_subscription_price, $this );
	}

	/**
	 * Get recurring price html output
	 * Formatted string representation of recurring price, cycle, frequency and first payment
	 *
	 * @todo  potentially deprecate
	 *
	 * @return string [formatted string representing price]
	 */
	public function get_recurring_price_html() {

		$price = '';
		$currency_symbol = get_lifterlms_currency_symbol();
		$recurring_price = $this->get_recurring_price();
		$recurring_first_payment = $this->get_recurring_first_payment();

		$display_price = ($currency_symbol . $recurring_price);
		$billing_period = $this->get_billing_period();
		$billing_freq = $this->get_billing_freq();
		$billing_cycle = $this->get_billing_cycle();

		// display billing period based on frequency
		if ($billing_freq > 1) {
			$billing_period_html = sprintf( _x( 'every %s %ss', 'billing recurrence interval', 'lifterlms' ), $billing_freq, $billing_period );
		} else {
			$billing_period_html = sprintf( _x( 'per %s', 'billing frequency interval', 'lifterlms' ), $billing_period );
		}

		// if first payment is different from recurring payment display first payment.
		if ($recurring_first_payment != $recurring_price) {
			$price = sprintf( _x( '%s%s then ', 'billing second payment', 'lifterlms' ), $currency_symbol, $recurring_first_payment );
		}

		if ( $billing_cycle == 0 ) {
			$price .= ($display_price . ' ' . $billing_period_html);
		} elseif ( $billing_cycle > 1 ) {
			$price .= sprintf( _x( '%s %s for %s %ss', 'billing cycle', 'lifterlms' ), $display_price, $billing_period_html, $billing_cycle, $billing_period );
		} else {
			$price .= sprintf( _x( '%s %s for %s %s', 'billing without cycle', 'lifterlms' ), $display_price, $billing_period_html, $billing_cycle, $billing_period );
		}

		return apply_filters( 'lifterlms_recurring_price_html', $price, $this );
	}


	/**
	 * Set price html to Free is ocurse is 0
	 *
	 * @return string
	 */
	public function set_price_html_as_free() {

		if ( $this->is_on_sale() && $this->get_regular_price() ) {

			$price = $this->get_adjusted_price_html( $this->get_regular_price(), __( 'Free!', 'lifterlms' ) );

			$price = apply_filters( 'lifterlms_free_sale_price_html', $price, $this );

		} else {

			$price = __( 'Free!', 'lifterlms' );

			$price = apply_filters( 'lifterlms_free_price_html', $price, $this );

		}

		return $price;

	}

	/**
	 * Determine if custom price output is enabled
	 * @return boolean
	 */
	public function is_custom_single_price() {
		return $this->is_custom_single_price;
	}


	/**
	 * Retrive the value of the custom price output
	 * @return string
	 */
	public function get_custom_single_price_html() {
		return apply_filters( 'lifterlms_get_custom_single_price_html', $this->custom_single_price_html, $this );
	}

	/**
	 * Check: Is the sale price different than the base price and is the sale price equal to the price returned from get_price().
	 *
	 * @return bool
	 */
	public function is_on_sale() {

		if ( $this->on_sale ) {

			$now = current_time( 'timestamp' );

			$start = $this->get_sale_start_date() . ' 00:00:00';
			$end = $this->get_sale_end_date() . ' 23:23:59'; // make the end of the day

			// start and end
			if ( $start && $end ) {

				return ( $now < strtotime( $end ) && $now > strtotime( $start ) );

			} // only start
			elseif ( $start && ! $end ) {

				return ( $now > strtotime( $start ) );

			} // only end
			elseif ( ! $start && $end ) {

				return ( $now < strtotime( $end ) );

			} // neither start nor end
			else {

				return true;

			}

		}

		return false;

	}

	public function get_coupon_discount_total( $price = '' ) {
		$adjustment = llms_get_coupon();
	    $total = $price;

		if ( ! empty( $adjustment ) && $adjustment->amount > 0 ) {
			if ($this->id == $adjustment->product_id) {
				if ( ( $adjustment->limit >= 0 ) || ( $adjustment->limit === 'unlimited' ) ) {
				    if ($adjustment->type == 'percent') {

						$amount = ($adjustment->amount / 100);

						$total = ($price * $amount);
						$total = sprintf( '%0.2f', $total );

				    } elseif ($adjustment->type == 'dollar') {
						$amount = round( $adjustment->amount, 2 );
						$total = ($amount);
						$total = sprintf( '%0.2f', $total );
				    }
				}
			}
		}
		return $total;
	}

	/**
	 * Adjust the price of a product based on a supplied coupon
	 * @param  float           $price     Price being adjusted
	 * @param  int|string|obj  $coupon_id Coupon ID, Coupon String, LLMS_Coupon Instance, WP_Post instance of an LLMS Coupon
	 * @param  string          $type      Payment type (single, recurring, first recurring)
	 * @return float
	 *
	 * @version  3.0.0
	 */
	public function get_coupon_adjusted_price( $price, $coupon_id, $type = 'single' ) {

		// instatiate the coupon
		$coupon = new LLMS_Coupon( $coupon_id );

		// if the coupon is valid, make adjustments
		if ( ! is_wp_error( $coupon->is_valid( $this->get_id() ) ) ) {

			// get the discount amount based on payment type
			switch ( $type ) {

				case 'first':
					$discount = $coupon->get_recurring_first_payment_amount();
				break;

				case 'recurring':
					$discount = $coupon->get_recurring_payments_amount();
				break;

				case 'single':
					$discount = $coupon->get_single_amount();
				break;

				default:
					/**
					 * Allow filtering of the price if the payment type is not defined above
					 * @var float
					 */
					$discount = apply_filters( 'lifterlms_get_coupon_adjusted_price_for_' . $type . '_payment', 0, $this, $coupon );

			}

			// make sure we have a float we can work with
			$price = floatval( $price );

			// discount type
			$discount_type = $coupon->get_discount_type();
			if ( 'percent' === $discount_type ) {

				$price = $price - ( $price * ( $discount / 100 ) );

			} elseif ( 'dollar' === $discount_type ) {

				$price = $price - $discount;

			}

			// adjust negative prices to be FREE
			// we're not paying folks to use our coupons, are we?
			$price = ( $price < 0 ) ? 0 : round( $price, 2 );

		}

		return $price;

	}


	/**
	 * Get function for price value.
	 *
	 * @return void
	 */
	public function get_price() {

		if ( $this->is_on_sale() ) {

			$price = $this->sale_price;

		} else {

			$price = $this->regular_price;

		}

		return apply_filters( 'lifterlms_get_price', $price, $this );

	}

	/**
	 * Get single purchase price
	 * @return int [single purchase price]
	 */
	public function get_single_price() {
		return apply_filters( 'lifterlms_get_single_price', $this->get_price(), $this );
	}

	/**
	 * Get recurring first payment price
	 * @return  int [first payment amount]
	 */
	public function get_recurring_first_payment() {

		return apply_filters( 'lifterlms_get_recurring_first_price', $this->get_coupon_adjusted_price( $this->llms_subscription_first_payment ), $this );
	}

	/**
	 * Get next recurring payment date
	 *
	 * @param  int $sub [sub id]
	 * @return datetime [date of next payment]
	 */
	public function get_recurring_next_payment_date( $sub ) {

		$billing_period = $this->get_billing_period( $sub );

		$billing_freq = $this->get_billing_freq( $sub );
		$billing_freq = $billing_freq > 0 ? $billing_freq : 1;

		switch ($billing_period) {
			case 'day':
				$next_payment_date = date( 'Y-m-d', strtotime( ' +' . $billing_freq . ' day' ) );
				break;
			case 'week':
				$next_payment_date = date( 'Y-m-d', strtotime( ' +' . $billing_freq . ' week' ) );
				break;
			case 'month':
				$next_payment_date = date( 'Y-m-d', strtotime( ' +' . $billing_freq . ' month' ) );
				break;
			case 'year':
				$next_payment_date = date( 'Y-m-d', strtotime( ' +' . $billing_freq . ' year' ) );
				break;

		}

		return $next_payment_date;
	}


	/**
	 * Get function for price value.
	 *
	 * @return void
	 */
	public function is_recurring() {
		return $this->llms_recurring_enabled;
	}

	/**
	 * Set function for price value.
	 *
	 * @return void
	 */
	public function set_price( $price ) {

		$this->price = $price;

	}

	/**
	 * get the base price value.
	 *
	 * @return void
	 */
	public function get_regular_price( $price = '' ) {

		return $this->regular_price;

	}

	/**
	 * get the base price value.
	 *
	 * @return void
	 */
	public function get_sale_price( $price = '' ) {

		return $this->sale_price;

	}

	/**
	 * Returns base price and sale price in html format.
	 *
	 * @return string
	 */
	public function get_adjusted_price_html( $base, $adjusted ) {

		if ( 0 == absint( $adjusted ) ) {

			$adjusted = __( 'Free!', 'lifterlms' );

		}

		return '<del>' . $this->get_formatted_price( $base ) . '</del> <ins>' . $this->get_formatted_price( $adjusted ) . '</ins>';

	}


	/**
	 * Retrive the sale start date
	 * @return string
	 */
	public function get_sale_start_date() {

		return $this->sale_price_dates_from;

	}

	/**
	 * Retrive the sale end date
	 * @return string
	 */
	public function get_sale_end_date() {
		return $this->sale_price_dates_to;
	}

	/**
	 * Determine if a product is free
	 * @return boolean
	 */
	public function is_free() {

		return ( ! $this->get_price() && ! $this->is_recurring() );

	}

	/**
	 * Retrieve a price formatted by llms_price for this product
	 *
	 * @param  mixed  $price  numeric price
	 * @return string
	 */
	public function get_formatted_price( $price ) {

		if ( ! is_numeric( $price ) ) {
			return $price;
		}

		$price_args = apply_filters( 'lifterlms_product_price_display_arguments', array(
			'with_currency' => true,
			'decimal_places' => 2,
			'trim_zeros' => false,
		), $this );

		return llms_price( $price, $price_args );

	}



	public function get_title() {
		return $this->post->post_title;
	}



	/**
	 * Retrieves all relivent post meta for order
	 *
	 * @param  [int] $id [Id of order]
	 * @return [object]     [Object containing all post meta relivent to oreder]
	 */
	public static function get_order_data( $id ) {
		global $wpdb;

		$order = new \stdClass();

		// Get Auction Meta
		$pm = get_post_meta( $id );

		$order->id = $id;
		$order->user_id = isset( $pm['_llms_user_id'][0] ) ? $pm['_llms_user_id'][0] : '';

		//product information
		$order->product_id = isset( $pm['_llms_order_product_id'][0] ) ? $pm['_llms_order_product_id'][0] : '';
		$order->product_title = get_the_title( $order->id );
		$order->product_title = isset( $pm['_llms_product_title'][0] ) ? $pm['_llms_product_title'][0] : '';
		$order->product_sku = isset( $pm['_llms_product_sku'][0] ) ? $pm['_llms_product_sku'][0] : '';

		//order information
		$order->order_currency = isset( $pm['_llms_order_currency'][0] ) ? $pm['_llms_order_currency'][0] : '';
		$order->order_date = isset( $pm['_llms_order_date'][0] ) ? LLMS_Date::db_date( $pm['_llms_order_date'][0] ) : '';
		$order->order_time = isset( $pm['_llms_order_date'][0] ) ? $pm['_llms_order_date'][0] : '';
		$order->order_type = isset( $pm['_llms_order_type'][0] ) ? $pm['_llms_order_type'][0] : '';

		//recurring payment information
		$order->order_recurring_price = isset( $pm['_llms_order_recurring_price'][0] ) ? $pm['_llms_order_recurring_price'][0] : '';
		$order->order_first_payment = isset( $pm['_llms_order_first_payment'][0] ) ? $pm['_llms_order_first_payment'][0] : 0;
		$order->order_billing_period = isset( $pm['_llms_order_billing_period'][0] ) ? $pm['_llms_order_billing_period'][0] : '';
		$order->order_billing_cycle = isset( $pm['_llms_order_billing_cycle'][0] ) ? $pm['_llms_order_billing_cycle'][0] : '';
		$order->order_billing_freq = isset( $pm['_llms_order_billing_freq'][0] ) ? $pm['_llms_order_billing_freq'][0] : '';
		$order->order_billing_start_date = isset( $pm['_llms_order_billing_start_date'][0] ) ? $pm['_llms_order_billing_start_date'][0] : '';

		//payment information
		$order->payment_type = isset( $pm['_llms_payment_type'][0] ) ? $pm['_llms_payment_type'][0] : '';
		$order->payment_method = isset( $pm['_llms_payment_method'][0] ) ? $pm['_llms_payment_method'][0] : '';
		$order->order_total = isset( $pm['_llms_order_total'][0] ) ? $pm['_llms_order_total'][0] : 0;

		//coupon information
		$order->coupon_id = isset( $pm['_llms_order_coupon_id'][0] ) ? $pm['_llms_order_coupon_id'][0] : '';
		$order->coupon_type = isset( $pm['_llms_order_coupon_type'][0] ) ? $pm['_llms_order_coupon_type'][0] : '';
		$order->coupon_amount = isset( $pm['_llms_order_coupon_amount'][0] ) ? $pm['_llms_order_coupon_amount'][0] : 0;
		$order->coupon_limit = isset( $pm['_llms_order_coupon_limit'][0] ) ? $pm['_llms_order_coupon_limit'][0] : '';
		$order->coupon_code = isset( $pm['_llms_order_coupon_code'][0] ) ? $pm['_llms_order_coupon_code'][0] : '';

		//paypal recurring specific
		if ( $order->payment_type === 'paypal' && $order->order_type === 'recurring' ) {
			//paypal profile id for recurring payments
			$order->paypal_profile_id = $pm['_llms_order_paypal_profile_id'][0];
		}

		return $order;
	}

}
