<?php
/**
 * Get & Set Helper options
 *
 * @package LifterLMS_Helper/Classes
 *
 * @since 3.0.0
 * @version 3.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Helper_Options
 *
 * @since 3.0.0
 * @since 3.2.0 Use `$instance` in favor of `$_instance`.
 */
class LLMS_Helper_Options {

	/**
	 * Singleton instance
	 *
	 * @var null|LLMS_Helper_Options
	 */
	protected static $instance = null;

	/**
	 * Main Instance
	 *
	 * @since 3.0.0
	 * @since 3.2.0 Use `self::$instance` in favor of `self::$_instance`.
	 *
	 * @return LLMS_Helper_Options
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Retrive a single option
	 *
	 * @since 3.0.0
	 *
	 * @param string $key     Option name.
	 * @param mixed  $default Default option value if option isn't already set.
	 * @return mixed
	 */
	private function get_option( $key, $default = '' ) {

		$options = $this->get_options();

		if ( isset( $options[ $key ] ) ) {
			return $options[ $key ];
		}

		return $default;
	}

	/**
	 * Retrieve all upgrader options array
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	private function get_options() {
		return get_option( 'llms_helper_options', array() );
	}

	/**
	 * Update the value of an option
	 *
	 * @since 3.0.0
	 *
	 * @param string $key Option name.
	 * @param mixed  $val Option value.
	 * @return boolean True if option value has changed, false if not or if update failed.
	 */
	private function set_option( $key, $val ) {

		$options         = $this->get_options();
		$options[ $key ] = $val;
		return update_option( 'llms_helper_options', $options, false );
	}

	/**
	 * Get info about addon channel subscriptions
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_channels() {
		return $this->get_option( 'channels', array() );
	}

	/**
	 * Set info about addon channel subscriptions
	 *
	 * @since 3.0.0
	 *
	 * @param array $channels Array of channel information.
	 * @return boolean True if option value has changed, false if not or if update failed.
	 */
	public function set_channels( $channels ) {
		return $this->set_option( 'channels', $channels );
	}

	/**
	 * Retrieve a timestamp for the last time the keys check cron was run
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_last_keys_cron_check() {
		return $this->get_option( 'last_keys_cron_check', 0 );
	}

	/**
	 * Set the last cron check time
	 *
	 * @since 3.0.0
	 *
	 * @param int $time Timestamp.
	 * @return boolean True if option value has changed, false if not or if update failed.
	 */
	public function set_last_keys_cron_check( $time ) {
		return $this->set_option( 'last_keys_cron_check', $time );
	}

	/**
	 * Retrieve saved license key data
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_license_keys() {
		return $this->get_option( 'license_keys', array() );
	}

	/**
	 * Update saved license key data
	 *
	 * @since 3.0.0
	 *
	 * @param array $keys Key data to save.
	 * @return boolean True if option value has changed, false if not or if update failed.
	 */
	public function set_license_keys( $keys ) {
		return $this->set_option( 'license_keys', $keys );
	}
}
