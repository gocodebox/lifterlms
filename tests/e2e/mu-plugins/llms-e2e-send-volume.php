<?php
/**
 * Plugin Name: LifterLMS E2E Send Volume
 * Description: Lowers the scan send-volume threshold and exposes fixture routes for the engagement send-volume end-to-end spec.
 *
 * @package LifterLMS/Tests/E2E
 *
 * @since [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lower the send-volume threshold so a handful of students trips the pause gate.
 *
 * @since [version]
 *
 * @return int
 */
add_filter(
	'llms_engagement_send_warning_threshold',
	static function () {
		return 2;
	}
);

/**
 * Register fixture routes used by the send-volume end-to-end spec.
 *
 * @since [version]
 *
 * @return void
 */
function llms_e2e_register_send_volume_routes() {

	$permission = static function () {
		return current_user_can( 'manage_options' );
	};

	register_rest_route(
		'llms-e2e/v1',
		'/email-template',
		array(
			'methods'             => 'POST',
			'permission_callback' => $permission,
			'callback'            => 'llms_e2e_create_email_template',
		)
	);

	register_rest_route(
		'llms-e2e/v1',
		'/backdate-enrollment',
		array(
			'methods'             => 'POST',
			'permission_callback' => $permission,
			'callback'            => 'llms_e2e_backdate_enrollment',
		)
	);

	register_rest_route(
		'llms-e2e/v1',
		'/engagement-author',
		array(
			'methods'             => 'POST',
			'permission_callback' => $permission,
			'callback'            => 'llms_e2e_set_engagement_author',
		)
	);

	register_rest_route(
		'llms-e2e/v1',
		'/run-engagement-scan',
		array(
			'methods'             => 'POST',
			'permission_callback' => $permission,
			'callback'            => 'llms_e2e_run_engagement_scan',
		)
	);
}
add_action( 'rest_api_init', 'llms_e2e_register_send_volume_routes' );

/**
 * Create a published email template the engagement editor can select.
 *
 * @since [version]
 *
 * @param WP_REST_Request $request Request object.
 * @return array|WP_Error
 */
function llms_e2e_create_email_template( $request ) {

	$title = sanitize_text_field( $request->get_param( 'title' ) );
	$id    = wp_insert_post(
		array(
			'post_type'    => 'llms_email',
			'post_status'  => 'publish',
			'post_title'   => $title ? $title : 'E2E email',
			'post_content' => 'Hello',
		),
		true
	);

	if ( is_wp_error( $id ) ) {
		return $id;
	}

	return array(
		'id'    => $id,
		'title' => get_the_title( $id ),
	);
}

/**
 * Backdate every user-postmeta row for an enrollment.
 *
 * A REST enrollment is stamped "now", so a 14-day inactivity trigger would not match it.
 *
 * @since [version]
 *
 * @param WP_REST_Request $request Request object.
 * @return array
 */
function llms_e2e_backdate_enrollment( $request ) {

	global $wpdb;

	$days = absint( $request->get_param( 'days' ) );
	$date = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( $days * DAY_IN_SECONDS ) );

	$wpdb->query(
		$wpdb->prepare(
			"UPDATE {$wpdb->prefix}lifterlms_user_postmeta SET updated_date = %s WHERE user_id = %d AND post_id = %d",
			$date,
			absint( $request->get_param( 'user_id' ) ),
			absint( $request->get_param( 'post_id' ) )
		)
	); // db call ok; no-cache ok.

	return array(
		'updated_date' => $date,
	);
}

/**
 * Point an engagement at a new administrator whose email is not the site admin address.
 *
 * The daily-scan pause email copies the author only when that address differs from
 * `admin_email`. The wp-env admin user is usually both, so the spec needs a distinct author.
 * The author is written directly so this does not run the save-time send-volume gate.
 *
 * @since [version]
 *
 * @param WP_REST_Request $request Request object.
 * @return array|WP_Error
 */
function llms_e2e_set_engagement_author( $request ) {

	$email   = 'volume-author-' . wp_rand( 10000, 99999 ) . '@example.com';
	$user_id = wp_insert_user(
		array(
			'user_login' => 'volume_author_' . wp_rand( 10000, 99999 ),
			'user_email' => $email,
			'user_pass'  => wp_generate_password(),
			'role'       => 'administrator',
		)
	);

	if ( is_wp_error( $user_id ) ) {
		return $user_id;
	}

	global $wpdb;
	$wpdb->update(
		$wpdb->posts,
		array( 'post_author' => $user_id ),
		array( 'ID' => absint( $request->get_param( 'engagement_id' ) ) )
	); // db call ok; no-cache ok.
	clean_post_cache( absint( $request->get_param( 'engagement_id' ) ) );

	return array(
		'user_id' => $user_id,
		'email'   => $email,
	);
}

/**
 * Run the daily engagement scan and return the emails it tried to send.
 *
 * @since [version]
 *
 * @return array
 */
function llms_e2e_run_engagement_scan() {

	$captured = array();

	$capture = static function ( $args ) use ( &$captured ) {
		$headers = $args['headers'] ?? array();
		if ( is_string( $headers ) ) {
			$headers = array_filter( array_map( 'trim', preg_split( "/\r\n|\n|\r/", $headers ) ) );
		}

		$captured[] = array(
			'to'      => $args['to'],
			'subject' => $args['subject'],
			'message' => $args['message'],
			'headers' => array_values( (array) $headers ),
		);

		return $args;
	};

	add_filter( 'wp_mail', $capture );
	llms()->engagements()->scanner->do_scan();
	remove_filter( 'wp_mail', $capture );

	return array(
		'admin_email' => get_option( 'admin_email' ),
		'mails'       => $captured,
	);
}
