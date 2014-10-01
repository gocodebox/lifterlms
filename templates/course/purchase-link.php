<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post, $course;

?>
<?php if ( ! llms_is_user_enrolled( get_current_user_id(), $course->id ) ) { ?>

<div class="llms-purchase-link-wrapper">
	<a href="<?php echo $course->get_checkout_url(); ?>" class="llms-purchase-link"><?php _e( 'Take This Course', 'lifterlms' ); ?></a> 
</div>

<?php  } ?>