<?php
/**
 * Course enrollments command file.
 *
 * @package LifterLMS/CLI
 *
 * @since 0.0.6
 * @version 0.0.6
 */

namespace LifterLMS\CLI\Commands\Course;

/**
 * Course enrollments command trait.
 *
 * @since 0.0.6
 */
trait Enrollments {

	/**
	 * Lists students enrolled in a course.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The course ID.
	 *
	 * [--page=<page>]
	 * : Page number for paginated results.
	 * ---
	 * default: 1
	 * ---
	 *
	 * [--per_page=<per_page>]
	 * : Number of results per page.
	 * ---
	 * default: 10
	 * ---
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 *   - csv
	 *   - yaml
	 *   - count
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific fields.
	 *
	 * ## EXAMPLES
	 *
	 *     # List enrollments for course 123.
	 *     $ wp llms course enrollments 123
	 *
	 *     # Get enrollment count.
	 *     $ wp llms course enrollments 123 --format=count
	 *
	 *     # Get enrollments as JSON (recommended for AI agents).
	 *     $ wp llms course enrollments 123 --format=json
	 *
	 * @since 0.0.6
	 *
	 * @param array $args       Indexed array of positional arguments.
	 * @param array $assoc_args Associative array of command options.
	 * @return void
	 */
	public function enrollments( $args, $assoc_args ) {

		list( $course_id ) = $args;
		$course_id = absint( $course_id );

		if ( ! $course_id || ! get_post( $course_id ) || 'course' !== get_post_type( $course_id ) ) {
			\WP_CLI::error( sprintf( 'Course %d not found.', $course_id ) );
		}

		if ( ! defined( 'REST_REQUEST' ) ) {
			define( 'REST_REQUEST', true );
		}

		$request = new \WP_REST_Request( 'GET', "/llms/v1/courses/{$course_id}/enrollments" );
		$request->set_param( 'page', $assoc_args['page'] ?? 1 );
		$request->set_param( 'per_page', $assoc_args['per_page'] ?? 10 );

		$response = rest_do_request( $request );

		if ( $error = $response->as_error() ) {
			\WP_CLI::error( $error );
		}

		$data    = $response->get_data();
		$headers = $response->get_headers();
		$format  = \WP_CLI\Utils\get_flag_value( $assoc_args, 'format', 'table' );

		if ( 'count' === $format ) {
			echo (int) ( $headers['X-WP-Total'] ?? count( $data ) );
			return;
		}

		if ( 'json' === $format ) {
			echo wp_json_encode( $data, JSON_PRETTY_PRINT );
			return;
		}

		if ( 'yaml' === $format ) {
			echo \Spyc::YAMLDump( $data, false, false, true );
			return;
		}

		if ( empty( $data ) ) {
			\WP_CLI::log( 'No enrollments found.' );
			return;
		}

		$fields = \WP_CLI\Utils\get_flag_value( $assoc_args, 'fields', null );
		if ( $fields ) {
			$fields = explode( ',', $fields );
		} else {
			$fields = array_keys( $data[0] );
		}

		$formatter = new \WP_CLI\Formatter( $assoc_args, $fields );
		$formatter->display_items( $data );
	}

}
