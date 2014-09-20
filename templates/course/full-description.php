<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit; 

global $post, $course;

//if ( ! $post->post_content ) return;
?>
<div class="llms-full-description">
	<?php echo apply_filters( 'lifterlms_full_description', $post->post_content ) ?>
	<h2><?php 
	$courseid = get_post_meta( $post->ID, '_parent_course');
	$parent_course = get_post($courseid[0]);
	//get_post_meta( $courseid, '_sections')

	//get_course( $courseid );

	var_dump( $parent_course ); ?></h2>


</div>