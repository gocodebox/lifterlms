<?php
/**
 * Notification View: Quiz Passed
 * @since    3.8.0
 * @version  3.10.1
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Notification_View_Quiz_Passed extends LLMS_Abstract_Notification_View {

	/**
	 * Settings for basic notifications
	 * @var  array
	 */
	protected $basic_options = array(
		/**
		 * Time in milliseconds to show a notification
		 * before automatically dismissing it
		 */
		'auto_dismiss' => 10000,
		/**
		 * Enables manual dismissal of notifications
		 */
		'dismissible' => true,
	);

	/**
	 * Notification Trigger ID
	 * @var  [type]
	 */
	public $trigger_id = 'quiz_passed';

	/**
	 * Setup body content for output
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_body() {
		if ( 'email' === $this->notification->get( 'type' ) ) {
			return sprintf( __( 'Congratulations! %1$s passed %2$s', 'lifterlms' ), '{{STUDENT_NAME}}', '{{QUIZ_TITLE}}' );
		}
		$content = sprintf( __( 'Congratulations! You passed %s!', 'lifterlms' ), '{{QUIZ_TITLE}}' );
		$content .= "\r\n\r\n{{GRADE_BAR}}";
		return $content;
	}

	/**
	 * Setup footer content for output
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_footer() {
		return '';
	}

	/**
	 * Setup notification icon for output
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_icon() {
		return $this->get_icon_default( 'positive' );
	}

	/**
	 * Setup merge codes that can be used with the notification
	 * @return   array
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_merge_codes() {
		return array(
			'{{COURSE_PROGRESS}}' => __( 'Course Progress Bar', 'lifterlms' ),
			'{{COURSE_TITLE}}' => __( 'Course Title', 'lifterlms' ),
			'{{GRADE}}' => __( 'Grade', 'lifterlms' ),
			'{{GRADE_BAR}}' => __( 'Grade Bar', 'lifterlms' ),
			'{{LESSON_TITLE}}' => __( 'Lesson Title', 'lifterlms' ),
			'{{QUIZ_TITLE}}' => __( 'Quiz Title', 'lifterlms' ),
			'{{STUDENT_NAME}}' => __( 'Student Name', 'lifterlms' ),
		);
	}

	/**
	 * Replace merge codes with actual values
	 * @param    string   $code  the merge code to ge merged data for
	 * @return   string
	 * @since    3.8.0
	 * @version  3.10.1
	 */
	protected function set_merge_data( $code ) {

		$quiz = new LLMS_Quiz( $this->notification->get( 'post_id' ) );
		$attempt = $this->user->quizzes()->get_last_completed_attempt( $this->notification->get( 'post_id' ) );
		$lesson = llms_get_post( $attempt->get( 'lesson_id' ) );
		if ( ! $lesson ) {
			return '';
		}

		switch ( $code ) {

			case '{{COURSE_TITLE}}':
				$course = $lesson->get_course();
				if ( $course ) {
					$code = $course->get( 'title' );
				} else {
					$code = '';
				}
			break;

			case '{{GRADE}}':
				$code = round( $attempt->get( 'grade' ), 2 ) . '%';
			break;

			case '{{GRADE_BAR}}':
				$code = lifterlms_course_progress_bar( $attempt->get( 'grade' ), false, false, false );
			break;

			case '{{LESSON_TITLE}}':
				$code = $lesson->get( 'title' );
			break;

			case '{{QUIZ_TITLE}}':
				$code = get_the_title( $quiz->get_id() );
			break;

			case '{{STUDENT_NAME}}':
				$code = $this->is_for_self() ? __( 'you', 'lifterlms' ) : $this->user->get_name();
			break;

		}

		return $code;

	}

	/**
	 * Setup notification subject for output
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_subject() {
		return sprintf( __( 'Congratulations! %1$s passed %2$s', 'lifterlms' ), '{{STUDENT_NAME}}', '{{QUIZ_TITLE}}' );
	}

	/**
	 * Setup notification title for output
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_title() {
		return  sprintf( __( '%s passed a quiz', 'lifterlms' ), '{{STUDENT_NAME}}' );
	}

}
