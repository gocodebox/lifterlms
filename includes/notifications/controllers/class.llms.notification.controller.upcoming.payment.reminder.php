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
class LLMS_Notification_Controller_Upcoming_Payment_Reminder extends LLMS_Notification_Controller_Payment_Retry {

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
	 * Callback function called when a payment retry is scheduled
	 *
	 * @since [version]
	 *
	 * @param int $order Instance of an LLMS_Order.
	 * @return void
	 */
	public function action_callback( $order = null ) {

		$order = llms_get_post( $order );

		$this->user_id = $order->get( 'user_id' );
		$this->post_id = $order->get( 'id' );

		$this->send();

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

}

return LLMS_Notification_Controller_Upcoming_Payment_Reminder::instance();
