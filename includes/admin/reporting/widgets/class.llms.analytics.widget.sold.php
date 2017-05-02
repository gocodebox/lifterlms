<?php
/**
* Sold Amount Widget
*
* Retrieves the total amount of all successful transactions
* according to active filters
*
* @since  3.0.0
* @version 3.0.0
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Analytics_Sold_Widget extends LLMS_Analytics_Widget {

	public $charts = true;

	protected function get_chart_data() {
		return array(
			'type' => 'amount', // type of field
			'key' => 'amount', // key of result field to add when counting
			'header' => array(
				'id' => 'sold',
				'label' => __( 'Net Sales', 'lifterlms' ),
				'type' => 'number',
			),
		);
	}

	public function set_query() {

		global $wpdb;

		$txn_meta_join = '';
		$txn_meta_where = '';
		// create an "IN" clause that can be used for lated in WHERE clauses
		if ( $this->get_posted_students() || $this->get_posted_posts() ) {

			// get an array of order based on posted students & products
			$this->set_order_data_query( array(
				'date_range' => false,
				'query_function' => 'get_col',
				'select' => array(
					'orders.ID',
				),
				'statuses' => array(
					'llms-active',
					'llms-completed',
					'llms-refunded',
				),
			) );
			$this->query();
			$order_ids = $this->get_results();

			$this->temp_q = $wpdb->last_query;
			$this->temp = $order_ids;

			if ( $order_ids ) {
				$txn_meta_join = "JOIN {$wpdb->postmeta} AS txn_meta ON txn_meta.post_id = txns.ID";
				$txn_meta_where .= " AND txn_meta.meta_key = '_llms_order_id'";
				$txn_meta_where .= ' AND txn_meta.meta_value IN ( ' . implode( ', ', $order_ids ) . ' )';
			} // End if().
			else {

				$this->query_function = 'get_var';
				$this->query = 'SELECT 0';
				return;

			}
		}

		// date range will be used to get transactions between given dates
		$dates = $this->get_posted_dates();
		$this->query_vars = array(
			$this->format_date( $dates['start'], 'start' ),
			$this->format_date( $dates['end'], 'end' ),
		);

		$this->query_function = 'get_results';
		$this->output_type = OBJECT;

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

	protected function format_response() {

		if ( ! $this->is_error() ) {

			return llms_price_raw( floatval( array_sum( wp_list_pluck( $this->get_results(), 'amount' ) ) ) );

		}

	}

}
