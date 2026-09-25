<?php
/**
 * REST Awarded Certificates Controller
 *
 * @package LifterLMS_REST/Classes/Controllers
 *
 * @since 10.2.0
 * @version 10.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Awarded_Certificates_Controller class.
 *
 * Manages certificates awarded to students (the `llms_my_certificate` post type).
 *
 * Awarding goes through `LLMS_Engagement_Handler::handle_certificate()` — the same path
 * used by engagements and the admin "Award Certificate" flow — so merge codes are rendered,
 * the sequential ID is incremented, and notifications/webhooks fire.
 *
 * @since 10.2.0
 */
class LLMS_REST_Awarded_Certificates_Controller extends LLMS_REST_Posts_Controller {

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'llms_my_certificate';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'awarded-certificates';

	/**
	 * LLMS post class.
	 *
	 * @var string
	 */
	protected $llms_post_class = 'LLMS_User_Certificate';

	/**
	 * Whether search is allowed.
	 *
	 * @var boolean
	 */
	protected $is_searchable = false;

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
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_award_args(),
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
						'description' => __( 'Unique identifier for the awarded certificate. The WordPress Post ID.', 'lifterlms' ),
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
					'args'                => array(
						'force' => array(
							'description' => __( 'Bypass the trash and delete the awarded certificate permanently, revoking it and removing the student\'s earned record.', 'lifterlms' ),
							'type'        => 'boolean',
							'default'     => false,
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Retrieve arguments for awarding a certificate (POST to the collection).
	 *
	 * Public so the award ability can derive its input schema from the same definition.
	 *
	 * @since 10.2.0
	 *
	 * @return array
	 */
	public function get_award_args() {

		return array(
			'student_id'     => array(
				'description' => __( 'The WP_User ID of the student being awarded the certificate.', 'lifterlms' ),
				'type'        => 'integer',
				'required'    => true,
			),
			'certificate_id' => array(
				'description' => __( 'The WP_Post ID of the certificate template to award.', 'lifterlms' ),
				'type'        => 'integer',
				'required'    => true,
			),
			'related_id'     => array(
				'description' => __( 'The WP_Post ID of a related post (course, lesson, etc...) that triggered the award.', 'lifterlms' ),
				'type'        => 'integer',
				'default'     => 0,
			),
		);
	}

	/**
	 * Check if a given request has access to read items.
	 *
	 * @since 10.2.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {

		if ( ! current_user_can( 'view_students' ) ) {
			return llms_rest_authorization_required_error();
		}

		return true;
	}

	/**
	 * Checks if an awarded certificate can be read.
	 *
	 * @since 10.2.0
	 *
	 * @param LLMS_User_Certificate $object The awarded certificate object.
	 * @return bool Whether the awarded certificate can be read.
	 */
	protected function check_read_permission( $object ) {
		return current_user_can( 'view_students', $object->get_user_id() );
	}

	/**
	 * Check if a given request has access to award a certificate.
	 *
	 * @since 10.2.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function create_item_permissions_check( $request ) {

		if ( ! current_user_can( LLMS_Roles::MANAGE_EARNED_ENGAGEMENT_CAP ) ) {
			return llms_rest_authorization_required_error( __( 'Sorry, you are not allowed to award certificates.', 'lifterlms' ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access to delete (revoke) an awarded certificate.
	 *
	 * @since 10.2.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function delete_item_permissions_check( $request ) {

		if ( ! current_user_can( LLMS_Roles::MANAGE_EARNED_ENGAGEMENT_CAP ) ) {
			return llms_rest_authorization_required_error( __( 'Sorry, you are not allowed to revoke certificates.', 'lifterlms' ) );
		}

		return true;
	}

	/**
	 * Get object.
	 *
	 * `llms_get_post()` can't map the `llms_my_certificate` post type to its model
	 * (`LLMS_User_Certificate`), so the object is instantiated directly.
	 *
	 * @since 10.2.0
	 *
	 * @param int|WP_Post $id Object ID or already retrieved WP_Post.
	 * @return LLMS_User_Certificate|WP_Error
	 */
	protected function get_object( $id ) {

		$post = get_post( $id );
		if ( ! $post || $this->post_type !== $post->post_type ) {
			return llms_rest_not_found_error();
		}

		return new LLMS_User_Certificate( $post );
	}

	/**
	 * Award a certificate to a student.
	 *
	 * @since 10.2.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {

		$student_id     = absint( $request['student_id'] );
		$certificate_id = absint( $request['certificate_id'] );
		$related_id     = absint( $request['related_id'] );

		if ( ! get_user_by( 'ID', $student_id ) ) {
			return llms_rest_bad_request_error( __( 'The provided student_id is not a valid user.', 'lifterlms' ) );
		}

		if ( 'llms_certificate' !== get_post_type( $certificate_id ) ) {
			return llms_rest_bad_request_error( __( 'The provided certificate_id is not a valid certificate template.', 'lifterlms' ) );
		}

		$result = LLMS_Engagement_Handler::handle_certificate(
			array( $student_id, $certificate_id, $related_id ? $related_id : '', null )
		);

		$error = $this->get_first_error( $result );
		if ( $error ) {
			// Preserve the original error code/message but ensure an HTTP status is set.
			$error_data = (array) $error->get_error_data();
			if ( empty( $error_data['status'] ) ) {
				$error->add_data( array_merge( $error_data, array( 'status' => 400 ) ) );
			}
			return $error;
		}

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $result, $request );
		$response = rest_ensure_response( $response );
		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '/%1$s/%2$s/%3$d', $this->namespace, $this->rest_base, $result->get( 'id' ) ) ) );

		return $response;
	}

	/**
	 * Extract the first WP_Error from an engagement handler result.
	 *
	 * @since 10.2.0
	 *
	 * @param mixed $result Result from `LLMS_Engagement_Handler::handle_certificate()`.
	 * @return WP_Error|false
	 */
	protected function get_first_error( $result ) {

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( is_array( $result ) ) {
			foreach ( $result as $item ) {
				if ( is_wp_error( $item ) ) {
					return $item;
				}
			}
		}

		return false;
	}

	/**
	 * Deletes (revokes) a single awarded certificate.
	 *
	 * When force-deleting, revocation goes through `LLMS_User_Certificate::delete()` so the
	 * student's earned record (user postmeta) is removed and deletion hooks fire.
	 *
	 * @since 10.2.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {

		if ( ! $this->is_delete_forced( $request ) ) {
			return parent::delete_item( $request );
		}

		$object   = $this->get_object( (int) $request['id'] );
		$response = new WP_REST_Response();
		$response->set_status( 204 );

		if ( is_wp_error( $object ) ) {
			if ( in_array( 'llms_rest_not_found', $object->get_error_codes(), true ) ) {
				return $response;
			}

			return $object;
		}

		$object->delete();

		return $response;
	}

	/**
	 * Retrieves the query params for the objects collection.
	 *
	 * @since 10.2.0
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {

		$query_params = parent::get_collection_params();

		$query_params['student'] = array(
			'description' => __( 'Limit results to certificates awarded to a specific student. Accepts a WP_User ID.', 'lifterlms' ),
			'type'        => 'integer',
		);

		$query_params['template'] = array(
			'description' => __( 'Limit results to certificates awarded from a specific certificate template. Accepts a WP_Post ID.', 'lifterlms' ),
			'type'        => 'integer',
		);

		return $query_params;
	}

	/**
	 * Format query arguments to retrieve a collection of objects.
	 *
	 * @since 10.2.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array|WP_Error
	 */
	protected function prepare_collection_query_args( $request ) {

		$query_args = parent::prepare_collection_query_args( $request );
		if ( is_wp_error( $query_args ) ) {
			return $query_args;
		}

		if ( ! empty( $request['student'] ) ) {
			$query_args['author'] = absint( $request['student'] );
		}

		if ( ! empty( $request['template'] ) ) {
			$query_args['post_parent'] = absint( $request['template'] );
		}

		return $query_args;
	}

	/**
	 * Prepare a single object output for response.
	 *
	 * @since 10.2.0
	 *
	 * @param LLMS_User_Certificate $certificate Awarded certificate object.
	 * @param WP_REST_Request       $request     Full details about the request.
	 * @return array
	 */
	protected function prepare_object_for_response( $certificate, $request ) {

		$data = parent::prepare_object_for_response( $certificate, $request );

		$data['certificate_id'] = $certificate->get( 'parent' );
		$data['student_id']     = $certificate->get_user_id();
		$data['related_id']     = $certificate->get_related_post_id();
		$data['engagement_id']  = $certificate->get( 'engagement' );
		// `get_sequential_id()` returns the display-formatted (zero-padded) string; the schema declares an integer.
		$data['sequential_id']  = absint( $certificate->get( 'sequential_id' ) );
		$data['allow_sharing']  = $certificate->is_sharing_enabled();

		return $data;
	}

	/**
	 * Get the Awarded Certificate's schema, conforming to JSON Schema.
	 *
	 * @since 10.2.0
	 *
	 * @return array Item schema data.
	 */
	protected function get_item_schema_base() {

		$schema = parent::get_item_schema_base();

		// Awarded certificates don't expose these post props.
		unset(
			$schema['properties']['excerpt'],
			$schema['properties']['comment_status'],
			$schema['properties']['ping_status'],
			$schema['properties']['password'],
			$schema['properties']['menu_order']
		);

		$schema['properties']['title']['description'] = __( 'The awarded certificate title.', 'lifterlms' );

		$awarded_properties = array(
			'certificate_id' => array(
				'description' => __( 'The WP_Post ID of the certificate template the certificate was awarded from.', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'student_id'     => array(
				'description' => __( 'The WP_User ID of the student the certificate was awarded to.', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'related_id'     => array(
				'description' => __( 'The WP_Post ID of the related post (course, lesson, etc...) that triggered the award. 0 when there is no related post.', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'engagement_id'  => array(
				'description' => __( 'The WP_Post ID of the engagement that triggered the award. 0 when awarded manually.', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'sequential_id'  => array(
				'description' => __( 'The sequential certificate ID.', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'allow_sharing'  => array(
				'description' => __( 'Whether the student has enabled public sharing of the certificate.', 'lifterlms' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
		);

		$schema['properties'] = array_merge( $schema['properties'], $awarded_properties );

		return $schema;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 10.2.0
	 *
	 * @param LLMS_User_Certificate $object  Object data.
	 * @param WP_REST_Request       $request Request object.
	 * @return array Links for the given object.
	 */
	protected function prepare_links( $object, $request ) {

		$links = parent::prepare_links( $object, $request );

		unset( $links['content'] );

		$student_id = $object->get_user_id();
		if ( $student_id ) {
			$links['student'] = array(
				'href'       => rest_url( sprintf( '/%1$s/students/%2$d', $this->namespace, $student_id ) ),
				'embeddable' => true,
			);
		}

		$template_id = $object->get( 'parent' );
		if ( $template_id ) {
			$links['certificate'] = array(
				'href'       => rest_url( sprintf( '/%1$s/certificates/%2$d', $this->namespace, $template_id ) ),
				'embeddable' => true,
			);
		}

		return $links;
	}
}
