<?php
/**
 * Notification Controller: Transaction Success
 * @since    [version]
 * @version  [version]
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
	protected $action_accepted_arguments = 1;

	/**
	 * Action hook used to trigger sending of the notification
	 * @var  string
	 */
	protected $action_hook = 'lifterlms_transaction_status_succeeded';

	/**
	 * Callback function called when a lesson is completed by a student
	 * @param    int     $transactin   Instance of a LLMS_Transaction
	 * @return   void
	 * @since    [version]
	 * @version  [version]
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
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_subscriber( $subscriber ) {

		switch ( $subscriber ) {

			// case 'author':
			// 	// $uid = $this->course->get( 'author' );
			// break;

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
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_supported_types() {
		return array(
			// 'basic' => __( 'Basic', 'lifterlms' ),
			'email' => __( 'Email', 'lifterlms' ),
		);
	}

	/**
	 * Get the translateable title for the notification
	 * used on settings screens
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_title() {
		return __( 'Purchase Receipt', 'lifterlms' );
	}

	/**
	 * Setup the subscriber options for the notification
	 * @param    string     $type  notification type id
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_subscriber_options( $type ) {

		$options = array();

		switch ( $type ) {

			case 'email':
				$options[] = array(
					'enabled' => 'yes',
					'id' => 'student',
					'title' => __( 'Student', 'lifterlms' ),
				);
				$options[] = array(
					'description' => __( 'Enter additional email addresses which will recieve this notification. Separate multilpe addresses with commas.', 'lifterlms' ),
					'enabled' => 'no',
					'id' => 'custom',
					'title' => __( 'Additional Recipients', 'lifterlms' ),
				);
			break;

		}

		return $options;

	}

}

return LLMS_Notification_Controller_Purchase_Receipt::instance();
