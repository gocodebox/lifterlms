<?php
/**
 * Automatically attempt to activate already activated add-ons during clones
 *
 * @package LifterLMS_Helper/Classes
 *
 * @since 2.5.0
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Helper_Cloned
 *
 * @since 2.5.0
 */
class LLMS_Helper_Cloned {

	/**
	 * Constructor
	 *
	 * @since 2.5.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'llms_site_clone_detected', array( $this, 'handle_clone' ) );
	}

	/**
	 * Attempt to automatically activate already activated add-ons when cloning
	 *
	 * If the key cannot be activated all activation related data will be removed
	 * Called when LifterLMS core detects a cloned site.
	 *
	 * @since 2.5.0
	 * @since 3.0.0 Unknown.
	 *
	 * @return void
	 */
	public function handle_clone() {

		$keys = llms_helper_options()->get_license_keys();

		if ( ! $keys ) {
			return;
		}

		$res = LLMS_Helper_Keys::activate_keys( array_keys( $keys ) );

		if ( ! is_wp_error( $res ) ) {

			$data = $res['data'];
			if ( isset( $data['activations'] ) ) {
				foreach ( $data['activations'] as $activation ) {
					LLMS_Helper_Keys::add_license_key( $activation );
				}
			}
		}
	}
}

return new LLMS_Helper_Cloned();
