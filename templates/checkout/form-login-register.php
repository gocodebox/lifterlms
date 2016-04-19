<div class="llms-form-wrapper">
    <div id="llms-login-fields" style="display: none;">
        <div class="llms-notice-box">
            <?php _e( 'Don\'t have an account?', 'lifterlms'); ?>
            <a href="#" class="llms-toggle" data-parent="llms-login-fields" data-target="llms-register-fields"><?php _e( 'Sign Up', 'lifterlms'); ?></a>
        </div>

        <?php llms_get_template( 'global/form-login-inner.php' ); ?>
        <input type="hidden" disabled name="llms-login" value="1">
    </div>
    <div id="llms-register-fields">
        <div class="llms-notice-box">
            <?php _e( 'Already registered?', 'lifterlms'); ?>
            <a href="#" class="llms-toggle" data-parent="llms-register-fields" data-target="llms-login-fields"><?php _e( 'Login', 'lifterlms'); ?></a>
        </div>

        <?php llms_get_template( 'global/form-registration-inner.php' ); ?>
        <input type="hidden" name="llms-registration" value="1">
    </div>
</div>
