<?php
/**
 * @author 		LifterLMS
 * @package 	LifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post, $lesson;

if ( ! $lesson || ! is_object( $lesson ) ) {

	$lesson = new LLMS_Lesson( $post->ID );

}

if ( ! $lesson->get_video() ) { return; }

?>

<div class="llms-video-wrapper">
	<div class="center-video">
		<?php echo $lesson->get_video(); ?>
	</div>
</div>
