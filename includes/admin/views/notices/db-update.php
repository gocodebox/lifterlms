<?php
/**
 * Review Request
 *
 * We're needy. Please tell us you like us, it means a lot.
 *
 * @package LifterLMS/Admin/Views
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;
$url = wp_nonce_url( add_query_arg( 'db_version', $this->db_version ), 'do_db_updates', 'llms-db-update' );
?>

<p><strong><?php _e( 'The LifterLMS database needs to be updated to the latest version.', 'lifterlms' ); ?></strong></p>
<p><?php _e( "The update will only take a few minutes and it will run in the background. A notice like this will let you know when it's finished.", 'lifterlms' ); ?></p>

<p><?php printf( __( 'See the %1$sdatabase update log%2$s for a complete list of changes scheduled for each upgrade.', 'lifterlms' ), '<a href="https://lifterlms.com/docs/lifterlms-database-updates/" target="_blank">', '</a>' ); ?></p>

<p><a class="button-primary" id="llms-start-updater" href="<?php echo $url; ?>"><?php _e( 'Run the Updater', 'lifterlms' ); ?></a></p>
