LifterLMS Blocks
================

[![Test PHPUnit](https://github.com/gocodebox/lifterlms-blocks/actions/workflows/test-phpunit.yml/badge.svg)](https://github.com/gocodebox/lifterlms-blocks/actions/workflows/test-phpunit.yml)
[![PHP Code Coverage Report](https://github.com/gocodebox/lifterlms-blocks/actions/workflows/php-test-coverage.yml/badge.svg)](https://github.com/gocodebox/lifterlms-blocks/actions/workflows/php-test-coverage.yml)
[![Coding Standards](https://github.com/gocodebox/lifterlms-blocks/actions/workflows/coding-standards.yml/badge.svg)](https://github.com/gocodebox/lifterlms-blocks/actions/workflows/coding-standards.yml)
[![Maintainability](https://api.codeclimate.com/v1/badges/49df50fa2a04ab1f8e55/maintainability)](https://codeclimate.com/github/gocodebox/lifterlms-blocks/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/49df50fa2a04ab1f8e55/test_coverage)](https://codeclimate.com/github/gocodebox/lifterlms-blocks/test_coverage)

WordPress Editor (Gutenberg) blocks for LifterLMS.

---

## Installing

**Via LifterLMS**

+ Since LifterLMS 3.25.0-alpha.1 this plugin is automatically included in the LifterLMS core codebase

**Installation of the plugin via Zip file**

+ Download the zip file using the "Clone or download" button
+ On your WordPress admin panel navigate to Plugins -> Add New
+ Upload the zip file
+ Activate the plugin


## Development

While developing Javascript within this plugin you can watch changes in the assets and (optionally) automatically reload using LiveReload

To watch changes in the `assets/src` directory, run `npm start`.

To use LiveReload, add the following to a mu-plugin to automatically reload during development:

```php
// add_action( 'wp_head', 'add_live_reload' ); // Auto-reload frontend.
// add_action( 'admin_head', 'add_live_reload' ); // Auto-reload admin panel.
function add_live_reload() {
  echo '<script src="http://localhost:35729/livereload.js"></script>';
}
```
