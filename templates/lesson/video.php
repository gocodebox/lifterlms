<?php
/**
 * Lesson Video embed
 *
 * @package LifterLMS/Templates
 *
 * @since 1.0.0
 * @version 3.1.1
 */

defined( 'ABSPATH' ) || exit;

global $post;

$lesson = new LLMS_Lesson( $post );

if ( ! $lesson->get( 'video_embed' ) ) {
	return; }
?>

<div class="llms-video-wrapper">
	<div class="center-video">
		<?php echo $lesson->get_video(); ?>
	</div>
</div>
