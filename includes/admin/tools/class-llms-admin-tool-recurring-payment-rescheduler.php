<?php
/**
 * Admin tool used to automatically reschedule recurring orders missing a pending scheduled payment action
 *
 * @package LifterLMS/Admin/Tools/Classes
 *
 * @since 4.6.0
 * @version 4.7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Tool_Recurring_Payment_Rescheduler class
 *
 * @since 4.6.0
 */
class LLMS_Admin_Tool_Recurring_Payment_Rescheduler extends LLMS_Abstract_Admin_Tool {

	/**
	 * Tool ID.
	 *
	 * @var string
	 */
	protected $id = 'recurring-payment-rescheduler';

	/**
	 * Retrieve a description of the tool
	 *
	 * This is displayed on the right side of the tool's list before the button.
	 *
	 * @since 4.6.0
	 * @since 4.7.0 Modified language and use count from `FOUND_ROWS()`.
	 *
	 * @return string
	 */
	protected function get_description() {

		$orders = $this->get_orders();
		$count  = wp_cache_get( sprintf( '%s-total-results', $this->id ), 'llms_tool_data' );

		$desc  = __( 'Check active recurring orders to ensure their recurring payment action is properly scheduled for the next payment. If a recurring payment is due and not scheduled it will be rescheduled.', 'lifterlms' );
		$desc .= ' ';
		// Translators: %d = the number of pending batches.
		$desc .= sprintf(
			_n(
				'There is %d order that will be checked.',
				'There are %d orders that will be checked in batches of 50.',
				$count,
				'lifterlms'
			),
			$count
		);

		return $desc;

	}

	/**
	 * Retrieve the tool's label
	 *
	 * The label is the tool's title. It's displayed in the left column on the tool's list.
	 *
	 * @since 4.6.0
	 *
	 * @return string
	 */
	protected function get_label() {
		return __( 'Reschedule Recurring Payments', 'lifterlms' );
	}

	/**
	 * Retrieve the tool's button text
	 *
	 * @since 4.6.0
	 *
	 * @return string
	 */
	protected function get_text() {
		return __( 'Reschedule Payments', 'lifterlms' );
	}

	/**
	 * Retrieve a list of orders
	 *
	 * @since 4.6.0
	 *
	 * @return int[]
	 */
	protected function get_orders() {

		$orders = wp_cache_get( $this->id, 'llms_tool_data' );
		if ( ! $orders ) {
			$orders = wp_list_pluck( $this->query_orders(), 'ID' );
			wp_cache_set( $this->id, $orders, 'llms_tool_data' );
		}

		return $orders;

	}

	/**
	 * Schedules payments and expiration for an order
	 *
	 * Retrieves orders from the `get_orders()` method and schedules a recurring payment
	 * and expiration action based on its existing calculated order data.
	 *
	 * @since 4.6.0
	 * @since 4.7.0 Set `plan_ended` metadata for orders with an ended plan and don't attempt to process them.
	 *
	 * @return int[] Returns an array of WP_Post IDs for orders successfully rescheduled by the method.
	 */
	protected function handle() {

		$orders = array();

		foreach ( $this->get_orders() as $id ) {
			$order = llms_get_post( $id );
			$next  = $order->get_next_payment_due_date();
			if ( is_wp_error( $next ) && 'plan-ended' === $next->get_error_code() ) {
				$order->set( 'plan_ended', 'yes' );
				continue;
			}
			$order->maybe_schedule_payment( false );
			$order->maybe_schedule_expiration();
			if ( $order->get_next_scheduled_action_time( 'llms_charge_recurring_payment' ) ) {
				$orders[] = $id;
			}
		}

		wp_cache_delete( $this->id, 'llms_tool_data' );
		wp_cache_delete( sprintf( '%s-total-results', $this->id ), 'llms_tool_data' );

		return $orders;

	}

	/**
	 * Perform a DB query for orders to be handled by the tool
	 *
	 * @since 4.6.0
	 * @since 4.7.0 Added `SQL_CALC_FOUND_ROWS` and improved query to exclude results with a completed payment plan.
	 *
	 * @return object[]
	 */
	protected function query_orders() {

		global $wpdb;

		$orders = $wpdb->get_results(
			"SELECT SQL_CALC_FOUND_ROWS p.ID
			   FROM {$wpdb->posts} AS p
		  LEFT JOIN {$wpdb->postmeta} AS m
			     ON p.ID = m.post_ID
			    AND m.meta_key = '_llms_plan_ended'
		  LEFT JOIN {$wpdb->prefix}actionscheduler_actions AS a
			     ON a.args   = CONCAT( '{\"order_id\":', p.ID, '}' )
			    AND a.hook   = 'llms_charge_recurring_payment'
			    AND a.status = 'pending'
			  WHERE 1
			    AND p.post_type   = 'llms_order'
			    AND p.post_status = 'llms-active'
			    AND a.action_id IS NULL
			    AND m.meta_value IS NULL
		   ORDER BY p.ID ASC
			  LIMIT 50
			;"
		); // no-cache ok -- Caching implemented in `get_orders()`.

		wp_cache_set( sprintf( '%s-total-results', $this->id ), $wpdb->get_var( 'SELECT FOUND_ROWS()' ), 'llms_tool_data' );

		return $orders;

	}

	/**
	 * Conditionally load the tool
	 *
	 * This tool should only load if there are orders that can be handled by the tool.
	 *
	 * @since 4.6.0
	 *
	 * @return boolean Return `true` to load the tool and `false` to not load it.
	 */
	protected function should_load() {
		return count( $this->get_orders() ) > 0;
	}

}

return new LLMS_Admin_Tool_Recurring_Payment_Rescheduler();
