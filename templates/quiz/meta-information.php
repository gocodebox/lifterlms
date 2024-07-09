<?php
/**
 * Single Quiz: Meta Information
 *
 * @package LifterLMS/Templates
 *
 * @since 3.9.0
 * @since 4.0.0 Unknown.
 * @since 4.17.0 Return early if accessed without a logged in user or the quiz can't be loaded from the `$post` global.
 * @since 7.4.0 Used `LLMS_Quiz::get_questions_count()` method for showing count and escaped labels.
 * @version 7.4.0
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

<h2 class="llms-quiz-meta-title"><?php esc_html_e( 'Quiz Information', 'lifterlms' ); ?></h2>
<ul class="llms-quiz-meta-info">
	<?php if ( $passing_percent ) : ?>
	<li class="llms-quiz-meta-item llms-passing-percent">
		<?php printf( esc_html__( 'Minimum Passing Grade: %s', 'lifterlms' ), '<span class="llms-pass-perc">' . esc_html( $passing_percent ) . '%</span>' ); ?>
	</li>
	<?php endif; ?>

	<li class="llms-quiz-meta-item llms-attempts">
		<?php printf( esc_html__( 'Remaining Attempts: %s', 'lifterlms' ), '<span class="llms-attempts">' . esc_html( $student->quizzes()->get_attempts_remaining_for_quiz( $quiz->get( 'id' ) ) ) . '</span>' ); ?>
	</li>

	<li class="llms-quiz-meta-item llms-question-count">
		<?php printf( esc_html__( 'Questions: %s', 'lifterlms' ), '<span class="llms-question-count">' . esc_html( $quiz->get_questions_count() ) . '</span>' ); ?>
	</li>

	<?php if ( $quiz->has_time_limit() ) : ?>
	<li class="llms-quiz-meta-item llms-time-limit">
		<?php printf( esc_html__( 'Time Limit: %s', 'lifterlms' ), '<span class="llms-time-limit">' . esc_html( $quiz->get_time_limit_string() ) . '</span>' ); ?>
	</li>
	<?php endif; ?>
</ul>
