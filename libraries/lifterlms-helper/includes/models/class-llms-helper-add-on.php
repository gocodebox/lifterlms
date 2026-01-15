<?php
/**
 * Extends core class to allow interaction with the .com api
 *
 * @package LifterLMS_Helper/Models
 *
 * @since 3.0.0
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Helper_Add_On
 *
 * @since 3.0.0
 * @since 3.2.0 Moved from `includes/model-llms-helper-add-on.php`.
 */
class LLMS_Helper_Add_On extends LLMS_Add_On {

	/**
	 * Find a license key for the add-on
	 *
	 * @since 3.0.0
	 * @since 3.2.0 Use strict comparison for `in_array()`.
	 * @since 3.2.1 Use `requires_license()` rather than checking the add-on's `has_license` prop directly.
	 *
	 * @return string|false
	 */
	public function find_license() {

		/**
		 * If the addon doesn't require a license return the first found license to ensure
		 * that the core can be updated via a license when subscribed to a beta channel
		 * and that the helper can always be upgraded.
		 */
		$requires_license = $this->requires_license();

		$id = $this->get( 'id' );
		foreach ( llms_helper_options()->get_license_keys() as $data ) {
			/**
			 * 1. If license is not required, return the first license found.
			 * 2. If the addon matches the licensed product
			 * 3. If the addon is included in the licensed bundle product.
			 */
			if ( ! $requires_license || $id === $data['product_id'] || in_array( $id, $data['addons'], true ) ) {
				return $data;
			}
		}

		return false;
	}

	/**
	 * Retrieve the update channel for the addon
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_channel_subscription() {
		$channels = llms_helper_options()->get_channels();
		return isset( $channels[ $this->get( 'id' ) ] ) ? $channels[ $this->get( 'id' ) ] : 'stable';
	}

	/**
	 * Retrieve download information for an add-on
	 *
	 * @since 3.0.0
	 * @since 3.2.1 Allow getting download info for add-ons which do not require licenses.
	 * @since 3.4.0 Use core textdomain.
	 *
	 * @return WP_Error|array
	 */
	public function get_download_info() {

		$key = $this->find_license();

		if ( $this->requires_license() && ! $key ) {
			return new WP_Error( 'no_license', __( 'Unable to locate a license key for the selected add-on.', 'lifterlms' ) );
		}

		$args = array(
			'url'         => get_site_url(),
			'add_on_slug' => $this->get( 'slug' ),
			'channel'     => $this->get_channel_subscription(),
		);

		if ( $key ) {
			$args['license_key'] = $key['license_key'];
			$args['update_key']  = $key['update_key'];
		}

		$req = new LLMS_Dot_Com_API(
			'/license/download',
			$args
		);

		$data = $req->get_result();

		if ( $req->is_error() ) {
			return $data;
		}

		return $data;
	}

	/**
	 * Translate strings
	 *
	 * @since 3.0.0
	 * @since 3.4.0 Use core textdomain.
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
	 * @since 3.0.0
	 * @since 3.2.1 Use `requires_license()` instead of checking `has_license` prop directly.
	 *
	 * @param bool $translate If true, returns the translated string for on-screen display.
	 * @return string
	 */
	public function get_license_status( $translate = false ) {

		if ( ! $this->requires_license() ) {
			$ret = 'none';
		} else {
			$ret = $this->is_licensed() ? 'license_active' : 'license_inactive';
		}

		return $translate ? $this->get_l10n( $ret ) : $ret;
	}

	/**
	 * Install the add-on via LifterLMS.com
	 *
	 * @since 3.0.0
	 * @since 3.4.0 Use core textdomain.
	 *
	 * @return string|WP_Error
	 */
	public function install() {

		$ret = LLMS_Helper()->upgrader()->install_addon( $this );

		if ( true === $ret ) {

			/* Translators: %s = Add-on name */
			return sprintf( __( '%s was successfully installed.', 'lifterlms' ), $this->get( 'title' ) );

		} elseif ( is_wp_error( $ret ) ) {

			return $ret;

		}

		/* Translators: %s = Add-on name */
		return new WP_Error( 'activation', sprintf( __( 'Could not install %s.', 'lifterlms' ), $this->get( 'title' ) ) );
	}

	/**
	 * Determines if the add-on is licensed
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public function is_licensed() {
		return ( false !== $this->find_license() );
	}

	/**
	 * Determines if the add-on requires a license
	 *
	 * @since 3.2.1
	 *
	 * @return bool
	 */
	public function requires_license() {
		return llms_parse_bool( $this->get( 'has_license' ) );
	}

	/**
	 * Update the addons update channel subscription
	 *
	 * @since 3.0.0
	 *
	 * @param string $channel Channel name [stable|beta].
	 * @return boolean
	 */
	public function subscribe_to_channel( $channel = 'stable' ) {

		$channels                       = llms_helper_options()->get_channels();
		$channels[ $this->get( 'id' ) ] = $channel;
		return llms_helper_options()->set_channels( $channels );
	}

	/**
	 * Install the add-on via LifterLMS.com
	 *
	 * @since 3.0.0
	 * @since 3.4.0 Use core textdomain.
	 *
	 * @return string|WP_Error
	 */
	public function update() {

		$ret = LLMS_Helper()->upgrader()->install_addon( $this, 'update' );

		if ( true === $ret ) {

			/* Translators: %s = Add-on name */
			return sprintf( __( '%s was successfully updated.', 'lifterlms' ), $this->get( 'title' ) );

		} elseif ( is_wp_error( $ret ) ) {

			return $ret;

		}

		/* Translators: %s = Add-on name */
		return new WP_Error( 'activation', sprintf( __( 'Could not update %s.', 'lifterlms' ), $this->get( 'title' ) ) );
	}
}
