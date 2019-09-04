<?php
/**
 * Memberships Reporting Table
 *
 * @since 3.32.0
 * @version 3.32.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Memberships Reporting Table class.
 *
 * @since 3.32.0
 */
class LLMS_Table_Memberships extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 *
	 * @since 3.32.0
	 *
	 * @var  string
	 */
	protected $id = 'memberships';

	/**
	 * Value of the field being filtered by.
	 * Only applicable if $filterby is set.
	 *
	 * @since 3.32.0
	 *
	 * @var  string
	 */
	protected $filter = 'any';

	/**
	 * Field results are filtered by.
	 *
	 * @since 3.32.0
	 *
	 * @var  string
	 */
	protected $filterby = 'instructor';

	/**
	 * Is the Table Exportable?
	 *
	 * @since 3.32.0
	 *
	 * @var  boolean
	 */
	protected $is_exportable = true;

	/**
	 * Determine if the table is filterable.
	 *
	 * @since 3.32.0
	 *
	 * @var  boolean
	 */
	protected $is_filterable = true;

	/**
	 * If true, tfoot will add ajax pagination links.
	 *
	 * @since 3.32.0
	 *
	 * @var  boolean
	 */
	protected $is_paginated = true;

	/**
	 * Determine of the table is searchable
	 *
	 * @since 3.32.0
	 *
	 * @var  boolean
	 */
	protected $is_searchable = true;

	/**
	 * Results sort order 'ASC' or 'DESC'.
	 * Only applicable of $orderby is not set.
	 *
	 * @since 3.32.0
	 *
	 * @var  string
	 */
	protected $order = 'ASC';

	/**
	 * Field results are sorted by.
	 *
	 * @since 3.32.0
	 *
	 * @var  string
	 */
	protected $orderby = 'title';

	/**
	 * Retrieve data for a cell.
	 *
	 * @since 3.32.0
	 *
	 * @param string $key   The column id / key.
	 * @param mixed  $data  Object / array of data that the function can use to extract the data.
	 * @return mixed
	 */
	protected function get_data( $key, $data ) {

		$membership = llms_get_post( $data );

		switch ( $key ) {

			case 'id':
				$value = $this->get_post_link( $membership->get( 'id' ) );
				break;

			case 'instructors':
				$data = array();
				foreach ( $membership->get_instructors() as $info ) {
					$instructor = llms_get_instructor( $info['id'] );
					if ( $instructor ) {
						$data[] = sprintf( '%1$s (%2$s)', $instructor->get( 'display_name' ), $info['label'] );
					}
				}
				$value = implode( ', ', $data );
				break;

			case 'students':
				$value = number_format_i18n( $membership->get_student_count(), 0 );
				break;

			case 'title':
				$url   = LLMS_Admin_Reporting::get_current_tab_url(
					array(
						'tab'           => 'memberships',
						'membership_id' => $membership->get( 'id' ),
					)
				);
				$value = '<a href="' . esc_url( $url ) . '">' . $membership->get( 'title' ) . '</a>';
				break;

			default:
				$value = $key;

		}// End switch().

		return $value;
	}

	/**
	 * Retrieve a list of Instructors to be used for Filtering.
	 *
	 * @since 3.32.0
	 *
	 * @return array
	 */
	private function get_instructor_filters() {

		$query = get_users(
			array(
				'fields'   => array( 'ID', 'display_name' ),
				'meta_key' => 'last_name',
				'orderby'  => 'meta_value',
				'role__in' => array( 'administrator', 'lms_manager', 'instructor', 'instructors_assistant' ),
			)
		);

		$instructors = wp_list_pluck( $query, 'display_name', 'ID' );

		return $instructors;

	}

	/**
	 * Execute a query to retrieve results from the table.
	 *
	 * @since 3.32.0
	 *
	 * @param  array $args  Optional. Array of query args. Default empty array.
	 * @return void
	 */
	public function get_results( $args = array() ) {

		$this->title = __( 'Memberships', 'lifterlms' );

		$args = $this->clean_args( $args );

		if ( isset( $args['page'] ) ) {
			$this->current_page = absint( $args['page'] );
		}

		$per = apply_filters( 'llms_reporting_' . $this->id . '_per_page', 25 );

		$this->order   = isset( $args['order'] ) ? $args['order'] : $this->order;
		$this->orderby = isset( $args['orderby'] ) ? $args['orderby'] : $this->orderby;

		$this->filter   = isset( $args['filter'] ) ? $args['filter'] : $this->get_filter();
		$this->filterby = isset( $args['filterby'] ) ? $args['filterby'] : $this->get_filterby();

		$query_args = array(
			'order'          => $this->order,
			'orderby'        => $this->orderby,
			'paged'          => $this->current_page,
			'post_status'    => array( 'publish', 'private' ),
			'post_type'      => 'llms_membership',
			'posts_per_page' => $per,
		);

		if ( 'any' !== $this->filter ) {

			$serialized_id = serialize(
				array(
					'id' => absint( $this->filter ),
				)
			);
			$serialized_id = str_replace( array( 'a:1:{', '}' ), '', $serialized_id );

			$query_args['meta_query'] = array(
				array(
					'compare' => 'LIKE',
					'key'     => '_llms_instructors',
					'value'   => $serialized_id,
				),
			);

		}

		if ( isset( $args['search'] ) ) {
			$query_args['s'] = sanitize_text_field( $args['search'] );
		}

		// if you can view others reports, make a regular query
		if ( current_user_can( 'view_others_lifterlms_reports' ) ) {

			$query = new WP_Query( $query_args );

			// user can only see their own reports, get a list of their students
		} elseif ( current_user_can( 'view_lifterlms_reports' ) ) {

			$instructor = llms_get_instructor();
			if ( ! $instructor ) {
				return;
			}
			$query = $instructor->get_memberships( $query_args, 'query' );

		} else {

			return;

		}

		$this->max_pages = $query->max_num_pages;

		if ( $this->max_pages > $this->current_page ) {
			$this->is_last_page = false;
		}

		$this->tbody_data = $query->posts;

	}

	/**
	 * Get the Text to be used as the placeholder in a searchable tables search input.
	 *
	 * @since 3.32.0
	 *
	 * @return string
	 */
	public function get_table_search_form_placeholder() {
		return apply_filters( 'llms_table_get_' . $this->id . '_search_placeholder', __( 'Search memberships...', 'lifterlms' ) );
	}

	/**
	 * Define the structure of arguments used to pass to the get_results method.
	 *
	 * @since 3.32.0
	 *
	 * @return array
	 */
	public function set_args() {
		return array();
	}

	/**
	 * Define the structure of the table.
	 *
	 * @since 3.32.0
	 *
	 * @return array
	 */
	protected function set_columns() {
		return array(
			'id'          => array(
				'exportable' => true,
				'title'      => __( 'ID', 'lifterlms' ),
				'sortable'   => true,
			),
			'title'       => array(
				'exportable' => true,
				'title'      => __( 'Title', 'lifterlms' ),
				'sortable'   => true,
			),
			'instructors' => array(
				'exportable' => true,
				'filterable' => current_user_can( 'view_others_lifterlms_reports' ) ? $this->get_instructor_filters() : false,
				'title'      => __( 'Instructors', 'lifterlms' ),
			),
			'students'    => array(
				'exportable' => true,
				'title'      => __( 'Students', 'lifterlms' ),
			),
		);
	}

}
