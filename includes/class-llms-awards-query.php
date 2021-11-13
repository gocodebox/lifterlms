<?php
/**
 * LLMS_Awards_Query class file
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Query awarded achievements and engagements
 *
 * @since [version]
 */
class LLMS_Awards_Query {

	/**
	 * Award type.
	 *
	 * Either "achievements" or "certificates".
	 *
	 * @var string
	 */
	public $type = '';

	/**
	 * User-submitted query args before sanitization, parsing, etc...
	 *
	 * @var array
	 */
	public $args = array();

	/**
	 * Final arguments passed to the WP_Query.
	 *
	 * @var array
	 */
	public $query_args = array();

	/**
	 * WP_Query object for the query instance.
	 *
	 * @var null
	 */
	public $query = null;

	/**
	 * Constructor
	 *
	 * @since [version]
	 *
	 * @param string $type       Award type to query. Accepts: "achievements" or "certificates".
	 * @param array  $query_args {
	 *     Query arguments.
	 *
	 *     @type array     $sort                   Array of sorting arguments where the key is value to sort by and the value is the direction.
	 *     @type int|int[] $users                  Include awards for the specified WP_User(s) by WP_User ID.
	 *     @type int|int[] $users__exclude         Exclude awards for the specified WP_User(s) by WP_User ID.
	 *     @type int|int[] $related_posts          Include awards related to the specified WP_Post(s) by WP_Post ID.
	 *     @type int|int[] $related_posts__exclude Exclude awards related to the specified WP_Post(s) by WP_Post ID.
	 *     @type int|int[] $engagements            Include awards created from the specified `llms_engagement` post(s) by WP_Post ID.
	 *     @type int|int[] $engagements__exclude   Exclude awards created from the specified `llms_engagement` post(s) by WP_Post ID.
	 *     @type int|int[] $templates              Include awards created from the specified `llms_achievement` or `llms_certificate` template post(s) by WP_Post ID.
	 *     @type int|int[] $templates__exclude     Exclude awards created from the specified `llms_achievement` or `llms_certificate` template  post(s) by WP_Post ID.
	 *     @type boolean   $manual                 Include only awards created manually. If specified the `$related_posts`, `$related_posts__exclude`, `$engagements`, `$engagements__exclude`, `$templates`, and `$templates__exclude` arguments will be ignored.
 	 *     @type int       $page                   Results page number.
	 *     @type int       $per_page               Number of results to display per page. Use `-1` to show all possible results.
	 *     @type boolean   $no_found_rows          Whether to skip counting the total rows found.
	 * }
	 */
	public function __construct( $type, $args = array() ) {

		$this->type       = $type;
		$this->args       = $args;
		$this->query_args = $this->prepare_query( $args );
		$this->query      = new WP_Query( $this->query_args );

	}

	/**
	 * Sanitize arguments passed into a query.
	 *
	 * @since [version]
	 *
	 * @param array $args Array of arguments.
	 * @return array Cleaned arguments.
	 */
	private function clean_args( $args ) {

		$args['sort'] = $this->clean_sort( (array) $args['sort'] );

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
			$args[ $key ] = $this->to_int_array( $args[ $key ] );
		}

		$args['manual'] = llms_parse_bool( $args['manual'] );

		$args['page']     = absint( $args['page'] );
		$args['per_page'] = intval( $args['per_page'] );

		$args['no_found_rows'] = llms_parse_bool( $args['no_found_rows'] );

		return $args;

	}

	/**
	 * Cleans the `sort` argument.
	 *
	 * Forces the sort order to DESC if an invalid order is provided.
	 *
	 * Ensures the sort fields are allowed and removes anything not in the allowed list.
	 *
	 * @since [version]
	 *
	 * @param array $sort Sort argument array.
	 * @return array
	 */
	private function clean_sort( $sort ) {

		foreach ( $sort as $field => &$order ) {

			$order = strtoupper( $order );
			if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
				$order = 'DESC';
			}

			if ( ! in_array( $field, $this->get_allowed_sort_fields(), true ) ) {
				unset( $sort[ $field ] );
			}

		}

		return $sort;

	}

	/**
	 * Retrieve a list of fields that are allowed to be used for result sorting.
	 *
	 * @since [version]
	 *
	 * @return string[]
	 */
	private function get_allowed_sort_fields() {

		$fields = array(
			'date',
			'ID',
			'user',
		);

		/**
		 * Filters the allowed sort fields.
		 *
		 * They dynamic portion of this hook, `$this->type`, refers to the award type
		 * for the query, either "achievements" or "certificates".
		 *
		 * @since [version]
		 *
		 * @param array $defaults Default arguments.
		 */
		return apply_filters( "llms_{$this->type}_awards_query_allowed_sort_fields", $fields );

	}

	/**
	 * Retrieve query argument default values.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	private function get_defaults() {

		$defaults = array(
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
			'manual'                 => false,
			'page'                   => 1,
			'per_page'               => 10,
			'no_found_rows'          => false,
		);

		/**
		 * Filters the query defaults.
		 *
		 * They dynamic portion of this hook, `$this->type`, refers to the award type
		 * for the query, either "achievements" or "certificates".
		 *
		 * @since [version]
		 *
		 * @param array $defaults Default arguments.
		 */
		return apply_filters( "llms_{$this->type}_awards_query_defaults", $defaults );

	}

	/**
	 * Retrieve the total found awards for the query.
	 *
	 * If `no_found_rows` is supplied to the initial query this will return `0`
	 * which may not reflect the actual number of found posts for the given query.
	 *
	 * @since [version]
	 *
	 * @return int
	 */
	public function get_found_results() {
		return $this->query->found_posts;
	}

	/**
	 * Retrieve the number of results for the given query page.
	 *
	 * @since [version]
	 *
	 * @return int
	 */
	public function get_number_results() {
		return $this->query->post_count;
	}

	/**
	 * Retrieve the award's object from a WP_Post.
	 *
	 * @since [version]
	 *
	 * @param WP_Post $post Post object.
	 * @return LLMS_User_Achievement|LLMS_User_Certificate|WP_Post
	 */
	private function get_object( $post ) {

		$object = $post;
		if ( 'achievements' === $this->type ) {
			$object = new LLMS_User_Achievement( $post );
		} elseif ( 'certificates' === $this->type ) {
			$object = llms_get_certificate( $post );
		}

		/**
		 * Filters the returned object.
		 *
		 * @since [version]
		 *
		 * @param object  $object     The retrieve object.
		 * @param string  $award_type The award type for the query, either "achievements" or "certificates".
		 * @param WP_Post $post       The original object.
		 */
		return apply_filters( 'llms_awards_query_post_type', $object, $this->type, $post );

	}

	/**
	 * Retrieve the post type for the query's award type.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	private function get_post_type() {

		$post_type = '';
		if ( 'achievements' === $this->type ) {
			$post_type = 'llms_my_achievement';
		} elseif ( 'certificates' === $this->type ) {
			$post_type = 'llms_my_certificate';
		}

		/**
		 * Filters the post type for the query's award type.
		 *
		 * @since [version]
		 *
		 * @param string $post_type  A WP_Post_Type name.
		 * @param string $award_type The award type for the query, either "achievements" or "certificates".
		 */
		return apply_filters( 'llms_awards_query_post_type', $post_type, $this->type );

	}

	/**
	 * Retrieve the results for the given query.
	 *
	 * @since [version]
	 *
	 * @param string $output Determine the return type. Either "OBJECTS" to return `LLMS_User_Achievement` or `LLMS_User_Certificate` objects
	 *                       or `POSTS` to return `WP_Post` objects.
	 * @return LLMS_User_Achievement[]|LLMS_User_Certificate[]|WP_Post[] Array of objects.
	 */
	public function get_results( $output = 'OBJECTS' ) {

		$posts = $this->query->posts;

		if ( 'POSTS' === $output ) {
			return $posts;
		}

		return array_map( array( $this, 'get_object' ), $posts );

	}

	/**
	 * Retrieve the meta key name for the template used to generate the awarded post.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	private function get_template_meta_key() {

		$meta_key = '';
		if ( 'achievements' === $this->type ) {
			$meta_key = '_llms_achievement_template';
		} elseif ( 'certificates' === $this->type ) {
			$meta_key = '_llms_certificate_template';
		}

		/**
		 * Filters the meta key name for the query's award type.
		 *
		 * @since [version]
		 *
		 * @param string $meta_key   The meta key name.
		 * @param string $award_type The award type for the query, either "achievements" or "certificates".
		 */
		return apply_filters( 'llms_awards_query_meta_key', $meta_key, $this->type );

	}

	/**
	 * Retrieve the total number of available result pages for the given query.
	 *
	 * If `no_found_rows` is supplied to the initial query this will return `0`
	 * which may not reflect the actual number of result pages.
	 *
	 * @since [version]
	 *
	 * @return int
	 */
	public function get_total_pages() {
		return $this->query->max_num_pages;
	}

	/**
	 * Determine if any results were found for the query.
	 *
	 * @since [version]
	 *
	 * @return boolean
	 */
	public function has_results() {
		return count( $this->query->posts ) >= 1;
	}

	/**
	 * Prepares the `meta_query` ultimately passed to the WP_Query.
	 *
	 * @since [version]
	 *
	 * @param array $args Cleaned LLMS_Awards_Query arguments.
	 * @return array An array of meta query arrays.
	 */
	private function prepare_meta_query( $args ) {

		// If a query for manual awards we skip all other relationships and return early.
		if ( $args['manual'] ) {

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

		return $this->prepare_meta_query_for_relationships( $args );

	}

	/**
	 * Retrieve meta query parts for related posts, engagements, and templates.
	 *
	 * @since [version]
	 *
	 * @param array $args Cleaned LLMS_Awards_Query arguments.
	 * @return array An array of meta query arrays.
	 */
	private function prepare_meta_query_for_relationships( $args ) {

		$meta_query = array();

		$relations = array(
			'related_posts' => '_llms_related',
			'engagements'   => '_llms_engagement',
			'templates'     => $this->get_template_meta_key(),
		);
		foreach ( $relations as $arg => $meta_key ) {

			// Include.
			if ( ! empty( $args[ $arg ] ) ) {
				$meta_query[] = array(
					'key'     => $meta_key,
					'value'   => $this->to_int_array( $args[ $arg ] ),
					'compare' => 'IN',
				);
			}

			// Exclude.
			$exclude_arg = $arg . '__exclude';
			if ( ! empty( $args[ $exclude_arg ] ) ) {

				$meta_query[] = array(
					'relation' => 'OR',
					array(
						'key'     => $meta_key,
						'value'   => $this->to_int_array( $args[ $exclude_arg ] ),
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
	 * @since [version]
	 *
	 * @param array $args Input arguments from `__construct()`.
	 * @return array Array of arguments suitable to pass to a WP_Query.
	 */
	private function prepare_query( $args ) {

		$args = $this->clean_args( wp_parse_args( $args, $this->get_defaults() ) );

		$query_args = array(
			'author__in'     => $args['users'],
			'author__not_in' => $args['users__exclude'],
			'post_type'      => $this->get_post_type(),
			'paged'          => $args['page'],
			'per_page'       => $args['per_page'],
			'no_found_rows'  => $args['no_found_rows'],
			'orderby'        => $args['sort'],
		);

		$meta_query = $this->prepare_meta_query( $args );
		if ( $meta_query ) {
			$query_args['meta_query'] = $meta_query;
		}

		/**
		 * Filters the WP_Query arguments for the awards query before passing them into WP_Query.
		 *
		 * They dynamic portion of this hook, `$this->type`, refers to the award type
		 * for the query, either "achievements" or "certificates".
		 *
		 * @since [version]
		 *
		 * @param array $query_args Prepared WP_Query arguments.
		 * @param array $args       Array of cleaned LLMS_Awards_Query arguments.
		 */
		return apply_filters( "llms_{$this->type}_awards_query", $query_args, $args );

	}

	/**
	 * Coerce an input value to an array of integers.
	 *
	 * This is used for any query arguments that allow an integer or an array
	 * of integers.
	 *
	 * @since [version]
	 *
	 * @param mixed $val Input value.
	 * @return int[]
	 */
	private function to_int_array( $val ) {

		$val = ! is_array( $val ) ? array( $val ) : $val;
		return array_values( array_filter( array_map( 'absint', $val ) ) );

	}

}
