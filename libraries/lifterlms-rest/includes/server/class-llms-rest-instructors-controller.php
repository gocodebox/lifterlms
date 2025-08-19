<?php
/**
 * REST Resource Controller for Instructors.
 *
 * @package LifterLMS_REST/Classes/Controllers
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.27
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Instructors_Controller class.
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.13 Fixed authentication error messages referring to 'students' or 'users' rather than 'instructors'.
 * @since 1.0.0-beta.14 Update `prepare_links()` to accept a second parameter, `WP_REST_Request`.
 */
class LLMS_REST_Instructors_Controller extends LLMS_REST_Users_Controller {

	/**
	 * Resource ID or Name.
	 *
	 * @var string
	 */
	protected $resource_name = 'instructor';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'instructors';

	/**
	 * Determine if the current user can view the requested student.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $item_id WP_User id.
	 * @return bool
	 */
	protected function check_read_item_permissions( $item_id ) {

		if ( get_current_user_id() === $item_id ) {
			return true;
		}

		return current_user_can( 'list_users', $item_id );
	}

	/**
	 * Format query arguments to retrieve a collection of objects
	 *
	 * @since 1.0.2
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array|WP_Error
	 */
	protected function prepare_collection_query_args( $request ) {

		$query_args = parent::prepare_collection_query_args( $request );
		if ( is_wp_error( $query_args ) ) {
			return $query_args;
		}

		if ( empty( $request['roles'] ) ) {
			$query_args = array_merge(
				$query_args,
				array(
					'roles' => $this->get_item_schema_base()['properties']['roles']['default'],
				)
			);
		}

		return $query_args;
	}

	/**
	 * Determine if current user has permission to create a user.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return true|WP_Error
	 */
	public function create_item_permissions_check( $request ) {

		if ( ! current_user_can( 'create_users' ) ) {
			return llms_rest_authorization_required_error( __( 'You are not allowed to create new instructors.', 'lifterlms' ) );
		}

		return $this->check_roles_permissions( $request );
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

		if ( ! current_user_can( 'delete_users', $request['id'] ) ) {
			return llms_rest_authorization_required_error( __( 'You are not allowed to delete this instructor.', 'lifterlms' ) );
		}

		return true;
	}

	/**
	 * Retrieves the query params for the objects collection.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {

		$params = parent::get_collection_params();

		$params['post_in'] = array(
			'description' => __( 'Retrieve only instructors for the specified course(s) and/or membership(s). Accepts a single WP Post ID or a comma separated list of IDs.', 'lifterlms' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
		);

		$params['post_not_in'] = array(
			'description' => __( 'Exclude instructors who do not have permissions for the specified course(s) and/or membership(s). Accepts a single WP Post ID or a comma separated list of IDs.', 'lifterlms' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
		);

		return $params;
	}

	/**
	 * Determine if current user has permission to get a user.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return true|WP_Error
	 */
	public function get_item_permissions_check( $request ) {

		if ( ! $this->check_read_item_permissions( $request['id'] ) ) {
			return llms_rest_authorization_required_error( __( 'You are not allowed to view this instructor.', 'lifterlms' ) );
		}

		return true;
	}

	/**
	 * Get the item schema base.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return array
	 */
	public function get_item_schema_base() {

		$schema = parent::get_item_schema_base();

		$schema['properties']['roles']['default'] = array( 'instructor' );

		return $schema;
	}

	/**
	 * Determine if current user has permission to list users.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.13 Fixed authentication error message referring to 'students' rather than 'instructors'.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return true|WP_Error
	 */
	public function get_items_permissions_check( $request ) {

		if ( ! current_user_can( 'list_users' ) ) {
			return llms_rest_authorization_required_error( __( 'You are not allowed to list instructors.', 'lifterlms' ) );
		}

		return true;
	}

	/**
	 * Get object.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id Object ID.
	 * @return LLMS_Instructor|WP_Error
	 */
	protected function get_object( $id ) {

		$instructor = llms_get_instructor( $id );
		return $instructor ? $instructor : llms_rest_not_found_error();
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.14 Added the `$request` parameter.
	 *
	 * @param obj             $object  Item object.
	 * @param WP_REST_Request $request Request object.
	 * @return array
	 */
	protected function prepare_links( $object, $request ) {

		$links = parent::prepare_links( $object, $request );

		$links['content'] = array(
			'href' => sprintf( '%s/content', $links['self']['href'] ),
		);

		return $links;
	}

	/**
	 * Updates additional information not handled by WP Core insert/update user functions.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int             $object_id WP User id.
	 * @param array           $prepared  Prepared item data.
	 * @param WP_REST_Request $request   Request object.
	 * @return LLMS_Abstract_User_Data|WP_error
	 */
	protected function update_additional_data( $object_id, $prepared, $request ) {

		$object = parent::update_additional_data( $object_id, $prepared, $request );

		if ( is_wp_error( $object ) ) {
			return $object;
		}

		// Add a parent_id of the current user when creating an instructors_assistant.
		// @todo: this should actually be handled by a `parent_ids` create/update arg required when assistant is a supplied role.
		if ( get_current_user_id() !== $object_id && ! empty( $prepared['roles'] ) && in_array( 'instructors_assistant', $prepared['roles'], true ) ) {
			$object->add_parents( get_current_user_id() );
		}

		return $object;
	}

	/**
	 * Determine if current user has permission to update a user.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.13 Refer to the instructor role on the authorization error message rather than the generic 'user'.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return true|WP_Error
	 */
	public function update_item_permissions_check( $request ) {

		if ( get_current_user_id() === $request['id'] ) {
			return true;
		}

		if ( ! current_user_can( 'edit_users', $request['id'] ) ) {
			return llms_rest_authorization_required_error( __( 'You are not allowed to edit this instructor.', 'lifterlms' ) );
		}

		return $this->check_roles_permissions( $request );
	}
}
