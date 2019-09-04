<?php
/**
 * Staging Site Recurring Payment Notice
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since 3.0.2
 * @version 3.0.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	exit;
}
?>

<p><strong><?php echo __( 'It looks like you may have installed LifterLMS on a staging site!', 'lifterlms' ); ?></strong></p>

<p><?php _e( 'LifterLMS watches for potential signs of a staging site and disables automatic payments so that your students do not receive duplicate charges.', 'lifterlms' ); ?></p>

<p>
<?php
printf(
	__( 'You can choose to enable automatic recurring payments using the buttons below. If you\'re not sure what to do, you can learn more %1$shere%2$s. You can always change your mind later by clicking "Reset Automatic Payments" on the LifterLMS General Settings screen under Tools and Utilities.', 'lifterlms' ),
	'<a href="https://lifterlms.com/docs/staging-sites-and-lifterlms-recurring-payments" target="_blank">',
	'</a>'
);
?>
</p>

<p>
	<a class="button-primary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'llms-staging-status', 'disable', admin_url( 'admin.php?page=llms-settings' ) ), 'llms_staging_status', '_llms_staging_nonce' ) ); ?>"><?php echo __( 'Leave Automatic Payments Disabled', 'lifterlms' ); ?></a>
	&nbsp;&nbsp;
	<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'llms-staging-status', 'enable', admin_url( 'admin.php?page=llms-settings' ) ), 'llms_staging_status', '_llms_staging_nonce' ) ); ?>"><?php echo __( 'Enable Automatic Payments', 'lifterlms' ); ?></a>
</p>
