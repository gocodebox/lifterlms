<?php
defined( 'ABSPATH' ) || exit;

/**
 * Notification Controller: Transaction Success
 * @since    3.8.0
 * @version  3.24.0
 */
class LLMS_Notification_Controller_Purchase_Receipt extends LLMS_Abstract_Notification_Controller {

	/**
	 * Trigger Identifier
	 * @var  [type]
	 */
	public $id = 'purchase_receipt';

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
		'lifterlms_resend_transaction_receipt',
		'lifterlms_transaction_status_succeeded',
	);

	/**
	 * Determines if test notifications can be sent
	 * @var  bool
	 */
	protected $testable = array(
		'basic' => false,
		'email' => true,
	);

	/**
	 * Callback function called when a lesson is completed by a student
	 * @param    int     $transaction   Instance of a LLMS_Transaction
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function action_callback( $transaction = null ) {

		$order = $transaction->get_order();
		$this->user_id = $order->get( 'user_id' );
		$this->post_id = $transaction->get( 'id' );

		$this->send();

	}

	/**
	 * Takes a subscriber type (student, author, etc) and retrieves a User ID
	 * @param    string     $subscriber  subscriber type string
	 * @return   int|false
	 * @since    3.8.0
	 * @version  3.10.2
	 */
	protected function get_subscriber( $subscriber ) {

		switch ( $subscriber ) {

			case 'author':
				$txn = llms_get_post( $this->post_id );
				$order = $txn->get_order();
				if ( ! $order ) {
					return false;
				}
				$product = $order->get_product();
				if ( ! $product ) {
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
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_supported_types() {
		return array(
			'email' => __( 'Email', 'lifterlms' ),
		);
	}

	/**
	 * Get an array of LifterLMS Admin Page settings to send test notifications
	 * @param    string     $type  notification type [basic|email]
	 * @return   array
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function get_test_settings( $type ) {

		$query = new WP_Query( array(
			'post_type' => 'llms_transaction',
			'posts_per_page' => 25,
		) );

		$options = array(
			'' => '',
		);
		foreach ( $query->posts as $post ) {
			$transaction = llms_get_post( $post );
			$order = $transaction->get_order();
			$student = llms_get_student( $order->get( 'user_id' ) );
			if ( $transaction && $student ) {
				$options[ $transaction->get( 'id' ) ] = esc_attr( sprintf( __( 'Order #%1$d from %2$s for "%3$s"', 'lifterlms' ), $order->get( 'id' ), $student->get_name(), $order->get( 'product_title' ) ) );
			}
		}

		return array(
			array(
				'class' => 'llms-select2',
				'custom_attributes' => array(
					'data-allow-clear' => true,
					'data-placeholder' => __( 'Select a transaction', 'lifterlms' ),
				),
				'default'	=> '',
				'id' => 'transaction_id',
				'desc' => '<br/>' . __( 'Send yourself a test notification using information from the selected transaction.', 'lifterlms' ),
				'options' => $options,
				'title' => __( 'Send a Test', 'lifterlms' ),
				'type' => 'select',
				// 'selected' => false,
			),
		);

	}

	/**
	 * Get the translateable title for the notification
	 * used on settings screens
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_title() {
		return __( 'Purchase Receipt', 'lifterlms' );
	}

	/**
	 * Send a test notification to the currently logged in users
	 * Extending classes should redefine this in order to properly setup the controller with post_id and user_id data
	 * @param    string   $type  notification type [basic|email]
	 * @param    array    $data  array of test notification data as specified by $this->get_test_data()
	 * @return   int|false
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function send_test( $type, $data = array() ) {

		if ( empty( $data['transaction_id'] ) ) {
			return;
		}

		$transaction = llms_get_post( $data['transaction_id'] );
		$order = $transaction->get_order();
		$this->user_id = $order->get( 'user_id' );
		$this->post_id = $transaction->get( 'id' );

		return parent::send_test( $type );

	}

	/**
	 * Setup the subscriber options for the notification
	 * @param    string     $type  notification type id
	 * @return   array
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_subscriber_options( $type ) {

		$options = array();

		switch ( $type ) {

			case 'email':
				$options[] = $this->get_subscriber_option_array( 'author', 'yes' );
				$options[] = $this->get_subscriber_option_array( 'student', 'yes' );
				$options[] = $this->get_subscriber_option_array( 'custom', 'no' );
			break;

		}

		return $options;

	}

}

return LLMS_Notification_Controller_Purchase_Receipt::instance();
