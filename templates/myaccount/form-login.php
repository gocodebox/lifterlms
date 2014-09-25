<?php
/**
 * Login Form
 *
 * @author 		lifterLMS
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

?>

<?php llms_print_notices(); ?>

<?php do_action( 'lifterlms_before_person_login_form' ); ?>

<?php if ( get_option( 'lifterlms_enable_myaccount_registration' ) === 'yes' ) : ?>

<div class="col2-set" id="person_login">

	<div class="col-1">

<?php endif; ?>

		<h2><?php _e( 'Login', 'lifterlms' ); ?></h2>

		<form method="post" class="login">

			<?php do_action( 'lifterlms_login_form_start' ); ?>

			<p>
				<label for="username"><?php _e( 'Username or email address', 'lifterlms' ); ?> <span class="required">*</span></label>
				<input type="text" class="input-text" name="username" id="username" value="<?php if ( ! empty( $_POST['username'] ) ) echo esc_attr( $_POST['username'] ); ?>" />
			</p>
			<p>
				<label for="password"><?php _e( 'Password', 'lifterlms' ); ?> <span class="required">*</span></label>
				<input class="input-text" type="password" name="password" id="password" />
			</p>

			<?php do_action( 'lifterlms_login_form' ); ?>

			<p>
				<?php wp_nonce_field( 'lifterlms-login' ); ?>
				<input type="submit" class="button" name="login" value="<?php _e( 'Login', 'lifterlms' ); ?>" /> 
				<label for="rememberme" class="inline">
					<input name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php _e( 'Remember me', 'lifterlms' ); ?>
				</label>
			</p>
			<p class="lost_password">
				<a href="<?php echo esc_url( llms_lostpassword_url() ); ?>"><?php _e( 'Lost your password?', 'lifterlms' ); ?></a>
			</p>

			<?php do_action( 'lifterlms_login_form_end' ); ?>

		</form>

<?php if ( get_option( 'lifterlms_enable_myaccount_registration' ) === 'yes' ) : ?>

	</div>

	<div class="col-2">

		<h2><?php _e( 'Register', 'lifterlms' ); ?></h2>

		<form method="post" class="register">

			<?php do_action( 'lifterlms_register_form_start' ); ?>

			<?php if ( 'no' === get_option( 'lifterlms_registration_generate_username' ) ) : ?>

				<p>
					<label for="reg_username"><?php _e( 'Username', 'lifterlms' ); ?> <span class="required">*</span></label>
					<input type="text" class="input-text" name="username" id="reg_username" value="<?php if ( ! empty( $_POST['username'] ) ) echo esc_attr( $_POST['username'] ); ?>" />
				</p>

			<?php endif; ?>

			<p>
				<label for="reg_email"><?php _e( 'Email address', 'lifterlms' ); ?> <span class="required">*</span></label>
				<input type="email" class="input-text" name="email" id="reg_email" value="<?php if ( ! empty( $_POST['email'] ) ) echo esc_attr( $_POST['email'] ); ?>" />
			</p>

			<?php if ( 'no' === get_option( 'lifterlms_registration_generate_password' ) ) : ?>
	
				<p>
					<label for="reg_password"><?php _e( 'Password', 'lifterlms' ); ?> <span class="required">*</span></label>
					<input type="password" class="input-text" name="password" id="reg_password" />
				</p>

			<?php endif; ?>

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

	</div>

</div>
<?php endif; ?>

<?php do_action( 'lifterlms_after_person_login_form' ); ?>