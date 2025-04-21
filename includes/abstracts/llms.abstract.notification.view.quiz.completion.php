<?php
/**
 * Shared Notification View for quiz completions abstract
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 3.24.0
 * @version 7.8.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shared Notification View for quiz completions abstract class
 *
 * @since 3.24.0
 * @since 4.0.0 Remove usage of deprecated class `LLMS_Quiz_Legacy`.
 */
abstract class LLMS_Abstract_Notification_View_Quiz_Completion extends LLMS_Abstract_Notification_View {

	/**
	 * Settings for basic notifications
	 *
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
		'dismissible'  => true,
	);

	/**
	 * Setup body for email notification
	 *
	 * @since 3.24.0
	 * @since 5.2.0 Build the table with mailer helper.
	 *
	 * @return string
	 */
	protected function set_body_email() {

		$mailer = llms()->mailer();

		$btn_style = $mailer->get_button_style();

		$rows = array(
			'STUDENT_NAME' => __( 'Student', 'lifterlms' ),
			'QUIZ_TITLE'   => __( 'Quiz', 'lifterlms' ),
			'LESSON_TITLE' => __( 'Lesson', 'lifterlms' ),
			'COURSE_TITLE' => __( 'Course', 'lifterlms' ),
			'GRADE'        => __( 'Grade', 'lifterlms' ),
			'STATUS'       => __( 'Status', 'lifterlms' ),
		);

		ob_start();
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped by the mailer.
		echo $mailer->get_table_html( $rows );
		?>
		<p><a href="{{REVIEW_URL}}" style="<?php echo esc_attr( $btn_style ); ?>"><?php esc_html_e( 'View the quiz attempt and leave remarks', 'lifterlms' ); ?></a></p>
		<p><small><?php esc_html_e( 'Trouble clicking? Copy and paste this URL into your browser:', 'lifterlms' ); ?><br><a href="{{REVIEW_URL}}">{{REVIEW_URL}}</a></small></p>
		<?php
		return ob_get_clean();
	}

	/**
	 * Setup footer content for output
	 *
	 * @since 3.24.0
	 *
	 * @return string
	 */
	protected function set_footer() {
		return '';
	}

	/**
	 * Setup merge codes that can be used with the notification
	 *
	 * @since 3.24.0
	 *
	 * @return array
	 */
	protected function set_merge_codes() {
		return array(
			'{{COURSE_PROGRESS}}' => __( 'Course Progress Bar', 'lifterlms' ),
			'{{COURSE_TITLE}}'    => __( 'Course Title', 'lifterlms' ),
			'{{GRADE}}'           => __( 'Grade', 'lifterlms' ),
			'{{GRADE_BAR}}'       => __( 'Grade Bar', 'lifterlms' ),
			'{{LESSON_TITLE}}'    => __( 'Lesson Title', 'lifterlms' ),
			'{{QUIZ_TITLE}}'      => __( 'Quiz Title', 'lifterlms' ),
			'{{REVIEW_URL}}'      => __( 'Review URL', 'lifterlms' ),
			'{{STATUS}}'          => __( 'Quiz Status', 'lifterlms' ),
			'{{STUDENT_NAME}}'    => __( 'Student Name', 'lifterlms' ),
		);
	}

	/**
	 * Replace merge codes with actual values.
	 *
	 * @since 3.24.0
	 * @since 4.0.0 Remove usage of deprecated class `LLMS_Quiz_Legacy`.
	 * @since 7.8.0 Don't try to round nulls.
	 *
	 * @param string $code The merge code to get merged data for.
	 * @return string
	 */
	protected function set_merge_data( $code ) {

		$quiz_id = $this->notification->get( 'post_id' );
		$attempt = $this->user->quizzes()->get_last_completed_attempt( $quiz_id );
		if ( ! $attempt ) {
			return '';
		}
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
				$code = round( $attempt->get( 'grade' ) ?? 0, 2 ) . '%';
				break;

			case '{{GRADE_BAR}}':
				$code = lifterlms_course_progress_bar( $attempt->get( 'grade' ), false, false, false );
				break;

			case '{{LESSON_TITLE}}':
				$code = $lesson->get( 'title' );
				break;

			case '{{QUIZ_TITLE}}':
				$code = get_the_title( $quiz_id );
				break;

			case '{{REVIEW_URL}}':
				$code = add_query_arg(
					array(
						'tab'        => 'quizzes',
						'stab'       => 'attempts',
						'quiz_id'    => $attempt->get( 'quiz_id' ),
						'attempt_id' => $attempt->get( 'id' ),
					),
					admin_url( 'admin.php?page=llms-reporting' )
				);
				break;

			case '{{STATUS}}':
				$code = $attempt->l10n( 'status' );
				break;

			case '{{STUDENT_NAME}}':
				$code = $this->is_for_self() ? __( 'you', 'lifterlms' ) : $this->user->get_name();
				break;

		}

		return $code;
	}
}
