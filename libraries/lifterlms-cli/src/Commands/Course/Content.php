<?php
/**
 * Course content (structure) command file.
 *
 * @package LifterLMS/CLI
 *
 * @since 0.0.6
 * @version 0.0.6
 */

namespace LifterLMS\CLI\Commands\Course;

/**
 * Course content command trait.
 *
 * @since 0.0.6
 */
trait Content {

	/**
	 * Gets the outline of a course (sections and lessons).
	 *
	 * Returns the full course structure including all sections and
	 * their nested lessons, ordered by position.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The course ID.
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
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific fields.
	 *
	 * ## EXAMPLES
	 *
	 *     # Get course outline as a table.
	 *     $ wp llms course content 123
	 *
	 *     # Get course outline as JSON (recommended for AI agents).
	 *     $ wp llms course content 123 --format=json
	 *
	 * @since 0.0.6
	 *
	 * @param array $args       Indexed array of positional arguments.
	 * @param array $assoc_args Associative array of command options.
	 * @return void
	 */
	public function content( $args, $assoc_args ) {

		list( $course_id ) = $args;
		$course_id = absint( $course_id );

		if ( ! $course_id || ! get_post( $course_id ) || 'course' !== get_post_type( $course_id ) ) {
			\WP_CLI::error( sprintf( 'Course %d not found.', $course_id ) );
		}

		if ( ! defined( 'REST_REQUEST' ) ) {
			define( 'REST_REQUEST', true );
		}

		$request  = new \WP_REST_Request( 'GET', "/llms/v1/courses/{$course_id}/content" );
		$response = rest_do_request( $request );

		if ( $error = $response->as_error() ) {
			\WP_CLI::error( $error );
		}

		$data   = $response->get_data();
		$format = \WP_CLI\Utils\get_flag_value( $assoc_args, 'format', 'table' );

		if ( 'json' === $format ) {
			echo wp_json_encode( $data, JSON_PRETTY_PRINT );
			return;
		}

		if ( 'yaml' === $format ) {
			echo \Spyc::YAMLDump( $data, false, false, true );
			return;
		}

		// Flatten for table/csv display.
		$items = array();
		foreach ( $data as $section ) {
			$items[] = array(
				'type'    => 'section',
				'id'      => $section['id'] ?? '',
				'title'   => $section['title']['rendered'] ?? $section['title'] ?? '',
				'order'   => $section['order'] ?? '',
				'parent'  => $course_id,
			);

			$lessons = $section['lessons'] ?? $section['content'] ?? array();
			foreach ( $lessons as $lesson ) {
				$items[] = array(
					'type'    => 'lesson',
					'id'      => $lesson['id'] ?? '',
					'title'   => $lesson['title']['rendered'] ?? $lesson['title'] ?? '',
					'order'   => $lesson['order'] ?? '',
					'parent'  => $section['id'] ?? '',
				);
			}
		}

		$fields = \WP_CLI\Utils\get_flag_value( $assoc_args, 'fields', 'type,id,title,order,parent' );
		$formatter = new \WP_CLI\Formatter( $assoc_args, explode( ',', $fields ) );
		$formatter->display_items( $items );
	}

}
