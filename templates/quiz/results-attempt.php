<?php
/**
 * Quiz Single Attempt Results
 * @since    [version]
 * @version  [version]
 *
 * @arg  $attempt  (obj)  LLMS_Quiz_Attempt instance
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! $attempt ) {
	return;
}
?>

<h2 class="llms-quiz-results-title"><?php printf( __( 'Attempt #%d Results', 'lifterlms' ), $attempt->get( 'attempt' ) ); ?></h2>

<aside class="llms-quiz-results-aside">
	<?php echo llms_get_donut( $attempt->get( 'grade' ), $attempt->l10n( 'status' ), 'default', array( $attempt->is_passing() ? 'passing' : 'failing' ) ); ?>
	<ul class="llms-quiz-meta-info">
		<li class="llms-quiz-meta-item"><?php printf( __( 'Correct Answers: %1$d / %2$d', 'lifterlms' ), $attempt->get_count( 'correct_answers' ), $attempt->get_count( 'questions' ) ); ?></li>
		<li class="llms-quiz-meta-item"><?php printf( __( 'Completed: %s', 'lifterlms' ), $attempt->get_date( 'start' ) ); ?></li>
		<li class="llms-quiz-meta-item"><?php printf( __( 'Total time: %s', 'lifterlms' ), $attempt->get_time() ); ?></li>
	</ul>
</aside>

<section class="llms-quiz-results-main">

	<?php
		/**
		 * llms_single_quiz_attempt_results_main
		 * @hooked lifterlms_template_quiz_attempt_results_questions_list - 10
		 */
		do_action( 'llms_single_quiz_attempt_results_main', $attempt );
	?>

</section>

