<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Metaboxes for Orders
 *
 * @version  3.0.0
 */
class LLMS_Meta_Box_Order_Notes extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 * @return void
	 * @since  3.0.0
	 */
	public function configure() {

		$this->id = 'lifterlms-order-notes';
		$this->title = __( 'Order Notes', 'lifterlms' );
		$this->screens = array(
			'llms_order',
		);
		$this->context = 'side';
		$this->priority = 'default';

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

		$curr_page = isset( $_GET['notes-page'] ) ? $_GET['notes-page'] : 1;
		$per_page = 10;

		$edit_link = get_edit_post_link( $this->post->ID );

		$notes = $order->get_notes( $per_page, $curr_page );
		$next_page = ( count( $notes ) == $per_page ) ? count( $order->get_notes( $per_page, $curr_page + 1 ) ) : 0;

		$prev_url = ( $curr_page > 1 ) ? add_query_arg( 'notes-page', $curr_page - 1, $edit_link ) . '#' . $this->id : false;
		$next_url = ( $next_page ) ? add_query_arg( 'notes-page', $curr_page + 1, $edit_link ) . '#' . $this->id : false;

		if ( $notes ) {
			echo '<ul class="llms-order-notes">';
			foreach ( $notes  as $note ) {
				?>

				<li class="llms-order-note" id="llms-order-note-<?php echo $note->comment_ID; ?>">
					<div class="llms-order-note-content"><?php echo wpautop( get_comment_text( $note->comment_ID ) ); ?></div>
					<div class="llms-order-note-meta">
						<?php printf( _x( 'by %s', 'order note author', 'lifterlms' ), get_comment_author( $note->comment_ID ) ); ?>
						<?php printf( _x( 'on %s', 'order note date', 'lifterlms' ), get_comment_date( 'M j, Y h:i a', $note->comment_ID ) ); ?>
					</div>

				</li>

				<?php
			}
			echo '</ul>';

			if ( ! empty( $prev_url ) || ! empty( $next_url ) ) {

				echo '<hr>';

			}

			if ( ! empty( $prev_url ) ) {
				echo '<a class="button" href="' . $prev_url . '">' . sprintf( __( '%s Newer', 'lifterlms' ), '&laquo;' ) . '</a> ';
			}

			if ( ! empty( $next_url ) ) {
				echo '<a class="button" href="' . $next_url . '">' . sprintf( __( 'Older %s', 'lifterlms' ), '&raquo;' ) . '</a>';
			}
		} else {

			_e( 'No order notes found.', 'lifterlms' );

		}// End if().

	}

	/**
	 * Save method
	 * Does nothing because there's no editable data in this metabox
	 * @param    int     $post_id  Post ID of the Order
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function save( $post_id ) {
		return;
	}

}
