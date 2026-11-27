<?php
/**
 * LifterLMS Set Password Email Body Content
 *
 * Sent to students who checked out without choosing a password.
 *
 * @package LifterLMS/Templates/Emails
 *
 * @since [version]
 * @version [version]
 *
 * @var string $url            URL of the set password form.
 * @var string $dashboard_url  URL of the student dashboard.
 * @var int    $expiration_hrs Number of hours the link is valid for.
 */

defined( 'ABSPATH' ) || exit; ?>

<p><?php echo wp_kses_post( sprintf( __( 'Your account %s is ready.', 'lifterlms' ), '<strong>{user_login}</strong>' ) ); ?></p>

<p><?php esc_html_e( 'To log in again later, set a password for your account:', 'lifterlms' ); ?></p>

<p><a href="<?php echo esc_url( $url ); ?>" style="{button_style}"><?php esc_html_e( 'Set Password', 'lifterlms' ); ?></a></p>

<p>
<?php
// Translators: %d = number of hours.
echo esc_html( sprintf( _n( 'This link expires in %d hour. You can request a new one from the lost password form.', 'This link expires in %d hours. You can request a new one from the lost password form.', $expiration_hrs, 'lifterlms' ), $expiration_hrs ) );
?>
</p>

<p><?php esc_html_e( 'Your dashboard:', 'lifterlms' ); ?> <a href="<?php echo esc_url( $dashboard_url ); ?>"><?php echo esc_url( $dashboard_url ); ?></a></p>

{divider}

<p><small><?php esc_html_e( 'Trouble clicking? Copy and paste this URL into your browser:', 'lifterlms' ); ?><br><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_url( $url ); ?></a></small></p>
