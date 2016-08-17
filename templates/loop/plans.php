<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

global $post;
/**
 * @todo  handle this template
 */
$product = new LLMS_Product( $post);
return;
?>

<?php if ( $product->is_purchasable() ): ?>

	<?php foreach( $product->get_access_plans() as $plan ): ?>


		<?php echo $plan->get_price( 'price' ); ?>
		<?php echo $plan->get_schedule_details(); ?>
		<br>

	<?php endforeach; ?>
<?php endif; ?>
