<?php
/**
 * Login Form
 *
 * @author 		lifterLMS
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$product_id = get_query_var( 'product-id' );

global $wpdb;

?>

<?php llms_print_notices(); ?>

<?php do_action( 'lifterlms_before_person_login_form' ); ?>

<div class="col-1 llms-person-login" id="person_login">

	<h2><?php _e( 'Login', 'lifterlms' ); ?></h2>

	<form method="post" class="llms-person-login-form login">

		<?php do_action( 'lifterlms_login_form_start' ); ?>

		<input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />

		<p>
			<label for="username"><?php _e( 'Username or email address', 'lifterlms' ); ?> <span class="required">*</span></label>
			<input type="text" class="input-text llms-input-text" name="username" id="username" value="<?php if ( ! empty( $_POST['username'] ) ) { echo esc_attr( $_POST['username'] ); } ?>" />
		</p>
		<p>
			<label for="password"><?php _e( 'Password', 'lifterlms' ); ?> <span class="required">*</span></label>
			<input class="input-text llms-input-text" type="password" name="password" id="password" />
		</p>

		<?php do_action( 'lifterlms_login_form' ); ?>

		<p>
			<?php wp_nonce_field( 'lifterlms-login' ); ?>
			<input type="submit" class="button" name="login" value="<?php _e( 'Login', 'lifterlms' ); ?>" />
			<label for="rememberme" class="inline llms-rememberme-link">
				<input class="llms-rememberme-link" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php _e( 'Remember me', 'lifterlms' ); ?>
			</label>
		</p>
		<div class="llms-lost-password-link">
			<p class="lost_password">
				<a href="<?php echo esc_url( llms_lostpassword_url() ); ?>"><?php _e( 'Lost your password?', 'lifterlms' ); ?></a>
			</p>
		</div>

		<?php do_action( 'lifterlms_login_form_end' ); ?>

	</form>

</div>

<?php do_action( 'lifterlms_after_person_login_form' ); ?>
