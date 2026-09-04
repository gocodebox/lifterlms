<?php
/**
 * REST and Abilities integration for course streams.
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Streams class.
 *
 * @since [version]
 */
class LLMS_REST_Streams {

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'llms_rest_allow_filtering_course_item_schema_to_add_fields', '__return_true' );
		add_filter( 'llms_rest_allow_filtering_lesson_item_schema_to_add_fields', '__return_true' );
		add_filter( 'llms_rest_course_item_schema', array( $this, 'add_course_schema_properties' ) );
		add_filter( 'llms_rest_lesson_item_schema', array( $this, 'add_lesson_schema_properties' ) );
		add_filter( 'llms_rest_prepare_course_object_response', array( $this, 'prepare_course_response' ), 10, 2 );
		add_filter( 'llms_rest_prepare_lesson_object_response', array( $this, 'prepare_lesson_response' ), 10, 2 );
		add_filter( 'llms_rest_pre_insert_course', array( $this, 'pre_insert_course' ), 10, 2 );
		add_filter( 'llms_rest_pre_insert_lesson', array( $this, 'pre_insert_lesson' ), 10, 2 );
		add_filter( 'llms_rest_ability_configs', array( $this, 'add_ability_configs' ) );

		add_action( 'rest_api_init', array( $this, 'register_enrollment_field' ) );
	}

	/**
	 * Add stream properties to the course REST schema.
	 *
	 * @since [version]
	 *
	 * @param array $schema Item schema.
	 * @return array
	 */
	public function add_course_schema_properties( $schema ) {

		$schema['properties']['streams_enabled'] = array(
			'description' => __( 'Whether course streams are enabled.', 'lifterlms' ),
			'type'        => 'boolean',
			'default'     => false,
			'context'     => array( 'view', 'edit' ),
		);
		$schema['properties']['streams']         = array(
			'description' => __( 'Course streams. Each stream has a stable id and a display name.', 'lifterlms' ),
			'type'        => 'array',
			'items'       => array(
				'type'       => 'object',
				'properties' => array(
					'id'   => array(
						'description' => __( 'Stable stream identifier.', 'lifterlms' ),
						'type'        => 'string',
					),
					'name' => array(
						'description' => __( 'Stream display name.', 'lifterlms' ),
						'type'        => 'string',
					),
				),
				'required'   => array( 'name' ),
			),
			'context'     => array( 'view', 'edit' ),
		);
		$schema['properties']['streams_default'] = array(
			'description' => __( 'Default stream id assigned to students who have not selected a stream.', 'lifterlms' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit' ),
		);

		return $schema;
	}

	/**
	 * Add stream properties to the lesson REST schema.
	 *
	 * @since [version]
	 *
	 * @param array $schema Item schema.
	 * @return array
	 */
	public function add_lesson_schema_properties( $schema ) {

		$schema['properties']['streams'] = array(
			'description' => __( 'Stream ids this lesson belongs to. An empty list means the lesson belongs to every stream.', 'lifterlms' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'string',
			),
			'context'     => array( 'view', 'edit' ),
		);

		return $schema;
	}

	/**
	 * Add stream fields to a course REST response.
	 *
	 * @since [version]
	 *
	 * @param array       $data   Response data.
	 * @param LLMS_Course $course Course object.
	 * @return array
	 */
	public function prepare_course_response( $data, $course ) {

		$data['streams_enabled'] = llms_parse_bool( $course->get( 'streams_enabled' ) );
		$data['streams']         = llms_get_course_streams( $course );
		$data['streams_default'] = llms_get_course_default_stream( $course );

		return $data;
	}

	/**
	 * Add stream fields to a lesson REST response.
	 *
	 * @since [version]
	 *
	 * @param array       $data   Response data.
	 * @param LLMS_Lesson $lesson Lesson object.
	 * @return array
	 */
	public function prepare_lesson_response( $data, $lesson ) {

		$data['streams'] = llms_sanitize_lesson_streams( $lesson->get( 'streams' ) );

		return $data;
	}

	/**
	 * Map course stream fields from a REST request into model properties.
	 *
	 * @since [version]
	 *
	 * @param array           $prepared_item Prepared item.
	 * @param WP_REST_Request $request         Request object.
	 * @return array
	 */
	public function pre_insert_course( $prepared_item, $request ) {

		if ( isset( $request['streams_enabled'] ) ) {
			$prepared_item['streams_enabled'] = llms_parse_bool( $request['streams_enabled'] ) ? 'yes' : 'no';
		}

		if ( isset( $request['streams'] ) ) {
			$prepared_item['streams'] = llms_sanitize_course_streams( $request['streams'] );
		}

		if ( isset( $request['streams_default'] ) ) {
			$prepared_item['streams_default'] = sanitize_title( $request['streams_default'] );
		}

		return $prepared_item;
	}

	/**
	 * Map lesson stream fields from a REST request into model properties.
	 *
	 * @since [version]
	 *
	 * @param array           $prepared_item Prepared item.
	 * @param WP_REST_Request $request         Request object.
	 * @return array
	 */
	public function pre_insert_lesson( $prepared_item, $request ) {

		if ( ! isset( $request['streams'] ) ) {
			return $prepared_item;
		}

		$course = null;
		if ( ! empty( $prepared_item['parent_course'] ) ) {
			$course = llms_get_post( $prepared_item['parent_course'] );
		} elseif ( ! empty( $request['id'] ) ) {
			$lesson = llms_get_post( $request['id'] );
			$course = $lesson instanceof LLMS_Lesson ? $lesson->get_course() : null;
		}

		$prepared_item['streams'] = llms_sanitize_lesson_streams( $request['streams'], $course );

		return $prepared_item;
	}

	/**
	 * Register the student stream field on enrollments.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function register_enrollment_field() {

		register_rest_field(
			'students-enrollments',
			'stream',
			array(
				'schema'          => array(
					'description' => __( 'Selected course stream id. Empty when the enrollment is not for a course with streams enabled.', 'lifterlms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'get_callback'    => array( $this, 'get_enrollment_stream' ),
				'update_callback' => array( $this, 'update_enrollment_stream' ),
			)
		);
	}

	/**
	 * Get the stream for an enrollment REST response.
	 *
	 * @since [version]
	 *
	 * @param array $enrollment Enrollment data.
	 * @return string
	 */
	public function get_enrollment_stream( $enrollment ) {

		if ( empty( $enrollment['student_id'] ) || empty( $enrollment['post_id'] ) ) {
			return '';
		}

		if ( 'course' !== get_post_type( $enrollment['post_id'] ) ) {
			return '';
		}

		return llms_get_student_stream( $enrollment['student_id'], $enrollment['post_id'] );
	}

	/**
	 * Update the stream for an enrollment.
	 *
	 * @since [version]
	 *
	 * @param string   $value      Stream id.
	 * @param stdClass $enrollment Enrollment object.
	 * @return bool|WP_Error
	 */
	public function update_enrollment_stream( $value, $enrollment ) {

		if ( empty( $enrollment->student_id ) || empty( $enrollment->post_id ) ) {
			return false;
		}

		if ( 'course' !== get_post_type( $enrollment->post_id ) ) {
			return new WP_Error(
				'llms_rest_stream_invalid_post',
				__( 'Streams can only be set on course enrollments.', 'lifterlms' ),
				array( 'status' => 400 )
			);
		}

		$updated = llms_set_student_stream( $enrollment->student_id, $enrollment->post_id, $value );
		if ( ! $updated ) {
			return new WP_Error(
				'llms_rest_stream_invalid',
				__( 'The requested stream is not valid for this course.', 'lifterlms' ),
				array( 'status' => 400 )
			);
		}

		return true;
	}

	/**
	 * Add student stream abilities.
	 *
	 * @since [version]
	 *
	 * @param array[] $configs Ability configurations.
	 * @return array[]
	 */
	public function add_ability_configs( $configs ) {

		$student_id_desc = __( 'Unique student identifier. The WordPress user ID.', 'lifterlms' );
		$post_id_desc    = __( 'Unique course identifier. The WordPress post ID.', 'lifterlms' );

		$configs[] = array(
			'name'        => 'get-student-stream',
			'label'       => __( 'Get Student Stream', 'lifterlms' ),
			'description' => __( 'Retrieves the selected course stream for a student enrollment. The stream is returned as the stream property on the enrollment.', 'lifterlms' ),
			'controller'  => 'LLMS_REST_Enrollments_Controller',
			'operation'   => 'get',
			'method'      => 'GET',
			'route'       => '/llms/v1/students/{id}/enrollments/{post_id}',
			'path_params' => array(
				'id'      => $student_id_desc,
				'post_id' => $post_id_desc,
			),
		);

		$configs[] = array(
			'name'        => 'set-student-stream',
			'label'       => __( 'Set Student Stream', 'lifterlms' ),
			'description' => __( 'Sets the selected course stream for a student enrollment. Pass the stream id in the stream property.', 'lifterlms' ),
			'controller'  => 'LLMS_REST_Enrollments_Controller',
			'operation'   => 'update',
			'method'      => 'PATCH',
			'route'       => '/llms/v1/students/{id}/enrollments/{post_id}',
			// Enrollments PATCH args are hardcoded to trigger/status and never pick up
			// register_rest_field( 'stream' ). Explicit args replace those so the ability
			// input schema includes stream (required) and trigger (default any, used by
			// permission checks that run before REST route matching applies defaults).
			'args'        => array(
				'stream'  => array(
					'description' => __( 'Selected course stream id.', 'lifterlms' ),
					'type'        => 'string',
					'required'    => true,
				),
				'trigger' => array(
					'description' => __( 'The trigger of the enrollment to act on.', 'lifterlms' ),
					'type'        => 'string',
					'default'     => 'any',
				),
			),
			'path_params' => array(
				'id'      => $student_id_desc,
				'post_id' => $post_id_desc,
			),
		);

		return $configs;
	}
}

return new LLMS_REST_Streams();
