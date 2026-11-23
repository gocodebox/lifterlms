<?php
/**
 * REST Resource Controller for Students.
 *
 * @package LifterLMS_REST/Classes/Controllers
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.27
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Students_Controller class.
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.7 Added `prepare_args_for_total_count_query()` method override.
 * @since 1.0.0-beta.12 Added item schema filter.
 *                      Added 'llms_rest_student_registered' action hook - fired after student's creation.
 * @since 1.0.0-beta.14 Update `prepare_links()` to accept a second parameter, `WP_REST_Request`.
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
	 * Get the item schema base.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return array
	 */
	public function get_item_schema_base() {

		$schema                                   = parent::get_item_schema_base();
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
	 * @since 1.0.0-beta.26 Don't autoload current user if a falsy user id is supplied.
	 *
	 * @param int $id Object ID.
	 * @return LLMS_Student|WP_Error
	 */
	protected function get_object( $id ) {

		$student = llms_get_student( $id, false );
		return $student ? $student : llms_rest_not_found_error();
	}

	/**
	 * Retrieve a query object based on arguments from a `get_items()` (collection) request.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array           $prepared Array of collection arguments.
	 * @param WP_REST_Request $request  Request object.
	 * @return WP_User_Query
	 */
	protected function get_objects_query( $prepared, $request ) {

		$prepared = $this->limit_query_to_readable_users( $prepared );

		$this->prepared_query_args = $prepared;

		// Runs before `get_objects_query_pre()`, which ends the WHERE clause with a HAVING clause.
		$limit = isset( $prepared['llms_rest_readable_post_ids'] );
		if ( $limit ) {
			add_action( 'pre_user_query', array( $this, 'limit_user_query_to_readable_users' ), 5 );
		}

		$remove = false;
		if ( ! empty( $prepared['enrolled_in'] ) || ! empty( $prepared['enrolled_not_in'] ) ) {

			add_action( 'pre_user_query', array( $this, 'get_objects_query_pre' ) );
			$remove = true;

		}

		$query = parent::get_objects_query( $prepared, $request );

		$this->prepared_query_args = array();

		if ( $limit ) {
			remove_action( 'pre_user_query', array( $this, 'limit_user_query_to_readable_users' ), 5 );
		}

		if ( $remove ) {
			remove_action( 'pre_user_query', array( $this, 'get_objects_query_pre' ) );
		}

		return $query;
	}

	/**
	 * Restrict collection query arguments to the users the current user can read.
	 *
	 * Pagination totals are computed from the collection query before the
	 * per-item read check runs. A caller who may read some students but not
	 * all of them would otherwise be refused every item while `X-WP-Total`
	 * still reports (and, combined with the `search` parameter, enumerates)
	 * the unreadable rows.
	 *
	 * This mirrors {@see LLMS_REST_Students_Controller::check_read_item_permissions()}:
	 * the scoped query matches the current user and the students they can
	 * read, so the pagination totals fall out of the constrained query. The
	 * scope is added to the query's SQL by
	 * {@see LLMS_REST_Students_Controller::limit_user_query_to_readable_users()}.
	 *
	 * @since 1.1.3
	 *
	 * @param array $prepared Collection query arguments.
	 * @return array
	 */
	protected function limit_query_to_readable_users( $prepared ) {

		// Callers who cannot read any student, or who can read all of them,
		// are already scoped consistently by the collection and per-item
		// permission checks.
		if ( ! current_user_can( 'view_students' ) || current_user_can( 'view_others_students' ) ) {
			return $prepared;
		}

		// Subclasses replace this read rule and scope their own collection.
		// The groups add-on's group members controller is one of them.
		if ( self::class !== static::class ) {
			return $prepared;
		}

		$prepared['llms_rest_readable_post_ids'] = array();

		$instructor = llms_get_instructor( get_current_user_id() );
		if ( $instructor ) {
			$prepared['llms_rest_readable_post_ids'] = array_map( 'absint', $instructor->get_posts( array( 'posts_per_page' => -1 ), 'ids' ) );
		}

		return $prepared;
	}

	/**
	 * Callback for WP_User_Query "pre_user_query" action.
	 *
	 * Limits the query to the current user and the students enrolled in one
	 * of the courses or memberships they instruct, as resolved by
	 * {@see LLMS_Instructor::has_student()}.
	 *
	 * @since 1.1.3
	 *
	 * @param WP_User_Query $query Query object.
	 * @return void
	 */
	public function limit_user_query_to_readable_users( $query ) {

		global $wpdb;

		$enrolled = '';
		$post_ids = $this->prepared_query_args['llms_rest_readable_post_ids'];
		if ( $post_ids ) {
			$post_ids = implode( ', ', array_map( 'absint', $post_ids ) );
			$enrolled = " OR {$wpdb->users}.ID IN (
				SELECT upm.user_id
				FROM {$wpdb->prefix}lifterlms_user_postmeta AS upm
				WHERE upm.meta_key = '_status'
				  AND upm.post_id IN ( {$post_ids} )
				  AND upm.meta_value = 'enrolled'
				  AND upm.meta_id = (
					  SELECT upm2.meta_id
					  FROM {$wpdb->prefix}lifterlms_user_postmeta AS upm2
					  WHERE upm2.user_id = upm.user_id
					    AND upm2.post_id = upm.post_id
					    AND upm2.meta_key = '_status'
					  ORDER BY upm2.updated_date DESC, upm2.meta_id DESC
					  LIMIT 1
				  )
			)";
		}

		$query->query_where .= $wpdb->prepare( " AND ( {$wpdb->users}.ID = %d{$enrolled} )", get_current_user_id() ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- IDs are cast to integers.
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

		if ( empty( $request['roles'] ) && empty( $request['search'] ) ) {
			$query_args = array_merge(
				$query_args,
				array(
					'roles' =>
						$this->get_item_schema_base()['properties']['roles']['default'],
				)
			);
		}

		return $query_args;
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
	 * Called right after a student is completely inserted (created/updated).
	 *
	 * @since 1.0.0-beta.12
	 *
	 * @param LLMS_Student    $student  Inserted or updated llms student.
	 * @param WP_REST_Request $request  Request object.
	 * @param array           $schema   The item schema.
	 * @param bool            $creating True when creating a post, false when updating.
	 */
	protected function object_completely_inserted( $student, $request, $schema, $creating ) {

		parent::object_completely_inserted( $student, $request, $schema, $creating );

		if ( $creating ) {
			/**
			 * Fires after a LifterLMS student has been created via the REST API.
			 *
			 * @since 1.0.0-beta.12
			 *
			 * @param LLMS_Student    $student Inserted or updated llms student.
			 * @param WP_REST_Request $request Request object.
			 * @param array           $schema  The item schema.
			 */
			do_action( 'llms_rest_student_registered', $this->get_object_id( $student ), $request, $schema );
		}
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.14 Added `$request` parameter.
	 *
	 * @param obj             $object  Item object.
	 * @param WP_REST_Request $request Request object.
	 * @return array
	 */
	protected function prepare_links( $object, $request ) {

		$links = parent::prepare_links( $object, $request );

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

		if ( is_wp_error( ( new WP_REST_Users_Controller() )->update_item_permissions_check( $request ) ) ) {
			return llms_rest_authorization_required_error( __( 'You are not allowed to edit this user.', 'lifterlms' ) );
		}

		if ( ! empty( $request['roles'] ) ) {
			return $this->check_roles_permissions( $request );
		}

		return true;
	}
}
