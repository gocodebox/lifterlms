<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post, $course, $product;

$user = new LLMS_Person;
$user_postmetas = $user->get_user_postmeta_data( get_current_user_id(), $course->id );

if ( $user_postmetas  ) {
	$course_progress = $course->get_percent_complete();

	if($course->get_next_uncompleted_lesson()) {
		$next_lesson = get_permalink($course->get_next_uncompleted_lesson());
	}
}

$single_price = $product->get_single_price();
$rec_price = $product->get_recurring_price();
$memberships_required = get_post_meta( $course->id, '_llms_restricted_levels', true );



?>
<div class="llms-purchase-link-wrapper">
	<?php
	if ( ! is_user_logged_in() ) {
		$message = apply_filters( 'lifterlms_checkout_message', '' );

		if ( ! empty( $message ) ) {
		}

		if ($memberships_required) {

			//if there is more than 1 membership that can view the content then redirect to memberships page
			if (count($memberships_required) > 1) {
				$membership_url = get_permalink( llms_get_page_id( 'memberships' ) );
			}
			//if only 1 membership level is assigned take visitor to the membership page
			else {
				$membership_url = get_permalink( $memberships_required[0] );
			}
			?>
			<a href="<?php echo $membership_url; ?>" class="button llms-button llms-purchase-button"><?php echo _e( 'Take This Course', 'lifterlms' ); ?></a>	
			<?php
		}

		//if course is purchasable redirect to login / registration page
		else {
			if(check_course_capacity()) {

				$account_url = get_permalink( llms_get_page_id( 'myaccount' ) );
				$account_redirect = add_query_arg( 'product-id', get_the_ID(), $account_url );

			?>
				<a href="<?php echo $account_redirect; ?>" class="button llms-button llms-purchase-button"><?php echo _e( 'Take This Course', 'lifterlms' ); ?></a>	
			<?php
			}else{
				echo "Course has reached its limit";
			}
		}
		//check if membership level is required
	?>
	<?php
	}
	//User is not enrolled
	elseif ( ! llms_is_user_enrolled( get_current_user_id(), $course->id ) ) {
		$user_id = get_current_user_id();
		$user_is_member = false;

		//check if user has required membership to take course
		if ($memberships_required) {
			foreach ($memberships_required as $key => $value) {
				if (llms_is_user_member($user_id, $value)) {
					$user_is_member = true;
				}
			}

			
		}

		//if a user is not a member and the product has a price > 0
		//if course can be purchased and user is not member redirect to checkout page
		if ( ($single_price  > 0 || $rec_price > 0) && !$user_is_member) {
		?>
			<a href="<?php echo $course->get_checkout_url(); ?>" class="button llms-button llms-purchase-button"><?php echo _e( 'Take This Course', 'lifterlms' ); ?></a>
		<?php
		}
		else {

		 ?>
			<?php if(check_course_capacity()) { ?>
				<form action="" method="post">
	
					<input type="hidden" name="product_id" value="<?php echo $course->id; ?>" />
				  	<input type="hidden" name="product_price" value="<?php echo $course->get_price(); ?>" />
				  	<input type="hidden" name="product_sku" value="<?php echo $course->get_sku(); ?>" />
				  	<input type="hidden" name="product_title" value="<?php echo $post->post_title; ?>" />
					<input type="hidden" name="payment_option" value="none_0" />
					<input id="payment_method_<?php echo 'none' ?>" type="hidden" name="payment_method" value="none_0" <?php //checked( $gateway->chosen, true ); ?> />
	
					<p><input type="submit" class="button llms-button llms-purchase-button" name="create_order_details" value="<?php _e( 'Take This Course', 'lifterlms' ); ?>" /></p>
	
					<?php wp_nonce_field( 'create_order_details' ); ?>
					<input type="hidden" name="action" value="create_order_details" />
				</form>
			<?php } else{
				echo "Course has reached its limit";
			} ?>

		<?php } 
	 }

	elseif( isset($next_lesson)  ) {
		$next_lesson = $course->get_next_uncompleted_lesson();

		lifterlms_course_progress_bar($course_progress,get_permalink( $next_lesson ));
	}
	else { ?>
	<h5 class="llms-h5"><?php printf( __( 'Course %s%% Complete!', 'lifterlms' ), $course_progress ); ?></h5>
<?php	} ?>
</div>