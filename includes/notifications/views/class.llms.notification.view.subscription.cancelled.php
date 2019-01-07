<?php
defined( 'ABSPATH' ) || exit;

/**
 * Notification View: Purchase Receipt
 * @since    3.17.8
 * @version  3.18.2
 */
class LLMS_Notification_View_Subscription_Cancelled extends LLMS_Abstract_Notification_View {

	/**
	 * Notification Trigger ID
	 * @var  [type]
	 */
	public $trigger_id = 'subscription_cancelled';

	/**
	 * Setup body content for output
	 * @return   string
	 * @since    3.17.8
	 * @version  3.17.8
	 */
	protected function set_body() {

		return sprintf(
			__( '%1$s has cancelled their subscription (#%2$s) to the %3$s %4$s', 'lifterlms' ),
			'{{CUSTOMER_NAME}}',
			'{{ORDER_ID}}',
			'{{PRODUCT_TYPE}}',
			'{{PRODUCT_TITLE_LINK}}'
		);

	}

	/**
	 * Setup footer content for output
	 * @return   string
	 * @since    3.17.8
	 * @version  3.17.8
	 */
	protected function set_footer() {
		return '';
	}

	/**
	 * Setup notification icon for output
	 * @return   string
	 * @since    3.17.8
	 * @version  3.17.8
	 */
	protected function set_icon() {
		return '';
	}

	/**
	 * Setup merge codes that can be used with the notification
	 * @return   array
	 * @since    3.17.8
	 * @version  3.17.8
	 */
	protected function set_merge_codes() {
		return array(
			'{{CUSTOMER_NAME}}' => __( 'Customer Name', 'lifterlms' ),
			'{{ORDER_ID}}' => __( 'Order ID', 'lifterlms' ),
			'{{PLAN_TITLE}}' => __( 'Plan Title', 'lifterlms' ),
			'{{PRODUCT_TITLE}}' => __( 'Product Title', 'lifterlms' ),
			'{{PRODUCT_TYPE}}' => __( 'Product Type', 'lifterlms' ),
			'{{PRODUCT_TITLE_LINK}}' => __( 'Product Title (Link)', 'lifterlms' ),
		);
	}

	/**
	 * Replace merge codes with actual values
	 * @param    string   $code  the merge code to ge merged data for
	 * @return   string
	 * @since    3.17.8
	 * @version  3.17.8
	 */
	protected function set_merge_data( $code ) {

		$order = $this->post;

		switch ( $code ) {

			case '{{CUSTOMER_NAME}}':
				$code = $order->get_customer_name();
			break;

			case '{{ORDER_ID}}':
				$code = $order->get( 'id' );
			break;

			case '{{PLAN_TITLE}}':
				$code = $order->get( 'plan_title' );
			break;

			case '{{PRODUCT_TITLE}}':
				$code = $order->get( 'product_title' );
			break;

			case '{{PRODUCT_TITLE_LINK}}':
				$permalink = esc_url( get_permalink( $order->get( 'product_id' ) ) );
				if ( $permalink ) {
					$title = $this->set_merge_data( '{{PRODUCT_TITLE}}' );
					$code = '<a href="' . $permalink . '">' . $title . '</a>';
				}
			break;

			case '{{PRODUCT_TYPE}}':
				$obj = $order->get_product();
				if ( $obj ) {
					$code = $obj->get_post_type_label( 'singular_name' );
				} else {
					$code = _x( 'Item', 'generic product type description', 'lifterlms' );
				}
			break;

		}// End switch().

		return $code;

	}

	/**
	 * Setup notification subject for output
	 * @return   string
	 * @since    3.17.8
	 * @version  3.17.8
	 */
	protected function set_subject() {
		return esc_html__( 'Subscription Cancellation Notice', 'lifterlms' );
	}

	/**
	 * Setup notification title for output
	 * @return   string
	 * @since    3.17.8
	 * @version  3.17.8
	 */
	protected function set_title() {
		return sprintf( esc_html__( '%1$s subscription cancellation', 'lifterlms' ), '{{PRODUCT_TYPE}}' );
	}

}
