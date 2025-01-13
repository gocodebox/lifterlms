<?php
/**
 * Product Options Admin Metabox HTML
 *
 * @package LifterLMS/Admin/Views
 *
 * @since 3.0.0
 * @since 6.0.0 Fix closing tag inside the `llms-no-plans-msg` div element.
 * @version 6.0.0
 *
 * @var LLMS_Course $course
 * @var array $checkout_redirection_types checkout redirect setting options.
 * @var LLMS_Product $product
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="llms-metabox" id="llms-product-options-access-plans">
	<p>
		<?php
			$access_plan_allowed_html = array(
				'a' => array(
					'href'   => array(),
					'target' => array(),
				),
			);
			// Translators: %1$s = Link to access plans documentation; %2$s = The singular label of the custom post type.
			printf( wp_kses( __( '<a target="_blank" href="%1$s">Access plans</a> define the payment options and access time-periods available for this %2$s.', 'lifterlms' ), $access_plan_allowed_html ), esc_url( 'https://lifterlms.com/docs/what-is-an-access-plan/' ), esc_html( strtolower( $product->get_post_type_label( 'singular_name' ) ) ) );
			?>
	</p>

	<section class="llms-collapsible-group llms-access-plans" id="llms-access-plans">
		<div class="llms-no-plans-msg">
			<div class="notice notice-warning inline"><p><?php printf( esc_html__( 'No access plans exist for your %s, click "Add New" to get started.', 'lifterlms' ), esc_html( strtolower( $product->get_post_type_label( 'singular_name' ) ) ) ); ?></p></div>
		</div>
		<?php foreach ( $product->get_access_plans( false, false ) as $plan ) : ?>
			<?php include 'access-plan.php'; ?>
		<?php endforeach; ?>
	</section>

	<div class="llms-plans-actions">
		<div class="d-all">
			<button class="llms-button-secondary small" id="llms-new-access-plan" type="button">
				<span class="fa fa-plus"></span>
				<?php esc_html_e( 'Add New Plan', 'lifterlms' ); ?>
			</button>
		</div>

		<div class="clear"></div>

		<div class="d-all d-right">
			<button class="llms-button-primary" id="llms-save-access-plans" type="button"><?php esc_html_e( 'Save All Plans', 'lifterlms' ); ?></button>
		</div>
	</div>

	<?php
		// unset $plan so it's not used for the model.
		unset( $plan );
		// model of an access plan we'll clone when clicking the "add" button.
		require 'access-plan.php';
	?>

	<?php if ( apply_filters( 'llms_show_access_plan_dialog', true ) ) : ?>
		<?php include 'access-plan-dialog.php'; ?>
	<?php endif; ?>

</div>
