<?php
/**
 * Reporting Sales Tab
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since Unknown
 * @version Unknown
 */

defined( 'ABSPATH' ) || exit;
if ( ! is_admin() ) {
	exit;
}
?>

<?php foreach ( $widget_data as $row => $widgets ) : ?>
	<div class="llms-widget-row llms-widget-row-<?php $row; ?>">
	<?php foreach ( $widgets as $id => $opts ) : ?>

		<div class="llms-widget-<?php echo $opts['cols']; ?>">
			<div class="llms-widget is-loading" data-method="<?php echo $id; ?>" id="llms-widget-<?php echo $id; ?>">

				<p class="llms-label"><?php echo $opts['title']; ?></p>
				<h1><?php echo $opts['content']; ?></h1>

				<span class="spinner"></span>

				<i class="fa fa-info-circle llms-widget-info-toggle"></i>
				<div class="llms-widget-info">
					<p><?php echo $opts['info']; ?></p>
				</div>

			</div>
		</div>

	<?php endforeach; ?>
	</div>
<?php endforeach; ?>

<div class="llms-charts-wrapper" id="llms-charts-wrapper"></div>

<div id="llms-analytics-json" style="display:none;"><?php echo $json; ?></div>
