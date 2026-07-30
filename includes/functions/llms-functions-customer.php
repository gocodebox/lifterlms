<?php
/**
 * Customer commerce helpers.
 *
 * @package LifterLMS/Functions
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retrieve the admin URL for the Customers list or a single customer.
 *
 * @since [version]
 *
 * @param int|null $user_id Optional. WP user ID for a single customer view. Default `null` (list).
 * @param array    $args    Optional. Additional query args.
 * @return string
 */
function llms_get_customers_admin_url( $user_id = null, $args = array() ) {

	$url_args = array_merge(
		array(
			'post_type' => 'llms_order',
			'page'      => 'llms-customers',
		),
		$args
	);

	if ( $user_id ) {
		$url_args['customer_id'] = absint( $user_id );
	}

	return add_query_arg( $url_args, admin_url( 'edit.php' ) );
}

/**
 * Determine whether a user is a customer (has at least one order).
 *
 * @since [version]
 *
 * @param int $user_id WP user ID.
 * @return boolean
 */
function llms_is_customer( $user_id ) {

	$user_id = absint( $user_id );
	if ( ! $user_id ) {
		return false;
	}

	$student = llms_get_student( $user_id );
	if ( ! $student ) {
		return false;
	}

	$orders = $student->get_orders(
		array(
			'count' => 1,
		)
	);

	return ! empty( $orders['orders'] );
}

/**
 * Retrieve commerce metrics for a customer.
 *
 * Net LTV matches `LLMS_Order::get_revenue( 'net' )`: sum of succeeded/refunded
 * transaction amounts minus refund amounts. Multi-currency stores are not converted;
 * amounts are summed as stored (same caveat as Sales reporting).
 *
 * @since [version]
 *
 * @param int $user_id WP user ID.
 * @return array {
 *     @type float       $ltv                    Net lifetime value.
 *     @type float       $gross                  Gross transaction total.
 *     @type float       $refunded               Total refunded amount.
 *     @type int         $order_count            Number of orders.
 *     @type float       $aov                    Average order value (ltv / order_count).
 *     @type string|null $first_order_date       MySQL datetime of first order.
 *     @type string|null $last_order_date        MySQL datetime of last order.
 *     @type int         $active_recurring_count Active/pending-cancel recurring orders.
 *     @type string      $currency               Store currency code.
 * }
 */
function llms_get_customer_metrics( $user_id ) {

	$user_id = absint( $user_id );
	$empty   = array(
		'ltv'                    => 0.0,
		'gross'                  => 0.0,
		'refunded'               => 0.0,
		'order_count'            => 0,
		'aov'                    => 0.0,
		'first_order_date'       => null,
		'last_order_date'        => null,
		'active_recurring_count' => 0,
		'currency'               => get_lifterlms_currency(),
	);

	if ( ! $user_id ) {
		return $empty;
	}

	$cache_key = 'llms_customer_metrics_' . $user_id;
	$cached    = wp_cache_get( $cache_key, 'llms_customers' );
	if ( false !== $cached ) {
		return $cached;
	}

	global $wpdb;

	$row = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT
				COUNT( DISTINCT orders.ID ) AS order_count,
				MIN( orders.post_date ) AS first_order_date,
				MAX( orders.post_date ) AS last_order_date,
				SUM(
					CASE
						WHEN order_type.meta_value = 'recurring'
							AND orders.post_status IN ( 'llms-active', 'llms-pending-cancel' )
						THEN 1 ELSE 0
					END
				) AS active_recurring_count,
				COALESCE( SUM( order_rev.gross ), 0 ) AS gross,
				COALESCE( SUM( order_rev.refunded ), 0 ) AS refunded
			FROM {$wpdb->posts} AS orders
			INNER JOIN {$wpdb->postmeta} AS user_meta
				ON user_meta.post_id = orders.ID
				AND user_meta.meta_key = '_llms_user_id'
				AND user_meta.meta_value = %s
			LEFT JOIN {$wpdb->postmeta} AS order_type
				ON order_type.post_id = orders.ID
				AND order_type.meta_key = '_llms_order_type'
			LEFT JOIN (
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
			) AS order_rev ON order_rev.order_id = orders.ID
			WHERE orders.post_type = 'llms_order'
				AND orders.post_status NOT IN ( 'trash', 'auto-draft' )",
			(string) $user_id
		),
		ARRAY_A
	); // db call ok; no-cache ok.

	if ( ! $row || ! absint( $row['order_count'] ) ) {
		wp_cache_set( $cache_key, $empty, 'llms_customers' );
		return $empty;
	}

	$gross       = (float) $row['gross'];
	$refunded    = (float) $row['refunded'];
	$ltv         = $gross - $refunded;
	$order_count = absint( $row['order_count'] );

	$metrics = array(
		'ltv'                    => $ltv,
		'gross'                  => $gross,
		'refunded'               => $refunded,
		'order_count'            => $order_count,
		'aov'                    => $order_count ? ( $ltv / $order_count ) : 0.0,
		'first_order_date'       => $row['first_order_date'] ? $row['first_order_date'] : null,
		'last_order_date'        => $row['last_order_date'] ? $row['last_order_date'] : null,
		'active_recurring_count' => absint( $row['active_recurring_count'] ),
		'currency'               => get_lifterlms_currency(),
	);

	/**
	 * Filters customer commerce metrics.
	 *
	 * @since [version]
	 *
	 * @param array $metrics Metrics array.
	 * @param int   $user_id WP user ID.
	 */
	$metrics = apply_filters( 'llms_get_customer_metrics', $metrics, $user_id );

	wp_cache_set( $cache_key, $metrics, 'llms_customers' );

	return $metrics;
}

/**
 * Clear cached customer metrics.
 *
 * @since [version]
 *
 * @param int $user_id WP user ID. When `0`, clears the high-spender threshold cache only.
 * @return void
 */
function llms_delete_customer_metrics_cache( $user_id = 0 ) {

	$user_id = absint( $user_id );
	if ( $user_id ) {
		wp_cache_delete( 'llms_customer_metrics_' . $user_id, 'llms_customers' );
	}

	delete_transient( 'llms_customer_high_spender_threshold' );
}

/**
 * Invalidate customer metrics when an order is saved.
 *
 * @since [version]
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @return void
 */
function llms_invalidate_customer_metrics_on_order_save( $post_id, $post ) {

	if ( ! $post || 'llms_order' !== $post->post_type ) {
		return;
	}

	$user_id = absint( get_post_meta( $post_id, '_llms_user_id', true ) );
	llms_delete_customer_metrics_cache( $user_id );
}
add_action( 'save_post_llms_order', 'llms_invalidate_customer_metrics_on_order_save', 20, 2 );

/**
 * Invalidate customer metrics when a transaction is saved.
 *
 * @since [version]
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @return void
 */
function llms_invalidate_customer_metrics_on_transaction_save( $post_id, $post ) {

	if ( ! $post || 'llms_transaction' !== $post->post_type ) {
		return;
	}

	$order_id = absint( get_post_meta( $post_id, '_llms_order_id', true ) );
	if ( ! $order_id ) {
		llms_delete_customer_metrics_cache();
		return;
	}

	$user_id = absint( get_post_meta( $order_id, '_llms_user_id', true ) );
	llms_delete_customer_metrics_cache( $user_id );
}
add_action( 'save_post_llms_transaction', 'llms_invalidate_customer_metrics_on_transaction_save', 20, 2 );

/**
 * Retrieve available customer segment definitions.
 *
 * @since [version]
 *
 * @return array Associative array of segment slug => label.
 */
function llms_get_customer_segments() {

	$segments = array(
		'all'           => __( 'All customers', 'lifterlms' ),
		'high_spenders' => __( 'High spenders', 'lifterlms' ),
		'active_subs'   => __( 'Active subscriptions', 'lifterlms' ),
		'free_only'     => __( 'Free only', 'lifterlms' ),
		'at_risk'       => __( 'At risk', 'lifterlms' ),
	);

	/**
	 * Filters the customer segment definitions.
	 *
	 * @since [version]
	 *
	 * @param array $segments Segment slug => label.
	 */
	return apply_filters( 'llms_customer_segments', $segments );
}

/**
 * Retrieve the LTV threshold for the high spenders segment (80th percentile among LTV > 0).
 *
 * @since [version]
 *
 * @return float
 */
function llms_get_customer_high_spender_threshold() {

	$cached = get_transient( 'llms_customer_high_spender_threshold' );
	if ( false !== $cached ) {
		return (float) $cached;
	}

	global $wpdb;

	$ltvs = $wpdb->get_col(
		"SELECT COALESCE( SUM( order_rev.gross ), 0 ) - COALESCE( SUM( order_rev.refunded ), 0 ) AS ltv
		FROM {$wpdb->posts} AS orders
		INNER JOIN {$wpdb->postmeta} AS user_meta
			ON user_meta.post_id = orders.ID
			AND user_meta.meta_key = '_llms_user_id'
			AND user_meta.meta_value > 0
		LEFT JOIN (
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
		) AS order_rev ON order_rev.order_id = orders.ID
		WHERE orders.post_type = 'llms_order'
			AND orders.post_status NOT IN ( 'trash', 'auto-draft' )
		GROUP BY user_meta.meta_value
		HAVING ltv > 0
		ORDER BY ltv ASC"
	); // db call ok; no-cache ok.

	if ( empty( $ltvs ) ) {
		set_transient( 'llms_customer_high_spender_threshold', 0, HOUR_IN_SECONDS );
		return 0.0;
	}

	$index     = (int) floor( ( count( $ltvs ) - 1 ) * 0.8 );
	$threshold = (float) $ltvs[ $index ];

	set_transient( 'llms_customer_high_spender_threshold', $threshold, HOUR_IN_SECONDS );

	return $threshold;
}
