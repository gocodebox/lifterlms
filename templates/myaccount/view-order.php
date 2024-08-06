<?php
/**
 * View an Order.
 *
 * @package LifterLMS/Templates
 *
 * @since 3.0.0
 * @since 3.33.0 Pass the current order object instance as param for all the actions and filters, plus redundant check on order existence removed.
 * @since 3.35.0 Access `$_GET` data via `llms_filter_input()`.
 * @since 5.4.0 Inform about deleted products.
 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
 * @since 6.0.0 Load sub-templates using hooks and template functions.
 * @version 6.0.0
 *
 * @var LLMS_Order $order        Current order object.
 * @var array      $transactions Result array from {@see LLMS_Order::get_transactions()}.
 * @var string     $layout_class The view's layout classname. Either `llms-stack-cols` or an empty string for the default side-by-side layout.
 */

defined( 'ABSPATH' ) || exit;

$classes = array_filter(
	array_map(
		'esc_attr',
		array( 'llms-sd-section', 'llms-view-order', $layout_class )
	)
);

llms_print_notices();
?>

<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">

	<h2 class="order-title">
		<?php echo esc_html( sprintf( __( 'Order #%d', 'lifterlms' ), $order->get( 'id' ) ) ); ?>
		<span class="llms-status <?php echo esc_attr( $order->get( 'status' ) ); ?>"><?php echo wp_kses_post( $order->get_status_name() ); ?></span>
	</h2>

	<?php
		/**
		 * Action run prior to the display of order information.
		 *
		 * @since Unknown
		 *
		 * @param LLMS_Order $order The order being displayed.
		 */
		do_action( 'lifterlms_before_view_order_table', $order );

		/**
		 * Displays information about the order.
		 *
		 * @hooked llms_template_view_order_information 10
		 *
		 * @since 6.0.0
		 *
		 * @param LLMS_Order $order The order being displayed.
		 */
		do_action( 'llms_view_order_information', $order );

		/**
		 * Displays user actions for the order.
		 *
		 * @hooked llms_template_view_order_information 10
		 *
		 * @since 6.0.0
		 *
		 * @param LLMS_Order $order The order being displayed.
		 */
		do_action( 'llms_view_order_actions', $order );
	?>

	<div class="clear"></div>

	<?php
		/**
		 * Displays order transactions.
		 *
		 * @since Unknown
		 *
		 * @param LLMS_Order $order        The order being displayed.
		 * @param array      $transactions Result array from {@see LLMS_Order::get_transactions()}.
		 */
		do_action( 'llms_view_order_transactions', $order, $transactions );

		/**
		 * Action run after the display of order information.
		 *
		 * @since Unknown
		 *
		 * @param LLMS_Order $order The order being displayed.
		 */
		do_action( 'lifterlms_after_view_order_table', $order );
	?>

</div>
