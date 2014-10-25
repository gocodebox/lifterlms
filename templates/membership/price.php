<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post, $product;
LLMS_log('function working');

?>
<?php if ( ! llms_is_user_member( get_current_user_id(), $product->id ) ) { ?>

	<?php if ( $product->get_price_html() ) { ?>

	<div class="llms-price-wrapper">

		<p class="llms-price">Price: <span class="length"><?php echo $product->get_price_html(); ?></span></p> 

	</div>

<?php }
 } ?>