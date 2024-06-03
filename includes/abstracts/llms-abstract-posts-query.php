<?php
/**
 * LLMS_Abstract_Posts_Query class file.
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 6.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Abstract WP_Posts query class.
 *
 * This class is meant to perform custom queries that ultimately
 * get passed into a `WP_Query`, ideally for a specific post type
 * or list of post types.
 *
 * @since 6.0.0
 *
 * Valid query arguments
 *
 * {@see LLMS_Abstract_Query} for inherited query arguments.
 *
 * @param string          $fields     WP_Post fields to return for each result. Accepts "all", "ids", or "id=>parent". Default: "all".
 * @param string[]|string $status     Limit results by WP_Post `$post_status`. Default: "publish".
 * @param string[]        $post_types Limit results to the specified post type(s).
 */
abstract class LLMS_Abstract_Posts_Query extends LLMS_Abstract_Query {

	/**
	 * Defines fields that can be sorted on via ORDER BY.
	 *
	 * @var string[]
	 */
	protected $allowed_sort_fields = array(
		'ID',
		'author',
		'title',
		'name',
		'type',
		'date',
		'modified',
		'parent',
		'menu_order',
	);

	/**
	 * Specify the post types allowed to be queried by this class
	 *
	 * This array should be a list of one or more post type names.
	 *
	 * @var string[]
	 */
	protected $allowed_post_types = array();

	/**
	 * The WP_Query instance.
	 *
	 * @var null
	 */
	protected $wp_query = null;

	/**
	 * Set result counts and pagination properties.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	protected function count_results() {

		$this->number_results = $this->wp_query->post_count;
		$this->found_results  = $this->found_results();
		$this->max_pages      = (int) $this->wp_query->max_num_pages;

	}

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
				'fields'     => 'all',
				'status'     => 'publish',
				'post_types' => $this->allowed_post_types,
				'sort'       => array(
					'date' => 'DESC',
					'ID'   => 'DESC',
				),
			),
			parent::default_arguments()
		);

	}

	/**
	 * Retrieve total found results for the query.
	 *
	 * @since 6.0.0
	 *
	 * @return int
	 */
	protected function found_results() {
		return $this->wp_query->found_posts;
	}

	/**
	 * Map input arguments to WP_Query arguments.
	 *
	 * @since 6.0.0
	 *
	 * @return array
	 */
	protected function get_arg_map() {

		return array(
			'page'       => 'paged',
			'per_page'   => 'posts_per_page',
			'post_types' => 'post_type',
			'search'     => 's',
			'sort'       => 'orderby',
			'status'     => 'post_status',
		);

	}

	/**
	 * Retrieve the WP_Query object for the query.
	 *
	 * @since 6.0.0
	 *
	 * @return WP_Query
	 */
	public function get_wp_query() {
		return $this->wp_query;
	}

	/**
	 * Performs the query.
	 *
	 * @since 6.0.0
	 *
	 * @return WP_Post[]|int[] Array of results corresponding to the value specified in the `$fields` query argument.
	 */
	protected function perform_query() {

		$this->wp_query = new WP_Query( $this->query );
		return $this->wp_query->posts;

	}

	/**
	 * Prepare the query.
	 *
	 * Should return the query which will be used by `query()`.
	 *
	 * @since 6.0.0
	 *
	 * @return mixed
	 */
	protected function prepare_query() {

		$map = $this->get_arg_map();

		$args = array();
		foreach ( $this->query_vars as $var => $val ) {

			$var          = array_key_exists( $var, $map ) ? $map[ $var ] : $var;
			$args[ $var ] = $val;
		}

		return $args;

	}

	/**
	 * Sets a query variable.
	 *
	 * Overrides parent method to ensure only allowed post types can be queried.
	 *
	 * @since 6.0.0
	 *
	 * @param string $key Variable key.
	 * @param mixed  $val Variable value.
	 * @return void
	 */
	public function set( $key, $val ) {

		if ( 'post_types' === $key ) {
			$val = $this->sanitize_post_types( $val );
		}

		parent::set( $key, $val );

	}

	/**
	 * Sanitize the `post_types` query argument.
	 *
	 * Any post types not explicitly included in the `$allowed_post_types` list are
	 * removed from the input.
	 *
	 * @since 6.0.0
	 *
	 * @param string[] $val Array of post types to query.
	 * @return string[] Cleaned array.
	 */
	protected function sanitize_post_types( $val ) {

		if ( ! is_array( $val ) ) {
			return array();
		}

		foreach ( $val as $index => $post_type ) {
			if ( ! in_array( $post_type, $this->allowed_post_types, true ) ) {
				unset( $val[ $index ] );
			}
		}

		return array_values( $val );

	}

}
