<?php
/**
 * License Key functions
 *
 * @package LifterLMS_Helper/Classes
 *
 * @since 3.0.0
 * @version 3.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Helper_Keys
 *
 * @since 3.0.0
 */
class LLMS_Helper_Keys {

	/**
	 * Activate LifterLMS License Keys with the remote server
	 *
	 * @since 3.0.0
	 * @since 3.0.1 Unknown.
	 *
	 * @param string|array $keys Array or a white-space separated list of API keys.
	 * @return array
	 */
	public static function activate_keys( $keys ) {

		// Sanitize before sending.
		if ( ! is_array( $keys ) ) {
			$keys = explode( PHP_EOL, $keys );
		}

		$keys = array_map( 'sanitize_text_field', $keys );
		$keys = array_map( 'trim', $keys );
		$keys = array_unique( $keys );

		$data = array(
			'keys' => $keys,
			'url'  => get_site_url(),
		);

		$req = new LLMS_Dot_Com_API( '/license/activate', $data );
		return $req->get_result();

	}

	/**
	 * Add a single license key
	 *
	 * @since 3.0.0
	 *
	 * @param string $activation_data  Array of activation details from api call.
	 * @return boolean True if option value has changed, false if not or if update failed.
	 */
	public static function add_license_key( $activation_data ) {

		$keys                                    = llms_helper_options()->get_license_keys();
		$keys[ $activation_data['license_key'] ] = array(
			'product_id'  => $activation_data['id'],
			'status'      => 1,
			'license_key' => $activation_data['license_key'],
			'update_key'  => $activation_data['update_key'],
			'addons'      => $activation_data['addons'],
		);

		return llms_helper_options()->set_license_keys( $keys );

	}

	/**
	 * Check all saved keys to ensure they're still active
	 *
	 * Outputs warnings if the key has expired or the status has changed remotely.
	 *
	 * Runs on daily cron (`llms_check_license_keys`).
	 *
	 * Only make api calls to check once / week.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $force Ignore the once/week setting and force a check.
	 * @return void
	 */
	public static function check_keys( $force = false ) {

		// Don't trigger during AJAX Requests.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		// Don't proceed if we don't have any keys to check.
		$keys = llms_helper_options()->get_license_keys();
		if ( ! $keys ) {
			return;
		}

		if ( ! $force ) {
			// Only check keys once a week.
			$last_send = llms_helper_options()->get_last_keys_cron_check();
			if ( $last_send > apply_filters( 'llms_check_license_keys_interval', strtotime( '-1 week' ) ) ) {
				return;
			}
		}

		// Record check time.
		llms_helper_options()->set_last_keys_cron_check( time() );

		$data = array(
			'keys' => array(),
			'url'  => get_site_url(),
		);

		foreach ( $keys as $key ) {
			$data['keys'][ $key['license_key'] ] = $key['update_key'];
		}

		$req = new LLMS_Dot_Com_API( '/license/status', $data );
		if ( ! $req->is_error() ) {

			$res = $req->get_result();
			include_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';

			/* Translators: %s = License Key */
			$msg = __( 'The license "%s" is no longer valid and was deactivated. Please visit your account dashboard at https://lifterlms.com/my-account for more information.', 'lifterlms-helper' );

			// Output error responses.
			if ( isset( $res['data']['errors'] ) ) {
				foreach ( array_keys( $res['data']['errors'] ) as $key ) {
					self::remove_license_key( $key );
					LLMS_Admin_Notices::add_notice(
						'key_check_' . sanitize_text_field( $key ),
						make_clickable( sprintf( $msg, $key ) ),
						array(
							'type'             => 'error',
							'dismiss_for_days' => 0,
						)
					);
				}
			}

			// Check status of keys, if the status has changed remove it locally.
			if ( isset( $res['data']['keys'] ) ) {
				foreach ( $res['data']['keys'] as $key => $data ) {

					if ( $data['status'] ) {
						continue;
					}

					self::remove_license_key( $key );
					LLMS_Admin_Notices::add_notice(
						'key_check_' . sanitize_text_field( $key ),
						make_clickable( sprintf( $msg, $key ) ),
						array(
							'type'             => 'error',
							'dismiss_for_days' => 0,
						)
					);

				}
			}
		}

	}

	/**
	 * Deactivate LifterLMS API keys with remote server
	 *
	 * @since 3.0.0
	 *
	 * @param array $keys Array of keys.
	 * @return array
	 */
	public static function deactivate_keys( $keys ) {

		$keys = array_map( 'sanitize_text_field', $keys );
		$keys = array_map( 'trim', $keys );

		$data = array(
			'keys' => array(),
			'url'  => get_site_url(),
		);

		$saved = llms_helper_options()->get_license_keys();
		foreach ( $keys as $key ) {
			if ( $saved[ $key ] && $saved[ $key ]['update_key'] ) {
				$data['keys'][ $key ] = $saved[ $key ]['update_key'];
			}
		}

		$req = new LLMS_Dot_Com_API( '/license/deactivate', $data );
		return $req->get_result();

	}

	/**
	 * Remove a single license key
	 *
	 * @since 3.0.0
	 *
	 * @param string $key License key.
	 * @return boolean True if option value has changed, false if not or if update failed.
	 */
	public static function remove_license_key( $key ) {
		$keys = llms_helper_options()->get_license_keys();
		if ( isset( $keys[ $key ] ) ) {
			unset( $keys[ $key ] );
		}
		return llms_helper_options()->set_license_keys( $keys );
	}

}
