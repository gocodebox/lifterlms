<?php
/**
 * List of attempt questions/answers for a single attempt
 *
 * @param LLMS_Quiz_Attempt $attempt LLMS_Quiz_Attempt instance.
 *
 * @since 3.17.8 Unknown.
 * @since 5.3.0 Display removed questions too.
 * @since 7.3.0 Script moved into the main llms.js.
 * @since 7.8.0 Hide answers if resumable attempt is incomplete.
 * @version 7.3.0
 *
 * @since 3.16.0
 */

defined( 'ABSPATH' ) || exit;

?>

<ol class="llms-quiz-attempt-results">
<?php if ( ! $attempt->can_be_resumed() ) : ?>
	<?php
	foreach ( $attempt->get_question_objects() as $attempt_question ) :
		$quiz_question = $attempt_question->get_question();
		if ( ! $quiz_question ) { // Question missing/deleted.
			?>
		<li class="llms-quiz-attempt-question type--<?php echo esc_attr( $quiz_question->get( 'question_type' ) ); ?> status--<?php echo esc_attr( $attempt_question->get_status() ); ?> <?php echo $attempt_question->is_correct() ? 'correct' : 'incorrect'; ?>"
			data-question-id="<?php echo esc_attr( $quiz_question->get( 'id' ) ); ?>"
			data-grading-manual="<?php echo $attempt_question->can_be_manually_graded() ? 'yes' : 'no'; ?>"
			data-points="<?php echo esc_attr( $attempt_question->get( 'points' ) ); ?>"
			data-points-curr="<?php echo esc_attr( $attempt_question->get( 'earned' ) ); ?>">
			<header class="llms-quiz-attempt-question-header">
				<span class="toggle-answer">
					<h3 class="llms-question-title"><?php esc_html_e( 'This question has been deleted', 'lifterlms' ); ?></h3>
					<span class="llms-points">
						<?php if ( $quiz_question->get( 'points' ) ) : ?>
							<?php echo esc_html( sprintf( __( '%1$d / %2$d points', 'lifterlms' ), $attempt_question->get( 'earned' ), $attempt_question->get( 'points' ) ) ); ?>
						<?php endif; ?>
					</span>
					<?php echo wp_kses_post( $attempt_question->get_status_icon() ); ?>
				</span>
			</header>
		</li>
			<?php
			continue;
		}
		?>

	<li class="llms-quiz-attempt-question type--<?php echo esc_attr( $quiz_question->get( 'question_type' ) ); ?> status--<?php echo esc_attr( $attempt_question->get_status() ); ?> <?php echo $attempt_question->is_correct() ? 'correct' : 'incorrect'; ?>"
		data-question-id="<?php echo esc_attr( $quiz_question->get( 'id' ) ); ?>"
		data-grading-manual="<?php echo $attempt_question->can_be_manually_graded() ? 'yes' : 'no'; ?>"
		data-points="<?php echo esc_attr( $attempt_question->get( 'points' ) ); ?>"
		data-points-curr="<?php echo esc_attr( $attempt_question->get( 'earned' ) ); ?>">
		<header class="llms-quiz-attempt-question-header">
			<a class="toggle-answer" href="#">

				<h3 class="llms-question-title"><?php echo wp_kses_post( $quiz_question->get_question( 'plain' ) ); ?></h3>

				<?php if ( $quiz_question->get( 'points' ) ) : ?>
					<span class="llms-points">
						<?php echo esc_html( sprintf( __( '%1$d / %2$d points', 'lifterlms' ), $attempt_question->get( 'earned' ), $attempt_question->get( 'points' ) ) ); ?>
					</span>
				<?php endif; ?>

				<?php echo wp_kses_post( $attempt_question->get_status_icon() ); ?>

			</a>
		</header>

		<section class="llms-quiz-attempt-question-main">

			<?php if ( apply_filters( 'llms_quiz_show_question_description', true, $attempt, $attempt_question, $quiz_question ) && $quiz_question->has_description() ) : ?>
				<div class="llms-quiz-attempt-answer-section llms-question-description">
					<?php echo wp_kses_post( $quiz_question->get_description() ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $attempt_question->get( 'answer' ) ) : ?>
				<div class="llms-quiz-attempt-answer-section llms-student-answer">
					<p class="llms-quiz-results-label student-answer"><?php esc_html_e( 'Selected answer: ', 'lifterlms' ); ?></p>
					<?php echo wp_kses_post( $attempt_question->get_answer() ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! $attempt_question->is_correct() ) : ?>
				<?php if ( llms_parse_bool( $quiz_question->get_quiz()->get( 'show_correct_answer' ) ) ) : ?>
					<?php if ( in_array( $quiz_question->get_auto_grade_type(), array( 'choices', 'conditional' ) ) ) : ?>
						<div class="llms-quiz-attempt-answer-section llms-correct-answer">
							<p class="llms-quiz-results-label correct-answer"><?php esc_html_e( 'Correct answer: ', 'lifterlms' ); ?></p>
							<?php echo wp_kses_post( $attempt_question->get_correct_answer() ); ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( llms_parse_bool( $quiz_question->get( 'clarifications_enabled' ) ) ) : ?>
					<div class="llms-quiz-attempt-answer-section llms-clarifications">
						<p class="llms-quiz-results-label clarification"><?php esc_html_e( 'Clarification: ', 'lifterlms' ); ?></p>
						<?php echo wp_kses_post( $quiz_question->get( 'clarifications' ) ); ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>


			<?php if ( $attempt_question->has_remarks() ) : ?>
				<div class="llms-quiz-attempt-answer-section llms-remarks">
					<p class="llms-quiz-results-label remarks"><?php esc_html_e( 'Instructor remarks: ', 'lifterlms' ); ?></p>
					<div class="llms-remarks"><?php echo wp_kses_post( wpautop( $attempt_question->get( 'remarks' ) ) ); ?></div>
				</div>
			<?php endif; ?>

		</section>

	</li>
	<?php endforeach; ?>
<?php else : ?>
	<?php if ( is_admin() ) : ?>
		<p><?php esc_html_e( 'The quiz is still ongoing.', 'lifterlms' ); ?></p>
	<?php else : ?>
		<p><?php esc_html_e( "The quiz is still ongoing. You can resume your attempt by clicking the 'Resume Quiz' button.", 'lifterlms' ); ?></p>
	<?php endif; ?>
<?php endif; ?>
</ol>
