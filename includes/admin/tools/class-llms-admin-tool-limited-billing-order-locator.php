<?php
/**
 * LLMS_Admin_Tool_Limited_Billing_Order_Locator class.
 *
 * @package LifterLMS/Admin/Tools/Classes
 *
 * @since 5.3.0
 * @version 5.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin tool which generates a report of limited billing orders affected by order end changes.
 *
 * @since 5.3.0
 *
 * @link https://github.com/gocodebox/lifterlms/pull/1744
 */
class LLMS_Admin_Tool_Limited_Billing_Order_Locator extends LLMS_Abstract_Admin_Tool {

	/**
	 * Tool ID.
	 *
	 * @var string
	 */
	protected $id = 'limited-billing-order-locator';

	/**
	 * Query the database for orders that may be affected by the change.
	 *
	 * @since 5.3.0
	 * @since 5.4.0 Retrieve orders ordered by their unique ID (DESC) instead of the default `date_created`.
	 *
	 * @return array[] Returns an array of arrays where each array represents a line in the generated CSV file.
	 */
	protected function generate_csv() {

		$csv = array();

		$orders = new WP_Query(
			array(
				'post_type'      => 'llms_order',
				'post_status'    => array( 'llms-active', 'llms-on-hold' ),
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'meta_query'     => array(
					array(
						'key'     => '_llms_billing_length',
						'value'   => 1,
						'compare' => '>=',
					),
					array(
						'key'     => '_llms_date_billing_end',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		foreach ( $orders->posts as $order ) {

			$order = llms_get_post( $order );
			if ( ! $order || ! is_a( $order, 'LLMS_Order' ) ) {
				continue;
			}

			$csv[] = $this->get_order_csv( $order );

		}

		return array_filter( $csv );

	}

	/**
	 * Create a csv "file" via output buffering and return it as a string.
	 *
	 * @since 5.3.0
	 *
	 * @return string
	 */
	protected function get_csv_file() {

		$csv = $this->get_csv();

		// Add header row.
		array_unshift(
			$csv,
			array(
				'Order ID',
				'Expected Payments',
				'Total Payments',
				'Successful Payments',
				'Refunded Payments',
				'Edit Link',
			)
		);

		// Create the CSV file.
		ob_start();
		$fh = fopen( 'php://output', 'w' );
		foreach ( $csv as $line ) {
			fputcsv( $fh, $line );
		}
		fclose( $fh ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose

		return ob_get_clean();

	}

	/**
	 * Retrieve a description of the tool.
	 *
	 * This is displayed on the right side of the tool's list before the button.
	 *
	 * @since 5.3.0
	 *
	 * @return string
	 */
	protected function get_description() {

		$count = count( $this->get_csv() );

		$desc = sprintf(
			// Translators: %1$s = opening anchor link to documentation; %2$s = closing anchor link.
			__( 'The method used to determine when a limited-billing recurring order has completed its payment plan changed during version 5.3.0. This tool provides a report of orders which may been affected by this change. %1$sRead more%2$s about this change.', 'lifterlms' ),
			'<a href="https://lifterlms.com/docs/payment-plan-orders-530/" target="_blank">',
			'</a>'
		);
		$desc .= ' ';
		// Translators: %d = the number of pending batches.
		$desc .= sprintf(
			_n(
				'There is %d order that should be reviewed.',
				'There are %d orders that should be reviewed.',
				$count,
				'lifterlms'
			),
			$count
		);

		return $desc;

	}

	/**
	 * Retrieve the tool's label.
	 *
	 * The label is the tool's title. It's displayed in the left column on the tool's list.
	 *
	 * @since 5.3.0
	 *
	 * @return string
	 */
	protected function get_label() {
		return __( 'Limited Billing Orders', 'lifterlms' );
	}

	/**
	 * Retrieves an array representing a line in generated CSV for the given order.
	 *
	 * An order is considered to be affected by the change if either of the following conditions are true:
	 *
	 *   + Any number of refunds exist for the order. Since we are now counting refunds towards the billing limit
	 *     an active order with any number of refunds should be reviewed by the admin.
	 *
	 *   + The plan is marked as "ended" and the total number of successful payments is not equal to the billing length.
	 *     In this scenario admins will likely want to add additional payments and start the order again.
	 *
	 * @since 5.3.0
	 *
	 * @param LLMS_Order $order The order object.
	 * @return array Array of information on the order to be stored in the generated CSV or an empty array of the order
	 *               was not affected by the change.
	 */
	protected function get_order_csv( $order ) {

		$refunds   = $this->get_txn_count_by_status( $order, 'llms-txn-refunded' );
		$successes = $this->get_txn_count_by_status( $order, 'llms-txn-succeeded' );
		$total     = $refunds + $successes;

		$ended    = llms_parse_bool( $order->get( 'plan_ended' ) );
		$expected = $order->get( 'billing_length' );

		if ( $refunds >= 1 || ( $ended && $total !== $expected ) ) {

			$id   = $order->get( 'id' );
			$link = get_edit_post_link( $id, 'raw' );
			return array( $id, $expected, $total, $successes, $refunds, $link );

		}

		return array();

	}

	/**
	 * Helper to get the number of transactions on an order for a given status.
	 *
	 * @since 5.3.0
	 *
	 * @param LLMS_Order $order  The order object.
	 * @param string     $status Transaction post status to query by.
	 * @return int Number of transactions for the requested status.
	 */
	protected function get_txn_count_by_status( $order, $status ) {

		$txns = $order->get_transactions(
			array(
				'per_page' => 1,
				'status'   => array( $status ),
				'type'     => array( 'recurring', 'single' ), // If a manual payment is recorded it's counted a single payment and that should count.
			)
		);

		return $txns['total'];

	}

	/**
	 * Retrieve the tool's button text.
	 *
	 * @since 5.3.0
	 *
	 * @return string
	 */
	protected function get_text() {
		return __( 'Download CSV', 'lifterlms' );
	}

	/**
	 * Retrieve a list of orders.
	 *
	 * @since 5.3.0
	 *
	 * @return int[]
	 */
	protected function get_csv() {

		$csv = wp_cache_get( $this->id, 'llms_tool_data' );
		if ( ! $csv ) {
			$csv = $this->generate_csv();
			wp_cache_set( $this->id, $csv, 'llms_tool_data' );
		}

		return $csv;

	}

	/**
	 * Generate the CSV file an serve it as a downloadable attachment.
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	protected function handle() {

		$file = $this->get_csv_file();

		if ( ! headers_sent() ) { // This makes the method testable via phpunit.
			header( 'Content-Type: text/csv' );
			header( 'Content-Disposition: attachment; filename=orders.csv' );
			header( 'Content-Length: ' . strlen( $file ) );
			nocache_headers();
		}

		llms_exit( $file );

	}

	/**
	 * Conditionally load the tool.
	 *
	 * This tool should only load if there are orders that can be handled by the tool.
	 *
	 * @since 5.3.0
	 *
	 * @return boolean Return `true` to load the tool and `false` to not load it.
	 */
	protected function should_load() {
		return count( $this->get_csv() ) > 0;
	}

}

return new LLMS_Admin_Tool_Limited_Billing_Order_Locator();
