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


if ( ! $lesson->get_video() ) { return; }
?>

<div class="llms-video-wrapper">
	<div class="center-video">
		<?php echo $lesson->get_video(); ?>
	</div>

</div>
