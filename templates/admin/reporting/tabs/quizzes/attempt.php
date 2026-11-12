<?php
/**
 * Single Quiz Tab: Single Attempt Subtab
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since 3.16.0
 * @since 3.17.3 Unknown.
 * @since 5.3.0 Do not show the "Start a review" button, if there are no existing questions to review.
 * @since 7.8.0 Add information on whether the attempt can be resumed or not and disable resume attempt button.
 * @since 10.1.0 Wrapped stat tiles in a flexbox grid so they stay aligned regardless of count/height.
 * @since [version] Add the late penalty form.
 * @version [version]
 *
 * @param LLMS_Quiz_Attempt $attempt Quiz attempt object.
 */

defined( 'ABSPATH' ) || exit;
if ( ! is_admin() ) {
	exit;
}

$student  = $attempt->get_student();
$siblings = array();
if ( $student ) {
	$siblings = $student->quizzes()->get_attempts_by_quiz(
		$attempt->get( 'quiz_id' ),
		array(
			'per_page' => 10,
		)
	);
}
?>

<div class="llms-reporting-tab-content">

	<section class="llms-reporting-tab-main llms-reporting-widgets">

		<header>
			<h3><?php echo wp_kses_post( $attempt->get_title() ); ?></h3>
		</header>
		<div class="llms-reporting-widgets-grid">
		<?php

		do_action( 'llms_reporting_single_quiz_attempt_before_widgets', $attempt );

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'      => 'd-1of4',
				'icon'      => 'graduation-cap',
				'id'        => 'llms-reporting-quiz-attempt-grade',
				'data'      => $attempt->get( 'grade' ),
				'data_type' => 'percentage',
				'text'      => __( 'Grade', 'lifterlms' ),
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'      => 'd-1of4',
				'icon'      => 'check-circle',
				'id'        => 'llms-reporting-quiz-attempt-correct',
				'data'      => sprintf( '%1$d / %2$d', $attempt->get_count( 'correct_answers' ), $attempt->get_count( 'questions' ) ),
				'data_type' => 'numeric',
				'text'      => __( 'Correct answers', 'lifterlms' ),
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'      => 'd-1of4',
				'icon'      => 'percent',
				'id'        => 'llms-reporting-quiz-attempt-points',
				'data'      => sprintf( '%1$d / %2$d', $attempt->get_count( 'points' ), $attempt->get_count( 'available_points' ) ),
				'data_type' => 'numeric',
				'text'      => __( 'Points earned', 'lifterlms' ),
			)
		);

		switch ( $attempt->get( 'status' ) ) {
			case 'pass':
				$icon = 'star';
				break;
			case 'incomplete':
			case 'fail':
				$icon = 'times-circle';
				break;
			case 'pending':
				$icon = 'clock-o';
				break;
			default:
				$icon = 'question-circle';
		}

		if ( $attempt->can_be_resumed() && $attempt->is_last_attempt() ) {
			$additional = ' - <i>' . esc_html__( 'Can be resumed', 'lifterlms' ) . '</i>';
		}

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'      => 'd-1of4',
				'icon'      => $icon,
				'id'        => 'llms-reporting-quiz-attempt-status',
				'data'      => $attempt->l10n( 'status' ) . ( $additional ?? '' ),
				'data_type' => 'text',
				'text'      => __( 'Status', 'lifterlms' ),
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'      => 'd-1of3',
				'icon'      => 'sign-in',
				'id'        => 'llms-reporting-quiz-attempt-start-date',
				'data'      => $attempt->get_date( 'start' ),
				'data_type' => 'date',
				'text'      => __( 'Start Date', 'lifterlms' ),
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'      => 'd-1of3',
				'icon'      => 'sign-out',
				'id'        => 'llms-reporting-quiz-attempt-end-date',
				'data'      => ( 'incomplete' !== $attempt->get( 'status' ) ) ? $attempt->get_date( 'end' ) : '&ndash;',
				'data_type' => 'date',
				'text'      => __( 'End Date', 'lifterlms' ),
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols' => 'd-1of3',
				'icon' => 'clock-o',
				'id'   => 'llms-reporting-quiz-attempt-time',
				'data' => ( 'incomplete' !== $attempt->get( 'status' ) ) ? $attempt->get_time() : '&ndash;',
				'text' => __( 'Time Elapsed', 'lifterlms' ),
			)
		);

		do_action( 'llms_reporting_single_quiz_attempt_after_widgets', $attempt );
		?>
		</div>

		<div class="clear"></div>

		<?php
		$late_penalty = $attempt->get_late_penalty();
		if ( ( $late_penalty || $attempt->is_late() ) && current_user_can( 'edit_post', $attempt->get( 'quiz_id' ) ) ) :
			$score = $attempt->get_score();
			?>
			<form action="" method="POST" class="llms-late-penalty">
				<h3><?php esc_html_e( 'Late penalty', 'lifterlms' ); ?></h3>

				<?php if ( ! $attempt->is_late() ) : ?>
					<p class="llms-late-penalty-notice"><?php esc_html_e( 'This attempt is no longer late because its due date changed. Remove the penalty unless you still want it to apply.', 'lifterlms' ); ?></p>
				<?php endif; ?>

				<?php if ( $late_penalty && null !== $score ) : ?>
					<p class="llms-late-penalty-summary">
						<?php
						echo esc_html(
							sprintf(
								// Translators: %1$s = Score before the late penalty; %2$s = Late penalty; %3$s = Final grade; %4$s = Pass or fail status.
								__( 'Score %1$s%% − Late penalty %2$s%% = Grade %3$s%% (%4$s)', 'lifterlms' ),
								llms()->grades()->round( $score ),
								$late_penalty,
								$attempt->get( 'grade' ),
								$attempt->l10n( 'status' )
							)
						);
						?>
					</p>
				<?php endif; ?>

				<p>
					<label for="llms-late-penalty"><?php esc_html_e( 'Penalty', 'lifterlms' ); ?></label>
					<span class="llms-late-penalty-input">
						<input type="number" id="llms-late-penalty" name="llms_late_penalty" min="0" max="100" step="any" value="<?php echo esc_attr( $late_penalty ? $late_penalty : '' ); ?>" aria-describedby="llms-late-penalty-help">
						<span aria-hidden="true">%</span>
					</span>
					<span id="llms-late-penalty-help" class="description"><?php esc_html_e( 'Percentage points taken off the score. The grade is recalculated, and if that changes whether the attempt passes, the student is sent the matching quiz passed or failed notification.', 'lifterlms' ); ?></span>
				</p>

				<p>
					<label for="llms-late-penalty-note"><?php esc_html_e( 'Note to the student (optional)', 'lifterlms' ); ?></label>
					<textarea id="llms-late-penalty-note" name="llms_late_penalty_note" rows="2"><?php echo esc_textarea( (string) $attempt->get( 'late_penalty_note' ) ); ?></textarea>
				</p>

				<p class="llms-late-penalty-actions">
					<button class="llms-button-primary small" name="llms_quiz_attempt_action" type="submit" value="llms_attempt_late_penalty"><?php esc_html_e( 'Save penalty', 'lifterlms' ); ?></button>
					<?php if ( $late_penalty ) : ?>
						<button class="llms-button-secondary small" name="llms_quiz_attempt_action" type="submit" value="llms_attempt_late_penalty_remove"><?php esc_html_e( 'Remove penalty', 'lifterlms' ); ?></button>
					<?php endif; ?>
				</p>

				<input type="hidden" name="llms_attempt_id" value="<?php echo esc_attr( $attempt->get( 'id' ) ); ?>">
				<input type="hidden" name="_llms_quiz_attempt_nonce" value="<?php echo esc_attr( wp_create_nonce( 'llms_quiz_attempt_actions' ) ); ?>">
			</form>
		<?php endif; ?>

		<h3><?php esc_html_e( 'Answers', 'lifterlms' ); ?></h3>

		<form action="" method="POST">

			<?php lifterlms_template_quiz_attempt_results_questions_list( $attempt ); ?>

			<br><br><br>
			<?php if ( $attempt->get_question_objects( true, true ) ) : // Show the start review button only if there are existing questions to review. ?>
				<button class="llms-button-primary large" name="llms_quiz_attempt_action" type="submit" value="llms_attempt_grade">
					<span class="default">
						<i class="fa fa-check-square-o" aria-hidden="true"></i>
						<?php esc_html_e( 'Start a Review', 'lifterlms' ); ?>
					</span>
					<span class="save">
						<i class="fa fa-floppy-o" aria-hidden="true"></i>
						<?php esc_html_e( 'Save Review', 'lifterlms' ); ?>
					</span>
			</button>
			<?php endif; ?>
			<?php if ( $attempt->can_be_resumed() ) : // Show the clear resume attempt button only if quiz can be resumed. ?>
			<button class="llms-button-secondary large" name="llms_quiz_attempt_action" type="submit" value="llms_disable_resume_attempt">
				<i class="fa fa-ban" aria-hidden="true"></i>
				<?php esc_html_e( 'Disable Resume Attempt', 'lifterlms' ); ?>
			</button>
			<?php endif; ?>
			<button class="llms-button-danger large" name="llms_quiz_attempt_action" type="submit" value="llms_attempt_delete">
				<i class="fa fa-trash-o" aria-hidden="true"></i>
				<?php esc_html_e( 'Delete Attempt', 'lifterlms' ); ?>
			</button>

			<input type="hidden" name="llms_attempt_id" value="<?php echo esc_attr( $attempt->get( 'id' ) ); ?>">

			<?php wp_nonce_field( 'llms_quiz_attempt_actions', '_llms_quiz_attempt_nonce' ); ?>

		</form>


	</section>

	<aside class="llms-reporting-tab-side">

		<h3><i class="fa fa-history" aria-hidden="true"></i> <?php esc_html_e( 'Additional Attempts', 'lifterlms' ); ?></h3>

		<?php foreach ( $siblings as $attempt ) : ?>
			<div class="llms-reporting-event quiz_attempt">

				<a href="
				<?php
				echo esc_url(
					LLMS_Admin_Reporting::get_current_tab_url(
						array(
							'attempt_id' => $attempt->get( 'id' ),
							'quiz_id'    => $attempt->get( 'quiz_id' ),
							'stab'       => 'attempts',
						)
					)
				);
				?>
				">

					<?php echo esc_html( sprintf( 'Attempt #%1$s - %2$s', $attempt->get( 'attempt' ), $attempt->get( 'grade' ) . '%' ) ); ?>
					<br>
					<time datetime="<?php echo esc_attr( $attempt->get( 'update_date' ) ); ?>"><?php echo esc_html( llms_get_date_diff( current_time( 'timestamp' ), $attempt->get( 'update_date' ), 1 ) ); ?></time>

				</a>

			</div>
		<?php endforeach; ?>

	</aside>

</div>
