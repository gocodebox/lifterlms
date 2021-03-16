<?php
/**
 * LifterLMS Options Table Data Store abstract class
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 3.8.0
 * @version 3.17.8
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
	 * Retrieve the value of an option from the database
	 *
	 * @since 3.8.0
	 *
	 * @param string $name    Option name (unprefixed).
	 * @param mixed  $default Default value to use if no option is found.
	 * @return mixed
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
