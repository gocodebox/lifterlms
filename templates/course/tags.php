<?php
/**
 * Course tags template
 *
 * @author      LifterLMS
 * @package     LifterLMS/Templates
 */

defined( 'ABSPATH' ) || exit;

global $post;

// Return if the course doesn't have a course_tag.
if ( ! has_term( '', 'course_tag', $post->ID ) ) {
	return;
}

?>

<div class="llms-meta llms-tags">
	<p><?php echo get_the_term_list( $post->ID, 'course_tag', __( 'Tags: ', 'lifterlms' ), ', ', '' ); ?></p>
</div>
