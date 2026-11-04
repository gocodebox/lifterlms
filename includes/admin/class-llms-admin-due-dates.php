<?php
/**
 * LLMS_Admin_Due_Dates class.
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin UI for quiz and assignment due dates: per-student due dates on the student course reporting screen
 * and the "late" flag on quiz attempts.
 *
 * @since [version]
 */
class LLMS_Admin_Due_Dates {

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'save' ) );
		add_action( 'llms_reporting_student_single_course_after_content', array( $this, 'output' ), 10, 2 );
		add_action( 'llms_reporting_single_quiz_attempt_before_widgets', array( $this, 'output_attempt_widget' ) );
	}

	/**
	 * Retrieve the items with due date support in a course, in syllabus order.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Course $course Course object.
	 * @return LLMS_Post_Model[]
	 */
	public static function get_items( $course ) {

		$items = array();

		foreach ( $course->get_lessons() as $lesson ) {

			$lesson_items = array();

			if ( $lesson->is_quiz_enabled() ) {
				$quiz = llms_get_post( $lesson->get( 'quiz' ) );
				if ( $quiz instanceof LLMS_Quiz ) {
					$lesson_items[] = $quiz;
				}
			}

			/**
			 * Filters the items with due dates in a lesson.
			 *
			 * Add-ons, such as Assignments, add their own items. Items must use {@see LLMS_Trait_Due_Date}.
			 *
			 * @since [version]
			 *
			 * @param LLMS_Post_Model[] $lesson_items Items.
			 * @param LLMS_Lesson       $lesson       Lesson object.
			 */
			$lesson_items = apply_filters( 'llms_lesson_due_date_items', $lesson_items, $lesson );

			foreach ( $lesson_items as $item ) {
				if ( method_exists( $item, 'get_due_timestamp' ) ) {
					$items[] = array(
						'item'   => $item,
						'lesson' => $lesson,
					);
				}
			}
		}

		return $items;
	}

	/**
	 * Save a per-student due date.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function save() {

		if ( ! isset( $_POST['_llms_due_date_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_llms_due_date_nonce'] ), 'llms-due-date-override' ) ) {
			return;
		}

		$item       = llms_get_post( llms_filter_input( INPUT_POST, 'llms_due_item_id', FILTER_SANITIZE_NUMBER_INT ) );
		$student_id = absint( llms_filter_input( INPUT_POST, 'llms_due_student_id', FILTER_SANITIZE_NUMBER_INT ) );
		$course     = $item && method_exists( $item, 'get_course' ) ? $item->get_course() : false;

		if ( ! $item || ! method_exists( $item, 'set_due_date_override' ) || ! $course || ! $student_id ) {
			return;
		}

		if ( ! current_user_can( 'edit_course', $course->get( 'id' ) ) ) {
			wp_die( esc_html__( 'You are not allowed to change due dates for this course.', 'lifterlms' ) );
		}

		$reason = llms_filter_input_sanitize_string( INPUT_POST, 'llms_due_reason' );

		if ( llms_filter_input( INPUT_POST, 'llms_due_reset' ) ) {
			$item->set_due_date_override( $student_id, '' );
		} elseif ( llms_filter_input( INPUT_POST, 'llms_due_none' ) ) {
			$item->set_due_date_override( $student_id, 'none', $reason );
		} else {
			$date = llms_filter_input_sanitize_string( INPUT_POST, 'llms_due_date' );
			$time = llms_filter_input_sanitize_string( INPUT_POST, 'llms_due_time' );
			if ( ! $date || ! strtotime( $date ) ) {
				wp_safe_redirect( add_query_arg( 'llms_due_error', 1, remove_query_arg( 'llms_due_saved', wp_get_referer() ) ) );
				exit;
			}
			$item->set_due_date_override( $student_id, $date . ' ' . ( $time ? $time : '23:59:59' ), $reason );
		}

		wp_safe_redirect( add_query_arg( 'llms_due_saved', $item->get( 'id' ), remove_query_arg( 'llms_due_error', wp_get_referer() ) ) );
		exit;
	}

	/**
	 * Output the due dates section on the student course reporting screen.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Student $student Student object.
	 * @param LLMS_Course  $course  Course object.
	 * @return void
	 */
	public function output( $student, $course ) {

		if ( ! $course instanceof LLMS_Course ) {
			return;
		}

		$items = self::get_items( $course );
		if ( ! $items ) {
			return;
		}

		$can_edit = current_user_can( 'edit_course', $course->get( 'id' ) );
		$format   = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		$saved    = absint( llms_filter_input( INPUT_GET, 'llms_due_saved', FILTER_SANITIZE_NUMBER_INT ) );
		?>
		<section class="llms-reporting-tab-main llms-due-dates-report">
			<header>
				<h3><?php esc_html_e( 'Due Dates', 'lifterlms' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Change a due date for this student only, for example for an extension. Accommodations for every course are set on the student\'s WordPress profile. Late status is always calculated against the student\'s current due date.', 'lifterlms' ); ?></p>
			</header>
			<?php if ( $saved ) : ?>
				<div class="notice notice-success inline"><p><?php esc_html_e( 'Due date updated.', 'lifterlms' ); ?></p></div>
			<?php elseif ( llms_filter_input( INPUT_GET, 'llms_due_error', FILTER_SANITIZE_NUMBER_INT ) ) : ?>
				<div class="notice notice-error inline"><p><?php esc_html_e( 'Enter a valid due date, or choose "No due date for this student".', 'lifterlms' ); ?></p></div>
			<?php endif; ?>
			<table class="llms-table zebra llms-due-dates-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Item', 'lifterlms' ); ?></th>
						<th><?php esc_html_e( 'Course due date', 'lifterlms' ); ?></th>
						<th><?php esc_html_e( 'Student due date', 'lifterlms' ); ?></th>
						<th><?php esc_html_e( 'Status', 'lifterlms' ); ?></th>
						<?php if ( $can_edit ) : ?>
							<th></th>
						<?php endif; ?>
					</tr>
				</thead>
				<tbody>
				<?php
				foreach ( $items as $row ) :
					$item      = $row['item'];
					$default   = $item->get_default_due_timestamp( $student->get_id() );
					$due       = $item->get_due_timestamp( $student->get_id() );
					$override  = $item->get_due_date_override( $student->get_id() );
					$submitted = $item->get_first_submission_time( $student->get_id() );
					$status    = $item->get_due_status( $student->get_id(), $submitted );
					$by        = $override && $override['by'] ? get_userdata( $override['by'] ) : false;
					?>
					<tr>
						<td>
							<strong><?php echo esc_html( $item->get( 'title' ) ); ?></strong><br>
							<small><?php echo esc_html( get_post_type_object( $item->get( 'type' ) )->labels->singular_name . ' · ' . $row['lesson']->get( 'title' ) ); ?></small>
						</td>
						<td><?php echo $default ? esc_html( date_i18n( $format, $default ) ) : '&ndash;'; ?></td>
						<td>
							<?php echo $due ? esc_html( date_i18n( $format, $due ) ) : esc_html__( 'No due date', 'lifterlms' ); ?>
							<?php $accommodation = ! $override && $default ? $item->get_due_date_accommodation( $student->get_id() ) : false; ?>
							<?php if ( $accommodation ) : ?>
								<span class="llms-due-badge llms-due-badge--override"><?php esc_html_e( 'Accommodation', 'lifterlms' ); ?></span>
								<br><small>
								<?php
								echo 'none' === $accommodation['type'] ?
									esc_html__( 'No due dates (student profile)', 'lifterlms' ) :
									// Translators: %d = Number of days.
									esc_html( sprintf( _n( '+%d day (student profile)', '+%d days (student profile)', $accommodation['days'], 'lifterlms' ), $accommodation['days'] ) );
								?>
								</small>
							<?php endif; ?>
							<?php if ( $override ) : ?>
								<span class="llms-due-badge llms-due-badge--override"><?php esc_html_e( 'Adjusted', 'lifterlms' ); ?></span>
								<br><small>
								<?php
								echo esc_html(
									trim(
										( $override['reason'] ? $override['reason'] . ' · ' : '' ) .
										// Translators: %1$s = user name; %2$s = date.
										sprintf( __( 'by %1$s on %2$s', 'lifterlms' ), $by ? $by->display_name : '?', date_i18n( get_option( 'date_format' ), strtotime( $override['date'] ) ) )
									)
								);
								?>
								</small>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( 'none' !== $status ) : ?>
								<span class="llms-due-badge llms-due-badge--<?php echo esc_attr( $status ); ?>"><?php echo esc_html( LLMS_Quiz::get_due_status_label( $status ) ); ?></span>
							<?php endif; ?>
							<?php if ( $submitted ) : ?>
								<br><small>
								<?php
								// Translators: %s = submission date.
								printf( esc_html__( 'Submitted %s', 'lifterlms' ), esc_html( date_i18n( $format, $submitted ) ) );
								?>
								</small>
							<?php endif; ?>
						</td>
						<?php if ( $can_edit ) : ?>
						<td class="llms-due-dates-actions">
							<details>
								<summary class="llms-button-secondary small"><?php esc_html_e( 'Change', 'lifterlms' ); ?></summary>
								<form method="post" class="llms-due-dates-form">
									<div class="llms-due-dates-when">
										<label><?php esc_html_e( 'Due date', 'lifterlms' ); ?>
											<input type="date" name="llms_due_date" value="<?php echo esc_attr( $due ? gmdate( 'Y-m-d', $due ) : '' ); ?>">
										</label>
										<label><?php esc_html_e( 'Time', 'lifterlms' ); ?>
											<input type="time" name="llms_due_time" value="<?php echo esc_attr( $due ? gmdate( 'H:i', $due ) : '23:59' ); ?>">
										</label>
									</div>
									<label><input type="checkbox" name="llms_due_none" value="1"<?php checked( $override && 'none' === $override['due'] ); ?>> <?php esc_html_e( 'No due date for this student', 'lifterlms' ); ?></label>
									<label><?php esc_html_e( 'Reason (instructors only)', 'lifterlms' ); ?>
										<input type="text" name="llms_due_reason" value="<?php echo esc_attr( $override ? $override['reason'] : '' ); ?>" placeholder="<?php esc_attr_e( 'e.g. Accommodation, illness', 'lifterlms' ); ?>">
									</label>
									<input type="hidden" name="llms_due_item_id" value="<?php echo esc_attr( $item->get( 'id' ) ); ?>">
									<input type="hidden" name="llms_due_student_id" value="<?php echo esc_attr( $student->get_id() ); ?>">
									<?php wp_nonce_field( 'llms-due-date-override', '_llms_due_date_nonce' ); ?>
									<button type="submit" class="llms-button-primary small"><?php esc_html_e( 'Save', 'lifterlms' ); ?></button>
									<?php if ( $override ) : ?>
										<button type="submit" name="llms_due_reset" value="1" class="llms-button-secondary small"><?php esc_html_e( 'Reset to course due date', 'lifterlms' ); ?></button>
									<?php endif; ?>
								</form>
							</details>
						</td>
						<?php endif; ?>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</section>
		<?php
	}

	/**
	 * Output a "late" widget on the single quiz attempt reporting screen.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Quiz_Attempt $attempt Quiz attempt.
	 * @return void
	 */
	public function output_attempt_widget( $attempt ) {

		$quiz = $attempt->get_quiz();
		$due  = $quiz ? $quiz->get_due_timestamp( $attempt->get( 'student_id' ) ) : false;
		if ( ! $due || ! $attempt->get( 'end_date' ) ) {
			return;
		}

		$late = $attempt->is_late();
		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'      => 'd-1of4',
				'icon'      => $late ? 'exclamation-triangle' : 'calendar-check-o',
				'id'        => 'llms-reporting-quiz-attempt-due',
				'data'      => $late ?
					// Translators: %s = Human readable time difference.
					sprintf( __( 'Late by %s', 'lifterlms' ), human_time_diff( $due, strtotime( $attempt->get( 'end_date' ) ) ) ) :
					__( 'On time', 'lifterlms' ),
				'data_type' => 'text',
				// Translators: %s = Due date.
				'text'      => sprintf( __( 'Due %s', 'lifterlms' ), date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $due ) ),
			)
		);
	}
}

return new LLMS_Admin_Due_Dates();
