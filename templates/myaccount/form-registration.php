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


// check for buddypress overrides
$integrations = LLMS()->integrations()->get_available_integrations();
if(array_key_exists('bp', $integrations) && $integrations['bp']->enabled) {
	$buddypress = true;
} else {
	$buddypress = false;
}
?>

<?php if ( get_option( 'lifterlms_enable_myaccount_registration' ) === 'yes' ) : ?>

	<?php do_action( 'lifterlms_before_person_register_form' ); ?>

	<div class="col-2">

		<?php if(!$buddypress): ?>

			<h2><?php _e( 'Register', 'lifterlms' ); ?></h2>

			<form method="post" class="register">

				<?php do_action( 'lifterlms_register_form_start' ); ?>

				<input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />

				<?php if ( 'no' === get_option( 'lifterlms_registration_generate_username' ) ) : ?>

					<p>
						<label for="reg_username"><?php _e( 'Username', 'lifterlms' ); ?> <span class="required">*</span></label>
						<input type="text" class="input-text llms-input-text" name="username" id="reg_username" value="<?php if ( ! empty( $_POST['username'] ) ) echo esc_attr( $_POST['username'] ); ?>" />
					</p>

				<?php endif; ?>

				<?php if ( 'yes' === get_option( 'lifterlms_registration_require_name' ) ) : ?>

					<p>
						<label for="reg_firstname"><?php _e( 'First Name', 'lifterlms' ); ?> <span class="required">*</span></label>
						<input type="text" class="input-text llms-input-text" name="firstname" id="reg_firstname" value="<?php if ( ! empty( $_POST['firstname'] ) ) echo esc_attr( $_POST['firstname'] ); ?>" />
					</p>
					<p>
						<label for="reg_lastname"><?php _e( 'Last Name', 'lifterlms' ); ?> <span class="required">*</span></label>
						<input type="text" class="input-text llms-input-text" name="lastname" id="reg_lastname" value="<?php if ( ! empty( $_POST['lastname'] ) ) echo esc_attr( $_POST['lastname'] ); ?>" />
					</p>
				<?php endif; ?>

				<?php if ( 'yes' === get_option( 'lifterlms_registration_require_address' ) ) : ?>
					
					<p>
						<label for="billing_address_1"><?php _e( 'Billing Address 1', 'lifterlms' ); ?> <span class="required">*</span></label>
						<input type="text" class="input-text llms-input-text" name="billing_address_1" id="billing_address_1" value="<?php if ( ! empty( $_POST['billing_address_1'] ) ) echo esc_attr( $_POST['billing_address_1'] ); ?>" />
					</p>
					<p>
						<label for="billing_address_2"><?php _e( 'Billing Address 2', 'lifterlms' ); ?></label>
						<input type="text" class="input-text llms-input-text" name="billing_address_2" id="billing_address_2" value="<?php if ( ! empty( $_POST['billing_address_2'] ) ) echo esc_attr( $_POST['billing_address_2'] ); ?>" />
					</p>
					<p>
						<label for="billing_city"><?php _e( 'Billing City', 'lifterlms' ); ?> <span class="required">*</span></label>
						<input type="text" class="input-text llms-input-text" name="billing_city" id="billing_city" value="<?php if ( ! empty( $_POST['billing_city'] ) ) echo esc_attr( $_POST['billing_city'] ); ?>" />
					</p>
					<p>
						<label for="billing_state"><?php _e( 'Billing State', 'lifterlms' ); ?> <span class="required">*</span></label>
						<input type="text" class="input-text llms-input-text" name="billing_state" id="billing_state" value="<?php if ( ! empty( $_POST['billing_state'] ) ) echo esc_attr( $_POST['billing_state'] ); ?>" />
					</p>
					<p>
						<label for="billing_zip"><?php _e( 'Billing Zip', 'lifterlms' ); ?> <span class="required">*</span></label>
						<input type="text" class="input-text llms-input-text" name="billing_zip" id="billing_address_1" value="<?php if ( ! empty( $_POST['billing_zip'] ) ) echo esc_attr( $_POST['billing_zip'] ); ?>" />
					</p>
					<p>
						<label for="billing_country"><?php _e( 'Billing Country', 'lifterlms' ); ?> <span class="required">*</span></label>
						<select id="llms_country_options" name="billing_country">
						<?php $country_options = get_lifterlms_countries();
							foreach ( $country_options as $code => $name ) { ?>
							<option value="<?php echo $code; ?>"><?php echo $name; ?></option>
							<?php } ?>
						</select>
					</p>
				<?php endif; ?>

				<p>
					<label for="reg_email"><?php _e( 'Email address', 'lifterlms' ); ?> <span class="required">*</span></label>
					<input type="email" class="input-text llms-input-text" name="email" id="reg_email" value="<?php if ( ! empty( $_POST['email'] ) ) echo esc_attr( $_POST['email'] ); ?>" />
				</p>

					<p>
						<label for="reg_password"><?php _e( 'Password', 'lifterlms' ); ?> <span class="required">*</span></label>
						<input type="password" class="input-text llms-input-text" name="password" id="reg_password" />
					</p>


				<!-- Used as anti-spam blocker -->
				<div style="left:-999em; position:absolute;"><label for="trap"><?php _e( 'Anti-spam', 'lifterlms' ); ?></label><input type="text" name="email_2" id="trap" tabindex="-1" /></div>

				<?php do_action( 'lifterlms_register_form' ); ?>
				<?php do_action( 'register_form' ); ?>

				<p class="form-row">
					<?php wp_nonce_field( 'lifterlms-register', 'register' ); ?>
					<input type="submit" class="button" name="register" value="<?php _e( 'Register', 'lifterlms' ); ?>" />
				</p>

				<?php do_action( 'lifterlms_register_form_end' ); ?>

			</form>

		<?php else : ?>
			<a href="<?php echo $integrations['bp']->get_registration_permalink(); ?>" title="Register" ><?php _e( 'Register', 'lifterlms' ); ?></a>

		<?php endif; ?>

	</div>

	<?php do_action( 'lifterlms_after_person_login_form' ); ?>

<?php endif; ?>
