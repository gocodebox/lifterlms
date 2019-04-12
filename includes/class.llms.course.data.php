<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Query data about a course
 * @since    3.15.0
 * @version  3.17.2
 */
class LLMS_Course_Data {

	protected $dates = array();

	/**
	 * Constructor
	 * @param    int     $course_id  WP Post ID of the course
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function __construct( $course_id ) {

		$this->course_id = $course_id;
		$this->course = llms_get_post( $this->course_id );

	}

	/**
	 * Allow dates and timestamps to be passed into various data functions
	 * @param    mixed     $date  date string or timestamp
	 * @return   int
	 * @since    3.15.0
	 * @version  3.16.0
	 */
	protected function strtotime( $date ) {
		if ( ! is_numeric( $date ) ) {
			$date = date( 'U', strtotime( $date ) );
		}
		return $date;
	}

	/**
	 * Retrieve an array of all post ids in the course
	 * Includes course id, all section ids, all lesson ids, and all quiz ids
	 * @return   array
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	private function get_all_ids() {
		return array_merge(
			array( $this->course_id ),
			$this->course->get_sections( 'ids' ),
			$this->course->get_lessons( 'ids' ),
			$this->course->get_quizzes()
		);
	}

	/**
	 * Retrieve a start or end date based on the period
	 * @param    string     $period  period [current|previous]
	 * @param    string     $date    date type [start|end]
	 * @return   string
	 * @since    3.15.0
	 * @version  3.16.0
	 */
	protected function get_date( $period, $date ) {

		return date( 'Y-m-d H:i:s', $this->dates[ $period ][ $date ] );

	}

	/**
	 * Set the dates pased on a date range period
	 * @param    string     $period  date range period
	 * @return   void
	 * @since    3.15.0
	 * @version  3.17.2
	 */
	public function set_period( $period = 'today' ) {

		$now = current_time( 'timestamp' );

		switch ( $period ) {

			case 'all_time':
				$curr_start = 0;
				$curr_end = $now;

				$prev_start = 0;
				$prev_end = $now;
			break;

			case 'last_year':
				$curr_start = strtotime( 'first day of january last year', $now );
				$curr_end = strtotime( 'last day of december last year', $now );

				$prev_start = strtotime( 'first day of january last year', $curr_start );
				$prev_end = strtotime( 'last day of december last year', $curr_start );
			break;

			case 'year':
				$curr_start = strtotime( 'first day of january this year', $now );
				$curr_end = strtotime( 'last day of december this year', $now );

				$prev_start = strtotime( 'first day of january last year', $now );
				$prev_end = strtotime( 'last day of december last year', $now );
			break;

			case 'last_month':
				$curr_start = strtotime( 'first day of previous month', $now );
				$curr_end = strtotime( 'last day of previous month', $now );

				$prev_start = strtotime( 'first day of previous month', $curr_start );
				$prev_end = strtotime( 'last day of previous month', $curr_start );
			break;

			case 'month':
				$curr_start = strtotime( 'first day of this month', $now );
				$curr_end = strtotime( 'last day of this month', $now );

				$prev_start = strtotime( 'first day of previous month', $now );
				$prev_end = strtotime( 'last day of previous month', $now );
			break;

			case 'last_week':
				$curr_start = strtotime( 'monday this week', $now - WEEK_IN_SECONDS );
				$curr_end = $now;

				$prev_start = strtotime( 'monday previous week', $curr_start - WEEK_IN_SECONDS );
				$prev_end = $curr_start - DAY_IN_SECONDS;
			break;

			case 'week':
				$curr_start = strtotime( 'monday this week', $now );
				$curr_end = $now;

				$prev_start = strtotime( 'monday previous week', $now );
				$prev_end = $curr_start - DAY_IN_SECONDS;
			break;

			case 'yesterday':
				$curr_start = $now - DAY_IN_SECONDS;
				$curr_end = $curr_start;

				$prev_start = $curr_start - DAY_IN_SECONDS;
				$prev_end = $prev_start;
			break;

			case 'today':
			default:

				$curr_start = $now;
				$curr_end = $now;

				$prev_start = $now - DAY_IN_SECONDS;
				$prev_end = $prev_start;

		}// End switch().

		$this->dates = array(
			'current' => array(
				'start' => strtotime( 'midnight', $curr_start ),
				'end' => strtotime( 'tomorrow', $curr_end ) - 1,
			),
			'previous' => array(
				'start' => strtotime( 'midnight', $prev_start ),
				'end' => strtotime( 'tomorrow', $prev_end ) - 1,
			),
		);

	}

	/**
	 * Retrieve # of course completions within the period
	 * @param    string     $period  date period [current|previous]
	 * @return   int
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_completions( $period = 'current' ) {

		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "
			SELECT DISTINCT COUNT( user_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_value = 'yes'
			  AND meta_key = '_is_complete'
			  AND post_id = %d
			  AND updated_date BETWEEN %s AND %s
			",
			$this->course_id,
			$this->get_date( $period, 'start' ),
			$this->get_date( $period, 'end' )
		) );

	}

	/**
	 * retrive # of course enrollments within the period
	 * @param    string     $period  date period [current|previous]
	 * @return   int
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_enrollments( $period = 'current' ) {

		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "
			SELECT DISTINCT COUNT( user_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_value = 'yes'
			  AND meta_key = '_start_date'
			  AND post_id = %d
			  AND updated_date BETWEEN %s AND %s
			",
			$this->course_id,
			$this->get_date( $period, 'start' ),
			$this->get_date( $period, 'end' )
		) );

	}

	/**
	 * retrive # of engagements related to the course awarded within the period
	 * @param    string     $type    engagement type [email|certificate|achievement]
	 * @param    string     $period  date period [current|previous]
	 * @return   int
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_engagements( $type, $period = 'current' ) {

		global $wpdb;

		$ids = implode( ',', $this->get_all_ids() );

		return $wpdb->get_var( $wpdb->prepare( "
			SELECT DISTINCT COUNT( user_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_key = %s
			  AND post_id IN ( {$ids} )
			  AND updated_date BETWEEN %s AND %s
			",
			'_' . $type,
			$this->get_date( $period, 'start' ),
			$this->get_date( $period, 'end' )
		) );

	}

	/**
	 * retrive # of lessons completed within the period
	 * @param    string     $period  date period [current|previous]
	 * @return   int
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_lesson_completions( $period = 'current' ) {

		global $wpdb;

		$lessons = implode( ',', $this->course->get_lessons( 'ids' ) );

		return $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT( * )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_value = 'yes'
			  AND meta_key = '_is_complete'
			  AND post_id IN ( {$lessons} )
			  AND updated_date BETWEEN %s AND %s
			",
			$this->get_date( $period, 'start' ),
			$this->get_date( $period, 'end' )
		) );

	}

	/**
	 * retrive # of orders placed for the course within the period
	 * @param    string     $period  date period [current|previous]
	 * @return   int
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_orders( $period = 'current' ) {

		$query = $this->orders_query( array(
			array(
				'after'     => $this->get_date( $period, 'start' ),
				'before'    => $this->get_date( $period, 'end' ),
				'inclusive' => true,
			),
		), 1 );
		return $query->found_posts;

	}

	/**
	 * retrive total amount of transactions related to orders for the course completed within the period
	 * @param    string     $period  date period [current|previous]
	 * @return   float
	 * @since    3.15.0
	 * @version  3.16.0
	 */
	public function get_revenue( $period ) {

		$query = $this->orders_query( -1 );
		$order_ids = wp_list_pluck( $query->posts, 'ID' );

		$revenue = 0;

		if ( $order_ids ) {

			$order_ids = implode( ',', $order_ids );

			global $wpdb;
			$revenue = $wpdb->get_var( $wpdb->prepare(
				"SELECT SUM( m2.meta_value )
				 FROM $wpdb->posts AS p
				 LEFT JOIN $wpdb->postmeta AS m1 ON m1.post_id = p.ID AND m1.meta_key = '_llms_order_id' -- join for the ID
				 LEFT JOIN $wpdb->postmeta AS m2 ON m2.post_id = p.ID AND m2.meta_key = '_llms_amount'-- get the actual amounts
				 WHERE p.post_type = 'llms_transaction'
				   AND p.post_status = 'llms-txn-succeeded'
				   AND m1.meta_value IN ({$order_ids})
				   AND p.post_modified BETWEEN %s AND %s
				;",
				$this->get_date( $period, 'start' ),
				$this->get_date( $period, 'end' )
			) );

			if ( is_null( $revenue ) ) {
				$revenue = 0;
			}
		}

		return apply_filters( 'llms_course_data_get_revenue', $revenue, $period, $this );

	}

	/**
	 * Retrieve the number of unenrollments on a given date
	 * @param    mixed     $start  date string or timestamp
	 * @param    mixed     $end    date string or timestamp
	 * @return   int
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_unenrollments( $period = 'current' ) {

		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "
			SELECT DISTINCT COUNT( user_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_value != 'enrolled'
			  AND meta_key = '_status'
			  AND post_id = %d
			  AND updated_date BETWEEN %s AND %s
			",
			$this->course_id,
			$this->get_date( $period, 'start' ),
			$this->get_date( $period, 'end' )
		) );

	}

	/**
	 * Execute a WP Query to retrieve orders within the given date range
	 * @param    int        $num_orders  number of orders to retrieve
	 * @param    array      $dates       date range (passed to WP_Query['date_query'])
	 * @return   obj
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	private function orders_query( $num_orders = 1, $dates = array() ) {

		$args = array(
			'post_type' => 'llms_order',
			'post_status' => array( 'llms-active', 'llms-complete' ),
			'posts_per_page' => $num_orders,
			'meta_key' => '_llms_product_id',
			'meta_value' => $this->course_id,
		);

		if ( $dates ) {
			$args['date_query'] = $dates;
		}

		$query = new WP_Query( $args );

		return $query;

	}

	/**
	 * Retrieve recent LLMS_User_Postmeta for the course
	 * @return   array
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function recent_events() {

		$query = new LLMS_Query_User_Postmeta( array(
			'per_page' => 10,
			'post_id' => $this->course_id,
			'types' => 'all',
		) );

		return $query->get_metas();

	}

}
