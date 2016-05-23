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
	 * Get SKU
	 *
	 * @return string
	 */
	public function get_sku() {

		return $this->sku;

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

		if ( $this->get_price() ) {
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
	public function get_subscription_price_html( $sub ) {

		$price = '';
		$currency_symbol = get_lifterlms_currency_symbol();
		$sub_price = $this->adjusted_price( $this->get_subscription_payment_price( $sub ) );
		$sub_first_payment = $this->adjusted_price( $this->get_subscription_first_payment( $sub ) );

		$suffix = $this->get_price_suffix_html();
		$display_price = ($currency_symbol . $sub_price);
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
		if ($sub_first_payment != $sub_price) {
			$price = sprintf( _x( '%s%s then ', 'billing first payment', 'lifterlms' ), $currency_symbol, $sub_first_payment );
		}

		if ( $billing_cycle == 0 ) {
			$price .= ($display_price . ' ' . $billing_period_html);
		} elseif ( $billing_cycle > 1 ) {
			$price .= sprintf( _x( '%s %s for %s %ss', 'billing cycle', 'lifterlms' ), $display_price, $billing_period_html, $billing_cycle, $billing_period );
		} else {
			$price .= sprintf( _x( '%s %s for %s %s', 'billing without cycle', 'lifterlms' ), $display_price, $billing_period_html, $billing_cycle, $billing_period );
		}

		return apply_filters( 'lifterlms_recurring_price_html', $price, $this );;
	}

	/**
	 * Get checkout url
	 *
	 * @return string
	 */
	public function get_checkout_url() {

		$checkout_page_id = llms_get_page_id( 'checkout' );
		$checkout_url = apply_filters( 'lifterlms_get_checkout_url', $checkout_page_id ? get_permalink( $checkout_page_id ) : '' );

		return add_query_arg( 'product-id', $this->id, $checkout_url );

	}


	/**
	 * Retrive the HTML for a single purchase of a product
	 *
	 * @since  2.2.0
	 *
	 * @param  string $price [description]
	 * @return [type]        [description]
	 */
	public function get_single_price_html( $price = '' ) {

		if ( $this->is_custom_single_price() ) {

			return $this->get_custom_single_price_html();

		}

		$suffix 				= $this->get_price_suffix_html();
		$currency_symbol 		= get_lifterlms_currency_symbol() != '' ? get_lifterlms_currency_symbol() : '';
		$display_price 			= $this->adjusted_price( $this->get_price() );
		$display_base_price 	= $this->get_regular_price();
		$display_sale_price    	= $this->get_sale_price();

		if ( $this->get_price() > 0 ) {

			$price = $this->set_price_html_as_value( $suffix, $currency_symbol, $display_price, $display_base_price, $display_sale_price );

		} elseif ( $this->get_price() === '' ) {

			$price = apply_filters( 'lifterlms_empty_price_html', '', $this );

		} elseif ( $this->get_price() == 0 ) {

			$price = $this->set_price_html_as_free();

		}

		/**
		 * @todo eventually deprecate this filter in favor of the single price equivalent
		 */
		$price = apply_filters( 'lifterlms_get_price_html', $price, $this );

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
	 * @return string [formatted string representing price]
	 */
	public function get_recurring_price_html() {

		$price = '';
		$currency_symbol = get_lifterlms_currency_symbol();
		$recurring_price = $this->get_recurring_price();
		$recurring_first_payment = $this->get_recurring_first_payment();

		$suffix = $this->get_price_suffix_html();
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

		return apply_filters( 'lifterlms_recurring_price_html', $price, $this );;
	}


	/**
	 * Set price html to a decimal value with currency and suffix.
	 *
	 * @return string
	 */
	public function set_price_html_as_value( $suffix, $currency_symbol, $display_price, $display_base_price, $display_sale_price ) {

		// Check if price is on sale and base price exists
		if ( $this->is_on_sale() && $this->get_regular_price() ) {

			//generate price with formatting and suffix
			$price = $currency_symbol;

			$price .= $this->get_price_variations_html( $display_base_price, $display_price ) . $suffix;

			$price = apply_filters( 'lifterlms_sale_price_html', $price, $this );

		} else {

			//generate price with formatting and suffix
			$price = $currency_symbol;

			$price .= llms_price( $display_price ) . $suffix;

			$price = apply_filters( 'lifterlms_price_html', $price, $this );

		}

		return $price;

	}

	/**
	 * Set price html to Free is ocurse is 0
	 *
	 * @return string
	 */
	public function set_price_html_as_free() {

		if ( $this->is_on_sale() && $this->get_regular_price() ) {

			$price .= $this->get_price_variations_html( $display_base_price, __( 'Free!', 'lifterlms' ) );

			$price .= apply_filters( 'lifterlms_free_sale_price_html', $price, $this );

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

			$start = $this->get_sale_start_date();
			$end = $this->get_sale_end_date();

			// no dates, the product is indefinitely on sale
			if ( ! $start && ! $end ) {
				return true;
			}

			$start = ( $start ) ? strtotime( $start . ' 00:00:00' ) : $start;
			$end = ( $end ) ? strtotime( $end . ' 23:23:59' ) : $end;

			// start and end
			if ( $start && $end ) {

				return ( $now < $end && $now > $start );

			} // only start
			elseif ( $start && ! $end ) {

				return ( $now > $start );

			} // only end
			elseif ( ! $start && $end ) {

				return ( $now < $end );

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
	 * Adjust price using coupon
	 *
	 * @param  string $price [product price]
	 * @return string       [adjusted product price]
	 */
	public function adjusted_price( $price = '' ) {
		$adjustment = llms_get_coupon();
		$total = $price;

		if ( ! empty( $adjustment ) && $adjustment->amount > 0 ) {
			if ($this->id == $adjustment->product_id) {

				if ( ( $adjustment->limit >= 0 ) || ( $adjustment->limit === 'unlimited' ) ) {
					if ($adjustment->type == 'percent') {

						$amount = (1 - ($adjustment->amount / 100));

						$total = ($price * $amount);
						$total = sprintf( '%0.2f', $total );
					} elseif ($adjustment->type == 'dollar') {
						$amount = round( $adjustment->amount, 2 );
						$total = ($price - $amount);
						$total = sprintf( '%0.2f', $total );
					}
				}
			}
		}
		return $total;
	}

	/**
	 * Get function for price value.
	 *
	 *DEPRECIATED
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

		return apply_filters( 'lifterlms_get_recurring_first_price', $this->adjusted_price( $this->llms_subscription_first_payment ), $this );
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
	 * creates the price suffix html
	 *
	 * @return void
	 */
	public function get_price_suffix_html() {

		$price_display_suffix  = get_option( 'lifterlms_price_display_suffix' );

		if ( $price_display_suffix ) {

			$price_display_suffix = ' <small class="lifterlms-price-suffix">' . $price_display_suffix . '</small>';

			$price_display_suffix = str_replace( $find, $replace, $price_display_suffix );

		}

		return apply_filters( 'lifterlms_get_price_suffix_html', $price_display_suffix, $this );
	}

	/**
	 * Returns base price and sale price in html format.
	 *
	 * @return string
	 */
	public function get_price_variations_html( $base, $sale ) {

		return '<del>' . ( ( is_numeric( $base ) ) ? llms_price( $base ) : $base ) . '</del> <ins>' . ( ( is_numeric( $sale ) ) ? llms_price( $sale ) : $sale ) . '</ins>';

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
