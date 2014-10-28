<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit; 

global $post, $course, $lesson;

?>
<div class="llms-full-description">
	
	<?php echo apply_filters( 'lifterlms_full_description', $post->post_content ) ?>
	<h2><?php 
	$parent_course = get_post( $lesson->get_parent_course() );
	?></h2>

</div>