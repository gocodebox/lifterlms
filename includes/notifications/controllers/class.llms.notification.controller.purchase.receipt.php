<?php
/**
 * Notification Controller: Transaction Success
 * @since    3.8.0
 * @version  3.10.2
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

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
