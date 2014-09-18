<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit; 

global $post;

if ( ! $post->post_excerpt ) return;
?>
<div class="llms-short-description">
	<?php echo apply_filters( 'lifterlms_short_description', $post->post_excerpt ) ?>
</div>