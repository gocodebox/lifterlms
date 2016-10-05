<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Metaboxes for Orders
 *
 * @version  3.0.0
 */
class LLMS_Meta_Box_Order_Submit extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 * @return void
	 * @since  3.0.0
	 */
	public function configure() {

		$this->id = 'lifterlms-order-submit';
		$this->title = __( 'Order Status', 'lifterlms' );
		$this->screens = array(
			'llms_order',
		);
		$this->context = 'side';
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
		$current_status = $order->get( 'status' );

		if ( $order->is_legacy() ) {

			_e( 'The status of a Legacy order cannot be changed.', 'lifterlms' );
			return;

		}

		$statuses = llms_get_order_statuses( $order->is_recurring() ? 'recurring' : 'single' );
		?>
		<div class="llms-metabox">

			<div class="llms-metabox-section d-all no-top-margin">

				<div class="llms-metabox-field">
					<label for="_llms_order_status"><?php _e( 'Update Order Status:', 'lifterlms' ) ?></label>
					<select id="_llms_order_status" name="_llms_order_status">
						<?php foreach ( $statuses as $key => $val ) : ?>
							<option value="<?php echo $key; ?>"<?php selected( $key, $current_status ); ?>><?php echo $val; ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="llms-metabox-field" style="text-align: right;">
					<input name="save" type="submit" class="button button-primary button-large" id="publish" value="<?php _e( 'Update Status', 'lifterlms' ); ?>">
				</div>

			</div>

		</div>
		<?php
		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

	}

	/**
	 * Save action, update order status
	 * @param    int     $post_id  WP Post ID of the Order
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function save( $post_id ) {

		$order = new LLMS_Order( $post_id );

		if ( isset( $_POST['_llms_order_status'] ) ) {

			$new_status = $_POST['_llms_order_status'];
			$old_status = $order->get( 'status' );

			if ( $old_status !== $new_status ) {

				// update the status
				$order->set( 'status', $new_status );

			}

		}

	}

}
