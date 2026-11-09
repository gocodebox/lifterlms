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
			foreach ( $lesson->get_due_date_items() as $item ) {
				$items[] = array(
					'item'   => $item,
					'lesson' => $lesson,
				);
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
				wp_safe_redirect( add_query_arg( 'llms_due_error', $item->get( 'id' ), remove_query_arg( 'llms_due_saved', wp_get_referer() ) ) . '#llms-due-dates-notice' );
				exit;
			}
			$item->set_due_date_override( $student_id, $date . ' ' . ( $time ? $time : '23:59:59' ), $reason );
		}

		wp_safe_redirect( add_query_arg( 'llms_due_saved', $item->get( 'id' ), remove_query_arg( 'llms_due_error', wp_get_referer() ) ) . '#llms-due-dates-notice' );
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
		$saved    = llms_get_post( absint( llms_filter_input( INPUT_GET, 'llms_due_saved', FILTER_SANITIZE_NUMBER_INT ) ) );
		$error    = absint( llms_filter_input( INPUT_GET, 'llms_due_error', FILTER_SANITIZE_NUMBER_INT ) );

		if ( $can_edit ) {
			wp_add_inline_script( 'llms-admin-scripts', self::get_inline_script() );
		}
		?>
		<section class="llms-reporting-tab-main llms-due-dates-report">
			<header>
				<h3><?php esc_html_e( 'Due Dates', 'lifterlms' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Change a due date for this student only, for example for an extension. Accommodations for every course are set on the student\'s WordPress profile. Late status is always calculated against the student\'s current due date.', 'lifterlms' ); ?></p>
			</header>
			<?php if ( $saved ) : ?>
				<div id="llms-due-dates-notice" class="notice notice-success inline" tabindex="-1">
					<?php // Translators: %s = Quiz or assignment title. ?>
					<p><?php echo esc_html( sprintf( __( 'Due date updated for %s.', 'lifterlms' ), $saved->get( 'title' ) ) ); ?></p>
				</div>
			<?php elseif ( $error ) : ?>
				<div id="llms-due-dates-notice" class="notice notice-error inline" tabindex="-1"><p><?php esc_html_e( 'Enter a valid due date, or choose "No due date for this student".', 'lifterlms' ); ?></p></div>
			<?php endif; ?>
			<table class="llms-table zebra llms-due-dates-table">
				<thead>
					<tr>
						<th><?php /* Translators: Table column heading for a quiz or assignment. */ esc_html_e( 'Item', 'lifterlms' ); ?></th>
						<th><?php esc_html_e( 'Course due date', 'lifterlms' ); ?></th>
						<th><?php esc_html_e( 'Student due date', 'lifterlms' ); ?></th>
						<th><?php esc_html_e( 'Status', 'lifterlms' ); ?></th>
						<?php if ( $can_edit ) : ?>
							<th><span class="screen-reader-text"><?php esc_html_e( 'Actions', 'lifterlms' ); ?></span></th>
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
						<td data-label="<?php /* Translators: Table column heading for a quiz or assignment. */ esc_attr_e( 'Item', 'lifterlms' ); ?>">
							<strong><?php echo esc_html( $item->get( 'title' ) ); ?></strong><br>
							<small><?php echo esc_html( get_post_type_object( $item->get( 'type' ) )->labels->singular_name . ' · ' . $row['lesson']->get( 'title' ) ); ?></small>
						</td>
						<td data-label="<?php esc_attr_e( 'Course due date', 'lifterlms' ); ?>"><?php echo $default ? esc_html( date_i18n( $format, $default ) ) : '&ndash;'; ?></td>
						<td data-label="<?php esc_attr_e( 'Student due date', 'lifterlms' ); ?>">
							<?php echo $due ? esc_html( date_i18n( $format, $due ) ) : esc_html__( 'No due date', 'lifterlms' ); ?>
							<?php $accommodation = ! $override && $default ? $item->get_due_date_accommodation( $student->get_id() ) : false; ?>
							<?php if ( $accommodation ) : ?>
								<span class="llms-due-badge llms-due-badge--override"><?php /* Translators: Badge on a due date that was extended or removed by an accommodation on the student's profile. */ esc_html_e( 'Accommodation', 'lifterlms' ); ?></span>
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
								<span class="llms-due-badge llms-due-badge--override"><?php /* Translators: Badge on a due date that an instructor changed for this student only. */ esc_html_e( 'Adjusted', 'lifterlms' ); ?></span>
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
						<td data-label="<?php esc_attr_e( 'Status', 'lifterlms' ); ?>">
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
							<?php
							$form_id = 'llms-due-dates-form-' . $item->get( 'id' );
							$is_open = (int) $item->get( 'id' ) === $error;
							?>
							<?php // Translators: %s = Quiz or assignment title. ?>
							<button type="button" class="llms-button-secondary small llms-due-dates-toggle" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $form_id ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Change due date for %s', 'lifterlms' ), $item->get( 'title' ) ) ); ?>">
								<?php // Translators: Button that opens the form to change a student's due date. ?>
								<?php esc_html_e( 'Change', 'lifterlms' ); ?>
							</button>
							<form method="post" class="llms-due-dates-form" id="<?php echo esc_attr( $form_id ); ?>"<?php echo $is_open ? '' : ' hidden'; ?>>
								<div class="llms-due-dates-when">
									<label><?php esc_html_e( 'Due date', 'lifterlms' ); ?>
										<input type="date" name="llms_due_date" value="<?php echo esc_attr( $due ? gmdate( 'Y-m-d', $due ) : '' ); ?>">
									</label>
									<label><?php /* Translators: Label for the time of day a quiz or assignment is due. */ esc_html_e( 'Time', 'lifterlms' ); ?>
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
	 * Retrieve the script for the due date "Change" buttons.
	 *
	 * Each button shows and hides its form and moves focus into it; Escape closes the form and returns focus
	 * to the button. "No due date for this student" disables the date and time fields. After a save, focus
	 * moves to the result notice so screen readers announce it.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected static function get_inline_script() {
		return <<<'JS'
document.querySelectorAll( '.llms-due-dates-toggle' ).forEach( function( button ) {
	var form = document.getElementById( button.getAttribute( 'aria-controls' ) ),
		none = form.querySelector( '[name="llms_due_none"]' );
	function toggle( open ) {
		button.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		form.hidden = ! open;
		if ( open ) {
			form.querySelector( 'input:not([type="hidden"]):not(:disabled)' ).focus();
		}
	}
	function syncNone() {
		form.querySelectorAll( '[name="llms_due_date"], [name="llms_due_time"]' ).forEach( function( input ) {
			input.disabled = none.checked;
		} );
	}
	button.addEventListener( 'click', function() {
		toggle( form.hidden );
	} );
	form.addEventListener( 'keydown', function( event ) {
		if ( 'Escape' === event.key ) {
			toggle( false );
			button.focus();
		}
	} );
	none.addEventListener( 'change', syncNone );
	syncNone();
} );
var notice = document.getElementById( 'llms-due-dates-notice' );
if ( notice ) {
	notice.focus();
}
JS;
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
					// Translators: The quiz attempt was submitted by its due date.
					__( 'On time', 'lifterlms' ),
				'data_type' => 'text',
				// Translators: %s = Due date.
				'text'      => sprintf( __( 'Due %s', 'lifterlms' ), date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $due ) ),
			)
		);
	}
}

return new LLMS_Admin_Due_Dates();
