<?php
/**
 * Query data about a course
 *
 * @package LifterLMS/Classes
 *
 * @since 3.15.0
 * @version 5.10.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Query data about a course
 *
 * @since 3.15.0
 * @since 3.30.3 Explicitly define class properties.
 * @since 3.31.0 Extends LLMS_Abstract_Post_Data.
 * @since 4.0.0 Remove previously deprecated class properties `$course` and `$course_id`.
 */
class LLMS_Course_Data extends LLMS_Abstract_Post_Data {

	public $course_id;

	public $course;

	/**
	 * Constructor
	 *
	 * @since 3.15.0
	 *
	 * @param int $course_id WP Post ID of the course
	 */
	public function __construct( $course_id ) {

		$this->course_id = $course_id;
		$this->course    = llms_get_post( $this->course_id );
		parent::__construct( $course_id );
	}

	/**
	 * Retrieve an array of all post ids in the course
	 *
	 * Includes course id, all section ids, all lesson ids, and all quiz ids.
	 *
	 * @since 3.15.0
	 * @since 3.31.0 Use $this->post_id instead of deprecated $this->course_id.
	 *
	 * @return array
	 */
	private function get_all_ids() {
		return array_merge(
			array( $this->post_id ),
			$this->post->get_sections( 'ids' ),
			$this->post->get_lessons( 'ids' ),
			$this->post->get_quizzes()
		);
	}

	/**
	 * Retrieve # of course completions within the period
	 *
	 * @since 3.15.0
	 * @since 3.31.0 Use $this->post_id instead of deprecated $this->course_id.
	 *
	 * @param string $period Optional. Date period [current|previous]. Default is 'current'.
	 * @return int
	 */
	public function get_completions( $period = 'current' ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT DISTINCT COUNT( user_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_value = 'yes'
			  AND meta_key = '_is_complete'
			  AND post_id = %d
			  AND updated_date BETWEEN %s AND %s
			",
				$this->post_id,
				$this->get_date( $period, 'start' ),
				$this->get_date( $period, 'end' )
			)
		);// db call ok; no-cache ok.
	}

	/**
	 * Retrieve # of course enrollments within the period
	 *
	 * @since 3.15.0
	 * @since 3.31.0 Use $this->post_id instead of deprecated $this->course_id.
	 *
	 * @param string $period Optional. Date period [current|previous]. Default is 'current'.
	 * @return int
	 */
	public function get_enrollments( $period = 'current' ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT DISTINCT COUNT( user_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_value = 'yes'
			  AND meta_key = '_start_date'
			  AND post_id = %d
			  AND updated_date BETWEEN %s AND %s
			",
				$this->post_id,
				$this->get_date( $period, 'start' ),
				$this->get_date( $period, 'end' )
			)
		);// db call ok; no-cache ok.
	}

	/**
	 * Retrieve # of engagements related to the course awarded within the period
	 *
	 * @since 3.15.0
	 *
	 * @param string $type   Engagement type [email|certificate|achievement].
	 * @param string $period Optional. Date period [current|previous]. Default is 'current'.
	 * @return int
	 */
	public function get_engagements( $type, $period = 'current' ) {

		global $wpdb;

		$ids = implode( ',', $this->get_all_ids() );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT DISTINCT COUNT( user_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_key = %s
			  AND post_id IN ( {$ids} )
			  AND updated_date BETWEEN %s AND %s
			",
				'_' . $type,
				$this->get_date( $period, 'start' ),
				$this->get_date( $period, 'end' )
			)
		);// db call ok; no-cache ok.
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Retrieves and returns the number of lessons completed within the period.
	 *
	 * @since 3.15.0
	 * @since 5.10.0 Fixed issue when the course has no lessons.
	 *
	 * @param string $period Optional. Date period [current|previous]. Default is 'current'.
	 * @return int
	 */
	public function get_lesson_completions( $period = 'current' ) {

		global $wpdb;

		$lessons = implode( ',', $this->post->get_lessons( 'ids' ) );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// Return early for courses without any lessons.
		if ( empty( $lessons ) ) {
			return 0;
		}

		return $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT COUNT( * )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_value = 'yes'
			  AND meta_key = '_is_complete'
			  AND post_id IN ( {$lessons} )
			  AND updated_date BETWEEN %s AND %s
			",
				$this->get_date( $period, 'start' ),
				$this->get_date( $period, 'end' )
			)
		);// db call ok; no-cache ok.
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Retrieve # of orders placed for the course within the period
	 *
	 * @since 3.15.0
	 * @since 4.21.0 Fixed order params passed to the `$this->orders_query()` method.
	 *
	 * @param string $period Optional. Date period [current|previous]. Default is 'current'.
	 * @return int
	 */
	public function get_orders( $period = 'current' ) {

		$query = $this->orders_query(
			1,
			array(
				array(
					'after'     => $this->get_date( $period, 'start' ),
					'before'    => $this->get_date( $period, 'end' ),
					'inclusive' => true,
				),
			)
		);
		return $query->found_posts;
	}

	/**
	 * Retrieve total amount of transactions related to orders for the course completed within the period
	 *
	 * @since 3.15.0
	 *
	 * @param string $period Date period [current|previous].
	 * @return float
	 */
	public function get_revenue( $period ) {

		$query     = $this->orders_query( -1 );
		$order_ids = wp_list_pluck( $query->posts, 'ID' );

		$revenue = 0;

		if ( $order_ids ) {

			$order_ids = implode( ',', $order_ids );

			global $wpdb;
			$revenue = $wpdb->get_var(
				$wpdb->prepare(
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
				)
			);// db call ok; no-cache ok.

			if ( is_null( $revenue ) ) {
				$revenue = 0;
			}
		}

		return apply_filters( 'llms_course_data_get_revenue', $revenue, $period, $this );
	}

	/**
	 * Retrieve the number of unenrollments on a given date.
	 *
	 * @since 3.15.0
	 *
	 * @param  tring $period Optional. Date period [current|previous]. Default 'current'.
	 * @return int
	 */
	public function get_unenrollments( $period = 'current' ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT DISTINCT COUNT( user_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_value != 'enrolled'
			  AND meta_key = '_status'
			  AND post_id = %d
			  AND updated_date BETWEEN %s AND %s
			",
				$this->post_id,
				$this->get_date( $period, 'start' ),
				$this->get_date( $period, 'end' )
			)
		);// db call ok; no-cache ok.
	}

	/**
	 * Execute a WP Query to retrieve orders within the given date range
	 *
	 * @since 3.15.0
	 * @since 4.21.0 Fixed the post status for completed orders.
	 *
	 * @param int   $num_orders Optional. Number of orders to retrieve. Default is `1`.
	 * @param array $dates      Optional. Date range (passed to WP_Query['date_query']). Default is empty array.
	 * @return WP_Query
	 */
	private function orders_query( $num_orders = 1, $dates = array() ) {

		$args = array(
			'post_type'      => 'llms_order',
			'post_status'    => array( 'llms-active', 'llms-completed' ),
			'posts_per_page' => $num_orders,
			'meta_key'       => '_llms_product_id',
			'meta_value'     => $this->post_id,
		);

		if ( ! empty( $dates ) ) {
			$args['date_query'] = $dates;
		}

		return new WP_Query( $args );
	}
}
