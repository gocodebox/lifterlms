<?php
/**
 * Student Management table on Courses and Memberships
 *
 * @since 3.4.0
 * @version 3.33.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Student Management table on Courses and Memberships class.
 *
 * @since 3.4.0
 * @since 3.33.0 Added table action button to delete a cancelled enrollment.
 * @since 3.33.0 Added popover tooltip to the table action button icons via llms tooltip data attribute api.
 */
class LLMS_Table_StudentManagement extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 *
	 * @var  string
	 */
	protected $id = 'student-management';

	/**
	 * Value of the field being filtered by
	 * Only applicable if $filterby is set
	 *
	 * @var  string
	 */
	protected $filter = 'any';

	/**
	 * Field results are filtered by
	 *
	 * @var  string
	 */
	protected $filterby = 'status';

	/**
	 * Determine if the table is filterable
	 *
	 * @var  boolean
	 */
	protected $is_filterable = true;

	/**
	 * If true, tfoot will add ajax pagination links
	 *
	 * @var  boolean
	 */
	protected $is_paginated = true;

	/**
	 * Determine of the table is searchable
	 *
	 * @var  boolean
	 */
	protected $is_searchable = true;

	/**
	 * Results sort order
	 * 'ASC' or 'DESC'
	 * Only applicable of $orderby is not set
	 *
	 * @var  string
	 */
	protected $order = 'ASC';

	/**
	 * Field results are sorted by
	 *
	 * @var  string
	 */
	protected $orderby = 'name';

	/**
	 * Post ID for the current table
	 *
	 * @var  int
	 */
	protected $post_id = null;

	/**
	 * Retrieve data for the columns
	 *
	 * @since 3.4.0
	 * @since 3.33.0 Added action button to delete a cancelled enrollment.
	 * @since 3.33.0 Added icon popover tooltip via llms tooltip data attribute api.
	 *
	 * @param string $key The column id / key.
	 * @param int    $user_id WP User ID.
	 * @return  mixed
	 */
	public function get_data( $key, $student ) {

		$value = '';

		switch ( $key ) {

			case 'actions':
				if ( $student->is_enrolled( $this->post_id ) ) {
					$trigger = $student->get_enrollment_trigger( $this->post_id );
					if ( false !== strpos( $trigger, 'order_' ) ) {
						$value = '<a class="llms-action-icon tip--top-left" href="' . get_edit_post_link( $student->get_enrollment_trigger_id( $this->post_id ) ) . '" target="_blank" data-tip="' . __( 'Visit the triggering order to manage this student\'s enrollment', 'lifterlms' ) . '"><span class="dashicons dashicons-external"></span></a>';
					} else {
						if ( current_user_can( 'unenroll' ) ) {
							$value = '<a class="llms-action-icon llms-remove-student tip--top-left" data-id="' . $student->get_id() . '" href="#llms-student-remove" data-tip="' . __( 'Cancel Enrollment', 'lifterlms' ) . '"><span class="dashicons dashicons-no"></span></a>';
						}
					}
				} else {
					if ( current_user_can( 'enroll' ) ) {
						$value = '<a class="llms-action-icon llms-add-student tip--top-left" data-id="' . $student->get_id() . '" href="#llms-student-add" data-tip="' . __( 'Reactivate Enrollment', 'lifterlms' ) . '"><span class="dashicons dashicons-update"></span></a>';
					}
					if ( current_user_can( 'unenroll' ) ) {
						$value .= '<a class="llms-action-icon danger llms-delete-enrollment tip--top-left" data-id="' . $student->get_id() . '" href="#llms-student-delete-enrollment" data-tip="' . __( 'Delete Enrollment', 'lifterlms' ) . '"><span class="dashicons dashicons-trash"></span></a>';
					}
				}
				break;

			case 'enrolled':
				$value = $student->get_enrollment_date( $this->post_id, 'updated' );
				break;

			case 'grade':
				$value = $student->get_grade( $this->post_id );
				break;

			case 'id':
				$id = $student->get_id();
				if ( current_user_can( 'edit_users', $id ) ) {
					$value = '<a href="' . esc_url( get_edit_user_link( $id ) ) . '">' . $id . '</a>';
				} else {
					$value = $id;
				}
				break;

			case 'last_lesson':
				$lid = $student->get_last_completed_lesson( $this->post_id );
				if ( $lid ) {
					$value = $this->get_post_link( $lid, llms_trim_string( get_the_title( $lid ), 30 ) );
				} else {
					$value = '&ndash;';
				}
				break;

			case 'name':
				$first = $student->get( 'first_name' );
				$last  = $student->get( 'last_name' );

				if ( ! $first || ! $last ) {
					$value = $student->get( 'display_name' );
				} else {
					$value = $last . ', ' . $first;
				}

				$url   = add_query_arg(
					array(
						'page'       => 'llms-reporting',
						'tab'        => 'students',
						'student_id' => $student->get_id(),
					),
					admin_url( 'admin.php' )
				);
				$value = '<a href="' . esc_url( $url ) . '">' . $value . '</a>';

				break;

			case 'progress':
				$value = $student->get_progress( $this->post_id ) . '%';
				break;

			case 'status':
				$value = llms_get_enrollment_status_name( $student->get_enrollment_status( $this->post_id ) );
				break;

			case 'trigger':
				$trigger = $student->get_enrollment_trigger( $this->post_id );
				if ( $trigger && false !== strpos( $trigger, 'order_' ) ) {
					$tid   = $student->get_enrollment_trigger_id( $this->post_id );
					$value = $this->get_post_link( $tid, sprintf( __( 'Order #%d', 'lifterlms' ), $tid ) );
				} elseif ( $trigger && false !== strpos( $trigger, 'admin_' ) ) {
					$tid        = $student->get_enrollment_trigger_id( $this->post_id );
					$admin      = llms_get_student( $tid );
					$admin_name = $admin ? $admin->get_name() : __( '[Deleted]', 'lifterlms' );
					$value      = $this->get_user_link( $tid, sprintf( __( 'Admin: %1$s (#%2$d)', 'lifterlms' ), $admin_name, $tid ) );
				} else {
					$value = $trigger;
				}
				break;

			default:
				$value = $key;

		}// End switch().

		return $this->filter_get_data( $value, $key, $student );

	}

	/**
	 * Retrieve a list of IDs for all the users enrollments
	 *
	 * @param    obj $student  instance of LLMS_Student
	 * @return   array             array of course ids
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	private function get_enrollments( $student ) {

		$r = array();

		$page = 1;
		$skip = 0;

		while ( true ) {

			$courses = $student->get_courses(
				array(
					'limit' => 5000,
					'skip'  => 5000 * ( $page - 1 ),
				)
			);

			$r = array_merge( $courses['results'] );

			if ( ! $courses['more'] ) {
				break;
			} else {
				$page++;
			}
		}

		return $r;

	}

	/**
	 * Get the Text to be used as the placeholder in a searchable tables search input
	 *
	 * @return   string
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	public function get_table_search_form_placeholder() {
		return apply_filters( 'llms_gradebook_get_' . $this->id . '_search_placeholder', __( 'Search students by name or email...', 'lifterlms' ) );
	}

	/**
	 * Execute a query to retrieve results from the table
	 *
	 * @param    array $args  array of query args
	 * @return   void
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	public function get_results( $args = array() ) {

		$this->title = __( 'Manage Existing Enrollments', 'lifterlms' );

		if ( ! $args ) {
			$args = $this->get_args();
		}

		$args = $this->clean_args( $args );

		$this->post_id = $args['post_id'];

		if ( isset( $args['page'] ) ) {
			$this->current_page = absint( $args['page'] );
		}

		$this->filter   = isset( $args['filter'] ) ? $args['filter'] : $this->get_filter();
		$this->filterby = isset( $args['filterby'] ) ? $args['filterby'] : $this->get_filterby();

		$this->order   = isset( $args['order'] ) ? $args['order'] : $this->get_order();
		$this->orderby = isset( $args['orderby'] ) ? $args['orderby'] : $this->get_orderby();

		$sort = array();
		switch ( $this->get_orderby() ) {
			case 'enrolled':
				$sort = array(
					'date'       => $this->get_order(),
					'last_name'  => 'ASC',
					'first_name' => 'ASC',
					'id'         => 'ASC',
				);
				break;

			case 'id':
				$sort = array(
					'id' => $this->get_order(),
				);
				break;

			case 'name':
				$sort = array(
					'last_name'  => $this->get_order(),
					'first_name' => 'ASC',
					'id'         => 'ASC',
				);
				break;

			case 'status':
				$sort = array(
					'status'     => $this->get_order(),
					'last_name'  => 'ASC',
					'first_name' => 'ASC',
					'id'         => 'ASC',
				);
				break;

		}

		$query_args = array(
			'page'     => $this->get_current_page(),
			'post_id'  => $args['post_id'],
			'per_page' => apply_filters( 'llms_' . $this->id . '_table_students_per_page', 20 ),
			'sort'     => $sort,
		);

		if ( 'status' === $this->get_filterby() && 'any' !== $this->get_filter() ) {

			$query_args['statuses'] = array( $this->get_filter() );

		}

		if ( isset( $args['search'] ) ) {

			$this->search         = $args['search'];
			$query_args['search'] = $this->get_search();

		}

		$query = new LLMS_Student_Query( $query_args );

		$this->max_pages    = $query->max_pages;
		$this->is_last_page = $query->is_last_page();

		$this->tbody_data = $query->get_students();

	}


	/**
	 * Define the structure of arguments used to pass to the get_results method
	 *
	 * @return   array
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	public function set_args() {

		if ( ! $this->post_id ) {
			global $post;
			$this->post_id = ! empty( $post->ID ) ? $post->ID : null;
		}

		return array(
			'post_id' => $this->post_id,
		);

	}

	/**
	 * Define the structure of the table
	 *
	 * @return   array
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	public function set_columns() {
		$cols = array(
			'id'          => array(
				'sortable' => true,
				'title'    => __( 'ID', 'lifterlms' ),
			),
			'name'        => array(
				'sortable' => true,
				'title'    => __( 'Name', 'lifterlms' ),
			),
			'status'      => array(
				'filterable' => llms_get_enrollment_statuses(),
				'sortable'   => true,
				'title'      => __( 'Status', 'lifterlms' ),
			),
			'enrolled'    => array(
				'sortable' => true,
				'title'    => __( 'Enrollment Updated', 'lifterlms' ),
			),
			'progress'    => array(
				'sortable' => false,
				'title'    => __( 'Progress', 'lifterlms' ),
			),
			'grade'       => array(
				'sortable' => false,
				'title'    => __( 'Grade', 'lifterlms' ),
			),
			'last_lesson' => array(
				'sortable' => false,
				'title'    => __( 'Last Lesson', 'lifterlms' ),
			),
			'trigger'     => array(
				'sortable' => false,
				'title'    => __( 'Enrollment Trigger', 'lifterlms' ),
			),
			'actions'     => array(
				'sortable' => false,
				'title'    => '&nbsp;',
			),
		);
		$args = $this->get_args();
		if ( 'llms_membership' === get_post_type( $args['post_id'] ) ) {
			unset( $cols['grade'] );
			unset( $cols['progress'] );
			unset( $cols['last_lesson'] );
		}
		return $cols;
	}

}
