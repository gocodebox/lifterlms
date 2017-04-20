<?php
/**
 * Notification View: Purchase Receipt
 * @since    [version]
 * @version  [version]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Notification_View_Purchase_Receipt extends LLMS_Abstract_Notification_View {

	/**
	 * Notification Trigger ID
	 * @var  [type]
	 */
	public $trigger_id = 'purchase_receipt';

	/**
	 * Setup body content for output
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_body() {

		ob_start();
		?>
		<p><?php printf( __( 'Order #%s', 'lifterlms' ), '{{ORDER_ID}}' ); ?></p>
		<p>{{TRANSACTION_DATE}} &ndash; {{TRANSACTION_ID}}</p>
		<?php
		return ob_get_clean();

	}

	/**
	 * Setup footer content for output
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_footer() {
		return '';
	}

	/**
	 * Setup notification icon for output
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_icon() {
		return '';
	}

	/**
	 * Setup merge codes that can be used with the notification
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_merge_codes() {
		return array(
			'{{ORDER_ID}}' => __( 'Order ID', 'lifterlms' ),
			'{{PRODUCT_TITLE}}' => __( 'Product Title', 'lifterlms' ),
			'{{STUDENT_NAME}}' => __( 'Student Name', 'lifterlms' ),
			'{{TRANSACTION_DATE}}' => __( 'Transaction Date', 'lifterlms' ),
			'{{TRANSACTION_ID}}' => __( 'Transaction ID', 'lifterlms' ),
		);
	}

	/**
	 * Replace merge codes with actual values
	 * @param    string   $code  the merge code to ge merged data for
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_merge_data( $code ) {

		$transaction = $this->post;
		$order = $transaction->get_order();

		switch ( $code ) {

			case '{{ORDER_ID}}':
				$code = $order->get( 'id' );
			break;

			case '{{PRODUCT_TITLE}}':
				$code = $order->get( 'product_title' );
			break;

			case '{{STUDENT_NAME}}':
				$code = $this->is_for_self() ? 'you' : $this->user->get_name();
			break;

			case '{{TRANSACTION_DATE}}':
				$code = $transaction->get( 'date', get_option( 'date_format' ) );
			break;

			case '{{TRANSACTION_ID}}':
				$code = $transaction->get( 'id' );
			break;

		}

		return $code;

	}

	/**
	 * Setup notification subject for output
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_subject() {
		return sprintf( __( 'Purchase Receipt for %s', 'lifterlms' ), '{{PRODUCT_TITLE}}' );
	}

	/**
	 * Setup notification title for output
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_title() {
		return __( 'Purchase Receipt', 'lifterlms' );
	}

}
