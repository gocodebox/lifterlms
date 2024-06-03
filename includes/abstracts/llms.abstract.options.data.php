<?php
/**
 * LifterLMS Options Table Data Store abstract class
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 3.8.0
 * @version 4.21.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Options Table Data Store abstract
 *
 * @since 3.8.0
 */
abstract class LLMS_Abstract_Options_Data {

	/**
	 * Option name prefix.
	 *
	 * @var string
	 */
	protected $option_prefix = 'llms_';

	/**
	 * Options data abstract version
	 *
	 * This is used to determine the behavior of the `get_option()` method.
	 *
	 * Concrete classes should use version 2 in order to use the new (future default)
	 * behavior of the method.
	 *
	 * @var int
	 */
	protected $version = 1;

	/**
	 * Retrieve the value of an option from the database
	 *
	 * @since 3.8.0
	 * @since 4.21.0 Changed the behavior of the function when the concrete class defines `$this->version` greater than 1.
	 *
	 * @param string $name     Option name (unprefixed).
	 * @param mixed  $default  Default value to use if no option is found.
	 * @return mixed The option value.
	 */
	public function get_option( $name, $default = false ) {

		$full_name = $this->get_option_name( $name );

		// If the class is version 1, use the old method.
		if ( 1 === $this->version ) {
			// If only one argument is passed switch the default to the old argument default (an empty string).
			$default = 1 === func_num_args() ? '' : $default;
			return $this->get_option_deprecated( $full_name, $default );
		}

		add_filter( "default_option_{$full_name}", array( $this, 'get_option_default_value' ), 10, 3 );

		// Call this way so that the `$passed_default_value` of the filter is accurate based on the number of arguments actually passed.
		$args = func_num_args() > 1 ? array( $full_name, $default ) : array( $full_name );
		$val  = get_option( ...$args );

		remove_filter( "default_option_{$full_name}", array( $this, 'get_option_default_value' ), 10, 3 );

		return $val;

	}

	/**
	 * Retrieve the value of an option from the database
	 *
	 * This is the "old" (to be deprecated) version of the function.
	 *
	 * We will transition extending classes little by little to use the new behavior and deprecate this once
	 * all classes are fully transitioned.
	 *
	 * @since 4.21.0
	 *
	 * @param string $name     Full (prefixed) option name.
	 * @param mixed  $default  Default value to use if no option is found.
	 * @return mixed The option value.
	 */
	private function get_option_deprecated( $name, $default = '' ) {
		$val = get_option( $name, '' );
		if ( '' === $val ) {
			return $default;
		}
		return $val;
	}

	/**
	 * Option default value autoloader
	 *
	 * By default, this method does nothing but extending classes can implement an autoloader to pull
	 * default values from other sources.
	 *
	 * This is a callback function for the WP core filter `default_option_{$option}`.
	 *
	 * @since 4.21.0
	 *
	 * @param mixed  $default_value        The default value. If no value is passed to `get_option()`, this will be an empty string.
	 *                                     Otherwise it will be the default value passed to the method.
	 * @param string $full_option_name     The full (prefixed) option name.
	 * @param bool   $passed_default_value Whether or not a default value was passed to `get_option()`.
	 * @return mixed The default option value.
	 */
	public function get_option_default_value( $default_value, $full_option_name, $passed_default_value ) {
		return $default_value;
	}

	/**
	 * Retrieve a prefix for options
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	protected function get_option_prefix() {
		return $this->option_prefix;
	}

	/**
	 * Retrieve a prefixed option name from the database
	 * Prefix automatically adds a trigger and type to the option name
	 * in addition to llms_notification
	 *
	 * @since 3.8.0
	 *
	 * @param string $name Option name (unprefixed).
	 * @return string
	 */
	public function get_option_name( $name ) {
		return $this->get_option_prefix() . $name;
	}

	/**
	 * Set the value of an option
	 *
	 * @since 3.17.8
	 *
	 * @param string $name  Option name (unprefixed).
	 * @param mixed  $value Option value.
	 * @return bool Returns `true` if option value has changed and `false` if no update or the update failed.
	 */
	public function set_option( $name, $value ) {
		return update_option( $this->get_option_name( $name ), $value );
	}

}
