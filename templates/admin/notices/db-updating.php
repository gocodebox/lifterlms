<?php
/**
 * Database Updating
 *
 * @since    3.4.3
 * @version  3.4.3
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }
$progress = LLMS_Install::$background_updater->get_progress() . '%';
?>
<p><strong><?php _e( 'LifterLMS database update', 'lifterlms' ); ?></strong> &ndash; <?php _e( 'Your database is being upgraded in the background.', 'lifterlms' ); ?></p>
<p><a href="https://lifterlms.com/docs/lifterlms-database-updates/"><?php _e( 'Click here for database update FAQs', 'lifterlms' ); ?></a></p>
<div style="background:#eee;"><div style="background:#ef476f;padding:5px;text-align:right;width:<?php echo $progress; ?>"><?php echo $progress; ?></div></div>
<p><a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=llms-settings' ), 'force_db_updates', 'llms-force-db-update' ); ?>"><?php _e( 'Taking too long? Click here to run the update now.', 'lifterlms' ); ?></a></p>
