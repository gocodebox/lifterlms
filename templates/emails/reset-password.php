<?php
/**
 * LifterLMS Reset Password Email Body Content
 *
 * @since    1.0.0
 * @version  3.8.0
 */

defined( 'ABSPATH' ) || exit; ?>

<p><?php echo wp_kses_post( sprintf( __( 'Someone recently requested that the password be reset for %s.', 'lifterlms' ), '<strong>{user_login}</strong>' ) ); ?></p>

<p><?php esc_html_e( 'To reset your password, click on the button below:', 'lifterlms' ); ?></p>

<p><a href="<?php echo esc_url( $url ); ?>" style="{button_style}"><?php esc_html_e( 'Reset Password', 'lifterlms' ); ?></a></p>

<p><?php esc_html_e( 'If this was a mistake you can ignore this email and your password will not be changed.', 'lifterlms' ); ?></p>

{divider}

<p><small><?php esc_html_e( 'Trouble clicking? Copy and paste this URL into your browser:', 'lifterlms' ); ?><br><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_url( $url ); ?></a></small></p>
