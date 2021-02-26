<?php
/**
 * Notification Controller: Upcoming Payment Reminder
 *
 * @package LifterLMS/Notifications/Controllers/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification Controller: Upcoming Payment Reminder
 *
 * @since [version]
 */
class LLMS_Notification_Controller_Upcoming_Payment_Reminder extends LLMS_Abstract_Notification_Controller {

	/**
	 * Trigger Identifier
	 *
	 * @var string
	 */
	public $id = 'upcoming_payment_reminder';

	/**
	 * Action hooks used to trigger sending of the notification
	 *
	 * @var array
	 */
	protected $action_hooks = array(
		'llms_send_upcoming_payment_reminder_notification',
	);

	/**
	 * Callback function called when the upcoming payment reminder notification is fired
	 *
	 * @since [version]
	 *
	 * @param int $order_id WP Post ID of the order.
	 * @return boolean
	 */
	public function action_callback( $order_id = null ) {

		$order = llms_get_post( $order_id );

		// The order has been deleted?
		if ( ! is_a( $order, 'LLMS_Order' ) ) {
			return false;
		}

		$user_id = $order->get( 'user_id' );

		// Deleted user?
		if ( ! get_user_by( 'id', $user_id ) ) {
			return false;
		}

		$this->user_id = $user_id;
		$this->post_id = $order->get( 'id' );

		$this->send();

		return true;

	}

	/**
	 * Takes a subscriber type (student, author, etc) and retrieves a User ID.
	 *
	 * @since [version]
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
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Upcoming Payment Reminder', 'lifterlms' );
	}

	/**
	 * Setup the subscriber options for the notification
	 *
	 * @since [version]
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

return LLMS_Notification_Controller_Upcoming_Payment_Reminder::instance();
