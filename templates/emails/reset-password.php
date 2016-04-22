<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php do_action( 'lifterlms_email_header', $email_heading ); ?>

<p><?php _e( 'Password reset', 'lifterlms' ); ?></p>
<p><?php printf( __( 'Username: %s', 'lifterlms' ), $user_login ); ?></p>
<p><?php _e( 'To reset your password, click on the link below.', 'lifterlms' ); ?></p>
<p>
	<a href="<?php echo esc_url( add_query_arg( array( 'key' => $reset_key, 'login' => rawurlencode( $user_login ) ), llms_get_endpoint_url( 'lost-password', '', get_permalink( llms_get_page_id( 'myaccount' ) ) ) ) ); ?>">
			<?php _e( 'Click here to reset your password', 'lifterlms' ); ?></a>
</p>
<p></p>
<?php do_action( 'lifterlms_email_footer' ); ?>
