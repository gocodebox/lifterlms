<?php
/**
 * Notification Controller: Student Welcome
 *
 * @since 3.8.0
 * @version 3.33.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification Controller: Student Welcome
 *
 * @since 3.8.0
 * @since 3.33.2 Add test send functionality.
 */
class LLMS_Notification_Controller_Student_Welcome extends LLMS_Abstract_Notification_Controller {

	/**
	 * Trigger Identifier
	 *
	 * @var  [type]
	 */
	public $id = 'student_welcome';

	/**
	 * Number of accepted arguments passed to the callback function
	 *
	 * @var  integer
	 */
	protected $action_accepted_args = 1;

	/**
	 * Action hooks used to trigger sending of the notification
	 *
	 * @var  array
	 */
	protected $action_hooks = array(
		'lifterlms_user_registered',
	);

	/**
	 * Determines if test notifications can be sent
	 *
	 * @var  bool
	 */
	protected $testable = array(
		'basic' => false,
		'email' => true,
	);

	/**
	 * Callback function called when a lesson is completed by a student
	 *
	 * @param    int $transaction   Instance of a LLMS_Transaction
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
	 *
	 * @param    string $subscriber  subscriber type string
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
	 *
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
	 *
	 * @since 3.33.2
	 *
	 * @param string $type Notification type [basic|email]
	 * @return array
	 */
	public function get_test_settings( $type ) {

		$query = new WP_User_Query(
			array(
				'number' => 25,
			)
		);

		$options = array(
			'' => '',
		);
		foreach ( $query->get_results() as $user ) {
			$student = llms_get_student( $user );
			if ( $student ) {
				$options[ $student->get_id() ] = esc_attr( sprintf( __( '%1$s <%2$s>', 'lifterlms' ), $student->get_name(), $student->get( 'user_email' ) ) );
			}
		}

		return array(
			array(
				'class'             => 'llms-select2',
				'custom_attributes' => array(
					'data-allow-clear' => true,
					'data-placeholder' => __( 'Select a user', 'lifterlms' ),
				),
				'default'           => '',
				'id'                => 'user_id',
				'desc'              => '<br/>' . __( 'Send yourself a test notification using information for the selected user.', 'lifterlms' ),
				'options'           => $options,
				'title'             => __( 'Send a Test', 'lifterlms' ),
				'type'              => 'select',
			),
		);

	}

	/**
	 * Get the translatable title for the notification
	 * used on settings screens
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_title() {
		return __( 'Student Welcome', 'lifterlms' );
	}

	/**
	 * Send a test notification to the currently logged in users
	 * Extending classes should redefine this in order to properly setup the controller with post_id and user_id data
	 *
	 * @since 3.33.2
	 *
	 * @param string $type Notification type [basic|email].
	 * @param array  $data Array of test notification data as specified by $this->get_test_data().
	 *
	 * @return int|false
	 */
	public function send_test( $type, $data = array() ) {

		if ( empty( $data['user_id'] ) ) {
			return;
		}

		$this->user_id = $data['user_id'];
		$this->post_id = null;

		return parent::send_test( $type );

	}

	/**
	 * Setup the subscriber options for the notification
	 *
	 * @param    string $type  notification type id
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
