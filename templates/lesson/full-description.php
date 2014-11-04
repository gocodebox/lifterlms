<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit; 

global $post, $course, $lesson;
	// add_filter('lifterlms_full_description', 'llms_filter_content',1, 1);


	// 	function llms_filter_content($content){
	// 	return apply_filters('get_the_content', wptexturize(do_shortcode($content)) );
	// }

	add_filter('lifterlms_full_description', 'do_shortcode');


?>
<div class="llms-full-description">
	<?php echo apply_filters( 'lifterlms_full_description', wptexturize($post->post_content) ); ?>
</div>
<div class="clear"></div>