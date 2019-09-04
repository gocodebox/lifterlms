<?php
/**
 * Order transactions metabox.
 *
 * @since 3.0.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Meta_Box_Order_Transactions
 *
 * @since 3.0.0
 * @since 3.35.0 Verify nonces and sanitize `$_POST` data.
 */
class LLMS_Meta_Box_Order_Transactions extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 *
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function configure() {

		$this->id       = 'lifterlms-order-transactions';
		$this->title    = __( 'Transactions', 'lifterlms' );
		$this->screens  = array(
			'llms_order',
		);
		$this->context  = 'normal';
		$this->priority = 'high';

	}

	/**
	 * Not used because our metabox doesn't use the standard fields api
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_fields() {
		return array();
	}

	/**
	 * Function to field WP::output() method call
	 * Passes output instruction to parent
	 *
	 * @since 3.0.0
	 * @since 3.35.0 Sanitize `$_GET` data.
	 *
	 * @return void
	 */
	public function output() {

		$order = new LLMS_Order( $this->post );

		$curr_page = isset( $_GET['txns-page'] ) ? absint( wp_unslash( $_GET['txns-page'] ) ) : 1;
		// allow users to see all if they really want to
		$per_page = isset( $_GET['txns-count'] ) ? absint( wp_unslash( $_GET['txns-count'] ) ) : 20;

		$transactions = $order->get_transactions(
			array(
				'per_page' => $per_page,
				'paged'    => $curr_page,
			)
		);

		$edit_link = get_edit_post_link( $this->post->ID );

		$prev_url = ( $transactions['page'] > 1 ) ? add_query_arg( 'txns-page', $curr_page - 1, $edit_link ) . '#' . $this->id : false;
		$next_url = ( $transactions['page'] < $transactions['pages'] ) ? add_query_arg( 'txns-page', $curr_page + 1, $edit_link ) . '#' . $this->id : false;
		$all_url  = ( $next_url || $prev_url ) ? add_query_arg( 'txns-count', -1, $edit_link ) . '#' . $this->id : false;

		llms_get_template(
			'admin/post-types/order-transactions.php',
			array(
				'all_url'      => $all_url,
				'next_url'     => $next_url,
				'prev_url'     => $prev_url,
				'transactions' => $transactions,
			)
		);

	}

	/**
	 * Resend a receipt for a transaction
	 *
	 * @param    int $post_id  WP Post ID of the current order
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	private function resend_receipt( $post_id ) {

		$txn_id = llms_filter_input( INPUT_POST, 'llms_resend_receipt', FILTER_SANITIZE_NUMBER_INT );
		if ( ! $txn_id ) {
			return;
		}
		do_action( 'lifterlms_resend_transaction_receipt', llms_get_post( $txn_id ) );

	}

	/**
	 * Save method, processes refunds / records manual txns
	 *
	 * @since 3.0.0
	 * @since 3.8.0 Unknown
	 * @since 3.35.0 Verify nonces and sanitize `$_POST` data.
	 *
	 * @param int $post_id Post ID of the Order.
	 * @return void
	 */
	public function save( $post_id ) {

		if ( ! llms_verify_nonce( 'lifterlms_meta_nonce', 'lifterlms_save_data' ) ) {
			return;
		}

		$actions = array(
			'llms_process_refund' => 'save_refund',
			'llms_record_txn'     => 'save_transaction',
			'llms_resend_receipt' => 'resend_receipt',
		);

		foreach ( $actions as $action => $method ) {
			$action = llms_filter_input( INPUT_POST, $action, FILTER_SANITIZE_STRING );
			if ( $action ) {
				$this->$method( $post_id );
				break;
			}
		}

	}

	/**
	 * Save method, processes refunds
	 *
	 * @since 3.0.0
	 * @since 3.35.0 Verify nonces and sanitize `$_POST` data.
	 *
	 * @param int $post_id Post ID of the Order.
	 * @return null
	 */
	private function save_refund( $post_id ) {

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce is verified in the save() method of this class.

		$txn_id = llms_filter_input( INPUT_POST, 'llms_refund_txn_id', FILTER_SANITIZE_NUMBER_INT );
		$amount = llms_filter_input( INPUT_POST, 'llms_refund_amount', FILTER_SANITIZE_STRING );
		if ( empty( $txn_id ) ) {
			return $this->add_error( __( 'Refund Error: Missing a transaction ID', 'lifterlms' ) );
		} elseif ( empty( $amount ) ) {
			return $this->add_error( __( 'Refund Error: Missing or invalid refund amount', 'lifterlms' ) );
		}

		$txn = new LLMS_Transaction( $txn_id );

		$refund = $txn->process_refund(
			$amount,
			llms_filter_input( INPUT_POST, 'llms_refund_note', FILTER_SANITIZE_STRING ),
			llms_filter_input( INPUT_POST, 'llms_process_refund', FILTER_SANITIZE_STRING )
		);

		if ( is_wp_error( $refund ) ) {
			$this->add_error( sprintf( _x( 'Refund Error: %s', 'admin error message', 'lifterlms' ), $refund->get_error_message() ) );
		}

		// phpcs:enable WordPress.Security.NonceVerification.Missing

	}


	/**
	 * Save method, records manual transactions
	 *
	 * @since 3.0.0
	 * @since 3.35.0 Verify nonces and sanitize `$_POST` data.
	 *
	 * @param int $post_id Post ID of the Order.
	 * @return null
	 */
	private function save_transaction( $post_id ) {

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce is verified in the save() method of this class.

		if ( empty( $_POST['llms_txn_amount'] ) ) {
			return $this->add_error( __( 'Refund Error: Missing or invalid payment amount', 'lifterlms' ) );
		}

		$order = new LLMS_Order( $post_id );

		$txn = $order->record_transaction(
			array(
				'amount'             => llms_filter_input( INPUT_POST, 'llms_txn_amount', FILTER_SANITIZE_STRING ),
				'source_description' => llms_filter_input( INPUT_POST, 'llms_txn_source', FILTER_SANITIZE_STRING ),
				'transaction_id'     => llms_filter_input( INPUT_POST, 'llms_txn_id', FILTER_SANITIZE_STRING ),
				'status'             => 'llms-txn-succeeded',
				'payment_gateway'    => 'manual',
				'payment_type'       => 'single',
			)
		);

		if ( ! empty( $_POST['llms_txn_note'] ) ) {
			$order->add_note( llms_filter_input( INPUT_POST, 'llms_txn_note', FILTER_SANITIZE_STRING ), true );
		}

		if ( is_wp_error( $txn ) ) {
			$this->add_error( sprintf( _x( 'Refund Error: %s', 'admin error message', 'lifterlms' ), $refund->get_error_message() ) );
		}

		// phpcs:enable WordPress.Security.NonceVerification.Missing

	}

}
