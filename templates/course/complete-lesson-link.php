<?php
/**
 * Lesson Progression actions
 * Mark Complete & Mark Incomplete buttons
 * Take Quiz Button when quiz attached
 * @since    1.0.0
 * @version  3.17.4
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$lesson = llms_get_post( $post );
if ( ! $lesson ) {
	return;
}

if ( ! llms_is_user_enrolled( get_current_user_id(), $lesson->get( 'parent_course' ) ) && ! current_user_can( 'edit_post', $lesson->get( 'id' ) ) ) {
	return;
}

$student = llms_get_student( get_current_user_id() );
$quiz_id = $lesson->get( 'quiz' );
if ( 'publish' !== get_post_status( $quiz_id ) && ! current_user_can( 'edit_post', $quiz_id ) ) {
	$quiz_id = false;
}

$show_button = $quiz_id ? false : true;
?>

<div class="clear"></div>
<div class="llms-lesson-button-wrapper">

	<?php do_action( 'llms_before_lesson_buttons', $lesson, $student ); ?>

	<?php if ( $student->is_complete( $lesson->get( 'id' ), 'lesson' ) ) : ?>

		<?php if ( apply_filters( 'llms_show_mark_complete_button', $show_button, $lesson ) ) : ?>

			<?php echo apply_filters( 'llms_lesson_complete_text', __( 'Lesson Complete', 'lifterlms' ) ); ?>
			<?php do_action( 'llms_after_lesson_complete_text', $lesson ); ?>

			<?php if ( 'yes' === get_option( 'lifterlms_retake_lessons', 'no' ) || apply_filters( 'lifterlms_retake_lesson_' . $lesson->get( 'parent_course' ), false ) ) : ?>

				<form action="" class="llms-incomplete-lesson-form" method="POST" name="mark_incomplete">

					<?php do_action( 'lifterlms_before_mark_incomplete_lesson' ); ?>

					<input type="hidden" name="mark-incomplete" value="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>" />
					<input type="hidden" name="action" value="mark_incomplete" />
					<?php wp_nonce_field( 'mark_incomplete' ); ?>

					<?php llms_form_field( array(
						'columns' => 12,
						'classes' => 'llms-button-secondary auto button',
						'id' => 'llms_mark_incomplete',
						'value' => apply_filters( 'lifterlms_mark_lesson_incomplete_button_text', __( 'Mark Incomplete', 'lifterlms' ), $lesson ),
						'last_column' => true,
						'name' => 'mark_incomplete',
						'required' => false,
						'type'  => 'submit',
					) ); ?>

					<?php do_action( 'lifterlms_after_mark_incomplete_lesson' ); ?>

				</form>

			<?php endif; ?>

		<?php endif; ?>

	<?php else : ?>

		<?php if ( apply_filters( 'llms_show_mark_complete_button', $show_button, $lesson ) ) : ?>

			<form action="" class="llms-complete-lesson-form" method="POST" name="mark_complete">

				<?php do_action( 'lifterlms_before_mark_complete_lesson' ); ?>

				<input type="hidden" name="mark-complete" value="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>" />
				<input type="hidden" name="action" value="mark_complete" />
				<?php wp_nonce_field( 'mark_complete' ); ?>

				<?php llms_form_field( array(
					'columns' => 12,
					'classes' => 'llms-button-primary auto button',
					'id' => 'llms_mark_complete',
					'value' => apply_filters( 'lifterlms_mark_lesson_complete_button_text', __( 'Mark Complete', 'lifterlms' ), $lesson ),
					'last_column' => true,
					'name' => 'mark_complete',
					'required' => false,
					'type'  => 'submit',
				) ); ?>

				<?php do_action( 'lifterlms_after_mark_complete_lesson' ); ?>

			</form>

		<?php endif; ?>

	<?php endif; ?>

	<?php if ( $quiz_id ) : ?>

		<?php do_action( 'llms_before_start_quiz_button' ); ?>

		<a class="llms-button-action auto button" id="llms_start_quiz" href="<?php echo get_permalink( $quiz_id ); ?>">
			<?php echo apply_filters( 'lifterlms_start_quiz_button_text', __( 'Take Quiz', 'lifterlms' ), $quiz_id, $lesson ); ?>
		</a>

		<?php do_action( 'llms_after_start_quiz_button' ); ?>

	<?php endif; ?>

	<?php do_action( 'llms_after_lesson_buttons', $lesson, $student ); ?>

</div>
