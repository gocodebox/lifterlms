<?php
/**
 * Admin tool used to autmoatically reschedule recurring orders missing a pending scheduled payment action
 *
 * @package LifterLMS/Admin/Tools/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Tool_Batch_Eraser
 *
 * @since [version]
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
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_description() {

		$count = count( $this->get_orders() );

		$desc  = __( 'Schedule recurring payment actions for orders which have not had a recurring payment action scheduled.', 'lifterlms' );
		$desc .= ' ';
		// Translators: %d = the number of pending batches.
		$desc .= sprintf(
			_n(
				'There is currently %d order that will have a payment rescheduled.',
				'There are currently %d orders that will have their payments rescheduled.',
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
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_label() {
		return __( 'Reschedule Recurring Payments', 'lifterlms' );
	}

	/**
	 * Retrieve the tool's button text
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_text() {
		return __( 'Reschedule Payments', 'lifterlms' );
	}

	/**
	 * Retrieve a list of orders
	 *
	 * @since [version]
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
	 * and expiration action based on it's existing calculated order data.
	 *
	 * @since [version]
	 *
	 * @return int[] Returns an array of WP_Post IDs for orders successfully rescheduled by the method.
	 */
	protected function handle() {

		$orders = array();

		foreach ( $this->get_orders() as $id ) {
			$order = llms_get_post( $id );
 			$order->maybe_schedule_payment( false );
			$order->maybe_schedule_expiration();
			if ( $order->get_next_scheduled_action_time( 'llms_charge_recurring_payment' ) ) {
				$orders[] = $id;
			}
		}

		wp_cache_delete( $this->id, 'llms_tool_data' );

		return $orders;

	}

	/**
	 * Perform a DB query for orders to be handled by the tool
	 *
	 * @since [version]
	 *
	 * @return object[]
	 */
	protected function query_orders() {

		global $wpdb;

		return $wpdb->get_results(
			"SELECT p.ID
			   FROM {$wpdb->posts} AS p
	      LEFT JOIN {$wpdb->prefix}actionscheduler_actions AS a
			     ON a.args   = CONCAT( '{\"order_id\":', p.ID, '}' )
			    AND a.hook   = 'llms_charge_recurring_payment'
			    AND a.status = 'pending'
			  WHERE 1
			    AND p.post_type   = 'llms_order'
			    AND p.post_status = 'llms-active'
			    AND a.action_id IS NULL
			  LIMIT 100
			;"
		);

	}

	/**
	 * Conditionally load the tool
	 *
	 * This tool should only load if there are orders that can be handled by the tool.
	 *
	 * @since [version]
	 *
	 * @return boolean Return `true` to load the tool and `false` to not load it.
	 */
	protected function should_load() {
		return count( $this->get_orders() ) > 0;
	}

}

return new LLMS_Admin_Tool_Recurring_Payment_Rescheduler();
