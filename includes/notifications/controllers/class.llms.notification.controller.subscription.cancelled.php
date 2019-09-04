<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notification Controller: Subscription Cancelled (by Student)
 *
 * @since    3.17.8
 * @version  3.17.8
 */
class LLMS_Notification_Controller_Subscription_Cancelled extends LLMS_Abstract_Notification_Controller {

	/**
	 * Trigger Identifier
	 *
	 * @var  [type]
	 */
	public $id = 'subscription_cancelled';

	/**
	 * Number of accepted arguments passed to the callback function
	 *
	 * @var  integer
	 */
	protected $action_accepted_args = 2;

	/**
	 * Action hooks used to trigger sending of the notification
	 *
	 * @var  array
	 */
	protected $action_hooks = array( 'llms_subscription_cancelled_by_student' );

	/**
	 * Callback function, called upon student subscription cancellation
	 *
	 * @param    obj $order       Instance of the LLMS_Order
	 * @param    int $student_id  WP User ID of the Student
	 * @return   void
	 * @since    3.17.8
	 * @version  3.17.8
	 */
	public function action_callback( $order = null, $student_id = null ) {

		$this->user_id = $student_id;
		$this->post_id = $order->get( 'id' );

		$this->send();

	}

	/**
	 * Takes a subscriber type (student, author, etc) and retrieves a User ID
	 *
	 * @param    string $subscriber  subscriber type string
	 * @return   int|false
	 * @since    3.17.8
	 * @version  3.17.8
	 */
	protected function get_subscriber( $subscriber ) {

		switch ( $subscriber ) {

			case 'author':
				$order = llms_get_post( $this->post_id );
				if ( ! $order ) {
					return false;
				}
				$product = $order->get_product();
				if ( ! $product ) {
					return false;
				}
				$uid = $product->get( 'author' );
				break;

			default:
				$uid = false;

		}

		return $uid;

	}

	/**
	 * Get the translatable title for the notification
	 * used on settings screens
	 *
	 * @return   string
	 * @since    3.17.8
	 * @version  3.17.8
	 */
	public function get_title() {
		return __( 'Subscription Cancellation Notice', 'lifterlms' );
	}

	/**
	 * Setup the subscriber options for the notification
	 *
	 * @param    string $type  notification type id
	 * @return   array
	 * @since    3.17.8
	 * @version  3.17.8
	 */
	protected function set_subscriber_options( $type ) {

		$options = array();

		switch ( $type ) {

			case 'email':
				$options[] = $this->get_subscriber_option_array( 'author', 'yes' );
				$options[] = $this->get_subscriber_option_array( 'custom', 'no' );
				break;

		}

		return $options;

	}

	/**
	 * Determine what types are supported
	 * Extending classes can override this function in order to add or remove support
	 * 3rd parties should add support via filter on $this->get_supported_types()
	 *
	 * @return   array        associative array, keys are the ID/db type, values should be translated display types
	 * @since    3.17.8
	 * @version  3.17.8
	 */
	protected function set_supported_types() {
		return array(
			'email' => __( 'Email', 'lifterlms' ),
		);
	}

}

return LLMS_Notification_Controller_Subscription_Cancelled::instance();
