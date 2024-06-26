<?php
/**
 * Notification View: Payment Receipt
 *
 * @package LifterLMS/Notifications/Views/Classes
 *
 * @since 3.8.0
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification View: Purchase Receipt
 *
 * @since 3.8.0
 * @since 3.8.2 Unknown.
 */
class LLMS_Notification_View_Purchase_Receipt extends LLMS_Abstract_Notification_View {

	/**
	 * Notification Trigger ID
	 *
	 * @var string
	 */
	public $trigger_id = 'purchase_receipt';

	/**
	 * Setup body content for output
	 *
	 * @since 3.8.0
	 * @since 5.2.0 Build the table with mailer helper.
	 *
	 * @return string
	 */
	protected function set_body() {

		$mailer = llms()->mailer();

		$rows = array(
			'TRANSACTION_DATE'   => __( 'Date', 'lifterlms' ),
			'PRODUCT_TITLE_LINK' => '{{PRODUCT_TYPE}}',
			'PLAN_TITLE'         => __( 'Plan', 'lifterlms' ),
			'TRANSACTION_AMOUNT' => __( 'Amount', 'lifterlms' ),
			'TRANSACTION_SOURCE' => __( 'Payment Method', 'lifterlms' ),
			'TRANSACTION_ID'     => __( 'Transaction ID', 'lifterlms' ),
		);

		ob_start();
		$mailer->output_table_html( $rows );
		?>
		<p><a href="{{ORDER_URL}}"><?php esc_html_e( 'View Order Details', 'lifterlms' ); ?></a></p>
		<?php
		return ob_get_clean();
	}

	/**
	 * Setup footer content for output
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	protected function set_footer() {
		return '';
	}

	/**
	 * Setup notification icon for output
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	protected function set_icon() {
		return '';
	}

	/**
	 * Setup merge codes that can be used with the notification
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	protected function set_merge_codes() {
		return array(
			'{{CUSTOMER_ADDRESS}}'   => __( 'Customer Address', 'lifterlms' ),
			'{{CUSTOMER_NAME}}'      => __( 'Customer Name', 'lifterlms' ),
			'{{CUSTOMER_PHONE}}'     => __( 'Customer Phone', 'lifterlms' ),
			'{{ORDER_ID}}'           => __( 'Order ID', 'lifterlms' ),
			'{{ORDER_URL}}'          => __( 'Order URL', 'lifterlms' ),
			'{{PLAN_TITLE}}'         => __( 'Plan Title', 'lifterlms' ),
			'{{PRODUCT_TITLE}}'      => __( 'Product Title', 'lifterlms' ),
			'{{PRODUCT_TYPE}}'       => __( 'Product Type', 'lifterlms' ),
			'{{PRODUCT_TITLE_LINK}}' => __( 'Product Title (Link)', 'lifterlms' ),
			'{{TRANSACTION_AMOUNT}}' => __( 'Transaction Amount', 'lifterlms' ),
			'{{TRANSACTION_DATE}}'   => __( 'Transaction Date', 'lifterlms' ),
			'{{TRANSACTION_ID}}'     => __( 'Transaction ID', 'lifterlms' ),
			'{{TRANSACTION_SOURCE}}' => __( 'Transaction Source', 'lifterlms' ),
		);
	}

	/**
	 * Replace merge codes with actual values
	 *
	 * @since 3.8.0
	 * @since 3.8.2 Unknown.
	 * @since 5.2.0 Retrieve the customer's full address using the proper order's method.
	 *
	 * @param string $code The merge code to get merged data for.
	 * @return string
	 */
	protected function set_merge_data( $code ) {

		$transaction = $this->post;
		$order       = $transaction->get_order();

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

			case '{{ORDER_ID}}':
				$code = $order->get( 'id' );
				break;

			case '{{ORDER_URL}}':
				$code = esc_url( $order->get_view_link() );
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
				if ( $obj ) {
					$code = $obj->get_post_type_label( 'singular_name' );
				} else {
					$code = _x( 'Item', 'generic product type description', 'lifterlms' );
				}
				break;

			case '{{TRANSACTION_AMOUNT}}':
				$code = $transaction->get_price( 'amount' );
				break;

			case '{{TRANSACTION_DATE}}':
				$code = $transaction->get_date( 'date', get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
				break;

			case '{{TRANSACTION_ID}}':
				$code = $transaction->get( 'id' );
				break;

			case '{{TRANSACTION_SOURCE}}':
				$code = $transaction->get( 'gateway_source_description' );
				break;

		}

		return $code;
	}

	/**
	 * Setup notification subject for output
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	protected function set_subject() {
		// Translators: %s = Product Title.
		return sprintf( __( 'Purchase Receipt for %s', 'lifterlms' ), '{{PRODUCT_TITLE}}' );
	}

	/**
	 * Setup notification title for output
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	protected function set_title() {
		// Translators: %s = Order ID.
		return sprintf( __( 'Purchase Receipt for Order #%s', 'lifterlms' ), '{{ORDER_ID}}' );
	}
}
