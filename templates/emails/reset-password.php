<?php
/**
 * LifterLMS Reset Password Email Body Content
 *
 * @since    1.0.0
 * @version  3.8.0
 */

defined( 'ABSPATH' ) || exit; ?>

<p><?php printf( __( 'Someone recently requested that the password be reset for %s.', 'lifterlms' ), '<strong>{user_login}</strong>' ); ?></p>

<p><?php _e( 'To reset your password, click on the button below:', 'lifterlms' ); ?></p>

<p><a href="<?php echo $url; ?>" style="{button_style}"><?php _e( 'Reset Password', 'lifterlms' ); ?></a></p>

<p><?php _e( 'If this was a mistake you can ignore this email and your password will not be changed.', 'lifterlms' ); ?></p>

{divider}

<p><small><?php _e( 'Trouble clicking? Copy and paste this URL into your browser:', 'lifterlms' ); ?><br><a href="<?php echo $url; ?>"><?php echo $url; ?></a></small></p>
