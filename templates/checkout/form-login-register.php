<?php
/**
 * Checkout Form
 *
 * @author 		LifterLMS
 * @package 	LifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$active = 'login';
if ( isset( $_POST['llms-registration'] ) && 1 == $_POST['llms-registration'] ) {
	$active = 'register';
}
?>

<div class="llms-form-wrapper">
    <div class="llms-user-fields llms-login-fields<?php echo ( 'login' === $active ) ? ' active': ''; ?>" id="llms-login-fields">
        <div class="llms-notice-box">
            <?php _e( 'Don\'t have an account?', 'lifterlms' ); ?>
            <a href="#" class="llms-toggle llms-button-text"><?php _e( 'Sign Up', 'lifterlms' ); ?></a>
        </div>

        <?php llms_get_template( 'global/form-login-inner.php' ); ?>
        <input type="hidden" disabled name="llms-login" value="1">
    </div>
    <div class="llms-user-fields llms-register-fields<?php echo ( 'register' === $active ) ? ' active': ''; ?>" id="llms-register-fields">
        <div class="llms-notice-box">
            <?php _e( 'Already registered?', 'lifterlms' ); ?>
            <a href="#" class="llms-toggle llms-button-text"><?php _e( 'Login', 'lifterlms' ); ?></a>
        </div>

        <?php llms_get_template( 'global/form-registration-inner.php' ); ?>
        <input type="hidden" name="llms-registration" value="1">
    </div>
</div>
