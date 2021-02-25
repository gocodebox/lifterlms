<?php
/**
 * Notification View: Upcoming Payment Reminder
 *
 * @package LifterLMS/Notifications/Views/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification View: Payment Retry
 *
 * @since [version]
 */
class LLMS_Notification_View_Upcoming_Payment_Reminder extends LLMS_Abstract_Notification_View {

	/**
	 * Settings for basic notifications
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
	 * Notification Trigger ID
	 *
	 * @var string
	 */
	public $trigger_id = 'upcoming_payment_reminder';

	/**
	 * Setup body content for output
	 *
	 * @since [version]
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
	 * Setup default notification body for basic notifications
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	private function set_body_basic() {
		return esc_html__( 'You will be charged for your subscription tomorrow.', 'lifterlms' );
	}

	/**
	 * Setup default notification body for email notifications
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	private function set_body_email() {

		$mailer = LLMS()->mailer();

		$rows = array(
			'NEXT_PAYMENT_DATE'  => __( 'Payment Due Date', 'lifterlms' ),
			'PRODUCT_TITLE_LINK' => '{{PRODUCT_TYPE}}',
			'PLAN_TITLE'         => __( 'Plan', 'lifterlms' ),
			'PAYMENT_AMOUNT'     => __( 'Amount', 'lifterlms' ),
		);

		ob_start();
		?><p><?php printf( __( 'Hello %s,', 'lifterlms' ), '{{CUSTOMER_NAME}}' ); ?></p>
		<p><?php printf( __( 'You will be charged for your subscription to %1$s tomorrow on %2$s.', 'lifterlms' ), '{{PRODUCT_TITLE}}', '{{NEXT_PAYMENT_DATE}}' ); ?></p>
		<h4><?php printf( __( 'Order #%s', 'lifterlms' ), '{{ORDER_ID}}' ); ?></h4>
		<?php echo $mailer->get_table_html( $rows ); ?>
		<p><a href="{{ORDER_URL}}"><?php _e( 'Update Payment Method', 'lifterlms' ); ?></a></p>
		<?php
		return ob_get_clean();
	}

	/**
	 * Setup footer content for output
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
	 * Setup notification icon for output
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function set_icon() {
		return $this->get_icon_default( 'warning' );
	}

	/**
	 * Setup merge codes that can be used with the notification
	 *
	 * @since [version]
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
	 * Replace merge codes with actual values
	 *
	 * @since [version]
	 *
	 * @param string $code The merge code to ge merged data for.
	 * @return string
	 */
	protected function set_merge_data( $code ) {

		$order = $this->post;

		switch ( $code ) {

			case '{{CUSTOMER_ADDRESS}}':
				$code = '';
				if ( isset( $order->billing_address_1 ) ) {
					$code .= $order->get( 'billing_address_1' );
					if ( isset( $order->billing_address_2 ) ) {
						$code .= ' ';
						$code .= $order->get( 'billing_address_2' );
					}
					$code .= ', ';
					$code .= $order->get( 'billing_city' );
					$code .= $order->get( 'billing_state' );
					$code .= ', ';
					$code .= $order->get( 'billing_zip' );
					$code .= ', ';
					$code .= llms_get_country_name( $order->get( 'billing_country' ) );
				}
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
				if ( is_a( $obj, 'WP_Post' ) ) {
					$code = _x( 'Item', 'generic product type description', 'lifterlms' );
				} else {
					$code = $obj->get_post_type_label( 'singular_name' );
				}
				break;

		}

		return $code;

	}

	/**
	 * Setup notification subject for output
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function set_subject() {
		// Translators: %1$s = The product title; %2$s = Next payment date.
		return sprintf( __( 'You will be charged for your subscription to %1$s tomorrow on %2$s', 'lifterlms' ), '{{PRODUCT_TITLE}}', '{{NEXT_PAYMENT_DATE}}' );
	}

	/**
	 * Setup notification title for output
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function set_title() {
		if ( 'email' === $this->notification->get( 'type' ) ) {
			// Translators: %s = The order ID.
			return sprintf( __( 'You will be charged for your subscription tomorrow. Order #%s', 'lifterlms' ), '{{ORDER_ID}}' );
		}
		// Translators: %s = The product title.
		return sprintf( __( 'You will be charged for your subscription to %s', 'lifterlms' ), '{{PRODUCT_TITLE}}' );
	}

}
