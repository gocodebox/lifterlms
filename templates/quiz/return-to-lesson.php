<?php
/**
 * Single Quiz: Return to Lesson Link
 * @since    1.0.0
 * @version  3.9.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
global $llms_quiz_attempt, $quiz, $post;

if ( $llms_quiz_attempt ) {
	$lesson = $llms_quiz_attempt->get( 'lesson_id' );
} elseif ( ! $quiz ) {
	$quiz = new LLMS_Quiz( $post->ID );
	$lesson = $quiz->get_assoc_lesson( get_current_user_id() );
} else {
	return;
}
?>

<div class="clear"></div>
<div class="llms-return">
	<a href="<?php echo esc_url( get_permalink( $lesson ) ); ?>"><?php _e( 'Return to Lesson', 'lifterlms' ); ?></a>
</div>
