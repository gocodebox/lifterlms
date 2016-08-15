<?php
/**
 * @author 		LifterLMS
 * @package 	LifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post, $course;

if ( ! $course || ! is_object( $course ) ) {

	$course = new LLMS_Course( $post->ID );

}

if ( ! $course->get_video() ) { return; }

?>

<div class="llms-video-wrapper">
	<div class="center-video">
		<?php echo $course->get_video(); ?>
	</div>
</div>
