<?php
/**
 * List of attempt questions/answers for a single attempt
 * @since    [version]
 * @version  [version]
 *
 * @arg  $attempt  (obj)  LLMS_Quiz_Attempt instance
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
?>

<ol class="llms-quiz-attempt-results">
<?php foreach ( $attempt->get_question_objects() as $attempt_question ) :
	$quiz_question = $attempt_question->get_question(); ?>

	<li class="llms-quiz-attempt-question status--<?php echo $attempt_question->get_status(); ?> <?php echo $attempt_question->is_correct() ? 'correct' : 'incorrect'; ?>">
		<header class="llms-quiz-attempt-question-header">
			<a class="toggle-answer" href="#">

				<h3 class="llms-question-title"><?php echo $quiz_question->get_question( 'plain' ); ?></h3>

				<?php if ( $quiz_question->get( 'points' ) ) : ?>
					<span class="llms-points">
						<?php printf( __( '%1$d / %2$d points', 'lifterlms' ), $attempt_question->get_earned_points(), $attempt_question->get( 'points' ) ); ?>
					</span>
				<?php endif; ?>

				<?php echo $attempt_question->get_status_icon(); ?>

			</a>
		</header>

		<section class="llms-quiz-attempt-question-main">

			<?php if ( $attempt_question->get( 'answer' ) ) : ?>
				<div class="llms-quiz-attempt-answer-section">
					<p class="llms-quiz-results-label student-answer"><?php _e( 'Your answer: ', 'lifterlms' ); ?></p>
					<?php echo $attempt_question->get_answer(); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! $attempt_question->is_correct() ) : ?>
				<?php if ( llms_parse_bool( $quiz_question->get_quiz()->get( 'show_correct_answer' ) ) ) : ?>
					<?php if ( 'choices' === $quiz_question->get_auto_grade_type() ) : ?>
						<div class="llms-quiz-attempt-answer-section">
							<p class="llms-quiz-results-label correct-answer"><?php _e( 'Correct answer: ', 'lifterlms' ); ?></p>
							<?php foreach ( $quiz_question->get_correct_choice() as $aid ) :
								$choice = $attempt_question->get_question()->get_choice( $aid ); ?>
								<?php echo $choice->get_choice(); ?>
							<?php endforeach; ?>
						</div>
					<?php elseif ( 'conditional' === $quiz_question->get_auto_grade_type() ) : ?>
						<div class="llms-quiz-attempt-answer-section">
							<p class="llms-quiz-results-label correct-answer"><?php _e( 'Correct answer: ', 'lifterlms' ); ?></p>
							<?php echo $quiz_question->get( 'correct_value' ); ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( llms_parse_bool( $quiz_question->get( 'clarifications_enabled' ) ) ) : ?>
					<div class="llms-quiz-attempt-answer-section">
						<p class="llms-quiz-results-label clarification"><?php _e( 'Clarification: ', 'lifterlms' ); ?></p>
						<?php echo $quiz_question->get( 'clarifications' ); ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<!-- // remarks... -->

		</section>

	</li>
<?php endforeach; ?>
</ol>

<script>
( function( $ ) {
	$( '.llms-quiz-attempt-question-header .toggle-answer' ).on( 'click', function( e ) {

		e.preventDefault();

		var $curr = $( this ).closest( 'header' ).next( '.llms-quiz-attempt-question-main' );

		$( this ).closest( 'li' ).siblings().find( '.llms-quiz-attempt-question-main' ).slideUp( 200 );

		if ( $curr.is( ':visible' ) ) {
			$curr.slideUp( 200 );
		}  else {
			$curr.slideDown( 200 );
		}

	} );
} )( jQuery );
</script>
