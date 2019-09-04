<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * LifterLMS Options Table Data Store Abstract
 *
 * @since   3.8.0
 * @version 3.17.8
 */
abstract class LLMS_Abstract_Options_Data {

	protected $option_prefix = 'llms_';

	/**
	 * Retrieve the value of an option from the database
	 *
	 * @param    string $name     option name (unprefixed)
	 * @param    mixed  $default  default value to use if no option is found
	 * @return   mixed
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_option( $name, $default = '' ) {
		$val = get_option( $this->get_option_name( $name ), '' );
		if ( '' === $val ) {
			return $default;
		}
		return $val;
	}

	/**
	 * Retrieve a prefix for options
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function get_option_prefix() {
		return $this->option_prefix;
	}

	/**
	 * Retrieve a prefixed option name from the database
	 * Prefix automatically adds a trigger and type to the option name
	 * in addition to llms_notification
	 *
	 * @param    string $name  option name (unprefixed)
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_option_name( $name ) {
		return $this->get_option_prefix() . $name;
	}

	/**
	 * Set the value of an option
	 *
	 * @param    string $name   option name (unprefixed)
	 * @param    mixed  $value  option value
	 * @return   bool               true if option value has changed
	 *                              false if no update or update failed
	 * @since    3.17.8
	 * @version  3.17.8
	 */
	public function set_option( $name, $value ) {
		return update_option( $this->get_option_name( $name ), $value );
	}

}
