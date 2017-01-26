<?php
/**
 * LifterLMS Uninstall
 * @since    1.0.0
 * @version  3.3.1
 */

// If uninstall not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

wp_clear_scheduled_hook( 'llms_check_for_expired_memberships' );
wp_clear_scheduled_hook( 'lifterlms_cleanup_sessions' );
wp_clear_scheduled_hook( 'llms_send_tracking_data' );
wp_clear_scheduled_hook( 'lifterlms_engagement_award_achievement' );
wp_clear_scheduled_hook( 'lifterlms_engagement_award_certificate' );
wp_clear_scheduled_hook( 'lifterlms_engagement_send_email' );

/**
 * Only actually delete LifterLMS and Related Data when constant is defined
 * This will prevent data loss when a plugin is deactivated
 */
if ( defined( 'LLMS_REMOVE_ALL_DATA' ) && true === LLMS_REMOVE_ALL_DATA ) {

	global $wpdb;

	wp_trash_post( get_option( 'lifterlms_shop_page_id' ) );
	wp_trash_post( get_option( 'lifterlms_memberships_page_id' ) );
	wp_trash_post( get_option( 'lifterlms_checkout_page_id' ) );
	wp_trash_post( get_option( 'lifterlms_myaccount_page_id' ) );

	LLMS_Install::remove_difficulties();

	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'lifterlms\_%';" );

	remove_role( 'student' );


}
