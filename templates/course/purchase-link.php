<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post, $course;

$user = new LLMS_Person;
$user_postmetas = $user->get_user_postmeta_data( get_current_user_id(), $course->id );

if ( $user_postmetas  ) {
	$course_progress = $course->get_percent_complete();

	$next_lesson = get_permalink($course->get_next_uncompleted_lesson());
}

?>

<div class="llms-purchase-link-wrapper">
	<?php if ( ! llms_is_user_enrolled( get_current_user_id(), $course->id ) ) { 
		
		if ( $course->get_price() > 0 ) {
		?>
			<a href="<?php echo $course->get_checkout_url(); ?>" class="button llms-purchase-link"><?php echo _e( 'Take This Course', 'lifterlms' ); ?></a>
		<?php
		}
		else { ?>
						
<form action="" method="post">

	<input type="hidden" name="product_id" value="<?php echo $course->id; ?>" />
  	<input type="hidden" name="product_price" value="<?php echo $course->get_price(); ?>" />
  	<input type="hidden" name="product_sku" value="<?php echo $course->get_sku(); ?>" />
  	<input type="hidden" name="product_title" value="<?php echo $post->post_title; ?>" />

	<input id="payment_method_<?php echo 'none' ?>" type="hidden" name="payment_method" value="none" <?php //checked( $gateway->chosen, true ); ?> />

	<p><input type="submit" class="button" name="create_order_details" value="<?php _e( 'Take This Course', 'lifterlms' ); ?>" /></p>

	<?php wp_nonce_field( 'create_order_details' ); ?>
	<input type="hidden" name="action" value="create_order_details" />
</form>

		<?php }
	?>
	<?php  } 

	elseif( isset( $next_lesson)  ) { 
		$next_lesson = $course->get_next_uncompleted_lesson();

	?>

		<div class="llms-progress">
			<div class="progress__indicator"><?php printf( __( '%s%%', 'lifterlms' ), $course_progress ); ?></div>
				<div class="progress-bar">
				<div class="progress-bar-complete" style="width:<?php echo $course_progress ?>%"></div>
			</div>
		</div>

		<a href="<?php echo get_permalink( $next_lesson ); ?>" class="button llms-purchase-link"><?php printf( __( 'Continue (%s%%)', 'lifterlms' ), $course_progress ); ?></a> 

<?php 
	}
	else { ?>
		<?php printf( __( 'Course %s%% Complete!', 'lifterlms' ), $course_progress ); ?>
<?php	} ?>
</div>