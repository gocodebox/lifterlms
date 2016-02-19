<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post, $product;

?>
<div class="llms-full-description">
	
	<?php echo apply_filters( 'the_content', apply_filters( 'lifterlms_full_description', do_shortcode( $post->post_content ) ) ); ?>

</div>
