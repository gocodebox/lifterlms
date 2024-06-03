<?php
/**
 * LLMS_Awards_Query class file.
 *
 * @package LifterLMS/Classes
 *
 * @since 6.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Query awarded achievements and engagements.
 *
 * @since 6.0.0
 *
 * Valid query arguments
 *
 * {@see LLMS_Abstract_Query} and {@see LLMS_Abstract_Posts_Query} for inherited query arguments.
 *
 * @param int|int[] $users                  Include awards for the specified WP_User(s) by WP_User ID.
 * @param int|int[] $users__exclude         Exclude awards for the specified WP_User(s) by WP_User ID.
 * @param int|int[] $related_posts          Include awards related to the specified WP_Post(s) by WP_Post ID.
 * @param int|int[] $related_posts__exclude Exclude awards related to the specified WP_Post(s) by WP_Post ID.
 * @param int|int[] $engagements            Include awards created from the specified `llms_engagement` post(s) by WP_Post ID.
 * @param int|int[] $engagements__exclude   Exclude awards created from the specified `llms_engagement` post(s) by WP_Post ID.
 * @param string    $type                   Award type, accepts "any", "achievement" or "certificate".
 * @param int|int[] $templates              Include awards created from the specified `llms_achievement` or `llms_certificate` template post(s) by WP_Post ID.
 * @param int|int[] $templates__exclude     Exclude awards created from the specified `llms_achievement` or `llms_certificate` template  post(s) by WP_Post ID.
 * @param boolean   $manual_only            Include only awards created manually. If specified the `$related_posts`, `$related_posts__exclude`, `$engagements`, `$engagements__exclude`, `$templates`, and `$templates__exclude` arguments will be ignored.
 */
class LLMS_Awards_Query extends LLMS_Abstract_Posts_Query {

	/**
	 * Identify the extending query.
	 *
	 * @var string
	 */
	protected $id = 'awards';

	/**
	 * Specify the post types allowed to be queried by this class.
	 *
	 * @var string[]
	 */
	protected $allowed_post_types = array(
		'llms_my_achievement',
		'llms_my_certificate',
	);

	/**
	 * Defines fields that can be sorted on via ORDER BY.
	 *
	 * @var string[]
	 */
	protected $allowed_sort_fields = array(
		'date',
		'ID',
		'user',
	);

	/**
	 * Retrieve query argument default values.
	 *
	 * @since 6.0.0
	 *
	 * @return array
	 */
	protected function default_arguments() {

		return wp_parse_args(
			array(
				'sort'                   => array(
					'date' => 'DESC',
					'ID'   => 'DESC',
				),
				'users'                  => array(),
				'users__exclude'         => array(),
				'related_posts'          => array(),
				'related_posts__exclude' => array(),
				'engagements'            => array(),
				'engagements__exclude'   => array(),
				'templates'              => array(),
				'templates__exclude'     => array(),
				'type'                   => 'any',
				'manual_only'            => false,
			),
			parent::default_arguments()
		);

	}

	/**
	 * Map input arguments to WP_Query arguments.
	 *
	 * @since 6.0.0
	 *
	 * @return array
	 */
	protected function get_arg_map() {

		$map = parent::get_arg_map();

		return array_merge(
			$map,
			array(
				'users'              => 'author__in',
				'users__exclude'     => 'author__not_in',
				'templates'          => 'post_parent__in',
				'templates__exclude' => 'post_parent__not_in',
			)
		);

	}

	/**
	 * Retrieve an array of award objects for the given result set returned by the query.
	 *
	 * @since 6.0.0
	 *
	 * @return array Array of LLMS_User_Achievement and/or LLMS_User_Certificate objects.
	 */
	public function get_awards() {

		$awards = array_filter( array_map( array( $this, 'get_object' ), $this->get_results() ) );

		if ( $this->get( 'suppress_filters' ) ) {
			return $awards;
		}

		/**
		 * Filters the query results array.
		 *
		 * @since 6.0.0
		 *
		 * @param array             $awards Array of LLMS_User_Achievement and/or LLMS_User_Certificate objects.
		 * @param LLMS_Awards_Query $query  Instance of the query class.
		 */
		return apply_filters( 'llms_awards_query_get_awards', $awards, $this );

	}

	/**
	 * Retrieve the object for a given result.
	 *
	 * @since 6.0.0
	 *
	 * @param int|WP_Post $post Post object or ID.
	 * @return LLMS_User_Achievement|LLMS_User_Certificate|null Returns the award object or `null` for unexpected post types.
	 */
	protected function get_object( $post ) {

		$post_type = get_post_type( $post );
		if ( 'llms_my_achievement' === $post_type ) {
			return new LLMS_User_Achievement( $post );
		} elseif ( 'llms_my_certificate' === $post_type ) {
			return llms_get_certificate( $post );
		}

		return null;

	}

	/**
	 * Parse arguments needed for the query.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	protected function parse_args() {

		$int_arrays = array(
			'users',
			'users__exclude',
			'related_posts',
			'related_posts__exclude',
			'engagements',
			'engagements__exclude',
			'templates',
			'templates__exclude',
		);
		foreach ( $int_arrays as $key ) {
			$this->arguments[ $key ] = $this->sanitize_id_array( $this->arguments[ $key ] );
		}

		$this->arguments['manual_only'] = llms_parse_bool( $this->arguments['manual_only'] );

		$this->arguments['page']     = absint( $this->arguments['page'] );
		$this->arguments['per_page'] = intval( $this->arguments['per_page'] );

		$this->arguments['no_found_rows'] = llms_parse_bool( $this->arguments['no_found_rows'] );

	}

	/**
	 * Retrieve the post type(s) based on the `$type` input.
	 *
	 * @since 6.0.0
	 *
	 * @return string[]
	 */
	protected function post_types() {

		$type  = $this->get( 'type' );
		$types = array();
		if ( 'any' === $type ) {
			$types = $this->allowed_post_types;
		} elseif ( 'achievement' === $type ) {
			$types = array( 'llms_my_achievement' );
		} elseif ( 'certificate' === $type ) {
			$types = array( 'llms_my_certificate' );
		}

		return $types;

	}

	/**
	 * Prepares the `meta_query` ultimately passed to the WP_Query.
	 *
	 * @since 6.0.0
	 *
	 * @return array An array of meta query arrays.
	 */
	private function prepare_meta_query() {

		// If a query for manual awards we skip all other relationships and return early.
		if ( $this->get( 'manual_only' ) ) {

			return array(
				'relation' => 'OR',
				// Meta doesn't exist.
				array(
					'key'     => '_llms_engagement',
					'compare' => 'NOT EXISTS',
				),
				// Or it's "empty".
				array(
					'key'     => '_llms_engagement',
					'value'   => array( '', '0', 0 ),
					'compare' => 'IN',
				),
			);

		}

		return $this->prepare_meta_query_for_relationships();

	}

	/**
	 * Retrieve meta query parts for related posts, engagements, and templates.
	 *
	 * @since 6.0.0
	 *
	 * @return array An array of meta query arrays.
	 */
	private function prepare_meta_query_for_relationships() {

		$meta_query = array();

		$relations = array(
			'related_posts' => '_llms_related',
			'engagements'   => '_llms_engagement',
		);
		foreach ( $relations as $arg => $meta_key ) {

			// Include.
			if ( ! empty( $this->get( $arg ) ) ) {
				$meta_query[] = array(
					'key'     => $meta_key,
					'value'   => $this->sanitize_id_array( $this->get( $arg ) ),
					'compare' => 'IN',
				);
			}

			// Exclude.
			$exclude_arg = $arg . '__exclude';
			if ( ! empty( $this->get( $exclude_arg ) ) ) {

				$meta_query[] = array(
					'relation' => 'OR',
					array(
						'key'     => $meta_key,
						'value'   => $this->sanitize_id_array( $this->get( $exclude_arg ) ),
						'compare' => 'NOT IN',
					),
					// Ensure posts that don't have the metadata set will be returned.
					array(
						'key'     => $meta_key,
						'compare' => 'NOT EXISTS',
					),
				);
			}
		}

		return $meta_query;

	}

	/**
	 * Prepare the WP_Query arguments for the awards query.
	 *
	 * @since 6.0.0
	 *
	 * @return array Array of arguments suitable to pass to a WP_Query.
	 */
	protected function prepare_query() {

		// Remove any extra arguments not found in the map.
		$args = array_intersect_key(
			parent::prepare_query(),
			array_flip( $this->get_arg_map() )
		);

		// Add post type(s).
		$args['post_type'] = $this->post_types();

		// Add meta query.
		$args['meta_query'] = $this->prepare_meta_query();

		// Remove empty arrays.
		return array_filter(
			$args,
			function( $val ) {
				return ! is_array( $val ) || ! empty( $val );
			}
		);

	}

}
