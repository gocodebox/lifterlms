<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order class
 *
 * @since  2.7.0
 */
class LLMS_Order {

	private $id;
	public $post;

	public function __construct( $order_id ) {

		if ( is_numeric( $order_id ) ) {

			$this->id   = absint( $order );
			$this->post = $this->get_post( $this->id );

		}

	}

	public function get_id() {
		return $this->id;
	}

	public function get_post() {
		return get_post( $this->order_id );
	}

	public function get_coupon_id() {
		return $this->get( 'coupon_id' );
	}



	private function get( $key, $single = true, $with_order = true ) {

		$order = ( $with_order ) ? 'order_' : '';
		return get_post_meta( $this->get_id(), '_llms_' . $order . $key, $single );

	}

}

'_llms_user_id', $order->user_id );
'_llms_payment_method', $order->payment_method );
'_llms_product_title', $order->product_title );
'_llms_payment_type', $order->payment_type );
'_llms_product_sku', $order->product_sku );
'_llms_product_type', $order->product_type );

'_llms_order_coupon_id', $coupon->id );
'_llms_order_coupon_type', $coupon->type );
'_llms_order_coupon_amount', $coupon->amount );
'_llms_order_coupon_limit', $coupon->limit );
'_llms_order_coupon_code', $coupon->coupon_code );
'_llms_order_total', $product->adjusted_price( $order->total ) );
'_llms_order_coupon_value', $product->get_coupon_discount_total( $order->total ) );
'_llms_order_total', $order->total );
'_llms_order_product_price', $order->product_price );
'_llms_order_original_total', $order->total );
'_llms_order_currency', $order->currency );
'_llms_order_product_id', $order->product_id );
'_llms_order_date', current_time( 'mysql' ) );
'_llms_order_type', $order->payment_option );
'_llms_order_recurring_price', $order->product_price );
'_llms_order_first_payment', $order->first_payment );
'_llms_order_billing_period', $order->billing_period );
'_llms_order_billing_cycle', $order->billing_cycle );
'_llms_order_billing_freq', $order->billing_freq );
'_llms_order_billing_start_date', $order->billing_start_date );
