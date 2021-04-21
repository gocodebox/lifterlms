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
	 * Add an action to trigger the notification to send
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	protected function add_actions() {

		parent::add_actions();

		// Add actions to recurring payment scheduling/unscheduling.
		add_action( 'llms_charge_recurring_payment_scheduled', array( $this, 'schedule_upcoming_payment_reminder' ), 10, 2 );
		add_action( 'llms_charge_recurring_payment_unscheduled', array( $this, 'unschedule_upcoming_payment_reminder' ) );

	}

	/**
	 * Callback function called when the upcoming payment reminder notification is fired
	 *
	 * @since [version]
	 *
	 * @param int $order_id WP Post ID of the order.
	 * @return boolean
	 */
	public function action_callback( $order_id = null ) {

		// These checks are basically the same we do in LLMS_Controller_Orders::recurring_charge().
		// TODO: create a new method that can be used by both.

		// Recurring payments disabled as a site feature when in staging mode.
		if ( ! LLMS_Site::get_feature( 'recurring_payments' ) ) {
			return false;
		}

		$order = llms_get_post( $order_id );

		// Make sure the order still exists.
		if ( ! $order || ! is_a( $order, 'LLMS_Order' ) ) {
			return false;
		}

		$user_id = $order->get( 'user_id' );

		// Check the user still exists.
		if ( ! get_user_by( 'id', $user_id ) ) {
			return false;
		}

		// Ensure Gateway is still available and supports recurring payments.
		$gateway = $order->get_gateway();
		if ( is_wp_error( $gateway ) || ! $gateway->supports( 'recurring_payments' ) ) {
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

	/**
	 * Cancels a scheduled upcoming payment reminder notification
	 *
	 * Does nothing if no payments are scheduled.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function unschedule_upcoming_payment_reminder( $order ) {

		$action_args = array(
			'order_id' => $order->get( 'id' ),
		);

		if ( as_next_scheduled_action( 'llms_send_upcoming_payment_reminder_notification', $action_args ) ) {
			as_unschedule_action( 'llms_send_upcoming_payment_reminder_notification', $action_args );
		}

	}

	/**
	 * Undocumented function
	 *
	 * @since [version]
	 *
	 * @param LLMS_Order $order Instance of the LLMS_Order which we'll schedule the payment reminder for.
	 * @param integer    $date  Optional. The upcoming payment due date in Unix time format and UTC. Default is 0.
	 *                          When not provided it'll be calculated from the order.
	 * @return void
	 */
	public function schedule_upcoming_payment_reminder( $order, $date = 0 ) {

		// Unschedule upcoming payment reminder (does nothing if no action scheduled).
		$this->unschedule_upcoming_payment_reminder();

		// Convert our reminder date to Unix Time and UTC before passing to the scheduler.
		$reminder_date = $date ? $date : get_gmt_from_date(
			$this->get_upcoming_payment_reminder_date( $order ),
			'U'
		);

		$action_args = array(
			'order_id' => $order->get( 'id' ),
		);

		// Schedule upcoming payment reminder.
		as_schedule_single_action(
			$reminder_date,
			'llms_send_upcoming_payment_reminder_notification',
			$action_args
		);

	}

	/**
	 * Retrieve the date to remind user before actual payment
	 *
	 * @since [version]
	 *
	 * @param LLMS_Order $order  Instance of the LLMS_Order which we'll calculate the reminder date from.
	 * @param integer    $date   Optional. The upcoming payment due date in Unix time format and UTC. Default is 0.
	 *                           When not provided it'll be calculated from the order.
	 * @param string     $format Optional. Date return format. Default is 'Y-m-d H:i:s'.
	 * @return WP_Error|string
	 */
	public function get_upcoming_payment_reminder_date( $order, $date = 0, $format = 'Y-m-d H:i:s' ) {

		$next_payment_date = $date ? $date : $order->get_next_payment_due_date( $format );

		if ( ! $next_payment_date || is_wp_error( $next_payment_date ) ) {
			return new WP_Error( 'plan-ended', __( 'No more payments due', 'lifterlms' ) );
		}

		/**
		 * Filters the number of days before the upcoming payment due date when to notify the customer
		 *
		 * @since [version]
		 *
		 * @param int        $days  The number of days before the upcoming payment due date when to notify the customer.
		 * @param LLMS_Order $order Order object.
		 */
		$days = apply_filters( 'llms_order_payment_reminder_days', 1, $order );

		// Sanitize: makes sure it's always a negative number.
		$days = -1 * min( 1, absint( $days ) );

		/**
		 * Filters the next upcoming payment reminder date
		 *
		 * A timestamp should always be returned as the conversion to the requested format
		 * will be performed on the returned value.
		 *
		 * @since [version]
		 *
		 * @param int        $upcoming_payment_reminder_time Unix timestamp for the next payment due date.
		 * @param LLMS_Order $order                          Order object.
		 * @param string     $format                         Requested date format.
		 */
		$upcoming_payment_reminder_time = apply_filters( 'llms_order_get_next_upcoming_payment_reminder_date', strtotime( "{$days} day", strtotime( $next_payment_date ) ), $order, $format );

		return date_i18n( $format, $upcoming_payment_reminder_time );

	}

}

return LLMS_Notification_Controller_Upcoming_Payment_Reminder::instance();
