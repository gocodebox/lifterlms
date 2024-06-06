<?php
/**
 * Pending database update notice.
 *
 * @package LifterLMS/Admin/Views/Notices
 *
 * @since 5.2.0
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;

$base_url = ! empty( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : admin_url();
$url      = wp_nonce_url( $base_url, 'do_db_updates', 'llms-db-update' );
?>

<p><strong><?php esc_html_e( 'The LifterLMS database needs to be updated to the latest version.', 'lifterlms' ); ?></strong></p>
<p><?php esc_html_e( "The update will only take a few minutes and it will run in the background. A notice like this will let you know when it's finished.", 'lifterlms' ); ?></p>

<p><?php printf( esc_html__( 'See the %1$sdatabase update log%2$s for a complete list of changes scheduled for each upgrade.', 'lifterlms' ), '<a href="https://lifterlms.com/docs/lifterlms-database-updates/" target="_blank">', '</a>' ); ?></p>

<p><a class="button-primary" id="llms-start-updater" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Run the Updater', 'lifterlms' ); ?></a></p>

<script>
( function() {
	document.getElementById( 'llms-start-updater' ).onclick = function( e ) {
		var confirm = window.confirm( '<?php echo esc_js( __( 'We strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'lifterlms' ) ); ?>' );
		if ( ! confirm ) {
			e.preventDefault();
		}
	};
} )();
</script>
