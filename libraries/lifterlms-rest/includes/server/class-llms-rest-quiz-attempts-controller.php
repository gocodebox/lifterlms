<?php
/**
 * REST Quiz Attempts Controller
 *
 * @package LifterLMS_REST/Classes/Controllers
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Quiz_Attempts_Controller class.
 *
 * Quiz attempts live in the `{$prefix}lifterlms_quiz_attempts` custom table and are
 * queried via LLMS_Query_Quiz_Attempt.
 *
 * @since [version]
 */
class LLMS_REST_Quiz_Attempts_Controller extends LLMS_REST_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'quiz-attempts';

	/**
	 * Schema properties available for ordering the collection.
	 *
	 * @var string[]
	 */
	protected $orderby_properties = array(
		'id',
		'start_date',
		'update_date',
		'end_date',
		'attempt',
		'grade',
	);

	/**
	 * Register routes.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the quiz attempt.', 'lifterlms' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => $this->get_get_item_params(),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => $this->get_delete_item_args(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/grade',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the quiz attempt.', 'lifterlms' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'grade_item' ),
					'permission_callback' => array( $this, 'grade_item_permissions_check' ),
					'args'                => $this->get_grade_item_args(),
				),
			)
		);
	}

	/**
	 * Retrieve arguments for the grade endpoint.
	 *
	 * Public so the grading ability can derive its input schema from the same definition.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	public function get_grade_item_args() {

		return array(
			'questions' => array(
				'description' => __( 'List of question grading data. Supply earned to award points, remarks to leave feedback, or both. Remarks-only submissions leave the attempt in pending status for later review.', 'lifterlms' ),
				'type'        => 'array',
				'required'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'      => array(
							'description' => __( 'The WP_Post ID of the question being graded.', 'lifterlms' ),
							'type'        => 'integer',
							'required'    => true,
						),
						'earned'  => array(
							'description' => __( 'The number of points earned for the answer. When omitted, points are left unchanged.', 'lifterlms' ),
							'type'        => 'integer',
							'minimum'     => 0,
						),
						'remarks' => array(
							'description' => __( 'Remarks (HTML allowed) displayed to the student for this answer.', 'lifterlms' ),
							'type'        => 'string',
						),
					),
				),
			),
		);
	}

	/**
	 * Check if a given request has access to read items.
	 *
	 * @since [version]
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {

		if ( ! current_user_can( 'view_lifterlms_reports' ) ) {
			return llms_rest_authorization_required_error();
		}

		return true;
	}

	/**
	 * Check if a given request has access to read an item.
	 *
	 * @since [version]
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {

		$attempt = $this->get_object( (int) $request['id'] );
		if ( is_wp_error( $attempt ) ) {
			return $attempt;
		}

		if ( ! $this->check_read_object_permissions( $attempt ) ) {
			return llms_rest_authorization_required_error();
		}

		return true;
	}

	/**
	 * Check if a given request has access to grade an item.
	 *
	 * Grading intentionally shares the read gate (`view_lifterlms_reports` +
	 * `edit_post` on the quiz): anyone who can view an attempt's answers and
	 * grades is a grader. This is a superset of the admin grading screen,
	 * which requires only `edit_post` on the quiz
	 * ({@see LLMS_Controller_Admin_Quiz_Attempts::maybe_run_actions()}).
	 *
	 * @since [version]
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function grade_item_permissions_check( $request ) {
		return $this->get_item_permissions_check( $request );
	}

	/**
	 * Check if a given request has access to update an item.
	 *
	 * The only update operation on quiz attempts is grading; this exists so
	 * abilities-aware clients (which delegate to the standard permission
	 * methods) can check access to the grade ability.
	 *
	 * @since [version]
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function update_item_permissions_check( $request ) {
		return $this->grade_item_permissions_check( $request );
	}

	/**
	 * Check if a given request has access to delete an item.
	 *
	 * @since [version]
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function delete_item_permissions_check( $request ) {

		$attempt = $this->get_object( (int) $request['id'] );
		if ( is_wp_error( $attempt ) ) {
			// Deleting a resource which doesn't exist returns a 204.
			if ( in_array( 'llms_rest_not_found', $attempt->get_error_codes(), true ) ) {
				return true;
			}
			return $attempt;
		}

		if ( ! $this->check_read_object_permissions( $attempt ) ) {
			return llms_rest_authorization_required_error();
		}

		return true;
	}

	/**
	 * Determine if the current user can view the attempt.
	 *
	 * Matches the admin reporting screen: reporting access plus the ability to
	 * edit the attempt's quiz.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Quiz_Attempt $attempt Quiz attempt object.
	 * @return bool
	 */
	protected function check_read_object_permissions( $attempt ) {

		return current_user_can( 'view_lifterlms_reports' ) && current_user_can( 'edit_post', $attempt->get( 'quiz_id' ) );
	}

	/**
	 * Get object.
	 *
	 * @since [version]
	 *
	 * @param int|LLMS_Quiz_Attempt $id Quiz attempt ID or object.
	 * @return LLMS_Quiz_Attempt|WP_Error
	 */
	protected function get_object( $id ) {

		if ( is_a( $id, 'LLMS_Quiz_Attempt' ) ) {
			return $id;
		}

		$attempt = new LLMS_Quiz_Attempt( (int) $id );

		return $attempt->exists() ? $attempt : llms_rest_not_found_error();
	}

	/**
	 * Delete the object.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Quiz_Attempt $object  Quiz attempt object.
	 * @param WP_REST_Request   $request Request object.
	 * @return true|WP_Error
	 */
	protected function delete_object( $object, $request ) {

		$object->delete();

		return true;
	}

	/**
	 * Retrieves the query params for the objects collection.
	 *
	 * @since [version]
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {

		$query_params = parent::get_collection_params();

		unset( $query_params['include'], $query_params['exclude'] );

		$query_params['student'] = array(
			'description' => __( 'Limit results to attempts by a specific student or a list of students. Accepts a single WP_User ID or a comma separated list of ids.', 'lifterlms' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
		);

		$query_params['quiz'] = array(
			'description' => __( 'Limit results to attempts of a specific quiz or a list of quizzes. Accepts a single WP_Post ID or a comma separated list of ids.', 'lifterlms' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
		);

		$query_params['lesson'] = array(
			'description' => __( 'Limit results to attempts of the quiz attached to a specific lesson. Accepts a lesson WP_Post ID.', 'lifterlms' ),
			'type'        => 'integer',
		);

		$query_params['status'] = array(
			'description' => __( 'Limit results to attempts matching a specific status. Use pending to retrieve the queue of attempts awaiting manual grading.', 'lifterlms' ),
			'type'        => 'string',
			'enum'        => array_keys( llms_get_quiz_attempt_statuses() ),
		);

		return $query_params;
	}

	/**
	 * Map schema to query arguments to retrieve a collection of objects.
	 *
	 * @since [version]
	 *
	 * @param array           $prepared   Array of collection arguments.
	 * @param array           $registered Registered collection params.
	 * @param WP_REST_Request $request    Full details about the request.
	 * @return array|WP_Error
	 */
	protected function map_params_to_query_args( $prepared, $registered, $request ) {

		$args = array(
			'page'     => isset( $prepared['page'] ) ? (int) $prepared['page'] : 1,
			'per_page' => isset( $prepared['per_page'] ) ? (int) $prepared['per_page'] : 10,
			'sort'     => array(
				$request['orderby'] => strtoupper( $request['order'] ),
			),
		);

		if ( ! empty( $prepared['student'] ) ) {
			$args['student_id'] = array_map( 'absint', (array) $prepared['student'] );
		}

		if ( ! empty( $prepared['quiz'] ) ) {
			$args['quiz_id'] = array_map( 'absint', (array) $prepared['quiz'] );
		}

		if ( ! empty( $prepared['lesson'] ) ) {
			// Attempts are related to a lesson through its attached quiz.
			$lesson  = llms_get_post( absint( $prepared['lesson'] ) );
			$quiz_id = is_a( $lesson, 'LLMS_Lesson' ) ? absint( $lesson->get( 'quiz' ) ) : 0;

			// No quiz on the lesson: force an empty result set.
			$args['quiz_id'] = array_merge(
				isset( $args['quiz_id'] ) ? $args['quiz_id'] : array(),
				array( $quiz_id ? $quiz_id : PHP_INT_MAX )
			);
		}

		if ( ! empty( $prepared['status'] ) ) {
			$args['status'] = $prepared['status'];
		}

		return $args;
	}

	/**
	 * Retrieve a query object based on arguments from a `get_items()` (collection) request.
	 *
	 * @since [version]
	 *
	 * @param array           $prepared Array of collection arguments.
	 * @param WP_REST_Request $request  Request object.
	 * @return LLMS_Query_Quiz_Attempt
	 */
	protected function get_objects_query( $prepared, $request ) {

		return new LLMS_Query_Quiz_Attempt( $prepared );
	}

	/**
	 * Retrieve an array of objects from the result of `$this->get_objects_query()`.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Query_Quiz_Attempt $query Query result.
	 * @return LLMS_Quiz_Attempt[]
	 */
	protected function get_objects_from_query( $query ) {

		return $query->get_attempts();
	}

	/**
	 * Retrieve pagination information from an objects query.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Query_Quiz_Attempt $query    Objects query result.
	 * @param array                   $prepared Array of collection arguments.
	 * @param WP_REST_Request         $request  Request object.
	 * @return array
	 */
	protected function get_pagination_data_from_query( $query, $prepared, $request ) {

		return array(
			'current_page'  => isset( $prepared['page'] ) ? (int) $prepared['page'] : 1,
			'total_results' => (int) $query->found_results,
			'total_pages'   => (int) $query->max_pages,
		);
	}

	/**
	 * Grade a quiz attempt.
	 *
	 * Mirrors the admin reporting screen grading flow
	 * ({@see LLMS_Controller_Admin_Quiz_Attempts::save_grade()}): remarks and/or
	 * earned points are stored per-question, then the attempt grade is
	 * recalculated. When all gradeable questions have been graded, completion
	 * actions are triggered.
	 *
	 * @since [version]
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function grade_item( $request ) {

		$attempt = $this->get_object( (int) $request['id'] );
		if ( is_wp_error( $attempt ) ) {
			return $attempt;
		}

		$submitted = array();
		foreach ( (array) $request['questions'] as $submitted_question ) {
			$submitted[ absint( $submitted_question['id'] ) ] = $submitted_question;
		}

		$questions = $attempt->get_questions();
		$found     = array();

		foreach ( $questions as &$question ) {

			$question_id = absint( $question['id'] );
			if ( ! isset( $submitted[ $question_id ] ) ) {
				continue;
			}

			$found[] = $question_id;
			$data    = $submitted[ $question_id ];

			if ( isset( $data['remarks'] ) ) {
				$question['remarks'] = wp_kses_post( nl2br( $data['remarks'] ) );
			}

			if ( isset( $data['earned'] ) ) {
				$earned             = absint( $data['earned'] );
				$question['earned'] = $earned;
				if ( ! empty( $question['points'] ) && ( $earned / $question['points'] ) >= 0.5 ) {
					$question['correct'] = 'yes';
				} else {
					$question['correct'] = 'no';
				}
			}
		}
		unset( $question );

		$missing = array_diff( array_keys( $submitted ), $found );
		if ( $missing ) {
			return llms_rest_bad_request_error(
				sprintf(
					// Translators: %s = comma separated list of question ids.
					__( 'The following questions are not part of this quiz attempt: %s.', 'lifterlms' ),
					implode( ', ', $missing )
				)
			);
		}

		$attempt->set_questions( $questions, true );
		$attempt->calculate_grade()->save();

		// If all questions were graded the grade will have been calculated and we can trigger completion actions.
		if ( in_array( $attempt->get( 'status' ), array( 'fail', 'pass' ), true ) ) {
			$attempt->do_completion_actions();
		}

		/** This action is documented in includes/controllers/class.llms.controller.admin.quiz.attempts.php in the LifterLMS core plugin. */
		do_action( 'llms_quiz_graded', $attempt->get_student()->get_id(), $attempt->get( 'quiz_id' ), $attempt );

		$request->set_param( 'context', 'edit' );

		return $this->prepare_item_for_response( $this->get_object( $attempt->get( 'id' ) ), $request );
	}

	/**
	 * Prepare a single object output for response.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Quiz_Attempt $attempt Quiz attempt object.
	 * @param WP_REST_Request   $request Full details about the request.
	 * @return array
	 */
	protected function prepare_object_for_response( $attempt, $request ) {

		$data = array(
			'id'             => (int) $attempt->get( 'id' ),
			'student_id'     => (int) $attempt->get( 'student_id' ),
			'quiz_id'        => (int) $attempt->get( 'quiz_id' ),
			'lesson_id'      => (int) $attempt->get( 'lesson_id' ),
			'start_date'     => $attempt->get( 'start_date' ),
			'update_date'    => $attempt->get( 'update_date' ),
			'end_date'       => $attempt->get( 'end_date' ),
			'status'         => $attempt->get( 'status' ),
			'attempt'        => (int) $attempt->get( 'attempt' ),
			'grade'          => (float) $attempt->get( 'grade' ),
			'can_be_resumed' => (bool) $attempt->get( 'can_be_resumed' ),
		);

		$fields = $this->get_fields_for_response( $request );

		if ( rest_is_field_included( 'questions', $fields ) ) {
			$data['questions'] = $this->prepare_questions_for_response( $attempt, $request );
		}

		$data = array_intersect_key( $data, array_flip( $fields ) );

		return $data;
	}

	/**
	 * Prepare the attempt's answered questions for the response.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Quiz_Attempt $attempt Quiz attempt object.
	 * @param WP_REST_Request   $request Full details about the request.
	 * @return array[]
	 */
	protected function prepare_questions_for_response( $attempt, $request ) {

		$questions = array();

		foreach ( $attempt->get_question_objects( false ) as $attempt_question ) {

			$question = $attempt_question->get_question();

			$data = array(
				'id'                     => (int) $attempt_question->get( 'id' ),
				'title'                  => $question ? $question->get( 'title' ) : '',
				'question_type'          => $question ? $question->get( 'question_type' ) : '',
				'points'                 => (int) $attempt_question->get( 'points' ),
				'earned'                 => is_null( $attempt_question->get( 'earned', null ) ) ? null : (int) $attempt_question->get( 'earned' ),
				'correct'                => $attempt_question->is_correct(),
				'status'                 => $attempt_question->get_status(),
				'can_be_manually_graded' => $attempt_question->can_be_manually_graded(),
				'remarks'                => $attempt_question->get( 'remarks' ),
				'answer'                 => (array) $attempt_question->get( 'answer' ),
				'answer_rendered'        => $question ? $attempt_question->get_answer() : '',
				'files'                  => $this->get_answer_files( $attempt_question ),
			);

			/**
			 * Filters a quiz attempt question prepared for a REST response.
			 *
			 * Allows add-ons (such as LifterLMS Advanced Quizzes) to enrich the
			 * question data, for example adding file information for upload-type answers.
			 *
			 * @since [version]
			 *
			 * @param array                      $data             Prepared question data.
			 * @param LLMS_Quiz_Attempt_Question $attempt_question Attempt question object.
			 * @param LLMS_Quiz_Attempt          $attempt          Quiz attempt object.
			 * @param WP_REST_Request            $request          Request object.
			 */
			$questions[] = apply_filters( 'llms_rest_prepare_quiz_attempt_question', $data, $attempt_question, $attempt, $request );
		}

		return $questions;
	}

	/**
	 * Resolve answer values to attachment file data.
	 *
	 * When an answer value is an attachment ID (as stored by upload-type questions),
	 * file information is included so API consumers (e.g. AI graders) can retrieve
	 * the submitted file.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Quiz_Attempt_Question $attempt_question Attempt question object.
	 * @return array[]
	 */
	protected function get_answer_files( $attempt_question ) {

		$files     = array();
		$protector = class_exists( 'LLMS_Media_Protector' ) ? new LLMS_Media_Protector() : false;

		foreach ( (array) $attempt_question->get( 'answer' ) as $value ) {

			if ( ! is_numeric( $value ) || 'attachment' !== get_post_type( (int) $value ) ) {
				continue;
			}

			$id           = (int) $value;
			$url          = wp_get_attachment_url( $id );
			$download_url = $url;

			// Protected uploads require a WordPress session to download; provide a signed,
			// expiring URL so external tools (e.g. AI graders) can fetch the file.
			if ( $protector && $protector->is_media_protected( $id ) ) {
				$download_url = $protector->get_signed_url( $id );
			}

			$files[] = array(
				'id'           => $id,
				'url'          => $url,
				'download_url' => $download_url,
				'filename'     => basename( (string) get_attached_file( $id ) ),
				'mime_type'    => get_post_mime_type( $id ),
			);
		}

		return $files;
	}

	/**
	 * Get the Quiz Attempt's schema, conforming to JSON Schema.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function get_item_schema_base() {

		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'quiz_attempt',
			'type'       => 'object',
			'properties' => array(
				'id'             => array(
					'description' => __( 'Unique identifier for the quiz attempt.', 'lifterlms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'student_id'     => array(
					'description' => __( 'The WP_User ID of the student.', 'lifterlms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'quiz_id'        => array(
					'description' => __( 'The WP_Post ID of the quiz.', 'lifterlms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'lesson_id'      => array(
					'description' => __( 'The WP_Post ID of the lesson.', 'lifterlms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'start_date'     => array(
					'description' => __( 'Date the attempt was started. Format: Y-m-d H:i:s.', 'lifterlms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'update_date'    => array(
					'description' => __( 'Date the attempt was last modified. Format: Y-m-d H:i:s.', 'lifterlms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'end_date'       => array(
					'description' => __( 'Date the attempt was completed. Format: Y-m-d H:i:s.', 'lifterlms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'status'         => array(
					'description' => __( 'The status of the quiz attempt. Attempts with the pending status require manual grading. Pass/fail is determined against the quiz passing grade in effect when the attempt was graded; later changes to the passing grade do not re-evaluate stored attempts.', 'lifterlms' ),
					'type'        => 'string',
					'enum'        => array_keys( llms_get_quiz_attempt_statuses() ),
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'attempt'        => array(
					'description' => __( 'The attempt number for the student on this quiz.', 'lifterlms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'grade'          => array(
					'description' => __( 'The grade of the quiz attempt.', 'lifterlms' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'can_be_resumed' => array(
					'description' => __( 'Whether the quiz attempt can be resumed.', 'lifterlms' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'questions'      => array(
					'description' => __( 'The attempt\'s answered questions, including raw and rendered answers, grading status, remarks, and file data for upload-type answers.', 'lifterlms' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'                     => array(
								'description' => __( 'The WP_Post ID of the question.', 'lifterlms' ),
								'type'        => 'integer',
							),
							'title'                  => array(
								'description' => __( 'The question title (the question itself).', 'lifterlms' ),
								'type'        => 'string',
							),
							'question_type'          => array(
								'description' => __( 'The type of the question.', 'lifterlms' ),
								'type'        => 'string',
							),
							'points'                 => array(
								'description' => __( 'The number of points available for the question.', 'lifterlms' ),
								'type'        => 'integer',
							),
							'earned'                 => array(
								'description' => __( 'The number of points earned. Null when not yet graded.', 'lifterlms' ),
								'type'        => array( 'integer', 'null' ),
							),
							'correct'                => array(
								'description' => __( 'Whether the answer was graded as correct.', 'lifterlms' ),
								'type'        => 'boolean',
							),
							'status'                 => array(
								'description' => __( 'The grading status of the question. waiting indicates the answer requires manual grading.', 'lifterlms' ),
								'type'        => 'string',
								'enum'        => array( 'graded', 'waiting', 'none' ),
							),
							'can_be_manually_graded' => array(
								'description' => __( 'Whether the question answer can be graded manually.', 'lifterlms' ),
								'type'        => 'boolean',
							),
							'remarks'                => array(
								'description' => __( 'Grader remarks (HTML) displayed to the student.', 'lifterlms' ),
								'type'        => array( 'string', 'null' ),
							),
							'answer'                 => array(
								'description' => __( 'The raw answer data. Choice IDs for choice questions, submitted text for text questions, or an attachment ID for upload questions.', 'lifterlms' ),
								'type'        => 'array',
							),
							'answer_rendered'        => array(
								'description' => __( 'The answer rendered as HTML.', 'lifterlms' ),
								'type'        => 'string',
							),
							'files'                  => array(
								'description' => __( 'File data for answers which resolve to uploaded attachments (e.g. upload-type questions). Use the download_url to retrieve the submitted file.', 'lifterlms' ),
								'type'        => 'array',
								'items'       => array(
									'type'       => 'object',
									'properties' => array(
										'id'           => array(
											'type' => 'integer',
										),
										'url'          => array(
											'type'   => 'string',
											'format' => 'uri',
										),
										'download_url' => array(
											'description' => __( 'URL to download the file. For protected uploads this is a temporary signed URL which expires; fetch it promptly and do not store it.', 'lifterlms' ),
											'type'        => 'string',
											'format'      => 'uri',
										),
										'filename'     => array(
											'type' => 'string',
										),
										'mime_type'    => array(
											'type' => 'string',
										),
									),
								),
							),
						),
					),
				),
			),
		);
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Quiz_Attempt $object  Quiz attempt object.
	 * @param WP_REST_Request   $request Request object.
	 * @return array Links for the given object.
	 */
	protected function prepare_links( $object, $request ) {

		$links = parent::prepare_links( $object, $request );

		$links['grade'] = array(
			'href' => rest_url( sprintf( '/%1$s/%2$s/%3$d/grade', $this->namespace, $this->rest_base, $object->get( 'id' ) ) ),
		);

		$links['student'] = array(
			'href'       => rest_url( sprintf( '/%1$s/students/%2$d', $this->namespace, $object->get( 'student_id' ) ) ),
			'embeddable' => true,
		);

		$links['quiz'] = array(
			'href'       => rest_url( sprintf( '/%1$s/quizzes/%2$d', $this->namespace, $object->get( 'quiz_id' ) ) ),
			'embeddable' => true,
		);

		$links['lesson'] = array(
			'href'       => rest_url( sprintf( '/%1$s/lessons/%2$d', $this->namespace, $object->get( 'lesson_id' ) ) ),
			'embeddable' => true,
		);

		return $links;
	}
}
