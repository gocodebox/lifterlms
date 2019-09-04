<?php
/**
 * REST Resource Controller for Students.
 *
 * @package  LifterLMS_REST/Classes/Controllers
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Students_Controller class..
 *
 * @since 1.0.0-beta.1
 */
class LLMS_REST_Students_Controller extends LLMS_REST_Users_Controller {

	/**
	 * Resource ID or Name.
	 *
	 * @var string
	 */
	protected $resource_name = 'student';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'students';

	/**
	 * Temporary array of prepared query args used to filter WP_User_Query
	 * when `enrolled_in` and `enrolled_not_in` args are present on the request.
	 *
	 * @var array
	 */
	private $prepared_query_args = array();

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

		return current_user_can( 'view_students', $item_id );

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

		if ( ! current_user_can( 'create_students' ) ) {
			return llms_rest_authorization_required_error( __( 'You are not allowed to create new students.', 'lifterlms' ) );
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

		if ( ! current_user_can( 'delete_students', $request['id'] ) ) {
			return llms_rest_authorization_required_error( __( 'You are not allowed to delete this student.', 'lifterlms' ) );
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

		// $params['roles']['default'] = 'student';

		$params['enrolled_in'] = array(
			'description' => __( 'Retrieve only students enrolled in the specified course(s) and/or membership(s). Accepts a single WP Post ID or a comma separated list of IDs.', 'lifterlms' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
		);

		$params['enrolled_not_in'] = array(
			'description' => __( 'Retrieve only students not enrolled in the specified course(s) and/or membership(s). Accepts a single WP Post ID or a comma separated list of IDs.', 'lifterlms' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
		);

		return $params;

	}

	/**
	 * Get the item schema.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function get_item_schema() {

		$schema                                   = parent::get_item_schema();
		$schema['properties']['roles']['default'] = array( 'student' );

		return $schema;

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
			return llms_rest_authorization_required_error( __( 'You are not allowed to view this student.', 'lifterlms' ) );
		}

		return true;

	}

	/**
	 * Determine if current user has permission to list users.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return true|WP_Error
	 */
	public function get_items_permissions_check( $request ) {

		if ( ! empty( $request['roles'] ) && ! current_user_can( 'view_others_students' ) ) {
			return llms_rest_authorization_required_error( __( 'You are not allowed to filter students by role.', 'lifterlms' ) );
		}

		if ( ! current_user_can( 'view_students' ) ) {
			return llms_rest_authorization_required_error( __( 'You are not allowed to list students.', 'lifterlms' ) );
		}

		return true;

	}

	/**
	 * Get object.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id Object ID.
	 * @return LLMS_Student|WP_Error
	 */
	protected function get_object( $id ) {

		$student = llms_get_student( $id );
		return $student ? $student : llms_rest_not_found_error();

	}

	/**
	 * Retrieve a query object based on arguments from a `get_items()` (collection) request.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array           $prepared Array of collection arguments.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_User_Query
	 */
	protected function get_objects_query( $prepared, $request ) {

		$remove = false;
		if ( ! empty( $prepared['enrolled_in'] ) || ! empty( $prepared['enrolled_not_in'] ) ) {

			$this->prepared_query_args = $prepared;
			add_action( 'pre_user_query', array( $this, 'get_objects_query_pre' ) );
			$remove = true;

		}

		$query = parent::get_objects_query( $prepared, $request );

		if ( $remove ) {

			$this->prepared_query_args = array();

			remove_action( 'pre_user_query', array( $this, 'get_objects_query_pre' ) );
		}

		return $query;

	}

	/**
	 * Callback for WP_User_Query "pre_user_query" action.
	 *
	 * Adds select fields and a having clause to check against `enrolled_in` and `enrolled_not_in` collection query args.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @link https://developer.wordpress.org/reference/hooks/pre_user_query/
	 *
	 * @param WP_User_Query $query Query object.
	 * @return void
	 */
	public function get_objects_query_pre( $query ) {

		$query->query_where .= ' Having 1 ';

		if ( ! empty( $this->prepared_query_args['enrolled_in'] ) ) {
			foreach ( $this->prepared_query_args['enrolled_in'] as $post_id ) {
				$post_id              = absint( $post_id );
				$query->query_fields .= ", {$this->get_objects_query_status_subquery( $post_id )}";
				$query->query_where  .= " AND p_{$post_id}_stat = 'enrolled'";
			}
		}

		if ( ! empty( $this->prepared_query_args['enrolled_not_in'] ) ) {
			foreach ( $this->prepared_query_args['enrolled_not_in'] as $post_id ) {
				$post_id              = absint( $post_id );
				$query->query_fields .= ", {$this->get_objects_query_status_subquery( $post_id )}";
				$query->query_where  .= " AND (  p_{$post_id}_stat IS NULL OR  p_{$post_id}_stat != 'enrolled' )";
			}
		}

	}

	/**
	 * Generates a subquery to check a user's enrollment status for a given course or membership.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $post_id Course or membership id.
	 * @return string
	 */
	private function get_objects_query_status_subquery( $post_id ) {

		global $wpdb;

		return "(
			SELECT meta_value
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE user_id = {$wpdb->users}.ID
			  AND post_id = {$post_id}
			  AND meta_key = '_status'
			ORDER BY updated_date DESC
			LIMIT 1
		) AS p_{$post_id}_stat";

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

		$links = parent::prepare_links( $object );

		$links['enrollments'] = array(
			'href' => sprintf( '%s/enrollments', $links['self']['href'] ),
		);
		$links['progress']    = array(
			'href' => sprintf( '%s/progress', $links['self']['href'] ),
		);

		return $links;

	}

	/**
	 * Determine if current user has permission to update a user.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return true|WP_Error
	 */
	public function update_item_permissions_check( $request ) {

		if ( get_current_user_id() === $request['id'] ) {
			return true;
		}

		if ( ! current_user_can( 'edit_students', $request['id'] ) ) {
			return llms_rest_authorization_required_error( __( 'You are not allowed to edit this student.', 'lifterlms' ) );
		}

		return $this->check_roles_permissions( $request );

	}

}
