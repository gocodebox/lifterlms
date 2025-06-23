<?php
/**
 * Quiz Results Template
 *
 * @package LifterLMS/Templates
 *
 * @since 1.0.0
 * @since 3.35.0 Access `$_GET` data via `llms_filter_input()`.
 * @since 4.17.0 Return early if accessed without a logged in user.
 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
 * @since 7.8.0 Don't try to round nulls.
 * @version 7.8.0
 *
 * @property LLMS_Quiz_Attempt $attempt Attempt object.
 */

defined( 'ABSPATH' ) || exit;

global $post;
$quiz = llms_get_post( $post );
if ( ! $quiz ) {
	return;
}

$student = llms_get_student();
if ( ! $student ) {
	return;
}

$attempts = $student->quizzes()->get_attempts_by_quiz(
	$quiz->get( 'id' ),
	array(
		'per_page' => 25,
		'sort'     => array(
			'attempt' => 'DESC',
		),
	)
);

$key     = llms_filter_input_sanitize_string( INPUT_GET, 'attempt_key' );
$attempt = $key ? $student->quizzes()->get_attempt_by_key( $key ) : false;

if ( ! $attempt && ! $attempts ) {
	return;
}
?>

<div class="clear"></div>
<div class="llms-quiz-results">

	<?php
		/**
		 * llms_single_quiz_attempt_results
		 *
		 * @hooked lifterlms_template_quiz_attempt_results - 10
		 */

		/**
		 * Action fired prior to the output of LifterLMS Quiz Results HTML
		 *
		 * @since Unknown
		 *
		 * @param LLMS_Quiz_Attempt $attempt Attempt object.
		 */
		do_action( 'llms_single_quiz_attempt_results', $attempt );
	?>

	<?php if ( $attempts ) : ?>
		<section class="llms-quiz-results-history">
			<h2 class="llms-quiz-results-title"><?php esc_html_e( 'View Previous Attempts', 'lifterlms' ); ?></h2>
			<select id="llms-quiz-attempt-select">
				<option value="">-- <?php esc_html_e( 'Select an Attempt', 'lifterlms' ); ?> --</option>
				<?php foreach ( $attempts as $attempt ) : ?>
					<option value="<?php echo esc_url( $attempt->get_permalink() ); ?>">
						<?php // Translators: %1$d = Attempt number; %2$s = Grade percentage; %3$s = Pass/fail text. ?>
						<?php echo esc_html( sprintf( __( 'Attempt #%1$d - %2$s (%3$s)', 'lifterlms' ), $attempt->get( 'attempt' ), round( $attempt->get( 'grade' ) ?? 0, 2 ) . '%', $attempt->l10n( 'status' ) ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</section>
	<?php endif; ?>

</div>
