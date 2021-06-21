<?php
/**
 * LLMS_Shortcode_User_Info class.
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since 5.0.0
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS User Information Shortcode.
 *
 * Shortcode: [llms-user]
 *
 * @since 5.0.0
 */
class LLMS_Shortcode_User_Info extends LLMS_Shortcode {

	/**
	 * Shortcode tag
	 *
	 * @var  string
	 */
	public $tag = 'llms-user';

	/**
	 * Retrieves a list of keys that cannot be displayed by the shortcode.
	 *
	 * @since 5.0.0
	 *
	 * @return string[]
	 */
	protected function get_blocklist() {

		/**
		 * Filters the list of keys which cannot be displayed using the [user] shortcode
		 *
		 * @since 5.0.0
		 *
		 * @param string[] $keys List of user and usermeta keys.
		 */
		return apply_filters( 'llms_user_info_shortcode_blocked_keys', array( 'user_pass' ) );

	}

	/**
	 * Retrieves an array of default attributes which are automatically merged
	 * with the user submitted attributes and passed to $this->get_output()
	 *
	 * @since 5.0.0
	 *
	 * @return array
	 */
	protected function get_default_attributes() {
		return array(
			'key' => '',
			'or'  => '',
		);
	}

	/**
	 * Retrieve the actual content of the shortcode
	 *
	 * $atts & $content are both filtered before being passed to get_output()
	 * output is filtered so the return of get_output() doesn't need its own filter
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	protected function get_output() {

		/**
		 * Filters the user used to retrieve user information displayed by the [llms-user] shortcode
		 *
		 * @since 5.0.0
		 *
		 * @param integer $user_id The WP_User ID of the currently logged-in user or `0` if no user logged in.
		 */
		$user_id = apply_filters( 'llms_user_info_shortcode_user_id', get_current_user_id() );
		$key     = $this->get_attribute( 'key' );
		$default = $this->get_attribute( 'or' );

		if ( in_array( $key, $this->get_blocklist(), true ) ) {
			return '';
		}

		// No user OR no key provided.
		if ( ! $user_id || ! $key ) {
			return $default;
		}

		$user = new WP_User( $user_id );
		$val  = $user->exists() ? $user->get( $key ) : null;

		return ! empty( $val ) && is_scalar( $val ) ? $val : $default;

	}

	/**
	 * Merge user attributes with default attributes.
	 *
	 * @since 5.0.0
	 *
	 * @param array $atts User-submitted shortcode attributes.
	 *
	 * @return array
	 */
	protected function set_attributes( $atts = array() ) {

		// Allow `key` attribute to be submitted without a key, eg: [llms-user first_name].
		if ( isset( $atts[0] ) ) {
			$atts['key'] = $atts[0];
			unset( $atts[0] );
		}

		return parent::set_attributes( $atts );

	}

}

return LLMS_Shortcode_User_Info::instance();
