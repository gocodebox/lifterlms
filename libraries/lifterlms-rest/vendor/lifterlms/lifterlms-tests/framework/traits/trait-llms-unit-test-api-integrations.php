<?php
/**
 * Utilities for external API integration tests.
 *
 * @since 3.3.0
 */
trait LLMS_Unit_Test_API_Integrations {

	/**
	 * List of env variables required to run api integration tests.
	 *
	 * If any one of the following variables is present, the integration tests
	 * will be run.
	 *
	 * @var string[]
	 */
	protected $api_integration_env_vars = array(
		'RUN_API_INTEGRATION_TESTS',
		'RUN_CODE_COVERAGE',
	);

	/**
	 * Conditionally skip tests requiring an API request to an external resource.
	 *
	 * If the test method or class is annotated with `@apiIntegration` and at least one
	 * of the environment variables listed in `$this->api_integration_env_vars` is present
	 * the test will run. Otherwise the test is skipped.
	 *
	 * @since 3.3.0
	 *
	 * @return [type] [description]
	 */
	protected function skip_api_integration_test() {

		if ( ! $this->is_api_integration_var_set() && $this->is_api_integration_test() ) {
			$this->markTestSkipped( 'API Integration tests skipped in this environment.' );
		}

	}

	/**
	 * Get annotations.
	 *
	 * Uses different methods depending on the version of phpunit.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	protected function get_annotations() {

		// PHPUnit < 9.5.0.
		if ( method_exists( $this, 'getAnnotations' ) ) {
			return $this->getAnnotations();
		}

		// PHPUnit >= 9.5.0.
		return \PHPUnit\Util\Test::parseTestMethodAnnotations(
			static::class,
			$this->getName( false )
		);

	}

	/**
	 * Parses annotations and determines if the `@apiIntegration` tag is set on the class or method.
	 *
	 * @since 3.3.0
	 *
	 * @return boolean
	 */
	protected function is_api_integration_test() {

		$annotations = $this->get_annotations();
		foreach ( array( 'class', 'method' ) as $depth ) {
			if ( isset( $annotations[ $depth ]['apiIntegration'] ) ) {
				return true;
			}
		}

		return false;

	}

	/**
	 * Determines if at least one of the variables in `$this->api_integration_env_vars` is set.
	 *
	 * @since 3.3.0
	 *
	 * @return boolean
	 */
	protected function is_api_integration_var_set() {

		foreach ( $this->api_integration_env_vars as $var ) {
			if ( getenv( $var ) ) {
				return true;
			}
		}

		return false;

	}

}
