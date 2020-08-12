<?php
/**
 * Sold Amount Widget
 *
 * @package LifterLMS/Admin/Reporting/Widgets/Classes
 *
 * @since 3.0.0
 * @version 3.36.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sold Amount Widget class
 *
 * Retrieves the total amount of all successful transactions
 * according to active filters.
 *
 * @since 3.0.0
 * @since 3.30.3 Explicitly define class properties.
 * @since 3.36.3 In `format_response()` method avoid running `wp_list_pluck()` on non arrays.
 */
class LLMS_Analytics_Sold_Widget extends LLMS_Analytics_Widget {

	public $charts = true;

	/**
	 * temporary order ids
	 *
	 * @var array
	 * @since 3.0.0
	 */
	public $temp = array();

	/**
	 * temporary query
	 *
	 * @since 3.0.0
	 * @var array
	 */
	public $temp_q = array();

	protected function get_chart_data() {
		return array(
			'type'   => 'amount', // Type of field.
			'key'    => 'amount', // Key of result field to add when counting.
			'header' => array(
				'id'    => 'sold',
				'label' => __( 'Net Sales', 'lifterlms' ),
				'type'  => 'number',
			),
		);
	}

	public function set_query() {

		global $wpdb;

		$txn_meta_join  = '';
		$txn_meta_where = '';
		// Create an "IN" clause that can be used for later in WHERE clauses.
		if ( $this->get_posted_students() || $this->get_posted_posts() ) {

			// Get an array of order based on posted students & products.
			$this->set_order_data_query(
				array(
					'date_range'     => false,
					'query_function' => 'get_col',
					'select'         => array(
						'orders.ID',
					),
					'statuses'       => array(
						'llms-active',
						'llms-completed',
						'llms-refunded',
					),
				)
			);
			$this->query();
			$order_ids = $this->get_results();

			$this->temp_q = $wpdb->last_query;
			$this->temp   = $order_ids;

			if ( $order_ids ) {
				$txn_meta_join   = "JOIN {$wpdb->postmeta} AS txn_meta ON txn_meta.post_id = txns.ID";
				$txn_meta_where .= " AND txn_meta.meta_key = '_llms_order_id'";
				$txn_meta_where .= ' AND txn_meta.meta_value IN ( ' . implode( ', ', $order_ids ) . ' )';
			} else {

				$this->query_function = 'get_var';
				$this->query          = 'SELECT 0';
				return;

			}
		}

		// Date range will be used to get transactions between given dates.
		$dates            = $this->get_posted_dates();
		$this->query_vars = array(
			$this->format_date( $dates['start'], 'start' ),
			$this->format_date( $dates['end'], 'end' ),
		);

		$this->query_function = 'get_results';
		$this->output_type    = OBJECT;

		$this->query = "SELECT
							  txns.post_modified AS date
							, sales.meta_value AS amount
						FROM {$wpdb->posts} AS txns
						{$txn_meta_join}
						JOIN {$wpdb->postmeta} AS sales ON sales.post_id = txns.ID
						WHERE
						        ( txns.post_status = 'llms-txn-succeeded' )
						    AND txns.post_type = 'llms_transaction'
							AND txns.post_date BETWEEN CAST( %s AS DATETIME ) AND CAST( %s AS DATETIME )
							AND sales.meta_key = '_llms_amount'
							{$txn_meta_where}
							ORDER BY txns.post_modified ASC
						;";

	}

	/**
	 * Format response.
	 *
	 * @since unknown
	 * @since 3.36.3 Avoid running `wp_list_pluck()` on non arrays.
	 */
	protected function format_response() {

		if ( ! $this->is_error() ) {

			$results = $this->get_results();
			return llms_price_raw( floatval( is_array( $results ) ? array_sum( wp_list_pluck( $results, 'amount' ) ) : $results ) );

		}

	}

}
