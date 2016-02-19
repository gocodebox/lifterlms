<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post;

$course_not_class = get_post_custom( $post->ID );
if ( ! isset( $lesson )) {
	$lesson = new LLMS_Lesson( $post->ID );
}

if ( ! $lesson->get_audio() ) { return; }
?>

<div class="llms-audio-wrapper">
	<div class="center-audio">
		<?php echo $lesson->get_audio(); ?>
	</div>

</div>
