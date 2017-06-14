<?php
/**
 * Single Quiz: Meta Information
 * @since    3.9.0
 * @version  3.9.2
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
global $llms_quiz_attempt, $quiz, $post;

if ( $llms_quiz_attempt ) {
	$quiz = $llms_quiz_attempt->get( 'quiz_id' );
} elseif ( ! $quiz ) {
	$quiz = $post->ID;
} else {
	return;
}

$quiz = llms_get_post( $quiz );
$passing_percent = $quiz->get_passing_percent();
$attempts_left = $quiz->get_remaining_attempts_by_user( get_current_user_id() );
$time_limit = $quiz->get_time_limit();
?>

<h2 class="llms-quiz-meta-title"><?php _e( 'Quiz Information', 'lifterlms' ); ?></h2>
<ul class="llms-quiz-meta-info">
	<?php if ( $passing_percent ) : ?>
	<li class="llms-quiz-meta-item passing-percent">
		<?php printf( __( 'Minimum Passing Grade: %s', 'lifterlms' ), '<span class="llms-pass-perc">' . $passing_percent . '%</span>' ); ?>
	</li>
	<?php endif; ?>

	<li class="llms-quiz-meta-item passing-percent">
		<?php printf( __( 'Remaining Attempts: %s', 'lifterlms' ), '<span class="llms-attempts">' . $attempts_left . '</span>' ); ?>
	</li>

	<?php if ( $time_limit ) : ?>
	<li class="llms-quiz-meta-item passing-percent">
		<?php printf( __( 'Time Limit: %s', 'lifterlms' ), '<span class="llms-time-limit">' . LLMS_Date::convert_to_hours_minutes_string( $time_limit ) . '</span>' ); ?>
	</li>
	<?php endif; ?>
</ul>
