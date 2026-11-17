Banner Notifications
====================

Admin banner notifications for LifterLMS.

This directory is part of the LifterLMS core repository and ships with LifterLMS. It is not a standalone plugin. The old [gocodebox/banner-notifications](https://github.com/gocodebox/banner-notifications) repository is archived.

Core loads it from `includes/llms-notifications.php`:

```php
$GLOBALS['lifterlms_banner_notifications'] = new Gocodebox_Banner_Notifier(
	array(
		'prefix'            => 'lifterlms',
		'version'           => llms()->version,
		'notifications_url' => 'https://notifications.lifterlms.com/v1/notifications.json',
	)
);
```

`Gocodebox_Banner_Notifier` (in `src/notifications.php`) requires `prefix`, `version`, and `notifications_url`.

## Contributing

Follow the [LifterLMS core contribution guidelines](../../.github/CONTRIBUTING.md). Changelog entries go in the core `.changelogs/` directory. PHPUnit coverage is in `tests/phpunit/unit-tests/libraries/banner-notifications/`.
