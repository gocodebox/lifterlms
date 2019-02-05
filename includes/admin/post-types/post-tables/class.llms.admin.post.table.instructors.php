<?php
defined( 'ABSPATH' ) || exit;

/**
 * Post table stuff for courses and memberships who have custom "instructor" stuff
 * which replaces "Author"
 *
 * @since    3.13.0
 * @version  3.24.0
 */
class LLMS_Admin_Post_Table_Instructors {

	private $post_types = array(
		'course',
		'llms_membership',
	);

	/**
	 * Constructor
	 * @return  void
	 * @since    3.3.0
	 * @version  3.13.0
	 */
	public function __construct() {

		foreach ( $this->post_types as $post_type ) {
			add_filter( 'manage_' . $post_type . '_posts_columns', array( $this, 'add_columns' ), 10, 1 );
			add_action( 'manage_' . $post_type . '_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );
			add_filter( 'views_edit-' . $post_type, array( $this, 'get_views' ), 777, 1 );
		}

		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

	}

	/**
	 * Add Custom Columns
	 * @param    array  $columns array of default columns
	 * @return   array
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function add_columns( $columns ) {

		$offset = array_search( 'title', array_keys( $columns ) );

		$add = array(
			'llms-instructors' => __( 'Instructors', 'lifterlms' ),
		);

		return array_slice( $columns, 0, $offset + 1 ) + $add + array_slice( $columns, $offset );

	}

	/**
	 * Create a string that can be used in a LIKE query for finding a student's id in the llms_instructors
	 * meta field on the usermeta table
	 * @param    int     $user_id  WP User ID
	 * @return   string
	 * @since    3.13.0
	 * @version  3.13.0
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
	 * @since    3.13.0
	 * @version  3.24.0
	 */
	public function get_views( $views ) {

		$post_type = sanitize_text_field( $_GET['post_type'] );

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
			WHERE p.post_type = %s
			  AND p.post_status NOT IN ( '" . implode( "','", $exclude_states ) . "' )
		", '%' . $this->get_serialized_id( $current_user_id ) . '%', $post_type ) ) );

		$label = sprintf(
			_nx(
				'Mine <span class="count">(%s)</span>',
				'Mine <span class="count">(%s)</span>',
				$count,
				'posts',
				'lifterlms'
			),
			number_format_i18n( $count )
		);

		$url = add_query_arg( array(
			'post_type' => $post_type,
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
	 * Manage content of custom columns
	 * @param    string  $column   column key/name
	 * @param    int     $post_id  WP Post ID of the coupon for the row
	 * @return   void
	 * @since    3.13.0
	 * @version  3.23.0
	 */
	public function manage_columns( $column, $post_id ) {

		$post = llms_get_post( $post_id );

		switch ( $column ) {

			case 'llms-instructors':
				$instructors = $post->get_instructors();
				$htmls = array();
				foreach ( $instructors as $user ) {

					$url = add_query_arg( array(
						'post_type' => $post->get( 'type' ),
						'author' => $user['id'],
					), 'edit.php' );

					$instructor = llms_get_instructor( $user['id'] );

					if ( $instructor ) {
						$htmls[] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $url ), $instructor->get( 'display_name' ) );
					}
				}
				echo implode( ', ', $htmls );
			break;

		}

	}

	/**
	 * Handle course & membership queries for searching by llms_instructors rather than author
	 * @param    obj     $query  WP_Query
	 * @return   void
	 * @since    3.13.0
	 * @version  3.13.0
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

		if ( isset( $query->query_vars['post_type'] ) && in_array( $query->query_vars['post_type'], $this->post_types ) && ! empty( $query->query_vars['author'] ) ) {

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

}

return new LLMS_Admin_Post_Table_Instructors();
