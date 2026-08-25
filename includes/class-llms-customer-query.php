<?php
/**
 * LLMS_Customer_Query class file
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Query customers (users with at least one order) with commerce aggregates.
 *
 * @since [version]
 */
class LLMS_Customer_Query extends LLMS_Database_Query {

	/**
	 * Identify the extending query.
	 *
	 * @var string
	 */
	protected $id = 'customer';

	/**
	 * Allowed ORDER BY fields.
	 *
	 * @var string[]
	 */
	protected $allowed_sort_fields = array(
		'user_id',
		'name',
		'ltv',
		'order_count',
		'aov',
		'last_order',
		'first_order',
		'registered',
		'active_recurring_count',
	);

	/**
	 * Retrieve default arguments.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function get_default_args() {

		$args = array(
			'segment' => 'all',
			'sort'    => array(
				'last_order' => 'DESC',
				'user_id'    => 'DESC',
			),
		);

		$args = wp_parse_args( $args, parent::get_default_args() );

		/**
		 * Filters the customer query default args.
		 *
		 * @since [version]
		 *
		 * @param array               $args            Default arguments.
		 * @param LLMS_Customer_Query $customer_query Query instance.
		 */
		return apply_filters( 'llms_customer_query_default_args', $args, $this );
	}

	/**
	 * Parse submitted arguments.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	protected function parse_args() {

		$segments = array_keys( llms_get_customer_segments() );
		$segment  = $this->arguments['segment'];
		if ( ! in_array( $segment, $segments, true ) ) {
			$this->arguments['segment'] = 'all';
		}
	}

	/**
	 * Prepare the SQL for the query.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function prepare_query() {

		$base = "SELECT {$this->sql_select_list()}
			FROM {$this->sql_from()}
			{$this->sql_where()}";

		if ( $this->get( 'count_only' ) ) {
			return "SELECT COUNT(*) AS total FROM ( {$base} ) AS counted";
		}

		if ( ! $this->get( 'no_found_rows' ) ) {
			$this->count_query = "SELECT COUNT(*) FROM ( {$base} ) AS counted";
		}

		return "{$base}
			{$this->sql_orderby()}
			{$this->sql_limit()}";
	}

	/**
	 * SELECT list for the outer aggregated customer rows.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function sql_select_list() {

		$columns = 'customers.user_id,
			customers.order_count,
			customers.gross,
			customers.refunded,
			customers.ltv,
			customers.aov,
			customers.first_order,
			customers.last_order,
			customers.active_recurring_count,
			u.user_email,
			u.user_registered AS registered,
			u.display_name,
			m_first.meta_value AS first_name,
			m_last.meta_value AS last_name';

		/**
		 * Filters customer query SELECT columns.
		 *
		 * @since [version]
		 *
		 * @param string              $columns         SELECT columns.
		 * @param LLMS_Customer_Query $customer_query Query instance.
		 */
		return apply_filters( 'llms_customer_query_select_columns', $this->sql_select_columns( $columns ), $this );
	}

	/**
	 * FROM clause: aggregated customers subquery joined to users.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function sql_from() {

		global $wpdb;

		$revenue_subquery = "
			SELECT
				order_id.meta_value AS order_id,
				SUM( CAST( amount.meta_value AS DECIMAL(20,8) ) ) AS gross,
				SUM(
					CASE
						WHEN txns.post_status = 'llms-txn-refunded'
						THEN COALESCE( CAST( refund.meta_value AS DECIMAL(20,8) ), 0 )
						ELSE 0
					END
				) AS refunded
			FROM {$wpdb->posts} AS txns
			INNER JOIN {$wpdb->postmeta} AS order_id
				ON order_id.post_id = txns.ID
				AND order_id.meta_key = '_llms_order_id'
			INNER JOIN {$wpdb->postmeta} AS amount
				ON amount.post_id = txns.ID
				AND amount.meta_key = '_llms_amount'
			LEFT JOIN {$wpdb->postmeta} AS refund
				ON refund.post_id = txns.ID
				AND refund.meta_key = '_llms_refund_amount'
			WHERE txns.post_type = 'llms_transaction'
				AND txns.post_status IN ( 'llms-txn-succeeded', 'llms-txn-refunded' )
			GROUP BY order_id.meta_value
		";

		$customers_subquery = "
			SELECT
				CAST( user_meta.meta_value AS UNSIGNED ) AS user_id,
				COUNT( DISTINCT orders.ID ) AS order_count,
				COALESCE( SUM( order_rev.gross ), 0 ) AS gross,
				COALESCE( SUM( order_rev.refunded ), 0 ) AS refunded,
				COALESCE( SUM( order_rev.gross ), 0 ) - COALESCE( SUM( order_rev.refunded ), 0 ) AS ltv,
				CASE
					WHEN COUNT( DISTINCT orders.ID ) > 0
					THEN ( COALESCE( SUM( order_rev.gross ), 0 ) - COALESCE( SUM( order_rev.refunded ), 0 ) ) / COUNT( DISTINCT orders.ID )
					ELSE 0
				END AS aov,
				MIN( orders.post_date ) AS first_order,
				MAX( orders.post_date ) AS last_order,
				SUM(
					CASE
						WHEN order_type.meta_value = 'recurring'
							AND orders.post_status IN ( 'llms-active', 'llms-pending-cancel' )
						THEN 1 ELSE 0
					END
				) AS active_recurring_count
			FROM {$wpdb->posts} AS orders
			INNER JOIN {$wpdb->postmeta} AS user_meta
				ON user_meta.post_id = orders.ID
				AND user_meta.meta_key = '_llms_user_id'
				AND user_meta.meta_value REGEXP '^[0-9]+$'
				AND CAST( user_meta.meta_value AS UNSIGNED ) > 0
			LEFT JOIN {$wpdb->postmeta} AS order_type
				ON order_type.post_id = orders.ID
				AND order_type.meta_key = '_llms_order_type'
			LEFT JOIN ( {$revenue_subquery} ) AS order_rev
				ON order_rev.order_id = orders.ID
			WHERE orders.post_type = 'llms_order'
				AND orders.post_status NOT IN ( 'trash', 'auto-draft' )
			GROUP BY CAST( user_meta.meta_value AS UNSIGNED )
		";

		$sql = "( {$customers_subquery} ) AS customers
			INNER JOIN {$wpdb->users} AS u ON u.ID = customers.user_id
			LEFT JOIN {$wpdb->usermeta} AS m_first ON m_first.user_id = u.ID AND m_first.meta_key = 'first_name'
			LEFT JOIN {$wpdb->usermeta} AS m_last ON m_last.user_id = u.ID AND m_last.meta_key = 'last_name'";

		/**
		 * Filters the customer query FROM clause.
		 *
		 * @since [version]
		 *
		 * @param string              $sql             FROM clause.
		 * @param LLMS_Customer_Query $customer_query Query instance.
		 */
		return apply_filters( 'llms_customer_query_from', $sql, $this );
	}

	/**
	 * WHERE clause (search + segments).
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function sql_where() {

		global $wpdb;

		$sql = 'WHERE 1 = 1';

		$search = $this->get( 'search' );
		if ( $search ) {
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			$sql .= $wpdb->prepare(
				' AND (
					u.user_email LIKE %s
					OR u.display_name LIKE %s
					OR m_first.meta_value LIKE %s
					OR m_last.meta_value LIKE %s
					OR CAST( customers.user_id AS CHAR ) = %s
				)',
				$like,
				$like,
				$like,
				$like,
				$search
			);
		}

		$sql .= $this->sql_segment();

		/**
		 * Filters the customer query WHERE clause.
		 *
		 * @since [version]
		 *
		 * @param string              $sql             WHERE clause.
		 * @param LLMS_Customer_Query $customer_query Query instance.
		 */
		return apply_filters( 'llms_customer_query_where', $sql, $this );
	}

	/**
	 * Segment filter SQL appended to WHERE.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function sql_segment() {

		global $wpdb;

		$sql     = '';
		$segment = $this->get( 'segment' );

		switch ( $segment ) {
			case 'high_spenders':
				$threshold = llms_get_customer_high_spender_threshold();
				$sql       = $wpdb->prepare( ' AND customers.ltv >= %f AND customers.ltv > 0', $threshold );
				break;

			case 'active_subs':
				$sql = ' AND customers.active_recurring_count > 0';
				break;

			case 'free_only':
				$sql = ' AND customers.ltv = 0';
				break;

			case 'at_risk':
				$cutoff = gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( DAY_IN_SECONDS * 90 ) );
				$sql    = $wpdb->prepare(
					' AND customers.ltv > 0
						AND customers.active_recurring_count = 0
						AND customers.last_order < %s',
					$cutoff
				);
				break;
		}

		/**
		 * Filters the customer query segment SQL.
		 *
		 * @since [version]
		 *
		 * @param string              $sql             Segment SQL fragment.
		 * @param string              $segment         Segment slug.
		 * @param LLMS_Customer_Query $customer_query Query instance.
		 */
		return apply_filters( 'llms_customer_query_segment_sql', $sql, $segment, $this );
	}

	/**
	 * Override ORDER BY to map friendly keys onto subquery aliases / user fields.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function sql_orderby() {

		if ( $this->get( 'count_only' ) ) {
			return '';
		}

		$sort = $this->get( 'sort' );
		if ( ! $sort ) {
			return parent::sql_orderby();
		}

		$map = array(
			'user_id'                => 'customers.user_id',
			'name'                   => 'm_last.meta_value',
			'ltv'                    => 'customers.ltv',
			'order_count'            => 'customers.order_count',
			'aov'                    => 'customers.aov',
			'last_order'             => 'customers.last_order',
			'first_order'            => 'customers.first_order',
			'registered'             => 'u.user_registered',
			'active_recurring_count' => 'customers.active_recurring_count',
		);

		$parts = array();
		foreach ( $sort as $orderby => $order ) {
			if ( ! isset( $map[ $orderby ] ) ) {
				continue;
			}
			// Validate against the unprefixed key; sanitize_sql_orderby() rejects dotted aliases.
			$safe = sanitize_sql_orderby( "{$orderby} {$order}" );
			if ( ! $safe ) {
				continue;
			}
			$order   = ( 'ASC' === strtoupper( $order ) ) ? 'ASC' : 'DESC';
			$parts[] = "{$map[ $orderby ]} {$order}";
		}

		if ( ! $parts ) {
			return '';
		}

		$sql = 'ORDER BY ' . implode( ', ', $parts );

		/**
		 * Filters the customer query ORDER BY clause.
		 *
		 * @since [version]
		 *
		 * @param string              $sql             ORDER BY clause.
		 * @param LLMS_Customer_Query $customer_query Query instance.
		 */
		return apply_filters( 'llms_customer_query_orderby', $sql, $this );
	}

	/**
	 * Retrieve customer result objects.
	 *
	 * @since [version]
	 *
	 * @return object[]
	 */
	public function get_customers() {

		$customers = $this->get_results();

		/**
		 * Filters the list of customers returned by the query.
		 *
		 * @since [version]
		 *
		 * @param object[]            $customers       Customer result objects.
		 * @param LLMS_Customer_Query $customer_query Query instance.
		 */
		return apply_filters( 'llms_customer_query_get_customers', $customers, $this );
	}
}
