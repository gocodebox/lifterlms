<?php
/**
 * Notification View: Payment Retry.
 *
 * @package LifterLMS/Notifications/Views/Classes
 *
 * @since 3.10.0
 * @version 5.4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification View: Payment Retry.
 *
 * @since 3.10.0
 */
class LLMS_Notification_View_Payment_Retry extends LLMS_Abstract_Notification_View {

	/**
	 * Settings for basic notifications.
	 *
	 * @var array
	 */
	protected $basic_options = array(
		/**
		 * Time in milliseconds to show a notification before automatically dismissing it.
		 */
		'auto_dismiss' => 10000,
		/**
		 * Enables manual dismissal of notifications
		 */
		'dismissible'  => true,
	);


	/**
	 * Notification Trigger ID.
	 *
	 * @var string
	 */
	public $trigger_id = 'payment_retry';

	/**
	 * Setup body content for output.
	 *
	 * @since 3.10.0
	 *
	 * @return string
	 */
	protected function set_body() {

		if ( 'email' === $this->notification->get( 'type' ) ) {
			return $this->set_body_email();
		}
		return $this->set_body_basic();
	}

	/**
	 * Setup default notification body for basic notifications.
	 *
	 * @since 3.10.0
	 *
	 * @return string
	 */
	private function set_body_basic() {
		return esc_html__( 'Head over to the order to see what went wrong and update your payment method to reactivate your subscription.', 'lifterlms' );
	}

	/**
	 * Setup default notification body for email notifications.
	 *
	 * @since 3.10.0
	 * @since 5.2.0 Build the table with mailer helper.
	 *
	 * @return void
	 */
	private function set_body_email() {
		$mailer = llms()->mailer();

		$rows = array(
			'NEXT_PAYMENT_DATE'  => __( 'Payment Due Date', 'lifterlms' ),
			'PRODUCT_TITLE_LINK' => '{{PRODUCT_TYPE}}',
			'PLAN_TITLE'         => __( 'Plan', 'lifterlms' ),
			'PAYMENT_AMOUNT'     => __( 'Amount', 'lifterlms' ),
		);

		ob_start();
		?><p><?php printf( esc_html__( 'Hello %s,', 'lifterlms' ), '{{CUSTOMER_NAME}}' ); ?></p>
		<p><?php printf( esc_html__( 'The automatic payment for your subscription to %1$s has failed. We\'ll automatically retry this charge on %2$s.', 'lifterlms' ), '{{PRODUCT_TITLE}}', '{{NEXT_PAYMENT_DATE}}' ); ?></p>
		<p><?php printf( esc_html__( 'To reactivate your subscription you can login to your account and %1$spay now%2$s.', 'lifterlms' ), '<a href="{{ORDER_URL}}">', '</a>' ); ?></p>
		<h4><?php printf( esc_html__( 'Order #%s', 'lifterlms' ), '{{ORDER_ID}}' ); ?></h4>
		<?php $mailer->output_table_html( $rows ); ?>
		<p><a href="{{ORDER_URL}}"><?php esc_html_e( 'Update Payment Method', 'lifterlms' ); ?></a></p>
		<?php
		return ob_get_clean();
	}

	/**
	 * Setup footer content for output.
	 *
	 * @since 3.10.0
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
	 * @since 3.10.0
	 *
	 * @return string
	 */
	protected function set_icon() {
		return $this->get_icon_default( 'warning' );
	}

	/**
	 * Setup merge codes that can be used with the notification.
	 *
	 * @since 3.10.0
	 *
	 * @return array
	 */
	protected function set_merge_codes() {
		return array(
			'{{CUSTOMER_ADDRESS}}'   => __( 'Customer Address', 'lifterlms' ),
			'{{CUSTOMER_NAME}}'      => __( 'Customer Name', 'lifterlms' ),
			'{{CUSTOMER_PHONE}}'     => __( 'Customer Phone', 'lifterlms' ),
			'{{NEXT_PAYMENT_DATE}}'  => __( 'Next Payment Date', 'lifterlms' ),
			'{{ORDER_ID}}'           => __( 'Order ID', 'lifterlms' ),
			'{{ORDER_URL}}'          => __( 'Order URL', 'lifterlms' ),
			'{{PAYMENT_AMOUNT}}'     => __( 'Payment Amount', 'lifterlms' ),
			'{{PLAN_TITLE}}'         => __( 'Plan Title', 'lifterlms' ),
			'{{PRODUCT_TITLE}}'      => __( 'Product Title', 'lifterlms' ),
			'{{PRODUCT_TYPE}}'       => __( 'Product Type', 'lifterlms' ),
			'{{PRODUCT_TITLE_LINK}}' => __( 'Product Title (Link)', 'lifterlms' ),
		);
	}

	/**
	 * Replace merge codes with actual values.
	 *
	 * @since 3.10.0
	 * @since 5.2.0 Retrieve the customer's full address using the proper order's method.
	 * @since 5.4.0 Account for deleted products.
	 *
	 * @param string $code The merge code to get merged data for.
	 * @return string
	 */
	protected function set_merge_data( $code ) {

		$order = $this->post;

		switch ( $code ) {

			case '{{CUSTOMER_ADDRESS}}':
				$code = $order->get_customer_full_address();
				break;

			case '{{CUSTOMER_NAME}}':
				$code = $order->get_customer_name();
				break;

			case '{{CUSTOMER_PHONE}}':
				$code = $order->get( 'billing_phone' );
				break;

			case '{{NEXT_PAYMENT_DATE}}':
				$code = $order->get_date( 'date_next_payment', get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
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

		}

		return $code;
	}

	/**
	 * Setup notification subject for output.
	 *
	 * @since 3.10.0
	 *
	 * @return string
	 */
	protected function set_subject() {
		return sprintf( __( 'Automatic payment for %1$s failed, retry scheduled for %2$s', 'lifterlms' ), '{{PRODUCT_TITLE}}', '{{NEXT_PAYMENT_DATE}}' );
	}

	/**
	 * Setup notification title for output.
	 *
	 * @since 3.10.0
	 *
	 * @return string
	 */
	protected function set_title() {
		if ( 'email' === $this->notification->get( 'type' ) ) {
			// Translators: %s = The order ID.
			return sprintf( __( 'Automatic payment failed for order #%s', 'lifterlms' ), '{{ORDER_ID}}' );
		}
		// Translators: %s = The product title.
		return sprintf( __( 'An automatic payment failed for your subscription to %s', 'lifterlms' ), '{{PRODUCT_TITLE}}' );
	}
}
