<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Meta Box Order
*
* Main metabox for order post.
* Displays details of order
*/
class LLMS_Meta_Box_Order {

	/**
	 * Static output class.
	 *
	 * Displays MetaBox
	 *
	 * @param  object $post [WP post object]
	 * @return void
	 */
	public static function output( $post ) {

		$order = new LLMS_Order( $post );

		llms_get_template( 'admin/post-types/order.php', array( 'order' => $order ) );

	}
}
