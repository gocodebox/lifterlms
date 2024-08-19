<?php
/**
 * Reporting widget view.
 *
 * @package LifterLMS/Admin/Views/Reporting
 *
 * @since 6.11.0
 *
 * @var array      $args             Input arguments provided to {@see LLMS_Admin_Reporting::output_widget}.
 * @var string     $data_after       Additional HTML to render inside the data display element.
 * @var string     $compare_class    A CSS class name added to the compare element's class list.
 * @var string     $compare_operator The operator to display before the changed data value.
 * @var string     $compare_title    A description of the previous data when displaying comparison data.
 * @var bool|float $change           The percent-change value when displaying comparisons or `false` when a comparison
 *                                   should not be displayed.
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="<?php echo esc_attr( $args['cols'] ); ?>">
	<div class="llms-reporting-widget <?php echo esc_attr( $args['id'] ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>">
		<?php if ( $args['icon'] ) : ?>
			<i class="fa fa-<?php echo esc_attr( $args['icon'] ); ?>" aria-hidden="true"></i>
		<?php endif; ?>
		<div class="llms-reporting-widget-data">
			<strong><?php echo wp_kses_post( $args['data'] . $data_after ); ?></strong>
			<?php if ( $change ) : ?>
				<small class="compare tooltip <?php echo esc_attr( $compare_class ); ?>" title="<?php echo esc_attr( $compare_title ); ?>">
					<?php echo wp_kses_post( $compare_operator . $change ); ?>%
				</small>
			<?php endif; ?>
		</div>
		<small><?php echo wp_kses_post( $args['text'] ); ?></small>
	</div>
</div>
