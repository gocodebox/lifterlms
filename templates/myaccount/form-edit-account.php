<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$user_id = get_current_user_id();

if ( 'yes' === get_option( 'lifterlms_registration_require_address' ) ) {
	$billing_address_1  = ( get_user_meta( $user_id, 'llms_billing_address_1' ) )      ? get_user_meta( $user_id, 'llms_billing_address_1', true ) : '';
	$billing_address_2  = ( get_user_meta( $user_id, 'llms_billing_address_2' )  )     ? get_user_meta( $user_id, 'llms_billing_address_2', true ) : '';
	$billing_city       = ( get_user_meta( $user_id, 'llms_billing_city' ) )           ? get_user_meta( $user_id, 'llms_billing_city', true )      : '';
	$billing_state      = ( get_user_meta( $user_id, 'llms_billing_state' ) )          ? get_user_meta( $user_id, 'llms_billing_state', true )     : '';
	$billing_zip        = ( get_user_meta( $user_id, 'llms_billing_zip' ) )            ? get_user_meta( $user_id, 'llms_billing_zip', true )       : '';
	$billing_country    = ( get_user_meta( $user_id, 'llms_billing_country' ) )        ? get_user_meta( $user_id, 'llms_billing_country', true )   : '';
}

if ( 'yes' === get_option( 'lifterlms_registration_add_phone' ) ) {
	$phone              = ( get_user_meta( $user_id, 'llms_phone' ) )                  ? get_user_meta( $user_id, 'llms_phone', true )             : '';
}
?>

<?php llms_print_notices(); ?>

<?php do_action( 'lifterlms_my_account_navigation' ); ?>

<?php do_action( 'lifterlms_edit_account_start' ); ?>

<form action="" class="llms-person-information-form" method="post">

	<div class="llms-basic-information">

		<h3><?php _e( 'Basic Information', 'lifterlms' ) ?></h3>

		<p class="form-row form-row-first">
			<label for="account_first_name"><?php _e( 'First name', 'lifterlms' ); ?></label>
			<input type="text" class="input-text llms-input-text" name="account_first_name" id="account_first_name" value="<?php echo esc_attr( $user->first_name ); ?>" />
		</p>
		<p class="form-row form-row-last">
			<label for="account_last_name"><?php _e( 'Last name', 'lifterlms' ); ?></label>
			<input type="text" class="input-text llms-input-text" name="account_last_name" id="account_last_name" value="<?php echo esc_attr( $user->last_name ); ?>" />
		</p>
		<p class="form-row form-row-wide">
			<label for="account_email"><?php _e( 'Email address', 'lifterlms' ); ?></label>
			<input type="email" class="input-text llms-input-text" name="account_email" id="account_email" value="<?php echo esc_attr( $user->user_email ); ?>" />
		</p>

		</div>

		<div class="llms-change-password">

		<h3><?php _e( 'Change Password', 'lifterlms' ) ?></h3>

		<p class="form-row form-row-first">
			<label for="password_1"><?php _e( 'Password (leave blank to leave unchanged)', 'lifterlms' ); ?></label>
			<input type="password" class="input-text llms-input-text" name="password_1" id="password_1" />
		</p>
		<p class="form-row form-row-last">
			<label for="password_2"><?php _e( 'Confirm new password', 'lifterlms' ); ?></label>
			<input type="password" class="input-text llms-input-text" name="password_2" id="password_2" />
		</p>

	</div>


	<?php if ( 'yes' === get_option( 'lifterlms_registration_require_address' ) ) : ?>

		<div class="llms-billing-information">

			<h3><?php _e( 'Billing Information', 'lifterlms' ) ?></h3>
			<p>
				<label for="billing_address_1"><?php _e( 'Billing Address 1', 'lifterlms' ); ?> <span class="required">*</span></label>
				<input type="text" class="input-text llms-input-text" name="billing_address_1" id="billing_address_1" value="<?php echo $billing_address_1; ?>" />
			</p>
			<p>
				<label for="billing_address_2"><?php _e( 'Billing Address 2', 'lifterlms' ); ?></label>
				<input type="text" class="input-text llms-input-text" name="billing_address_2" id="billing_address_2" value="<?php echo $billing_address_2; ?>" />
			</p>
			<p>
				<label for="billing_city"><?php _e( 'Billing City', 'lifterlms' ); ?> <span class="required">*</span></label>
				<input type="text" class="input-text llms-input-text" name="billing_city" id="billing_city" value="<?php echo $billing_city; ?>" />
			</p>
			<p>
				<label for="billing_state"><?php _e( 'Billing State', 'lifterlms' ); ?> <span class="required">*</span></label>
				<input type="text" class="input-text llms-input-text" name="billing_state" id="billing_state" value="<?php echo $billing_state; ?>" />
			</p>
			<p>
				<label for="billing_zip"><?php _e( 'Billing Zip', 'lifterlms' ); ?> <span class="required">*</span></label>
				<input type="text" class="input-text llms-input-text" name="billing_zip" id="billing_address_1" value="<?php echo $billing_zip; ?>" />
			</p>
			<p>
				<label for="billing_country"><?php _e( 'Billing Country', 'lifterlms' ); ?> <span class="required">*</span></label>
				<select id="llms_country_options" name="billing_country">
				<?php $country_options = get_lifterlms_countries();
				foreach ( $country_options as $code => $name ) : ?>
						<?php if ($billing_country == $code) : ?>
							<option value="<?php echo $code; ?>" selected><?php echo $name; ?></option>
						<?php else : ?>
						<option value="<?php echo $code; ?>"><?php echo $name; ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
			</p>

		</div>
	<?php endif; ?>

	<?php if ( 'yes' === get_option( 'lifterlms_registration_add_phone' ) ) : ?>
		<div class="llms-form-item-wrapper phone">
			<label for="llms_phone"><?php _e( 'Phone', 'lifterlms' ); ?></label>
			<input type="text" class="input-text llms-input-text" name="phone" id="llms_phone" value="<?php echo $phone; ?>" />
		</div>
	<?php endif; ?>

	<?php do_action( 'lifterlms_edit_account_form_end' ); ?>
	<div class="clear"></div>

	<p><input type="submit" class="button" name="save_account_details" value="<?php _e( 'Save changes', 'lifterlms' ); ?>" /></p>

	<?php wp_nonce_field( 'save_account_details' ); ?>
	<input type="hidden" name="action" value="save_account_details" />
</form>
<?php do_action( 'lifterlms_edit_account_end' ); ?>
