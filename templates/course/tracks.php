<?php
/**
 * Course tracks template
 *
 * @author      LifterLMS
 * @package     LifterLMS/Templates
 */

defined( 'ABSPATH' ) || exit;

global $post;
?>

<div class="llms-meta llms-tracks">
	<p><?php echo get_the_term_list( $post->ID, 'course_track', __( 'Tracks: ', 'lifterlms' ), ', ', '' ); ?></p>
</div>
