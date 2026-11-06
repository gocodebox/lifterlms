<?php
/**
 * Pluggable functions and test helpers loaded by the lifterlms-tests bootstrap before the plugin
 *
 * @package LifterLMS/Tests
 *
 * @since [version]
 */

/**
 * Plug llms_constant() to allow mocking a constant's value via llms_tests_set_constant().
 *
 * @since [version]
 *
 * @param string $name Constant name.
 * @return mixed Mocked value when one exists, otherwise the real constant's value.
 */
function llms_constant( $name ) {
	global $llms_tests_constants;
	if ( array_key_exists( $name, (array) $llms_tests_constants ) ) {
		return $llms_tests_constants[ $name ];
	}
	return constant( $name );
}

/**
 * Plug llms_defined() to treat constants mocked via llms_tests_set_constant() as defined.
 *
 * @since [version]
 *
 * @param string $name Constant name.
 * @return bool Returns `true` if the constant is mocked or defined and `false` otherwise.
 */
function llms_defined( $name ) {
	global $llms_tests_constants;
	return array_key_exists( $name, (array) $llms_tests_constants ) || defined( $name );
}

/**
 * Mock the value of a constant read via llms_constant() and llms_defined().
 *
 * LLMS_UnitTestCase removes mocks on teardown. Other test cases must call llms_tests_reset_constants().
 *
 * @since [version]
 *
 * @param string $name  Constant name.
 * @param mixed  $value Mocked value.
 * @return void
 */
function llms_tests_set_constant( $name, $value ) {
	global $llms_tests_constants;
	$llms_tests_constants[ $name ] = $value;
}

/**
 * Remove all mocked constants.
 *
 * @since [version]
 *
 * @return void
 */
function llms_tests_reset_constants() {
	global $llms_tests_constants;
	$llms_tests_constants = array();
}
