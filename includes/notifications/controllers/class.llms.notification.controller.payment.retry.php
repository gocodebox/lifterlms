<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notification Controller: Payment Retry Scheduled
 * @since    3.10.0
 * @version  3.10.0
 */
class LLMS_Notification_Controller_Payment_Retry extends LLMS_Abstract_Notification_Controller {

	/**
	 * Trigger Identifier
	 * @var  [type]
	 */
	public $id = 'payment_retry';

	/**
	 * Number of accepted arguments passed to the callback function
	 * @var  integer
	 */
	protected $action_accepted_args = 1;

	/**
	 * Action hooks used to trigger sending of the notification
	 * @var  array
	 */
	protected $action_hooks = array(
		'llms_send_automatic_payment_retry_notification',
	);

	/**
	 * Callback function called when a payment retry is scheduled
	 * @param    int     $order   Instance of an LLMS_Order
	 * @return   void
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	public function action_callback( $order = null ) {

		$this->user_id = $order->get( 'user_id' );
		$this->post_id = $order->get( 'id' );

		$this->send();

	}

	/**
	 * Takes a subscriber type (student, author, etc) and retrieves a User ID
	 * @param    string     $subscriber  subscriber type string
	 * @return   int|false
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	protected function get_subscriber( $subscriber ) {

		switch ( $subscriber ) {

			case 'author':
				$order = llms_get_post( $this->post_id );
				if ( ! is_a( $order, 'LLMS_Order' ) ) {
					return false;
				}
				$product = $order->get_product();
				if ( is_a( $product, 'WP_Post' ) ) {
					return false;
				}
				$uid = $product->get( 'author' );
			break;

			case 'student':
				$uid = $this->user_id;
			break;

			default:
				$uid = false;

		}

		return $uid;

	}

	/**
	 * Determine what types are supported
	 * Extending classes can override this function in order to add or remove support
	 * 3rd parties should add support via filter on $this->get_supported_types()
	 * @return   array        associative array, keys are the ID/db type, values should be translated display types
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	protected function set_supported_types() {
		return array(
			'basic' => __( 'Basic', 'lifterlms' ),
			'email' => __( 'Email', 'lifterlms' ),
		);
	}

	/**
	 * Get the translateable title for the notification
	 * used on settings screens
	 * @return   string
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	public function get_title() {
		return __( 'Payment Retry Scheduled', 'lifterlms' );
	}

	/**
	 * Setup the subscriber options for the notification
	 * @param    string     $type  notification type id
	 * @return   array
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	protected function set_subscriber_options( $type ) {

		$options = array();

		switch ( $type ) {

			case 'basic':
				$options[] = $this->get_subscriber_option_array( 'student', 'yes' );
			break;

			case 'email':
				$options[] = $this->get_subscriber_option_array( 'author', 'no' );
				$options[] = $this->get_subscriber_option_array( 'student', 'yes' );
				$options[] = $this->get_subscriber_option_array( 'custom', 'no' );
			break;

		}

		return $options;

	}

}

return LLMS_Notification_Controller_Payment_Retry::instance();
