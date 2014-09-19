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
	<h2><?php var_dump($course) ?></h2>
</div>