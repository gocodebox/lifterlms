<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notification View: Purchase Receipt
 * @since    3.10.0
 * @version  3.10.0
 */
class LLMS_Notification_View_Manual_Payment_Due extends LLMS_Abstract_Notification_View {

	/**
	 * Settings for basic notifications
	 * @var  array
	 */
	protected $basic_options = array(
		/**
		 * Time in milliseconds to show a notification
		 * before automatically dismissing it
		 */
		'auto_dismiss' => 10000,
		/**
		 * Enables manual dismissal of notifications
		 */
		'dismissible' => true,
	);


	/**
	 * Notification Trigger ID
	 * @var  [type]
	 */
	public $trigger_id = 'manual_payment_due';

	/**
	 * Setup body content for output
	 * @return   string
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	protected function set_body() {

		if ( 'email' === $this->notification->get( 'type' ) ) {
			return $this->set_body_email();
		}
		return $this->set_body_basic();

	}

	/**
	 * Setup default notification body for basic notifications
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	private function set_body_basic() {
		return __( 'Head over to your dashboard for payment instructions.', 'lifterlms' );
	}

	/**
	 * Setup default notification body for email notifications
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	private function set_body_email() {
		$mailer = LLMS()->mailer();

		$table_style = sprintf(
			'border-collapse:collapse;color:%1$s;font-family:%2$s;font-size:%3$s;Margin-bottom:15px;text-align:left;width:100%%;',
			$mailer->get_css( 'font-color', false ),
			$mailer->get_css( 'font-family', false ),
			$mailer->get_css( 'font-size', false )
		);
		$tr_style = 'color:inherit;font-family:inherit;font-size:inherit;';
		$td_style = sprintf( 'border-bottom:1px solid %s;color:inherit;font-family:inherit;font-size:inherit;padding:10px;', $mailer->get_css( 'divider-color', false ) );

		$rows = array(
			'NEXT_PAYMENT_DATE' => __( 'Payment Due Date', 'lifterlms' ),
			'PRODUCT_TITLE_LINK' => '{{PRODUCT_TYPE}}',
			'PLAN_TITLE' => __( 'Plan', 'lifterlms' ),
			'PAYMENT_AMOUNT' => __( 'Amount', 'lifterlms' ),
		);

		ob_start();
		?><p><?php printf( __( 'Hello %s,', 'lifterlms' ), '{{CUSTOMER_NAME}}' ); ?></p>
		<p><?php printf( __( 'A payment for your subscription to %1$s is due.', 'lifterlms' ), '{{PRODUCT_TITLE}}' ); ?></p>
		<p><?php printf( __( 'Sign in to your account and %1$spay now%2$s.', 'lifterlms' ), '<a href="{{ORDER_URL}}">','</a>' ); ?></p>
		<h4><?php printf( __( 'Order #%s', 'lifterlms' ), '{{ORDER_ID}}' ); ?></h4>
		<table style="<?php echo $table_style; ?>">
		<?php foreach ( $rows as $code => $name ) : ?>
			<tr style="<?php echo $tr_style; ?>">
				<th style="<?php echo $td_style; ?>width:33.3333%;"><?php echo $name; ?></th>
				<td style="<?php echo $td_style; ?>">{{<?php echo $code; ?>}}</td>
			</tr>
		<?php endforeach; ?>
		</table>
		<p><a href="{{ORDER_URL}}"><?php _e( 'Pay Invoice', 'lifterlms' ); ?></a></p>
		<?php
		return ob_get_clean();
	}

	/**
	 * Setup footer content for output
	 * @return   string
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	protected function set_footer() {
		$url = $this->set_merge_data( '{{ORDER_URL}}' );
		return '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Pay Now', 'lifterlms' ) . '</a>';
	}

	/**
	 * Setup notification icon for output
	 * @return   string
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	protected function set_icon() {
		return $this->get_icon_default( 'warning' );
	}

	/**
	 * Setup merge codes that can be used with the notification
	 * @return   array
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	protected function set_merge_codes() {
		return array(
			'{{CUSTOMER_ADDRESS}}' => __( 'Customer Address', 'lifterlms' ),
			'{{CUSTOMER_NAME}}' => __( 'Customer Name', 'lifterlms' ),
			'{{CUSTOMER_PHONE}}' => __( 'Customer Phone', 'lifterlms' ),
			'{{NEXT_PAYMENT_DATE}}' => __( 'Next Payment Date', 'lifterlms' ),
			'{{ORDER_ID}}' => __( 'Order ID', 'lifterlms' ),
			'{{ORDER_URL}}' => __( 'Order URL', 'lifterlms' ),
			'{{PAYMENT_AMOUNT}}' => __( 'Payment Amount', 'lifterlms' ),
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
	 * @since    3.10.0
	 * @version  3.10.0
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
					$code = '<a href="' . $permalink . '">' . $title . '</a>';
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

		}// End switch().

		return $code;

	}

	/**
	 * Setup notification subject for output
	 * @return   string
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	protected function set_subject() {
		return sprintf( __( 'A payment is due for your subscription to %s', 'lifterlms' ), '{{PRODUCT_TITLE}}' );
	}

	/**
	 * Setup notification title for output
	 * @return   string
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	protected function set_title() {
		if ( 'email' === $this->notification->get( 'type' ) ) {
			return sprintf( __( 'Payment Due for Order #%s', 'lifterlms' ), '{{ORDER_ID}}' );
		}
		return sprintf( __( 'A payment is due for your subscription to %s', 'lifterlms' ), '{{PRODUCT_TITLE}}' );
	}

}
