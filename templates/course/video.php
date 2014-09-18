<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post, $course;

$course_not_class = get_post_custom($post->ID);

?>



<div class="llms-video-wrapper">

	<?php echo wp_oembed_get($course->get_video()); ?>

</div>