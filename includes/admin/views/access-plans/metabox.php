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

	<div
		id="llms-access-plan-dialog"
		class="llms-dialog-container"
		aria-labelledby="llms-access-plan-dialog-title"
		aria-hidden="true"
	>
		<!-- 2. The dialog overlay -->
		<div class="llms-dialog-overlay" data-a11y-dialog-hide></div>
		<!-- 3. The actual dialog -->
		<div class="llms-dialog-content" role="document">
			<button class="llms-dialog-close" type="button" data-a11y-dialog-hide aria-label="<?php echo esc_html( __( 'Close', 'lifterlms' ) ); ?>">
				&times;
			</button>
			<h1 id="llms-access-plan-dialog-title"><?php echo esc_html( __( 'What type of Access Plan do you want to create?', 'lifterlms' ) ); ?></h1>

			<div class="llms-access-plan-templates">
				<button class="template" data-template="free">
					<strong>Free</strong>
					<span>Free access that never expires.</span>
				</button>
				<button class="template" data-template="monthly">
					<strong>Monthly</strong>
					<span>Charge a recurring monthly subscription that never ends.</span>
				</button>
				<button class="template" data-template="annual">
					<strong>Annual</strong>
					<span>Charge a recurring annual subscription that never ends.</span>
				</button>
				<button class="template" data-template="one-time">
					<strong>One Time</strong>
					<span>Charge a one-time payment for a fixed period.</span>
				</button>
				<button class="template" data-template="lifetime">
					<strong>Lifetime</strong>
					<span>Charge a one-time payment that never expires.</span>
				</button>
				<button class="template" data-template="paid-trial">
					<strong>Paid Trial</strong>
					<span>Charge a fee for trial access and capture recurring payment info with a future monthly subscription that will start in 1 week.</span>
				</button>
				<button class="template" data-template="free-trial">
					<strong>Free Trial</strong>
					<span>Grant free access for 1 week with a future monthly subscription that will start in 1 week.</span>
				</button>
				<button class="template" data-template="hidden-access">
					<strong>Hidden Access</strong>
					<span>Grant free access without making this plan publicly available.</span>
				</button>
				<button class="template" data-template="sale">
					<strong>Sale</strong>
					<span>Discount a one-time payment for lifetime access.</span>
				</button>
				<button class="template" data-template="presell">
					<strong>Presell</strong>
					<span>Offer lifetime access for a one-time payment with a future start date.</span>
				</button>
				<a target="_blank" href="https://lifterlms.com/product/groups/?utm_source=LifterLMS%20Plugin&utm_medium=Access%20Plans&utm_campaign=Plugin%20to%20Sale">
					<span class="add-on">Add-on</span>
					<strong>Group Access</strong>
					<span>Allow a buyer to purchase lifetime access for a group of people.</span>
				</a>
				<button class="template" data-template="advanced">
					<strong>Advanced</strong>
					<span>Show all settings to create an access plan from scratch.</span>
				</button>
			</div>
		</div>
	</div>

</div>
