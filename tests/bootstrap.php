<?php

// disable xdebug backtrace
if ( function_exists( 'xdebug_disable' ) ) {
	xdebug_disable();
}

echo 'Welcome to the LifterLMS Test Suite' . PHP_EOL . PHP_EOL . PHP_EOL;

require 'tmp/wordpress-develop/tests/phpunit/includes/bootstrap.php';

foreach ( glob( 'tests/framework/class.llms.*.php' ) as $file ) {
	require $file;
}

$tests_dir  = dirname( __FILE__ );
$plugin_dir = dirname( $tests_dir );

require_once $plugin_dir . '/lifterlms.php' ;

echo 'Installing LifterLMS...' . PHP_EOL;

LLMS_Install::install();

// Reload capabilities after install, see https://core.trac.wordpress.org/ticket/28374
if ( version_compare( $GLOBALS['wp_version'], '4.7', '<' ) ) {
	$GLOBALS['wp_roles']->reinit();
} else {
	$GLOBALS['wp_roles'] = null;
	wp_roles();
}
