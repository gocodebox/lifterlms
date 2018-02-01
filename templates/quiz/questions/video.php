<?php
/**
 * Single Question featured video template
 * @since    3.16.0
 * @version  3.16.0
 *
 * @arg  $attempt  (obj)  LLMS_Quiz_Attempt instance
 * @arg  $question (obj)  LLMS_Question instance
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! $question->has_video() ) {
	return;
}
?>

<div class="llms-question-video llms-video-wrapper">
	<div class="center-video"><?php echo $question->get_video(); ?></div>
</div>
