<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Base Product Class
*
* Class used for instantiating Product object
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
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

		}

		elseif ( $product instanceof LLMS_Product ) {

			$this->id   = absint( $product->id );
			$this->post = $product;

		}

		elseif ( isset( $product->ID ) ) {

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

	public function get_payment_options() {

		$single = $this->get_price();
		$recurring = $this->is_recurring();

		$options = array();

		if ($this->get_price()) {
			array_push($options, 'single');
		}
		if ($this->is_recurring()) {
			array_push($options, 'recurring');
		}	
		return apply_filters( 'lifterlms_product_get_payment_options', $options);
	}

	public function get_subscriptions() {
		return $this->llms_subscriptions;
	}

	public function get_billing_period($sub) {
		return $sub['billing_period'];
	}

	public function get_billing_freq($sub) {
		return $sub['billing_freq'];
	}

	public function get_billing_cycle($sub) {
		return $sub['billing_cycle'];
	}

	public function get_subscription_total_price($sub) {
		return $sub['total_price'];
	}

	public function get_subscription_first_payment($sub) {
		return $sub['first_payment'];
	}

	public function get_subscription_payment_price($sub) {
		return $sub['sub_price'];
	}

	public function get_subscription_price_html($sub) {

		$price = '';
		$currency_symbol = get_lifterlms_currency_symbol();
		$sub_price = $this->adjusted_price( $this->get_subscription_payment_price($sub) );
		$sub_first_payment = $this->adjusted_price( $this->get_subscription_first_payment($sub) );


		$suffix = $this->get_price_suffix_html();
		$display_price = ($currency_symbol . $sub_price);
		$billing_period = $this->get_billing_period($sub);
		$billing_freq = $this->get_billing_freq($sub);
		$billing_cycle = $this->get_billing_cycle($sub);


		// display billing period based on frequency
		if ($billing_freq > 1) {
			$billing_period_html = 'every ' . $billing_freq . ' ' . $billing_period . 's';
		}
		else {
			$billing_period_html = 'per ' . $billing_period;
		}

		// / month = $1 


		// if first payment is different from recurring payment display first payment. 
		if ($sub_first_payment != $sub_price) {
			$price = $currency_symbol . $sub_first_payment . ' then ';
		}

		if ( $billing_cycle == 0 ) {
			$price .= ($display_price . ' ' . $billing_period_html);
		}

		elseif ( $billing_cycle > 1 ) {
			$price .= ($display_price . ' ' . $billing_period_html . ' for ' . $billing_cycle . ' ' . $billing_period . 's');
		}
		else {
			$price .= ($display_price . ' ' . $billing_period_html . ' for ' . $billing_cycle . ' ' . $billing_period);
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
		$checkout_url =  apply_filters( 'lifterlms_get_checkout_url', $checkout_page_id ? get_permalink( $checkout_page_id ) : '' );
		
		return add_query_arg( 'product-id', $this->id, $checkout_url );

	}

	/**
	 * Get price in html format
	 *
	 * @return string
	 */
	public function get_price_html( $price = '' ) {

		$suffix 				= $this->get_price_suffix_html();
		$currency_symbol 		= get_lifterlms_currency_symbol() != '' ? get_lifterlms_currency_symbol() : '';
		$display_price 			= $this->adjusted_price($this->get_price());
		$display_base_price 	= $this->get_base_price();
		$display_sale_price    	= $this->get_sale_price();

		if ( $this->get_price() > 0 ) {
			$price = $this->set_price_html_as_value($suffix, $currency_symbol, $display_price, $display_base_price, $display_sale_price);

		}

		elseif ( $this->get_price() === '' ) {

			$price = apply_filters( 'lifterlms_empty_price_html', '', $this );

		}

		elseif ( $this->get_price() == 0 ) {

			$price = $this->set_price_html_as_free();

		}

		return apply_filters( 'lifterlms_get_price_html', $price, $this );
	}

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
			$billing_period_html = 'every ' . $billing_freq . ' ' . $billing_period . 's';
		}
		else {
			$billing_period_html = 'per ' . $billing_period;
		}

		// / month = $1 


		// if first payment is different from recurring payment display first payment. 
		if ($recurring_first_payment != $recurring_price) {
			$price = $currency_symbol . $recurring_first_payment . ' then ';
		}

		if ( $billing_cycle == 0 ) {
			$price .= ($display_price . ' ' . $billing_period_html);
		}

		elseif ( $billing_cycle > 1 ) {
			$price .= ($display_price . ' ' . $billing_period_html . ' for ' . $billing_cycle . ' ' . $billing_period . 's');
		}
		else {
		$price .= ($display_price . ' ' . $billing_period_html . ' for ' . $billing_cycle . ' ' . $billing_period);
		}

		return apply_filters( 'lifterlms_recurring_price_html', $price, $this );;
	}


	/**
	 * Set price html to a decimal value with currency and suffix.
	 *
	 * @return string
	 */
	public function set_price_html_as_value($suffix, $currency_symbol, $display_price, $display_base_price, $display_sale_price) {


		// Check if price is on sale and base price exists
		if ( $this->is_on_sale() && $this->get_base_price() ) {

			//generate price with formatting and suffix
			$price = $currency_symbol;

			$price .= $this->get_price_variations_html( $display_base_price, $display_price ) . $suffix;

			$price = apply_filters( 'lifterlms_sale_price_html', $price, $this );

		}

		else {

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

		if ( $this->is_on_sale() && $this->get_base_price() ) {

			$price .= $this->get_price_variations_html( $display_base_price, __( 'Free!', 'lifterlms' ) );

			$price .= apply_filters( 'lifterlms_free_sale_price_html', $price, $this );

		}

		else {

			$price = __( 'Free!', 'lifterlms' );

			$price = apply_filters( 'lifterlms_free_price_html', $price, $this );

		}

		return $price;

	}

	/**
	 * Check: Is the sale price different than the base price and is the sale price equal to the price returned from get_price().
	 *
	 * @return bool
	 */
	public function is_on_sale() {

		return ( $this->get_sale_price() != $this->get_base_price() && $this->get_sale_price() == $this->get_price() );

	}

	public function get_coupon_discount_total($price = '') {
		$adjustment = llms_get_coupon();
	    $total = $price;

		if ( !empty( $adjustment ) && $adjustment->amount > 0 ) {
			if ($this->id == $adjustment->product_id) {
				if ($adjustment->limit >= 0) {
				    if ($adjustment->type == 'percent') {

						$amount =  ($adjustment->amount / 100);

						$total = ($price * $amount);
						$total = sprintf('%0.2f', $total);
				        
				    }
				    elseif ($adjustment->type == 'dollar') {
						$amount = round( $adjustment->amount, 2 );
						$total = ($amount);
						$total = sprintf('%0.2f', $total);
				    }
				}
			}
		}
		return $total;
	}


	public function adjusted_price($price = '') {
		$adjustment = llms_get_coupon();
		$total = $price;

		if ( !empty( $adjustment ) && $adjustment->amount > 0 ) {
			if ($this->id == $adjustment->product_id) {
				if ($adjustment->limit >= 0) {
					if ($adjustment->type == 'percent') {

						$amount =  (1 - ($adjustment->amount / 100));

						$total = ($price * $amount);
						$total = sprintf('%0.2f', $total);
					}
					elseif ($adjustment->type == 'dollar') {
						$amount = round( $adjustment->amount, 2 );
						$total = ($price - $amount);
						$total = sprintf('%0.2f', $total);
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
		return apply_filters( 'lifterlms_get_price', $this->price, $this );

	}

	public function get_single_price() {
		return apply_filters( 'lifterlms_get_single_price', $this->price, $this );
	}

	public function get_recurring_price() {

		//return apply_filters( 'lifterlms_get_recurring_price', $this->adjusted_price($this->llms_subscription_price), $this );
	}

	public function get_recurring_first_payment() {

		return apply_filters( 'lifterlms_get_recurring_first_price', $this->adjusted_price($this->llms_subscription_first_payment), $this );
	}



	public function get_recurring_next_payment_date($sub) {

		$billing_period = $this->get_billing_period($sub);

		$billing_freq = $this->get_billing_freq($sub);
		$billing_freq = $billing_freq > 0 ? $billing_freq : 1;

		switch($billing_period) {
			case 'day':
				$next_payment_date = date('Y-m-d', strtotime(' +' . $billing_freq . ' day'));
				break;
			case 'week':
				$next_payment_date = date('Y-m-d', strtotime(' +' . $billing_freq . ' week'));
				break;
			case 'month':
				$next_payment_date = date('Y-m-d', strtotime(' +' . $billing_freq . ' month'));
				break;
			case 'year':
				$next_payment_date = date('Y-m-d', strtotime(' +' . $billing_freq . ' year'));
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
	public function get_base_price( $price = '' ) {

		$price = $price;

	}

	/**
	 * get the base price value.
	 *
	 * @return void
	 */
	public function get_sale_price( $price = '' ) {

		$price = $price;

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

}