<?php
/**
 * Course tags template
 * @author 		LifterLMS
 * @package 	LifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post, $course;
if ( 'yes' !== get_option( 'lifterlms_course_display_tags' ) ) {
	return;
}
?>

<div class="llms-meta llms-tags">
	<p><?php echo get_the_term_list( $post->ID, 'course_tag', __( 'Tags: ', 'lifterlms' ), ', ', '' ); ?></p>
</div>
