<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Metaboxes for Orders
 *
 * @version  3.0.0
 */
class LLMS_Meta_Box_Order_Details extends LLMS_Admin_Metabox {

	/**
	 * Function to field WP::output() method call
	 * Passes output instruction to parent
	 *
	 * @param object $post WP global post object
	 * @return void
	 *
	 * @version  3.0.0
	 */
	public static function output( $post ) {

		$order = new LLMS_Order ( $post );
		llms_get_template( 'admin/post-types/order.php', array( 'order' => $order ) );

	}

}
