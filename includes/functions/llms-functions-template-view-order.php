<?php
/**
 * Order view template functions.
 *
 * @package LifterLMS/Functions
 *
 * @since 6.0.0
 * @version 6.0.0
 */

if ( ! function_exists( 'llms_template_view_order' ) ) {

	/**
	 * Loads the template for a single order view on the student dashboard.
	 *
	 * @since 6.0.0
	 *
	 * @param LLMS_Order $order The order to display.
	 * @return void
	 */
	function llms_template_view_order( $order ) {

		// Validate order object and only allow the order's user to view the order.
		if ( ! $order instanceof LLMS_Order || get_current_user_id() !== $order->get( 'user_id' ) ) {
			esc_html_e( 'Invalid Order.', 'lifterlms' );
			return;
		}

		/**
		 * Allows customization of the view order layout on the student dashboard.
		 *
		 * @since 6.0.0
		 *
		 * @param boolean    $use_stacked_layout If `true`, forces usage of the stacked layout instead of the default side-by-side layout.
		 * @param LLMS_Order $order              The order to display.
		 */
		$layout_class = apply_filters( 'llms_sd_stacked_order_layout', false, $order ) ? 'llms-stack-cols' : '';

		$transactions = _llms_template_view_order_get_transactions( $order );

		llms_get_template( 'myaccount/view-order.php', compact( 'order', 'transactions', 'layout_class' ) );
	}
}

if ( ! function_exists( 'llms_template_view_order_actions' ) ) {

	/**
	 * Loads the single order view actions sidebar on the student dashboard.
	 *
	 * @since 6.0.0
	 *
	 * @param LLMS_Order $order The order to display.
	 * @return void
	 */
	function llms_template_view_order_actions( $order ) {
		llms_get_template( 'myaccount/view-order-actions.php', compact( 'order' ) );
	}
}

if ( ! function_exists( 'llms_template_view_order_information' ) ) {

	/**
	 * Loads the single order view information main area on the student dashboard.
	 *
	 * @since 6.0.0
	 *
	 * @param LLMS_Order $order The order to display.
	 * @return void
	 */
	function llms_template_view_order_information( $order ) {
		$gateway = $order->get_gateway();
		llms_get_template( 'myaccount/view-order-information.php', compact( 'order', 'gateway' ) );
	}
}

if ( ! function_exists( 'llms_template_view_order_transactions' ) ) {

	/**
	 * Loads the single order view transactions table on the student dashboard.
	 *
	 * @since 6.0.0
	 *
	 * @param LLMS_Order $order        The order to display.
	 * @param array      $transactions Result array from LLMS_Order::get_transactions(). If null, will load transactions from the order.
	 * @param integer    $per_page     Number of results to display per page. Only used if `$transactions` is `null`.
	 * @param integer    $page         Current results page to display. Only used if `$transactions` is `null`.
	 * @return void
	 */
	function llms_template_view_order_transactions( $order, $transactions = null, $per_page = 20, $page = null ) {

		if ( is_null( $transactions ) ) {
			$transactions = _llms_template_view_order_get_transactions( $order, $per_page, $page );
		}

		if ( empty( $transactions['transactions'] ) ) {
			return;
		}

		llms_get_template( 'myaccount/view-order-transactions.php', compact( 'transactions' ) );
	}
}

/**
 * Loads transactions for the given order.
 *
 * @since 6.0.0
 *
 * @access private
 *
 * @param LLMS_order   $order    Order object.
 * @param integer      $per_page Transactions to display per page.
 * @param null|integer $page     Results page.
 * @return array Results from LLMS_Order::get_transactions().
 */
function _llms_template_view_order_get_transactions( $order, $per_page = 20, $page = null ) {

	$page = is_null( $page ) ? absint( llms_filter_input( INPUT_GET, 'txnpage', FILTER_SANITIZE_NUMBER_INT ) ) : $page;

	return $order->get_transactions(
		array(
			/**
			 * Filters the number of transactions displayed on each page when viewing order details.
			 *
			 * @since Unknown
			 *
			 * @param integer $per_page Number of orders per page. Default: `20`.
			 */
			'per_page' => apply_filters( 'llms_student_dashboard_transactions_per_page', $per_page ),
			'paged'    => $page ? $page : 1,
		)
	);
}
