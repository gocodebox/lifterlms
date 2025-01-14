<?php
/**
 * Individual Access Plan as displayed within the "Product Options" metabox.
 *
 * @package LifterLMS/Admin/Views
 *
 * @since 3.0.0
 * @since 3.30.0 Added checkout redirect settings.
 * @since 3.31.0 Change sale_price input from text to number to ensure min value validation is properly enforced by browsers.
 * @since 3.37.18 Don't localize the price "step" html attribute.
 * @since 4.14.0 Get the access plan's raw content to display it in the wp_editor.
 * @since 7.3.0 Added another icon for possible issues with the access plan configuration.
 * @version 7.3.0
 *
 * @var LLMS_Course      $course                     LLMS_Course.
 * @var array            $checkout_redirection_types Checkout redirect setting options.
 * @var LLMS_Access_Plan $plan                       LLMS_Access_Plan.
 */

defined( 'ABSPATH' ) || exit;

// Create a "step" attribute for price fields according to LLMS settings.
$price_step = number_format( 0.01, get_lifterlms_decimals() );

if ( ! isset( $plan ) ) {

	$id    = 'llms-new-access-plan-model';
	$plan  = false;
	$order = 777;

} else {

	$id                    = 'llms-access-plan-' . $plan->get( 'id' );
	$order                 = $plan->get( 'menu_order' );
	$visibility            = $plan->get_visibility();
	$frequency             = $plan->get( 'frequency' );
	$period                = $plan->get( 'period' );
	$access_expiration     = $plan->get( 'access_expiration' );
	$access_period         = $plan->get( 'access_period' );
	$trial_offer           = $plan->get( 'trial_offer' );
	$on_sale               = $plan->get( 'on_sale' );
	$availability          = $plan->get( 'availability' );
	$checkout_redirect_url = $plan->get( 'checkout_redirect_url' );
}
?>

<div class="llms-metabox-section d-all llms-collapsible llms-access-plan" id="<?php echo esc_attr( $id ); ?>"<?php echo $plan ? 'data-id="' . esc_attr( $plan->get( 'id' ) ) . '"' : ''; ?>>

	<header class="llms-collapsible-header">
		<div class="d-2of3">
			<span class="fa fa-bars llms-drag-handle"></span>
			<h3>
				<?php if ( $plan ) : ?>
					<span class="llms-plan-title" data-default="<?php esc_attr_e( 'Unnamed Access Plan', 'lifterlms' ); ?>"><?php echo esc_html( $plan->get( 'title' ) ); ?></span>
					<small>(<?php printf( esc_html_x( 'ID# %s', 'Product Access Plan ID', 'lifterlms' ), esc_html( $plan->get( 'id' ) ) ); ?>)</small>
				<?php else : ?>
					<span class="llms-plan-title" data-default="<?php esc_attr_e( 'New Access Plan', 'lifterlms' ); ?>"><?php esc_html_e( 'New Access Plan', 'lifterlms' ); ?></span>
				<?php endif; ?>
			</h3>
		</div>
		<div class="d-1of3 d-right">
			<span class="tip--top-left" data-tip="<?php esc_attr_e( 'This access plan requires attention for possible misconfigurations', 'lifterlms' ); ?>">
				<span class="dashicons dashicons-warning medium-danger"></span>
			</span>
			<span class="tip--top-left" data-tip="<?php esc_attr_e( 'Errors were found during access plan validation', 'lifterlms' ); ?>">
				<span class="dashicons dashicons-warning"></span>
			</span>
			<span class="dashicons dashicons-trash llms-plan-delete"></span>
			<span class="dashicons dashicons-arrow-down"></span>
			<span class="dashicons dashicons-arrow-up"></span>
		</div>
	</header>

	<section class="llms-collapsible-body">

		<?php
			/**
			 * Action hook fired before access plan's meta box row two
			 *
			 * @since Unknown
			 *
			 * @param LLMS_Access_Plan $plan  LLMS_Access_Plan.
			 * @param integer          $id    Access Plan ID.
			 * @param integer          $order The order of the access plan.
			 */
			do_action( 'llms_access_plan_mb_before_body', $plan, $id, $order );
		?>

		<h4><?php esc_html_e( 'General Plan Information', 'lifterlms' ); ?></h4>
		<?php if ( $plan ) : ?>
			<p class="llms-plan-link"><?php printf( esc_html__( 'Direct to Checkout Purchase Link: %s', 'lifterlms' ), '<code>' . esc_url( $plan->get_checkout_url( false ) ) . '</code>' ); ?></p>
		<?php endif; ?>

		<div class="llms-plan-row-1">

			<div class="llms-metabox-field d-1of2">
				<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][title]">
					<?php esc_html_e( 'Plan Title', 'lifterlms' ); ?>
					<span class="llms-required">*<span class="screen-reader-text"> <?php esc_html_e( 'required', 'lifterlms' ); ?></span></span>
					<span class="screen-reader-text"><?php esc_html_e( 'The title of the access plan, displayed to users at the top of the plan.', 'lifterlms' ); ?></span>
					<span class="tip--top-right" data-tip="<?php esc_attr_e( 'The title of the access plan, displayed to users at the top of the plan.', 'lifterlms' ); ?>">
						<i class="fa fa-question-circle"></i>
					</span>
				</label>
				<input id="_llms_plans[<?php echo esc_attr( $order ); ?>][title]" class="llms-plan-title" name="_llms_plans[<?php echo esc_attr( $order ); ?>][title]" required="required" type="text"<?php echo ( $plan ? ' value="' . esc_attr( $plan->get( 'title' ) ) . '"' : ' disabled="disabled"' ); ?>>
			</div>

			<div class="llms-metabox-field d-1of4">
				<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][enroll_text]">
					<?php esc_html_e( 'Enroll Button Text', 'lifterlms' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'The text displayed on the enrollment button for this access plan.', 'lifterlms' ); ?></span>
					<span class="tip--top-right" data-tip="<?php esc_attr_e( 'The text displayed on the enrollment button for this access plan.', 'lifterlms' ); ?>">
						<i class="fa fa-question-circle"></i>
					</span>
				</label>
				<input id="_llms_plans[<?php echo esc_attr( $order ); ?>][enroll_text]" name="_llms_plans[<?php echo esc_attr( $order ); ?>][enroll_text]" type="text"<?php echo ( $plan ) ? ' value="' . esc_attr( $plan->get( 'enroll_text' ) ) . '"' : ' value="' . esc_attr__( 'Enroll Now', 'lifterlms' ) . '" disabled="disabled"'; ?>>
			</div>

			<div class="llms-metabox-field d-1of4">
				<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][visibility]">
					<?php esc_html_e( 'Visibility', 'lifterlms' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Set whether this access plan is visible, hidden, or featured in the pricing table.', 'lifterlms' ); ?></span>
					<span class="tip--top-left" data-tip="<?php esc_attr_e( 'Set whether this access plan is visible, hidden, or featured in the pricing table.', 'lifterlms' ); ?>">
						<i class="fa fa-question-circle"></i>
					</span>
				</label>
				<select id="_llms_plans[<?php echo esc_attr( $order ); ?>][visibility]" name="_llms_plans[<?php echo esc_attr( $order ); ?>][visibility]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
					<?php foreach ( llms_get_access_plan_visibility_options() as $val => $name ) : ?>
						<option value="<?php echo esc_attr( $val ); ?>"<?php selected( $val, ( $plan ) ? $visibility : null ); ?>><?php echo esc_attr( $name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="clear"></div>

		</div>

		<?php
			/**
			 * Action hook fired after access plan's meta box row two
			 *
			 * @since Unknown
			 *
			 * @param LLMS_Access_Plan $plan  LLMS_Access_Plan.
			 * @param integer          $id    Access Plan ID.
			 * @param integer          $order The order of the access plan.
			 */
			do_action( 'llms_access_plan_mb_after_row_one', $plan, $id, $order );
		?>

		<div class="llms-plan-row-2">

			<div class="llms-metabox-field d-all">
				<label for="_llms_plans_content_<?php echo esc_attr( $id ); ?>">
					<?php esc_html_e( 'Plan Description', 'lifterlms' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Description text of the access plan shown on the pricing table. Bullet points of top plan benefits are often used here. ', 'lifterlms' ); ?></span>
					<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Description text of the access plan shown on the pricing table. Bullet points of top plan benefits are often used here. ', 'lifterlms' ); ?>">
						<i class="fa fa-question-circle"></i>
					</span>
				</label>
				<?php
				wp_editor(
					htmlspecialchars_decode( $plan ? $plan->get( 'content', true ) : '' ),
					'_llms_plans_content_' . $id,
					/**
					 * Filters the access plan editor settings
					 *
					 * @since Unknown
					 *
					 * @param array $settings See _WP_Editors::parse_settings() for description.
					 */
					apply_filters(
						'llms_access_plan_editor_settings',
						array(
							'drag_drop_upload' => true,
							'editor_height'    => 60,
							'media_buttons'    => false,
							'teeny'            => true,
							'textarea_name'    => '_llms_plans[' . $order . '][content]',
							'quicktags'        => array(
								'buttons' => 'strong,em,del,ul,ol,li,close',
							),
						)
					)
				);
				?>
			</div>

			<div class="clear"></div>

		</div>

		<?php
			/**
			 * Action hook fired after access plan's meta box row two
			 *
			 * @since Unknown
			 *
			 * @param LLMS_Access_Plan $plan  LLMS_Access_Plan.
			 * @param integer          $id    Access Plan ID.
			 * @param integer          $order The order of the access plan.
			 */
			do_action( 'llms_access_plan_mb_after_row_two', $plan, $id, $order );
		?>

		<h4><?php esc_html_e( 'Plan Pricing', 'lifterlms' ); ?></h4>

		<div class="llms-plan-row-3">

			<div class="llms-metabox-field d-1of4">
				<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][is_free]">
					<?php esc_html_e( 'Plan Type', 'lifterlms' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Specify if the plan is free or paid.', 'lifterlms' ); ?></span>
					<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Specify if the plan is free or paid.', 'lifterlms' ); ?>">
						<i class="fa fa-question-circle"></i>
					</span>
				</label>
				<select id="_llms_plans[<?php echo esc_attr( $order ); ?>][is_free]" data-controller-id="llms-is-free" name="_llms_plans[<?php echo esc_attr( $order ); ?>][is_free]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
					<option value="yes"<?php selected( 'yes', $plan ? $plan->get( 'is_free' ) : '' ); ?>><?php esc_html_e( 'Free', 'lifterlms' ); ?></option>
					<option value="no"<?php selected( 'no', $plan ? $plan->get( 'is_free' ) : true ); ?>><?php esc_html_e( 'Paid', 'lifterlms' ); ?></option>
				</select>

			</div>

			<div data-controller="llms-is-free" data-value-is="no">

				<div class="llms-metabox-field d-1of6">
					<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][price]">
						<?php esc_html_e( 'Price', 'lifterlms' ); ?>
						<span class="llms-required">*<span class="screen-reader-text"><?php esc_html_e( 'required', 'lifterlms' ); ?></span></span>
						<span class="screen-reader-text"><?php esc_html_e( 'Set the pricing for this access plan.', 'lifterlms' ); ?></span>
						<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Set the pricing for this access plan.', 'lifterlms' ); ?>">
							<i class="fa fa-question-circle"></i>
						</span>
					</label>
					<input id="_llms_plans[<?php echo esc_attr( $order ); ?>][price]" class="llms-plan-price" name="_llms_plans[<?php echo esc_attr( $order ); ?>][price]" min="<?php echo esc_attr( $price_step ); ?>" placeholder="<?php echo esc_attr( strip_tags( llms_price( 1000 ) ) ); ?>" required="required" step="<?php echo esc_attr( $price_step ); ?>" type="number"<?php echo ( $plan ? ' value="' . esc_attr( $plan->get( 'price' ) ) . '"' : ' disabled="disabled"' ); ?>>
				</div>

				<div class="llms-metabox-field d-1of4">
					<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][frequency]">
						<?php esc_html_e( 'Frequency', 'lifterlms' ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Choose how often the payment is charged: one-time or recurring.', 'lifterlms' ); ?></span>
						<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Choose how often the payment is charged: one-time or recurring.', 'lifterlms' ); ?>">
							<i class="fa fa-question-circle"></i>
						</span>
					</label>
					<select id="_llms_plans[<?php echo esc_attr( $order ); ?>][frequency]" data-controller-id="llms-plan-frequency" name="_llms_plans[<?php echo esc_attr( $order ); ?>][frequency]"<?php echo ( $plan ? '' : ' disabled="disabled"' ); ?>>
						<option value="0"<?php selected( '0', ( $plan ) ? $frequency : null ); ?>><?php esc_html_e( 'one-time payment', 'lifterlms' ); ?></option>
						<option value="1"<?php selected( '1', ( $plan ) ? $frequency : null ); ?>><?php esc_html_e( 'every', 'lifterlms' ); ?></option>
						<option value="2"<?php selected( '2', ( $plan ) ? $frequency : null ); ?>><?php esc_html_e( 'every 2nd', 'lifterlms' ); ?></option>
						<option value="3"<?php selected( '3', ( $plan ) ? $frequency : null ); ?>><?php esc_html_e( 'every 3rd', 'lifterlms' ); ?></option>
						<option value="4"<?php selected( '4', ( $plan ) ? $frequency : null ); ?>><?php esc_html_e( 'every 4th', 'lifterlms' ); ?></option>
						<option value="5"<?php selected( '5', ( $plan ) ? $frequency : null ); ?>><?php esc_html_e( 'every 5th', 'lifterlms' ); ?></option>
						<option value="6"<?php selected( '6', ( $plan ) ? $frequency : null ); ?>><?php esc_html_e( 'every 6th', 'lifterlms' ); ?></option>
					</select>
				</div>

				<?php // Recurring plan options. ?>
				<div data-controller="llms-plan-frequency" data-value-is-not="0">

					<div class="llms-metabox-field d-1of6">
						<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][period]">
							<?php esc_html_e( 'Plan Period', 'lifterlms' ); ?>
							<span class="screen-reader-text"><?php esc_html_e( 'Define the billing cycle period for the recurring plan.', 'lifterlms' ); ?></span>
							<span class="tip--top-left" data-tip="<?php esc_attr_e( 'Define the billing cycle period for the recurring plan.', 'lifterlms' ); ?>">
								<i class="fa fa-question-circle"></i>
							</span>
						</label>
						<select id="_llms_plans[<?php echo esc_attr( $order ); ?>][period]" data-controller-id="llms-plan-period" name="_llms_plans[<?php echo esc_attr( $order ); ?>][period]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
							<option value="year"<?php selected( 'year', ( $plan && 0 != $frequency ) ? $period : null ); ?>><?php esc_html_e( 'year', 'lifterlms' ); ?></option>
							<option value="month"<?php selected( 'month', ( $plan && 0 != $frequency ) ? $period : null ); ?>><?php esc_html_e( 'month', 'lifterlms' ); ?></option>
							<option value="week"<?php selected( 'week', ( $plan && 0 != $frequency ) ? $period : null ); ?>><?php esc_html_e( 'week', 'lifterlms' ); ?></option>
							<option value="day"<?php selected( 'day', ( $plan && 0 != $frequency ) ? $period : null ); ?>><?php esc_html_e( 'day', 'lifterlms' ); ?></option>
						</select>
					</div>

					<div class="llms-metabox-field d-1of6">
						<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][length]">
							<?php esc_html_e( 'Plan Length', 'lifterlms' ); ?>
							<span class="screen-reader-text"><?php esc_html_e( 'Specify the duration of the plan period.', 'lifterlms' ); ?></span>
							<span class="tip--top-left" data-tip="<?php esc_attr_e( 'Specify the duration of the plan period.', 'lifterlms' ); ?>">
								<i class="fa fa-question-circle"></i>
							</span>
						</label>
						<select id="_llms_plans[<?php echo esc_attr( $order ); ?>][length]" data-controller="llms-plan-period" data-value-is="year" name="_llms_plans[<?php echo esc_attr( $order ); ?>][length]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
							<option value="0"<?php selected( 0, ( $plan && 'year' === $period ) ? $plan->get( 'length' ) : '' ); ?>><?php esc_html_e( 'for all time', 'lifterlms' ); ?></option>
							<?php $i = 1; while ( $i <= 6 ) : ?>
								<option value="<?php echo esc_attr( $i ); ?>"<?php selected( $i, ( $plan && 'year' === $period ) ? $plan->get( 'length' ) : '' ); ?>><?php printf( esc_html( _n( 'for %s year', 'for %s years', $i, 'lifterlms' ) ), esc_html( $i ) ); ?></option>
								<?php
								++$i;
	endwhile;
							?>
						</select>

						<select data-controller="llms-plan-period" data-value-is="month" name="_llms_plans[<?php echo esc_attr( $order ); ?>][length]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
							<option value="0"<?php selected( 0, ( $plan && 'month' === $period ) ? $plan->get( 'length' ) : '' ); ?>><?php esc_html_e( 'for all time', 'lifterlms' ); ?></option>
							<?php $i = 1; while ( $i <= 24 ) : ?>
								<option value="<?php echo esc_attr( $i ); ?>"<?php selected( $i, ( $plan && 'month' === $period ) ? $plan->get( 'length' ) : '' ); ?>><?php printf( esc_html( _n( 'for %s month', 'for %s months', $i, 'lifterlms' ) ), esc_html( $i ) ); ?></option>
								<?php
								++$i;
	endwhile;
							?>
						</select>

						<select data-controller="llms-plan-period" data-value-is="week" name="_llms_plans[<?php echo esc_attr( $order ); ?>][length]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
							<option value="0"<?php selected( 0, ( $plan && 'week' === $period ) ? $plan->get( 'length' ) : '' ); ?>><?php esc_html_e( 'for all time', 'lifterlms' ); ?></option>
							<?php $i = 1; while ( $i <= 52 ) : ?>
								<option value="<?php echo esc_attr( $i ); ?>"<?php selected( $i, ( $plan && 'week' === $period ) ? $plan->get( 'length' ) : '' ); ?>><?php printf( esc_html( _n( 'for %s week', 'for %s weeks', $i, 'lifterlms' ) ), esc_html( $i ) ); ?></option>
								<?php
								++$i;
	endwhile;
							?>
						</select>

						<select data-controller="llms-plan-period" data-value-is="day" name="_llms_plans[<?php echo esc_attr( $order ); ?>][length]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
							<option value="0"<?php selected( 0, ( $plan && 'day' === $period ) ? $plan->get( 'length' ) : '' ); ?>><?php esc_html_e( 'for all time', 'lifterlms' ); ?></option>
							<?php $i = 1; while ( $i <= 90 ) : ?>
								<option value="<?php echo esc_attr( $i ); ?>"<?php selected( $i, ( $plan && 'day' === $period ) ? $plan->get( 'length' ) : '' ); ?>><?php printf( esc_html( _n( 'for %s day', 'for %s days', $i, 'lifterlms' ) ), esc_html( $i ) ); ?></option>
								<?php
								++$i;
	endwhile;
							?>
						</select>

					</div>

				</div>

			</div>


			<?php
				// If only the manual gateway is enabled, show a notice and link to our Ecommerce Add-ons.
				$active_gateways = llms()->payment_gateways()->get_enabled_payment_gateways();
			if ( 1 === count( $active_gateways ) && array_key_exists( 'manual', $active_gateways ) ) :
				?>
					<div data-controller="llms-is-free" data-value-is="no">

						<div class="d-all">

							<div class="notice notice-warning inline llms-admin-notice">

								<div class="llms-admin-notice-content">
								<?php
									$allowed_ecommerce_add_ons_html = array(
										'a'  => array(
											'href'   => array(),
											'target' => array(),
											'title'  => array(),
											'rel'    => array(),
										),
										'em' => array(),
									);
									printf(
										wp_kses(
											/* translators: %s: URL to the LifterLMS Ecommerce Add-ons page */
											__( 'Your site is not set up to process payments. Check out the <a href="%s" target="_blank">Ecommerce Add-ons for LifterLMS</a> to enable live payments via credit card, PayPal, and more.', 'lifterlms' ),
											$allowed_ecommerce_add_ons_html
										),
										'https://lifterlms.com/product-category/e-commerce/?utm_source=LifterLMS%20Plugin&utm_medium=Access%20Plans&utm_campaign=Plugin%20to%20Sale'
									);
								?>
									<a href="
									<?php
									echo esc_url(
										add_query_arg(
											array(
												'page' => 'llms-settings',
												'tab'  => 'checkout',
											),
											admin_url( 'admin.php' )
										)
									);
									?>
												"><?php esc_html_e( 'View Payment Gateway Settings', 'lifterlms' ); ?>
									</a>
								</div>

							</div>

						</div>

					</div>

					<?php
				endif;
			?>

			<div class="clear"></div>

		</div>

		<?php
			/**
			 * Action hook fired after access plan's meta box row three
			 *
			 * @since Unknown
			 *
			 * @param LLMS_Access_Plan $plan  LLMS_Access_Plan.
			 * @param integer          $id    Access Plan ID.
			 * @param integer          $order The order of the access plan.
			 */
			do_action( 'llms_access_plan_mb_after_row_three', $plan, $id, $order );
		?>

		<div class="llms-plan-row-4" data-controller="llms-plan-frequency" data-value-is-not="0">

			<div class="llms-metabox-field d-1of4">
				<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][trial_offer]">
					<?php esc_html_e( 'Trial Offer', 'lifterlms' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Enable or disable a free or paid trial period for this plan.', 'lifterlms' ); ?></span>
					<span class="tip--top-left" data-tip="<?php esc_attr_e( 'Enable or disable a free or paid trial period for this plan.', 'lifterlms' ); ?>">
						<i class="fa fa-question-circle"></i>
					</span>
				</label>
				<select id="_llms_plans[<?php echo esc_attr( $order ); ?>][trial_offer]" data-controller-id="llms-trial-offer" name="_llms_plans[<?php echo esc_attr( $order ); ?>][trial_offer]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
					<option value="no"<?php selected( 'no', $plan ? $trial_offer : '' ); ?>><?php esc_html_e( 'No trial offer', 'lifterlms' ); ?></option>
					<option value="yes"<?php selected( 'yes', $plan ? $trial_offer : '' ); ?>><?php esc_html_e( 'Enable trial', 'lifterlms' ); ?></option>
				</select>
			</div>

			<div class="llms-metabox-field d-1of6" data-controller="llms-trial-offer" data-value-is="yes">
				<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][trial_price]">
					<?php esc_html_e( 'Trial Price', 'lifterlms' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Set the price for the trial period of this plan.', 'lifterlms' ); ?></span>
					<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Set the price for the trial period of this plan.', 'lifterlms' ); ?>">
						<i class="fa fa-question-circle"></i>
					</span>
				</label>
				<input id="_llms_plans[<?php echo esc_attr( $order ); ?>][trial_price]" name="_llms_plans[<?php echo esc_attr( $order ); ?>][trial_price]" min="0" placeholder="<?php echo esc_attr( strip_tags( llms_price( 1000 ) ) ); ?>" required="required" step="<?php echo esc_attr( $price_step ); ?>" type="text"<?php echo ( $plan ) ? ' value="' . esc_attr( $plan->get( 'trial_price' ) ) . '"' : ' disabled="disabled"'; ?>>
			</div>

			<div class="llms-metabox-field d-1of4" data-controller="llms-trial-offer" data-value-is="yes">
				<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][trial_length]">
					<?php esc_html_e( 'Trial Length', 'lifterlms' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Specify the length of the trial period.', 'lifterlms' ); ?></span>
					<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Specify the length of the trial period.', 'lifterlms' ); ?>">
						<i class="fa fa-question-circle"></i>
					</span>
				</label>
				<input id="_llms_plans[<?php echo esc_attr( $order ); ?>][trial_length]" name="_llms_plans[<?php echo esc_attr( $order ); ?>][trial_length]" min="1" placeholder="1" required="required" type="text"<?php echo ( $plan ) ? ' value="' . esc_attr( $plan->get( 'trial_length' ) ) . '"' : ' value="1" disabled="disabled"'; ?>>
			</div>

			<div class="llms-metabox-field d-1of4" data-controller="llms-trial-offer" data-value-is="yes">
				<label for="_llms_plans[<?php echo $order; ?>][trial_period]">
					<?php esc_html_e( 'Trial Period', 'lifterlms' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Define the time length for the trial period (days, weeks, months, years).', 'lifterlms' ); ?></span>
					<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Define the time length for the trial period (days, weeks, months, years).', 'lifterlms' ); ?>">
						<i class="fa fa-question-circle"></i>
					</span>
				</label>
				<select id="_llms_plans[<?php echo $order; ?>][trial_period]" name="_llms_plans[<?php echo $order; ?>][trial_period]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
					<option value="year"<?php selected( 'year', ( $plan && 'yes' === $trial_offer ) ? $plan->get( 'trial_period' ) : '' ); ?>><?php esc_html_e( 'year(s)', 'lifterlms' ); ?></option>
					<option value="month"<?php selected( 'month', ( $plan && 'yes' === $trial_offer ) ? $plan->get( 'trial_period' ) : '' ); ?>><?php esc_html_e( 'month(s)', 'lifterlms' ); ?></option>
					<option value="week"<?php selected( 'week', ( $plan && 'yes' === $trial_offer ) ? $plan->get( 'trial_period' ) : '' ); ?>><?php esc_html_e( 'week(s)', 'lifterlms' ); ?></option>
					<option value="day"<?php selected( 'day', ( $plan && 'yes' === $trial_offer ) ? $plan->get( 'trial_period' ) : '' ); ?>><?php esc_html_e( 'day(s)', 'lifterlms' ); ?></option>
				</select>
			</div>

			<div class="clear"></div>

		</div>

		<?php
			/**
			 * Action hook fired after access plan's meta box row four
			 *
			 * @since Unknown
			 *
			 * @param LLMS_Access_Plan $plan  LLMS_Access_Plan.
			 * @param integer          $id    Access Plan ID.
			 * @param integer          $order The order of the access plan.
			 */
			do_action( 'llms_access_plan_mb_after_row_four', $plan, $id, $order );
		?>

		<div class="llms-plan-row-5" data-controller="llms-is-free" data-value-is="no">

			<div class="llms-metabox-field d-1of4">
				<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][on_sale]">
					<?php esc_html_e( 'Sale Pricing', 'lifterlms' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Indicate if the plan has a sale.', 'lifterlms' ); ?></span>
					<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Indicate if the plan has a sale.', 'lifterlms' ); ?>">
						<i class="fa fa-question-circle"></i>
					</span>
				</label>
				<select id="_llms_plans[<?php echo esc_attr( $order ); ?>][on_sale]" data-controller-id="llms-on-sale" name="_llms_plans[<?php echo esc_attr( $order ); ?>][on_sale]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
					<option value="no"<?php selected( 'no', $plan ? $on_sale : '' ); ?>><?php esc_html_e( 'Not on sale', 'lifterlms' ); ?></option>
					<option value="yes"<?php selected( 'yes', $plan ? $on_sale : '' ); ?>><?php esc_html_e( 'On Sale', 'lifterlms' ); ?></option>
				</select>
			</div>

			<div class="llms-metabox-field d-1of6" data-controller="llms-on-sale" data-value-is="yes">
				<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][sale_price]">
					<?php esc_html_e( 'Sale Price', 'lifterlms' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Set the discounted price for the sale period.', 'lifterlms' ); ?></span>
					<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Set the discounted price for the sale period.', 'lifterlms' ); ?>">
						<i class="fa fa-question-circle"></i>
					</span>
				</label>
				<input id="_llms_plans[<?php echo esc_attr( $order ); ?>][sale_price]" name="_llms_plans[<?php echo esc_attr( $order ); ?>][sale_price]" min="0" placeholder="<?php echo esc_attr( strip_tags( llms_price( 1000 ) ) ); ?>" required="required" step="<?php echo esc_attr( $price_step ); ?>" type="number"<?php echo ( $plan && 'yes' === $on_sale ) ? ' value="' . esc_attr( $plan->get( 'sale_price' ) ) . '"' : ' disabled="disabled"'; ?>>
			</div>

			<div class="llms-metabox-field d-1of4" data-controller="llms-on-sale" data-value-is="yes">
				<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][sale_start]">
					<?php esc_html_e( 'Sale Start Date', 'lifterlms' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Specify when the sale period starts. ', 'lifterlms' ); ?></span>
					<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Specify when the sale period starts. ', 'lifterlms' ); ?>">
						<i class="fa fa-question-circle"></i>
					</span>
				</label>
				<input id="_llms_plans[<?php echo esc_attr( $order ); ?>][sale_start]" class="llms-access-plan-datepicker" name="_llms_plans[<?php echo esc_attr( $order ); ?>][sale_start]" placeholder="MM/DD/YYYY" type="text"<?php echo ( $plan && 'yes' === $on_sale ) ? ' value="' . esc_attr( $plan->get_date( 'sale_start', 'm/d/Y' ) ) . '"' : ' disabled="disabled"'; ?>>
			</div>

			<div class="llms-metabox-field d-1of4" data-controller="llms-on-sale" data-value-is="yes">
				<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][sale_end]">
					<?php esc_html_e( 'Sale End Date', 'lifterlms' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Specify when the sale period ends.', 'lifterlms' ); ?></span>
					<span class="tip--top-left" data-tip="<?php esc_attr_e( 'Specify when the sale period ends.', 'lifterlms' ); ?>">
						<i class="fa fa-question-circle"></i>
					</span>
				</label>
				<input id="_llms_plans[<?php echo esc_attr( $order ); ?>][sale_end]" class="llms-access-plan-datepicker" name="_llms_plans[<?php echo esc_attr( $order ); ?>][sale_end]" placeholder="MM/DD/YYYY" type="text"<?php echo ( $plan && 'yes' === $on_sale ) ? ' value="' . esc_attr( $plan->get_date( 'sale_end', 'm/d/Y' ) ) . '"' : ' disabled="disabled"'; ?>>
			</div>

			<div class="clear"></div>

		</div>

		<?php
			/**
			 * Action hook fired after access plan's meta box row five
			 *
			 * @since Unknown
			 *
			 * @param LLMS_Access_Plan $plan  LLMS_Access_Plan.
			 * @param integer          $id    Access Plan ID.
			 * @param integer          $order The order of the access plan.
			 */
			do_action( 'llms_access_plan_mb_after_row_five', $plan, $id, $order );
		?>

		<?php
			// Do we have any memberships to restrict this plan to?
			$memberships_count = wp_count_posts( 'llms_membership' );
		if ( $course && $memberships_count->publish > 0 ) :
			?>

				<h4><?php esc_html_e( 'Membership Settings', 'lifterlms' ); ?></h4>

				<div class="llms-plan-row-6">

					<div class="llms-metabox-field d-1of3">
						<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][availability]">
						<?php esc_html_e( 'Plan Availability', 'lifterlms' ); ?>
							<span class="screen-reader-text"><?php esc_html_e( 'Choose who can purchase this plan: anyone or members only.', 'lifterlms' ); ?></span>
							<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Choose who can purchase this plan: anyone or members only.', 'lifterlms' ); ?>">
								<i class="fa fa-question-circle"></i>
							</span>
						</label>
						<select data-controller-id="llms-availability" name="_llms_plans[<?php echo $order; ?>][availability]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
							<option value="open"<?php selected( 'open', $plan ? $availability : '' ); ?>><?php esc_html_e( 'Anyone', 'lifterlms' ); ?></option>
							<option value="members"<?php selected( 'members', $plan ? $availability : '' ); ?>><?php esc_html_e( 'Members only', 'lifterlms' ); ?></option>
						</select>
					</div>

					<div class="llms-metabox-field d-1of2" data-controller="llms-availability" data-value-is="members">
						<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][availability_restrictions][]">
						<?php esc_html_e( 'Memberships', 'lifterlms' ); ?>
							<span class="screen-reader-text"><?php esc_html_e( 'Select the memberships required to access this plan.', 'lifterlms' ); ?></span>
							<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Select the memberships required to access this plan.', 'lifterlms' ); ?>">
								<i class="fa fa-question-circle"></i>
							</span>
						</label>
						<select id="_llms_plans[<?php echo esc_attr( $order ); ?>][availability_restrictions][]" class="llms-availability-restrictions" data-post-type="llms_membership" multiple="multiple" name="_llms_plans[<?php echo esc_attr( $order ); ?>][availability_restrictions][]" required="required" style="width:100%; height: 25px;" <?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
						<?php if ( $plan ) : ?>
								<?php foreach ( $plan->get_array( 'availability_restrictions' ) as $membership_id ) : ?>
									<option value="<?php echo esc_attr( $membership_id ); ?>" selected="selected"><?php echo esc_html( get_the_title( $membership_id ) ); ?> (<?php printf( esc_html__( 'ID# %d', 'lifterlms' ), esc_html( $membership_id ) ); ?>)</option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>

					<div class="clear"></div>

				</div>

				<?php
			endif;
		?>

		<h4><?php esc_html_e( 'Expiration Settings', 'lifterlms' ); ?></h4>

		<div class="llms-plan-row-6">

			<div class="llms-metabox-field d-1of4">
				<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][access_expiration]">
					<?php esc_html_e( 'Access Expiration', 'lifterlms' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Define if and when the access to the plan expires.', 'lifterlms' ); ?></span>
					<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Define if and when the access to the plan expires.', 'lifterlms' ); ?>">
						<i class="fa fa-question-circle"></i>
					</span>
				</label>
				<select id="_llms_plans[<?php echo esc_attr( $order ); ?>][access_expiration]" data-controller-id="llms-access-expiration" name="_llms_plans[<?php echo esc_attr( $order ); ?>][access_expiration]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
					<option value="lifetime"<?php selected( 'lifetime', $plan ? $access_expiration : '' ); ?>><?php esc_html_e( 'Lifetime Access', 'lifterlms' ); ?></option>
					<option value="limited-period"<?php selected( 'limited-period', $plan ? $access_expiration : '' ); ?>><?php esc_html_e( 'Expires after', 'lifterlms' ); ?></option>
					<option value="limited-date"<?php selected( 'limited-date', $plan ? $access_expiration : '' ); ?>><?php esc_html_e( 'Expires on', 'lifterlms' ); ?></option>
				</select>
			</div>

			<div class="llms-metabox-field d-1of6" data-controller="llms-access-expiration" data-value-is="limited-date">
				<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][access_expires]">
					<?php esc_html_e( 'Expiration Date', 'lifterlms' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Specify the exact date when the access from this plan expires.', 'lifterlms' ); ?></span>
					<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Specify the exact date when the access from this plan expires.', 'lifterlms' ); ?>">
						<i class="fa fa-question-circle"></i>
					</span>
				</label>
				<input id="_llms_plans[<?php echo esc_attr( $order ); ?>][access_expires]" class="llms-access-plan-datepicker" name="_llms_plans[<?php echo esc_attr( $order ); ?>][access_expires]" placeholder="MM/DD/YYYY" required="required" type="text"<?php echo ( $plan && 'limited-date' === $access_expiration ) ? ' value="' . esc_attr( $plan->get_date( 'access_expires', 'm/d/Y' ) ) . '"' : ' value="' . esc_attr( date_i18n( 'm/d/y', current_time( 'timestamp' ) ) ) . '" disabled="disabled"'; ?>>
			</div>

			<div class="llms-metabox-field d-1of6" data-controller="llms-access-expiration" data-value-is="limited-period">
				<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][access_length]">
					<?php esc_html_e( 'Access Length', 'lifterlms' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Set the length of the access period for limited-duration plans.', 'lifterlms' ); ?></span>
					<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Set the length of the access period for limited-duration plans.', 'lifterlms' ); ?>">
						<i class="fa fa-question-circle"></i>
					</span>
				</label>
				<input id="_llms_plans[<?php echo esc_attr( $order ); ?>][access_length]" name="_llms_plans[<?php echo esc_attr( $order ); ?>][access_length]" min="1" placeholder="1" required="required" type="number"<?php echo ( $plan ) ? ' value="' . esc_attr( $plan->get( 'access_length' ) ) . '"' : ' value="1" disabled="disabled"'; ?>>
			</div>

			<div class="llms-metabox-field d-1of4" data-controller="llms-access-expiration" data-value-is="limited-period">
				<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][access_period]">
					<?php esc_html_e( 'Access Period', 'lifterlms' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Define the time unit for the access period (days, weeks, months, years).', 'lifterlms' ); ?></span>
					<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Define the time unit for the access period (days, weeks, months, years).', 'lifterlms' ); ?>">
						<i class="fa fa-question-circle"></i>
					</span>
				</label>
				<select id="_llms_plans[<?php echo esc_attr( $order ); ?>][access_period]" name="_llms_plans[<?php echo esc_attr( $order ); ?>][access_period]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
					<option value="year"<?php selected( 'year', ( $plan && 'limited-period' === $access_expiration ) ? $access_period : '' ); ?>><?php esc_html_e( 'year(s)', 'lifterlms' ); ?></option>
					<option value="month"<?php selected( 'month', ( $plan && 'limited-period' === $access_expiration ) ? $access_period : '' ); ?>><?php esc_html_e( 'month(s)', 'lifterlms' ); ?></option>
					<option value="week"<?php selected( 'week', ( $plan && 'limited-period' === $access_expiration ) ? $access_period : '' ); ?>><?php esc_html_e( 'week(s)', 'lifterlms' ); ?></option>
					<option value="day"<?php selected( 'day', ( $plan && 'limited-period' === $access_expiration ) ? $access_period : '' ); ?>><?php esc_html_e( 'day(s)', 'lifterlms' ); ?></option>
				</select>
			</div>

			<div class="clear"></div>

		</div>

		<?php
			/**
			 * Action hook fired after access plan's meta box row six
			 *
			 * @since Unknown
			 *
			 * @param LLMS_Access_Plan $plan  LLMS_Access_Plan.
			 * @param integer          $id    Access Plan ID.
			 * @param integer          $order The order of the access plan.
			 */
			do_action( 'llms_access_plan_mb_after_row_six', $plan, $id, $order );
		?>

		<h4 class="llms-redirection-settings"><?php esc_html_e( 'Redirection Settings', 'lifterlms' ); ?></h4>

		<div class="llms-plan-row-7 llms-redirection-settings">

			<div class="llms-metabox-field d-all" data-controller="llms-availability" data-value-is="members">
				<label>
					<?php esc_html_e( 'Override Membership Redirects', 'lifterlms' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Enable this to override membership redirects with the settings below.', 'lifterlms' ); ?></span>
					<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Enable this to override membership redirects with the settings below.', 'lifterlms' ); ?>">
						<i class="fa fa-question-circle"></i>
					</span>
				</label>
				<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][checkout_redirect_forced]">
					<input id="_llms_plans[<?php echo esc_attr( $order ); ?>][checkout_redirect_forced]" name="_llms_plans[<?php echo esc_attr( $order ); ?>][checkout_redirect_forced]" type="checkbox" value="yes"<?php checked( 'yes', $plan ? $plan->get( 'checkout_redirect_forced' ) : 'no' ); ?>>
					<?php esc_html_e( 'Any redirection set up on the Membership Access Plans will be overridden by the following settings.', 'lifterlms' ); ?>
				</label>
			</div>

			<div class="llms-checkout-redirect-settings">

				<div class="llms-metabox-field d-1of2">
					<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][checkout_redirect_type]">
						<?php esc_html_e( 'Checkout Redirect', 'lifterlms' ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Choose where users are redirected after checkout.', 'lifterlms' ); ?></span>
						<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Choose where users are redirected after checkout.', 'lifterlms' ); ?>">
							<i class="fa fa-question-circle"></i>
						</span>
					</label>
					<select id="_llms_plans[<?php echo esc_attr( $order ); ?>][checkout_redirect_type]" class="llms-checkout-redirect-type" data-controller-id="llms-checkout-redirect-type" name="_llms_plans[<?php echo esc_attr( $order ); ?>][checkout_redirect_type]" required="required" style="width:100%; height: 25px;"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
						<?php $saved_checkout_redirect_type = 'self'; ?>
						<?php if ( $plan ) : ?>
							<?php
							$saved_checkout_redirect_type = ! empty( $plan->get( 'checkout_redirect_type' ) ) ? $plan->get( 'checkout_redirect_type' ) : 'self';
							?>
						<?php endif; ?>
						<?php foreach ( $checkout_redirection_types as $checkout_redirection_type => $checkout_redirection_label ) : ?>
							<option value="<?php echo esc_attr( $checkout_redirection_type ); ?>"<?php selected( $checkout_redirection_type, $saved_checkout_redirect_type ); ?>><?php echo esc_html( $checkout_redirection_label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="llms-metabox-field d-1of2" data-controller="llms-checkout-redirect-type" data-value-is="page">
					<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][checkout_redirect_page]">
						<?php esc_html_e( 'Select a Page', 'lifterlms' ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Select a page to redirect users to after checkout.', 'lifterlms' ); ?></span>
						<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Select a page to redirect users to after checkout.', 'lifterlms' ); ?>">
							<i class="fa fa-question-circle"></i>
						</span>
					</label>
					<select id="_llms_plans[<?php echo esc_attr( $order ); ?>][checkout_redirect_page]" class="llms-checkout-redirect-page" name="_llms_plans[<?php echo esc_attr( $order ); ?>][checkout_redirect_page]" data-post-type="page" style="width:100%; height: 25px;"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
						<?php if ( $plan ) : ?>
							<?php $llms_checkout_redirect_page = $plan->get( 'checkout_redirect_page' ); ?>
							<?php if ( ! empty( $llms_checkout_redirect_page ) ) : ?>
								<option value="<?php echo esc_attr( $llms_checkout_redirect_page ); ?>" selected="selected"><?php echo esc_html( get_the_title( $llms_checkout_redirect_page ) ); ?> ( #<?php echo esc_html( $llms_checkout_redirect_page ); ?>)</option>
							<?php endif; ?>
						<?php endif; ?>
					</select>
				</div>

				<div class="llms-metabox-field d-1of2" data-controller="llms-checkout-redirect-type" data-value-is="url">
					<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][checkout_redirect_url]">
						<?php esc_html_e( 'Enter a URL', 'lifterlms' ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Enter a specific URL to redirect users to after checkout.', 'lifterlms' ); ?></span>
						<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Enter a specific URL to redirect users to after checkout.', 'lifterlms' ); ?>">
							<i class="fa fa-question-circle"></i>
						</span>
					</label>
					<input id="_llms_plans[<?php echo esc_attr( $order ); ?>][checkout_redirect_url]" type="text" class="llms-checkout-redirect-url" name="_llms_plans[<?php echo esc_attr( $order ); ?>][checkout_redirect_url]"<?php echo ( $plan ) ? ' value="' . esc_attr( $plan->get( 'checkout_redirect_url' ) ) . '"' : ' disabled="disabled"'; ?> value="<?php echo ( $plan ) ? esc_attr( $plan->get( 'checkout_redirect_url' ) ) : ''; ?>" />
				</div>

			</div>

			<div class="clear"></div>

		</div>

		<?php if ( llms_parse_bool( get_option( 'llms_access_plans_allow_skus', 'no' ) ) ) : ?>

			<h4><?php esc_html_e( 'Advanced Access Plan Settings', 'lifterlms' ); ?></h4>

			<div class="llms-plan-row-8">

				<div class="llms-metabox-field d-1of4">
					<label for="_llms_plans[<?php echo esc_attr( $order ); ?>][sku]">
						<?php esc_html_e( 'Plan SKU', 'lifterlms' ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Set a unique SKU for this access plan for inventory tracking.', 'lifterlms' ); ?></span>
						<span class="tip--top-right" data-tip="<?php esc_attr_e( 'Set a unique SKU for this access plan for inventory tracking.', 'lifterlms' ); ?>">
							<i class="fa fa-question-circle"></i>
						</span>
					</label>
					<input id="_llms_plans[<?php echo esc_attr( $order ); ?>][sku]" name="_llms_plans[<?php echo esc_attr( $order ); ?>][sku]" type="text"<?php echo ( $plan ? ' value="' . esc_attr( $plan->get( 'sku' ) ) . '"' : ' disabled="disabled"' ); ?>>
				</div>

				<div class="clear"></div>

			</div>

		<?php endif; ?>

		<div class="clear"></div>

		<input class="plan-order" name="_llms_plans[<?php echo esc_attr( $order ); ?>][menu_order]" type="hidden" value="<?php echo ( $plan ) ? esc_attr( $plan->get( 'menu_order' ) ) : esc_attr( $order ); ?>"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
		<input name="_llms_plans[<?php echo esc_attr( $order ); ?>][id]" type="hidden"<?php echo ( $plan ) ? ' value="' . esc_attr( $plan->get( 'id' ) ) . '"' : ' disabled="disabled"'; ?>>

		<?php
			/**
			 * Action hook fired after access plan's meta box body
			 *
			 * @since Unknown
			 *
			 * @param LLMS_Access_Plan $plan  LLMS_Access_Plan.
			 * @param integer          $id    Access Plan ID.
			 * @param integer          $order The order of the access plan.
			 */
			do_action( 'llms_access_plan_mb_after_body', $plan, $id, $order );
		?>

	</section>

</div>
