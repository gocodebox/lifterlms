<?php
/**
 * Quiz Single Attempt Results
 * @since    [version]
 * @version  [version]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! $attempt ) {
	return;
}
?>

<h2 class="llms-quiz-results-title"><?php printf( __( 'Attempt #%d Results', 'lifterlms' ), $attempt->get( 'attempt' ) ); ?></h2>

<aside class="llms-quiz-results-aside">
	<?php echo llms_get_donut( $attempt->get( 'grade' ), $attempt->l10n( 'passed' ), 'default', array( $attempt->get( 'passed' ) ? 'passing' : 'failing' ) ); ?>
	<ul class="llms-quiz-meta-info">
		<li class="llms-quiz-meta-item"><?php printf( __( 'Correct Answers: %1$d / %2$d', 'lifterlms' ), $attempt->get_count( 'correct_answers' ), $attempt->get_count( 'questions' ) ); ?></li>
		<li class="llms-quiz-meta-item"><?php printf( __( 'Completed: %s', 'lifterlms' ), $attempt->get_date( 'start' ) ); ?></li>
		<li class="llms-quiz-meta-item"><?php printf( __( 'Total time: %s', 'lifterlms' ), $attempt->get_time() ); ?></li>
	</ul>
</aside>

<section class="llms-quiz-results-main">
	<?php if ( apply_filters( 'llms_quiz_show_attempt_results', true ) ) : ?>
		<ol class="llms-quiz-attempt-results">
		<?php foreach ( $attempt->get_questions() as $qdata ) :
			$question = llms_get_post( $qdata['id'] );
			$correct = llms_parse_bool( $qdata['correct'] );
			// var_dump( $qdata ); ?>
			<li class="llms-quiz-attempt-question points-<?php echo $question->get( 'points' ); ?> <?php echo $correct ? 'correct' : 'incorrect'; ?>">
				<header class="llms-quiz-attempt-question-header">
					<a class="toggle-answer" href="#">
						<h3 class="llms-question-title"><?php echo $question->get( 'title' ); ?></h3>
						<?php if ( $question->get( 'points' ) ) : ?>
							<span class="llms-points">
								<?php printf( '%1$d / %2$d points', $correct ? $qdata['points'] : 0, $qdata['points'] ); ?>
							</span>
						<?php endif; ?>
						<?php if ( $qdata['correct'] ) : ?>
							<div class="llms-correct">
								<i class="fa fa-<?php echo $correct ? 'check' : 'times'; ?>-circle" aria-hidden="true"></i>
							</div>
						<?php endif; ?>
					</a>
				</header>
				<?php if ( $qdata['answer'] ) : ?>
					<section class="llms-quiz-attempt-question-main">
						<?php if ( $question->supports( 'choices' ) && $question->supports( 'grading', 'auto' ) ) : ?>

							<div class="llms-quiz-attempt-answer-section">
								<p class="llms-quiz-results-label student-answer"><?php _e( 'Your answer: ', 'lifterlms' ); ?></p>
								<?php foreach ( $qdata['answer'] as $aid ) :
									$choice = $question->get_choice( $aid ); ?>
									<?php echo $choice->get_choice(); ?>
								<?php endforeach; ?>
							</div>

							<?php if ( ! $correct && llms_parse_bool( $question->get_quiz()->get( 'show_correct_answer' ) ) ) : ?>
								<div class="llms-quiz-attempt-answer-section">
									<p class="llms-quiz-results-label correct-answer"><?php _e( 'Correct answer: ', 'lifterlms' ); ?></p>
									<?php foreach ( $question->get_correct_choice() as $aid ) :
										$choice = $question->get_choice( $aid ); ?>
										<?php echo $choice->get_choice(); ?>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<?php if ( ! $correct && llms_parse_bool( $question->get( 'clarifications_enabled' ) ) ) : ?>
								<div class="llms-quiz-attempt-answer-section">
									<p class="llms-quiz-results-label clarification"><?php _e( 'Clarification: ', 'lifterlms' ); ?></p>
									<?php echo $question->get( 'clarifications' ); ?>
								</div>
							<?php endif; ?>

						<?php endif; ?>

					</section>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
		</ol>
	<?php endif; ?>
</section>

