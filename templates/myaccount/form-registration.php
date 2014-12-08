<?php
/**
 * Registration Form
 *
 * @author 		lifterLMS
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$product_id = get_query_var( 'product-id' );
global $wpdb;
?>

<?php if ( get_option( 'lifterlms_enable_myaccount_registration' ) === 'yes' ) : ?>

	<?php do_action( 'lifterlms_before_person_register_form' ); ?>

	<div class="col-2">

		<h2 class="llms-title llms-h2"><?php _e( 'Register', 'lifterlms' ); ?></h2>

		<form method="post" class="llms-new-person-form register">
		
			<?php do_action( 'lifterlms_register_form_start' ); ?>

			<?php if ( 'no' === get_option( 'lifterlms_registration_generate_username' ) ) : ?>

				<div class="llms-form-item-wrapper">
					<label for="reg_username"><?php _e( 'Username', 'lifterlms' ); ?> <span class="required">*</span></label>
					<input type="text" class="input-text llms-input-text" name="username" id="reg_username" value="<?php if ( ! empty( $_POST['username'] ) ) echo esc_attr( $_POST['username'] ); ?>" />
				</div>

			<?php endif; ?>

			<?php if ( 'yes' === get_option( 'lifterlms_registration_require_name' ) ) : ?>

				<div class="llms-form-item-wrapper">
					<label for="reg_firstname"><?php _e( 'First Name', 'lifterlms' ); ?> <span class="required">*</span></label>
					<input type="text" class="input-text llms-input-text" name="firstname" id="reg_firstname" value="<?php if ( ! empty( $_POST['firstname'] ) ) echo esc_attr( $_POST['firstname'] ); ?>" />
				</div>

				<div class="llms-form-item-wrapper">
					<label for="reg_lastname"><?php _e( 'Last Name', 'lifterlms' ); ?> <span class="required">*</span></label>
					<input type="text" class="input-text llms-input-text" name="lastname" id="reg_lastname" value="<?php if ( ! empty( $_POST['lastname'] ) ) echo esc_attr( $_POST['lastname'] ); ?>" />
				</div>
			<?php endif; ?>

			<?php do_action( 'lifterlms_register_form_after_names' ); ?>

			<?php if ( 'yes' === get_option( 'lifterlms_registration_require_address' ) ) : ?>
				<div class="llms-form-item-wrapper">
					<label for="billing_address_1"><?php _e( 'Billing Address 1', 'lifterlms' ); ?> <span class="required">*</span></label>
					<input type="text" class="input-text llms-input-text" name="billing_address_1" id="billing_address_1" value="<?php if ( ! empty( $_POST['billing_address_1'] ) ) echo esc_attr( $_POST['billing_address_1'] ); ?>" />
				</div>
				<div class="llms-form-item-wrapper">
					<label for="billing_address_2"><?php _e( 'Billing Address 2', 'lifterlms' ); ?></label>
					<input type="text" class="input-text llms-input-text" name="billing_address_2" id="billing_address_2" value="<?php if ( ! empty( $_POST['billing_address_2'] ) ) echo esc_attr( $_POST['billing_address_2'] ); ?>" />
				</div>
				<div class="llms-form-item-wrapper">
					<label for="billing_city"><?php _e( 'Billing City', 'lifterlms' ); ?> <span class="required">*</span></label>
					<input type="text" class="input-text llms-input-text" name="billing_city" id="billing_city" value="<?php if ( ! empty( $_POST['billing_city'] ) ) echo esc_attr( $_POST['billing_city'] ); ?>" />
				</div>
				<div class="llms-form-item-wrapper">
					<label for="billing_state"><?php _e( 'Billing State', 'lifterlms' ); ?> <span class="required">*</span></label>
					<input type="text" class="input-text llms-input-text" name="billing_state" id="billing_state" value="<?php if ( ! empty( $_POST['billing_state'] ) ) echo esc_attr( $_POST['billing_state'] ); ?>" />
				</div>
				<div class="llms-form-item-wrapper">
					<label for="billing_zip"><?php _e( 'Billing Zip', 'lifterlms' ); ?> <span class="required">*</span></label>
					<input type="text" class="input-text llms-input-text" name="billing_zip" id="billing_address_1" value="<?php if ( ! empty( $_POST['billing_zip'] ) ) echo esc_attr( $_POST['billing_zip'] ); ?>" />
				</div>
				<div class="llms-form-item-wrapper">
					<label for="billing_country"><?php _e( 'Billing Country', 'lifterlms' ); ?> <span class="required">*</span></label>
					<select id="llms_country_options" name="billing_country">
					<?php $country_options = get_lifterlms_countries();
						foreach ( $country_options as $code => $name ) { ?>
							<option value="<?php echo $code; ?>"<?php selected( $_POST['billing_country'], $code ); ?>><?php echo $name; ?></option>
						<?php } ?>
					</select>
				</div>
			<?php endif; ?>

			<?php do_action( 'lifterlms_register_form_before_email' ); ?>

			<div class="llms-form-item-wrapper">
				<label for="reg_email"><?php _e( 'Email address', 'lifterlms' ); ?> <span class="required">*</span></label>
				<input type="email" class="input-text llms-input-text" name="email" id="reg_email" value="<?php if ( ! empty( $_POST['email'] ) ) echo esc_attr( $_POST['email'] ); ?>" />
			</div>

			<?php if ( 'yes' === get_option( 'lifterlms_registration_confirm_email' ) ) : ?>

				<div class="llms-form-item-wrapper">
					<label for="reg_email_2"><?php _e( 'Re-enter your email address', 'lifterlms' ); ?> <span class="required">*</span></label>
					<input type="email" class="input-text llms-input-text" name="email_confirm" id="reg_email_2" value="<?php if ( ! empty( $_POST['email_confirm'] ) ) echo esc_attr( $_POST['email_confirm'] ); ?>" />
				</div>

			<?php endif; ?>

			<?php do_action( 'lifterlms_register_form_after_email' ); ?>

			<div class="llms-form-item-wrapper">
				<label for="reg_password"><?php _e( 'Password', 'lifterlms' ); ?> <span class="required">*</span></label>
				<input type="password" class="input-text llms-input-text" name="password" id="reg_password" />
			</div>

	        <div class="llms-form-item-wrapper">
	            <label for="password_2"><?php _e( 'Re-enter new password', 'lifterlms' ); ?> <span class="required">*</span></label>
	            <input type="password" class="input-text llms-input-text" name="password_2" id="password_2" />
	        </div>


	        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />

			<!-- Used as anti-spam blocker -->
			<div style="left:-999em; position:absolute;"><label for="trap"><?php _e( 'Anti-spam', 'lifterlms' ); ?></label><input type="text" name="email_2" id="trap" tabindex="-1" /></div>

			<?php do_action( 'lifterlms_register_form' ); ?>
			<?php do_action( 'register_form' ); ?>


			<div class="llms-form-item-wrapper llms-submit-wrapper">
				<?php wp_nonce_field( 'lifterlms-register', 'register' ); ?>
				<input type="submit" class="button" name="register" value="<?php _e( 'Register', 'lifterlms' ); ?>" />
			</div>

			<?php do_action( 'lifterlms_register_form_end' ); ?>

		</form>

	</div>

	<?php do_action( 'lifterlms_after_person_register_form' ); ?>

<?php endif; ?>
