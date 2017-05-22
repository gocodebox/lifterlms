<?php
/**
 * Metaboxes for Orders
 *
 * @since    3.0.0
 * @version  3.8.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Meta_Box_Order_Transactions extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function configure() {

		$this->id = 'lifterlms-order-transactions';
		$this->title = __( 'Transactions', 'lifterlms' );
		$this->screens = array(
			'llms_order',
		);
		$this->context = 'normal';
		$this->priority = 'high';

	}

	/**
	 * Not used because our metabox doesn't use the standard fields api
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_fields() {}

	/**
	 * Function to field WP::output() method call
	 * Passes output instruction to parent
	 * @param    object  $post  WP global post object
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function output() {

		$order = new LLMS_Order( $this->post );

		$curr_page = isset( $_GET['txns-page'] ) ? $_GET['txns-page'] : 1;
		// allow users to see all if they really want to
		$per_page = isset( $_GET['txns-count'] ) ? $_GET['txns-count'] : 20;

		$transactions = $order->get_transactions( array(
			'per_page' => $per_page,
			'paged' => $curr_page,
		) );

		$edit_link = get_edit_post_link( $this->post->ID );

		$prev_url = ( $transactions['page'] > 1 ) ? add_query_arg( 'txns-page', $curr_page - 1, $edit_link ) . '#' . $this->id : false;
		$next_url = ( $transactions['page'] < $transactions['pages'] ) ? add_query_arg( 'txns-page', $curr_page + 1, $edit_link ) . '#' . $this->id : false;
		$all_url = ( $next_url || $prev_url ) ? add_query_arg( 'txns-count', -1, $edit_link ) . '#' . $this->id : false;

		llms_get_template( 'admin/post-types/order-transactions.php', array(
			'all_url' => $all_url,
			'next_url' => $next_url,
			'prev_url' => $prev_url,
			'transactions' => $transactions,
		) );

	}

	/**
	 * Resend a receipt for a transaction
	 * @param    int     $post_id  WP Post ID of the current order
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	private function resend_receipt( $post_id ) {
		if ( ! is_numeric( $_POST['llms_resend_receipt'] ) ) {
			return;
		}
		$txn = llms_get_post( absint( $_POST['llms_resend_receipt'] ) );
		do_action( 'lifterlms_resend_transaction_receipt', $txn );
	}

	/**
	 * Save method, processes refunds / records manual txns
	 * @param    int     $post_id  Post ID of the Order
	 * @return   void
	 * @since    3.0.0
	 * @version  3.8.0
	 */
	public function save( $post_id ) {

		$actions = array(
			'llms_process_refund' => 'save_refund',
			'llms_record_txn' => 'save_transaction',
			'llms_resend_receipt' => 'resend_receipt',
		);

		foreach ( $actions as $action => $method ) {

			if ( isset( $_POST[ $action ] ) ) {

				$this->$method( $post_id );
				break;

			}
		}

	}

	/**
	 * Save method, processes refunds
	 * @param    int     $post_id  Post ID of the Order
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	private function save_refund( $post_id ) {
		// can't proceed with a txn id
		if ( empty( $_POST['llms_refund_txn_id'] ) ) {
			return $this->add_error( __( 'Refund Error: Missing a transaction ID', 'lifterlms' ) );
		} elseif ( empty( $_POST['llms_refund_amount'] ) ) {
			return $this->add_error( __( 'Refund Error: Missing or invalid refund amount', 'lifterlms' ) );
		}

		$txn = new LLMS_Transaction( $_POST['llms_refund_txn_id'] );

		$refund = $txn->process_refund( $_POST['llms_refund_amount'], $_POST['llms_refund_note'], $_POST['llms_process_refund'] );

		if ( is_wp_error( $refund ) ) {
			$this->add_error( sprintf( _x( 'Refund Error: %s', 'admin error message', 'lifterlms' ), $refund->get_error_message() ) );
		}
	}


	/**
	 * Save method, records manual transactions
	 * @param    int     $post_id  Post ID of the Order
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	private function save_transaction( $post_id ) {
		if ( empty( $_POST['llms_txn_amount'] ) ) {
			return $this->add_error( __( 'Refund Error: Missing or invalid payment amount', 'lifterlms' ) );
		}

		$order = new LLMS_Order( $post_id );

		$txn = $order->record_transaction( array(
			'amount' => floatval( $_POST['llms_txn_amount'] ),
			'source_description' => sanitize_text_field( $_POST['llms_txn_source'] ),
			'transaction_id' => sanitize_text_field( $_POST['llms_txn_id'] ),
			'status' => 'llms-txn-succeeded',
			'payment_gateway' => 'manual',
			'payment_type' => 'single',
		) );

		if ( ! empty( $_POST['llms_txn_note'] ) ) {
			$order->add_note( sanitize_text_field( $_POST['llms_txn_note'] ), true );
		}

		if ( is_wp_error( $txn ) ) {
			$this->add_error( sprintf( _x( 'Refund Error: %s', 'admin error message', 'lifterlms' ), $refund->get_error_message() ) );
		}
	}

}
