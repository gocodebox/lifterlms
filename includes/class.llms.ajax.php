<?php
/**
 * AJAX Event Handler
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 4.4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_AJAX
 *
 * @since 1.0.0
 * @since 3.35.0 Unknown.
 * @since 4.0.0 Removed previously deprecated ajax actions and related methods.
 */
class LLMS_AJAX {

	/**
	 * Nonce validation argument
	 *
	 * @var string
	 */
	const NONCE = 'llms-ajax';

	/**
	 * Hook into ajax events
	 *
	 * @since 1.0.0
	 * @since 3.16.0 Unknown.
	 * @since 4.0.0 Stop registering previously deprecated actions.
	 *
	 * @return void
	 */
	public function __construct() {

		$ajax_events = array(
			'check_voucher_duplicate' => false,
			'query_quiz_questions'    => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_' . $ajax_event, array( $this, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_' . $ajax_event, array( $this, $ajax_event ) );
			}
		}

		self::register();

		require_once 'admin/class.llms.admin.builder.php';
		add_filter( 'heartbeat_received', array( 'LLMS_Admin_Builder', 'heartbeat_received' ), 10, 2 );

	}

	/**
	 * Register the AJAX handler class with all the appropriate WordPress hooks.
	 *
	 * @since Unknown
	 * @since 4.4.0 Move `register_script()` to script enqueue hook in favor of `wp_loaded`.
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

		$action = is_admin() ? 'admin_enqueue_scripts' : 'wp_enqueue_scripts';
		add_action( $action, array( $this, 'register_script' ), 20 );

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
	 * Register our AJAX JavaScript.
	 *
	 * @since 1.0.0
	 * @since 3.35.0 Sanitize data & declare script versions.
	 * @since 4.4.0 Don't register the `llms` script.
	 * @deprecated 4.4.0 Retrieve ajax nonce via `window.llms.ajax-nonce` in favor of `wp_ajax_data.nonce`.
	 *
	 * @return void
	 */
	public function register_script() {

		wp_localize_script( 'llms', 'wp_ajax_data', $this->get_ajax_data() );

	}

	/**
	 * Get the AJAX data
	 *
	 * @since Unknown
	 * @deprecated 4.4.0 Retrieve ajax nonce via `window.llms.ajax-nonce` in favor of `wp_ajax_data.nonce`.
	 *
	 * @return array
	 */
	public function get_ajax_data() {
		return array(
			'nonce' => wp_create_nonce( self::NONCE ),
		);
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
	 * Check if a voucher is a duplicate.
	 *
	 * @return void
	 */
	public function check_voucher_duplicate() {

		global $wpdb;
		$table = $wpdb->prefix . 'lifterlms_vouchers_codes';

		$codes   = ! empty( $_REQUEST['codes'] ) ? llms_filter_input( INPUT_POST, 'codes', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY ) : array();
		$post_id = ! empty( $_REQUEST['postId'] ) ? llms_filter_input( INPUT_POST, 'postId', FILTER_SANITIZE_NUMBER_INT ) : 0;

		$codes_as_string = join( '","', $codes );

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		$query        = 'SELECT code
                  FROM ' . $table . '
                  WHERE code IN ("' . $codes_as_string . '")
                  AND voucher_id != ' . $post_id;
		$codes_result = $wpdb->get_results( $query, ARRAY_A );
		// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared

		echo json_encode(
			array(
				'success'    => true,
				'duplicates' => $codes_result,
			)
		);

		wp_die();
	}

	/**
	 * Retrieve Quiz Questions
	 *
	 * Used by Select2 AJAX functions to load paginated quiz questions
	 * Also allows querying by question title
	 *
	 * @return void
	 */
	public function query_quiz_questions() {

		// Grab the search term if it exists.
		$term = array_key_exists( 'term', $_REQUEST ) ? llms_filter_input( INPUT_POST, 'term', FILTER_SANITIZE_STRING ) : '';

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

}

new LLMS_AJAX();
