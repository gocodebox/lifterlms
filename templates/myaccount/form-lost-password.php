<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>

<?php llms_print_notices(); ?>

<form method="post" class="lost_reset_password">

	<?php if ( 'lost_password' == $args['form'] ) : ?>

		<p><?php echo apply_filters( 'lifterlms_lost_password_message', __( 'Lost your password? Enter your email address and we will send you a link to reset it.', 'lifterlms' ) ); ?></p>

		<p><label for="user_login"><?php _e( 'Username or email', 'lifterlms' ); ?></label> 
		<input class="input-text llms-input-text" type="text" name="user_login" id="user_login" /></p>

	<?php else : ?>

		<p><?php echo apply_filters( 'lifterlms_reset_password_message', __( 'Enter a new password below.', 'lifterlms' ) ); ?></p>

		<p>
			<label for="password_1"><?php _e( 'New password', 'lifterlms' ); ?> <span class="required">*</span></label>
			<input type="password" class="input-text llms-input-text" name="password_1" id="password_1" />
		</p>
		<p>
			<label for="password_2"><?php _e( 'Re-enter new password', 'lifterlms' ); ?> <span class="required">*</span></label>
			<input type="password" class="input-text llms-input-text" name="password_2" id="password_2" />
		</p>

		<input type="hidden" name="reset_key" value="<?php echo isset( $args['key'] ) ? $args['key'] : ''; ?>" />
		<input type="hidden" name="reset_login" value="<?php echo isset( $args['login'] ) ? $args['login'] : ''; ?>" />

	<?php endif; ?>

	<div class="clear"></div>

	<p><input type="submit" class="button" name="llms_reset_password" value="<?php echo 'lost_password' == $args['form'] ? __( 'Reset Password', 'lifterlms' ) : __( 'Save', 'lifterlms' ); ?>" /></p>
	<?php wp_nonce_field( $args['form'] ); ?>

</form>
