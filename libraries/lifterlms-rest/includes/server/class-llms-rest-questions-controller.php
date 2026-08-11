<?php
/**
 * REST Questions Controller
 *
 * @package LifterLMS_REST/Classes/Controllers
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Questions_Controller class.
 *
 * @since [version]
 */
class LLMS_REST_Questions_Controller extends LLMS_REST_Posts_Controller {

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'llms_question';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'questions';

	/**
	 * Schema properties available for ordering the collection.
	 *
	 * Default to menu_order to match {@see LLMS_Question_Manager::get_questions()}.
	 *
	 * @var string[]
	 */
	protected $orderby_properties = array(
		'menu_order',
		'id',
		'title',
		'date_created',
		'date_updated',
		'relevance',
	);

	/**
	 * Register routes.
	 *
	 * Registers the default CRUD routes and an additional nested collection
	 * route to list all questions belonging to a specific quiz.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function register_routes() {

		parent::register_routes();

		register_rest_route(
			$this->namespace,
			'/quizzes/(?P<quiz_id>[\d]+)/questions',
			array(
				'args'   => array(
					'quiz_id' => array(
						'description' => __( 'Unique identifier for the parent quiz. The WordPress Post ID.', 'lifterlms' ),
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
	 * Retrieves the query params for the objects collection.
	 *
	 * @since [version]
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {

		$query_params = parent::get_collection_params();

		$query_params['parent'] = array(
			'description' => __( 'Limit results to questions belonging to a specific quiz. Accepts a quiz WP_Post ID.', 'lifterlms' ),
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

		// The nested route's quiz_id param takes precedence over the parent collection param.
		$parent = ! empty( $request['quiz_id'] ) ? $request['quiz_id'] : $request['parent'];

		if ( ! empty( $parent ) ) {
			$query_args['meta_query'] = array(
				array(
					'key'   => '_llms_parent_id',
					'value' => absint( $parent ),
				),
			);
		}

		return $query_args;
	}

	/**
	 * Prepares a single question for create or update.
	 *
	 * @since [version]
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return array|WP_Error Array of question args or WP_Error.
	 */
	protected function prepare_item_for_database( $request ) {

		$prepared_item = parent::prepare_item_for_database( $request );
		if ( is_wp_error( $prepared_item ) ) {
			return $prepared_item;
		}

		$schema = $this->get_item_schema();

		if ( ! empty( $schema['properties']['parent_id'] ) && isset( $request['parent_id'] ) ) {

			$parent_id = absint( $request['parent_id'] );

			if ( $parent_id ) {

				$quiz = llms_get_post( $parent_id );
				if ( ! is_a( $quiz, 'LLMS_Quiz' ) ) {
					return llms_rest_bad_request_error( __( 'The provided parent_id is not a valid quiz.', 'lifterlms' ) );
				}

				if ( ! current_user_can( 'edit_post', $parent_id ) ) {
					return llms_rest_authorization_required_error( __( 'Sorry, you are not allowed to add questions to the requested quiz.', 'lifterlms' ) );
				}
			}

			$prepared_item['parent_id'] = $parent_id;
		}

		if ( ! empty( $schema['properties']['question_type'] ) && isset( $request['question_type'] ) ) {
			$prepared_item['question_type'] = $request['question_type'];
		}

		if ( ! empty( $schema['properties']['points'] ) && isset( $request['points'] ) ) {
			$prepared_item['points'] = absint( $request['points'] );
		}

		if ( ! empty( $schema['properties']['video_src'] ) && isset( $request['video_src'] ) ) {
			$prepared_item['video_src']     = $request['video_src'];
			$prepared_item['video_enabled'] = empty( $request['video_src'] ) ? 'no' : 'yes';
		}

		if ( ! empty( $schema['properties']['clarifications'] ) && isset( $request['clarifications'] ) ) {
			$prepared_item['clarifications']         = $request['clarifications'];
			$prepared_item['clarifications_enabled'] = empty( $request['clarifications'] ) ? 'no' : 'yes';
		}

		if ( ! empty( $schema['properties']['multi_choices'] ) && isset( $request['multi_choices'] ) ) {
			$prepared_item['multi_choices'] = $request['multi_choices'] ? 'yes' : 'no';
		}

		// The question description lives in post_content and its display is controlled by description_enabled.
		if ( isset( $prepared_item['post_content'] ) ) {
			$prepared_item['description_enabled'] = empty( $prepared_item['post_content'] ) ? 'no' : 'yes';
		}

		// Auto-sequence among siblings when creating without an explicit positive menu_order.
		// The shared posts schema defaults menu_order to 0, so treat 0 as "unset" here.
		if ( empty( $request['id'] ) && ! empty( $prepared_item['parent_id'] ) && empty( $prepared_item['menu_order'] ) ) {
			$prepared_item['menu_order'] = $this->get_next_menu_order( $prepared_item['parent_id'] );
		}

		/**
		 * Filters a question before it is inserted via the REST API.
		 *
		 * @since [version]
		 *
		 * @param array           $prepared_item Array of question item properties prepared for database.
		 * @param WP_REST_Request $request       Full details about the request.
		 * @param array           $schema        The item schema.
		 */
		return apply_filters( 'llms_rest_pre_insert_llms_question', $prepared_item, $request, $schema );
	}

	/**
	 * Retrieve the next menu_order for a new question under a quiz.
	 *
	 * @since [version]
	 *
	 * @param int $parent_id Quiz post ID.
	 * @return int
	 */
	protected function get_next_menu_order( $parent_id ) {

		$query = new WP_Query(
			array(
				'post_type'              => 'llms_question',
				'post_status'            => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'posts_per_page'         => 1,
				'orderby'                => 'menu_order',
				'order'                  => 'DESC',
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'meta_query'             => array(
					array(
						'key'   => '_llms_parent_id',
						'value' => absint( $parent_id ),
					),
				),
			)
		);

		if ( empty( $query->posts ) ) {
			return 1;
		}

		$last = get_post( $query->posts[0] );

		return $last ? absint( $last->menu_order ) + 1 : 1;
	}

	/**
	 * Updates the question's choices from a REST request.
	 *
	 * Submitted choices are treated as the complete set: existing choices which
	 * are not included in the request are deleted.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Question   $question      LLMS_Question instance.
	 * @param WP_REST_Request $request       Full details about the request.
	 * @param array           $schema        The item schema.
	 * @param array           $prepared_item Prepared item array.
	 * @param bool            $creating      Optional. Whether we're in creation or update phase. Default true (create).
	 * @return bool|WP_Error True on success or false if nothing to update, WP_Error object if something went wrong during the update.
	 */
	protected function update_additional_object_fields( $question, $request, $schema, $prepared_item, $creating = true ) {

		if ( empty( $schema['properties']['choices'] ) || ! isset( $request['choices'] ) ) {
			return false;
		}

		$existing = $question->get_choices( 'choices' );
		$existing = is_array( $existing ) ? $existing : array();
		$keep     = array();

		foreach ( (array) $request['choices'] as $choice_data ) {

			$data = array();
			if ( isset( $choice_data['choice'] ) ) {
				$data['choice'] = $choice_data['choice'];
			}
			if ( isset( $choice_data['choice_type'] ) ) {
				$data['choice_type'] = $choice_data['choice_type'];
			}
			if ( isset( $choice_data['correct'] ) ) {
				$data['correct'] = (bool) $choice_data['correct'];
			}
			if ( isset( $choice_data['marker'] ) ) {
				$data['marker'] = $choice_data['marker'];
			}

			$choice = empty( $choice_data['id'] ) ? false : $question->get_choice( $choice_data['id'] );

			if ( $choice ) {
				$choice->update( $data )->save();
				$keep[] = $choice_data['id'];
			} else {
				$created = $question->create_choice( $data );
				if ( $created ) {
					$keep[] = $created;
				}
			}
		}

		foreach ( $existing as $choice ) {
			if ( ! in_array( $choice->get( 'id' ), $keep, true ) ) {
				$question->delete_choice( $choice->get( 'id' ) );
			}
		}

		return true;
	}

	/**
	 * Prepare a single object output for response.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Question   $question Question object.
	 * @param WP_REST_Request $request  Full details about the request.
	 * @return array
	 */
	protected function prepare_object_for_response( $question, $request ) {

		$data = parent::prepare_object_for_response( $question, $request );

		$data['parent_id']      = $question->get( 'parent_id' );
		$data['question_type']  = $question->get( 'question_type' );
		$data['points']         = $question->get( 'points' );
		$data['multi_choices']  = llms_parse_bool( $question->get( 'multi_choices' ) );
		$data['clarifications'] = $question->get( 'clarifications' );
		$data['video_src']      = $question->get( 'video_src' );
		$data['choices']        = array();

		if ( $question->supports( 'choices' ) ) {
			foreach ( $question->get_choices() as $choice ) {
				$data['choices'][] = array(
					'id'          => $choice->get( 'id' ),
					'choice'      => $choice->get( 'choice' ),
					'choice_type' => $choice->get( 'choice_type' ),
					'correct'     => (bool) $choice->get( 'correct' ),
					'marker'      => $choice->get( 'marker' ),
				);
			}
		}

		/**
		 * Filters question data prepared for the REST response.
		 *
		 * @since [version]
		 *
		 * @param array           $data     Array of question properties prepared for response.
		 * @param LLMS_Question   $question Question object.
		 * @param WP_REST_Request $request  Request object.
		 */
		return apply_filters( 'llms_rest_prepare_llms_question_object_response', $data, $question, $request );
	}

	/**
	 * Get the Question's schema, conforming to JSON Schema.
	 *
	 * @since [version]
	 *
	 * @return array Item schema data.
	 */
	protected function get_item_schema_base() {

		$schema = parent::get_item_schema_base();

		// Questions don't expose these post props.
		unset(
			$schema['properties']['excerpt'],
			$schema['properties']['password'],
			$schema['properties']['featured_media'],
			$schema['properties']['comment_status'],
			$schema['properties']['ping_status'],
			$schema['properties']['permalink']
		);

		// The question description (content) is optional.
		$schema['properties']['content']['required'] = false;

		$question_properties = array(
			'parent_id'      => array(
				'description' => __( 'WordPress post ID of the question\'s parent quiz. 0 indicates an "orphaned" question.', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => 'absint',
				),
			),
			'question_type'  => array(
				'description' => __( 'The type of the question. Additional types may be registered by add-ons such as LifterLMS Advanced Quizzes.', 'lifterlms' ),
				'type'        => 'string',
				'default'     => 'choice',
				'enum'        => array_keys( llms_get_question_types() ),
				'context'     => array( 'view', 'edit' ),
			),
			'points'         => array(
				'description' => __( 'The number of points awarded for a correct answer to the question.', 'lifterlms' ),
				'type'        => 'integer',
				'default'     => 1,
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => 'absint',
				),
			),
			'multi_choices'  => array(
				'description' => __( 'Whether students may select more than one choice. Only used by choice-type questions.', 'lifterlms' ),
				'type'        => 'boolean',
				'default'     => false,
				'context'     => array( 'view', 'edit' ),
			),
			'clarifications' => array(
				'description' => __( 'Clarification text (HTML) displayed to students when reviewing the question after grading.', 'lifterlms' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => 'wp_kses_post',
				),
			),
			'video_src'      => array(
				'description' => __( 'URL to an oEmbed enabled video to display with the question.', 'lifterlms' ),
				'type'        => 'string',
				'format'      => 'uri',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => 'esc_url_raw',
				),
			),
			'choices'        => array(
				'description' => __( 'List of the question\'s choices. Only used by question types which support choices. Treated as the complete set on update: existing choices omitted from the list are deleted.', 'lifterlms' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'          => array(
							'description' => __( 'The choice unique identifier. Provide to update an existing choice, omit to create a new choice.', 'lifterlms' ),
							'type'        => 'string',
						),
						'choice'      => array(
							'description' => __( 'The choice content. A string for text choices or an object (id, src) for image choices.', 'lifterlms' ),
							'type'        => array( 'string', 'object' ),
						),
						'choice_type' => array(
							'description' => __( 'The type of the choice content.', 'lifterlms' ),
							'type'        => 'string',
							'enum'        => array( 'text', 'image' ),
							'default'     => 'text',
						),
						'correct'     => array(
							'description' => __( 'Whether the choice is a correct answer.', 'lifterlms' ),
							'type'        => 'boolean',
							'default'     => false,
						),
						'marker'      => array(
							'description' => __( 'The choice marker (A, B, C...). Automatically generated when omitted.', 'lifterlms' ),
							'type'        => 'string',
						),
					),
				),
			),
		);

		$schema['properties'] = array_merge( $schema['properties'], $question_properties );

		return $schema;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Question   $object  Object data.
	 * @param WP_REST_Request $request Request object.
	 * @return array Links for the given object.
	 */
	protected function prepare_links( $object, $request ) {

		$links = parent::prepare_links( $object, $request );

		$parent_id = $object->get( 'parent_id' );
		if ( $parent_id ) {
			$links['quiz'] = array(
				'href'       => rest_url( sprintf( '/%1$s/quizzes/%2$d', $this->namespace, $parent_id ) ),
				'embeddable' => true,
			);
		}

		return $links;
	}
}
