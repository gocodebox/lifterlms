<?php
/**
 * Lesson Audio embed
 *
 * @package LifterLMS/Templates
 *
 * @since 1.0.0
 * @version 3.1.1
 */

defined( 'ABSPATH' ) || exit;

global $post;

$lesson = new LLMS_Lesson( $post );

if ( ! $lesson->get( 'audio_embed' ) ) {
	return; }
?>

<div class="llms-audio-wrapper">
	<div class="center-audio">
		<?php echo $lesson->get_audio(); ?>
	</div>
</div>
