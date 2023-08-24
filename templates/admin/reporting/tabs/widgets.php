<?php
/**
 * Reporting Sales Tab
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since Unknown
 * @since 7.2.0 Add content tag param to widget options.
 * @since 7.3.0 Escape output.
 * @version 7.3.0
 *
 * @param array $widget_data Array of widget data to display.
 */

defined( 'ABSPATH' ) || exit;
if ( ! is_admin() ) {
	exit;
}

?>

<?php foreach ( $widget_data as $row => $widgets ) : ?>
	<div class="llms-widget-row llms-widget-row-<?php echo esc_attr( $row ); ?>">
	<?php foreach ( $widgets as $id => $opts ) : ?>

		<div class="llms-widget-<?php echo esc_attr( $opts['cols'] ); ?>">
			<div class="llms-widget is-loading" data-method="<?php echo esc_attr( $id ); ?>" id="llms-widget-<?php echo esc_attr( $id ); ?>">

				<p class="llms-label"><?php echo esc_html( $opts['title'] ); ?></p>

				<?php if ( ! empty( $opts['link'] ) ) { ?>
					<a href="<?php echo esc_url( $opts['link'] ); ?>">
				<?php } ?>

				<?php
				printf(
					'<%s class="llms-widget-content">%s</%s>',
					esc_html( $opts['content_tag'] ?? 'h3' ),
					esc_html( $opts['content'] ?? '' ),
					esc_html( $opts['content_tag'] ?? 'h3' )
				);
				?>

				<?php if ( ! empty( $opts['link'] ) ) { ?>
					</a>
				<?php } ?>

				<span class="spinner"></span>

				<i class="fa fa-info-circle llms-widget-info-toggle"></i>
				<div class="llms-widget-info">
					<p><?php echo esc_html( $opts['info'] ); ?></p>
				</div>

			</div>
		</div>

	<?php endforeach; ?>
	</div>
<?php endforeach; ?>

<div class="llms-charts-wrapper" id="llms-charts-wrapper"></div>

<div id="llms-analytics-json" style="display:none;"><?php echo esc_html( $json ); ?></div>
