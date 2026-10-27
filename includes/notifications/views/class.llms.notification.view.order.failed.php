<?php
/**
 * Notification View: Order Failed.
 *
 * @package LifterLMS/Notifications/Views/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification View: Order Failed.
 *
 * @since [version]
 */
class LLMS_Notification_View_Order_Failed extends LLMS_Abstract_Notification_View {

	/**
	 * Notification Trigger ID.
	 *
	 * @var string
	 */
	public $trigger_id = 'order_failed';

	/**
	 * Setup body content for output.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function set_body() {
		return $this->set_body_email();
	}

	/**
	 * Setup default notification body for email notifications.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	private function set_body_email() {
		$mailer = llms()->mailer();

		$rows = array(
			'PRODUCT_TITLE_LINK' => '{{PRODUCT_TYPE}}',
			'PLAN_TITLE'         => __( 'Plan', 'lifterlms' ),
			'PAYMENT_AMOUNT'     => __( 'Amount', 'lifterlms' ),
		);

		ob_start();
		?><p><?php printf( esc_html__( 'Hello %s,', 'lifterlms' ), '{{CUSTOMER_NAME}}' ); ?></p>
		<p><?php printf( esc_html__( 'We were unable to process the payment for your %1$s on order #%2$s.', 'lifterlms' ), '{{PRODUCT_TITLE}}', '{{ORDER_ID}}' ); ?></p>
		<p><?php printf( esc_html__( 'To resolve this you can login to your account and %1$spay now%2$s or update your payment method.', 'lifterlms' ), '<a href="{{ORDER_URL}}">', '</a>' ); ?></p>
		<h4><?php printf( esc_html__( 'Order #%s', 'lifterlms' ), '{{ORDER_ID}}' ); ?></h4>
		<?php $mailer->output_table_html( $rows ); ?>
		<p><a href="{{ORDER_URL}}"><?php esc_html_e( 'Update Payment Method', 'lifterlms' ); ?></a></p>
		<?php
		return ob_get_clean();
	}

	/**
	 * Setup footer content for output.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function set_footer() {
		$url = $this->set_merge_data( '{{ORDER_URL}}' );
		return '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Update Payment Method', 'lifterlms' ) . '</a>';
	}

	/**
	 * Setup notification icon for output.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function set_icon() {
		return $this->get_icon_default( 'warning' );
	}

	/**
	 * Setup merge codes that can be used with the notification.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function set_merge_codes() {
		return array(
			'{{CUSTOMER_EMAIL}}'      => __( 'Customer Email', 'lifterlms' ),
			'{{CUSTOMER_NAME}}'       => __( 'Customer Name', 'lifterlms' ),
			'{{CUSTOMER_PHONE}}'      => __( 'Customer Phone', 'lifterlms' ),
			'{{ORDER_ID}}'            => __( 'Order ID', 'lifterlms' ),
			'{{ORDER_URL}}'           => __( 'Order URL', 'lifterlms' ),
			'{{PAYMENT_AMOUNT}}'      => __( 'Payment Amount', 'lifterlms' ),
			'{{PLAN_TITLE}}'          => __( 'Plan Title', 'lifterlms' ),
			'{{PRODUCT_TITLE}}'       => __( 'Product Title', 'lifterlms' ),
			'{{PRODUCT_TITLE_LINK}}'  => __( 'Product Title (Link)', 'lifterlms' ),
			'{{PRODUCT_TYPE}}'        => __( 'Product Type', 'lifterlms' ),
			'{{RETRY_NOTICE}}'        => __( 'Retry Notice', 'lifterlms' ),
		);
	}

	/**
	 * Replace merge codes with actual values.
	 *
	 * @since [version]
	 *
	 * @param string $code The merge code to get merged data for.
	 * @return string
	 */
	protected function set_merge_data( $code ) {

		$order = $this->post;
		if ( ! is_a( $order, 'LLMS_Order' ) ) {
			return $code;
		}

		switch ( $code ) {

			case '{{CUSTOMER_EMAIL}}':
				$code = $order->get( 'billing_email' );
				break;

			case '{{CUSTOMER_NAME}}':
				$code = $order->get_customer_name();
				break;

			case '{{CUSTOMER_PHONE}}':
				$code = $order->get( 'billing_phone' );
				break;

			case '{{ORDER_ID}}':
				$code = $order->get( 'id' );
				break;

			case '{{ORDER_URL}}':
				$code = esc_url( $order->get_view_link() );
				break;

			case '{{PAYMENT_AMOUNT}}':
				$code = $order->get_price( 'total' );
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
					$code  = '<a href="' . $permalink . '">' . $title . '</a>';
				} else {
					$code = $this->set_merge_data( '{{PRODUCT_TITLE}}' );
				}
				break;

			case '{{PRODUCT_TYPE}}':
				$obj = $order->get_product();
				if ( empty( $obj ) ) {
					$code = __( '[DELETED ITEM]', 'lifterlms' );
				} elseif ( is_a( $obj, 'WP_Post' ) ) {
					$code = _x( 'Item', 'generic product type description', 'lifterlms' );
				} else {
					$code = $obj->get_post_type_label( 'singular_name' );
				}
				break;

			case '{{RETRY_NOTICE}}':
				if ( $order->is_recurring() ) {
					$code = esc_html__( 'We have exhausted all automatic retry attempts for this recurring payment.', 'lifterlms' );
				} else {
					$code = esc_html__( 'No further payments will be attempted for this order.', 'lifterlms' );
				}
				break;

		}

		return $code;
	}

	/**
	 * Setup notification subject for output.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function set_subject() {
		// Translators: %s = The product title.
		return sprintf( __( 'Payment for %s failed', 'lifterlms' ), '{{PRODUCT_TITLE}}' );
	}

	/**
	 * Setup notification title for output.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function set_title() {
		// Translators: %s = The order ID.
		return sprintf( __( 'Order #%s payment failed', 'lifterlms' ), '{{ORDER_ID}}' );
	}
}
