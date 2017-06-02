<?php
/**
 * Quiz Start & Next lesson buttons
 * @since    1.0.0
 * @version  3.9.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $llms_quiz_attempt, $quiz, $post;

$user_id = get_current_user_id();

$key = '';
if ( $llms_quiz_attempt ) {
	$key = $llms_quiz_attempt->get_key();
	$quiz = $llms_quiz_attempt->get( 'quiz_id' );
	$lesson = $llms_quiz_attempt->get( 'lesson_id' );
} elseif ( ! $quiz ) {
	$quiz = $post->ID;
	$lesson = $quiz->get_assoc_lesson( $user_id );
} else {
	return;
}

$quiz = new LLMS_Quiz( $quiz );
$lesson = llms_get_post( $lesson );
?>

<div class="llms-quiz-buttons llms-button-wrapper" id="quiz-start-button">

	<?php do_action( 'lifterlms_before_start_quiz' ); ?>

	<?php if ( isset( $quiz ) ) : ?>

		<form method="POST" action="" name="llms_start_quiz" enctype="multipart/form-data">

			<?php if ( $quiz->is_open( $user_id ) ) : ?>

				<input id="llms-attempt-key" name="llms_attempt_key" type="hidden" value="<?php echo $key; ?>"/>
				<input id="llms-lesson-id" name="llms_lesson_id" type="hidden" value="<?php echo $lesson->get( 'id' ); ?>"/>
				<input id="llms-quiz-id" name="llms_quiz_id" type="hidden" value="<?php echo $quiz->get_id(); ?>"/>

				<input type="hidden" name="action" value="llms_start_quiz" />

				<?php wp_nonce_field( 'llms_start_quiz' ); ?>

				<button class="llms-start-quiz-button llms-button-action button" id="llms_start_quiz" name="llms_start_quiz" type="submit">
					<?php echo apply_filters( 'lifterlms_begin_quiz_button_text', __( 'Start Quiz', 'lifterlms' ), $quiz, $lesson ); ?>
				</button>

			<?php else : ?>
				<p><?php _e( 'You are not able take this quiz', 'lifterlms' ); ?></p>
			<?php endif; ?>

			<?php if ( $lesson->get_next_lesson() && llms_is_complete( $user_id, $lesson->get( 'id' ), 'lesson' ) ) : ?>
				<a href="<?php echo get_permalink( $lesson->get_next_lesson() );?>" class="button llms-button-secondary llms-next-lesson"><?php _e( 'Next Lesson','lifterlms' ); ?></a>
			<?php endif; ?>
		</form>

	<?php else : ?>

		<p><?php _e( 'You are not able take this quiz', 'lifterlms' ); ?></p>

	<?php endif; ?>

	<?php do_action( 'lifterlms_after_start_quiz' ); ?>

</div>
