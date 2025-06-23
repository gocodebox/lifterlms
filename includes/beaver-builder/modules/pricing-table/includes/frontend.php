<?php
/**
 * LifterLMS Course/Membership Pricing Table Module HTML.
 *
 * @package LifterLMS_Labs/Labs/BeaverBuilder/Modules/PricingTable/Templates
 *
 * @since 1.3.0
 * @since 1.7.0 Escaped attributes.
 * @version 1.7.0
 *
 * @param $settings obj Beaver Builder module settings instance.
 */

defined( 'ABSPATH' ) || exit;

$product_id = $module->get_product_id( $settings );
if ( ! $product_id ) {
	return;
}
?>

<div class="llms-lab-pricing-table">
	<?php do_action( 'llms_lab_bb_before_pricing_table', $product_id ); ?>
	[lifterlms_pricing_table product="<?php echo esc_attr( $product_id ); ?>"]
	<?php do_action( 'llms_lab_bb_after_pricing_table', $product_id ); ?>
</div>
