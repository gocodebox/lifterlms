<?php
/**
 * Single Quiz: Meta Information
 *
 * @package LifterLMS/Templates
 *
 * @since 3.9.0
 * @since 4.0.0 Unknown.
 * @since 4.17.0 Return early if accessed without a logged in user or the quiz can't be loaded from the `$post` global.
 * @version 4.17.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$student = llms_get_student();
if ( ! $student ) {
	return;
}

$quiz = llms_get_post( $post );
if ( ! $quiz ) {
	return;
}
$passing_percent = $quiz->get( 'passing_percent' );
?>

<h2 class="llms-quiz-meta-title"><?php _e( 'Quiz Information', 'lifterlms' ); ?></h2>
<ul class="llms-quiz-meta-info">
	<?php if ( $passing_percent ) : ?>
	<li class="llms-quiz-meta-item llms-passing-percent">
		<?php printf( __( 'Minimum Passing Grade: %s', 'lifterlms' ), '<span class="llms-pass-perc">' . $passing_percent . '%</span>' ); ?>
	</li>
	<?php endif; ?>

	<li class="llms-quiz-meta-item llms-attempts">
		<?php printf( __( 'Remaining Attempts: %s', 'lifterlms' ), '<span class="llms-attempts">' . $student->quizzes()->get_attempts_remaining_for_quiz( $quiz->get( 'id' ) ) . '</span>' ); ?>
	</li>

	<li class="llms-quiz-meta-item llms-question-count">
		<?php printf( __( 'Questions: %s', 'lifterlms' ), '<span class="llms-question-count">' . count( $quiz->get_questions( 'ids' ) ) . '</span>' ); ?>
	</li>

	<?php if ( $quiz->has_time_limit() ) : ?>
	<li class="llms-quiz-meta-item llms-time-limit">
		<?php printf( __( 'Time Limit: %s', 'lifterlms' ), '<span class="llms-time-limit">' . $quiz->get_time_limit_string() . '</span>' ); ?>
	</li>
	<?php endif; ?>
</ul>
