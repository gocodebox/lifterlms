<?php
/**
 * Generates Code Coverage reports when run with behat test suite
 *
 * Lifted with thanks and gratitude from ampproject/amp-wp.
 *
 * @link https://github.com/ampproject/amp-wp/blob/develop/tests/php/maybe-generate-wp-cli-coverage.php
 *
 * @since 2.0.0
 * @version 2.0.0
 */

use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Driver\XdebugDriver;
use SebastianBergmann\CodeCoverage\Driver\Xdebug;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Clover;
use SebastianBergmann\CodeCoverage\Report\Text;


$project_dir = getenv( 'BEHAT_PROJECT_DIR' );

if ( ! class_exists( 'SebastianBergmann\CodeCoverage\Filter' ) ) {
	require "${project_dir}/vendor/autoload.php";
}

$filter = new Filter();
$includeDirectory = method_exists( $filter, 'includeDirectory' ) ? 'includeDirectory' : 'addDirectoryToWhitelist';
$includeFiles     = method_exists( $filter, 'includeFiles' ) ? 'includeFiles' : 'addFilesToWhitelist';

$filter->$includeDirectory( "{$project_dir}/includes" );
$filter->$includeFiles( array(
	"{$project_dir}/lifterlms-cli.php",
	"{$project_dir}/class-lifterlms-cli.php",
) );

$driver = class_exists( 'XdebugDriver' ) ? new XdebugDriver( $filter ) : new Xdebug( $filter );

$coverage = new CodeCoverage(
	( $driver ),
	$filter
);

$feature  = getenv( 'BEHAT_FEATURE_TITLE' );
$scenario = getenv( 'BEHAT_SCENARIO_TITLE' );
$name     = "{$feature} - {$scenario}";

$coverage->start( $name );

register_shutdown_function(
	static function () use ( $coverage, $feature, $scenario, $name ) {
		$coverage->stop();

		$project_dir = getenv( 'BEHAT_PROJECT_DIR' );

		$feature_suffix  = preg_replace( '/[^a-z0-9]+/', '-', strtolower( $feature ) );
		$scenario_suffix = preg_replace( '/[^a-z0-9]+/', '-', strtolower( $scenario ) );
		$filename        = "clover-behat/{$feature_suffix}-{$scenario_suffix}.xml";
		$destination     = "{$project_dir}/tmp/coverage/{$filename}";
		( new Clover() )->process( $coverage, $destination, $name );
	}
);
