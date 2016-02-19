<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $quiz;

if ( ! $quiz ) {

	$quiz = new LLMS_Quiz( $post->ID );

}

$user_id = get_current_user_id();
$quiz_session = LLMS()->session->get( 'llms_quiz' );


$lesson = $quiz->get_assoc_lesson( $user_id );

if ( ! $lesson ) {
	$quiz_session = LLMS()->session->get( 'llms_quiz' );
	$lesson = $quiz_session->assoc_lesson;
	$lesson_link = get_permalink( $lesson );
} else {
	$lesson_link = get_permalink( $lesson );
}

if ( $lesson ) {

}

if ( ! empty( $lesson ) ) :
?>

<div class="clear"></div>
<div class="llms-return">
	<?php printf( __( '<a href="%s">Return to Lesson</a>', 'lifterlms' ), $lesson_link );?>
</div>

<?php endif; ?>
