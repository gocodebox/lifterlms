<?php
/**
 * Order Details Metabox
 *
 * @since 3.0.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Meta_Box_Order_Details
 *
 * @since 3.0.0
 * @since 3.35.0 Verify nonces and sanitize `$_POST` data.
 */
class LLMS_Meta_Box_Order_Details extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 *
	 * @return void
	 * @since  3.0.0
	 * @version  3.0.0
	 */
	public function configure() {

		$this->id       = 'lifterlms-order-details';
		$this->title    = __( 'Order Details', 'lifterlms' );
		$this->screens  = array(
			'llms_order',
		);
		$this->context  = 'normal';
		$this->priority = 'high';

	}

	/**
	 * Not used because our metabox doesn't use the standard fields api
	 *
	 * @since  3.0.0
	 *
	 * @return array
	 */
	public function get_fields() {
		return array();
	}

	/**
	 * Function to field WP::output() method call
	 * Passes output instruction to parent
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Unknown.
	 *
	 * @return void
	 */
	public function output() {

		$order = llms_get_post( $this->post );
		if ( ! $order || ! is_a( $order, 'LLMS_Order' ) ) {
			return;
		}
		$gateway = $order->get_gateway();

		llms_get_template(
			'admin/post-types/order-details.php',
			array(
				'gateway' => $gateway,
				'order'   => $order,
			)
		);

	}

	/**
	 * Save method
	 *
	 * @since 3.0.0
	 * @since 3.10.0 Unknown.
	 * @since 3.35.0 Verify nonces and sanitize `$_POST` data.
	 *
	 * @param    int $post_id  Post ID of the Order
	 * @return   void
	 */
	public function save( $post_id ) {

		if ( ! llms_verify_nonce( 'lifterlms_meta_nonce', 'lifterlms_save_data' ) ) {
			return;
		}

		$order = llms_get_post( $this->post );
		if ( ! $order || ! is_a( $order, 'LLMS_Order' ) ) {
			return;
		}

		$fields = array(
			'payment_gateway',
			'gateway_customer_id',
			'gateway_subscription_id',
			'gateway_source_id',
		);

		foreach ( $fields as $key ) {

			if ( isset( $_POST[ $key ] ) ) {
				$order->set( $key, llms_filter_input( INPUT_POST, $key, FILTER_SANITIZE_STRING ) );
			}
		}

	}

}
