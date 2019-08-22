<?php
/**
 * REST Sections Controller Class
 *
 * @package LifterLMS_REST/Classes/Controllers
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;


/**
 * LLMS_REST_Sections_Controller
 *
 * @since 1.0.0-beta.1
 */
class LLMS_REST_Sections_Controller extends LLMS_REST_Posts_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'sections';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'section';

	/**
	 * Parent id.
	 *
	 * @var int
	 */
	protected $parent_id;

	/**
	 * Schema properties available for ordering the collection.
	 *
	 * @var string[]
	 */
	protected $orderby_properties = array(
		'id',
		'title',
		'date_created',
		'date_updated',
		'order',
	);

	/**
	 * Lessons controller class.
	 *
	 * @var string
	 */
	protected $content_controller_class;

	/**
	 * Lessons controller.
	 *
	 * @var LLMS_REST_Lessons_Controller
	 */
	protected $content_controller;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param string $content_controller_class Optional. The class name of the content controller. Default 'LLMS_REST_Lessons_Controller'.
	 */
	public function __construct( $content_controller_class = 'LLMS_REST_Lessons_Controller' ) {

		$this->collection_params        = $this->build_collection_params();
		$this->content_controller_class = $content_controller_class;

		if ( $this->content_controller_class && class_exists( $this->content_controller_class ) ) {
			$this->content_controller = new $this->content_controller_class();
			$this->content_controller->set_collection_params( $this->get_content_collection_params() );
		}

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

		if ( isset( $this->content_controller ) ) {
			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base . '/(?P<id>[\d]+)/content',
				array(
					'args'   => array(
						'id' => array(
							// translators: %1$s the post type name.
							'description' => sprintf( __( 'Unique %1$s Identifier. The WordPress Post ID', 'lifterlms' ), $this->post_type ),
							'type'        => 'integer',
						),
					),
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_content_items' ),
						'permission_callback' => array( $this->content_controller, 'get_items_permissions_check' ),
						'args'                => $this->content_controller->get_collection_params(),
					),
					'schema' => array( $this->content_controller, 'get_public_item_schema' ),
				)
			);
		}
	}

	/**
	 * Retrieves an array of arguments for the delete endpoint.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array Delete endpoint arguments.
	 */
	public function get_delete_item_args() {
		return array();
	}

	/**
	 * Whether the delete should be forced.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool True if the delete should be forced, false otherwise.
	 */
	protected function is_delete_forced( $request ) {
		return true;
	}

	/**
	 * Whether the trash is supported.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return bool True if the trash is supported, false otherwise.
	 */
	protected function is_trash_supported() {
		return false;
	}

	/**
	 * Set parent id.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $parent_id Course parent id.
	 * @return void
	 */
	public function set_parent_id( $parent_id ) {
		$this->parent_id = $parent_id;
	}

	/**
	 * Get parent id.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return int|null Course parent id. Null if not set.
	 */
	public function get_parent_id() {
		return isset( $this->parent_id ) ? $this->parent_id : null;
	}


	/**
	 * Get object.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id Object ID.
	 * @return LLMS_Section|WP_Error
	 */
	protected function get_object( $id ) {
		$section = llms_get_post( $id );
		return $section && is_a( $section, 'LLMS_Section' ) ? $section : llms_rest_not_found_error();
	}

	/**
	 * Get an LLMS_Section
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $section_args Section args.
	 * @return LLMS_Post_Model|WP_Error
	 */
	protected function create_llms_post( $section_args ) {
		$section = new LLMS_Section( 'new', $section_args );
		return $section && is_a( $section, 'LLMS_Section' ) ? $section : llms_rest_not_found_error();
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

		$schema = $this->get_item_schema();

		// LLMS Section parent id.
		if ( ! empty( $schema['properties']['parent_id'] ) && isset( $request['parent_id'] ) ) {

			$parent_course = llms_get_post( $request['parent_id'] );

			if ( ! $parent_course || ! is_a( $parent_course, 'LLMS_Course' ) ) {
				return llms_rest_bad_request_error( __( 'Invalid parent_id param. It must be a valid Course ID.', 'lifterlms' ) );
			}

			$prepared_item['parent_course'] = $request['parent_id'];
		}

		// LLMS Section order.
		if ( ! empty( $schema['properties']['order'] ) && isset( $request['order'] ) ) {

			// order must be > 0. It's sanitized as absint so it cannot come as negative value.
			if ( 0 === $request['order'] ) {
				return llms_rest_bad_request_error( __( 'Invalid order param. It must be greater than 0.', 'lifterlms' ) );
			}

			$prepared_item['order'] = $request['order'];
		}

		return $prepared_item;

	}

	/**
	 * Get the Section's schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function get_item_schema() {

		$schema = parent::get_item_schema();

		// Section's title.
		$schema['properties']['title']['description'] = __( 'Section Title', 'lifterlms' );

		// Section's parent id.
		$schema['properties']['parent_id'] = array(
			'description' => __( 'WordPress post ID of the parent item. Must be a Course ID.', 'lifterlms' ),
			'type'        => 'integer',
			'context'     => array( 'view', 'edit' ),
			'arg_options' => array(
				'sanitize_callback' => 'absint',
			),
			'required'    => true,
		);

		// Section order.
		$schema['properties']['order'] = array(
			'description' => __( 'Order of the section within the course.', 'lifterlms' ),
			'type'        => 'integer',
			'default'     => 1,
			'context'     => array( 'view', 'edit' ),
			'arg_options' => array(
				'sanitize_callback' => 'absint',
			),
			'required'    => true,
		);

		// remove unnecessary properties.
		$unnecessary_properties = array(
			'permalink',
			'slug',
			'content',
			'menu_order',
			'excerpt',
			'featured_media',
			'status',
			'password',
			'featured_media',
			'comment_status',
			'ping_status',
		);

		foreach ( $unnecessary_properties as $unnecessary_property ) {
			unset( $schema['properties'][ $unnecessary_property ] );
		}

		return $schema;

	}

	/**
	 * Retrieves the query params for the objects collection.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array The Enrollments collection parameters.
	 */
	public function get_collection_params() {
		return $this->collection_params;
	}

	/**
	 * Retrieves the query params for the objects collection.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $collection_params The Enrollments collection parameters to be set.
	 * @return void
	 */
	public function set_collection_params( $collection_params ) {
		$this->collection_params = $collection_params;
	}

	/**
	 * Retrieves the query params for the objects collection.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array Collection parameters.
	 */
	public function build_collection_params() {

		$query_params = parent::get_collection_params();

		$query_params['parent'] = array(
			'description'       => __( 'Filter sections by the parent post (course) ID.', 'lifterlms' ),
			'type'              => 'integer',
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $query_params;
	}

	/**
	 * Prepare a single object output for response.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param LLMS_Section    $section Section object.
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array
	 */
	protected function prepare_object_for_response( $section, $request ) {

		$data = parent::prepare_object_for_response( $section, $request );

		// Parent course.
		$data['parent_id'] = $section->get_parent_course();

		// Order.
		$data['order'] = $section->get( 'order' );

		return $data;

	}

	/**
	 * Prepare objects query.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return array
	 */
	protected function prepare_objects_query( $request ) {

		$query_args = parent::prepare_objects_query( $request );

		// Orderby 'order' requires a meta query.
		if ( isset( $query_args['orderby'] ) && 'order' === $query_args['orderby'] ) {
			$query_args = array_merge(
				$query_args,
				array(
					'meta_key' => '_llms_order',
					'orderby'  => 'meta_value_num',
				)
			);
		}

		if ( isset( $this->parent_id ) ) {
			$parent_id = $this->parent_id;
		} elseif ( ! empty( $request['parent'] ) && $request['parent'] > 1 ) {
			$parent_id = $request['parent'];
		}

		// Filter by parent.
		if ( ! empty( $parent_id ) ) {
			$query_args = array_merge(
				$query_args,
				array(
					'meta_query' => array(
						array(
							'key'     => '_llms_parent_course',
							'value'   => $parent_id,
							'compare' => '=',
						),
					),
				)
			);
		}

		return $query_args;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param LLMS_Section $section  LLMS Section.
	 * @return array Links for the given object.
	 */
	protected function prepare_links( $section ) {

		$links            = parent::prepare_links( $section );
		$parent_course_id = $section->get_parent_course();

		/**
		 * If the section has no course parent return earlier
		 */
		if ( ! $parent_course_id ) {
			return $links;

		}

		$parent_course = new LLMS_Course( $parent_course_id );
		if ( ! is_a( $parent_course, 'LLMS_Course' ) ) {
			return $links;
		}

		$section_id    = $section->get( 'id' );
		$section_links = array();

		// Parent (course).
		$section_links['parent'] = array(
			'type' => 'course',
			'href' => rest_url( sprintf( '/%s/%s/%d', 'llms/v1', 'courses', $parent_course_id ) ),
		);

		// Siblings.
		$section_links['siblings'] = array(
			'href' => add_query_arg(
				'parent',
				$parent_course_id,
				$links['collection']['href']
			),
		);

		// Next.
		$next_section = $section->get_next();
		if ( $next_section ) {
			$section_links['next'] = array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $next_section->get( 'id' ) ) ),
			);
		}

		// Previous.
		$previous_section = $section->get_previous();
		if ( $previous_section ) {
			$section_links['previous'] = array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $previous_section->get( 'id' ) ) ),
			);
		}

		return array_merge( $links, $section_links );
	}

	/**
	 * Checks if a Section can be read
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param LLMS_Section $section The Section oject.
	 * @return bool Whether the post can be read.
	 */
	protected function check_read_permission( $section ) {

		/**
		 * As of now, sections of password protected courses cannot be read
		 */
		if ( post_password_required( $section->get( 'parent_course' ) ) ) {
			return false;
		}

		return parent::check_read_permission( $section );

	}

	/**
	 * Retrieves the content controller.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return  LLMS_REST_Lessons_Controller|null
	 */
	public function get_content_controller() {
		return $this->content_controller;
	}

	/**
	 * Retrieves the query params for the lessons objects collection.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array Collection parameters.
	 */
	public function get_content_collection_params() {

		$query_params = $this->content_controller->get_collection_params();

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
	 * Get a collection of content items (lessons).
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_content_items( $request ) {

		$this->content_controller->set_parent_id( $request['id'] );
		$result = $this->content_controller->get_items( $request );

		// Specs require 404 when no section's lessons are found.
		if ( ! is_wp_error( $result ) && empty( $result->data ) ) {
			return llms_rest_not_found_error();
		}

		return $result;

	}

}
