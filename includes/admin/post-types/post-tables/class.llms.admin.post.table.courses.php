<?php
/**
 * Add, Customize, and Manage LifterLMS Course
 *
 * @since    3.3.0
 * @version  [version]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Post_Table_Courses {

	/**
	 * Constructor
	 * @return  void
	 * @since    3.3.0
	 * @version  [version]
	 */
	public function __construct() {

		add_filter( 'post_row_actions', array( $this, 'add_links' ), 1, 2 );

		add_filter( 'manage_course_posts_columns', array( $this, 'add_columns' ), 10, 1 );
		add_action( 'manage_course_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );

		add_filter( 'bulk_actions-edit-course', array( $this, 'register_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-course', array( $this, 'handle_bulk_actions' ), 10, 3 );

		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

		add_filter( 'views_edit-course', array( $this, 'get_views' ), 10, 1 );

	}

	/**
	 * Add Custom Columns
	 * @param    array  $columns array of default columns
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function add_columns( $columns ) {

		$offset = array_search( 'author', array_keys( $columns ) );

		$add = array(
			'llms-instructors' => __( 'Instructors', 'lifterlms' ),
		);

		return array_slice( $columns, 0, $offset ) + $add + array_slice( $columns, $offset + 1 );

	}

	/**
	 * Add course builder edit link
	 * @param    array     $actions  existing actions
	 * @param    obj       $post     WP_Post object
	 * @since    [version]
	 * @version  [version]
	 */
	public function add_links( $actions, $post ) {

		if ( current_user_can( 'edit_course', $post->ID ) && post_type_supports( $post->post_type, 'llms-clone-post' ) ) {

			$url = add_query_arg( array(
				'page' => 'llms-course-builder',
				'course_id' => $post->ID,
			), admin_url( 'admin.php' ) );

			$actions = array_merge( array(
				'llms-builder' => '<a href="' . esc_url( $url ) . '">' . __( 'Builder', 'lifterlms' ) . '</a>',
			), $actions );

		}

		return $actions;

	}

	/**
	 * Create a string that can be used in a LIKE query for finding a student's id in the llms_instructors
	 * meta field on the usermeta table
	 * @param    int     $user_id  WP User ID
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	private function get_serialized_id( $user_id ) {
		$val = serialize( array(
			'id' => absint( $user_id ),
		) );
		return str_replace( array( 'a:1:{', '}' ), '', $val );
	}

	/**
	 * Ensure that the "Mine" view quick link at the top of the table displays the correct number
	 * Most of this is based on WordPress core functions found in wp-admin/includes/class-wp-posts-list-table.php
	 * @param    array     $views  array of view link HTML string
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_views( $views ) {

		$current_user_id = get_current_user_id();

		$exclude_states = get_post_stati( array(
			'show_in_admin_all_list' => false,
		) );

		global $wpdb;
		$count = intval( $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT( 1 )
			FROM $wpdb->posts AS p
			JOIN $wpdb->postmeta AS m
			  ON p.ID = m.post_id
			 AND m.meta_key = '_llms_instructors'
			 AND m.meta_value LIKE %s
			WHERE p.post_type = 'course'
			  AND p.post_status NOT IN ( '" . implode( "','", $exclude_states ) . "' )
		", '%' . $this->get_serialized_id( $current_user_id ) . '%' ) ) );

		$label = sprintf(
			_nx(
				'Mine <span class="count">(%s)</span>',
				'Mine <span class="count">(%s)</span>',
				$count,
				'posts'
			),
			number_format_i18n( $count )
		);

		$url = add_query_arg( array(
			'post_type' => 'course',
			'author' => $current_user_id,
		), 'edit.php' );

		$class = '';
		if ( isset( $_GET['author'] ) && ( $_GET['author'] == $current_user_id ) ) {
			$class = 'class="current"';
		}

		// if mine doesn't already exist in views, we need to add it after "All" manually
		// to preserve the user experience
		if ( ! isset( $views['mine'] ) ) {

			$offset = array_search( 'all', array_keys( $views ) );
			$add = array(
				'mine' => '',
			);
			$views = array_slice( $views, 0, $offset + 1 ) + $add + array_slice( $views, $offset + 1 );

		}

		$views['mine'] = sprintf( '<a href="%1$s"%2$s>%3$s</a>', esc_url( $url ), $class, $label );

		return $views;
	}

	/**
	 * Exports courses from the Bulk Actions menu on the courses post table
	 * @param    string     $redirect_to  url to redirect to upon export comletion (not used)
	 * @param    string     $doaction     action name called
	 * @param    array      $post_ids     selected post ids
	 * @return   void
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function handle_bulk_actions( $redirect_to, $doaction, $post_ids ) {

		// ensure it's our custom action
		if ( $doaction !== 'llms_export' ) {
			return $redirect_to;
		}

		$data = array(
			'_generator' => 'LifterLMS/BulkCourseExporter',
			'_source' => get_site_url(),
			'_version' => LLMS()->version,
			'courses' => array(),
		);

		foreach ( $post_ids as $post_id ) {

			$c = new LLMS_Course( $post_id );
			$data['courses'][] = $c->toArray();

		}

		$title = str_replace( ' ', '-', __( 'courses export', 'lifterlms' ) );
		$title = preg_replace( '/[^a-zA-Z0-9-]/', '', $title );

		$filename = apply_filters( 'llms_bulk_export_courses_filename', $title . '_' . current_time( 'Ymd' ), $this );

		header( 'Content-type: application/json' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '.json"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		echo json_encode( $data );

		die;

	}

	/**
	 * Manage content of custom columns
	 * @param    string  $column   column key/name
	 * @param    int     $post_id  WP Post ID of the coupon for the row
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function manage_columns( $column, $post_id ) {

		$course = llms_get_post( $post_id );

		switch ( $column ) {

			case 'llms-instructors':
				$instructors = $course->get_instructors();
				$htmls = array();
				foreach ( $instructors as $user ) {

					$url = add_query_arg( array(
						'post_type' => 'course',
						'author' => $user['id'],
					), 'edit.php' );

					$instructor = llms_get_instructor( $user['id'] );

					$htmls[] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $url ), $instructor->display_name );

				}
				echo implode( ', ', $htmls );
			break;

		}

	}

	/**
	 * Handle course queries for searching by llms_instructors rather than author
	 * @param    obj     $query  WP_Query
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function pre_get_posts( $query ) {

		if ( ! is_admin() ) {
			return;
		}

		if ( ! $query->is_main_query() ) {
			return;
		}

		// don't run duplicates
		if ( $query->get( 'llms_instructor_query' ) ) {
			return;
		}

		// var_dump( $query->query_vars );

		if ( isset( $query->query_vars['post_type'] ) && 'course' === $query->query_vars['post_type'] && ! empty( $query->query_vars['author'] ) ) {

			// get the query or a default to work with
			$meta_query = $query->get( 'meta_query' );
			if ( ! $meta_query ) {
				$meta_query = array();
			}

			// set an and relation for our filters
			// if other filters already exist, we'll ensure we obey them as well this way
			$meta_query['relation'] = 'AND';

			$meta_query[] = array(
				'compare' => 'LIKE',
				'key' => '_llms_instructors',
				'value' => $this->get_serialized_id( $query->query_vars['author'] ),
			);

			$query->set( 'meta_query', $meta_query );

			$query->set( 'llms_instructor_query', true );

			$query->set( 'author', '' );

		}

	}

	/**
	 * Register bulk actions
	 * @param    array     $actions  existing bulk actions
	 * @return   array
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	function register_bulk_actions( $actions ) {

		$actions['llms_export'] = __( 'Export', 'lifterlms' );
		return $actions;

	}

}

return new LLMS_Admin_Post_Table_Courses();
