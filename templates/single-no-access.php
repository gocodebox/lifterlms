<?php
/**
 * The Template for displaying all single courses.
 *
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;
global $post;
get_header();

while ( have_posts() ) : the_post(); 

	// if ($post->post_type =='course') {
	// 	llms_get_template_part( 'content', 'single-course' );	
	// }
	// elseif ($post->post_type =='llms_membership') {
	// 	llms_get_template_part( 'content', 'single-membership' );	
	// }
	// else {
		llms_get_template_part( 'content', 'no-access' ); 
	//}

endwhile;
?>

<?php

get_sidebar();
get_footer(); ?>