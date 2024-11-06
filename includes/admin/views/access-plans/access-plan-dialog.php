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
				<strong><?php echo esc_html( __( 'Free', 'lifterlms' ) ); ?></strong>
				<span><?php echo esc_html( __( 'Free access that never expires.', 'lifterlms' ) ); ?></span>
			</button>
			<button class="template" data-template="monthly">
				<strong><?php echo esc_html( __( 'Monthly', 'lifterlms' ) ); ?></strong>
				<span><?php echo esc_html( __( 'Charge a recurring monthly subscription that never ends.', 'lifterlms' ) ); ?></span>
			</button>
			<button class="template" data-template="annual">
				<strong><?php echo esc_html( __( 'Annual', 'lifterlms' ) ); ?></strong>
				<span><?php echo esc_html( __( 'Charge a recurring annual subscription that never ends.', 'lifterlms' ) ); ?></span>
			</button>
			<button class="template" data-template="one-time">
				<strong><?php echo esc_html( __( 'One Time', 'lifterlms' ) ); ?></strong>
				<span><?php echo esc_html( __( 'Charge a one-time payment for a fixed period.', 'lifterlms' ) ); ?></span>
			</button>
			<button class="template" data-template="lifetime">
				<strong><?php echo esc_html( __( 'Lifetime', 'lifterlms' ) ); ?></strong>
				<span><?php echo esc_html( __( 'Charge a one-time payment that never expires.', 'lifterlms' ) ); ?></span>
			</button>
			<button class="template" data-template="paid-trial">
				<strong><?php echo esc_html( __( 'Paid Trial', 'lifterlms' ) ); ?></strong>
				<span><?php echo esc_html( __( 'Charge a fee for trial access and capture recurring payment info with a future monthly subscription that will start in 1 week.', 'lifterlms' ) ); ?></span>
			</button>
			<button class="template" data-template="free-trial">
				<strong><?php echo esc_html( __( 'Free Trial', 'lifterlms' ) ); ?></strong>
				<span><?php echo esc_html( __( 'Grant free access for 1 week with a future monthly subscription that will start in 1 week.', 'lifterlms' ) ); ?></span>
			</button>
			<button class="template" data-template="hidden-access">
				<strong><?php echo esc_html( __( 'Hidden Access', 'lifterlms' ) ); ?></strong>
				<span><?php echo esc_html( __( 'Grant free access without making this plan publicly available.', 'lifterlms' ) ); ?></span>
			</button>
			<button class="template" data-template="sale">
				<strong><?php echo esc_html( __( 'Sale', 'lifterlms' ) ); ?></strong>
				<span><?php echo esc_html( __( 'Discount a one-time payment for lifetime access.', 'lifterlms' ) ); ?></span>
			</button>
			<button class="template" data-template="presell">
				<strong><?php echo esc_html( __( 'Pre-sale', 'lifterlms' ) ); ?></strong>
				<span><?php echo esc_html( __( 'Offer lifetime access for a one-time payment with a future start date.', 'lifterlms' ) ); ?></span>
			</button>
			<?php
			/**
			 * Action hook fired after access plan's dialog box pre-sale option.
			 *
			 * @since [version]
			 */
			do_action( 'llms_access_plan_dialog_after_pre_sale' );
			?>
			<?php if ( apply_filters( 'llms_access_plan_dialog_show_group_addon_option', true ) ) : ?>
				<a target="_blank" href="https://lifterlms.com/product/groups/?utm_source=LifterLMS%20Plugin&utm_medium=Access%20Plans&utm_campaign=Plugin%20to%20Sale">
					<span class="add-on"><?php echo esc_html( __( 'Add-on', 'lifterlms' ) ); ?></span>
					<strong><?php echo esc_html( __( 'Group Access', 'lifterlms' ) ); ?></strong>
					<span><?php echo esc_html( __( 'Allow a buyer to purchase lifetime access for a group of people.', 'lifterlms' ) ); ?></span>
				</a>
			<?php endif; ?>
			<button class="template" data-template="advanced">
				<strong><?php echo esc_html( __( 'Advanced', 'lifterlms' ) ); ?></strong>
				<span><?php echo esc_html( __( 'Show all settings to create an access plan from scratch.', 'lifterlms' ) ); ?></span>
			</button>
		</div>
	</div>
</div>
