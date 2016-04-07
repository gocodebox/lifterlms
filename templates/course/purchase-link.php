<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post, $course, $product;

if ( ! $course  || ! is_object( $course ) ) {

	$course = new LLMS_Course( $post->ID );

}
if ( ! $product ) {
	$product = new LLMS_Product( $post->ID );
}

$user_id = get_current_user_id();

$user = new LLMS_Person;
$user_postmetas = $user->get_user_postmeta_data( $user_id, $course->id );

if ( $user_postmetas  ) {
	$course_progress = $course->get_percent_complete();

	if ($course->get_next_uncompleted_lesson()) {
		$next_lesson = get_permalink( $course->get_next_uncompleted_lesson() );
	}
}

$single_price = $product->get_single_price();
$rec_price = $product->get_recurring_price();
$memberships_required = get_post_meta( $course->id, '_llms_restricted_levels', true );

$user_is_member = llms_does_user_memberships_contain_course( $user_id, $course->id );
?>

<div class="llms-purchase-link-wrapper">
	<?php
	if (! llms_is_user_enrolled( get_current_user_id(), $course->id ) ) {
		if ( (! is_user_logged_in() || $single_price > 0 || $rec_price > 0) && ! $user_is_member ) {
			$message = apply_filters( 'lifterlms_checkout_message', '' );

			if ( ! empty( $message ) ) {
			}

			//if membership required to take course
			if ($memberships_required) {

				//if there is more than 1 membership that can view the content then redirect to memberships page
				if (($single_price > 0 || $rec_price > 0)) {
				?>
				<a href="<?php echo $course->get_checkout_url(); ?>" class="button llms-button llms-purchase-button"><?php printf( '%s', $course->get_purchase_button_text() ); ?></a>
					<?php } ?>
				<a href="<?php echo $course->get_membership_link(); ?>" class="button llms-button llms-purchase-button"><?php printf( '%s', $course->get_purchase_membership_button_text() ); ?></a>
				<?php
			} //if course is purchasable redirect to login / registration page
			else {
				if ( check_course_capacity() ) { ?>
					<a href="<?php echo $course->get_checkout_url(); ?>" class="button llms-button llms-purchase-button"><?php printf( '%s', $course->get_purchase_button_text() ); ?></a>
				<?php
				} else {
					_e( 'Course is no longer available', 'lifterlms' );
				}
			}
			//check if membership level is required
		?>
		<?php
		} else if ( ! $user_is_member && $memberships_required ) {
			?>
				<a href="<?php echo $course->get_membership_link(); ?>" class="button llms-button llms-purchase-button"><?php printf( '%s', $course->get_purchase_membership_button_text() ); ?></a>
			<?php
		} else {
				?>
				<?php if ( check_course_capacity() ) { ?>
					<form action="" method="post">

						<input type="hidden" name="product_id" value="<?php echo $course->id; ?>" />
						<input type="hidden" name="product_price" value="<?php echo $course->get_price(); ?>" />
						<input type="hidden" name="product_sku" value="<?php echo $course->get_sku(); ?>" />
						<input type="hidden" name="product_title" value="<?php echo $post->post_title; ?>" />

						<input type="hidden" name="payment_option" value="none_0" />
						<input id="payment_method_<?php echo 'none' ?>" type="hidden" name="payment_method" value="none_0" <?php //checked( $gateway->chosen, true ); ?> />
						<p><input type="submit" class="button llms-button llms-purchase-button" name="create_order_details" value="<?php printf( '%s', $course->get_purchase_button_text() ); ?>" /></p>

						<?php wp_nonce_field( 'create_order_details' ); ?>
						<input type="hidden" name="action" value="create_order_details" />
					</form>
				<?php } else {
					_e( 'Course is no longer available', 'lifterlms' );
				} ?>

		<?php }
	} elseif ( isset( $next_lesson )  ) {
		$next_lesson = $course->get_next_uncompleted_lesson();

		lifterlms_course_progress_bar( $course_progress, get_permalink( $next_lesson ) );
	} else { ?>
		<h5 class="llms-h5"><?php printf( __( 'Course %s%% Complete!', 'lifterlms' ), $course_progress ); ?></h5>
<?php } ?>
</div>
