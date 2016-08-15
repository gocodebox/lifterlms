<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Metaboxes for Orders
 *
 * @version  3.0.0
 */
class LLMS_Meta_Box_Order_Transactions extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 * @return void
	 * @since  3.0.0
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
	 * @return array
	 *
	 * @since  3.0.0
	 */
	public function get_fields() {}

	/**
	 * Function to field WP::output() method call
	 * Passes output instruction to parent
	 *
	 * @param object $post WP global post object
	 * @return void
	 *
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
	 * Save method, processes refunds
	 * @param    int     $post_id  Post ID of the Order
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function save( $post_id ) {

		// only save here if a process refund button was used to submit the form
		if ( ! isset( $_POST['llms_process_refund'] ) ) {
			return;
		}
		// can't proceed with a txn id
		elseif ( empty( $_POST['llms_refund_txn_id'] ) ) {
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

}
