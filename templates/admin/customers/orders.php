<?php
/**
 * Single customer orders tab
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since [version]
 * @version [version]
 *
 * @property array $orders_result Orders result array from LLMS_Student::get_orders().
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	exit;
}
?>
<section class="llms-reporting-tab-main">
	<header>
		<h3><?php esc_html_e( 'Order history', 'lifterlms' ); ?></h3>
	</header>
	<?php llms_get_template( 'admin/customers/orders-table.php', array( 'orders_result' => $orders_result ) ); ?>
</section>
