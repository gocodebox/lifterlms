<?php
/**
 * Notification Controller: Order Failed
 *
 * @package LifterLMS/Notifications/Controllers/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification Controller: Order Failed
 *
 * Sends a notification when an order is marked as failed. Covers both the
 * initial checkout failure and the terminal recurring payment failure that
 * occurs after all retry rules are exhausted.
 *
 * @since [version]
 */
class LLMS_Notification_Controller_Order_Failed extends LLMS_Abstract_Notification_Controller {

	/**
	 * Trigger Identifier
	 *
	 * @var string
	 */
	public $id = 'order_failed';

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
		'lifterlms_order_status_failed',
		'llms_automatic_payment_maximum_retries_reached',
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
	 * Per-request cache of order IDs for which the notification has already
	 * been dispatched. Prevents duplicate sends when both action hooks fire
	 * for the same order in a single request (e.g. when the terminal retry
	 * failure cascades into the `llms-failed` status transition).
	 *
	 * @var int[]
	 */
	protected static $sent = array();

	/**
	 * Callback function called when an order is marked as failed.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Order $order Instance of an LLMS_Order.
	 * @return void
	 */
	public function action_callback( $order = null ) {

		if ( ! is_a( $order, 'LLMS_Order' ) ) {
			return;
		}

		$order_id = $order->get( 'id' );
		if ( ! $order_id || isset( self::$sent[ $order_id ] ) ) {
			return;
		}

		self::$sent[ $order_id ] = true;

		$this->user_id = $order->get( 'user_id' );
		$this->post_id = $order_id;

		$this->send();

	}

	/**
	 * Takes a subscriber type (student, author, etc) and retrieves a User ID
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
				if ( ! $product || is_a( $product, 'WP_Post' ) ) {
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
		return __( 'Order Failed', 'lifterlms' );
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

			case 'email':
				$options[] = $this->get_subscriber_option_array( 'author', 'no' );
				$options[] = $this->get_subscriber_option_array( 'student', 'yes' );
				$options[] = $this->get_subscriber_option_array( 'custom', 'no' );
				break;

		}

		return $options;

	}

	/**
	 * Determine what types are supported
	 *
	 * @since [version]
	 *
	 * @return array Associative array, keys are the ID/db type, values should be translated display types.
	 */
	protected function set_supported_types() {
		return array(
			'email' => __( 'Email', 'lifterlms' ),
		);
	}

	/**
	 * Get an array of LifterLMS Admin Page settings to send test notifications
	 *
	 * @since [version]
	 *
	 * @param string $type Notification type [basic|email].
	 * @return array
	 */
	public function get_test_settings( $type ) {

		$query = new WP_Query(
			array(
				'post_type'      => 'llms_order',
				'posts_per_page' => 25,
				'post_status'    => 'any',
			)
		);

		$options = array(
			'' => '',
		);
		foreach ( $query->posts as $post ) {
			$order   = llms_get_post( $post );
			$student = $order ? llms_get_student( $order->get( 'user_id' ) ) : false;
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
					'data-placeholder' => __( 'Select an order', 'lifterlms' ),
				),
				'default'           => '',
				'id'                => 'order_id',
				'desc'              => '<br/>' . __( 'Send yourself a test notification using information from the selected order.', 'lifterlms' ),
				'options'           => $options,
				'title'             => __( 'Send a Test', 'lifterlms' ),
				'type'              => 'select',
			),
		);
	}

	/**
	 * Send a test notification to the currently logged in user
	 *
	 * @since [version]
	 *
	 * @param string $type Notification type [basic|email].
	 * @param array  $data Array of test notification data as specified by $this->get_test_settings().
	 * @return int|false
	 */
	public function send_test( $type, $data = array() ) {

		if ( empty( $data['order_id'] ) ) {
			return;
		}

		$order         = llms_get_post( $data['order_id'] );
		if ( ! is_a( $order, 'LLMS_Order' ) ) {
			return;
		}
		$this->user_id = $order->get( 'user_id' );
		$this->post_id = $order->get( 'id' );

		return parent::send_test( $type );

	}

}

return LLMS_Notification_Controller_Order_Failed::instance();
