<?php
defined( 'ABSPATH' ) || exit;

class LLMS_AddOn_Upgrader {

	/**
	 * LifterLMS.com REST API URL
	 * @since    [version]
	 * @version  [version]
	 */
	const API_URL = 'https://lifterlms.com/wp-json/llms/v3';

	public function __construct() {
	}

	/**
	 * Make an API request to LifterLMS.com
	 * @param    string     $endpoint  api endpint
	 * @param    array      $body      array of data to send with the request
	 * @param    string     $method    request method
	 * @return   object
	 * @since    [version]
	 * @version  [version]
	 */
	private function make_request( $endpoint, $body = array(), $method = 'POST' ) {

		$url = apply_filters( 'llms_addon_upgrader_request_url', self::API_URL . $endpoint );
		$data = apply_filters( 'llms_addon_upgrader_request_data', array(
			'body' => $body,
			'method' => $method,
		), $url );

		return wp_remote_request( $url, $data );

	}

	/**
	 * Retrieve all upgrader options array
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_options() {
		return get_option( 'llms_addon_upgrader', array() );
	}

	/**
	 * Retrive a single option
	 * @param    string     $key      option name
	 * @param    mixed      $default  default option value if option isn't already set
	 * @return   mixed
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_option( $key, $default = '' ) {

		$options = $this->get_options();

		if ( isset( $options[ $key ] ) ) {
			return $options[ $key ];
		}

		return $default;

	}

	/**
	 * Update the value of an option
	 * @param    string     $key  option name
	 * @param    mixed      $val  option value
	 * @return   boolean          True if option value has changed, false if not or if update failed.
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_option( $key, $val ) {

		$options = $this->get_options();
		$options[ $key ] = $val;
		return update_option( 'llms_addon_upgrader', $options, false );

	}


	public function get_products_for_keys( $keys ) {

		// sanitize before sending
		$keys = explode( ' ', sanitize_text_field( $keys ) );
		$keys = array_map( 'trim', $keys );
		$keys = array_unique( $keys );

		$data = array(
			'keys' => $keys,
			// 'url' => get_site_url(),
		);

		$post = $this->make_request( '/activate/available', $data );

		if ( is_wp_error( $post ) ) {
			return $post;
		}

		return json_decode( wp_remote_retrieve_body( $post ), true );

	}

	public function get_products() {

		$data = false;
		// $data = get_transient( 'llms_products_api_result' );

		if ( false === $data ) {

			$get = $this->make_request( '/products', array(), 'GET' );

			if ( is_wp_error( $get ) ) {
				return $get;
			}

			$data = json_decode( $get['body'], true );
			set_transient( 'llms_products_api_result', $data, DAY_IN_SECONDS );

		}

		return $data;

	}

	public function get_available_products() {
		return $this->get_option( 'available_products', array() );
	}

	public function set_available_products( $products ) {
		return $this->set_option( 'available_products', $products );
	}


	public function get_available() {
		return array();
	}

	public function get_installed() {
		return array();
	}


	public function get_keys() {
		return $this->get_option( 'keys', array() );
	}

	public function set_keys( $keys ) {
		return $this->set_option( 'keys', $keys );
	}

}
