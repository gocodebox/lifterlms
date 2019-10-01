<?php
/**
 * REST Courses Controller Class
 *
 * @package LifterLMS_REST/Classes/Controllers
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Courses_Controller
 *
 * @since 1.0.0-beta.1
 */
class LLMS_REST_Courses_Controller extends LLMS_REST_Posts_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'courses';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'course';

	/**
	 * Enrollments controller
	 *
	 * @var LLMS_REST_Enrollments_Controller
	 */
	protected $enrollments_controller;

	/**
	 * Sections controller
	 *
	 * @var LLMS_REST_Sections_Controller
	 */
	protected $sections_controller;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0-beta.1
	 */
	public function __construct() {

		$this->enrollments_controller = new LLMS_REST_Enrollments_Controller();
		$this->enrollments_controller->set_collection_params( $this->get_enrollments_collection_params() );

		$this->sections_controller = new LLMS_REST_Sections_Controller( '' );
		$this->sections_controller->set_collection_params( $this->get_course_content_collection_params() );

	}

	/**
	 * Register routes.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function register_routes() {

		parent::register_routes();

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/enrollments',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique Course Identifier. The WordPress Post ID', 'lifterlms' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this->enrollments_controller, 'get_items' ),
					'permission_callback' => array( $this->enrollments_controller, 'get_items_permissions_check' ),
					'args'                => $this->enrollments_controller->get_collection_params(),
				),
				'schema' => array( $this->enrollments_controller, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/content',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique Course Identifier. The WordPress Post ID', 'lifterlms' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_course_content_items' ),
					'permission_callback' => array( $this->sections_controller, 'get_items_permissions_check' ),
					'args'                => $this->sections_controller->get_collection_params(),
				),
				'schema' => array( $this->sections_controller, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Get object.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id Object ID.
	 * @return LLMS_Course|WP_Error
	 */
	protected function get_object( $id ) {
		$course = llms_get_post( $id );
		return $course && is_a( $course, 'LLMS_Course' ) ? $course : llms_rest_not_found_error();
	}

	/**
	 * Get an LLMS_Course
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $object_args Object args.
	 * @return LLMS_Post_Model|WP_Error
	 */
	protected function create_llms_post( $object_args ) {
		$object = new LLMS_Course( 'new', $object_args );
		return $object && is_a( $object, 'LLMS_Course' ) ? $object : llms_rest_not_found_error();
	}

	/**
	 * Get the Course's schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function get_item_schema() {

		$schema = parent::get_item_schema();

		$course_properties = array(
			'catalog_visibility'        => array(
				'description' => __( 'Visibility of the course in catalogs and search results.', 'lifterlms' ),
				'type'        => 'string',
				'enum'        => array_keys( llms_get_product_visibility_options() ),
				'default'     => 'catalog_search',
				'context'     => array( 'view', 'edit' ),
			),
			// consider to move tags and cats in the posts controller abstract.
			'categories'                => array(
				'description' => __( 'List of course categories.', 'lifterlms' ),
				'type'        => 'array',
				'items'       => array(
					'type' => 'integer',
				),
				'context'     => array( 'view', 'edit' ),
			),
			'tags'                      => array(
				'description' => __( 'List of course tags.', 'lifterlms' ),
				'type'        => 'array',
				'items'       => array(
					'type' => 'integer',
				),
				'context'     => array( 'view', 'edit' ),
			),
			'difficulties'              => array(
				'description' => __( 'List of course difficulties.', 'lifterlms' ),
				'type'        => 'array',
				'items'       => array(
					'type' => 'integer',
				),
				'context'     => array( 'view', 'edit' ),
			),
			'tracks'                    => array(
				'description' => __( 'List of course tracks.', 'lifterlms' ),
				'type'        => 'array',
				'items'       => array(
					'type' => 'integer',
				),
				'context'     => array( 'view', 'edit' ),
			),
			'instructors'               => array(
				'description' => __( 'List of course instructors.', 'lifterlms' ),
				'type'        => 'array',
				'items'       => array(
					'type' => 'integer',
				),
				'context'     => array( 'view', 'edit' ),
			),
			'audio_embed'               => array(
				'description' => __( 'URL to an oEmbed enable audio URL.', 'lifterlms' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'format'      => 'uri',
				'arg_options' => array(
					'sanitize_callback' => 'esc_url_raw',
				),
			),
			'video_embed'               => array(
				'description' => __( 'URL to an oEmbed enable video URL.', 'lifterlms' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'format'      => 'uri',
				'arg_options' => array(
					'sanitize_callback' => 'esc_url_raw',
				),
			),
			'capacity_enabled'          => array(
				'description' => __( 'Determines if an enrollment capacity limit is enabled.', 'lifterlms' ),
				'type'        => 'boolean',
				'default'     => false,
			),
			'capacity_limit'            => array(
				'description' => __( 'Number of students who can be enrolled in the course before enrollment closes.', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => 'absint',
				),
			),
			'capacity_message'          => array(
				'description' => __( 'Message displayed when enrollment capacity has been reached.', 'lifterlms' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database().
					'validate_callback' => null, // Note: validation implemented in self::prepare_item_for_database().
				),
				'properties'  => array(
					'raw'      => array(
						'description' => __( 'Raw message content.', 'lifterlms' ),
						'type'        => 'string',
						'context'     => array( 'edit' ),
					),
					'rendered' => array(
						'description' => __( 'Rendered message content.', 'lifterlms' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
			),
			'prerequisite'              => array(
				'description' => __( 'Course ID of the prerequisite course.', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => 'absint',
				),
			),
			'prerequisite_track'        => array(
				'description' => __( 'Term ID of a prerequisite track.', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => 'absint',
				),
			),
			'length'                    => array(
				'description' => __( 'User defined course length.', 'lifterlms' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database().
					'validate_callback' => null, // Note: validation implemented in self::prepare_item_for_database().
				),
				'properties'  => array(
					'raw'      => array(
						'description' => __( 'Raw length description.', 'lifterlms' ),
						'type'        => 'string',
						'context'     => array( 'edit' ),
					),
					'rendered' => array(
						'description' => __( 'Rendered length description.', 'lifterlms' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
			),
			'restricted_message'        => array(
				'description' => __( 'Message displayed when non-enrolled visitors try to access restricted course content (lessons, quizzes, etc..) directly.', 'lifterlms' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database().
					'validate_callback' => null, // Note: validation implemented in self::prepare_item_for_database().
				),
				'properties'  => array(
					'raw'      => array(
						'description' => __( 'Raw message content.', 'lifterlms' ),
						'default'     => __( 'You must enroll in this course to access course content.', 'lifterlms' ),
						'type'        => 'string',
						'context'     => array( 'edit' ),
					),
					'rendered' => array(
						'description' => __( 'Rendered message content.', 'lifterlms' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
				'default'     => __( 'You must enroll in this course to access course content.', 'lifterlms' ),
			),
			'access_closes_date'        => array(
				'description' => __(
					'Date when the course closes. After this date enrolled students may no longer view and interact with the restricted course content.
					If blank the course is open indefinitely after the the access_opens_date has passed.
					Does not affect course enrollment, see enrollment_opens_date to control the course enrollment close date.
					Format: Y-m-d H:i:s.',
					'lifterlms'
				),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
			'access_closes_message'     => array(
				'description' => __( 'Message displayed to enrolled students when the course is accessed after the access_closes_date has passed.', 'lifterlms' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database().
					'validate_callback' => null, // Note: validation implemented in self::prepare_item_for_database().
				),
				'properties'  => array(
					'raw'      => array(
						'description' => __( 'Raw message content.', 'lifterlms' ),
						'type'        => 'string',
						'context'     => array( 'edit' ),
					),
					'rendered' => array(
						'description' => __( 'Rendered message content.', 'lifterlms' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
				'default'     => __( 'This course closed on [lifterlms_course_info id={{course_id}} key="end_date"].', 'lifterlms' ),
			),
			'access_opens_date'         => array(
				'description' => __(
					'Date when the course opens, allowing enrolled students to begin to view and interact with the restricted course content.
					If blank the course is open until after the access_closes_date has passed.
					Does not affect course enrollment, see enrollment_opens_date to control the course enrollment start date.
					Format: Y-m-d H:i:s.',
					'lifterlms'
				),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
			'access_opens_message'      => array(
				'description' => __( 'Message displayed to enrolled students when the course is accessed before the access_opens_date has passed.', 'lifterlms' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database().
					'validate_callback' => null, // Note: validation implemented in self::prepare_item_for_database().
				),
				'properties'  => array(
					'raw'      => array(
						'description' => __( 'Raw message content.', 'lifterlms' ),
						'type'        => 'string',
						'context'     => array( 'edit' ),
					),
					'rendered' => array(
						'description' => __( 'Rendered message content.', 'lifterlms' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
				'default'     => __( 'This course opens on [lifterlms_course_info id={{course_id}} key="start_date"].', 'lifterlms' ),
			),
			'enrollment_closes_date'    => array(
				'description' => __(
					'Date when the course enrollment closes.
					If blank course enrollment is open indefinitely after the the enrollment_opens_date has passed.
					Does not affect course content access, see access_opens_date to control course access close date.
					Format: Y-m-d H:i:s.',
					'lifterlms'
				),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
			'enrollment_closes_message' => array(
				'description' => __( 'Message displayed to visitors when attempting to enroll into a course after the enrollment_closes_date has passed.', 'lifterlms' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database().
					'validate_callback' => null, // Note: validation implemented in self::prepare_item_for_database().
				),
				'properties'  => array(
					'raw'      => array(
						'description' => __( 'Raw message content.', 'lifterlms' ),
						'type'        => 'string',
						'context'     => array( 'edit' ),
					),
					'rendered' => array(
						'description' => __( 'Rendered message content.', 'lifterlms' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
				'default'     => __( 'Enrollment in this course closed on [lifterlms_course_info id={{course_id}} key="enrollment_end_date"].', 'lifterlms' ),
			),
			'enrollment_opens_date'     => array(
				'description' => __(
					'Date when the course enrollment opens.
					If blank course enrollment is open until after the enrollment_closes_date has passed.
					Does not affect course content access, see access_opens_date to control course access start date.
					Format: Y-m-d H:i:s.',
					'lifterlms'
				),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
			'enrollment_opens_message'  => array(
				'description' => __( 'Message displayed to visitors when attempting to enroll into a course before the enrollment_opens_date has passed.', 'lifterlms' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database().
					'validate_callback' => null, // Note: validation implemented in self::prepare_item_for_database().
				),
				'properties'  => array(
					'raw'      => array(
						'description' => __( 'Raw message content.', 'lifterlms' ),
						'type'        => 'string',
						'context'     => array( 'edit' ),
					),
					'rendered' => array(
						'description' => __( 'Rendered message content.', 'lifterlms' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
				'default'     => __( 'Enrollment in this course opens on [lifterlms_course_info id={{course_id}} key="enrollment_start_date"].', 'lifterlms' ),
			),
			'sales_page_page_id'        => array(
				'description' => __(
					'The WordPress page ID of the sales page. Required when sales_page_type equals page. Only returned when the sales_page_type equals page.',
					'lifterlms'
				),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => 'absint',
				),
			),
			'sales_page_page_type'      => array(
				'description' => __(
					'Determines the type of sales page content to display.<br> - <code>none</code> displays the course content.<br> - <code>content</code> displays alternate content from the <code>excerpt</code> property.<br> - <code>page</code> redirects to the WordPress page defined in <code>content_page_id</code>.<br> - <code>url</code> redirects to the URL defined in <code>content_page_url</code>',
					'lifterlms'
				),
				'type'        => 'string',
				'default'     => 'none',
				'enum'        => array_keys( llms_get_sales_page_types() ),
				'context'     => array( 'view', 'edit' ),
			),
			'sales_page_page_url'       => array(
				'description' => __(
					'The URL of the sales page content. Required when <code>content_type</code> equals <code>url</code>. Only returned when the <code>content_type</code> equals <code>url</code>.',
					'lifterlms'
				),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'format'      => 'uri',
				'arg_options' => array(
					'sanitize_callback' => 'esc_url_raw',
				),
			),
			'video_tile'                => array(
				'description' => __( 'When true the video_embed will be used on the course tiles (on the catalog, for example) instead of the featured image.', 'lifterlms' ),
				'type'        => 'boolean',
				'default'     => false,
				'context'     => array( 'view', 'edit' ),
			),
		);

		$schema['properties'] = array_merge( (array) $schema['properties'], $course_properties );

		return $schema;

	}

	/**
	 * Prepare a single object output for response.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param LLMS_Course     $course  Course object.
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array
	 */
	protected function prepare_object_for_response( $course, $request ) {

		$data = parent::prepare_object_for_response( $course, $request );

		// Catalog visibility.
		$data['catalog_visibility'] = $course->get_product()->get_catalog_visibility();

		// Categories.
		$data['categories'] = $course->get_categories(
			array(
				'fields' => 'ids',
			)
		);

		// Tags.
		$data['tags'] = $course->get_tags(
			array(
				'fields' => 'ids',
			)
		);

		// Difficulties.
		$difficulties         = $course->get_difficulty( 'term_id' );
		$difficulties         = empty( $difficulties ) ? array() : array( $difficulties );
		$data['difficulties'] = $difficulties;

		// Tracks.
		$data['tracks'] = $course->get_tracks(
			array(
				'fields' => 'ids',
			)
		);

		// Instructors.
		$instructors         = $course->get_instructors();
		$instructors         = empty( $instructors ) ? array() : wp_list_pluck( $instructors, 'id' );
		$data['instructors'] = $instructors;

		// Audio Embed.
		$data['audio_embed'] = $course->get( 'audio_embed' );

		// Video Embed.
		$data['video_embed'] = $course->get( 'video_embed' );

		// Video tile.
		$data['video_tile'] = 'yes' === $course->get( 'tile_featured_video' );

		// Capacity.
		$data['capacity_enabled'] = 'yes' === $course->get( 'enable_capacity' );

		$data['capacity_limit']   = $course->get( 'capacity' );
		$data['capacity_message'] = array(
			'raw'      => $course->get( 'capacity_message', $raw = true ),
			'rendered' => do_shortcode( $course->get( 'capacity_message' ) ),
		);

		// Prerequisite.
		$data['prerequisite'] = (int) $course->get_prerequisite_id();

		// Prerequisite track.
		$data['prerequisite_track'] = (int) $course->get_prerequisite_id( 'course_track' );

		// Length.
		$data['length'] = array(
			'raw'      => $course->get( 'length', $raw = true ),
			'rendered' => do_shortcode( $course->get( 'length' ) ),
		);

		// Restricted message.
		$data['restricted_message'] = array(
			'raw'      => $course->get( 'content_restricted_message', $raw = true ),
			'rendered' => do_shortcode( $course->get( 'content_restricted_message' ) ),
		);

		// Access open/closed.
		$data['access_opens_date']  = $course->get_date( 'start_date', 'Y-m-d H:i:s' );
		$data['access_closes_date'] = $course->get_date( 'end_date', 'Y-m-d H:i:s' );

		$data['access_opens_message'] = array(
			'raw'      => $course->get( 'course_opens_message', $raw = true ),
			'rendered' => do_shortcode( $course->get( 'course_opens_message' ) ),
		);

		$data['access_closes_message'] = array(
			'raw'      => $course->get( 'course_closed_message', $raw = true ),
			'rendered' => do_shortcode( $course->get( 'course_closed_message' ) ),
		);

		// Enrollment open/closed.
		$data['enrollment_opens_date']  = $course->get_date( 'enrollment_start_date', 'Y-m-d H:i:s' );
		$data['enrollment_closes_date'] = $course->get_date( 'enrollment_end_date', 'Y-m-d H:i:s' );

		$data['enrollment_opens_message'] = array(
			'raw'      => $course->get( 'enrollment_opens_message', $raw = true ),
			'rendered' => do_shortcode( $course->get( 'enrollment_opens_message' ) ),
		);

		$data['enrollment_closes_message'] = array(
			'raw'      => $course->get( 'enrollment_closed_message', $raw = true ),
			'rendered' => do_shortcode( $course->get( 'enrollment_closed_message' ) ),
		);

		// Sales page page type.
		$data['sales_page_page_type'] = $course->get( 'sales_page_content_type' );

		// Sales page id/url.
		if ( 'page' === $data['sales_page_page_type'] ) {
			$data['sales_page_page_id'] = $course->get( 'sales_page_content_page_id' );
		} elseif ( 'url' === $data['sales_page_page_type'] ) {
			$data['sales_page_page_url'] = $course->get( 'sales_page_content_url' );
		}

		return $data;

	}

	/**
	 * Prepares a single post for create or update.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request  Request object.
	 * @return array|WP_Error Array of llms post args or WP_Error.
	 */
	protected function prepare_item_for_database( $request ) {

		$prepared_item = parent::prepare_item_for_database( $request );
		$schema        = $this->get_item_schema();

		// Course Audio embed URL.
		if ( ! empty( $schema['properties']['audio_embed'] ) && isset( $request['audio_embed'] ) ) {
			$prepared_item['audio_embed'] = $request['audio_embed'];
		}

		// Course Video embed URL.
		if ( ! empty( $schema['properties']['video_embed'] ) && isset( $request['video_embed'] ) ) {
			$prepared_item['video_embed'] = $request['video_embed'];
		}

		// Video tile.
		if ( ! empty( $schema['properties']['video_tile'] ) && isset( $request['video_tile'] ) ) {
			$prepared_item['tile_featured_video'] = empty( $request['video_tile'] ) ? 'no' : 'yes';
		}

		// Capacity enabled.
		if ( ! empty( $schema['properties']['capacity_enabled'] ) && isset( $request['capacity_enabled'] ) ) {
			$prepared_item['enable_capacity'] = empty( $request['capacity_enabled'] ) ? 'no' : 'yes';
		}

		// Capacity message.
		if ( ! empty( $schema['properties']['capacity_message'] ) && isset( $request['capacity_message'] ) ) {
			if ( is_string( $request['capacity_message'] ) ) {
				$prepared_item['capacity_message'] = $request['capacity_message'];
			} elseif ( isset( $request['capacity_message']['raw'] ) ) {
				$prepared_item['capacity_message'] = $request['capacity_message']['raw'];
			}
		}

		// Capacity limit.
		if ( ! empty( $schema['properties']['capacity_limit'] ) && isset( $request['capacity_limit'] ) ) {
			$prepared_item['capacity'] = $request['capacity_limit'];
		}

		// Restricted message.
		if ( ! empty( $schema['properties']['restricted_message'] ) && isset( $request['restricted_message'] ) ) {
			if ( is_string( $request['restricted_message'] ) ) {
				$prepared_item['content_restricted_message'] = $request['restricted_message'];
			} elseif ( isset( $request['restricted_message']['raw'] ) ) {
				$prepared_item['content_restricted_message'] = $request['restricted_message']['raw'];
			}
		}

		// Length.
		if ( ! empty( $schema['properties']['length'] ) && isset( $request['length'] ) ) {
			if ( is_string( $request['length'] ) ) {
				$prepared_item['length'] = $request['length'];
			} elseif ( isset( $request['length']['raw'] ) ) {
				$prepared_item['length'] = $request['length']['raw'];
			}
		}

		// Access dates.
		if ( ! empty( $schema['properties']['access_opens_date'] ) && isset( $request['access_opens_date'] ) ) {
			$access_opens_date           = rest_parse_date( $request['access_opens_date'] );
			$prepared_item['start_date'] = date_i18n( 'Y-m-d H:i:s', $access_opens_date );
		}

		if ( ! empty( $schema['properties']['access_closes_date'] ) && isset( $request['access_closes_date'] ) ) {
			$access_closes_date        = rest_parse_date( $request['access_closes_date'] );
			$prepared_item['end_date'] = date_i18n( 'Y-m-d H:i:s', $access_closes_date );
		}

		// Needed until the following will be implemented: https://github.com/gocodebox/lifterlms/issues/908.
		if ( ! empty( $prepared_item['start_date'] ) || ! empty( $prepared_item['end_date'] ) ) {
			$prepared_item['time_period'] = 'yes';
		} else {
			$prepared_item['time_period'] = 'no';
		}

		// Enrollment dates.
		if ( ! empty( $schema['properties']['enrollment_opens_date'] ) && isset( $request['enrollment_opens_date'] ) ) {
			$enrollment_opens_date                  = rest_parse_date( $request['enrollment_opens_date'] );
			$prepared_item['enrollment_start_date'] = date_i18n( 'Y-m-d H:i:s', $enrollment_opens_date );
		}

		if ( ! empty( $schema['properties']['enrollment_closes_date'] ) && isset( $request['enrollment_closes_date'] ) ) {
			$enrollment_closes_date               = rest_parse_date( $request['enrollment_closes_date'] );
			$prepared_item['enrollment_end_date'] = date_i18n( 'Y-m-d H:i:s', $enrollment_closes_date );
		}

		// Needed until the following will be implemented: https://github.com/gocodebox/lifterlms/issues/908.
		if ( ! empty( $prepared_item['enrollment_start_date'] ) || ! empty( $prepared_item['enrollment_end_date'] ) ) {
			$prepared_item['enrollment_period'] = 'yes';
		} else {
			$prepared_item['enrollment_period'] = 'no';
		}

		// Sales page.
		if ( ! empty( $schema['properties']['sales_page_page_type'] ) && isset( $request['sales_page_page_type'] ) ) {
			$prepared_item['sales_page_content_type'] = $request['sales_page_page_type'];
		}

		if ( ! empty( $schema['properties']['sales_page_page_id'] ) && isset( $request['sales_page_page_id'] ) ) {
			$sales_page = get_post( $request['sales_page_page_id'] );
			if ( $sales_page && is_a( $sales_page, 'WP_Post' ) ) {
				$prepared_item['sales_page_content_page_id'] = $request['sales_page_page_id']; // maybe allow only published pages?
			} else {
				$prepared_item['sales_page_content_page_id'] = 0;
			}
		}

		if ( ! empty( $schema['properties']['sales_page_page_url'] ) && isset( $request['sales_page_page_url'] ) ) {
			$prepared_item['sales_page_content_url'] = $request['sales_page_page_url'];
		}

		return $prepared_item;

	}

	/**
	 * Updates a single llms post.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param LLMS_Course     $course        LLMS_Course instance.
	 * @param WP_REST_Request $request       Full details about the request.
	 * @param array           $schema        The item schema.
	 * @param array           $prepared_item Array.
	 * @param bool            $creating      Optional. Whether we're in creation or update phase. Default true (create).
	 * @return bool|WP_Error True on success, WP_Error object otherwise.
	 */
	protected function update_additional_object_fields( $course, $request, $schema, $prepared_item, $creating = true ) {

		$error = new WP_Error();

		// Course catalog visibility.
		if ( ! empty( $schema['properties']['catalog_visibility'] ) && isset( $request['catalog_visibility'] ) ) {
			$course->get_product()->set_catalog_visibility( $request['catalog_visibility'] );
		}

		// Instructors.
		if ( ! empty( $schema['properties']['instructors'] ) && isset( $request['instructors'] ) ) {

			$instructors = array();

			foreach ( $request['instructors'] as $instructor_id ) {
				$user_data = get_userdata( $instructor_id );
				if ( ! empty( $user_data ) ) {
					$instructors[] = array(
						'id'   => $instructor_id,
						'name' => $user_data->display_name,
					);
				}
			}

			$course->set_instructors( $instructors );
		}

		$to_set = array();

		// Prerequisite.
		if ( ! empty( $schema['properties']['prerequisite'] ) && isset( $request['prerequisite'] ) ) {
			// check if course exists.
			$prerequisite = llms_get_post( $request['prerequisite'] );
			if ( is_a( $prerequisite, 'LLMS_Course' ) ) {
				$to_set['prerequisite'] = $request['prerequisite'];
			}
		}

		// Prerequisite track.
		if ( ! empty( $schema['properties']['prerequisite_track'] ) && isset( $request['prerequisite_track'] ) ) {
			// check if the track exists.
			$track = new LLMS_Track( $request['prerequisite_track'] );
			if ( $track->term ) {
				$to_set['prerequisite_track'] = $request['prerequisite_track'];
			}
		}

		// Needed until the following will be implemented: https://github.com/gocodebox/lifterlms/issues/908.
		if ( ! empty( $to_set['prerequisite'] ) || ! empty( $to_set['prerequisite_track'] ) ) {
			$to_set['has_prerequisite'] = 'yes';
		} else {
			$to_set['has_prerequisite'] = 'no';
		}

		/**
		 * The following properties have a default value that contains a placeholder ({{course_id}}) that can be "expanded" only
		 * after the course has been created.
		 */
		// Access opens/closes messages.
		if ( ! empty( $schema['properties']['access_opens_message'] ) && isset( $request['access_opens_message'] ) ) {
			if ( is_string( $request['access_opens_message'] ) ) {
				$to_set['course_opens_message'] = $request['access_opens_message'];
			} elseif ( isset( $request['access_opens_message']['raw'] ) ) {
				$to_set['course_opens_message'] = $request['access_opens_message']['raw'];
			}
		}

		if ( ! empty( $schema['properties']['access_closes_message'] ) && isset( $request['access_closes_message'] ) ) {
			if ( is_string( $request['access_closes_message'] ) ) {
				$to_set['course_closed_message'] = $request['access_closes_message'];
			} elseif ( isset( $request['access_closes_message']['raw'] ) ) {
				$to_set['course_closed_message'] = $request['access_closes_message']['raw'];
			}
		}

		// Enrolmments opens/closes messages.
		if ( ! empty( $schema['properties']['enrollment_opens_message'] ) && isset( $request['enrollment_opens_message'] ) ) {
			if ( is_string( $request['enrollment_opens_message'] ) ) {
				$to_set['enrollment_opens_message'] = $request['enrollment_opens_message'];
			} elseif ( isset( $request['enrollment_opens_message']['raw'] ) ) {
				$to_set['enrollment_opens_message'] = $request['enrollment_opens_message']['raw'];
			}
		}

		if ( ! empty( $schema['properties']['enrollment_closes_message'] ) && isset( $request['enrollment_closes_message'] ) ) {
			if ( is_string( $request['enrollment_closes_message'] ) ) {
				$to_set['enrollment_closed_message'] = $request['enrollment_closes_message'];
			} elseif ( isset( $request['enrollment_closes_message']['raw'] ) ) {
				$to_set['enrollment_closed_message'] = $request['enrollment_closes_message']['raw'];
			}
		}

		// Are we creating a course?
		// If so, replace the placeholder with the actual course id.
		if ( $creating ) {

			$_to_expand_props = array(
				'course_opens_message',
				'course_closed_message',
				'enrollment_opens_message',
				'enrollment_closed_message',
			);

			$course_id = $course->get( 'id' );

			foreach ( $_to_expand_props as $prop ) {
				if ( ! empty( $to_set[ $prop ] ) ) {
					$to_set[ $prop ] = str_replace( '{{course_id}}', $course_id, $to_set[ $prop ] );
				}
			}
		}

		// Set bulk.
		if ( ! empty( $to_set ) ) {
			$update = $course->set_bulk( $to_set );
			if ( is_wp_error( $update ) ) {
				$error = $update;
			}
		}

		return $error->has_errors() ? $error : true;

	}

	/**
	 * Maps a taxonomy name to the relative rest base
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param object $taxonomy The taxonomy object.
	 * @return string The taxonomy rest base.
	 */
	protected function get_taxonomy_rest_base( $taxonomy ) {

		$base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;

		$taxonomy_base_map = array(
			'course_cat'        => 'categories',
			'course_difficulty' => 'difficulties',
			'course_tag'        => 'tags',
			'course_track'      => 'tracks',
		);

		return isset( $taxonomy_base_map[ $base ] ) ? $taxonomy_base_map[ $base ] : $base;

	}

	/**
	 * Get action/filters to be removed before preparing the item for response.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param LLMS_Course $course Course object.
	 * @return array Array of action/filters to be removed for response.
	 */
	protected function get_filters_to_be_removed_for_response( $course ) {

		if ( ! llms_blocks_is_post_migrated( $course->get( 'id' ) ) ) {
			return array();
		}

		return array(
			// hook => [callback, priority].
			'lifterlms_single_course_after_summary' => array(
				// Course Information.
				array(
					'callback' => 'lifterlms_template_single_meta_wrapper_start',
					'priority' => 5,
				),
				array(
					'callback' => 'lifterlms_template_single_length',
					'priority' => 10,
				),
				array(
					'callback' => 'lifterlms_template_single_difficulty',
					'priority' => 20,
				),
				array(
					'callback' => 'lifterlms_template_single_course_tracks',
					'priority' => 25,
				),
				array(
					'callback' => 'lifterlms_template_single_course_categories',
					'priority' => 30,
				),
				array(
					'callback' => 'lifterlms_template_single_course_tags',
					'priority' => 35,
				),
				array(
					'callback' => 'lifterlms_template_single_meta_wrapper_end',
					'priority' => 50,
				),
				// Course Progress.
				array(
					'callback' => 'lifterlms_template_single_course_progress',
					'priority' => 60,
				),
				// Course Syllabus.
				array(
					'callback' => 'lifterlms_template_single_syllabus',
					'priority' => 90,
				),
				// Instructors.
				array(
					'callback' => 'lifterlms_template_course_author',
					'priority' => 40,
				),
				// Pricing Table.
				array(
					'callback' => 'lifterlms_template_pricing_table',
					'priority' => 60,
				),
			),
		);
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param LLMS_Course $course  LLMS Course.
	 * @return array Links for the given object.
	 */
	protected function prepare_links( $course ) {
		$links     = parent::prepare_links( $course );
		$course_id = $course->get( 'id' );

		$course_links = array();

		// Access plans.
		$course_links['access_plans'] = array(
			'href' => add_query_arg(
				'post',
				$course_id,
				rest_url( sprintf( '%s/%s', 'llms/v1', 'access-plans' ) )
			),
		);

		// Enrollments.
		$course_links['enrollments'] = array(
			'href' => rest_url( sprintf( '/%s/%s/%d/%s', $this->namespace, $this->rest_base, $course_id, 'enrollments' ) ),
		);

		// Insturctors.
		$course_links['instructors'] = array(
			'href' => add_query_arg(
				'post',
				$course_id,
				rest_url( sprintf( '%s/%s', 'llms/v1', 'instructors' ) )
			),
		);

		// Prerequisite.
		$prerequisite = $course->get_prerequisite_id();
		if ( ! empty( $prerequisite ) ) {
			$course_links['prerequisites'][] = array(
				'type' => $this->post_type,
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $prerequisite ) ),
			);
		}

		// Prerequisite track.
		$prerequisite_track = $course->get_prerequisite_id( 'course_track' );
		if ( ! empty( $prerequisite_track ) ) {
			$course_links['prerequisites'][] = array(
				'type' => 'track',
				'href' => rest_url( sprintf( 'wp/v2/%s/%d', 'course_track', $prerequisite_track ) ),
			);
		}

		// Students.
		$course_links['students'] = array(
			'href' => add_query_arg(
				'enrolled_in',
				$course_id,
				rest_url( sprintf( '%s/%s', 'llms/v1', 'students' ) )
			),
		);

		return array_merge( $links, $course_links );
	}

	/**
	 * Retrieves the query params for the enrollments objects collection.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array Collection parameters.
	 */
	public function get_enrollments_collection_params() {
		$query_params = $this->enrollments_controller->get_collection_params();

		unset( $query_params['post'] );

		$query_params['student'] = array(
			'description'       => __( 'Limit results to a specific student or a list of students. Accepts a single student id or a comma separated list of student ids.', 'lifterlms' ),
			'type'              => 'string',
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $query_params;
	}

	/**
	 * Retrieves the query params for the sections objects collection.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array Collection parameters.
	 */
	public function get_course_content_collection_params() {

		$query_params = $this->sections_controller->get_collection_params();

		$query_params['orderby']['enum']    = array(
			'order',
			'id',
			'title',
		);
		$query_params['orderby']['default'] = 'order';

		unset( $query_params['parent'] );

		return $query_params;

	}

	/**
	 * Get a collection of content items (sections).
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_course_content_items( $request ) {

		$this->sections_controller->set_parent_id( $request['id'] );
		$result = $this->sections_controller->get_items( $request );

		// Specs require 404 when no course's sections are found.
		if ( ! is_wp_error( $result ) && empty( $result->data ) ) {
			return llms_rest_not_found_error();
		}

		return $result;

	}

}
