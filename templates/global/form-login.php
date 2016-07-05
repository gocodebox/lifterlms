<?php
/**
 * Login Form
 *
 * @author 		lifterLMS
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( is_user_logged_in() ) {
	return; }
?>
<form method="post" class="login" <?php if ( $hidden ) { echo 'style="display:none;"'; } ?>>

	<?php do_action( 'lifterlms_login_form_start' ); ?>

	<?php if ( $message ) { echo wpautop( wptexturize( $message ) ); } ?>

	<p class="form-row form-row-first">
		<label for="username"><?php _e( 'Username or email', 'lifterlms' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="username" id="username" />
	</p>
	<p class="form-row form-row-last">
		<label for="password"><?php _e( 'Password', 'lifterlms' ); ?> <span class="required">*</span></label>
		<input class="input-text" type="password" name="password" id="password" />
	</p>
	<div class="clear"></div>

	<?php do_action( 'lifterlms_login_form' ); ?>

	<p class="form-row">
		<?php wp_nonce_field( 'lifterlms-login' ); ?>
		<input type="submit" class="button" name="login" value="<?php _e( 'Login', 'lifterlms' ); ?>" />
		<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ) ?>" />
		<label for="rememberme" class="inline">
			<input name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php _e( 'Remember me', 'lifterlms' ); ?>
		</label>
	</p>
	<p class="lost_password">
		<a href="<?php echo esc_url( llms_lostpassword_url() ); ?>"><?php _e( 'Lost your password?', 'lifterlms' ); ?></a>
	</p>

	<div class="clear"></div>

	<?php do_action( 'lifterlms_login_form_end' ); ?>

</form>
