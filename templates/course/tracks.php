<?php
/**
 * Course tracks template
 * @author 		LifterLMS
 * @package 	LifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post, $course;
if ( 'yes' !== get_option( 'lifterlms_course_display_tracks' ) ) {
	return;
}
?>

<div class="llms-meta llms-tracks">
	<p><?php echo get_the_term_list( $post->ID, 'course_track', __( 'Tracks: ', 'lifterlms' ), ', ', '' ); ?></p>
</div>
