<?php
/**
 * Course stream selector
 *
 * Dropdown shown above the course syllabus so enrolled students can switch streams.
 *
 * @package LifterLMS/Templates
 *
 * @since [version]
 * @version [version]
 *
 * @property LLMS_Course  $course  Course object.
 * @property array        $streams Course streams.
 * @property string       $current Current stream id.
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $course ) || empty( $streams ) ) {
	return;
}
?>
<div class="llms-stream-selector-wrapper">
	<form method="post" class="llms-stream-selector" action="">
		<?php wp_nonce_field( 'llms_change_stream', 'llms_change_stream_nonce' ); ?>
		<input type="hidden" name="llms_change_stream" value="1">
		<input type="hidden" name="llms_stream_course_id" value="<?php echo esc_attr( $course->get( 'id' ) ); ?>">
		<label for="llms_stream_id"><?php esc_html_e( 'Stream', 'lifterlms' ); ?></label>
		<select name="llms_stream_id" id="llms_stream_id" onchange="this.form.submit()">
			<?php foreach ( $streams as $stream ) : ?>
				<option value="<?php echo esc_attr( $stream['id'] ); ?>"<?php selected( $current, $stream['id'] ); ?>>
					<?php echo esc_html( $stream['name'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<noscript>
			<button type="submit" class="llms-button-secondary"><?php esc_html_e( 'Update', 'lifterlms' ); ?></button>
		</noscript>
	</form>
</div>
