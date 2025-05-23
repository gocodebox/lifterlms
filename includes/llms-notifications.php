<?php
defined( 'ABSPATH' ) || exit;

require_once LLMS_PLUGIN_DIR . '/libraries/banner-notifications/banner-notifications.php';

$GLOBALS['lifterlms_banner_notifications'] = new Gocodebox_Banner_Notifier(
	array(
		'prefix'            => 'lifterlms',
		'version'           => llms()->version,
		'notifications_url' => 'https://notifications.lifterlms.com/v1/notifications.json',
	)
);

function llms_maybe_hide_notifications( $priority ) {
	if ( ! is_admin() ) {
		return 0;
	}

	$current_screen = get_current_screen();

	if ( ! isset( $current_screen->post_type ) ) {
		return $priority;
	}

	// Check if we're on the main WP admin dashboard.
	if ( 'dashboard' === $current_screen->id ) {
		return $priority;
	}

	if ( llms_is_block_editor() ) {
		return 0;
	}

	if (
		strpos( $current_screen->post_type, 'llms_' ) !== 0 &&
		strpos( $current_screen->base, 'lifterlms' ) !== 0 &&
		! in_array( $current_screen->post_type, array( 'course', 'lesson' ), true )
	) {
		return 0;
	}

	return $priority;
}

add_filter( 'lifterlms_max_notification_priority', 'llms_maybe_hide_notifications', 10, 1 );
