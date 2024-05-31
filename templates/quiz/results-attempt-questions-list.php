<?php
/**
 * List of attempt questions/answers for a single attempt
 *
 * @since 3.16.0
 * @since 3.17.8 Unknown.
 * @since 5.3.0 Display removed questions too.
 * @since 7.3.0 Script moved into the main llms.js.
 * @version 7.3.0
 *
 * @param LLMS_Quiz_Attempt $attempt LLMS_Quiz_Attempt instance.
 */

defined( 'ABSPATH' ) || exit;

?>

<ol class="llms-quiz-attempt-results">
<?php
foreach ( $attempt->get_question_objects() as $attempt_question ) :
	$quiz_question = $attempt_question->get_question();
	if ( ! $quiz_question ) { // Question missing/deleted.
		?>
		<li class="llms-quiz-attempt-question type--removed status--<?php echo $attempt_question->get_status(); ?> <?php echo $attempt_question->is_correct() ? 'correct' : 'incorrect'; ?>">
			<header class="llms-quiz-attempt-question-header">
				<span class="toggle-answer">
					<h3 class="llms-question-title"><?php esc_html_e( 'This question has been deleted', 'lifterlms' ); ?></h3>
					<span class="llms-points">
						<?php printf( __( '%1$d / %2$d points', 'lifterlms' ), $attempt_question->get( 'earned' ), $attempt_question->get( 'points' ) ); ?>
					</span>
					<?php echo $attempt_question->get_status_icon(); ?>
				</span>
			</header>
		</li>
		<?php
		continue;
	}
	?>

	<li class="llms-quiz-attempt-question type--<?php echo $quiz_question->get( 'question_type' ); ?> status--<?php echo $attempt_question->get_status(); ?> <?php echo $attempt_question->is_correct() ? 'correct' : 'incorrect'; ?>"
		data-question-id="<?php echo $quiz_question->get( 'id' ); ?>"
		data-grading-manual="<?php echo $attempt_question->can_be_manually_graded() ? 'yes' : 'no'; ?>"
		data-points="<?php echo $attempt_question->get( 'points' ); ?>"
		data-points-curr="<?php echo $attempt_question->get( 'earned' ); ?>">
		<header class="llms-quiz-attempt-question-header">
			<a class="toggle-answer" href="#">

				<h3 class="llms-question-title"><?php echo $quiz_question->get_question( 'plain' ); ?></h3>

				<?php if ( $quiz_question->get( 'points' ) ) : ?>
					<span class="llms-points">
						<?php printf( __( '%1$d / %2$d points', 'lifterlms' ), $attempt_question->get( 'earned' ), $attempt_question->get( 'points' ) ); ?>
					</span>
				<?php endif; ?>

				<?php echo $attempt_question->get_status_icon(); ?>

			</a>
		</header>

		<section class="llms-quiz-attempt-question-main">

			<?php if ( apply_filters( 'llms_quiz_show_question_description', true, $attempt, $attempt_question, $quiz_question ) && $quiz_question->has_description() ) : ?>
				<div class="llms-quiz-attempt-answer-section llms-question-description">
					<?php echo $quiz_question->get_description(); ?>
				</div>
			<?php endif; ?>

			<?php if ( $attempt_question->get( 'answer' ) ) : ?>
				<div class="llms-quiz-attempt-answer-section llms-student-answer">
					<p class="llms-quiz-results-label student-answer"><?php _e( 'Selected answer: ', 'lifterlms' ); ?></p>
					<?php echo $attempt_question->get_answer(); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! $attempt_question->is_correct() ) : ?>
				<?php if ( llms_parse_bool( $quiz_question->get_quiz()->get( 'show_correct_answer' ) ) ) : ?>
					<?php if ( in_array( $quiz_question->get_auto_grade_type(), array( 'choices', 'conditional' ) ) ) : ?>
						<div class="llms-quiz-attempt-answer-section llms-correct-answer">
							<p class="llms-quiz-results-label correct-answer"><?php _e( 'Correct answer: ', 'lifterlms' ); ?></p>
							<?php echo $attempt_question->get_correct_answer(); ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( llms_parse_bool( $quiz_question->get( 'clarifications_enabled' ) ) ) : ?>
					<div class="llms-quiz-attempt-answer-section llms-clarifications">
						<p class="llms-quiz-results-label clarification"><?php _e( 'Clarification: ', 'lifterlms' ); ?></p>
						<?php echo $quiz_question->get( 'clarifications' ); ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>


			<?php if ( $attempt_question->has_remarks() ) : ?>
				<div class="llms-quiz-attempt-answer-section llms-remarks">
					<p class="llms-quiz-results-label remarks"><?php _e( 'Instructor remarks: ', 'lifterlms' ); ?></p>
					<div class="llms-remarks"><?php echo wpautop( $attempt_question->get( 'remarks' ) ); ?></div>
				</div>
			<?php endif; ?>

		</section>

	</li>
<?php endforeach; ?>
</ol>
