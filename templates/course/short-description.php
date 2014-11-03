<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit; 

global $post;
add_filter('post_excerpt', 'do_shortcode');
if ( ! $post->post_excerpt ) return;
?>
<div class="llms-short-description">
	<?php echo apply_filters( 'lifterlms_full_description', do_shortcode( $post->post_excerpt ) ); ?>
</div>
<div class="clear"></div>