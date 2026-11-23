<?php
/**
 * Plugin installation
 *
 * @package LifterLMS_Helper/Classes
 *
 * @since 3.0.0
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Helper_Install
 *
 * @since 3.0.0
 */
class LLMS_Helper_Install {

	/**
	 * Initialize the install class
	 *
	 * @since 3.0.0
	 * @since 3.0.1 Unknown.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'check_version' ), 5 );
	}

	/**
	 * Checks the current LLMS version and runs installer if required
	 *
	 * @since 3.0.0
	 * @since 3.4.0 Use llms_helper() in favor of deprecated LLMS_Helper().
	 *
	 * @return void
	 */
	public static function check_version() {

		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'llms_helper_version' ) !== llms_helper()->version ) {

			self::install();

			/**
			 * Action run after the helper library is updated.
			 *
			 * @since 3.0.0
			 */
			do_action( 'llms_helper_updated' );

		}
	}

	/**
	 * Core install function
	 *
	 * @since 3.0.0
	 * @since 3.4.0 Skip migration when loaded as a library.
	 *
	 * @return void
	 */
	public static function install() {

		if ( ! is_blog_installed() ) {
			return;
		}

		do_action( 'llms_helper_before_install' );

		if ( ( ! defined( 'LLMS_HELPER_LIB' ) || ! LLMS_HELPER_LIB ) && ! get_option( 'llms_helper_version', '' ) ) {
			self::_migrate_300();
		}

		self::update_version();

		do_action( 'llms_helper_after_install' );
	}

	/**
	 * Update the LifterLMS version record to the latest version
	 *
	 * @since 3.0.0
	 * @since 3.4.0 Use llms_helper() in favor of deprecated LLMS_Helper().
	 *
	 * @param string $version version number.
	 * @return void
	 */
	public static function update_version( $version = null ) {
		delete_option( 'llms_helper_version' );
		add_option( 'llms_helper_version', is_null( $version ) ? llms_helper()->version : $version );
	}

	/**
	 * Migrate to version 3.0.0
	 *
	 * @since 3.0.0
	 * @since 3.0.2 Unknown.
	 * @since 3.4.0 Use core textdomain.
	 *
	 * @return void
	 */
	private static function _migrate_300() {

		$text  = '<p><strong>' . __( 'Welcome to the LifterLMS Helper', 'lifterlms' ) . '</strong></p>';
		$text .= '<p>' . __( 'This plugin allows your website to interact with your subscriptions at LifterLMS.com to ensure your add-ons stay up to date.', 'lifterlms' ) . '</p>';
		// Translators: %1$s = Opening anchor tag; %2$s = closing anchor tag.
		$text .= '<p>' . sprintf( __( 'You can activate your add-ons from the %1$sAdd-Ons & More%2$s screen.', 'lifterlms' ), '<a href="' . admin_url( 'admin.php?page=llms-add-ons' ) . '">', '</a>' ) . '</p>';

		$keys   = array();
		$addons = llms_get_add_ons();
		if ( ! is_wp_error( $addons ) && isset( $addons['items'] ) ) {
			foreach ( $addons['items'] as $addon ) {

				$addon = llms_get_add_on( $addon );

				if ( ! $addon->is_installable() ) {
					continue;
				}

				$option_name = sprintf( '%s_activation_key', $addon->get( 'slug' ) );

				$key = get_option( $option_name );
				if ( $key ) {
					$keys[] = get_option( $option_name );
				}

				delete_option( $option_name );
				delete_option( sprintf( '%s_update_key', $addon->get( 'slug' ) ) );

			}
		}

		if ( $keys ) {

			$res = LLMS_Helper_Keys::activate_keys( $keys );

			if ( ! is_wp_error( $res ) ) {

				$data = $res['data'];
				if ( isset( $data['activations'] ) ) {

					// Translators: %d = Number of keys that have been migrated.
					$text .= '<p>' . sprintf( _n( '%d license has been automatically migrated from the previous version of the LifterLMS Helper', '%d licenses have been automatically migrated from the previous version of the LifterLMS Helper.', count( $data['activations'] ), 'lifterlms' ), count( $data['activations'] ) ) . ':</p>';

					foreach ( $data['activations'] as $activation ) {
						LLMS_Helper_Keys::add_license_key( $activation );
						$text .= '<p><em>' . $activation['license_key'] . '</em></p>';
					}
				}
			}
		}

		LLMS_Admin_Notices::flash_notice( $text, 'info' );

		// Clean up legacy options.
		$remove = array(
			'lifterlms_stripe_activation_key',
			'lifterlms_paypal_activation_key',
			'lifterlms_gravityforms_activation_key',
			'lifterlms_mailchimp_activation_key',
			'llms_helper_key_migration',
		);

		foreach ( $remove as $opt ) {
			delete_option( $opt );
		}
	}
}

LLMS_Helper_Install::init();
