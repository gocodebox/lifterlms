<?php
/**
 * Quiz Results Template
 * @since    1.0.0
 * @version  3.16.0
 *
 * @arg  $attempt  (obj)  LLMS_Quiz_Attempt instance
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post;
$quiz = llms_get_post( $post );
if ( ! $quiz ) {
	return;
}

$student = llms_get_student();
$attempts = $student->quizzes()->get_attempts_by_quiz( $quiz->get( 'id' ), array(
	'per_page' => 25,
	'sort' => array(
		'attempt' => 'DESC',
	),
) );

$attempt = isset( $_GET['attempt_key'] ) ? $student->quizzes()->get_attempt_by_key( $_GET['attempt_key'] ) : false;

if ( ! $attempt && ! $attempts ) {
	return;
}
?>

<div class="clear"></div>
<div class="llms-quiz-results">

	<?php
		/**
		 * llms_single_quiz_attempt_results
		 * @hooked lifterlms_template_quiz_attempt_results - 10
		 */
		do_action( 'llms_single_quiz_attempt_results', $attempt );
	?>

	<?php if ( $attempts ) : ?>
		<section class="llms-quiz-results-history">
			<h2 class="llms-quiz-results-title"><?php _e( 'View Previous Attempts', 'lifterlms' ); ?></h2>
			<select id="llms-quiz-attempt-select">
				<option value="">-- <?php _e( 'Select an Attempt', 'lifterlms' ); ?> --</option>
				<?php foreach ( $attempts as $attempt ) : ?>
					<option value="<?php echo esc_url( $attempt->get_permalink() ); ?>">
						<?php // translators: 1: attempt number; 2: grade percentage; 3: pass/fail text ?>
						<?php printf( __( 'Attempt #%1$d - %2$s (%3$s)', 'lifterlms' ), $attempt->get( 'attempt' ), round( $attempt->get( 'grade' ), 2 ) . '%', $attempt->l10n( 'status' ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</section>
	<?php endif; ?>

</div>
