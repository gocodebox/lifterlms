<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Order Details Metabox
 *
 * @since    3.0.0
 * @version  3.0.0
 */
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

		$order = new LLMS_Order( $this->post );
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
	 * @version  3.0.0
	 */
	public function save( $post_id ) {
		return;
	}

}
