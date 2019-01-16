<?php
/**
 * Single Quiz Tab: Single Attempt Subtab
 * @since    3.16.0
 * @version  3.17.3
 * @arg  obj  $attempt  instance of the LLMS_Quiz_Attempt]
 */
if ( ! defined( 'ABSPATH' ) || ! is_admin() ) {
	exit;
}

$student = $attempt->get_student();
$siblings = array();
if ( $student ) {
	$siblings = $student->quizzes()->get_attempts_by_quiz( $attempt->get( 'quiz_id' ), array(
		'per_page' => 10,
	) );
}
?>

<div class="llms-reporting-tab-content">

	<section class="llms-reporting-tab-main llms-reporting-widgets">

		<header>
			<h3><?php echo $attempt->get_title(); ?></h3>
		</header><?php

		do_action( 'llms_reporting_single_quiz_attempt_before_widgets', $attempt );

		LLMS_Admin_Reporting::output_widget( array(
			'cols' => 'd-1of4',
			'icon' => 'graduation-cap',
			'id' => 'llms-reporting-quiz-attempt-grade',
			'data' => $attempt->get( 'grade' ),
			'data_type' => 'percentage',
			'text' => __( 'Grade', 'lifterlms' ),
		) );

		LLMS_Admin_Reporting::output_widget( array(
			'cols' => 'd-1of4',
			'icon' => 'check-circle',
			'id' => 'llms-reporting-quiz-attempt-correct',
			'data' => sprintf( '%1$d / %2$d', $attempt->get_count( 'correct_answers' ), $attempt->get_count( 'questions' ) ),
			'data_type' => 'numeric',
			'text' => __( 'Correct answers', 'lifterlms' ),
		) );

		LLMS_Admin_Reporting::output_widget( array(
			'cols' => 'd-1of4',
			'icon' => 'percent',
			'id' => 'llms-reporting-quiz-attempt-points',
			'data' => sprintf( '%1$d / %2$d', $attempt->get_count( 'points' ), $attempt->get_count( 'available_points' ) ),
			'data_type' => 'numeric',
			'text' => __( 'Points earned', 'lifterlms' ),
		) );

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

		LLMS_Admin_Reporting::output_widget( array(
			'cols' => 'd-1of4',
			'icon' => $icon,
			'id' => 'llms-reporting-quiz-attempt-status',
			'data' => $attempt->l10n( 'status' ),
			'data_type' => 'text',
			'text' => __( 'Status', 'lifterlms' ),
		) );

		LLMS_Admin_Reporting::output_widget( array(
			'cols' => 'd-1of3',
			'icon' => 'sign-in',
			'id' => 'llms-reporting-quiz-attempt-start-date',
			'data' => $attempt->get_date( 'start' ),
			'data_type' => 'date',
			'text' => __( 'Start Date', 'lifterlms' ),
		) );

		LLMS_Admin_Reporting::output_widget( array(
			'cols' => 'd-1of3',
			'icon' => 'sign-out',
			'id' => 'llms-reporting-quiz-attempt-end-date',
			'data' => ( 'incomplete' !== $attempt->get( 'status' ) ) ? $attempt->get_date( 'end' ) : '&ndash;',
			'data_type' => 'date',
			'text' => __( 'End Date', 'lifterlms' ),
		) );

		LLMS_Admin_Reporting::output_widget( array(
			'cols' => 'd-1of3',
			'icon' => 'clock-o',
			'id' => 'llms-reporting-quiz-attempt-time',
			'data' => ( 'incomplete' !== $attempt->get( 'status' ) ) ? $attempt->get_time() : '&ndash;',
			'text' => __( 'Time Elapsed', 'lifterlms' ),
		) );

		do_action( 'llms_reporting_single_quiz_attempt_after_widgets', $attempt ); ?>

		<div class="clear"></div>

		<h3><?php _e( 'Answers', 'lifterlms' ); ?></h3>

		<form action="" method="POST">

			<?php lifterlms_template_quiz_attempt_results_questions_list( $attempt ); ?>

			<br><br><br>

			<button class="llms-button-primary large" name="llms_quiz_attempt_action" type="submit" value="llms_attempt_grade">
				<span class="default">
					<i class="fa fa-check-square-o" aria-hidden="true"></i>
					<?php _e( 'Start a Review', 'lifterlms' ); ?>
				</span>
				<span class="save">
					<i class="fa fa-floppy-o" aria-hidden="true"></i>
					<?php _e( 'Save Review', 'lifterlms' ); ?>
				</span>
			</button>

			<button class="llms-button-danger large" name="llms_quiz_attempt_action" type="submit" value="llms_attempt_delete">
				<i class="fa fa-trash-o" aria-hidden="true"></i>
				<?php _e( 'Delete Attempt', 'lifterlms' ); ?>
			</button>

			<input type="hidden" name="llms_attempt_id" value="<?php echo $attempt->get( 'id' ); ?>">

			<?php wp_nonce_field( 'llms_quiz_attempt_actions', '_llms_quiz_attempt_nonce' ); ?>

		</form>


	</section>

	<aside class="llms-reporting-tab-side">

		<h3><i class="fa fa-history" aria-hidden="true"></i> <?php _e( 'Additional Attempts', 'lifterlms' ); ?></h3>

		<?php foreach ( $siblings as $attempt ) : ?>
			<div class="llms-reporting-event quiz_attempt">

				<a href="<?php echo esc_url( LLMS_Admin_Reporting::get_current_tab_url( array(
						'attempt_id' => $attempt->get( 'id' ),
						'quiz_id' => $attempt->get( 'quiz_id' ),
						'stab' => 'attempts',
				) ) ); ?>">

					<?php printf( 'Attempt #%1$s - %2$s', $attempt->get( 'attempt' ), $attempt->get( 'grade' ) . '%' ); ?>
					<br>
					<time datetime="<?php echo $attempt->get( 'update_date' ); ?>"><?php echo llms_get_date_diff( current_time( 'timestamp' ), $attempt->get( 'update_date' ), 1 ); ?></time>

				</a>

			</div>
		<?php endforeach; ?>

	</aside>

</div>
