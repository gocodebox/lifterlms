<?php
/**
 * Transaction Count Widget
 *
 * @package LifterLMS/Admin/Reporting/Widgets/Classes
 *
 * @since 8.0.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Transaction Count Widget class
 *
 * Locates number of transactions from a given date range
 * by a given group of students.
 */
class LLMS_Analytics_Transactions_Widget extends LLMS_Analytics_Widget {

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
			'type'   => 'count',
			'header' => array(
				'id'    => 'sold',
				'label' => __( '# of Transactions', 'lifterlms' ),
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
				)
			);
			$this->query();
			$order_ids = $this->get_results();

			$this->temp_q = $wpdb->last_query;
			$this->temp   = $order_ids;

			if ( $order_ids ) {
				$txn_meta_join   = "JOIN {$wpdb->postmeta} AS txn_meta ON txn_meta.post_id = txns.ID";
				$txn_meta_where .= " AND txn_meta.meta_key = '_llms_order_id'";
				$txn_meta_where .= ' AND txn_meta.meta_value IN ( ' . implode( ', ', array_map( 'absint', $order_ids ) ) . ' )';
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
							  txns.post_date as date
						FROM {$wpdb->posts} AS txns
						{$txn_meta_join}
						WHERE
						        ( txns.post_status = 'llms-txn-succeeded' OR txns.post_status = 'llms-txn-refunded' )
						    AND txns.post_type = 'llms_transaction'
							AND txns.post_date >= CAST( %s as DATETIME )
							AND txns.post_date < CAST( %s as DATETIME )
							{$txn_meta_where}
							ORDER BY txns.post_modified ASC
						;";
	}

	protected function format_response() {

		if ( ! $this->is_error() ) {

			return count( $this->get_results() );

		}
	}
}
