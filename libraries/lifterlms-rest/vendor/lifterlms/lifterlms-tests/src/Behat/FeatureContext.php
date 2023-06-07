<?php
/**
 * FeatureContext class file
 *
 * @package LifterLMS/Tests/Behat
 *
 * @since 2.0.0
 * @version 2.0.1
 */

namespace LifterLMS\Tests\Behat;

use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface;

use WP_CLI\Process;
use WP_CLI\Tests\Context\FeatureContext as WP_CLI_FeatureContext;

/**
 * Behat feature test context class
 *
 * This class extends the one that is provided by the wp-cli/wp-cli-tests package.
 * To see a list of all recognized step definitions, run `vendor/bin/behat -dl`.
 *
 * @since 2.0.0
 */
class FeatureContext extends WP_CLI_FeatureContext {

	use GivenStepDefinitions;
	use WhenStepDefinitions;

	/**
	 * The current feature.
	 *
	 * @var FeatureNode|null
	 */
	private static $feature;

	/**
	 * The current scenario.
	 *
	 * @var ScenarioInterface|null
	 */
	private $scenario;

	private $db_defaults = array(
		'TESTS_DB_NAME' => 'llms_cli_tests',
		'TESTS_DB_USER' => 'root',
		'TESTS_DB_PASS' => 'password',
		'TESTS_DB_HOST' => '127.0.01',
	);

	public function __construct() {

		$vars = array(
			'TESTS_DB_NAME' => 'WP_CLI_TEST_DBNAME',
			'TESTS_DB_USER' => 'WP_CLI_TEST_DBUSER',
			'TESTS_DB_PASS' => 'WP_CLI_TEST_DBPASS',
			'TESTS_DB_HOST' => 'WP_CLI_TEST_DBHOST',
		);

		foreach ( $vars as $var => $wpcli_var ) {
			$val       = getenv( $var );
			$wpcli_val = getenv( $wpcli_var );
			if ( ! $wpcli_val ) {
				$val = $val ? $val : $this->db_defaults[ $var ];
				putenv( "$wpcli_var=$val" );
			}
		}

		parent::__construct();

		// Load custom environment vars.
		$envfile = dirname( self::get_vendor_dir() ) . '/.behatenv';
		if ( file_exists( $envfile ) ) {
			$json = json_decode( file_get_contents( $envfile ), true );
			foreach ( $json as $key => $val ) {
				putenv( "{$key}={$val}" );
			}
		}

	}

	/**
	 * @BeforeFeature
	 */
	public static function store_feature( BeforeFeatureScope $scope ) {
		self::$feature = $scope->getFeature();
	}

	/**
	 * @BeforeScenario
	 */
	public function store_scenario( BeforeScenarioScope $scope ) {
		$this->scenario = $scope->getScenario();
	}

	/**
	 * @AfterScenario
	 */
	public function forget_scenario( AfterScenarioScope $scope ) {
		$this->scenario = null;
	}

	/**
	 * @AfterFeature
	 */
	public static function forget_feature( AfterFeatureScope $scope ) {
		self::$feature = null;
	}

	/**
	 * Ensure that a requested directory exists and create it recursively as needed.
	 *
	 * @since 2.0.0
	 *
	 * @param string $directory Directory to ensure the existence of.
	 * @throws RuntimeException When the directory cannot be created.
	 * @return void
	 */
	private function ensure_dir_exists( $directory ) {

		$parent = dirname( $directory );
		if ( ! empty( $parent ) && ! is_dir( $parent ) ) {
			$this->ensure_dir_exists( $parent );
		}

		if ( ! is_dir( $directory ) && ! mkdir( $directory ) && ! is_dir( $directory ) ) {
			throw new RuntimeException( "Could not create directory '{$directory}'." );
		}

	}

	public function proc_with_env( $command, $env = array(), $assoc_args = array(), $path = '' ) {

		$proc = parent::proc( $command, $assoc_args, $path );

		$reflector = new \ReflectionObject( $proc );

		$props = array(
			'command' => null,
			'cwd'     => null,
			'env'     => null,
		);
		foreach ( $props as $key => &$val ) {

			$prop = $reflector->getProperty( $key );
			$prop->setAccessible( true );

			if ( 'env' === $key ) {
				$prop->setValue( $proc, array_merge( $prop->getValue( $proc ), $env ) );
			}

			$val = $prop->getValue( $proc );

		}

		return Process::create( ...array_values( $props ) );

	}

	private function get_env() {

		return array(
			'BEHAT_PROJECT_DIR' => $this->variables['PROJECT_DIR'],
			'BEHAT_FEATURE_TITLE'  => self::$feature->getTitle(),
			'BEHAT_SCENARIO_TITLE' => $this->scenario->getTitle(),
		);

	}

}
