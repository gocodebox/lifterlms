<?php
defined( 'ABSPATH' ) || exit;

/**
 * Shared Notification View for quiz completions
 * @since    3.24.0
 * @version  3.24.0
 */
class LLMS_Notification_View_Quiz_Graded extends LLMS_Abstract_Notification_View {

	/**
	 * Notification Trigger ID
	 * @var  string
	 */
	public $trigger_id = 'quiz_graded';

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
	 * Setup body content for output
	 * @return   string
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	protected function set_body() {
		if ( 'email' === $this->notification->get( 'type' ) ) {
			return $this->set_body_email();
		}
		// Translators: %s = Quiz attempt grade.
		$content = sprintf( __( 'You received a %1$s', 'lifterlms' ), '{{GRADE}}' );
		return $content;
	}

	/**
	 * Setup body for email notification
	 * @return  string
	 * @since   3.24.0
	 * @version 3.24.0
	 */
	protected function set_body_email() {

		$mailer = LLMS()->mailer();

		$btn_style = $mailer->get_button_style();

		$table_style = sprintf(
			'border-collapse:collapse;color:%1$s;font-family:%2$s;font-size:%3$s;Margin-bottom:15px;text-align:left;width:100%%;',
			$mailer->get_css( 'font-color', false ),
			$mailer->get_css( 'font-family', false ),
			$mailer->get_css( 'font-size', false )
		);
		$tr_style    = 'color:inherit;font-family:inherit;font-size:inherit;';
		$td_style    = sprintf( 'border-bottom:1px solid %s;color:inherit;font-family:inherit;font-size:inherit;padding:10px;', $mailer->get_css( 'divider-color', false ) );

		$rows = array(
			'QUIZ_TITLE'   => __( 'Quiz', 'lifterlms' ),
			'LESSON_TITLE' => __( 'Lesson', 'lifterlms' ),
			'COURSE_TITLE' => __( 'Course', 'lifterlms' ),
			'GRADE'        => __( 'Grade', 'lifterlms' ),
			'STATUS'       => __( 'Status', 'lifterlms' ),
		);

		ob_start();
		?><table style="<?php echo $table_style; ?>">
		<?php foreach ( $rows as $code => $name ) : ?>
			<tr style="<?php echo $tr_style; ?>">
				<th style="<?php echo $td_style; ?>width:33.3333%;"><?php echo $name; ?></th>
				<td style="<?php echo $td_style; ?>">{{<?php echo $code; ?>}}</td>
			</tr>
		<?php endforeach; ?>
		</table>
		<p><a href="{{REVIEW_URL}}" style="<?php echo $btn_style; ?>"><?php _e( 'View the whole attempt', 'lifterlms' ); ?></a></p>
		<p><small><?php _e( 'Trouble clicking? Copy and paste this URL into your browser:', 'lifterlms' ); ?><br><a href="{{REVIEW_URL}}">{{REVIEW_URL}}</a></small></p>
		<?php
		return ob_get_clean();

	}

	/**
	 * Setup notification icon for output
	 * @return   string
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	protected function set_icon() {
		return $this->get_icon_default( 'warning' );
	}

	/**
	 * Setup footer content for output
	 * @return   string
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	protected function set_footer() {

		$attempt = new LLMS_Quiz_Attempt( $this->notification->get( 'post_id' ) );
		if ( ! $attempt->exists() ) {
			return '';
		}

		return '<a href="' . esc_url( $attempt->get_permalink() ) . '">' . __( 'View the attempt', 'lifterlms' ) . '</a>';
	}

	/**
	 * Setup merge codes that can be used with the notification
	 * @return   array
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	protected function set_merge_codes() {
		return array(
			'{{COURSE_TITLE}}' => __( 'Course Title', 'lifterlms' ),
			'{{GRADE}}' => __( 'Grade', 'lifterlms' ),
			'{{LESSON_TITLE}}' => __( 'Lesson Title', 'lifterlms' ),
			'{{QUIZ_TITLE}}' => __( 'Quiz Title', 'lifterlms' ),
			'{{REVIEW_URL}}' => __( 'Review URL', 'lifterlms' ),
			'{{STATUS}}' => __( 'Quiz Status', 'lifterlms' ),
			'{{STUDENT_NAME}}' => __( 'Student Name', 'lifterlms' ),
		);
	}

	/**
	 * Replace merge codes with actual values
	 * @param    string   $code  the merge code to ge merged data for
	 * @return   string
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	protected function set_merge_data( $code ) {

		$attempt = new LLMS_Quiz_Attempt( $this->notification->get( 'post_id' ) );
		if ( ! $attempt->exists() ) {
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
				$code = LLMS()->grades()->round( $attempt->get( 'grade' ) ) . '%';
			break;

			case '{{LESSON_TITLE}}':
				$code = $lesson->get( 'title' );
			break;

			case '{{QUIZ_TITLE}}':
				$code = get_the_title( $attempt->get( 'quiz_id' ) );
			break;

			case '{{REVIEW_URL}}':
				$code = $attempt->get_permalink();
			break;

			case '{{STATUS}}':
				$code = $attempt->l10n( 'status' );
			break;

			case '{{STUDENT_NAME}}':
				$code = $this->user->get_name();
			break;

		}// End switch().

		return $code;

	}

	/**
	 * Setup notification subject for output
	 * @return   string
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	protected function set_subject() {
		// Translators %s = Quiz Title.
		return sprintf( __( 'Your quiz "%s" has been reviewed', 'lifterlms' ), '{{QUIZ_TITLE}}' );
	}

	/**
	 * Setup notification title for output
	 * @return   string
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	protected function set_title() {
		return __( 'Quiz Review Details', 'lifterlms' );
	}

}
