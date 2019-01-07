<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notification Controller: Student Welcome
 * @since    3.8.0
 * @version  3.8.0
 */
class LLMS_Notification_Controller_Student_Welcome extends LLMS_Abstract_Notification_Controller {

	/**
	 * Trigger Identifier
	 * @var  [type]
	 */
	public $id = 'student_welcome';

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
		'lifterlms_user_registered',
	);

	/**
	 * Callback function called when a lesson is completed by a student
	 * @param    int     $transaction   Instance of a LLMS_Transaction
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function action_callback( $user_id = null ) {

		$this->user_id = $user_id;
		$this->post_id = null;

		$this->send();

	}

	/**
	 * Takes a subscriber type (student, author, etc) and retrieves a User ID
	 * @param    string     $subscriber  subscriber type string
	 * @return   int|false
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function get_subscriber( $subscriber ) {

		switch ( $subscriber ) {

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
		return __( 'Student Welcome', 'lifterlms' );
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
				$options[] = $this->get_subscriber_option_array( 'student', 'yes' );
				$options[] = $this->get_subscriber_option_array( 'custom', 'no' );
			break;

		}

		return $options;

	}

}

return LLMS_Notification_Controller_Student_Welcome::instance();
