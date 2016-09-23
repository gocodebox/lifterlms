<?php
/**
 * Course categories template
 * @author 		LifterLMS
 * @package 	LifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post;
?>

<div class="llms-meta llms-categories">
	<p><?php echo get_the_term_list( $post->ID, 'course_cat', __( 'Categories: ', 'lifterlms' ), ', ', '' ); ?></p>
</div>
