<?php
/**
 * Notification Controller: Payment Retry Scheduled
 *
 * @package LifterLMS/Notifications/Controllers/Classes
 *
 * @since 3.10.0
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification Controller: Payment Retry Scheduled
 *
 * @since 3.10.0
 */
class LLMS_Notification_Controller_Payment_Retry extends LLMS_Abstract_Notification_Controller {

	/**
	 * Trigger Identifier
	 *
	 * @var string
	 */
	public $id = 'payment_retry';

	/**
	 * Number of accepted arguments passed to the callback function
	 *
	 * @var integer
	 */
	protected $action_accepted_args = 1;

	/**
	 * Action hooks used to trigger sending of the notification
	 *
	 * @var array
	 */
	protected $action_hooks = array(
		'llms_send_automatic_payment_retry_notification',
	);

	/**
	 * Callback function called when a payment retry is scheduled
	 *
	 * @since 3.10.0
	 *
	 * @param int $order Instance of an LLMS_Order.
	 * @return void
	 */
	public function action_callback( $order = null ) {

		$this->user_id = $order->get( 'user_id' );
		$this->post_id = $order->get( 'id' );

		$this->send();

	}

	/**
	 * Takes a subscriber type (student, author, etc) and retrieves a User ID
	 *
	 * @since 3.10.0
	 *
	 * @param string $subscriber Subscriber type string.
	 * @return int|false
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
	 * Get the translatable title for the notification
	 *
	 * Used on settings screens.
	 *
	 * @since 3.10.0
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Payment Retry Scheduled', 'lifterlms' );
	}

	/**
	 * Setup the subscriber options for the notification
	 *
	 * @since 3.10.0
	 *
	 * @param string $type Notification type id.
	 * @return array
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
