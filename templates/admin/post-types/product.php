<?php
/**
 * Order Details metabox for Order on Admin Panel
 *
 * @since  3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! is_admin() ) { exit; }
?>
<div class="llms-metabox" id="llms-product-options-access-plans">

	<header class="llms-metabox-section d-all no-top-margin">

		<div class="d-2of3">

			<h2><?php printf( __( '%s Access Plans', 'lifterlms' ), $product->get_post_type_label( 'singular_name' ) ); ?></h2>
			<h3><?php printf( __( 'Access plans define the payment options available for this %s during checkout', 'lifterlms' ), strtolower( $product->get_post_type_label( 'singular_name' ) ) ); ?></h3>

		</div>

		<div class="d-1of3 d-right last-col">
			<button class="llms-button-secondary small" id="llms-new-access-plan" type="button"><?php _e( 'Add Access Plan', 'lifterlms' ); ?></button>
			<p style="display:none;"><em><?php printf( __( 'You cannot create more than %d access plans for each product.', 'lifterlms' ), $product->get_access_plan_limit() ); ?></em></p>
		</div>

	</header>

	<div class="clear"></div>

	<section class="llms-collapsible-group llms-access-plans" id="llms-access-plans">
		<p class="no-plans-message"><?php printf( __( 'No access plans exist for your %s.', 'lifterlms' ), strtolower( $product->get_post_type_label( 'singular_name' ) ) ); ?></p>
		<?php foreach ( $product->get_access_plans( false, false ) as $plan ) : ?>
			<?php llms_get_template( 'admin/post-types/product-access-plan.php', array(
				'course' => $course,
				'plan' => $plan,
			) ); ?>
		<?php endforeach; ?>
	</section>

	<div class="llms-metabox-section d-all d-right">
		<button class="llms-button-primary small" id="llms-save-access-plans" type="button"><?php _e( 'Save Access Plans', 'lifterlms' ); ?></button>
		<p style="display:none;"><em><?php printf( __( 'You cannot create more than %d access plans for each product.', 'lifterlms' ), $product->get_access_plan_limit() ); ?></em></p>
	</div>

	<?php // model of an access plan we'll clone when clicking the "add" button ?>
	<?php llms_get_template( 'admin/post-types/product-access-plan.php', array(
		'course' => $course,
	) ); ?>


	<div id="llms-delete-plan-modal" class="topModal">
		<div class="llms-modal-header"><?php _e( 'Confirm Your Action', 'lifterlms' ); ?></div>
		<div class="llms-modal-content">

			<p><?php _e( 'After deleting this access plan, any students subscribed to this plan will still have access and will continue to make recurring payments according to the access plan\'s settings. If you wish to terminate their plans you must do so manually.', 'lifterlms' ); ?></p>
			<p><strong><?php _e( 'This action cannot be reversed. ', 'lifterlms' ); ?></strong></p>
			<p><?php _e( 'Press the "Delete" button to permanently remove this plan.', 'lifterlms' ); ?></p>
			<p><a class="llms-button-danger" href="#" id="llms-confirm-delete-plan"><?php _e( 'Delete', 'lifterlms' ); ?></a></p>

		</div>
	</div>

</div>
