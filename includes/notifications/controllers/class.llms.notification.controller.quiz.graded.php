<?php
defined( 'ABSPATH' ) || exit;

/**
 * Notification Controller: Quiz Graded
 *
 * @since    3.24.0
 * @version  3.24.0
 */
class LLMS_Notification_Controller_Quiz_Graded extends LLMS_Abstract_Notification_Controller {

	/**
	 * Trigger Identifier
	 *
	 * @var  [type]
	 */
	public $id = 'quiz_graded';

	/**
	 * Number of accepted arguments passed to the callback function
	 *
	 * @var  integer
	 */
	protected $action_accepted_args = 3;

	/**
	 * Action hooks used to trigger sending of the notification
	 *
	 * @var  array
	 */
	protected $action_hooks = array( 'llms_quiz_graded' );

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
	 * Callback function called when a quiz is failed by a student
	 *
	 * @param    int   $student_id  WP User ID of a LifterLMS Student
	 * @param    array $quiz_id     WP Post ID of a LifterLMS quiz
	 * @param    obj   $attempt     LLMS_Quiz_Attempt
	 * @return   void
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function action_callback( $student_id = null, $quiz_id = null, $attempt = null ) {

		$this->user_id = $student_id;
		$this->post_id = $attempt->get( 'id' );

		$this->send();

	}

	/**
	 * Get an array of LifterLMS Admin Page settings to send test notifications
	 *
	 * @param    string $type  notification type [basic|email]
	 * @return   array
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function get_test_settings( $type ) {

		if ( 'email' !== $type ) {
			return;
		}

		$query = new LLMS_Query_Quiz_Attempt(
			array(
				'per_page' => 25,
			)
		);

		$options = array(
			'' => '',
		);

		$attempts = array();

		if ( $query->has_results() ) {
			foreach ( $query->get_attempts() as $attempt ) {
				$quiz    = llms_get_post( $attempt->get( 'quiz_id' ) );
				$student = llms_get_student( $attempt->get( 'student_id' ) );
				if ( $attempt && $student && $quiz ) {
					$options[ $attempt->get( 'id' ) ] = esc_attr( sprintf( __( 'Attempt #%1$d for Quiz "%2$s" by %3$s', 'lifterlms' ), $attempt->get( 'id' ), $quiz->get( 'title' ), $student->get_name() ) );
				}
			}
		}

		return array(
			array(
				'class'             => 'llms-select2',
				'custom_attributes' => array(
					'data-allow-clear' => true,
					'data-placeholder' => __( 'Select a passed quiz', 'lifterlms' ),
				),
				'default'           => '',
				'id'                => 'attempt_id',
				'desc'              => '<br/>' . __( 'Send yourself a test notification using information from the selected quiz.', 'lifterlms' ),
				'options'           => $options,
				'title'             => __( 'Send a Test', 'lifterlms' ),
				'type'              => 'select',
			),
		);
	}


	/**
	 * Takes a subscriber type (student, author, etc) and retrieves a User ID
	 *
	 * @param    string $subscriber  subscriber type string
	 * @return   int|false
	 * @since    3.24.0
	 * @version  3.24.0
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
	 * Get the translatable title for the notification
	 * used on settings screens
	 *
	 * @return   string
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function get_title() {
		return __( 'Quizzes: Quiz Graded', 'lifterlms' );
	}

	/**
	 * Send a test notification to the currently logged in users
	 * Extending classes should redefine this in order to properly setup the controller with post_id and user_id data
	 *
	 * @param    string $type  notification type [basic|email]
	 * @param    array  $data  array of test notification data as specified by $this->get_test_data()
	 * @return   int|false
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function send_test( $type, $data = array() ) {

		if ( empty( $data['attempt_id'] ) ) {
			return;
		}

		$attempt       = new LLMS_Quiz_Attempt( $data['attempt_id'] );
		$this->user_id = $attempt->get( 'student_id' );
		$this->post_id = $attempt->get( 'id' );
		return parent::send_test( $type );

	}

	/**
	 * Setup the subscriber options for the notification
	 *
	 * @param    string $type  notification type id
	 * @return   array
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	protected function set_subscriber_options( $type ) {

		$options = array();

		switch ( $type ) {

			case 'basic':
				$options[] = $this->get_subscriber_option_array( 'student', 'yes' );
				break;

			case 'email':
				$options[] = $this->get_subscriber_option_array( 'student', 'yes' );
				$options[] = $this->get_subscriber_option_array( 'custom', 'no' );
				break;

		}

		return $options;

	}

}

return LLMS_Notification_Controller_Quiz_Graded::instance();
