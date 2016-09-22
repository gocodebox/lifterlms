<?php
/**
 * Quiz Start Button
 *
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $quiz;

$user_id = get_current_user_id();
if ( ! empty( $quiz ) ) {
	$lesson = new LLMS_Lesson( $quiz->get_assoc_lesson( $user_id ) );
}
?>

<div class="clear"></div>
<div class="llms-button-wrapper" id="quiz-start-button">

	<?php do_action( 'lifterlms_before_start_quiz' ); ?>

	<?php if ( isset( $quiz ) ) : ?>

		<form method="POST" action="" name="llms_start_quiz" enctype="multipart/form-data">
			<?php if ( $quiz->is_open( $user_id ) ) : ?>
				<input id="llms-user" name="llms-user_id" type="hidden" value="<?php echo $user_id; ?>"/>
				<input id="llms-quiz" name="llms-quiz_id" type="hidden" value="<?php echo $quiz->id; ?>"/>
				<input id="llms_start_quiz" type="button" class="llms-button-action" name="llms_start_quiz" value="<?php _e( 'Start Quiz', 'lifterlms' ); ?>" />
				<input type="hidden" name="action" value="llms_start_quiz" />

				 	<?php wp_nonce_field( 'llms_start_quiz' ); ?>
			<?php else : ?>
				<p><?php _e( 'You are not able take this quiz', 'lifterlms' ); ?></p>
			<?php endif; ?>

			<?php if ( $lesson->get_next_lesson() && ! empty( $quiz ) && $quiz->is_passing_score( $user_id, $quiz->get_user_grade( $user_id ) ) ) : ?>
				<a href="<?php echo get_permalink( $lesson->get_next_lesson() );?>" class="button llms-button-primary llms-next-lesson"><?php _e( 'Next Lesson','lifterlms' ); ?></a>
			<?php endif; ?>
		</form>

	<?php else : ?>

		<p><?php _e( 'You are not able take this quiz', 'lifterlms' ); ?></p>

	<?php endif; ?>

	<?php do_action( 'lifterlms_after_start_quiz' ); ?>

</div>
