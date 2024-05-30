<?php
/**
 * Quiz Start & Next lesson buttons
 *
 * @since 1.0.0
 * @since 3.25.0 Unknown.
 * @since 4.17.0 Early bail on orphan quiz.
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

		<form method="POST" action="" name="llms_start_quiz" enctype="multipart/form-data">

			<?php if ( $quiz->is_open() ) : ?>

				<input id="llms-lesson-id" name="llms_lesson_id" type="hidden" value="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>"/>
				<input id="llms-quiz-id" name="llms_quiz_id" type="hidden" value="<?php echo esc_attr( $quiz->get( 'id' ) ); ?>"/>

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
						echo wp_kses_post( apply_filters( 'lifterlms_begin_quiz_button_text', __( 'Start Quiz', 'lifterlms' ), $quiz, $lesson ) );
					?>
				</button>

			<?php else : ?>
				<p><?php esc_html_e( 'You are not able to take this quiz', 'lifterlms' ); ?></p>
			<?php endif; ?>

			<?php if ( $lesson->get_next_lesson() && llms_is_complete( get_current_user_id(), $lesson->get( 'id' ), 'lesson' ) ) : ?>
				<a href="<?php echo esc_url( get_permalink( $lesson->get_next_lesson() ) ); ?>" class="button llms-button-secondary llms-next-lesson"><?php esc_html_e( 'Next Lesson', 'lifterlms' ); ?></a>
			<?php endif; ?>
		</form>

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
