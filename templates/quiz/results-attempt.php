<?php
/**
 * Quiz Single Attempt Results
 *
 * @param LLMS_Quiz_Attempt $attempt LLMS_Quiz_Attempt instance.
 *
 * @version  [version]
 * @since 7.8.0 Hide answers if resumable attempt is incomplete.
 * @since [version] Show the score and late penalty when the attempt has one.
 *
 * @since    3.16.0
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

<h2 class="llms-quiz-results-title"><?php echo esc_html( sprintf( __( 'Attempt #%d Results', 'lifterlms' ), $attempt->get( 'attempt' ) ) ); ?></h2>

<?php if ( ! $attempt->can_be_resumed() ) : ?>
	<aside class="llms-quiz-results-aside">
		<?php if ( $attempt->get_count( 'available_points' ) ) : ?>
			<?php echo wp_kses_post( llms_get_donut( $attempt->get( 'grade' ), $attempt->l10n( 'status' ), 'default', array( $donut_class ) ) ); ?>
		<?php endif; ?>
		<ul class="llms-quiz-meta-info">
			<?php if ( $attempt->get_count( 'gradeable_questions' ) ) : ?>
				<li class="llms-quiz-meta-item"><?php echo esc_html( sprintf( __( 'Correct Answers: %1$d / %2$d', 'lifterlms' ), $attempt->get_count( 'correct_answers' ), $attempt->get_count( 'gradeable_questions' ) ) ); ?></li>
			<?php endif; ?>
			<?php if ( $attempt->get_late_penalty() ) : ?>
				<?php // Translators: %s = Quiz attempt score before the late penalty. ?>
				<li class="llms-quiz-meta-item"><?php echo esc_html( sprintf( __( 'Score: %s%%', 'lifterlms' ), $attempt->get_score() ) ); ?></li>
				<?php // Translators: %s = Late penalty taken off the score, in percentage points. ?>
				<li class="llms-quiz-meta-item"><?php echo esc_html( sprintf( __( 'Late penalty: −%s%%', 'lifterlms' ), $attempt->get_late_penalty() ) ); ?></li>
				<?php if ( $attempt->get( 'late_penalty_note' ) ) : ?>
					<li class="llms-quiz-meta-item llms-late-penalty-note"><?php echo esc_html( $attempt->get( 'late_penalty_note' ) ); ?></li>
				<?php endif; ?>
			<?php endif; ?>
			<li class="llms-quiz-meta-item"><?php echo esc_html( sprintf( __( 'Completed: %s', 'lifterlms' ), $attempt->get_date( 'start' ) ) ); ?></li>
			<li class="llms-quiz-meta-item"><?php echo esc_html( sprintf( __( 'Total time: %s', 'lifterlms' ), $attempt->get_time() ) ); ?></li>
		</ul>
	</aside>
<?php endif; ?>

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

