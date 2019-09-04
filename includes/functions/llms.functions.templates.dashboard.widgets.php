<?php
/**
 * Template functions for displaying stat widgets on the student dashboard
 *
 * @since    3.24.0
 * @version  3.24.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main function used to display a dashboard widget
 *
 * @param    string $title       Title of the widget.
 * @param    string $content     Content (HTML) of the widget body.
 * @param    string $empty_text  Content (text) to display if $content is empty.
 * @return   void
 * @since    3.24.0
 * @version  3.24.0
 */
function llms_sd_dashboard_widget( $title, $content, $empty_text = '' ) {
	?>
	<div class="llms-sd-widget">
		<h4 class="llms-sd-widget-title"><?php echo esc_html( $title ); ?></h4>
		<?php if ( $content ) : ?>
			<?php echo $content; ?>
		<?php elseif ( ! $content && $empty_text ) : ?>
			<p class="llms-sd-widget-empty"><?php echo esc_html( $empty_text ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Displays a date widget
 *
 * @param   string $title      Title of the widget.
 * @param   int    $timestamp  Timestamp used to display the date.
 * @param   string $empty_text Content (text) to display if $content is empty.
 * @return  void
 * @since   3.24.0
 * @version 3.24.0
 */
function llms_sd_dashboard_date_widget( $title, $timestamp, $empty_text = '' ) {

	$html = '';
	if ( $timestamp ) {
		ob_start();
		?>
		<div class="llms-sd-date">
			<span class="month"><?php echo date_i18n( 'F', $timestamp ); ?></span>
			<span class="day"><?php echo date_i18n( 'j', $timestamp ); ?></span>
			<span class="year"><?php echo date_i18n( 'Y', $timestamp ); ?></span>
			<span class="diff"><?php printf( __( '%s ago', 'lifterlms' ), llms_get_date_diff( $timestamp, current_time( 'timestamp' ) ) ); ?>
		</div>
		<?php
		$html = ob_get_clean();
	}

	llms_sd_dashboard_widget( $title, $html, $empty_text );

}

/**
 * Displays a donut chart widget
 *
 * @param    string $title  Title of the widget.
 * @param    float  $perc   donut chart percentage.
 * @param    string $text   Text to display within the donut.
 * @param    string $size   Size of the chart.
 * @return   void
 * @since    3.24.0
 * @version  3.24.0
 */
function llms_sd_dashboard_donut_widget( $title, $perc, $text = '', $size = 'medium' ) {

	llms_sd_dashboard_widget( $title, llms_get_donut( $perc, $text, $size ) );

}
