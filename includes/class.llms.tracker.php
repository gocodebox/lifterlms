<?php
/**
 * Handle sending data to LifterLMS when tracking is enabled
 *
 * @package LifterLMS/Classes
 *
 * @since 3.0.0
 * @version 7.4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS telemetry tracking data.
 *
 * @since 3.0.0
 */
class LLMS_Tracker {

	/**
	 * URL endpoint where we'll receive the data
	 */
	const API_URL = 'https://lifterlms.com/llms-api/tracking';

	/**
	 * Initialize.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'llms_send_tracking_data', array( __CLASS__, 'send_data' ) );
	}

	/**
	 * Retrieve a timestamp of the last time the tracks sent data home
	 *
	 * @since    3.0.0
	 *
	 * @return   int        a timestamp
	 */
	private static function get_last_send_time() {
		return apply_filters( 'llms_tracker_get_last_send_time', get_option( 'llms_tracker_last_send_time', 0 ) );
	}

	/**
	 * Send data home
	 *
	 * @since 3.0.0
	 * @since 7.4.0 Fix return type.
	 *
	 * @param bool $force Force send regardless or the last send time.
	 * @return array|WP_Error|void
	 */
	public static function send_data( $force = false ) {

		// Don't trigger during AJAX Requests.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		// Allow forcing of the send despite the interval.
		if ( ! $force && ! apply_filters( 'llms_tracker_force_send', false ) ) {

			// Only send data once a week.
			$last_send = self::get_last_send_time();
			if ( $last_send && $last_send > apply_filters( 'llms_tracker_send_interval', strtotime( '-1 week' ) ) ) {
				return;
			}
		}

		// Record a last send time.
		update_option( 'llms_tracker_last_send_time', time() );

		$r = wp_remote_post(
			self::API_URL,
			array(
				'body'        => array(
					'data' => json_encode( LLMS_Data::get_data( 'tracker' ) ),
				),
				'cookies'     => array(),
				'headers'     => array(
					'user-agent' => 'LifterLMS_Tracker/' . md5( esc_url( home_url( '/' ) ) ) . ';',
				),
				'method'      => 'POST',
				'redirection' => 5,
				'timeout'     => 60,
			)
		);

		if ( ! is_wp_error( $r ) ) {

			return json_decode( $r['body'], true );

		} else {

			return $r;

		}

	}

}
