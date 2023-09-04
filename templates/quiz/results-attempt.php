<?php
/**
 * Quiz Single Attempt Results.
 *
 * @since 3.16.0
 * @since [version] Hide answers if resumable attempt is incomplete.
 * @version 3.27.0
 *
 * @arg $attempt (obj) LLMS_Quiz_Attempt instance.
 */
defined( 'ABSPATH' ) || exit;

if ( ! $attempt ) {
	return;
}
$donut_class = $attempt->get( 'status' );
if ( in_array( $donut_class, array( 'pass', 'fail' ) ) ) {
	$donut_class .= 'ing';
}
?>

<h2 class="llms-quiz-results-title"><?php printf( esc_html__( 'Attempt #%d Results', 'lifterlms' ), $attempt->get( 'attempt' ) ); ?></h2>

<?php if ( ! $attempt->can_be_resumed() ) { ?>
	<aside class="llms-quiz-results-aside">
		<?php if ( $attempt->get_count( 'available_points' ) ) : ?>
			<?php echo llms_get_donut( $attempt->get( 'grade' ), $attempt->l10n( 'status' ), 'default', array( $donut_class ) ); ?>
		<?php endif; ?>
		<ul class="llms-quiz-meta-info">
			<?php if ( $attempt->get_count( 'gradeable_questions' ) ) : ?>
				<li class="llms-quiz-meta-item"><?php printf( esc_html__( 'Correct Answers: %1$d / %2$d', 'lifterlms' ), $attempt->get_count( 'correct_answers' ), $attempt->get_count( 'gradeable_questions' ) ); ?></li>
			<?php endif; ?>
			<li class="llms-quiz-meta-item"><?php printf( esc_html__( 'Completed: %s', 'lifterlms' ), $attempt->get_date( 'start' ) ); ?></li>
			<li class="llms-quiz-meta-item"><?php printf( esc_html__( 'Total time: %s', 'lifterlms' ), $attempt->get_time() ); ?></li>
		</ul>
	</aside>
<?php } ?>

<section class="llms-quiz-results-main">

	<?php
		/**
		 * llms_single_quiz_attempt_results_main
		 *
		 * @hooked lifterlms_template_quiz_attempt_results_questions_list - 10
		 */
		do_action( 'llms_single_quiz_attempt_results_main', $attempt );
	?>

</section>

