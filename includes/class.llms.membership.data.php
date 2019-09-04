<?php
/**
 * Query data about a membership.
 *
 * @since 3.32.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Query data about a membership.
 *
 * @since 3.32.0
 * @since 3.35.0 Sanitize post ids from WP_Query before using for a new DB query.
 */
class LLMS_Membership_Data extends LLMS_Abstract_Post_Data {

	/**
	 * Retrieve # of membership enrollments within the period.
	 *
	 * @since 3.32.0
	 *
	 * @param string $period Optional.Date period [current|previous]. Default 'current'.
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
		);

	}

	/**
	 * Retrieve # of engagements related to the membership awarded within the period.
	 *
	 * @since 3.32.0
	 *
	 * @param string $type   Engagement type [email|certificate|achievement].
	 * @param string $period Optional. Date period [current|previous]. Default 'current'.
	 * @return int
	 */
	public function get_engagements( $type, $period = 'current' ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT DISTINCT COUNT( user_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_key = %s
			  AND post_id = %d
			  AND updated_date BETWEEN %s AND %s
			",
				'_' . $type,
				$this->post_id,
				$this->get_date( $period, 'start' ),
				$this->get_date( $period, 'end' )
			)
		);

	}

	/**
	 * Retrieve # of orders placed for the membership within the period.
	 *
	 * @since 3.32.0
	 *
	 * @param string $period Optional. Date period [current|previous]. Default 'current'.
	 * @return int
	 */
	public function get_orders( $period = 'current' ) {

		$query = $this->orders_query(
			array(
				array(
					'after'     => $this->get_date( $period, 'start' ),
					'before'    => $this->get_date( $period, 'end' ),
					'inclusive' => true,
				),
			),
			1
		);
		return $query->found_posts;

	}

	/**
	 * Retrieve total amount of transactions related to orders for the course completed within the period.
	 *
	 * @since 3.32.0
	 * @since 3.35.0 Sanitize post ids from WP_Query before using for a new DB query.
	 *
	 * @param string $period Optional. Date period [current|previous]. Default 'current'.
	 * @return float
	 */
	public function get_revenue( $period ) {

		$query     = $this->orders_query( -1 );
		$order_ids = wp_list_pluck( $query->posts, 'ID' );

		$revenue = 0;

		if ( $order_ids ) {

			$order_ids = implode( ',', array_map( 'absint', $order_ids ) );

			global $wpdb;

			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- ID list is sanitized via `absint()` earlier in this method.
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
			);
			// phpcs:enabled WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			if ( is_null( $revenue ) ) {
				$revenue = 0;
			}
		}

		return apply_filters( 'llms_membership_data_get_revenue', $revenue, $period, $this );

	}

	/**
	 * Retrieve the number of unenrollments on a given date.
	 *
	 * @since 3.32.0
	 *
	 * @param string $period Optional. Date period [current|previous]. Default 'current'.
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
		);

	}

	/**
	 * Execute a WP Query to retrieve orders within the given date range.
	 *
	 * @since 3.32.0
	 *
	 * @param int   $num_orders Optional. Number of orders to retrieve. Default 1.
	 * @param array $dates      Optiona. Date range (passed to WP_Query['date_query']). Default empty array.
	 * @return obj
	 */
	private function orders_query( $num_orders = 1, $dates = array() ) {

		$args = array(
			'post_type'      => 'llms_order',
			'post_status'    => array( 'llms-active', 'llms-complete' ),
			'posts_per_page' => $num_orders,
			'meta_key'       => '_llms_product_id',
			'meta_value'     => $this->post_id,
		);

		if ( $dates ) {
			$args['date_query'] = $dates;
		}

		$query = new WP_Query( $args );

		return $query;

	}

}
