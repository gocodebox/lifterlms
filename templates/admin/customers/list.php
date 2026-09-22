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
 * @property array  $counts          Segment slug => count.
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	exit;
}
?>
<div class="wrap lifterlms llms-customers-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Customers', 'lifterlms' ); ?></h1>
	<hr class="wp-header-end">

	<ul class="subsubsub">
		<?php
		$segment_keys = array_keys( $segments );
		$last_key     = end( $segment_keys );
		foreach ( $segments as $slug => $label ) :
			$class = ( $current_segment === $slug ) ? 'current' : '';
			?>
			<li>
				<a href="<?php echo esc_url( llms_get_customers_admin_url( null, array( 'segment' => $slug ) ) ); ?>"<?php echo $class ? ' class="' . esc_attr( $class ) . '"' : ''; ?>>
					<?php echo esc_html( $label ); ?>
					<?php if ( isset( $counts[ $slug ] ) ) : ?>
						<span class="count">(<?php echo esc_html( number_format_i18n( $counts[ $slug ] ) ); ?>)</span>
					<?php endif; ?>
				</a><?php echo ( $slug !== $last_key ) ? ' |' : ''; ?>
			</li>
		<?php endforeach; ?>
	</ul>

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
