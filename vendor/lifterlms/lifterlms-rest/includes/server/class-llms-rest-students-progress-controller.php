<?php
/**
 * REST Controller for Student Progress
 *
 * @package  LifterLMS_REST/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Student_Progress_Controller class.
 *
 * @since 1.0.0-beta.1
 */
class LLMS_REST_Students_Progress_Controller extends LLMS_REST_Controller {

	/**
	 * Base Resource
	 *
	 * @var string
	 */
	protected $rest_base = 'students/(?P<id>[\d]+)/progress/(?P<post_id>[\d]+)';

	/**
	 * Schema properties available for ordering the collection.
	 *
	 * @var string[]
	 */
	protected $orderby_properties = array(
		'date_created',
		'date_updated',
		'progress',
	);

	/**
	 * Determine if the current user can view the requested item.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool
	 */
	protected function check_read_item_permissions( $request ) {

		// Can read your own progress.
		if ( get_current_user_id() === $request['id'] ) {
			return true;
		}

		// Must be able to edit post and student to view other's progress.
		if ( current_user_can( 'edit_post', $request['post_id'] ) && current_user_can( 'edit_students', $request['id'] ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Determine if current user has permission to delete a user.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return true|WP_Error
	 */
	public function delete_item_permissions_check( $request ) {

		if ( ! current_user_can( 'edit_post', $request['post_id'] ) || ! current_user_can( 'delete_students', $request['id'] ) ) {
			return llms_rest_authorization_required_error();
		}

		return true;

	}

	/**
	 * Delete the object.
	 *
	 * Note: we do not return 404s when the resource to delete cannot be found. We assume it's already been deleted and respond with 204.
	 * Errors returned by this method should be any error other than a 404!
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param obj             $object Instance of the object from $this->get_object().
	 * @param WP_REST_Request $request Request object.
	 * @return true|WP_Error true when the object is removed, WP_Error on failure.
	 */
	protected function delete_object( $object, $request ) {

		$post = llms_get_post( $request['post_id'] );

		$ids = 'lesson' === $post->get( 'type' ) ? array( $post->get( 'id' ) ) : $post->get_lessons( 'ids' );

		if ( $ids ) {
			foreach ( $ids as $id ) {
				llms_bulk_delete_user_postmeta(
					$request['id'],
					$id,
					array(
						'_status'             => null,
						'_completion_trigger' => null,
					)
				);
			}
		}

		if ( 'lesson' !== $post->get( 'type' ) ) {
			llms_mark_incomplete( $request['id'], $post->get( 'id' ), $post->get( 'type' ) );
		}

		return true;

	}

	/**
	 * Retrieve a updated/created dates for a given post.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param LLMS_Student                         $student Student Object.
	 * @param LLMS_Course|LLMS_Section|LLMS_Lesson $post Course, Section, or Lesson post object.
	 * @param string                               $order Sort order, ASC or DESC.
	 * @return string|null
	 */
	protected function get_date( $student, $post, $order ) {

		$lessons = 'lesson' === $post->get( 'type' ) ? array( $post->get( 'id' ) ) : $post->get_lessons( 'ids' );

		if ( $lessons ) {

			$lessons = implode( ', ', $lessons );

			global $wpdb;
			// @todo: rewrite query so we don't have to ignore CS rules.
			//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$date = $wpdb->get_var(
				$wpdb->prepare(
					"
				SELECT updated_date
				  FROM {$wpdb->prefix}lifterlms_user_postmeta
				 WHERE user_id = %d
				   AND post_id IN ( {$lessons} )
				   AND meta_key = '_is_complete'
		         ORDER BY updated_date {$order}
				 LIMIT 1;
				",
					$student->get( 'id' )
				)
			);
			//phpcs:enable

			if ( $date ) {
				return mysql_to_rfc3339( $date );
			}
		}

		return null;

	}

	/**
	 * Get a single item.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {

		$object   = $this->get_object( array( $request['id'], $request['post_id'] ) );
		$response = $this->prepare_item_for_response( $object, $request );

		return rest_ensure_response( $response );

	}

	/**
	 * Check if a given request has access to read an item.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {

		if ( ! $this->check_read_item_permissions( $request ) ) {
			return llms_rest_authorization_required_error();
		}

		return true;
	}

	/**
	 * Get the API Key's schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function get_item_schema() {

		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'students-progress',
			'type'       => 'object',
			'properties' => array(
				'student_id'   => array(
					'description' => __( 'The ID of the student.', 'lifterlms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'post_id'      => array(
					'description' => __( 'The ID of the course/membership.', 'lifterlms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'date_created' => array(
					'description' => __( 'Creation date. Format: Y-m-d H:i:s', 'lifterlms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'validate_callback' => array( $this, 'validate_date_created' ),
					),
				),
				'date_updated' => array(
					'description' => __( 'Date last modified. Format: Y-m-d H:i:s', 'lifterlms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'status'       => array(
					'description' => __( 'The status of the enrollment.', 'lifterlms' ),
					'enum'        => array( 'complete', 'incomplete' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'string',
					'required'    => true,
				),
				'progress'     => array(
					'description' => __( 'Student\'s progress as a percentage.', 'lifterlms' ),
					'enum'        => array( 'complete', 'incomplete' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'number',
					'readonly'    => true,
				),
			),
		);

	}

	/**
	 * Get object.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int[] $ids {
	 *     Numeric array of ids.
	 *
	 *     @type int $ids[0] Student id.
	 *     @type int $ids[1] Post id.
	 * }
	 * @return object|WP_Error
	 */
	protected function get_object( $ids ) {

		$obj = new stdClass();
		if ( ! is_array( $ids ) ) {
			return $obj;
		}

		$student_id = $ids[0];
		$post_id    = $ids[1];

		$post = llms_get_post( $post_id );

		$student = llms_get_student( $student_id );

		$obj->student_id = $student_id;
		$obj->post_id    = $post_id;

		if ( 'lesson' === $post->get( 'type' ) ) {
			$obj->progress = $student->is_complete( $post_id, 'lesson' ) ? (float) 100 : (float) 0;
		} else {
			$obj->progress = (float) $student->get_progress( $post_id, $post->get( 'type' ) );
		}

		$obj->status = $obj->progress < 100 ? 'incomplete' : 'complete';

		$obj->date_updated = $this->get_date( $student, $post, 'DESC' );
		$obj->date_created = $this->get_date( $student, $post, 'ASC' );

		return $obj;

	}

	/**
	 * Retrieve an ID from the object
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param obj $object Item object.
	 * @return int
	 */
	protected function get_object_id( $object ) {

		return array( $object->student_id, $object->post_id );

	}


	/**
	 * Prepare request arguments for a database insert/update.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_Rest_Request $request Request object.
	 * @return array
	 */
	protected function prepare_item_for_database( $request ) {

		$prepared       = parent::prepare_item_for_database( $request );
		$prepared['id'] = $request['id'];

		return $prepared;

	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param obj $object Item object.
	 * @return array
	 */
	protected function prepare_links( $object ) {

		$base = rest_url(
			sprintf(
				'/%1$s/%2$s',
				$this->namespace,
				str_replace(
					array( '(?P<id>[\d]+)', '(?P<post_id>[\d]+)' ),
					array( $object->student_id, $object->post_id ),
					$this->rest_base
				)
			)
		);

		$post_type = get_post_type( $object->post_id );

		$links = array(
			'self'    => array(
				'href' => $base,
			),
			'post'    => array(
				'type' => $post_type,
				'href' => rest_url( sprintf( '/%1$s/%2$ss/%3$d', $this->namespace, $post_type, $object->post_id ) ),
			),
			'student' => array(
				'href' => rest_url( sprintf( '/%1$s/students/%2$d', $this->namespace, $object->student_id ) ),
			),
		);

		return $links;

	}

	/**
	 * Prepare an object for response.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param LLMS_Abstract_User_Data $object User object.
	 * @param WP_REST_Request         $request Request object.
	 * @return array
	 */
	protected function prepare_object_for_response( $object, $request ) {

		return (array) $object;

	}

	/**
	 * Register routes.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'args'   => array(
					'id'      => array(
						'description' => __( 'Unique identifier for the student. The WP User ID.', 'lifterlms' ),
						'type'        => 'integer',
					),
					'post_id' => array(
						'description'       => __( 'Unique course, lesson, or section Identifer. The WordPress Post ID.', 'lifterlms' ),
						'type'              => 'integer',
						'validate_callback' => array( $this, 'validate_post_id' ),
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => $this->get_get_item_params(),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( 'POST' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => array(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

	}

	/**
	 * Determine if current user has permission to create/update an enrollment.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return true|WP_Error
	 */
	public function update_item_permissions_check( $request ) {

		if ( ! current_user_can( 'edit_post', $request['post_id'] ) || ! current_user_can( 'edit_students', $request['id'] ) ) {
			return llms_rest_authorization_required_error();
		}

		return true;

	}

	/**
	 * Update the object in the database with prepared data.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array           $prepared Prepared item data.
	 * @param WP_REST_Request $request Request object.
	 * @return obj Object Instance of object from $this->get_object().
	 */
	protected function update_object( $prepared, $request ) {

		if ( in_array( get_post_type( $prepared['post_id'] ), array( 'course', 'section' ), true ) ) {
			$post    = llms_get_post( $prepared['post_id'] );
			$lessons = $post->get_lessons( 'ids' );
		} else {
			$lessons = array( $prepared['post_id'] );
		}

		foreach ( $lessons as $lesson_id ) {

			if ( 'complete' === $prepared['status'] ) {
				llms_mark_complete( $prepared['id'], $lesson_id, 'lesson', 'api_' . get_current_user_id() );
			} elseif ( 'incomplete' === $prepared['status'] ) {
				llms_mark_incomplete( $prepared['id'], $lesson_id, 'lesson', 'api_' . get_current_user_id() );
			}
		}

		return $this->get_object( array( $prepared['id'], $prepared['post_id'] ) );

	}

	/**
	 * Validate the date_created
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param string          $value Date string.
	 * @param WP_REST_Request $request Request object.
	 * @param string          $param Parameter name ("post_id").
	 * @return bool
	 */
	public function validate_date_created( $value, $request, $param ) {

		$ts  = rest_parse_date( $value );
		$now = llms_current_time( 'U' );

		if ( $ts > $now ) {
			return llms_rest_bad_request_error( __( 'Created date cannot be in the future.', 'lifterlms' ) );
		}

		return true;
	}

	/**
	 * Validate the path parameter "post_id".
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int             $value Post ID.
	 * @param WP_REST_Request $request Request object.
	 * @param string          $param Parameter name ("post_id").
	 * @return bool
	 */
	public function validate_post_id( $value, $request, $param ) {
		$post = get_post( $value );
		if ( ! $post ) {
			return false;
		} elseif ( ! in_array( $post->post_type, array( 'course', 'lesson', 'section' ), true ) ) {
			return false;
		} elseif ( ! llms_is_user_enrolled( $request['id'], $value ) ) {
			return false;
		}

		return true;
	}

}
