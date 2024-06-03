<?php
/**
 * LLMS_Add_On model class file.
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 3.22.0
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Add-On Model
 *
 * @since 3.22.0
 */
class LLMS_Add_On {

	/**
	 * Add On ID
	 *
	 * @var string
	 */
	private $id = '';

	/**
	 * Add On Data
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Constructor
	 *
	 * @since 3.22.0
	 * @since 4.21.3 Move lookup logic to its own private method: `lookup_add_on()`.
	 *
	 * @param string|array $addon      Add-on data array or a string (such as an ID or update file path) used to lookup the addon.
	 * @param string       $lookup_key If $addon is a string, this determines how to lookup the addon from the available list of addons.
	 * @return void
	 */
	public function __construct( $addon = array(), $lookup_key = 'id' ) {

		if ( is_string( $addon ) ) {
			$addon = $this->lookup_add_on( $lookup_key, $addon );
		}

		$this->id   = ! empty( $addon['id'] ) ? $addon['id'] : '';
		$this->data = $addon ? $addon : array();

	}

	/**
	 * Magic getter to retrieve add-on props from private $data array
	 *
	 * @since 3.22.0
	 *
	 * @param string $key Property key.
	 * @return mixed
	 */
	public function __get( $key ) {
		return isset( $this->data[ $key ] ) ? $this->data[ $key ] : '';
	}

	/**
	 * Activate an add-on
	 *
	 * @since 3.22.0
	 * @since 3.25.0 Unknown.
	 *
	 * @return string|WP_Error
	 */
	public function activate() {

		$ret = false;
		if ( 'plugin' === $this->get( 'type' ) ) {

			$ret = activate_plugins( $this->get( 'update_file' ) );

		} elseif ( 'theme' === $this->get( 'type' ) ) {

			$ret = true;
			switch_theme( $this->get( 'update_file' ) );

		}

		if ( true === $ret ) {
			// Translators: %s = Add-on name.
			return sprintf( __( '%s was successfully activated.', 'lifterlms' ), $this->get( 'title' ) );
		}

		// Translators: %s = Add-on name.
		return new WP_Error( 'activation', sprintf( __( 'Could not activate %s.', 'lifterlms' ), $this->get( 'title' ) ) );

	}

	/**
	 * Deactivate the addon
	 *
	 * @since 3.22.0
	 * @since 4.21.3 Updated the failure error code from 'activation' to 'deactivation'.
	 *
	 * @return string|WP_Error
	 */
	public function deactivate() {

		$ret = false;

		if ( 'plugin' === $this->get( 'type' ) ) {

			deactivate_plugins( $this->get( 'update_file' ) );
			// Translators: %s = Add-on name.
			return sprintf( __( '%s was successfully deactivated.', 'lifterlms' ), $this->get( 'title' ) );

		}

		// Translators: %s = Add-on name.
		return new WP_Error( 'deactivation', sprintf( __( 'Could not deactivate %s.', 'lifterlms' ), $this->get( 'title' ) ) );

	}

	/**
	 * Get add-on properties
	 *
	 * @since 3.22.0
	 *
	 * @param string $key Property key.
	 * @return mixed
	 */
	public function get( $key ) {
		return $this->$key;
	}

	/**
	 * Retrieve the update channel for the addon
	 *
	 * @since 3.22.0
	 *
	 * @return string
	 */
	public function get_channel_subscription() {
		return 'stable';
	}

	/**
	 * Determine the status of an addon's license
	 *
	 * @since 3.22.0
	 *
	 * @param bool $translate If `true`, returns the translated string for on-screen display.
	 * @return string
	 */
	public function get_install_status( $translate = false ) {

		if ( ! $this->is_installable() ) {
			$ret = 'none';
		} else {
			$ret = $this->is_installed() ? 'installed' : 'uninstalled';
		}

		return $translate ? $this->get_l10n( $ret ) : $ret;

	}

	/**
	 * Get the currently installed version of an addon
	 *
	 * @since 3.22.0
	 *
	 * @return string
	 */
	public function get_installed_version() {
		if ( $this->is_installable() && $this->is_installed() ) {
			$type = $this->get( 'type' );
			if ( 'plugin' === $type ) {
				$data = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $this->get( 'update_file' ) );
				return $data['Version'];
			} elseif ( 'theme' === $type ) {
				$data = wp_get_theme( $this->get( 'update_file' ) );
				return $data->get( 'Version' );
			}
		}
		return '';
	}

	/**
	 * Retrieve the latest available version for the current channel
	 *
	 * @since 3.22.0
	 *
	 * @return string
	 */
	public function get_latest_version() {
		if ( 'beta' === $this->get_channel_subscription() && $this->get( 'version_beta' ) ) {
			return $this->get( 'version_beta' );
		}
		return $this->get( 'version' );
	}

	/**
	 * Translate strings
	 *
	 * @since 3.22.0
	 *
	 * @param string $string Untranslated string / key.
	 * @return string
	 */
	public function get_l10n( $string ) {

		$strings = array(

			'active'           => __( 'Active', 'lifterlms' ),
			'inactive'         => __( 'Inactive', 'lifterlms' ),

			'installed'        => __( 'Installed', 'lifterlms' ),
			'uninstalled'      => __( 'Not Installed', 'lifterlms' ),

			'activate'         => __( 'Activate', 'lifterlms' ),
			'deactivate'       => __( 'Deactivate', 'lifterlms' ),
			'install'          => __( 'Install', 'lifterlms' ),

			'none'             => __( 'N/A', 'lifterlms' ),

			'license_active'   => __( 'Licensed', 'lifterlms' ),
			'license_inactive' => __( 'Unlicensed', 'lifterlms' ),

		);

		return $strings[ $string ];

	}

	/**
	 * Determine the status of an addon's license
	 *
	 * @since 3.22.0
	 *
	 * @param bool $translate If `true`, returns the translated string for on-screen display.
	 * @return string
	 */
	public function get_license_status( $translate = false ) {

		if ( ! llms_parse_bool( $this->get( 'has_license' ) ) ) {
			$ret = 'none';
		} else {
			$ret = $this->is_licensed() ? 'license_active' : 'license_inactive';
		}

		return $translate ? $this->get_l10n( $ret ) : $ret;

	}

	/**
	 * Retrieve a utm'd link to the add-on
	 *
	 * @since 3.22.0
	 * @since 4.21.3 Use `rawurlencode()` in favor of `urlencode()`.
	 *
	 * @return string
	 */
	public function get_permalink() {

		$url = add_query_arg(
			array(
				'utm_source'   => rawurlencode( 'LifterLMS Plugin' ),
				'utm_campaign' => rawurlencode( 'Plugin to Sale' ),
				'utm_medium'   => rawurlencode( 'Add-Ons Screen' ),
				'utm_content'  => rawurlencode( sprintf( '%1$s Ad %2$s', $this->get( 'title' ), LLMS_VERSION ) ),
			),
			$this->get( 'permalink' )
		);

		return $url;

	}

	/**
	 * Get the type of addon
	 *
	 * @since 3.22.0
	 * @since 4.21.3 Use strict comparison for `in_array()`.
	 *
	 * @return string
	 */
	public function get_type() {

		$type = $this->get( 'type' );

		if ( $type ) {
			return $type;
		}

		$cats = array_keys( $this->get( 'categories' ) );

		if ( in_array( 'bundles', $cats, true ) ) {
			$type = 'bundle';
		} elseif ( in_array( 'third-party', $cats, true ) ) {
			$type = 'external';
		} else {
			$type = 'support';
		}

		return $type;

	}

	/**
	 * Get the addon's status
	 *
	 * @since 3.22.0
	 * @param bool $translate If `true`, translates the status for on-screen display.
	 * @return string
	 */
	public function get_status( $translate = false ) {

		if ( ! $this->is_installable() ) {
			$ret = 'none';
		} elseif ( $this->is_installed() ) {
			$ret = $this->is_active() ? 'active' : 'inactive';
		} else {
			$ret = 'uninstalled';
		}

		if ( $translate ) {
			$ret = $this->get_l10n( $ret );
		}

		return $ret;

	}

	/**
	 * Get the addon or author image URL for the add-on.
	 *
	 * @since 7.5.0
	 *
	 * @param string $type Type of image to retrieve. Defaults to 'addon'. Accepts 'addon' or 'author'.
	 * @return string
	 */
	public function get_image( $type = 'addon' ) {

		$img = 'author' === $type ? $this->get( 'author' )['image'] : $this->get( 'image' );

		if ( ! $img ) {
			return '';
		}

		if ( is_readable( llms()->plugin_path() . '/assets/images/addons/' . basename( $img ) ) ) {
			return llms()->plugin_url() . '/assets/images/addons/' . basename( $img );
		}

		return $img;
	}

	/**
	 * Determine if there is an available update for the add-on
	 *
	 * @since 3.22.0
	 *
	 * @return bool
	 */
	public function has_available_update() {
		return version_compare( $this->get_installed_version(), $this->get_latest_version(), '<' );
	}

	/**
	 * Determine if an installable addon is active
	 *
	 * @since 3.22.0
	 *
	 * @return bool
	 */
	public function is_active() {

		if ( $this->is_installable() && $this->is_installed() ) {

			$file = $this->get( 'update_file' );
			$type = $this->get_type();
			if ( 'plugin' === $type ) {
				return is_plugin_active( $file );
			} elseif ( 'theme' === $type ) {
				$theme = wp_get_theme();
				return ( $file === $theme->get_stylesheet() );
			}
		}

		return false;

	}

	/**
	 * Determines if the add-on is installable
	 *
	 * @since 3.22.0
	 * @since 3.22.1 Unknown.
	 * @since 4.21.3 Use strict comparison for `in_array()`.
	 *
	 * @return boolean
	 */
	public function is_installable() {
		return ( $this->get( 'update_file' ) && in_array( $this->get_type(), array( 'plugin', 'theme' ), true ) );
	}

	/**
	 * Determine if the add-on is currently installed
	 *
	 * @since 3.22.0
	 * @since 4.21.3 Use strict comparison for `in_array()`.
	 *
	 * @return bool
	 */
	public function is_installed() {

		if ( ! $this->is_installable() ) {
			return false;
		}

		$type = $this->get_type();

		if ( 'plugin' === $type ) {
			return in_array( $this->get( 'update_file' ), array_keys( get_plugins() ), true );
		} elseif ( 'theme' === $type ) {
			return wp_get_theme( $this->get( 'update_file' ) )->exists();
		}

		return false;

	}

	/**
	 * Determines if the add-on is licensed
	 *
	 * @since 3.22.0
	 *
	 * @return bool
	 */
	public function is_licensed() {
		return false;
	}

	/**
	 * Locate an add-on by a key/val pair
	 *
	 * Loads add-ons via `llms_get_add_ons()` and loops through the items list
	 * to find an addon specified by the key/val pair.
	 *
	 * @since 4.21.3
	 *
	 * @param string $lookup_key Key found within the add-on item. EG: "id" or "update_file".
	 * @param string $lookup_val Value of the key to match.
	 * @return array|false Returns the add-on data array of `false` if no found.
	 */
	private function lookup_add_on( $lookup_key, $lookup_val ) {

		$addons = llms_get_add_ons();

		// Error communicating with the API or no items found.
		if ( is_wp_error( $addons ) || empty( $addons['items'] ) ) {
			return false;
		}

		// Loop through the list.
		foreach ( $addons['items'] as $addon ) {

			// We've found a match.
			if ( isset( $addon[ $lookup_key ] ) && $addon[ $lookup_key ] === $lookup_val ) {
				return $addon;
			}
		}

		return false;

	}

	/**
	 * Verifies the add-on can be uninstalled, and performs the uninstall (permanently deleting its files)
	 *
	 * @since 5.1.1
	 *
	 * @return string|WP_Error Success message or an error object.
	 */
	public function uninstall() {

		$title = $this->get( 'title' );

		if ( ! $this->is_installed() ) {
			// Translators: %s = Add-on title.
			return new WP_Error( 'not-installed', sprintf( __( '%s is not installed.', 'lifterlms' ), $title ) );
		}

		if ( $this->is_active() ) {
			// Translators: %s = Add-on title.
			return new WP_Error( 'uninstall-active', sprintf( __( '%s is active and cannot be uninstalled.', 'lifterlms' ), $title ) );
		}

		return $this->uninstall_real();

	}

	/**
	 * Actually performs the uninstall
	 *
	 * @since 5.1.1
	 *
	 * @return string|WP_Error Success message or an error object.
	 */
	private function uninstall_real() {

		$type = $this->get_type();

		if ( ! in_array( $type, array( 'plugin', 'theme' ), true ) ) {
			// Translators: %s = add-on type.
			return new WP_Error( 'uninstall-invalid-type', sprintf( __( 'Cannot uninstall "%s" type add-ons.', 'lifterlms' ), $type ) );
		}

		$file = $this->get( 'update_file' );

		if ( 'plugin' === $type ) {
			uninstall_plugin( $file );
			$del = delete_plugins( array( $file ) );
		} else {
			$del = delete_theme( $file );
		}

		if ( is_wp_error( $del ) ) {
			return $del;
		}

		// Translators: %s = Add-on title.
		return sprintf( __( '%s was successfully uninstalled.', 'lifterlms' ), $this->get( 'title' ) );

	}

}
