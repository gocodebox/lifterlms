<?php
/**
 * Product Options Admin Metabox HTML
 *
 * @package  LifterLMS/Admin/Views
 *
 * @since 3.0.0
 * @version 3.29.0
 *
 * @var LLMS_Course $course
 * @var array $checkout_redirection_types checkout redirect setting options.
 * @var LLMS_Product $product
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="llms-metabox" id="llms-product-options-access-plans">

	<section class="llms-collapsible-group llms-access-plans" id="llms-access-plans">
		<div class="llms-no-plans-msg">
			<p><?php printf( __( 'Access plans define the payment options and access time-periods available for this %s.', 'lifterlms' ), strtolower( $product->get_post_type_label( 'singular_name' ) ) ); ?></h3>
			<p><?php printf( __( 'No access plans exist for your %s, click "Add New" to get started.', 'lifterlms' ), strtolower( $product->get_post_type_label( 'singular_name' ) ) ); ?></p>
		</div>
		<?php foreach ( $product->get_access_plans( false, false ) as $plan ) : ?>
			<?php include 'access-plan.php'; ?>
		<?php endforeach; ?>
	</section>

	<div class="llms-metabox-section d-all d-right">
		<button class="llms-button-secondary" id="llms-new-access-plan" type="button"><?php _e( 'Add New', 'lifterlms' ); ?></button>
		<button class="llms-button-primary" id="llms-save-access-plans" type="button"><?php _e( 'Save', 'lifterlms' ); ?></button>
	</div>

	<?php
		// unset $plan so it's not used for the model.
		unset( $plan );
		// model of an access plan we'll clone when clicking the "add" button.
		require 'access-plan.php';
	?>

</div>
