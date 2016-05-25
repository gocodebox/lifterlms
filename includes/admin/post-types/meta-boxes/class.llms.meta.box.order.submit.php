<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Metaboxes for Orders
 *
 * @version  3.0.0
 */
class LLMS_Meta_Box_Order_Submit extends LLMS_Admin_Metabox {

	/**
	 * Function to field WP::output() method call
	 * Passes output instruction to parent
	 *
	 * @param object $post WP global post object
	 * @return void
	 *
	 * @version  3.0.0
	 */
	public static function output( $post ) {

		$order = new LLMS_Order( $post );
		$current_status = $order->get_status();

		$statuses = llms_get_order_statuses( $order->get_type() );

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

				<div class="llms-metabox-field">
					<label><?php _e( 'Student Enrollment Action:', 'lifterlms' ) ?></label>
					<span class="show-conditionally default"><?php _e( 'No action' ); ?></span>
					<span class="show-conditionally llms-active llms-completed"><?php _e( 'Enroll Student' ); ?></span>
					<span class="show-conditionally llms-expired llms-refunded llms-cancelled"><?php _e( 'Unenroll Student' ); ?></span>
				</div>

				<div class="llms-metabox-field">
					<label for="_llms_skip_enrollment_actions"><input name="_llms_skip_enrollment_actions" id="_llms_skip_enrollment_actions" type="checkbox" value="1"> <?php _e( 'Skip Student Enrollment Action', 'lifterlms' ) ?></label>
				</div>

				<div class="llms-metabox-field" style="text-align: right;">
					<input name="save" type="submit" class="button button-primary button-large" id="publish" value="<?php _e( 'Update Status' ); ?>">
				</div>

			</div>

		</div>
		<?php
		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

	}


	public static function save( $post_id, $post ) {

		$order = new LLMS_Order( $post );

		if ( isset( $_POST['_llms_order_status'] ) ) {

			$new_status = $_POST['_llms_order_status'];

			// if status has changed
			if ( $order->get_status() !== $new_status ) {

				// if skipping enrollment actions
				// update the post using direct WP update functions
				if ( isset( $_POST['_llms_skip_enrollment_actions'] ) ) {

					wp_update_post( array(
						'ID' => $post_id,
						'post_status' => $new_status,
					) );

				} // otherwise use the order's update status method
				// which will trigger enrollment actions
				else {

					$order->update_status( $new_status );

				}

			}

		}

	}

}
