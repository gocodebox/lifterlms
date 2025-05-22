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
