<?php
/**
 * Quiz Start & Next lesson buttons
 *
 * @since 1.0.0
 * @since 3.25.0 Unknown.
 * @since 4.17.0 Early bail on orphan quiz.
 * @since 7.8.0 Added support for quiz resume.
 * @version 7.8.0
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

		<?php if ( $quiz->is_open() ) : ?>
			<form method="POST" action="" name="llms_start_quiz" enctype="multipart/form-data">

				<input id="llms-lesson-id" name="llms_lesson_id" type="hidden" value="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>"/>
				<input id="llms-quiz-id" name="llms_quiz_id" type="hidden" value="<?php echo esc_attr( $quiz->get( 'id' ) ); ?>"/>

				<input type="hidden" name="action" value="llms_start_quiz" />

				<?php wp_nonce_field( 'llms_start_quiz' ); ?>

				<?php if ( $quiz->can_be_resumed_by_student() ) : ?>
					<?php
						$message  = esc_html__( 'You have a partially completed attempt for this quiz. You can continue where you left off by clicking the Resume Quiz button below.', 'lifterlms' );
						$message .= '<div><button class="llms-start-quiz-button llms-button-secondary button" id="llms_start_quiz" name="llms_start_quiz" type="submit">';

						/**
						 * Filters the restart quiz button text
						 *
						 * @since Unknown
						 *
						 * @param string      $button_text The start quiz button text.
						 * @param LLMS_Quiz   $quiz        The current quiz instance.
						 * @param LLMS_Lesson $lesson      The parent lesson instance.
						 */
						$message .= wp_kses_post( apply_filters( 'lifterlms_restart_quiz_button_text', __( 'Restart Quiz Instead', 'lifterlms' ), $quiz, $lesson ) );

						$message .= '</button></div>';
					?>
					<?php llms_print_notice( $message, 'notice' ); ?>
				<?php else : ?>

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
							echo wp_kses_post( apply_filters( 'lifterlms_begin_quiz_button_text', __( 'Start Quiz', 'lifterlms' ), $quiz, $lesson ) );
						?>
					</button>
				<?php endif; ?>
			</form>

		<?php else : ?>
			<p><?php esc_html_e( 'You are not able to take this quiz', 'lifterlms' ); ?></p>
		<?php endif; ?>

		<?php if ( $quiz->can_be_resumed_by_student() ) : ?>
			<form method="POST" action="" name="llms_resume_quiz" enctype="multipart/form-data">

				<input id="llms-attempt-key" name="llms_attempt_key" type="hidden" value="<?php echo esc_attr( $quiz->get_student_last_attempt_key() ); ?>"/>

				<input type="hidden" name="action" value="llms_resume_quiz" />

				<?php wp_nonce_field( 'llms_resume_quiz' ); ?>

				<button class="llms-resume-quiz-button llms-button-action button" id="llms_resume_quiz" name="llms_resume_quiz" type="submit">
					<?php
						/**
						 * Filters the quiz resume button text.
						 *
						 * @since 7.8.0
						 *
						 * @param string      $button_text The resume quiz button text.
						 * @param LLMS_Quiz   $quiz        The current quiz instance.
						 * @param LLMS_Lesson $lesson      The parent lesson instance.
						 */
						echo esc_html( apply_filters( 'lifterlms_resume_quiz_button_text', esc_html__( 'Resume Quiz', 'lifterlms' ), $quiz, $lesson ) );
					?>
				</button>

			</form>
		<?php endif; ?>

		<?php if ( $lesson->get_next_lesson() && llms_is_complete( get_current_user_id(), $lesson->get( 'id' ), 'lesson' ) ) : ?>
			<a href="<?php echo esc_url( get_permalink( $lesson->get_next_lesson() ) ); ?>" class="button llms-button-secondary llms-next-lesson"><?php esc_html_e( 'Next Lesson', 'lifterlms' ); ?></a>
		<?php endif; ?>

	<?php else : ?>

		<p><?php esc_html_e( 'You are not able to take this quiz', 'lifterlms' ); ?></p>

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
