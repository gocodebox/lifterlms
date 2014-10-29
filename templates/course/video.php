<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post, $course;

$course_not_class = get_post_custom($post->ID);

if ( !$course->get_video() ) return;

?>

<div class="llms-video-wrapper">
	<div class="center-video">
		<?php echo $course->get_video(); ?>
	</div>

</div>