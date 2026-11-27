<?php
/**
 * License Key functions
 *
 * @package LifterLMS_Helper/Classes
 *
 * @since 3.0.0
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Helper_Keys
 *
 * @since 3.0.0
 */
class LLMS_Helper_Keys {

	/**
	 * Activate LifterLMS License Keys with the remote server.
	 *
	 * @since 3.0.0
	 * @since 3.0.1 Unknown.
	 * @since 3.4.2 Removed empty key lines.
	 * @since 3.5.0 Caching results. Added `$force` parameter.
	 *
	 * @param string|array $keys  Array or a white-space separated list of API keys.
	 * @param bool         $force Optional. Whether to force a remote check. Default `false`.
	 * @return array
	 */
	public static function activate_keys( $keys, $force = false ) {

		// Sanitize before sending.
		if ( ! is_array( $keys ) ) {
			$keys = explode( PHP_EOL, $keys );
		}

		$keys = array_map( 'sanitize_text_field', $keys );
		$keys = array_map( 'trim', $keys );
		$keys = array_unique( $keys );
		$keys = array_filter( $keys ); // Remove empty keys.

		$data = array(
			'keys' => $keys,
			'url'  => get_site_url(),
		);

		// Check for a cached result based on the keys and url input.
		$cache_hash = md5( wp_json_encode( $data ) );
		if ( $force ) {
			// Delete cache if forcing a remote check.
			delete_site_transient( 'llms_helper_keys_activation_response_' . $cache_hash );
		} else {
			// Use the cached result if present.
			$cached_req_result = get_site_transient( 'llms_helper_keys_activation_response_' . $cache_hash );
			if ( ! empty( $cached_req_result ) ) {
				return $cached_req_result;
			}
		}

		$req = new LLMS_Dot_Com_API( '/license/activate', $data );
		set_site_transient( 'llms_helper_keys_activation_response_' . $cache_hash, $req->get_result(), HOUR_IN_SECONDS );

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

		// The site is licensed again, any earlier lapse no longer describes it.
		if ( llms_helper_options()->get_lapsed_license_keys() ) {
			llms_helper_options()->set_lapsed_license_keys( array() );
		}

		// Nor does the notice telling that this key was deactivated.
		self::delete_lapse_notice( $activation_data['license_key'] );

		return self::save_license_key( $activation_data );
	}

	/**
	 * Check all saved keys to ensure they're still active
	 *
	 * Outputs warnings if the key has expired or the status has changed remotely.
	 *
	 * Also checks the keys that were removed because LifterLMS.com reported them as no longer valid
	 * and restores the ones it reports as valid again, for example after a failed renewal payment
	 * was recovered.
	 *
	 * Runs on daily cron (`llms_check_license_keys`).
	 *
	 * Only make api calls to check once / week, or once / day during the week after a key lapsed,
	 * see {@see LLMS_Helper_Keys::get_check_interval()}.
	 *
	 * @since 3.0.0
	 * @since 3.4.0 Use core textdomain.
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
		$keys   = llms_helper_options()->get_license_keys();
		$lapsed = self::get_lapsed_keys_to_check();
		if ( ! $keys && ! $lapsed ) {
			return;
		}

		if ( ! $force ) {
			// Only check keys once a week, or once a day during the week after a key lapsed.
			$last_send = llms_helper_options()->get_last_keys_cron_check();
			if ( $last_send > apply_filters( 'llms_check_license_keys_interval', time() - self::get_check_interval( $lapsed ) ) ) {
				return;
			}
		}

		// Record check time.
		llms_helper_options()->set_last_keys_cron_check( time() );

		$data = array(
			'keys' => array(),
			'url'  => get_site_url(),
		);

		foreach ( array_merge( array_values( $keys ), array_values( $lapsed ) ) as $key ) {
			$data['keys'][ $key['license_key'] ] = $key['update_key'];
		}

		$req = new LLMS_Dot_Com_API( '/license/status', $data );
		if ( ! $req->is_error() ) {

			$res = $req->get_result();
			include_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';

			// LifterLMS.com has no activation of these keys for this site, they cannot become valid again.
			if ( isset( $res['data']['errors'] ) ) {
				foreach ( array_keys( $res['data']['errors'] ) as $key ) {

					if ( isset( $lapsed[ $key ] ) ) {
						self::stop_checking_lapsed_key( $key );
						// The notice no longer tells that the key will be reactivated.
						if ( LLMS_Admin_Notices::has_notice( 'key_check_' . llms_obfuscate_license_key( $key ) ) ) {
							self::add_lapse_notice( $key, false );
						}
						continue;
					}

					self::lapse_license_key( $key );
					self::add_lapse_notice( $key, false );
				}
			}

			// Check status of keys, if the status has changed remove or restore it locally.
			if ( isset( $res['data']['keys'] ) ) {
				foreach ( $res['data']['keys'] as $key => $key_data ) {

					// A lapsed key stays lapsed until LifterLMS.com reports it as valid again.
					if ( isset( $lapsed[ $key ] ) ) {
						if ( ! empty( $key_data['status'] ) ) {
							self::restore_license_key( $key, $key_data );
						}
						continue;
					}

					if ( $key_data['status'] ) {
						continue;
					}

					// The key is inactive, for example while a failed renewal payment is retried, and can become valid again.
					self::lapse_license_key( $key, true );
					self::add_lapse_notice( $key, true );
				}
			}
		}
	}

	/**
	 * Deactivate LifterLMS API keys with remote server
	 *
	 * @since 3.0.0
	 * @since 3.4.1 Ensure key exists before attempting to deactivate it.
	 * @since 3.5.0 Deleting any cached activation result.
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

		// Delete any cached activation result.
		$cache_hash = md5( wp_json_encode( $data ) );
		delete_site_transient( 'llms_helper_keys_activation_response_' . $cache_hash );

		$saved = llms_helper_options()->get_license_keys();
		foreach ( $keys as $key ) {
			if ( isset( $saved[ $key ] ) && $saved[ $key ]['update_key'] ) {
				$data['keys'][ $key ] = $saved[ $key ]['update_key'];
			}
		}

		$req = new LLMS_Dot_Com_API( '/license/deactivate', $data );
		return $req->get_result();
	}

	/**
	 * Retrieve stored information about a key by the license key
	 *
	 * @since 3.3.1
	 *
	 * @param string $key License key.
	 * @return array|false Associative array of license key information. Returns `false` if the provided license key was not found.
	 */
	public static function get( $key ) {

		$saved = llms_helper_options()->get_license_keys();
		return isset( $saved[ $key ] ) ? $saved[ $key ] : false;
	}

	/**
	 * Remove a license key that LifterLMS.com reports as no longer valid and remember that it lapsed.
	 *
	 * Removing the key leaves no trace of the site ever having been licensed. The lapse record
	 * lets the site tell "the license expired" apart from "never licensed".
	 *
	 * The record holds the obfuscated key. It also holds the key itself and its update key when the
	 * key is to be checked again, so that it can be restored once LifterLMS.com reports it as valid.
	 *
	 * @since [version]
	 *
	 * @param string $key     License key.
	 * @param bool   $recheck Whether {@see LLMS_Helper_Keys::check_keys()} keeps checking the key and restores it when it is valid again.
	 * @return boolean True if the key was saved on this site and has been removed, false otherwise.
	 */
	public static function lapse_license_key( $key, $recheck = false ) {

		$saved = self::get( $key );
		if ( ! $saved ) {
			return false;
		}

		$record = array(
			'product_id' => $saved['product_id'],
			'time'       => time(),
		);
		if ( $recheck ) {
			$record['license_key'] = $saved['license_key'];
			$record['update_key']  = $saved['update_key'];
		}

		$lapsed                                       = llms_helper_options()->get_lapsed_license_keys();
		$lapsed[ llms_obfuscate_license_key( $key ) ] = $record;
		llms_helper_options()->set_lapsed_license_keys( $lapsed );

		return self::remove_license_key( $key );
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

	/**
	 * Add the admin notice telling that a license key is no longer valid and was deactivated.
	 *
	 * @since [version]
	 *
	 * @param string $key     License key.
	 * @param bool   $recheck Whether the key is checked again and restored when it is valid again.
	 * @return void
	 */
	private static function add_lapse_notice( $key, $recheck ) {

		if ( $recheck ) {
			/* Translators: %s = License Key */
			$msg = __( 'The license "%s" is no longer valid and was deactivated. It will be reactivated on this site automatically if it becomes valid again. Please visit your account dashboard at https://lifterlms.com/my-account for more information.', 'lifterlms' );
		} else {
			/* Translators: %s = License Key */
			$msg = __( 'The license "%s" is no longer valid and was deactivated. Please visit your account dashboard at https://lifterlms.com/my-account for more information.', 'lifterlms' );
		}

		LLMS_Admin_Notices::add_notice(
			'key_check_' . llms_obfuscate_license_key( sanitize_text_field( $key ) ),
			make_clickable( sprintf( $msg, llms_obfuscate_license_key( $key ) ) ),
			array(
				'type'             => 'error',
				'dismiss_for_days' => 0,
			)
		);
	}

	/**
	 * Delete the admin notice telling that a license key is no longer valid and was deactivated.
	 *
	 * @since [version]
	 *
	 * @param string $key License key.
	 * @return void
	 */
	private static function delete_lapse_notice( $key ) {

		include_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';

		$notice_id = 'key_check_' . llms_obfuscate_license_key( sanitize_text_field( $key ) );
		if ( LLMS_Admin_Notices::has_notice( $notice_id ) ) {
			LLMS_Admin_Notices::delete_notice( $notice_id );
		}
	}

	/**
	 * Retrieve how long to wait between two checks of the license keys.
	 *
	 * A key that lapsed in the last week is likely to become valid again, for example when a failed
	 * renewal payment is retried or the customer updates their payment method, so it is checked every
	 * day. After that it is checked every week, so that sites whose license is not renewed do not
	 * keep sending a request a day.
	 *
	 * @since [version]
	 *
	 * @param array[] $lapsed Lapsed license keys to check, see {@see LLMS_Helper_Keys::get_lapsed_keys_to_check()}.
	 * @return int Interval in seconds.
	 */
	private static function get_check_interval( $lapsed ) {

		/**
		 * Filters how long after a license key lapsed it is checked every day.
		 *
		 * After that, the key is checked every week, like the saved license keys.
		 *
		 * @since [version]
		 *
		 * @param int $period Period in seconds.
		 */
		$period = apply_filters( 'llms_helper_lapsed_license_daily_check_period', WEEK_IN_SECONDS );

		/*
		 * The cron event runs once a day, never exactly 24 hours apart. Half a day lets every run check,
		 * and keeps the run on the last day of the period from being skipped when it is late.
		 */
		$tolerance = DAY_IN_SECONDS / 2;

		foreach ( $lapsed as $record ) {
			if ( time() < $record['time'] + $period + $tolerance ) {
				return $tolerance;
			}
		}

		return WEEK_IN_SECONDS;
	}

	/**
	 * Retrieve the lapsed license keys that can become valid again.
	 *
	 * @since [version]
	 *
	 * @return array[] Lapsed license records, see {@see LLMS_Helper_Options::get_lapsed_license_keys()}, keyed by the license key.
	 */
	private static function get_lapsed_keys_to_check() {

		$keys = array();
		foreach ( llms_helper_options()->get_lapsed_license_keys() as $record ) {
			if ( ! empty( $record['license_key'] ) && ! empty( $record['update_key'] ) ) {
				$keys[ $record['license_key'] ] = $record;
			}
		}

		return $keys;
	}

	/**
	 * Save a lapsed license key that LifterLMS.com reports as valid again and forget that it lapsed.
	 *
	 * @since [version]
	 *
	 * @param string $key      License key.
	 * @param array  $key_data Details of the key's activation from the api call.
	 * @return boolean True if the key was restored, false if the api call lacks the details needed to save it.
	 */
	private static function restore_license_key( $key, $key_data ) {

		if ( ! isset( $key_data['id'], $key_data['update_key'], $key_data['addons'] ) ) {
			return false;
		}

		$obfuscated = llms_obfuscate_license_key( $key );

		$lapsed = llms_helper_options()->get_lapsed_license_keys();
		unset( $lapsed[ $obfuscated ] );
		llms_helper_options()->set_lapsed_license_keys( $lapsed );

		self::delete_lapse_notice( $key );

		$key_data['license_key'] = $key;
		return self::save_license_key( $key_data );
	}

	/**
	 * Save a single license key.
	 *
	 * @since [version]
	 *
	 * @param array $activation_data Array of activation details from an api call.
	 * @return boolean True if option value has changed, false if not or if update failed.
	 */
	private static function save_license_key( $activation_data ) {

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
	 * Stop checking a lapsed license key that cannot become valid again.
	 *
	 * The record that the key lapsed is kept, without the key.
	 *
	 * @since [version]
	 *
	 * @param string $key License key.
	 * @return void
	 */
	private static function stop_checking_lapsed_key( $key ) {

		$obfuscated = llms_obfuscate_license_key( $key );

		$lapsed = llms_helper_options()->get_lapsed_license_keys();
		if ( isset( $lapsed[ $obfuscated ] ) ) {
			unset( $lapsed[ $obfuscated ]['license_key'], $lapsed[ $obfuscated ]['update_key'] );
			llms_helper_options()->set_lapsed_license_keys( $lapsed );
		}
	}
}
