<?php
/**
 * AJAX Event Handler
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_AJAX
 *
 * @since 1.0.0
 * @since 3.35.0 Unknown.
 * @since 4.0.0 Removed previously deprecated ajax actions and related methods.
 * @since 6.0.0 Removed deprecated items.
 *              - `LLMS_AJAX::check_voucher_duplicate()` method.
 *              - `LLMS_AJAX::get_ajax_data()` method.
 *              - `LLMS_AJAX::register_script()` method.
 */
class LLMS_AJAX {

	/**
	 * Nonce validation argument
	 *
	 * @var string
	 */
	const NONCE = 'llms-ajax';

	/**
	 * Hook into ajax events.
	 *
	 * @since 1.0.0
	 * @since 3.16.0 Unknown.
	 * @since 4.0.0 Stop registering previously deprecated actions.
	 * @since 5.9.0 Move `check_voucher_duplicate()` to `LLMS_AJAX_Handler`.
	 * @since 6.0.0 Removed loading of class files that don't instantiate their class in favor of autoloading.
	 * @since 7.5.0 Added `favorite_object` ajax event.
	 *
	 * @return void
	 */
	public function __construct() {

		$ajax_events = array(
			'query_quiz_questions' => false,
			'favorite_object'      => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_' . $ajax_event, array( $this, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_' . $ajax_event, array( $this, $ajax_event ) );
			}
		}

		self::register();

		add_filter( 'heartbeat_received', array( 'LLMS_Admin_Builder', 'heartbeat_received' ), 10, 2 );
	}

	/**
	 * Register the AJAX handler class with all the appropriate WordPress hooks.
	 *
	 * @since Unknown
	 * @since 4.4.0 Move `register_script()` to script enqueue hook in favor of `wp_loaded`.
	 * @since 6.0.0 Removed the `wp_enqueue_scripts` action callback to the deprecated `LLMS_AJAX::register_script()` method.
	 *
	 * @return void
	 */
	public function register() {

		$handler = 'LLMS_AJAX';
		$methods = get_class_methods( 'LLMS_AJAX_Handler' );

		foreach ( $methods as $method ) {
			add_action( 'wp_ajax_' . $method, array( $handler, 'handle' ) );
			add_action( 'wp_ajax_nopriv_' . $method, array( $handler, 'handle' ) );
		}

	}

	/**
	 * Handles the AJAX request for my plugin.
	 *
	 * @since Unknown
	 *
	 * @return void
	 */
	public static function handle() {

		// Make sure we are getting a valid AJAX request.
		check_ajax_referer( self::NONCE );

		$request = $_REQUEST;

		$response = call_user_func( 'LLMS_AJAX_Handler::' . $request['action'], $request );

		if ( $response instanceof WP_Error ) {
			self::send_error( $response );
		}

		wp_send_json_success( $response );

		die();

	}

	public static function scrub_request( $request ) {

		foreach ( $request as $key => $value ) {

			if ( is_array( $value ) ) {
				$request[ $key ] = self::scrub_request( $value );
			} else {
				$request[ $key ] = llms_clean( $value );
			}
		}

		return $request;

	}

	/**
	 * Sends a JSON response with the details of the given error.
	 *
	 * @param WP_Error $error
	 */
	private static function send_error( $error ) {
		wp_send_json(
			array(
				'code'    => $error->get_error_code(),
				'message' => $error->get_error_message(),
			)
		);
	}

	/**
	 * Retrieve Quiz Questions
	 *
	 * Used by Select2 AJAX functions to load paginated quiz questions
	 * Also allows querying by question title
	 *
	 * @since Unknown
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return void
	 */
	public function query_quiz_questions() {

		// Grab the search term if it exists.
		$term = array_key_exists( 'term', $_REQUEST ) ? llms_filter_input_sanitize_string( INPUT_POST, 'term' ) : '';
		$page = array_key_exists( 'page', $_REQUEST ) ? llms_filter_input( INPUT_POST, 'page', FILTER_SANITIZE_NUMBER_INT ) : 0;

		global $wpdb;

		$limit = 30;
		$start = $limit * $page;

		if ( $term ) {
			$like = " AND post_title LIKE '%s'";
			$vars = array( '%' . $term . '%', $start, $limit );
		} else {
			$like = '';
			$vars = array( $start, $limit );
		}

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$questions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title
			 FROM $wpdb->posts
			 WHERE
			 	    post_type = 'llms_question'
			 	AND post_status = 'publish'
			 	$like
			 ORDER BY post_title
			 LIMIT %d, %d
			",
				$vars
			)
		);

		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$r = array();
		foreach ( $questions as $q ) {

			$r[] = array(
				'id'   => $q->ID,
				'name' => $q->post_title . ' (' . $q->ID . ')',
			);

		}

		echo json_encode(
			array(
				'items'   => $r,
				'more'    => count( $r ) === $limit,
				'success' => true,
			)
		);

		wp_die();

	}

	/**
	 * Add Favorite / Unfavorite postmeta for an object.
	 *
	 * @since 7.5.0
	 *
	 * @return void
	 */
	public function favorite_object() {

		// Grab the data if it exists.
		$user_action = llms_filter_input_sanitize_string( INPUT_POST, 'user_action' );
		$object_id   = llms_filter_input( INPUT_POST, 'object_id', FILTER_SANITIZE_NUMBER_INT );
		$object_type = llms_filter_input_sanitize_string( INPUT_POST, 'object_type' );
		$user_id     = get_current_user_id();
		$student     = llms_get_student( $user_id );
		if ( is_null( $object_id ) || ! $student ) {
			return;
		}

		if ( 'favorite' === $user_action ) {
			// You can never mark favorite a non-free lesson of a course you're not enrolled into.
			if ( 'lesson' === $object_type ) {
				$lesson            = llms_get_post( $object_id );
				$can_mark_favorite = $lesson && ( $student->is_enrolled( $object_id ) || $lesson->is_free() );
				if ( ! $can_mark_favorite ) {
					return;
				}
			}

			llms_mark_favorite( $user_id, $object_id, $object_type );
		} elseif ( 'unfavorite' === $user_action ) {
			llms_mark_unfavorite( $user_id, $object_id, $object_type );
		}

		echo wp_json_encode(
			array(
				'total_favorites' => llms_get_object_total_favorites( $object_id ),
				'success'         => true,
			)
		);

		wp_die();

	}

}

new LLMS_AJAX();
