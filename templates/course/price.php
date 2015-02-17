<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post, $course;

if ( ! $course ) {

	$course = new LLMS_Course( $post->ID );
	
}

$product_obj = new LLMS_Product($post->ID);
$single_html_price = sprintf( __( 'Single payment of %s', 'lifterlms' ), $product_obj->get_price_html() ); 
//$recurring_html_price = $product_obj->get_recurring_price_html();
$payment_options = $product_obj->get_payment_options();

?>
<div class="llms-price-wrapper">
	<?php if ( ! llms_is_user_enrolled( get_current_user_id(), $course->id ) ) :
		foreach ($payment_options as $key => $value) :
			if ($value == 'single') : ?>
			<h4 class="llms-price"><span><?php echo $single_html_price; ?></span></h4> 

			<?php 
			if ( count($payment_options) > 1 ) {
				echo 'Or';
			}

			endif; 
			if ($value == 'recurring') : 
				$subs = $product_obj->get_subscriptions();
			foreach ($subs as $id => $sub) : ?>
				<h4 class="llms-price"><span><?php echo $product_obj->get_subscription_price_html($sub); ?></span></h4>
			<?php endforeach; ?>
			

			<?php endif; ?>
		<?php 
		endforeach;
	endif; ?>
</div>