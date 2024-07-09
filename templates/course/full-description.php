<?php
/**
 * @author      codeBOX
 * @package     lifterLMS/Templates
 */

defined( 'ABSPATH' ) || exit;

global $post;

?>
<div class="llms-full-description">
	<?php echo wp_kses_post( apply_filters( 'lifterlms_full_description', do_shortcode( $post->post_content ) ) ); ?>
</div>
<div class="clear"></div>
