<?php
/**
 * Individual Access Plan as displayed within the "Product Options" metabox.
 *
 * @package  LifterLMS/Admin/Views
 *
 * @since 3.0.0
 * @since 3.30.0 Added checkout redirect settings.
 * @since 3.31.0 Change sale_price input from text to number to ensure min value validation is properly enforced by browsers.
 *
 * @var obj $course LLMS_Course.
 * @var array $checkout_redirection_types checkout redirect setting options.
 * @var obj $plan LLMS_Access_Plan.
 */

defined( 'ABSPATH' ) || exit;

// create a "step" attribute for price fields according to LLMS settings
$price_step = number_format( 0.01, get_lifterlms_decimals(), get_lifterlms_decimal_separator(), get_lifterlms_thousand_separator() );

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


<div class="llms-metabox-section d-all llms-collapsible llms-access-plan" id="<?php echo $id; ?>"<?php echo $plan ? 'data-id="' . $plan->get( 'id' ) . '"' : ''; ?>>

	<header class="llms-collapsible-header">
		<h3 class="d-1of2">
			<?php if ( $plan ) : ?>
				<span class="llms-plan-title" data-default="<?php _e( 'Unnamed Access Plan', 'lifterlms' ); ?>"><?php echo $plan->get( 'title' ); ?></span>
				<small>(<?php printf( _x( 'ID# %s', 'Product Access Plan ID', 'lifterlms' ), $plan->get( 'id' ) ); ?>)</small>
				<small class="llms-plan-link"><a href="<?php echo esc_url( $plan->get_checkout_url( false ) ); ?>"><?php _e( 'Purchase Link', 'lifterlms' ); ?></a></small>
			<?php else : ?>
				<span class="llms-plan-title" data-default="<?php _e( 'New Access Plan', 'lifterlms' ); ?>"><?php _e( 'New Access Plan', 'lifterlms' ); ?></span>
			<?php endif; ?>
		</h3>
		<div class="d-1of2 d-right">
			<span class="tip--top-left" data-tip="<?php esc_attr_e( 'Errors were found during access plan validation', 'lifterlms' ); ?>">
				<span class="dashicons dashicons-warning"></span>
			</span>
			<span class="dashicons dashicons-arrow-down"></span>
			<span class="dashicons dashicons-arrow-up"></span>
			<span class="dashicons dashicons-menu llms-drag-handle"></span>
			<span class="dashicons dashicons-no llms-plan-delete"></span>
		</div>
	</header>

	<section class="llms-collapsible-body">

		<?php do_action( 'llms_access_plan_mb_before_body', $plan, $id, $order ); ?>

		<div class="llms-plan-row-1">

			<div class="llms-metabox-field d-1of3">
				<label><?php _e( 'Plan Title', 'lifterlms' ); ?><span class="llms-required">*</span></label>
				<input class="llms-plan-title" name="_llms_plans[<?php echo $order; ?>][title]" required="required" type="text"<?php echo ( $plan ) ? ' value="' . $plan->get( 'title' ) . '"' : ' disabled="disabled"'; ?>>
			</div>

			<div class="llms-metabox-field d-1of6">
				<label><?php _e( 'Plan SKU', 'lifterlms' ); ?></label>
				<input name="_llms_plans[<?php echo $order; ?>][sku]" type="text"<?php echo ( $plan ) ? ' value="' . $plan->get( 'sku' ) . '"' : ' disabled="disabled"'; ?>>
			</div>

			<div class="llms-metabox-field d-1of6">
				<label><?php _e( 'Enroll Text', 'lifterlms' ); ?></label>
				<input name="_llms_plans[<?php echo $order; ?>][enroll_text]" placeholder="<?php _e( 'Enroll, Join, Buy...', 'lifterlms' ); ?>" type="text"<?php echo ( $plan ) ? ' value="' . $plan->get( 'enroll_text' ) . '"' : ' disabled="disabled"'; ?>>
			</div>

			<div class="llms-metabox-field d-1of6">
				<label><?php _e( 'Visibility', 'lifterlms' ); ?></label>
				<select name="_llms_plans[<?php echo $order; ?>][visibility]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
					<?php foreach ( llms_get_access_plan_visibility_options() as $val => $name ) : ?>
						<option value="<?php echo esc_attr( $val ); ?>"<?php selected( $val, ( $plan ) ? $visibility : null ); ?>><?php echo esc_attr( $name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="llms-metabox-field d-1of6">
				<label><?php _e( 'Is Free', 'lifterlms' ); ?></label>
				<input data-controller-id="llms-plan-is-free" name="_llms_plans[<?php echo $order; ?>][is_free]" type="checkbox" value="yes"<?php checked( 'yes', $plan ? $plan->get( 'is_free' ) : 'no' ); ?>>
				<em><?php _e( 'No payment required', 'lifterlms' ); ?></em>
			</div>

		</div>

		<div class="clear"></div>

		<?php do_action( 'llms_access_plan_mb_after_row_one', $plan, $id, $order ); ?>

		<div class="llms-plan-row-2" data-controller="llms-plan-is-free" data-value-is-not="yes">

			<div class="llms-metabox-field d-1of4">
				<label><?php _e( 'Price', 'lifterlms' ); ?><span class="llms-required">*</span></label>
				<input class="llms-plan-price" name="_llms_plans[<?php echo $order; ?>][price]" min="<?php echo $price_step; ?>" placeholder="<?php echo strip_tags( llms_price( 99.99 ) ); ?>" required="required" step="<?php echo $price_step; ?>" type="number"<?php echo ( $plan ) ? ' value="' . $plan->get( 'price' ) . '"' : ' disabled="disabled"'; ?>>
			</div>

			<div class="llms-metabox-field d-1of4">
				<label><?php _e( 'Frequency', 'lifterlms' ); ?></label>
				<select data-controller-id="llms-plan-frequency" name="_llms_plans[<?php echo $order; ?>][frequency]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
					<option value="0"<?php selected( '0', ( $plan ) ? $frequency : null ); ?>><?php _e( 'one-time payment', 'lifterlms' ); ?></option>
					<option value="1"<?php selected( '1', ( $plan ) ? $frequency : null ); ?>><?php _e( 'every', 'lifterlms' ); ?></option>
					<option value="2"<?php selected( '2', ( $plan ) ? $frequency : null ); ?>><?php _e( 'every 2nd', 'lifterlms' ); ?></option>
					<option value="3"<?php selected( '3', ( $plan ) ? $frequency : null ); ?>><?php _e( 'every 3rd', 'lifterlms' ); ?></option>
					<option value="4"<?php selected( '4', ( $plan ) ? $frequency : null ); ?>><?php _e( 'every 4th', 'lifterlms' ); ?></option>
					<option value="5"<?php selected( '5', ( $plan ) ? $frequency : null ); ?>><?php _e( 'every 5th', 'lifterlms' ); ?></option>
					<option value="6"<?php selected( '6', ( $plan ) ? $frequency : null ); ?>><?php _e( 'every 6th', 'lifterlms' ); ?></option>
				</select>
			</div>


			<?php // recurring plan options ?>
			<div data-controller="llms-plan-frequency" data-value-is-not="0">

				<div class="llms-metabox-field d-1of4">
					<label><?php _e( 'Plan Period', 'lifterlms' ); ?></label>
					<select data-controller-id="llms-plan-period" name="_llms_plans[<?php echo $order; ?>][period]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
						<option value="year"<?php selected( 'year', ( $plan && 0 != $frequency ) ? $period : null ); ?>><?php _e( 'year', 'lifterlms' ); ?></option>
						<option value="month"<?php selected( 'month', ( $plan && 0 != $frequency ) ? $period : null ); ?>><?php _e( 'month', 'lifterlms' ); ?></option>
						<option value="week"<?php selected( 'week', ( $plan && 0 != $frequency ) ? $period : null ); ?>><?php _e( 'week', 'lifterlms' ); ?></option>
						<option value="day"<?php selected( 'day', ( $plan && 0 != $frequency ) ? $period : null ); ?>><?php _e( 'day', 'lifterlms' ); ?></option>
					</select>
				</div>

				<div class="llms-metabox-field d-1of4">
					<label><?php _e( 'Plan Length', 'lifterlms' ); ?></label>
					<select data-controller="llms-plan-period" data-value-is="year" name="_llms_plans[<?php echo $order; ?>][length]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
						<option value="0"<?php selected( 0, ( $plan && 'year' === $period ) ? $plan->get( 'length' ) : '' ); ?>><?php _e( 'for all time', 'lifterlms' ); ?></option>
						<?php $i = 1; while ( $i <= 6 ) : ?>
							<option value="<?php echo $i; ?>"<?php selected( $i, ( $plan && 'year' === $period ) ? $plan->get( 'length' ) : '' ); ?>><?php printf( _n( 'for %s year', 'for %s years', $i, 'lifterlms' ), $i ); ?></option>
							<?php
							$i++;
endwhile;
						?>
					</select>

					<select data-controller="llms-plan-period" data-value-is="month" name="_llms_plans[<?php echo $order; ?>][length]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
						<option value="0"<?php selected( 0, ( $plan && 'month' === $period ) ? $plan->get( 'length' ) : '' ); ?>><?php _e( 'for all time', 'lifterlms' ); ?></option>
						<?php $i = 1; while ( $i <= 24 ) : ?>
							<option value="<?php echo $i; ?>"<?php selected( $i, ( $plan && 'month' === $period ) ? $plan->get( 'length' ) : '' ); ?>><?php printf( _n( 'for %s month', 'for %s months', $i, 'lifterlms' ), $i ); ?></option>
							<?php
							$i++;
endwhile;
						?>
					</select>

					<select data-controller="llms-plan-period" data-value-is="week" name="_llms_plans[<?php echo $order; ?>][length]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
						<option value="0"<?php selected( 0, ( $plan && 'week' === $period ) ? $plan->get( 'length' ) : '' ); ?>><?php _e( 'for all time', 'lifterlms' ); ?></option>
						<?php $i = 1; while ( $i <= 52 ) : ?>
							<option value="<?php echo $i; ?>"<?php selected( $i, ( $plan && 'week' === $period ) ? $plan->get( 'length' ) : '' ); ?>><?php printf( _n( 'for %s week', 'for %s weeks', $i, 'lifterlms' ), $i ); ?></option>
							<?php
							$i++;
endwhile;
						?>
					</select>

					<select data-controller="llms-plan-period" data-value-is="day" name="_llms_plans[<?php echo $order; ?>][length]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
						<option value="0"<?php selected( 0, ( $plan && 'day' === $period ) ? $plan->get( 'length' ) : '' ); ?>><?php _e( 'for all time', 'lifterlms' ); ?></option>
						<?php $i = 1; while ( $i <= 90 ) : ?>
							<option value="<?php echo $i; ?>"<?php selected( $i, ( $plan && 'day' === $period ) ? $plan->get( 'length' ) : '' ); ?>><?php printf( _n( 'for %s day', 'for %s days', $i, 'lifterlms' ), $i ); ?></option>
							<?php
							$i++;
endwhile;
						?>
					</select>
				</div>

			</div>

		</div>

		<div class="clear"></div>

		<?php do_action( 'llms_access_plan_mb_after_row_two', $plan, $id, $order ); ?>

		<div class="llms-plan-row-3">
			<div class="d-1of2">

				<div class="llms-metabox-field d-1of2">
					<label><?php _e( 'Access Expiration', 'lifterlms' ); ?></label>
					<select data-controller-id="llms-access-expiration" name="_llms_plans[<?php echo $order; ?>][access_expiration]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
						<option value="lifetime"<?php selected( 'lifetime', $plan ? $access_expiration : '' ); ?>><?php _e( 'Lifetime Access', 'lifterlms' ); ?></option>
						<option value="limited-period"<?php selected( 'limited-period', $plan ? $access_expiration : '' ); ?>><?php _e( 'Expires after', 'lifterlms' ); ?></option>
						<option value="limited-date"<?php selected( 'limited-date', $plan ? $access_expiration : '' ); ?>><?php _e( 'Expires on', 'lifterlms' ); ?></option>
					</select>
				</div>

				<div class="llms-metabox-field d-1of4" data-controller="llms-access-expiration" data-value-is="limited-date">
					<label>&nbsp;</label>
					<input class="llms-access-plan-datepicker" name="_llms_plans[<?php echo $order; ?>][access_expires]" placeholder="MM/DD/YYYY" required="required" type="text"<?php echo ( $plan && 'limited-date' === $access_expiration ) ? ' value="' . $plan->get_date( 'access_expires', 'm/d/Y' ) . '"' : ' value="' . date_i18n( 'm/d/y', current_time( 'timestamp' ) ) . '" disabled="disabled"'; ?>>
				</div>

				<div class="llms-metabox-field d-1of6" data-controller="llms-access-expiration" data-value-is="limited-period">
					<label>&nbsp;</label>
					<input name="_llms_plans[<?php echo $order; ?>][access_length]" min="1" placeholder="1" required="required" type="number"<?php echo ( $plan ) ? ' value="' . $plan->get( 'access_length' ) . '"' : ' value="1" disabled="disabled"'; ?>>
				</div>

				<div class="llms-metabox-field d-1of4" data-controller="llms-access-expiration" data-value-is="limited-period">
					<label>&nbsp;</label>
					<select name="_llms_plans[<?php echo $order; ?>][access_period]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
						<option value="year"<?php selected( 'year', ( $plan && 'limited-period' === $access_expiration ) ? $access_period : '' ); ?>><?php _e( 'year(s)', 'lifterlms' ); ?></option>
						<option value="month"<?php selected( 'month', ( $plan && 'limited-period' === $access_expiration ) ? $access_period : '' ); ?>><?php _e( 'month(s)', 'lifterlms' ); ?></option>
						<option value="week"<?php selected( 'week', ( $plan && 'limited-period' === $access_expiration ) ? $access_period : '' ); ?>><?php _e( 'week(s)', 'lifterlms' ); ?></option>
						<option value="day"<?php selected( 'day', ( $plan && 'limited-period' === $access_expiration ) ? $access_period : '' ); ?>><?php _e( 'day(s)', 'lifterlms' ); ?></option>
					</select>
				</div>

			</div>

			<?php if ( $course ) : ?>

				<div class="d-1of2">

					<div class="llms-metabox-field d-1of3">
						<label><?php _e( 'Plan Availability', 'lifterlms' ); ?></label>
						<select data-controller-id="llms-availability" name="_llms_plans[<?php echo $order; ?>][availability]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
							<option value="open"<?php selected( 'open', $plan ? $availability : '' ); ?>><?php _e( 'Anyone', 'lifterlms' ); ?></option>
							<option value="members"<?php selected( 'members', $plan ? $availability : '' ); ?>><?php _e( 'Members only', 'lifterlms' ); ?></option>
						</select>
					</div>

					<div class="llms-metabox-field d-2of3" data-controller="llms-availability" data-value-is="members">
						<label><?php _e( 'Memberships', 'lifterlms' ); ?></label>
						<select class="llms-availability-restrictions" data-post-type="llms_membership" multiple="multiple" name="_llms_plans[<?php echo $order; ?>][availability_restrictions][]" required="required" style="width:100%; height: 25px;" <?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
							<?php if ( $plan ) : ?>
								<?php foreach ( $plan->get_array( 'availability_restrictions' ) as $membership_id ) : ?>
									<option value="<?php echo $membership_id; ?>" selected="selected"><?php echo get_the_title( $membership_id ); ?> (<?php printf( __( 'ID# %d', 'lifterlms' ), $membership_id ); ?>)</option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>

				</div>

			<?php endif; ?>
		</div>

		<div class="clear"></div>

		<?php do_action( 'llms_access_plan_mb_after_row_three', $plan, $id, $order ); ?>

		<div class="llms-plan-row-4" data-controller="llms-plan-frequency" data-value-is-not="0">
			<div class="llms-metabox-field d-1of5">
				<label><?php _e( 'Trial Offer', 'lifterlms' ); ?></label>
				<select data-controller-id="llms-trial-offer" name="_llms_plans[<?php echo $order; ?>][trial_offer]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
					<option value="no"<?php selected( 'no', $plan ? $trial_offer : '' ); ?>><?php _e( 'No trial offer', 'lifterlms' ); ?></option>
					<option value="yes"<?php selected( 'yes', $plan ? $trial_offer : '' ); ?>><?php _e( 'Enable trial', 'lifterlms' ); ?></option>
				</select>
			</div>

			<div class="llms-metabox-field d-1of6" data-controller="llms-trial-offer" data-value-is="yes">
				<label><?php _e( 'Trial Price', 'lifterlms' ); ?></label>
				<input name="_llms_plans[<?php echo $order; ?>][trial_price]" min="0" placeholder="<?php echo strip_tags( llms_price( 99.99 ) ); ?>" required="required" step="<?php echo $price_step; ?>" type="text"<?php echo ( $plan ) ? ' value="' . $plan->get( 'trial_price' ) . '"' : ' disabled="disabled"'; ?>>
			</div>

			<div class="llms-metabox-field d-1of6" data-controller="llms-trial-offer" data-value-is="yes">
				<label><?php _e( 'Trial Length', 'lifterlms' ); ?></label>
				<input name="_llms_plans[<?php echo $order; ?>][trial_length]" min="1" placeholder="1" required="required" type="text"<?php echo ( $plan ) ? ' value="' . $plan->get( 'trial_length' ) . '"' : ' value="1" disabled="disabled"'; ?>>
			</div>

			<div class="llms-metabox-field d-1of6" data-controller="llms-trial-offer" data-value-is="yes">
				<label>&nbsp;</label>
				<select name="_llms_plans[<?php echo $order; ?>][trial_period]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
					<option value="year"<?php selected( 'year', ( $plan && 'yes' === $trial_offer ) ? $plan->get( 'trial_period' ) : '' ); ?>><?php _e( 'year(s)', 'lifterlms' ); ?></option>
					<option value="month"<?php selected( 'month', ( $plan && 'yes' === $trial_offer ) ? $plan->get( 'trial_period' ) : '' ); ?>><?php _e( 'month(s)', 'lifterlms' ); ?></option>
					<option value="week"<?php selected( 'week', ( $plan && 'yes' === $trial_offer ) ? $plan->get( 'trial_period' ) : '' ); ?>><?php _e( 'week(s)', 'lifterlms' ); ?></option>
					<option value="day"<?php selected( 'day', ( $plan && 'yes' === $trial_offer ) ? $plan->get( 'trial_period' ) : '' ); ?>><?php _e( 'day(s)', 'lifterlms' ); ?></option>
				</select>
			</div>
		</div>

		<div class="clear"></div>

		<?php do_action( 'llms_access_plan_mb_after_row_four', $plan, $id, $order ); ?>

		<div class="llms-plan-row-5" data-controller="llms-plan-is-free" data-value-is-not="yes">
			<div class="llms-metabox-field d-1of5">
				<label><?php _e( 'Sale Pricing', 'lifterlms' ); ?></label>
				<select data-controller-id="llms-on-sale" name="_llms_plans[<?php echo $order; ?>][on_sale]"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
					<option value="no"<?php selected( 'no', $plan ? $on_sale : '' ); ?>><?php _e( 'Not on sale', 'lifterlms' ); ?></option>
					<option value="yes"<?php selected( 'yes', $plan ? $on_sale : '' ); ?>><?php _e( 'On Sale', 'lifterlms' ); ?></option>
				</select>
			</div>

			<div class="llms-metabox-field d-1of6" data-controller="llms-on-sale" data-value-is="yes">
				<label><?php _e( 'Sale Price', 'lifterlms' ); ?></label>
				<input name="_llms_plans[<?php echo $order; ?>][sale_price]" min="0" placeholder="<?php echo strip_tags( llms_price( 99.99 ) ); ?>" required="required" step="<?php echo $price_step; ?>" type="number"<?php echo ( $plan && 'yes' === $on_sale ) ? ' value="' . $plan->get( 'sale_price' ) . '"' : ' disabled="disabled"'; ?>>
			</div>

			<div class="llms-metabox-field d-1of6" data-controller="llms-on-sale" data-value-is="yes">
				<label><?php _e( 'Sale Start Date', 'lifterlms' ); ?></label>
				<input class="llms-access-plan-datepicker" name="_llms_plans[<?php echo $order; ?>][sale_start]" placeholder="MM/DD/YYYY" type="text"<?php echo ( $plan && 'yes' === $on_sale ) ? ' value="' . $plan->get_date( 'sale_start', 'm/d/Y' ) . '"' : ' disabled="disabled"'; ?>>
			</div>

			<div class="llms-metabox-field d-1of6" data-controller="llms-on-sale" data-value-is="yes">
				<label><?php _e( 'Sale End Date', 'lifterlms' ); ?></label>
				<input class="llms-access-plan-datepicker" name="_llms_plans[<?php echo $order; ?>][sale_end]" placeholder="MM/DD/YYYY" type="text"<?php echo ( $plan && 'yes' === $on_sale ) ? ' value="' . $plan->get_date( 'sale_end', 'm/d/Y' ) . '"' : ' disabled="disabled"'; ?>>
			</div>
		</div>

		<div class="clear"></div>

		<?php do_action( 'llms_access_plan_mb_after_row_five', $plan, $id, $order ); ?>

		<div class="llms-plan-row-6 llms-metabox-field d-all">
			<label><?php _e( 'Plan Description', 'lifterlms' ); ?></label>
			<?php
			wp_editor(
				htmlspecialchars_decode( $plan ? $plan->get( 'content' ) : '' ),
				'_llms_plans_content_' . $id,
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

		<?php do_action( 'llms_access_plan_mb_after_row_six', $plan, $id, $order ); ?>

		<div class="llms-plan-row-7">
			<div class="llms-metabox-field d-all" data-controller="llms-availability" data-value-is="members">
				<label><?php _e( 'Override Membership Redirects', 'lifterlms' ); ?></label>
				<input name="_llms_plans[<?php echo $order; ?>][checkout_redirect_forced]" type="checkbox" value="yes"<?php checked( 'yes', $plan ? $plan->get( 'checkout_redirect_forced' ) : 'no' ); ?>>
				<em><?php _e( 'Any redirection set up on the Membership Access Plans will be overridden by the following settings.', 'lifterlms' ); ?></em>
			</div>
			<div class="llms-metabox-field d-all llms-checkout-redirect-settings">
			<div class="llms-metabox-field d-1of2">
				<label><?php _e( 'Checkout redirect', 'lifterlms' ); ?></label>
				<select class="llms-checkout-redirect-type" data-controller-id="llms-checkout-redirect-type" name="_llms_plans[<?php echo $order; ?>][checkout_redirect_type]" required="required" style="width:100%; height: 25px;"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
					<?php $saved_checkout_redirect_type = 'self'; ?>
					<?php if ( $plan ) : ?>
						<?php
						$saved_checkout_redirect_type = ! empty( $plan->get( 'checkout_redirect_type' ) ) ? $plan->get( 'checkout_redirect_type' ) : 'self';
						?>
					<?php endif; ?>
					<?php foreach ( $checkout_redirection_types as $checkout_redirection_type => $checkout_redirection_label ) : ?>
						<option value="<?php echo $checkout_redirection_type; ?>"<?php selected( $checkout_redirection_type, $saved_checkout_redirect_type ); ?>><?php echo $checkout_redirection_label; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="llms-metabox-field d-1of2" data-controller="llms-checkout-redirect-type" data-value-is="page">
				<label><?php _e( 'Select a page', 'lifterlms' ); ?></label>
				<select class="llms-checkout-redirect-page" name="_llms_plans[<?php echo $order; ?>][checkout_redirect_page]" data-post-type="page" style="width:100%; height: 25px;"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
					<?php if ( $plan ) : ?>
						<?php $llms_checkout_redirect_page = $plan->get( 'checkout_redirect_page' ); ?>
						<?php if ( ! empty( $llms_checkout_redirect_page ) ) : ?>
							<option value="<?php echo $llms_checkout_redirect_page; ?>" selected="selected"><?php echo get_the_title( $llms_checkout_redirect_page ); ?> ( #<?php echo $llms_checkout_redirect_page; ?>)</option>
						<?php endif; ?>
					<?php endif; ?>
				<select>
			</div>
			<div class="llms-metabox-field d-1of2" data-controller="llms-checkout-redirect-type" data-value-is="url">
				<label><?php _e( 'Enter a URL', 'lifterlms' ); ?></label>
				<input type="text" class="llms-checkout-redirect-url" name="_llms_plans[<?php echo $order; ?>][checkout_redirect_url]"<?php echo ( $plan ) ? ' value="' . $plan->get( 'checkout_redirect_url' ) . '"' : ' disabled="disabled"'; ?> value="<?php echo ( $plan ) ? $plan->get( 'checkout_redirect_url' ) : ''; ?>" />
			</div>
			</div>
		</div>

		<div class="clear"></div>

		<input class="plan-order" name="_llms_plans[<?php echo $order; ?>][menu_order]" type="hidden" value="<?php echo ( $plan ) ? $plan->get( 'menu_order' ) : $order; ?>"<?php echo ( $plan ) ? '' : ' disabled="disabled"'; ?>>
		<input name="_llms_plans[<?php echo $order; ?>][id]" type="hidden"<?php echo ( $plan ) ? ' value="' . $plan->get( 'id' ) . '"' : ' disabled="disabled"'; ?>>

		<?php do_action( 'llms_access_plan_mb_after_body', $plan, $id, $order ); ?>

	</section>

</div>
