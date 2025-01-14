<?php
/**
 * Course categories template
 *
 * @author      LifterLMS
 * @package     LifterLMS/Templates
 */

defined( 'ABSPATH' ) || exit;

global $post;

// Return if the course doesn't have a course_cat.
if ( ! has_term( '', 'course_cat', $post->ID ) ) {
	return;
}

?>

<div class="llms-meta llms-categories">
	<p><?php echo get_the_term_list( $post->ID, 'course_cat', __( 'Categories: ', 'lifterlms' ), ', ', '' ); ?></p>
</div>
