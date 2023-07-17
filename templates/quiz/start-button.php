<?php
/**
 * Quiz Start & Next lesson buttons
 *
 * @since 1.0.0
 * @since 3.25.0 Unknown.
 * @since 4.17.0 Early bail on orphan quiz.
 * @since [version] Added support for quiz resume.
 */

defined( 'ABSPATH' ) || exit;

global $post;
$quiz   = llms_get_post( $post );
$lesson = $quiz->get_lesson();

if ( ! $lesson || ! is_a( $lesson, 'LLMS_Lesson' ) ) {
	return;
}
?>

<div class="llms-quiz-buttons llms-button-wrapper" id="quiz-start-button">

	<?php
		/**
		 * Fired before the start quiz button
		 *
		 * @since Unknown
		 */
		do_action( 'lifterlms_before_start_quiz' );
	?>

	<?php if ( $quiz ) : ?>

		<?php $start_button_string = $quiz->can_be_resumed() && $quiz->can_be_resumed_by_student() ? __( 'Restart Quiz', 'lifterlms' ) : __( 'Start Quiz', 'lifterlms' ); ?>

		<form method="POST" action="" name="llms_start_quiz" enctype="multipart/form-data">

			<?php if ( $quiz->is_open() ) : ?>

				<input id="llms-lesson-id" name="llms_lesson_id" type="hidden" value="<?php echo $lesson->get( 'id' ); ?>"/>
				<input id="llms-quiz-id" name="llms_quiz_id" type="hidden" value="<?php echo $quiz->get( 'id' ); ?>"/>

				<input type="hidden" name="action" value="llms_start_quiz" />

				<?php wp_nonce_field( 'llms_start_quiz' ); ?>

				<button class="llms-start-quiz-button llms-button-action button" id="llms_start_quiz" name="llms_start_quiz" type="submit">
					<?php
						/**
						 * Filters the quiz button text
						 *
						 * @since Unknown
						 *
						 * @param string      $button_text The start quiz button text.
						 * @param LLMS_Quiz   $quiz        The current quiz instance.
						 * @param LLMS_Lesson $lesson      The parent lesson instance.
						 */
						echo apply_filters( 'lifterlms_begin_quiz_button_text', $start_button_string, $quiz, $lesson );
					?>
				</button>

			<?php else : ?>
				<p><?php _e( 'You are not able to take this quiz', 'lifterlms' ); ?></p>
			<?php endif; ?>

		</form>

		<?php if ( $quiz->can_be_resumed() && $quiz->can_be_resumed_by_student() ) : ?>

			<form method="POST" action="" name="llms_resume_quiz" enctype="multipart/form-data">

				<?php if ( $quiz->is_open() ) : ?>

					<input id="llms-lesson-id" name="llms_lesson_id" type="hidden" value="<?php echo $lesson->get( 'id' ); ?>"/>
					<input id="llms-quiz-id" name="llms_quiz_id" type="hidden" value="<?php echo $quiz->get( 'id' ); ?>"/>
					<input id="llms-attempt-key" name="llms_attempt_key" type="hidden" value="<?php echo $quiz->get_last_quiz_attempt_key(); ?>"/>

					<input type="hidden" name="action" value="llms_resume_quiz" />

					<?php wp_nonce_field( 'llms_resume_quiz' ); ?>

					<button class="llms-resume-quiz-button llms-button-action button" id="llms_resume_quiz" name="llms_resume_quiz" type="submit">
						<?php
							/**
							 * Filters the quiz resume button text.
							 *
							 * @since [version]
							 *
							 * @param string      $button_text The resume quiz button text.
							 * @param LLMS_Quiz   $quiz        The current quiz instance.
							 * @param LLMS_Lesson $lesson      The parent lesson instance.
							 */
							echo esc_html( apply_filters( 'lifterlms_resume_quiz_button_text', __( 'Resume Quiz', 'lifterlms' ), $quiz, $lesson ) );
						?>
					</button>

				<?php endif; ?>

			</form>
		<?php endif; ?>

		<?php if ( $lesson->get_next_lesson() && llms_is_complete( get_current_user_id(), $lesson->get( 'id' ), 'lesson' ) ) : ?>
			<a href="<?php echo get_permalink( $lesson->get_next_lesson() ); ?>" class="button llms-button-secondary llms-next-lesson"><?php _e( 'Next Lesson', 'lifterlms' ); ?></a>
		<?php endif; ?>

	<?php else : ?>

		<p><?php _e( 'You are not able to take this quiz', 'lifterlms' ); ?></p>

	<?php endif; ?>

	<?php
		/**
		 * Fired after the start quiz button
		 *
		 * @since Unknown
		 */
		do_action( 'lifterlms_after_start_quiz' );
	?>

</div>
