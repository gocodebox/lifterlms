<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post, $course;

?>

<div class="llms-purchase-link-wrapper">
	<?php if ( ! llms_is_user_enrolled( get_current_user_id(), $course->id ) ) { ?>
		<a href="<?php echo get_post_permalink( $course->ID); ?>" class="button llms-purchase-link"><?php _e( 'View Course', 'lifterlms' ); ?></a> 
	<?php  } 

	else { 
		$user = new LLMS_Person;
		$user_postmetas = $user->get_user_postmeta_data( get_current_user_id(), $course->id );

		$course_progress = $user_postmetas['_progress']->meta_value;

	?>
		<div class="llms-progress">
			<div class="progress__indicator"><?php printf( __( '%s%%', 'lifterlms' ), $course_progress ); ?></div>
				<div class="progress-bar">
				<div class="progress-bar-complete" style="width:<?php echo $course_progress ?>%"></div>
			</div>
		</div>

		<a href="<?php echo get_post_permalink( $course->ID); ?>" class="button llms-purchase-link"><?php printf( __( 'Continue (%s%%)', 'lifterlms' ), $course_progress ); ?></a> 

	<?php } ?>
</div>