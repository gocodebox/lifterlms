<?php
/**
 * Students Reporting Table
 *
 * @since 3.2.0
 * @version 3.36.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Table_Students class.
 *
 * @since 3.2.0
 * @since 3.28.0 Unknown.
 * @since 3.31.0 Allow filtering the table by Course or Membership
 * @since 3.36.0 Add "Last Seen" column.
 * @since 3.36.1 Fixed "Last Seen" column displaying wrong date when the student last login date was saved as timestamp.
 */
class LLMS_Table_Students extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 *
	 * @var  string
	 */
	protected $id = 'students';

	/**
	 * Value of the field being filtered by
	 * Only applicable if $filterby is set
	 *
	 * @since 3.31.0
	 * @var  string
	 */
	protected $filter = '';

	/**
	 * Field results are filtered by
	 *
	 * @since 3.31.0
	 * @var  string
	 */
	protected $filterby = 'course_membership';

	/**
	 * Is the Table Exportable?
	 *
	 * @var  boolean
	 */
	protected $is_exportable = true;

	/**
	 * Determine if the table is filterable
	 *
	 * @since 3.31.0
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
	 * Number of records to display per page
	 *
	 * @var int
	 */
	protected $per_page = 25;

	/**
	 * Retrieve data for the columns
	 *
	 * @since 3.2.0
	 * @since 3.15.0 Unknown.
	 * @since 3.36.0 Added "Last Seen" column.
	 * @since 3.36.1 Fixed "Last Seen" column displaying wrong date when the student last login date was saved as timestamp.
	 *
	 * @param    string $key        the column id / key
	 * @param    obj    $student    Instance of the LLMS_Student
	 * @return   mixed
	 */
	public function get_data( $key, $student ) {

		switch ( $key ) {

			case 'achievements':
				$url   = LLMS_Admin_Reporting::get_current_tab_url(
					array(
						'stab'       => 'achievements',
						'student_id' => $student->get_id(),
					)
				);
				$value = '<a href="' . esc_url( $url ) . '">' . count( $student->get_achievements() ) . '</a>';
				break;

			case 'certificates':
				$url   = LLMS_Admin_Reporting::get_current_tab_url(
					array(
						'stab'       => 'certificates',
						'student_id' => $student->get_id(),
					)
				);
				$value = '<a href="' . esc_url( $url ) . '">' . count( $student->get_certificates() ) . '</a>';
				break;

			case 'completions':
				$courses = $student->get_completed_courses();
				$value   = count( $courses['results'] );
				break;

			case 'enrollments':
				$url         = LLMS_Admin_Reporting::get_current_tab_url(
					array(
						'stab'       => 'courses',
						'student_id' => $student->get_id(),
					)
				);
				$enrollments = $student->get_courses(
					array(
						'limit' => 1,
					)
				);
				$value       = '<a href="' . esc_url( $url ) . '">' . $enrollments['found'] . '</a>';
				break;

			case 'id':
				$id = $student->get_id();
				if ( current_user_can( 'list_users' ) ) {
					$value = '<a href="' . esc_url( get_edit_user_link( $id ) ) . '">' . $id . '</a>';
				} else {
					$value = $id;
				}
				break;

			case 'last_seen':
				$query = new LLMS_Events_Query(
					array(
						'actor'    => $student->get_id(),
						'per_page' => 1,
						'sort'     => array(
							'date' => 'DESC',
						),
					)
				);

				if ( $query->number_results ) {
					$events = $query->get_events();
					$last   = array_shift( $events );
					$value  = $last->get( 'date' );
				} else {
					$value = $student->get( 'last_login' );
				}

				$value = $value ? date_i18n( get_option( 'date_format' ), is_numeric( $value ) ? $value : strtotime( $value ) ) : '&ndash;';

				break;

			case 'memberships':
				$url   = LLMS_Admin_Reporting::get_current_tab_url(
					array(
						'stab'       => 'memberships',
						'student_id' => $student->get_id(),
					)
				);
				$value = '<a href="' . esc_url( $url ) . '">' . count( $student->get_membership_levels() ) . '</a>';
				break;

			case 'name':
				$first = $student->get( 'first_name' );
				$last  = $student->get( 'last_name' );

				if ( ! $first || ! $last ) {
					$value = $student->get( 'display_name' );
				} else {
					$value = $last . ', ' . $first;
				}

				$url   = LLMS_Admin_Reporting::get_current_tab_url(
					array(
						'student_id' => $student->get_id(),
					)
				);
				$value = '<a href="' . esc_url( $url ) . '">' . $value . '</a>';

				break;

			case 'overall_grade':
				$value = $student->get_overall_grade( true );
				if ( is_numeric( $value ) ) {
					$value .= '%';
				}
				break;

			case 'overall_progress':
				$value = $this->get_progress_bar_html( $student->get_overall_progress( true ) );
				break;

			case 'registered':
				$value = $student->get_registration_date();
				break;

			default:
				$value = $key;

		}// End switch().

		return $this->filter_get_data( $value, $key, $student );

	}

	/**
	 * Retrieve data for a cell in an export file
	 * Should be overridden in extending classes
	 *
	 * @param    string $key        the column id / key
	 * @param    obj    $student    Instance of the LLMS_Student
	 * @return   mixed
	 * @since    3.15.0
	 * @version  3.26.1
	 */
	public function get_export_data( $key, $student ) {

		switch ( $key ) {

			case 'id':
				$value = $student->get_id();
				break;

			case 'courses_cancelled':
			case 'courses_enrolled':
			case 'courses_expired':
				$status  = explode( '_', $key );
				$status  = array_pop( $status );
				$courses = $student->get_courses(
					array(
						'status' => $status,
					)
				);
				$titles  = array();
				foreach ( $courses['results'] as $id ) {
					$titles[] = get_the_title( $id );
				}
				$value = implode( ', ', $titles );

				break;

			case 'email':
				$value = $student->get( 'user_email' );
				break;

			case 'memberships_cancelled':
			case 'memberships_enrolled':
			case 'memberships_expired':
				$status      = explode( '_', $key );
				$status      = array_pop( $status );
				$memberships = $student->get_memberships(
					array(
						'status' => $status,
					)
				);
				$titles      = array();
				foreach ( $memberships['results'] as $id ) {
					$titles[] = get_the_title( $id );
				}
				$value = implode( ', ', $titles );

				break;

			case 'name_first':
				$value = $student->get( 'first_name' );
				break;

			case 'name_last':
				$value = $student->get( 'last_name' );
				break;

			case 'overall_grade':
				$value = $student->get_overall_grade( false );
				if ( is_numeric( $value ) ) {
					$value .= '%';
				}
				break;

			case 'overall_progress':
				$value = $student->get_overall_progress( false ) . '%';
				break;

			case 'billing_address_1':
			case 'billing_address_2':
			case 'billing_city':
			case 'billing_state':
			case 'billing_zip':
			case 'billing_country':
			case 'phone':
				$value = $student->get( $key );
				break;

			default:
				$value = $this->get_data( $key, $student );

		}// End switch().

		return $this->filter_get_data( $value, $key, $student, 'export' );

	}

	/**
	 * Get the Text to be used as the placeholder in a searchable tables search input
	 *
	 * @return   string
	 * @since    3.2.0
	 * @version  3.15.0
	 */
	public function get_table_search_form_placeholder() {
		return apply_filters( 'llms_table_get_' . $this->id . '_search_placeholder', __( 'Search students by name or email...', 'lifterlms' ) );
	}

	/**
	 * Get HTML for the filters displayed in the head of the table
	 *
	 * This overrides the LLMS_Admin_Table method.
	 *
	 * @since 3.31.0
	 *
	 * @return string
	 */
	public function get_table_filters_html() {
		$select_id = sprintf( '%1$s-%2$s-filter', $this->id, 'course-membership' );

		ob_start();
		?>
		<div class="llms-table-filters">
			<div class="llms-table-filter-wrap">
				<label class="screen-reader-text" for="<?php echo $select_id; ?>">
					<?php _e( 'Choose Course/Membership', 'lifterlms' ); ?>
				</label>
				<select data-post-type="llms_membership,course" class="llms-posts-select2 llms-table-filter" id="<?php echo $select_id; ?>" name="course_membership" style="min-width:200px;max-width:500px;"></select>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Retrieve an array of query arguments to pass to the LLMS_Student_Query
	 *
	 * @since 3.28.0
	 * @since 3.31.0 Added logic to setup the query args in order to allow the filtering by Course or Membership.
	 *
	 * @return array
	 */
	private function get_query_args() {

		$query_args = array(
			'page'     => $this->get_current_page(),
			'post_id'  => array(),
			'per_page' => $this->get_per_page(),
			'sort'     => $this->get_sort(),
		);

		if ( 'status' === $this->get_filterby() && 'any' !== $this->get_filter() ) {

			$query_args['statuses'] = array( $this->get_filter() );

		} elseif ( 'course_membership' === $this->get_filterby() && '' !== $this->get_filter() ) {

			$query_args['post_id']  = absint( $this->get_filter() );
			$query_args['statuses'] = 'enrolled';

		}

		if ( $this->get_search() ) {
			$query_args['search'] = $this->get_search();
		}

		return $query_args;

	}

	/**
	 * Execute a query to retrieve results from the table
	 *
	 * @param    array $args  array of query args
	 * @return   void
	 * @since    3.2.0
	 * @version  3.28.0
	 */
	public function get_results( $args = array() ) {

		// Current user can't access this report.
		if ( ! current_user_can( 'view_others_lifterlms_reports' ) && ! current_user_can( 'view_lifterlms_reports' ) ) {
			return;
		}

		$this->parse_args( $args );

		$query_args = $this->get_query_args();

		if ( current_user_can( 'view_others_lifterlms_reports' ) ) {

			$query = new LLMS_Student_Query( $query_args );

		} elseif ( current_user_can( 'view_lifterlms_reports' ) ) {

			$instructor = llms_get_instructor();
			if ( ! $instructor ) {
				return;
			}
			$query = $instructor->get_students( $query_args );

		}

		$this->max_pages    = $query->max_pages;
		$this->is_last_page = $query->is_last_page();

		$this->tbody_data = $query->get_students();

	}

	/**
	 * Setup the array of sort arguments to pass to the LLMS_Student_Query for the table
	 *
	 * @return  array
	 * @since   3.28.0
	 * @version 3.28.0
	 */
	private function get_sort() {

		$sort = array();
		switch ( $this->get_orderby() ) {

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

			case 'overall_grade':
				$sort = array(
					'overall_grade' => $this->get_order(),
					'last_name'     => 'ASC',
					'first_name'    => 'ASC',
					'id'            => 'ASC',
				);
				break;

			case 'overall_progress':
				$sort = array(
					'overall_progress' => $this->get_order(),
					'last_name'        => 'ASC',
					'first_name'       => 'ASC',
					'id'               => 'ASC',
				);
				break;

			case 'registered':
				$sort = array(
					'registered' => $this->get_order(),
					'last_name'  => 'ASC',
					'first_name' => 'ASC',
					'id'         => 'ASC',
				);
				break;

		}// End switch().

		return $sort;

	}

	/**
	 * Parse arguments passed to get_results() method & setup table class variables.
	 *
	 * @since 3.28.0
	 * @since 3.31.0 Added logic to parse 'filterby' and 'filter' args when this table is filterable.
	 *
	 * @param   array $args array of arguments.
	 * @return  void
	 */
	protected function parse_args( $args = array() ) {

		if ( ! $args ) {
			$args = $this->get_args();
		}

		$args = $this->clean_args( $args );

		if ( isset( $args['page'] ) ) {
			$this->current_page = absint( $args['page'] );
		}

		$this->order   = isset( $args['order'] ) ? $args['order'] : $this->get_order();
		$this->orderby = isset( $args['orderby'] ) ? $args['orderby'] : $this->get_orderby();

		$this->per_page = isset( $args['per_page'] ) ? $args['per_page'] : $this->get_per_page();

		if ( $this->is_filterable ) {
			$this->filterby = isset( $args['filterby'] ) ? $args['filterby'] : $this->get_filterby();
			$this->filter   = isset( $args['filter'] ) ? $args['filter'] : $this->get_filter();
		}

		if ( isset( $args['search'] ) ) {
			$this->search = $args['search'];
		}

	}

	/**
	 * Define the structure of arguments used to pass to the get_results method
	 *
	 * @return   array
	 * @since    2.3.0
	 * @version  3.28.0
	 */
	public function set_args() {
		return array(
			'per_page' => apply_filters( 'llms_table_' . $this->id . '_per_page', $this->per_page ),
		);
	}

	/**
	 * Define the structure of the table
	 *
	 * @since 3.2.0
	 * @since 3.15.0 Unknown.
	 * @since 3.36.0 Add "Last Seen" column.
	 *
	 * @return   array
	 */
	public function set_columns() {
		return array(
			'id'                    => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'ID', 'lifterlms' ),
			),
			'email'                 => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Email', 'lifterlms' ),
			),
			'name'                  => array(
				'sortable' => true,
				'title'    => __( 'Name', 'lifterlms' ),
			),
			'name_last'             => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Last Name', 'lifterlms' ),
			),
			'name_first'            => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'First Name', 'lifterlms' ),
			),
			'registered'            => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'Registration Date', 'lifterlms' ),
			),
			'last_seen'             => array(
				'exportable' => true,
				'sortable'   => false,
				'title'      => __( 'Last Seen', 'lifterlms' ),
			),
			'overall_progress'      => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'Progress', 'lifterlms' ),
			),
			'overall_grade'         => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'Grade', 'lifterlms' ),
			),
			'enrollments'           => array(
				'sortable' => false,
				'title'    => __( 'Enrollments', 'lifterlms' ),
			),
			'completions'           => array(
				'sortable' => false,
				'title'    => __( 'Completions', 'lifterlms' ),
			),
			'certificates'          => array(
				'sortable' => false,
				'title'    => __( 'Certificates', 'lifterlms' ),
			),
			'achievements'          => array(
				'sortable' => false,
				'title'    => __( 'Achievements', 'lifterlms' ),
			),
			'memberships'           => array(
				'sortable' => false,
				'title'    => __( 'Memberships', 'lifterlms' ),
			),
			'billing_address_1'     => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Billing Address 1', 'lifterlms' ),
			),
			'billing_address_2'     => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Billing Address 2', 'lifterlms' ),
			),
			'billing_city'          => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Billing City', 'lifterlms' ),
			),
			'billing_state'         => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Billing State', 'lifterlms' ),
			),
			'billing_zip'           => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Billing Zip', 'lifterlms' ),
			),
			'billing_country'       => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Billing Country', 'lifterlms' ),
			),
			'phone'                 => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Phone', 'lifterlms' ),
			),
			'courses_enrolled'      => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Courses (Enrolled)', 'lifterlms' ),
			),
			'courses_cancelled'     => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Courses (Cancelled)', 'lifterlms' ),
			),
			'courses_expired'       => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Courses (Expired)', 'lifterlms' ),
			),
			'memberships_enrolled'  => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Memberships (Enrolled)', 'lifterlms' ),
			),
			'memberships_cancelled' => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Memberships (Cancelled)', 'lifterlms' ),
			),
			'memberships_expired'   => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Memberships (Expired)', 'lifterlms' ),
			),
		);
	}

	/**
	 * Set the table's title.
	 *
	 * @return  string
	 * @since   3.28.0
	 * @version 3.28.0
	 */
	protected function set_title() {
		return __( 'Students', 'lifterlms' );
	}

}
