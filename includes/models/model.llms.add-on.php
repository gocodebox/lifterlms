<?php
/**
 * LifterLMS Add-On Model
 *
 * @package  LifterLMS/Models
 * @since    3.22.0
 * @version  3.25.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Add_On model.
 */
class LLMS_Add_On {

	/**
	 * Add On ID
	 *
	 * @var  string
	 */
	private $id = '';

	/**
	 * Add On Data
	 *
	 * @var  array
	 */
	private $data = array();

	/**
	 * Constructor
	 *
	 * @param    array  $addon       array of addon data
	 * @param    string $lookup_key  if $addon is a string, this determines how to lookup the addon from the available list of addons
	 * @return   void
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	public function __construct( $addon = array(), $lookup_key = 'id' ) {

		if ( is_string( $addon ) ) {

			$lookup_val = $addon;
			$addons     = llms_get_add_ons();
			if ( ! empty( $addons['items'] ) ) {
				foreach ( $addons['items'] as $addon ) {

					if ( isset( $addon[ $lookup_key ] ) && $addon[ $lookup_key ] == $lookup_val ) {
						$this->data = $addon;
						break;
					}
				}
			}
		}

		$this->data = $addon;
		$this->id   = $addon['id'];

	}

	/**
	 * Magic getter to retrieve add-on props from private $data array
	 *
	 * @param    string $key  property key
	 * @return   mixed
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	public function __get( $key ) {
		return isset( $this->data[ $key ] ) ? $this->data[ $key ] : '';
	}

	/**
	 * Activate an add-on
	 *
	 * @return   string|WP_Error
	 * @since    3.22.0
	 * @version  3.25.0
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
			/* Translators: %s = Add-on name */
			return sprintf( __( '%s was successfully activated.', 'lifterlms' ), $this->get( 'title' ) );
		}

		/* Translators: %s = Add-on name */
		return new WP_Error( 'activation', sprintf( __( 'Could not activate %s.', 'lifterlms' ), $this->get( 'title' ) ) );

	}

	/**
	 * Deactivate the addon
	 *
	 * @return   string|WP_Error
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	public function deactivate() {

		$ret = false;
		if ( 'plugin' === $this->get( 'type' ) ) {

			deactivate_plugins( $this->get( 'update_file' ) );
			/* Translators: %s = Add-on name */
			return sprintf( __( '%s was successfully deactivated.', 'lifterlms' ), $this->get( 'title' ) );

		}

		/* Translators: %s = Add-on name */
		return new WP_Error( 'activation', sprintf( __( 'Could not deactivate %s.', 'lifterlms' ), $this->get( 'title' ) ) );

	}

	/**
	 * Get add-on properties
	 *
	 * @param    string $key  property key
	 * @return   mixed
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	public function get( $key ) {
		return $this->$key;
	}

	/**
	 * Retrieve the update channel for the addon
	 *
	 * @return   string
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	public function get_channel_subscription() {
		return 'stable';
	}

	/**
	 * Determine the status of an addon's license
	 *
	 * @param    bool $translate   if true, returns the translated string for on-screen display
	 * @return   string
	 * @since    3.22.0
	 * @version  3.22.0
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
	 * @return   string
	 * @since    3.22.0
	 * @version  3.22.0
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
	 * @return   strin
	 * @since    3.22.0
	 * @version  3.22.0
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
	 * @param    string $status  untranslated string / key
	 * @return   string
	 * @since    3.22.0
	 * @version  3.22.0
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
	 * @param    bool $translate   if true, returns the translated string for on-screen display
	 * @return   string
	 * @since    3.22.0
	 * @version  3.22.0
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
	 * @return   string
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	public function get_permalink() {

		$url = add_query_arg(
			array(
				'utm_source'   => urlencode( 'LifterLMS Plugin' ),
				'utm_campaign' => urlencode( 'Plugin to Sale' ),
				'utm_medium'   => urlencode( 'Add-Ons Screen' ),
				'utm_content'  => urlencode( sprintf( '%1$s Ad %2$s', $this->get( 'title' ), LLMS_VERSION ) ),
			),
			$this->get( 'permalink' )
		);

		return $url;

	}

	/**
	 * Get the type of addon
	 *
	 * @return   string
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	public function get_type() {

		$type = $this->get( 'type' );

		if ( $type ) {
			return $type;
		}

		$cats = array_keys( $this->get( 'categories' ) );

		if ( in_array( 'bundles', $cats ) ) {
			$type = 'bundle';
		} elseif ( in_array( 'third-party', $cats ) ) {
			$type = 'external';
		} else {
			$type = 'support';
		}

		return $type;

	}

	/**
	 * Get the addon's status
	 *
	 * @param    bool $translate  if true, translates the status for on-screen display
	 * @return   string
	 * @since    3.22.0
	 * @version  3.22.0
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
	 * Determine if there is an available update for the add-on
	 *
	 * @return   bool
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	public function has_available_update() {
		return version_compare( $this->get_installed_version(), $this->get_latest_version(), '<' );
	}

	/**
	 * Determine if an installable addon is active
	 *
	 * @return   bool
	 * @since    3.22.0
	 * @version  3.22.0
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
	 * @return   bool
	 * @since    3.22.0
	 * @version  3.22.1
	 */
	public function is_installable() {
		return ( $this->get( 'update_file' ) && in_array( $this->get_type(), array( 'plugin', 'theme' ) ) );
	}

	/**
	 * Determine if the add-on is currently installed
	 *
	 * @return   bool
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	public function is_installed() {

		if ( ! $this->is_installable() ) {
			return false;
		}

		$type = $this->get_type();

		if ( 'plugin' === $type ) {
			return in_array( $this->get( 'update_file' ), array_keys( get_plugins() ) );
		} elseif ( 'theme' === $type ) {
			return wp_get_theme( $this->get( 'update_file' ) )->exists();
		}

		return false;

	}

	/**
	 * Determines if the add-on is licensed
	 *
	 * @return   bool
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	public function is_licensed() {
		return false;
	}

}
