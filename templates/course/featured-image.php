<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
global $post;
?>
<div class="post-thumbnail">
<h1 class="llms-featured-image"><?php echo lifterlms_get_featured_image_banner( $post->ID ); ?></h1>
</div>
