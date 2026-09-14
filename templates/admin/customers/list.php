<?php
/**
 * Customers list admin template
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since [version]
 * @version [version]
 *
 * @property string $current_segment Current segment slug.
 * @property array  $segments        Segment slug => label.
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	exit;
}
?>
<div class="wrap lifterlms llms-customers-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Customers', 'lifterlms' ); ?></h1>
	<hr class="wp-header-end">

	<nav class="llms-customer-segments" aria-label="<?php esc_attr_e( 'Customer segments', 'lifterlms' ); ?>">
		<ul>
			<?php foreach ( $segments as $slug => $label ) : ?>
				<li class="<?php echo ( $current_segment === $slug ) ? 'llms-active' : ''; ?>">
					<a href="<?php echo esc_url( llms_get_customers_admin_url( null, array( 'segment' => $slug ) ) ); ?>">
						<?php echo esc_html( $label ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>

	<section class="llms-customers-table llms-reporting-tab">
		<?php
		$table = new LLMS_Table_Customers();
		$table->get_results(
			array(
				'segment' => $current_segment,
			)
		);
		$table->output_table_html();
		?>
	</section>
</div>
