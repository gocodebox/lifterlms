<?php
/**
 * Notification Controller: Upcoming Payment Reminder
 *
 * @package LifterLMS/Notifications/Controllers/Classes
 *
 * @since 3.10.0
 * @version 3.10.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification Controller: Upcoming Payment Reminder
 *
 * @since 3.10.0
 */
class LLMS_Notification_Controller_Upcoming_Payment_Reminder extends LLMS_Notification_Controller_Payment_Retry {

	/**
	 * Trigger Identifier
	 *
	 * @var  [type]
	 */
	public $id = 'upcoming_payment_reminder';

	/**
	 * Action hooks used to trigger sending of the notification
	 *
	 * @var  array
	 */
	protected $action_hooks = array(
		'llms_send_upcoming_payment_reminder_notification',
	);

	/**
	 * Callback function called when a payment retry is scheduled
	 *
	 * @param    int $order   Instance of an LLMS_Order
	 * @return   void
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	public function action_callback( $order = null ) {

		$order = llms_get_post($order);

		$this->user_id = $order->get( 'user_id' );
		$this->post_id = $order->get( 'id' );

		$this->send();

	}

	/**
	 * Get the translatable title for the notification
	 * used on settings screens
	 *
	 * @return   string
	 */
	public function get_title() {
		return __( 'Upcoming Payment Reminder', 'lifterlms' );
	}

}

return LLMS_Notification_Controller_Upcoming_Payment_Reminder::instance();
