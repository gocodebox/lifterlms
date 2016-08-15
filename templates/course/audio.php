<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post, $course;

if ( ! $course->get_audio() ) { return; }
?>

<div class="llms-audio-wrapper">
	<div class="center-audio">
		<?php echo $course->get_audio(); ?>
	</div>
</div>
