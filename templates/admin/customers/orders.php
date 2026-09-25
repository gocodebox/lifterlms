<?php
/**
 * Single customer orders tab
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since [version]
 * @version [version]
 *
 * @property LLMS_Student $student       Student / customer.
 * @property array        $orders_result Orders result array from LLMS_Student::get_orders().
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	exit;
}

$current_page = max( 1, absint( $orders_result['page'] ) );
$total_pages  = max( 1, absint( $orders_result['pages'] ) );

$page_url = function ( $page ) use ( $student ) {
	return llms_get_customers_admin_url(
		$student->get_id(),
		array(
			'stab'  => 'orders',
			'paged' => $page,
		)
	);
};
?>
<section class="llms-reporting-tab-main">
	<header>
		<h3><?php esc_html_e( 'Order history', 'lifterlms' ); ?></h3>
	</header>
	<?php llms_get_template( 'admin/customers/orders-table.php', array( 'orders_result' => $orders_result ) ); ?>

	<?php if ( $total_pages > 1 ) : ?>
		<nav class="llms-customer-orders-pagination" aria-label="<?php esc_attr_e( 'Order history pagination', 'lifterlms' ); ?>">
			<?php if ( $current_page > 1 ) : ?>
				<a class="button" href="<?php echo esc_url( $page_url( $current_page - 1 ) ); ?>">&larr; <?php esc_html_e( 'Previous', 'lifterlms' ); ?></a>
			<?php endif; ?>
			<span class="llms-customer-orders-page-count">
				<?php
				printf(
					/* translators: %1$d: current page number, %2$d: total number of pages */
					esc_html__( 'Page %1$d of %2$d', 'lifterlms' ),
					absint( $current_page ),
					absint( $total_pages )
				);
				?>
			</span>
			<?php if ( $current_page < $total_pages ) : ?>
				<a class="button" href="<?php echo esc_url( $page_url( $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'lifterlms' ); ?> &rarr;</a>
			<?php endif; ?>
		</nav>
	<?php endif; ?>
</section>
