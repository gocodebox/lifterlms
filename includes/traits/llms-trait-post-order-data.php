<?php
/**
 * LLMS_Trait_Post_Order_Data
 *
 * @package LifterLMS/Traits
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Order and revenue reporting utility methods intended to be used by classes that
 * extend {@see LLMS_Abstract_Post_Data}.
 *
 * @since [version]
 */
trait LLMS_Trait_Post_Order_Data {

	/**
	 * Retrieve total amount of transactions related to orders for the course completed within the period.
	 *
	 * @since [version]
	 *
	 * @param string $period Optional. Date period [current|previous]. Default 'current'.
	 * @return float
	 */
	public function get_revenue( $period ) {

		$query     = $this->orders_query( -1 );
		$order_ids = wp_list_pluck( $query->posts, 'ID' );

		$revenue = null;
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
		}

		$revenue = is_null( $revenue ) ? 0.0 : $revenue; 
		$type = llms_strip_prefixes( $this->post->get( 'type' ) );

		/**
		 * Filters the earned revenue displayed on reporting screens for the given post.
		 *
		 * The dynamic portion of this hook, {$type}, refers to the current, unprefixed, post type
		 * being displayed. For example "course" or "membership".
		 * 
		 * @since [version]
		 *
		 * @param float                         $revenue The total revenue.
		 * @param string                        $period  The view string for the displayed period, either "current" or "previous".
		 * @param LLMS_Abstract_Post_Order_Data $data    Instance of the post data class.
		 */
		return apply_filters( "llms_{$type}_data_get_revenue", $revenue, $period, $this );

	}

	/**
	 * Retrieve # of orders placed for the membership within the period.
	 *
	 * @since [version]
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
	 * Execute a WP Query to retrieve orders within the given date range
	 *
	 * @since [version]
	 *
	 * @param int   $num_orders Optional. Number of orders to retrieve. Default is `1`.
	 * @param array $dates      Optional. Date range (passed to WP_Query['date_query']). Default is empty array.
	 * @return WP_Query
	 */
	protected function orders_query( $num_orders = 1, $dates = array() ) {

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