<?php
/**
 * REST Quizzes Controller
 *
 * @package LifterLMS_REST/Classes/Controllers
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Quizzes_Controller class.
 *
 * @since [version]
 */
class LLMS_REST_Quizzes_Controller extends LLMS_REST_Posts_Controller {

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'llms_quiz';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'quizzes';

	/**
	 * Retrieves the query params for the objects collection.
	 *
	 * @since [version]
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {

		$query_params = parent::get_collection_params();

		$query_params['lesson'] = array(
			'description' => __( 'Limit results to quizzes attached to a specific lesson. Accepts a lesson WP_Post ID.', 'lifterlms' ),
			'type'        => 'integer',
		);

		return $query_params;
	}

	/**
	 * Format query arguments to retrieve a collection of objects.
	 *
	 * @since [version]
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array|WP_Error
	 */
	protected function prepare_collection_query_args( $request ) {

		$query_args = parent::prepare_collection_query_args( $request );
		if ( is_wp_error( $query_args ) ) {
			return $query_args;
		}

		if ( ! empty( $request['lesson'] ) ) {
			$query_args['meta_query'] = array(
				array(
					'key'   => '_llms_lesson_id',
					'value' => absint( $request['lesson'] ),
				),
			);
		}

		return $query_args;
	}

	/**
	 * Prepares a single quiz for create or update.
	 *
	 * @since [version]
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return array|WP_Error Array of quiz args or WP_Error.
	 */
	protected function prepare_item_for_database( $request ) {

		$prepared_item = parent::prepare_item_for_database( $request );
		if ( is_wp_error( $prepared_item ) ) {
			return $prepared_item;
		}

		// Default new quizzes to published when status is omitted (LLMS_Post_Model otherwise creates drafts).
		if ( empty( $request['id'] ) && empty( $prepared_item['post_status'] ) ) {
			$status = $this->handle_status_param( 'publish' );
			if ( is_wp_error( $status ) ) {
				return $status;
			}
			$prepared_item['post_status'] = $status;
		}

		$schema = $this->get_item_schema();

		if ( ! empty( $schema['properties']['lesson_id'] ) && isset( $request['lesson_id'] ) ) {

			$lesson_id = absint( $request['lesson_id'] );

			if ( $lesson_id ) {

				$lesson = llms_get_post( $lesson_id );
				if ( ! is_a( $lesson, 'LLMS_Lesson' ) ) {
					return llms_rest_bad_request_error( __( 'The provided lesson_id is not a valid lesson.', 'lifterlms' ) );
				}

				if ( ! current_user_can( 'edit_post', $lesson_id ) ) {
					return llms_rest_authorization_required_error( __( 'Sorry, you are not allowed to attach a quiz to the requested lesson.', 'lifterlms' ) );
				}
			}

			$prepared_item['lesson_id'] = $lesson_id;
		}

		// Integer/float quiz settings.
		foreach ( array( 'passing_percent', 'allowed_attempts', 'time_limit' ) as $prop ) {
			if ( ! empty( $schema['properties'][ $prop ] ) && isset( $request[ $prop ] ) ) {
				$prepared_item[ $prop ] = $request[ $prop ];
			}
		}

		// Boolean ("yesno") quiz settings.
		foreach ( array( 'limit_attempts', 'limit_time', 'show_correct_answer', 'random_questions', 'can_be_resumed', 'disable_retake' ) as $prop ) {
			if ( ! empty( $schema['properties'][ $prop ] ) && isset( $request[ $prop ] ) ) {
				$prepared_item[ $prop ] = llms_bool_to_string( $request[ $prop ] );
			}
		}

		/**
		 * Filters a quiz before it is inserted via the REST API.
		 *
		 * @since [version]
		 *
		 * @param array           $prepared_item Array of quiz item properties prepared for database.
		 * @param WP_REST_Request $request       Full details about the request.
		 * @param array           $schema        The item schema.
		 */
		return apply_filters( 'llms_rest_pre_insert_llms_quiz', $prepared_item, $request, $schema );
	}

	/**
	 * Keep the lesson <-> quiz relationship meta in sync, mirroring the course builder.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Quiz       $quiz          LLMS_Quiz instance.
	 * @param WP_REST_Request $request       Full details about the request.
	 * @param array           $schema        The item schema.
	 * @param array           $prepared_item Prepared item array.
	 * @param bool            $creating      Optional. Whether we're in creation or update phase. Default true (create).
	 * @return bool|WP_Error True on success or false if nothing to update, WP_Error object if something went wrong during the update.
	 */
	protected function update_additional_object_fields( $quiz, $request, $schema, $prepared_item, $creating = true ) {

		if ( ! isset( $request['lesson_id'] ) ) {
			return false;
		}

		$quiz_id   = $quiz->get( 'id' );
		$lesson_id = absint( $request['lesson_id'] );

		// Detach any other lesson currently pointing at this quiz.
		$attached_lessons = get_posts(
			array(
				'post_type'      => 'lesson',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => '_llms_quiz',
				'meta_value'     => $quiz_id,
			)
		);

		foreach ( $attached_lessons as $attached_lesson_id ) {
			if ( absint( $attached_lesson_id ) === $lesson_id ) {
				continue;
			}
			$old_lesson = llms_get_post( $attached_lesson_id );
			if ( is_a( $old_lesson, 'LLMS_Lesson' ) ) {
				$old_lesson->set( 'quiz', 0 );
				$old_lesson->set( 'quiz_enabled', 'no' );
			}
		}

		if ( $lesson_id ) {
			$lesson = llms_get_post( $lesson_id );
			if ( is_a( $lesson, 'LLMS_Lesson' ) ) {
				$lesson->set( 'quiz', $quiz_id );
				$lesson->set( 'quiz_enabled', 'yes' );
			}
		}

		return true;
	}

	/**
	 * Deletes a single quiz.
	 *
	 * Detaches the quiz from any lesson before deletion.
	 *
	 * @since [version]
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {

		$object = $this->get_object( (int) $request['id'] );

		if ( ! is_wp_error( $object ) && $this->is_delete_forced( $request ) ) {

			$attached_lessons = get_posts(
				array(
					'post_type'      => 'lesson',
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'meta_key'       => '_llms_quiz',
					'meta_value'     => $object->get( 'id' ),
				)
			);

			foreach ( $attached_lessons as $attached_lesson_id ) {
				$lesson = llms_get_post( $attached_lesson_id );
				if ( is_a( $lesson, 'LLMS_Lesson' ) ) {
					$lesson->set( 'quiz', 0 );
					$lesson->set( 'quiz_enabled', 'no' );
				}
			}
		}

		return parent::delete_item( $request );
	}

	/**
	 * Prepare a single object output for response.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Quiz       $quiz    Quiz object.
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array
	 */
	protected function prepare_object_for_response( $quiz, $request ) {

		$data = parent::prepare_object_for_response( $quiz, $request );

		$data['lesson_id']           = $quiz->get( 'lesson_id' );
		$data['passing_percent']     = $quiz->get( 'passing_percent' );
		$data['allowed_attempts']    = $quiz->get( 'allowed_attempts' );
		$data['limit_attempts']      = llms_parse_bool( $quiz->get( 'limit_attempts' ) );
		$data['limit_time']          = llms_parse_bool( $quiz->get( 'limit_time' ) );
		$data['time_limit']          = $quiz->get( 'time_limit' );
		$data['show_correct_answer'] = llms_parse_bool( $quiz->get( 'show_correct_answer' ) );
		$data['random_questions']    = llms_parse_bool( $quiz->get( 'random_questions' ) );
		$data['can_be_resumed']      = llms_parse_bool( $quiz->get( 'can_be_resumed' ) );
		$data['disable_retake']      = llms_parse_bool( $quiz->get( 'disable_retake' ) );

		return $data;
	}

	/**
	 * Get the Quiz's schema, conforming to JSON Schema.
	 *
	 * @since [version]
	 *
	 * @return array Item schema data.
	 */
	protected function get_item_schema_base() {

		$schema = parent::get_item_schema_base();

		$quiz_properties = array(
			'lesson_id'           => array(
				'description' => __( 'WordPress post ID of the quiz\'s parent lesson. 0 indicates an "orphaned" quiz not attached to any lesson.', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => 'absint',
				),
			),
			'passing_percent'     => array(
				'description' => __( 'Grade required for a student to "pass" the quiz.', 'lifterlms' ),
				'type'        => 'number',
				'default'     => 65,
				'minimum'     => 0,
				'maximum'     => 100,
				'context'     => array( 'view', 'edit' ),
			),
			'limit_attempts'      => array(
				'description' => __( 'Whether the number of attempts students are allowed to take the quiz is limited.', 'lifterlms' ),
				'type'        => 'boolean',
				'default'     => false,
				'context'     => array( 'view', 'edit' ),
			),
			'allowed_attempts'    => array(
				'description' => __( 'Number of times a student is allowed to take the quiz before being locked out of it. Only used when limit_attempts is true.', 'lifterlms' ),
				'type'        => 'integer',
				'default'     => 5,
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => 'absint',
				),
			),
			'limit_time'          => array(
				'description' => __( 'Whether a time limit is enforced on the quiz.', 'lifterlms' ),
				'type'        => 'boolean',
				'default'     => false,
				'context'     => array( 'view', 'edit' ),
			),
			'time_limit'          => array(
				'description' => __( 'Quiz time limit, in minutes. Only used when limit_time is true.', 'lifterlms' ),
				'type'        => 'integer',
				'default'     => 30,
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => 'absint',
				),
			),
			'show_correct_answer' => array(
				'description' => __( 'Whether to show the correct answer(s) to students on the quiz results screen.', 'lifterlms' ),
				'type'        => 'boolean',
				'default'     => false,
				'context'     => array( 'view', 'edit' ),
			),
			'random_questions'    => array(
				'description' => __( 'Whether to randomize the order of questions for each attempt.', 'lifterlms' ),
				'type'        => 'boolean',
				'default'     => false,
				'context'     => array( 'view', 'edit' ),
			),
			'can_be_resumed'      => array(
				'description' => __( 'Whether the latest incomplete quiz attempt can be resumed.', 'lifterlms' ),
				'type'        => 'boolean',
				'default'     => false,
				'context'     => array( 'view', 'edit' ),
			),
			'disable_retake'      => array(
				'description' => __( 'Whether students are prevented from retaking the quiz after passing it.', 'lifterlms' ),
				'type'        => 'boolean',
				'default'     => false,
				'context'     => array( 'view', 'edit' ),
			),
		);

		$schema['properties'] = array_merge( $schema['properties'], $quiz_properties );

		return $schema;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Quiz       $object  Object data.
	 * @param WP_REST_Request $request Request object.
	 * @return array Links for the given object.
	 */
	protected function prepare_links( $object, $request ) {

		$links = parent::prepare_links( $object, $request );

		$object_id = $object->get( 'id' );

		$links['questions'] = array(
			'href' => rest_url( sprintf( '/%1$s/%2$s/%3$d/questions', $this->namespace, $this->rest_base, $object_id ) ),
		);

		$lesson_id = $object->get( 'lesson_id' );
		if ( $lesson_id ) {
			$links['lesson'] = array(
				'href'       => rest_url( sprintf( '/%1$s/lessons/%2$d', $this->namespace, $lesson_id ) ),
				'embeddable' => true,
			);
		}

		return $links;
	}
}
