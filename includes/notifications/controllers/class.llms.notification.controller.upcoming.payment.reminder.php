<?php
/**
 * Notification Controller: Upcoming Payment Reminder
 *
 * @package LifterLMS/Notifications/Controllers/Classes
 *
 * @since 5.2.0
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification Controller: Upcoming Payment Reminder
 *
 * @since 5.2.0
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
	 * Determines if test notifications can be sent
	 *
	 * @var bool
	 */
	protected $testable = array(
		'basic' => false,
		'email' => true,
	);

	/**
	 * Number of accepted arguments passed to the callback function
	 *
	 * @var integer
	 */
	protected $action_accepted_args = 2;

	/**
	 * Add an action to trigger the notification to send
	 *
	 * @since 5.2.0
	 *
	 * @return void
	 */
	protected function add_actions() {

		parent::add_actions();

		// Add actions to recurring payment scheduling/unscheduling.
		add_action( 'llms_charge_recurring_payment_scheduled', array( $this, 'schedule_upcoming_payment_reminders' ), 10, 2 );
		add_action( 'llms_charge_recurring_payment_unscheduled', array( $this, 'unschedule_upcoming_payment_reminders' ) );

	}

	/**
	 * Callback function called when the upcoming payment reminder notification is fired
	 *
	 * @since 5.2.0
	 *
	 * @param int    $order_id WP Post ID of the order.
	 * @param string $type     The notification type identifier.
	 * @return boolean
	 */
	public function action_callback( $order_id = null, $type = null ) {

		// Make sure order_id and type have been provided.
		if ( ! $order_id || ! $type ) {
			return false;
		}

		// These checks are basically the same we do in LLMS_Controller_Orders::recurring_charge().

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

		$this->send( false, array( $type ) );

		return true;

	}

	/**
	 * Takes a subscriber type (student, author, etc) and retrieves a User ID.
	 *
	 * @since 5.2.0
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
	 * @since 5.2.0
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Upcoming Payment Reminder', 'lifterlms' );
	}

	/**
	 * Setup the subscriber options for the notification
	 *
	 * @since 5.2.0
	 *
	 * @param string $type The notification type identifier.
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
	 * Cancels scheduled upcoming payment reminder notifications
	 *
	 * Does nothing if no payments are scheduled.
	 *
	 * @since 5.2.0
	 *
	 * @param LLMS_Order $order Instance of the LLMS_Order which we'll schedule the payment reminder for.
	 * @return void
	 */
	public function unschedule_upcoming_payment_reminders( $order ) {

		$types = array_keys( $this->get_supported_types() );

		foreach ( $types as $type ) {
			$this->unschedule_upcoming_payment_reminder( $order, $type );
		}

	}

	/**
	 * Cancels a scheduled upcoming payment reminder notification type
	 *
	 * Does nothing if no payments are scheduled.
	 *
	 * @since 5.2.0
	 *
	 * @param LLMS_Order $order Instance of the LLMS_Order which we'll schedule the payment reminder for.
	 * @param string     $type  The notification type identifier.
	 * @return void
	 */
	public function unschedule_upcoming_payment_reminder( $order, $type ) {

		$action_args = $this->get_recurring_payment_reminder_action_args( $order, $type );

		if ( as_next_scheduled_action( 'llms_send_upcoming_payment_reminder_notification', $action_args ) ) {
			as_unschedule_action( 'llms_send_upcoming_payment_reminder_notification', $action_args );
		}

	}

	/**
	 * Schedule upcoming payment reminder notification
	 *
	 * @since 5.2.0
	 *
	 * @param LLMS_Order $order        Instance of the LLMS_Order which we'll schedule the payment reminder for.
	 * @param int        $payment_date Optional. The upcoming payment due date in Unix time format and UTC. Default is 0.
	 *                                 When not provided it'll be calculated from the order.
	 * @return array
	 */
	public function schedule_upcoming_payment_reminders( $order, $payment_date = 0 ) {

		$types  = array_keys( $this->get_supported_types() );
		$return = array();
		foreach ( $types as $type ) {
			$return[ $type ] = $this->schedule_upcoming_payment_reminder( $order, $type, $payment_date );
		}

		return $return;

	}

	/**
	 * Schedule upcoming payment reminder notification
	 *
	 * @since 5.2.0
	 *
	 * @param LLMS_Order $order        Instance of the LLMS_Order which we'll schedule the payment reminder for.
	 * @param string     $type         The notification type identifier.
	 * @param int        $payment_date Optional. The upcoming payment due date in Unix time format and UTC. Default is 0.
	 *                                 When not provided it'll be calculated from the order.
	 * @return WP_Error|int WP_Error either if there's no reminder date or if it's passed. Otherwise returns the return value of `as_schedule_single_action`: the action's ID.
	 */
	public function schedule_upcoming_payment_reminder( $order, $type, $payment_date = 0 ) {

		$action_args = $this->get_recurring_payment_reminder_action_args( $order, $type );

		// Unschedule upcoming payment reminder (does nothing if no action scheduled).
		$this->unschedule_upcoming_payment_reminder( $order, $type );

		// Convert our reminder date to Unix Time and UTC before passing to the scheduler.
		$reminder_date = $this->get_upcoming_payment_reminder_date( $order, $type, $payment_date );

		// If no reminder date.
		if ( is_wp_error( $reminder_date ) ) {
			return $reminder_date;
		}

		// Or reminder date set in the past.
		if ( $reminder_date < llms_current_time( 'U', true ) ) {
			return new WP_Error( 'upcoming-payment-reminder-passed', __( 'Upcoming payment reminder passed', 'lifterlms' ) );
		}

		// Schedule upcoming payment reminder.
		return as_schedule_single_action(
			$reminder_date,
			'llms_send_upcoming_payment_reminder_notification',
			$action_args
		);

	}

	/**
	 * Retrieve the date to remind user before actual payment
	 *
	 * @since 5.2.0
	 *
	 * @param LLMS_Order $order        Instance of the LLMS_Order which we'll schedule the payment reminder for.
	 * @param string     $type         The notification type identifier.
	 * @param integer    $payment_date Optional. The upcoming payment due date in Unix time format and UTC. Default is 0.
	 *                                 When not provided it'll be calculated from the order.
	 * @return WP_Error|integer Returns a WP_Error if there's no payment scheduled, otherwise the reminder date in Unix format and UTC.
	 */
	private function get_upcoming_payment_reminder_date( $order, $type, $payment_date = 0 ) {

		$next_payment_date = $payment_date ? $payment_date : $order->get_recurring_payment_due_date_for_scheduler();
		if ( is_wp_error( $next_payment_date ) ) {
			return $next_payment_date;
		}

		/**
		 * Filters the number of days before the upcoming payment due date when to notify the customer
		 *
		 * The dynamic portion of this filter, `$this->id`, refers to the notification trigger identifier.
		 *
		 * @since 5.2.0
		 *
		 * @param integer    $days  The number of days before the upcoming payment due date when to notify the customer.
		 * @param LLMS_Order $order Order object.
		 * @param string     $type  The notification type identifier.
		 */
		$days = apply_filters( "llms_notification_{$this->id}_reminder_days", $this->get_reminder_days( $type ), $order, $type );

		// Sanitize: makes sure it's always a negative number.
		$days = -1 * max( 1, absint( $days ) );

		/**
		 * Filters the next upcoming payment reminder date
		 *
		 * The dynamic portion of this filter, `$this->id`, refers to the notification trigger identifier.
		 *
		 * @since 5.2.0
		 *
		 * @param integer    $upcoming_payment_reminder_time Unix timestamp for the next payment due date.
		 * @param LLMS_Order $order                          Order object.
		 * @param string     $type                           The notification type identifier.
		 */
		$upcoming_payment_reminder_time = apply_filters( "llms_notification_{$this->id}_reminder_date", strtotime( "{$days} day", $next_payment_date ), $order, $type );

		return $upcoming_payment_reminder_time;

	}


	/**
	 * Retrieve arguments passed to order-related events processed by the action scheduler
	 *
	 * @since 5.2.0
	 *
	 * @param LLMS_Order $order Instance of the LLMS_Order which we'll schedule the payment reminder for.
	 */
	private function get_recurring_payment_reminder_action_args( $order, $type ) {
		return array(
			'order_id' => $order->get( 'id' ),
			'type'     => $type,
		);
	}

	/**
	 * Set array of additional options to be added to the notification view in the admin panel
	 *
	 * @since 5.2.0
	 *
	 * @param string $type Type of the notification.
	 * @return array
	 */
	protected function set_additional_options( $type ) {

		return array(
			array(
				'id'                => $this->get_option_name( $type . '_reminder_days' ),
				'title'             => __( 'Reminder days', 'lifterlms' ),
				'desc'              => '<br>' . __( 'The number of days before the upcoming payment due date when to notify the customer.', 'lifterlms' ),
				'type'              => 'number',
				'value'             => $this->get_reminder_days( $type ),
				'custom_attributes' => array(
					'min' => 1,
				),
			),
		);

	}

	/**
	 * Get an array of LifterLMS Admin Page settings to send test notifications
	 *
	 * Retrieves 25 recurring orders with an existing next payment date.
	 *
	 * @since 5.2.0
	 *
	 * @param string $type Notification type [basic|email].
	 * @return array
	 */
	public function get_test_settings( $type ) {

		$query = new WP_Query(
			array(
				'post_type'      => 'llms_order',
				'posts_per_page' => 25,
				'post_status'    => array( 'llms-active', 'llms-failed', 'llms-on-hold', 'llms-pending', 'llms-pending-cancel' ),
				'meta_query'     => array(
					'relation' => 'and',
					array(
						'key'     => '_llms_order_type',
						'value'   => 'recurring',
						'compare' => '=',
					),
					array(
						'key'     => '_llms_date_next_payment',
						'compare' => 'EXISTS',
					),
				),
				'no_found_rows'  => true,
				'order_by'       => 'ID',
			)
		);

		$options = array(
			'' => '',
		);
		foreach ( $query->posts as $post ) {
			$order   = llms_get_post( $post );
			$student = llms_get_student( $order->get( 'user_id' ) );
			if ( $order && $student ) {
				$options[ $order->get( 'id' ) ] = esc_attr(
					sprintf(
						// Translators: %1$d = The Order ID; %2$s The customer's full name; %3$s The product title.
						__( 'Order #%1$d from %2$s for "%3$s"', 'lifterlms' ),
						$order->get( 'id' ),
						$student->get_name(),
						$order->get( 'product_title' )
					)
				);
			}
		}

		return array(
			array(
				'class'             => 'llms-select2',
				'custom_attributes' => array(
					'data-allow-clear' => true,
					'data-placeholder' => __( 'Select a recurring order', 'lifterlms' ),
				),
				'default'           => '',
				'id'                => 'order_id',
				'desc'              => '<br/>' . __( 'Send yourself a test notification using information from the selected recurring order.', 'lifterlms' ),
				'options'           => $options,
				'title'             => __( 'Send a Test', 'lifterlms' ),
				'type'              => 'select',
			),
		);

	}

	/**
	 * Send a test notification to the currently logged in users
	 *
	 * @since 5.2.0
	 *
	 * @param string $type Notification type [basic|email].
	 * @param array  $data Array of test notification data as specified by $this->get_test_data().
	 *
	 * @return int|false
	 */
	public function send_test( $type, $data = array() ) {

		if ( empty( $data['order_id'] ) ) {
			return;
		}

		$order         = llms_get_post( $data['order_id'] );
		$this->user_id = $order->get( 'user_id' );
		$this->post_id = $order->get( 'id' );

		return parent::send_test( $type );

	}

	/**
	 * Undocumented function
	 *
	 * @since 5.2.0
	 *
	 * @param string $type    The notification type identifier.
	 * @param int    $default Opional. The default value. Default is `1`.
	 * @return int
	 */
	private function get_reminder_days( $type, $default = 1 ) {
		return $this->get_option( $type . '_reminder_days', $default );
	}
}

return LLMS_Notification_Controller_Upcoming_Payment_Reminder::instance();
