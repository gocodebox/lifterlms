<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post, $lesson;

if ( ! $lesson->get_audio() ) { return; }
?>

<div class="llms-audio-wrapper">
	<div class="center-audio">
		<?php echo $lesson->get_audio(); ?>
	</div>
</div>
