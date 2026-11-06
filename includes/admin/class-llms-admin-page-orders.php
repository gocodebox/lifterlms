<?php
/**
 * LLMS_Admin_Page_Orders class file.
 *
 * Registers the Orders & Transactions and Subscriptions admin pages
 * under the Orders CPT menu.
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 11.0.0
 * @version 11.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin page registration for the Orders & Transactions and Subscriptions tables.
 *
 * @since 11.0.0
 */
class LLMS_Admin_Page_Orders {

	/**
	 * Hook suffixes for the registered pages.
	 *
	 * These match the `WP_Screen::$id` values and are used to enqueue the
	 * admin tables JS on the correct screens.
	 *
	 * @since 11.0.0
	 *
	 * @var string[]
	 */
	protected $page_hooks = array();

	/**
	 * Constructor.
	 *
	 * @since 11.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_pages' ) );
		add_action( 'admin_menu', array( $this, 'reorder_orders_menu' ), 999 );
		add_filter( 'llms_load_table_resources_pages', array( $this, 'add_table_resource_pages' ) );
		add_action( 'admin_init', array( $this, 'maybe_redirect_old_listing' ) );
		add_action( 'admin_init', array( $this, 'maybe_serve_transaction_receipt' ) );
		add_action( 'admin_notices', array( $this, 'maybe_render_transaction_parent_notice' ) );
	}

	/**
	 * Reorder the Orders submenu so the new views land first.
	 *
	 * WordPress points a CPT's top-level menu link at its first submenu item. By
	 * removing the default "All Orders" list-table link and promoting the new
	 * "Orders & Transactions" page to the top, clicking the top-level "Orders"
	 * menu opens the new view instead of the raw CPT listing (or Coupons).
	 *
	 * The single-order edit screen (`post.php`) remains fully accessible.
	 *
	 * @since 11.0.0
	 *
	 * @return void
	 */
	public function reorder_orders_menu() {

		global $submenu;

		$parent = 'edit.php?post_type=llms_order';

		if ( empty( $submenu[ $parent ] ) ) {
			return;
		}

		// Desired leading order, keyed by page slug.
		$priority = array(
			'llms-orders-transactions' => 0,
			'llms-subscriptions'       => 1,
		);

		$front = array();
		$rest  = array();

		foreach ( $submenu[ $parent ] as $item ) {

			$slug = $item[2];

			// Drop the default "All Orders" list-table link (slug equals the parent slug).
			if ( $parent === $slug ) {
				continue;
			}

			if ( isset( $priority[ $slug ] ) ) {
				$front[ $priority[ $slug ] ] = $item;
			} else {
				$rest[] = $item;
			}
		}

		ksort( $front );

		$submenu[ $parent ] = array_merge( array_values( $front ), $rest );
	}

	/**
	 * Redirect the old CPT listing URL to the new Orders & Transactions page.
	 *
	 * Only redirects when viewing the raw CPT listing (not the single edit screen
	 * and not one of our own custom pages).
	 *
	 * @since 11.0.0
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

		// Never redirect our own custom pages (prevents a redirect loop).
		if ( llms_filter_input( INPUT_GET, 'page' ) ) {
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
	 * @since 11.0.0
	 *
	 * @return void
	 */
	public function register_pages() {

		$parent_slug = 'edit.php?post_type=llms_order';

		// Same capability as the Orders screen, including the lifterlms_admin_order_access filter.
		$capability = apply_filters( 'lifterlms_admin_order_access', 'manage_lifterlms' );

		$orders_hook = add_submenu_page(
			$parent_slug,
			__( 'Orders & Transactions', 'lifterlms' ),
			__( 'Orders & Transactions', 'lifterlms' ),
			$capability,
			'llms-orders-transactions',
			array( $this, 'render_orders_transactions_page' )
		);

		if ( $orders_hook ) {
			$this->page_hooks[] = $orders_hook;
			add_action( 'load-' . $orders_hook, array( $this, 'maybe_handle_bulk_action' ) );
		}

		$this->page_hooks[] = add_submenu_page(
			$parent_slug,
			__( 'Subscriptions', 'lifterlms' ),
			__( 'Subscriptions', 'lifterlms' ),
			$capability,
			'llms-subscriptions',
			array( $this, 'render_subscriptions_page' )
		);
	}

	/**
	 * Add the new pages to the list of pages that load table JS resources.
	 *
	 * The hook suffixes returned by `add_submenu_page()` match the
	 * `WP_Screen::$id` of each page, so they can be used directly.
	 *
	 * @since 11.0.0
	 *
	 * @param string[] $pages Array of screen IDs.
	 * @return string[]
	 */
	public function add_table_resource_pages( $pages ) {
		return array_merge( $pages, $this->page_hooks );
	}

	/**
	 * Process a bulk action submitted from the Orders & Transactions table.
	 *
	 * Rows are selected by their order, so "Move to Trash" trashes each selected
	 * order (and hides its transactions), matching the bulk trash action of the
	 * orders post type list.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function maybe_handle_bulk_action() {

		if ( 'trash' !== llms_filter_input( INPUT_POST, 'llms_bulk_action' ) ) {
			return;
		}

		check_admin_referer( LLMS_Table_Orders_Transactions::BULK_NONCE_ACTION );

		$order_ids = llms_filter_input( INPUT_POST, 'order_ids', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY );
		$order_ids = array_unique( array_filter( array_map( 'absint', (array) $order_ids ) ) );

		$trashed = 0;
		foreach ( $order_ids as $order_id ) {
			if ( 'llms_order' !== get_post_type( $order_id ) || ! current_user_can( 'delete_post', $order_id ) ) {
				continue;
			}
			if ( wp_trash_post( $order_id ) ) {
				++$trashed;
			}
		}

		wp_safe_redirect( add_query_arg( 'trashed', $trashed, admin_url( 'edit.php?post_type=llms_order&page=llms-orders-transactions' ) ) );
		exit;
	}

	/**
	 * Render the Orders & Transactions page.
	 *
	 * @since 11.0.0
	 *
	 * @return void
	 */
	public function render_orders_transactions_page() {
		$table = new LLMS_Table_Orders_Transactions();
		$table->get_results();
		echo '<div class="wrap lifterlms llms-orders-transactions-wrap">';
		// Hidden heading gives admin notices a home; the table renders its own visible title.
		echo '<h1 class="screen-reader-text">' . esc_html__( 'Orders & Transactions', 'lifterlms' ) . '</h1>';

		$trashed = absint( llms_filter_input( INPUT_GET, 'trashed', FILTER_SANITIZE_NUMBER_INT ) );
		if ( $trashed ) {
			printf(
				'<div class="notice notice-success is-dismissible"><p>%1$s <a href="%2$s">%3$s</a></p></div>',
				esc_html(
					sprintf(
						/* translators: %d: number of orders moved to the trash */
						_n( '%d order moved to the Trash.', '%d orders moved to the Trash.', $trashed, 'lifterlms' ),
						$trashed
					)
				),
				esc_url( admin_url( 'edit.php?post_type=llms_order&post_status=trash' ) ),
				esc_html__( 'View Trash', 'lifterlms' )
			);
		}

		$table->output_table_html();
		echo '</div>';
	}

	/**
	 * Render the Subscriptions page.
	 *
	 * @since 11.0.0
	 *
	 * @return void
	 */
	public function render_subscriptions_page() {
		$table = new LLMS_Table_Subscriptions();
		$table->get_results();
		echo '<div class="wrap lifterlms llms-subscriptions-wrap">';
		// Hidden heading gives admin notices a home; the table renders its own visible title.
		echo '<h1 class="screen-reader-text">' . esc_html__( 'Subscriptions', 'lifterlms' ) . '</h1>';
		$table->output_table_html();
		echo '</div>';
	}

	/**
	 * Render a note on the order edit screen when arriving from a transaction row.
	 *
	 * When a transaction in the Orders & Transactions table links to its parent
	 * order, the link carries `llms_txn_id`. This surfaces a note at the top of the
	 * order edit screen letting the user know the transaction itself lives in the
	 * Transactions list below, with a jump link to it.
	 *
	 * @since 11.0.0
	 *
	 * @return void
	 */
	public function maybe_render_transaction_parent_notice() {

		global $pagenow;

		if ( 'post.php' !== $pagenow ) {
			return;
		}

		$txn_id = absint( llms_filter_input( INPUT_GET, 'llms_txn_id', FILTER_SANITIZE_NUMBER_INT ) );
		if ( ! $txn_id ) {
			return;
		}

		$post_id = absint( llms_filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT ) );
		if ( ! $post_id || 'llms_order' !== get_post_type( $post_id ) ) {
			return;
		}

		// Only show the note when the transaction actually belongs to this order.
		$transaction = llms_get_post( $txn_id );
		if ( ! $transaction instanceof LLMS_Transaction || absint( $transaction->get( 'order_id' ) ) !== $post_id ) {
			return;
		}

		printf(
			'<div class="notice notice-info"><p>%1$s <a href="#lifterlms-order-transactions">%2$s</a></p></div>',
			sprintf(
				/* translators: %d: transaction ID */
				esc_html__( 'Viewing details on the parent order for transaction #%d. The transaction can be found below.', 'lifterlms' ),
				$txn_id
			),
			esc_html__( 'View transactions', 'lifterlms' )
		);
	}

	/**
	 * Serve a single transaction receipt (HTML or PDF via lifterlms-pdfs).
	 *
	 * Triggered via URL parameter: ?llms_receipt_txn={transaction_id}&_wpnonce={nonce}
	 *
	 * @since 11.0.0
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

		if ( ! llms_current_user_can_manage_orders() ) {
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
		 * @since 11.0.0
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
	 * @since 11.0.0
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
