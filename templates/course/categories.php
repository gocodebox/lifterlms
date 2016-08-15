<?php
/**
 * Course categories template
 * @author 		LifterLMS
 * @package 	LifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post, $course;
if ( 'yes' !== get_option( 'lifterlms_course_display_categories' ) ) {
	return;
}
?>

<div class="llms-meta llms-categories">
	<p><?php echo get_the_term_list( $post->ID, 'course_cat', __( 'Categories: ', 'lifterlms' ), ', ', '' ); ?></p>
</div>
