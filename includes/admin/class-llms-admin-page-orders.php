<?php
/**
 * LLMS_Admin_Page_Orders class file.
 *
 * Registers the Orders & Transactions and Subscriptions admin pages
 * under the Orders CPT menu.
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin page registration for the Orders & Transactions and Subscriptions tables.
 *
 * @since [version]
 */
class LLMS_Admin_Page_Orders {

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_pages' ) );
		add_action( 'admin_menu', array( $this, 'hide_default_orders_submenu' ), 999 );
		add_filter( 'llms_load_table_resources_pages', array( $this, 'add_table_resource_pages' ) );
		add_action( 'admin_init', array( $this, 'maybe_redirect_old_listing' ) );
		add_action( 'admin_init', array( $this, 'maybe_serve_transaction_receipt' ) );
	}

	/**
	 * Hide the default "All Orders" submenu item from the Orders CPT menu.
	 *
	 * The CPT edit screen remains accessible by direct URL for editing individual orders.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function hide_default_orders_submenu() {
		remove_submenu_page( 'edit.php?post_type=llms_order', 'edit.php?post_type=llms_order' );
	}

	/**
	 * Redirect the old CPT listing URL to the new Orders & Transactions page.
	 *
	 * Only redirects when viewing the listing (not the single edit screen).
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function maybe_redirect_old_listing() {

		global $pagenow;

		if ( 'edit.php' !== $pagenow ) {
			return;
		}

		$post_type = llms_filter_input( INPUT_GET, 'post_type' );
		if ( 'llms_order' !== $post_type ) {
			return;
		}

		// Don't redirect if there's a specific post status filter (e.g. Trash view).
		$post_status = llms_filter_input( INPUT_GET, 'post_status' );
		if ( $post_status && 'all' !== $post_status ) {
			return;
		}

		wp_safe_redirect( admin_url( 'edit.php?post_type=llms_order&page=llms-orders-transactions' ) );
		exit;
	}

	/**
	 * Register submenu pages under the Orders CPT menu.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function register_pages() {

		$parent_slug = 'edit.php?post_type=llms_order';

		add_submenu_page(
			$parent_slug,
			__( 'Orders & Transactions', 'lifterlms' ),
			__( 'Transactions', 'lifterlms' ),
			apply_filters( 'lifterlms_admin_order_access', 'manage_lifterlms' ),
			'llms-orders-transactions',
			array( $this, 'render_orders_transactions_page' )
		);

		add_submenu_page(
			$parent_slug,
			__( 'Subscriptions', 'lifterlms' ),
			__( 'Subscriptions', 'lifterlms' ),
			apply_filters( 'lifterlms_admin_order_access', 'manage_lifterlms' ),
			'llms-subscriptions',
			array( $this, 'render_subscriptions_page' )
		);
	}

	/**
	 * Add the new pages to the list of pages that load table JS resources.
	 *
	 * @since [version]
	 *
	 * @param string[] $pages Array of screen IDs.
	 * @return string[]
	 */
	public function add_table_resource_pages( $pages ) {
		$pages[] = 'llms_order_page_llms-orders-transactions';
		$pages[] = 'llms_order_page_llms-subscriptions';
		return $pages;
	}

	/**
	 * Render the Orders & Transactions page.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function render_orders_transactions_page() {
		$table = new LLMS_Table_Orders_Transactions();
		$table->get_results();
		echo '<div class="wrap lifterlms llms-orders-transactions-wrap">';
		$table->output_table_html();
		echo '</div>';
	}

	/**
	 * Render the Subscriptions page.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function render_subscriptions_page() {
		$table = new LLMS_Table_Subscriptions();
		$table->get_results();
		echo '<div class="wrap lifterlms llms-subscriptions-wrap">';
		$table->output_table_html();
		echo '</div>';
	}

	/**
	 * Serve a single transaction receipt (HTML or PDF via lifterlms-pdfs).
	 *
	 * Triggered via URL parameter: ?llms_receipt_txn={transaction_id}&_wpnonce={nonce}
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function maybe_serve_transaction_receipt() {

		$txn_id = absint( llms_filter_input( INPUT_GET, 'llms_receipt_txn', FILTER_SANITIZE_NUMBER_INT ) );
		if ( ! $txn_id ) {
			return;
		}

		$nonce = llms_filter_input( INPUT_GET, '_wpnonce' );
		if ( ! wp_verify_nonce( $nonce, 'llms_txn_receipt_' . $txn_id ) ) {
			wp_die( esc_html__( 'Invalid request.', 'lifterlms' ) );
		}

		if ( ! current_user_can( 'view_lifterlms_reports' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this receipt.', 'lifterlms' ) );
		}

		$transaction = llms_get_post( $txn_id );
		if ( ! $transaction instanceof LLMS_Transaction ) {
			wp_die( esc_html__( 'Transaction not found.', 'lifterlms' ) );
		}

		$order = llms_get_post( $transaction->get( 'order_id' ) );
		if ( ! $order instanceof LLMS_Order ) {
			wp_die( esc_html__( 'Order not found.', 'lifterlms' ) );
		}

		/**
		 * Allow the LifterLMS PDFs plugin to handle single-transaction PDF generation.
		 *
		 * If a plugin hooks in and handles this action (e.g. generates a PDF), it should
		 * call exit() to prevent the HTML fallback from rendering.
		 *
		 * @since [version]
		 *
		 * @param LLMS_Transaction $transaction The transaction object.
		 * @param LLMS_Order       $order       The parent order object.
		 */
		do_action( 'llms_serve_transaction_receipt', $transaction, $order );

		// HTML fallback: render the printable receipt template.
		include LLMS_PLUGIN_DIR . 'templates/admin/receipt-transaction.php';
		exit;
	}

	/**
	 * Get the URL for downloading a transaction receipt.
	 *
	 * @since [version]
	 *
	 * @param int $txn_id Transaction post ID.
	 * @return string
	 */
	public static function get_receipt_url( $txn_id ) {
		return wp_nonce_url(
			admin_url( '?llms_receipt_txn=' . $txn_id ),
			'llms_txn_receipt_' . $txn_id
		);
	}
}

return new LLMS_Admin_Page_Orders();
