<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( is_user_logged_in() ) {
	return; }
?>
<form method="post" class="login" <?php if ( $hidden ) { echo 'style="display:none;"'; } ?>>

	<?php do_action( 'lifterlms_login_form_start' ); ?>

	<?php if ( $message ) { echo wpautop( wptexturize( $message ) ); } ?>

	<?php llms_get_template('global/form-login-inner.php'); ?>

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
