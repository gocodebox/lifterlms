<?php
/**
 * Notification View: Upcoming Payment Reminder.
 *
 * @package LifterLMS/Notifications/Views/Classes
 *
 * @since 5.2.0
 * @version 5.4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification View: Payment Retry.
 *
 * @since 5.2.0
 */
class LLMS_Notification_View_Upcoming_Payment_Reminder extends LLMS_Abstract_Notification_View {

	/**
	 * Settings for basic notifications.
	 *
	 * @var array
	 */
	protected $basic_options = array(
		/**
		 * Time in milliseconds to show a notification
		 * before automatically dismissing it.
		 */
		'auto_dismiss' => 10000,
		/**
		 * Enables manual dismissal of notifications.
		 */
		'dismissible'  => true,
	);


	/**
	 * Notification Trigger ID.
	 *
	 * @var string
	 */
	public $trigger_id = 'upcoming_payment_reminder';

	/**
	 * Setup body content for output.
	 *
	 * @since 5.2.0
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
	 * @since 5.2.0
	 *
	 * @return string
	 */
	private function set_body_basic() {
		return sprintf( esc_html__( 'You will be charged for your subscription to %1$s tomorrow.', 'lifterlms' ), '{{PRODUCT_TITLE}}' );
	}

	/**
	 * Setup default notification body for email notifications.
	 *
	 * @since 5.2.0
	 *
	 * @return string
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
		?>
		<p>
		<?php
			// Translators: %s= The customer name.
			printf( esc_html__( 'Hello %s,', 'lifterlms' ), '{{CUSTOMER_NAME}}' );
		?>
		</p>
		<p>
		<?php
			// Translators: %1$s = The product title, %2$s The upcoming payment due date.
			printf( esc_html__( 'You will be charged for your subscription to %1$s on %2$s.', 'lifterlms' ), '{{PRODUCT_TITLE}}', '{{NEXT_PAYMENT_DATE}}' );
		?>
		</p>
		<h4>
		<?php
			// Translators: %s= The order ID.
			printf( esc_html__( 'Order #%s', 'lifterlms' ), '{{ORDER_ID}}' );
		?>
		</h4>
		<?php
		$mailer->output_table_html( $rows );
		?>
		<p><a href="{{ORDER_URL}}"><?php esc_html_e( 'Update Payment Method', 'lifterlms' ); ?></a></p>
		<?php
		return ob_get_clean();
	}

	/**
	 * Setup footer content for output.
	 *
	 * @since 5.2.0
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
	 * @since 5.2.0
	 *
	 * @return string
	 */
	protected function set_icon() {
		return $this->get_icon_default( 'warning' );
	}

	/**
	 * Setup merge codes that can be used with the notification.
	 *
	 * @since 5.2.0
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
	 * @since 5.2.0
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
	 * @since 5.2.0
	 *
	 * @return string
	 */
	protected function set_subject() {
		// Translators: %s = The product title.
		return sprintf( __( 'Upcoming payment reminder for your subscription to %1$s', 'lifterlms' ), '{{PRODUCT_TITLE}}' );
	}

	/**
	 * Setup notification title for output
	 *
	 * @since 5.2.0
	 *
	 * @return string
	 */
	protected function set_title() {
		return __( 'Upcoming Subscription Payment', 'lifterlms' );
	}
}
