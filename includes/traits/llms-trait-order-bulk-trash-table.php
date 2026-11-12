<?php
/**
 * LifterLMS Order Bulk Trash Table trait.
 *
 * @package LifterLMS/Traits
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds a select column and a bulk "Move to Trash" action for orders to an admin table.
 *
 * Used by the Orders & Transactions and Subscriptions tables. The bulk action is processed
 * by {@see LLMS_Admin_Page_Orders::maybe_handle_bulk_action()}.
 *
 * This trait should only be used by classes that extend from the {@see LLMS_Admin_Table} class.
 *
 * @since [version]
 */
trait LLMS_Trait_Order_Bulk_Trash_Table {

	/**
	 * Get the HTML ID of the bulk actions form, referenced by each row's checkbox.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_bulk_form_id() {
		return 'llms-' . str_replace( '_', '-', $this->id ) . '-bulk';
	}

	/**
	 * Build the bulk action checkbox for a row.
	 *
	 * The checkbox selects the row's order, so every row of an order trashes the same order.
	 * It's tied to the bulk actions form through the `form` attribute because the table
	 * body is replaced on AJAX reloads and sits outside the form.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Order $order  Order object.
	 * @param int        $row_id Post ID of the row (transaction or order), used for a unique input ID.
	 * @return string
	 */
	protected function get_checkbox_html( $order, $row_id ) {
		$order_id = $order->get( 'id' );
		$input_id = 'llms-select-' . absint( $row_id );
		return sprintf(
			'<input id="%1$s" type="checkbox" name="order_ids[]" value="%2$d" form="%3$s"><label for="%1$s"><span class="screen-reader-text">%4$s</span></label>',
			esc_attr( $input_id ),
			$order_id,
			esc_attr( $this->get_bulk_form_id() ),
			/* translators: %d: order ID */
			esc_html( sprintf( __( 'Select order #%d', 'lifterlms' ), $order_id ) )
		);
	}

	/**
	 * Whether the current user can trash orders in bulk.
	 *
	 * @since [version]
	 *
	 * @return bool
	 */
	protected function user_can_bulk_trash() {
		$post_type = get_post_type_object( 'llms_order' );
		return $post_type && current_user_can( $post_type->cap->delete_posts );
	}

	/**
	 * Get the select (checkbox) column definition, when the current user can bulk trash.
	 *
	 * @since [version]
	 *
	 * @return array Empty, or an array with the `cb` column.
	 */
	protected function get_bulk_trash_columns() {

		if ( ! $this->user_can_bulk_trash() ) {
			return array();
		}

		return array(
			'cb' => array(
				'exportable' => false,
				'sortable'   => false,
				'title'      => __( 'Select', 'lifterlms' ),
			),
		);
	}

	/**
	 * Output the bulk actions form, with a link to the trashed orders when there are any.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	protected function output_bulk_actions_html() {

		if ( ! $this->user_can_bulk_trash() ) {
			return;
		}
		?>
		<form class="llms-table-bulk-actions" id="<?php echo esc_attr( $this->get_bulk_form_id() ); ?>" method="post">
			<?php wp_nonce_field( LLMS_Admin_Page_Orders::BULK_NONCE_ACTION ); ?>
			<label class="screen-reader-text" for="<?php echo esc_attr( $this->id ); ?>-bulk-action">
				<?php esc_html_e( 'Select bulk action', 'lifterlms' ); ?>
			</label>
			<select id="<?php echo esc_attr( $this->id ); ?>-bulk-action" name="llms_bulk_action">
				<option value=""><?php esc_html_e( 'Bulk actions', 'lifterlms' ); ?></option>
				<option value="trash"><?php esc_html_e( 'Move to Trash', 'lifterlms' ); ?></option>
			</select>
			<button class="button action" type="submit"><?php esc_html_e( 'Apply', 'lifterlms' ); ?></button>
			<?php
			$trash_count = absint( wp_count_posts( 'llms_order' )->trash );
			if ( $trash_count ) :
				?>
				<a class="llms-table-trash-link" href="<?php echo esc_url( admin_url( 'edit.php?post_type=llms_order&post_status=trash' ) ); ?>">
					<?php esc_html_e( 'Trash', 'lifterlms' ); ?> <span class="count">(<?php echo esc_html( number_format_i18n( $trash_count ) ); ?>)</span>
				</a>
			<?php endif; ?>
		</form>
		<?php
	}
}
