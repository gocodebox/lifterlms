<?php
/**
 * Update functions for version 3.28.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since 3.39.0
 * @version 3.39.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Clear the unused cron `lifterlms_cleanup_sessions`
 *
 * @since 3.28.0
 *
 * @return void
 */
function llms_update_3280_clear_session_cleanup_cron() {
	wp_clear_scheduled_hook( 'lifterlms_cleanup_sessions' );
}

/**
 * Update db version at conclusion of 3.28.0 updates
 *
 * @return void
 *
 * @since 3.28.0
 */
function llms_update_3280_update_db_version() {
	LLMS_Install::update_db_version( '3.28.0' );
}
