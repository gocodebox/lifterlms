<?php
/**
 * REST Controller for Student Grades.
 *
 * @package LifterLMS_REST/Classes
 *
 * @since 10.2.0
 * @version 10.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Students_Grades_Controller class.
 *
 * Read-only collection of a student's grades: one item per course including the
 * course grade and a per-lesson breakdown with lesson and quiz grades. Add-ons
 * (e.g. LifterLMS Assignments) can append additional gradable elements to each
 * lesson via the `llms_rest_student_grades_lesson_data` filter.
 *
 * @since 10.2.0
 */
class LLMS_REST_Students_Grades_Controller extends LLMS_REST_Controller {

	/**
	 * Base Resource
	 *
	 * @var string
	 */
	protected $rest_base = 'students/(?P<id>[\d]+)/grades';

	/**
	 * Schema properties available for ordering the collection.
	 *
	 * @var string[]
	 */
	protected $orderby_properties = array(
		'date',
		'title',
	);

	/**
	 * Register routes.
	 *
	 * @since 10.2.0
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique student identifier. The WordPress User ID.', 'lifterlms' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Determine if the current user can view the requested student's grades.
	 *
	 * Students can read their own grades; otherwise reporting and student
	 * visibility capabilities are required.
	 *
	 * @since 10.2.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return true|WP_Error
	 */
	public function get_items_permissions_check( $request ) {

		$student_id = absint( $request['id'] );

		if ( get_current_user_id() !== $student_id && ! ( current_user_can( 'view_lifterlms_reports' ) && current_user_can( 'view_students', $student_id ) ) ) {
			return llms_rest_authorization_required_error();
		}

		if ( ! llms_get_student( $student_id, false ) ) {
			return llms_rest_not_found_error();
		}

		return true;
	}

	/**
	 * Retrieve the query collection params.
	 *
	 * @since 10.2.0
	 *
	 * @return array
	 */
	public function get_collection_params() {

		$params = parent::get_collection_params();

		unset( $params['include'], $params['exclude'], $params['search'] );

		$params['course'] = array(
			'description' => __( 'Limit results to the specified course or courses. Accepts a single WP Post ID or a comma separated list of IDs.', 'lifterlms' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
		);

		return $params;
	}

	/**
	 * Retrieve the query for the collection of objects.
	 *
	 * @since 10.2.0
	 *
	 * @param array           $prepared Array of collection args.
	 * @param WP_REST_Request $request  Request object.
	 * @return object
	 */
	protected function get_objects_query( $prepared, $request ) {

		$student_id = absint( $request['id'] );
		$student    = llms_get_student( $student_id );
		$page       = isset( $prepared['page'] ) ? max( 1, absint( $prepared['page'] ) ) : 1;
		$per_page   = isset( $prepared['per_page'] ) ? absint( $prepared['per_page'] ) : 10;

		if ( ! empty( $prepared['course'] ) ) {

			$course_ids = array_values(
				array_filter(
					array_map( 'absint', $prepared['course'] ),
					function ( $course_id ) {
						return 'course' === get_post_type( $course_id );
					}
				)
			);

			$found      = count( $course_ids );
			$course_ids = array_slice( $course_ids, ( $page - 1 ) * $per_page, $per_page );

		} else {

			$enrollments = $student->get_courses(
				array(
					'limit'   => $per_page,
					'skip'    => ( $page - 1 ) * $per_page,
					'orderby' => isset( $prepared['orderby'] ) ? $prepared['orderby'] : 'date',
					'order'   => isset( $prepared['order'] ) ? strtoupper( $prepared['order'] ) : 'DESC',
				)
			);

			$found      = absint( $enrollments['found'] );
			$course_ids = array_map( 'absint', $enrollments['results'] );
		}

		return (object) array(
			'student_id' => $student_id,
			'course_ids' => $course_ids,
			'found'      => $found,
			'page'       => $page,
			'per_page'   => $per_page,
		);
	}

	/**
	 * Retrieve an array of objects from the result of `$this->get_objects_query()`.
	 *
	 * @since 10.2.0
	 *
	 * @param object $query Objects query result.
	 * @return object[]
	 */
	protected function get_objects_from_query( $query ) {

		return array_map(
			function ( $course_id ) use ( $query ) {
				return (object) array(
					'id'         => $course_id,
					'student_id' => $query->student_id,
				);
			},
			$query->course_ids
		);
	}

	/**
	 * Retrieve pagination information from an objects query.
	 *
	 * @since 10.2.0
	 *
	 * @param object          $query    Objects query result.
	 * @param array           $prepared Array of collection args.
	 * @param WP_REST_Request $request  Request object.
	 * @return array
	 */
	protected function get_pagination_data_from_query( $query, $prepared, $request ) {

		return array(
			'current_page'  => $query->page,
			'total_results' => $query->found,
			'total_pages'   => (int) ceil( $query->found / $query->per_page ),
		);
	}

	/**
	 * Retrieve an object by "id".
	 *
	 * Collection objects are built by `$this->get_objects_from_query()` and passed
	 * through unchanged; there is no single-item route for this resource.
	 *
	 * @since 10.2.0
	 *
	 * @param object $object Object from the collection query.
	 * @return object
	 */
	protected function get_object( $object ) {

		return $object;
	}

	/**
	 * Determine if the current user can view the object.
	 *
	 * Permissions are validated for the whole collection in
	 * `$this->get_items_permissions_check()`.
	 *
	 * @since 10.2.0
	 *
	 * @param object $object Object.
	 * @return bool
	 */
	protected function check_read_object_permissions( $object ) {

		return true;
	}

	/**
	 * Prepare an object for response.
	 *
	 * @since 10.2.0
	 *
	 * @param object          $object  Object with `id` (course ID) and `student_id` properties.
	 * @param WP_REST_Request $request Request object.
	 * @return array
	 */
	protected function prepare_object_for_response( $object, $request ) {

		$student = llms_get_student( $object->student_id );
		$course  = llms_get_post( $object->id );

		$data = array(
			'student_id' => $object->student_id,
			'post_id'    => $object->id,
			'grade'      => $this->get_grade( $object->id, $student ),
			'lessons'    => array(),
		);

		if ( is_a( $course, 'LLMS_Course' ) ) {
			foreach ( $course->get_lessons( 'lessons' ) as $lesson ) {
				$data['lessons'][] = $this->prepare_lesson_data( $lesson, $student, $request );
			}
		}

		/**
		 * Filters the student grades data prepared for the REST response.
		 *
		 * @since 10.2.0
		 *
		 * @param array           $data    Array of course grade data for the student.
		 * @param LLMS_Student    $student Student object.
		 * @param WP_REST_Request $request Request object.
		 */
		return apply_filters( 'llms_rest_prepare_student_grades_object_response', $data, $student, $request );
	}

	/**
	 * Prepare the grades data for a single lesson.
	 *
	 * @since 10.2.0
	 *
	 * @param LLMS_Lesson     $lesson  Lesson object.
	 * @param LLMS_Student    $student Student object.
	 * @param WP_REST_Request $request Request object.
	 * @return array
	 */
	protected function prepare_lesson_data( $lesson, $student, $request ) {

		$lesson_data = array(
			'id'    => $lesson->get( 'id' ),
			'title' => $lesson->get( 'title' ),
			'grade' => $this->get_grade( $lesson->get( 'id' ), $student ),
			'quiz'  => null,
		);

		if ( $lesson->is_quiz_enabled() ) {

			$quiz_id   = absint( $lesson->get( 'quiz' ) );
			$quiz_data = array(
				'id'         => $quiz_id,
				'grade'      => null,
				'attempt_id' => null,
				'status'     => null,
			);

			$attempt = $student->quizzes()->get_best_attempt( $quiz_id );
			if ( $attempt ) {
				$grade                   = $attempt->get( 'grade' );
				$quiz_data['grade']      = is_numeric( $grade ) ? (float) $grade : null;
				$quiz_data['attempt_id'] = absint( $attempt->get( 'id' ) );
				$quiz_data['status']     = $attempt->get( 'status' );
			}

			$lesson_data['quiz'] = $quiz_data;
		}

		/**
		 * Filters the per-lesson grades data prepared for the REST response.
		 *
		 * Allows add-ons providing additional gradable elements (e.g. assignments)
		 * to append their grade data to each lesson.
		 *
		 * @since 10.2.0
		 *
		 * @param array           $lesson_data Array of lesson grade data.
		 * @param LLMS_Lesson     $lesson      Lesson object.
		 * @param LLMS_Student    $student     Student object.
		 * @param WP_REST_Request $request     Request object.
		 */
		return apply_filters( 'llms_rest_student_grades_lesson_data', $lesson_data, $lesson, $student, $request );
	}

	/**
	 * Retrieve a grade for a course or lesson as a float, or null when ungraded.
	 *
	 * @since 10.2.0
	 *
	 * @param int          $post_id Course or lesson ID.
	 * @param LLMS_Student $student Student object.
	 * @return float|null
	 */
	protected function get_grade( $post_id, $student ) {

		// Skip the in-memory cache so grades updated earlier in the same process (e.g. by a grading ability) are reflected.
		$grade = llms()->grades()->get_grade( $post_id, $student, false );

		return is_numeric( $grade ) ? (float) $grade : null;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 10.2.0
	 *
	 * @param object          $object  Object with `id` (course ID) and `student_id` properties.
	 * @param WP_REST_Request $request Request object.
	 * @return array
	 */
	protected function prepare_links( $object, $request ) {

		return array(
			'student'       => array(
				'href' => rest_url( sprintf( '/%1$s/students/%2$d', $this->namespace, $object->student_id ) ),
			),
			'post'          => array(
				'type' => 'course',
				'href' => rest_url( sprintf( '/%1$s/courses/%2$d', $this->namespace, $object->id ) ),
			),
			'quiz-attempts' => array(
				'href' => add_query_arg(
					array(
						'student' => $object->student_id,
					),
					rest_url( sprintf( '/%1$s/quiz-attempts', $this->namespace ) )
				),
			),
		);
	}

	/**
	 * Get the item schema.
	 *
	 * @since 10.2.0
	 *
	 * @return array
	 */
	protected function get_item_schema_base() {

		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'student-grades',
			'type'       => 'object',
			'properties' => array(
				'student_id' => array(
					'description' => __( 'Unique student identifier. The WordPress User ID.', 'lifterlms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'post_id'    => array(
					'description' => __( 'Unique course identifier. The WordPress Post ID.', 'lifterlms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'grade'      => array(
					'description' => __( 'Student\'s overall course grade as a percentage. `null` when no gradable elements have been graded yet.', 'lifterlms' ),
					'type'        => array( 'number', 'null' ),
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'lessons'    => array(
					'description' => __( 'Per-lesson grade breakdown for the course.', 'lifterlms' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'    => array(
								'description' => __( 'Unique lesson identifier. The WordPress Post ID.', 'lifterlms' ),
								'type'        => 'integer',
							),
							'title' => array(
								'description' => __( 'Lesson title.', 'lifterlms' ),
								'type'        => 'string',
							),
							'grade' => array(
								'description' => __( 'Student\'s lesson grade as a percentage. `null` when the lesson has no graded elements.', 'lifterlms' ),
								'type'        => array( 'number', 'null' ),
							),
							'quiz'  => array(
								'description' => __( 'Grade information for the lesson\'s quiz. `null` when the lesson has no quiz enabled.', 'lifterlms' ),
								'type'        => array( 'object', 'null' ),
								'properties'  => array(
									'id'         => array(
										'description' => __( 'Unique quiz identifier. The WordPress Post ID.', 'lifterlms' ),
										'type'        => 'integer',
									),
									'grade'      => array(
										'description' => __( 'Grade of the student\'s best quiz attempt as a percentage. `null` when the quiz has not been attempted or graded.', 'lifterlms' ),
										'type'        => array( 'number', 'null' ),
									),
									'attempt_id' => array(
										'description' => __( 'Unique identifier of the student\'s best quiz attempt.', 'lifterlms' ),
										'type'        => array( 'integer', 'null' ),
									),
									'status'     => array(
										'description' => __( 'Status of the student\'s best quiz attempt.', 'lifterlms' ),
										'type'        => array( 'string', 'null' ),
									),
								),
							),
						),
					),
				),
			),
		);
	}
}
