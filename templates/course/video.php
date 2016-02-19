<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post, $course;

if ( ! $course || ! is_object( $course )) {

	$course = new LLMS_Course( $post->ID );

}

$course_not_class = get_post_custom( $post->ID );

if ( ! $course->get_video() ) { return; }

?>

<div class="llms-video-wrapper">
	<div class="center-video">
		<?php echo $course->get_video(); ?>
	</div>

</div>
