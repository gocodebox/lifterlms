<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>

<?php llms_print_notices(); ?>

<nav class="account-links">
    <?php

    printf(
        __( '<a href="%1$s">Sign out</a>  &middot;  ', 'lifterlms' ) . ' ',
        wp_logout_url( get_permalink( llms_get_page_id( 'myaccount' ) ) )
    );


    printf( __( '<a href="%s">My Courses</a>', 'lifterlms' ),
        get_permalink( llms_get_page_id( 'myaccount' ) )
    );

    ?>
</nav>

<form action="" method="post">

    <h3>Basic Information</h3>

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

    <h3>Change Password</h3>

    <p class="form-row form-row-first">
        <label for="password_1"><?php _e( 'Password (leave blank to leave unchanged)', 'lifterlms' ); ?></label>
        <input type="password" class="input-text llms-input-text" name="password_1" id="password_1" />
    </p>
    <p class="form-row form-row-last">
        <label for="password_2"><?php _e( 'Confirm new password', 'lifterlms' ); ?></label>
        <input type="password" class="input-text llms-input-text" name="password_2" id="password_2" />
    </p>
    <div class="clear"></div>

    <p><input type="submit" class="button" name="save_account_details" value="<?php _e( 'Save changes', 'lifterlms' ); ?>" /></p>

    <?php wp_nonce_field( 'save_account_details' ); ?>
    <input type="hidden" name="action" value="save_account_details" />
</form>