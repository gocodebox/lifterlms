<?php
/**
 * Order Details Metabox
 *
 * @since    3.0.0
 * @version  3.10.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Meta_Box_Order_Details extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 * @return void
	 * @since  3.0.0
	 * @version  3.0.0
	 */
	public function configure() {

		$this->id = 'lifterlms-order-details';
		$this->title = __( 'Order Details', 'lifterlms' );
		$this->screens = array(
			'llms_order',
		);
		$this->context = 'normal';
		$this->priority = 'high';

	}

	/**
	 * Not used because our metabox doesn't use the standard fields api
	 * @return array
	 *
	 * @since  3.0.0
	 * @version  3.0.0
	 */
	public function get_fields() {}

	/**
	 * Function to field WP::output() method call
	 * Passes output instruction to parent
	 *
	 * @param object $post WP global post object
	 * @return void
	 *
	 * @since    1.0.0
	 * @version  3.0.0
	 */
	public function output() {

		$order = llms_get_post( $this->post );
		if ( ! $order || ! is_a( $order, 'LLMS_Order' ) ) {
			return;
		}
		$gateway = $order->get_gateway();

		llms_get_template( 'admin/post-types/order-details.php', array(
			'gateway' => $gateway,
			'order' => $order,
		) );

	}

	/**
	 * Save method
	 * Does nothing because there's no editable data in this metabox
	 * @param    int     $post_id  Post ID of the Order
	 * @return   void
	 * @since    3.0.0
	 * @version  3.10.0
	 */
	public function save( $post_id ) {

		$order = llms_get_post( $this->post );
		if ( ! $order || ! is_a( $order, 'LLMS_Order' ) ) {
			return;
		}
		// $gateway = $order->get_gateway();

		$fields = array(
			'payment_gateway',
			'gateway_customer_id',
			'gateway_subscription_id',
			'gateway_source_id',
		);

		foreach ( $fields as $key ) {

			if ( isset( $_POST[ $key ] ) ) {
				$order->set( $key, sanitize_text_field( $_POST[ $key ] ) );
			}
		}

	}

}
