<?php
/**
 * Log file viewer
 *
 * Used on the LifterLMS Admin Status Logs tab to output a single log file.
 *
 * @package LifterLMS/Admin/Views
 *
 * @since 3.37.14
 * @version 3.37.14
 *
 * @property array[] $logs       Associative array of log files. The array key is the file "slug" and the value is the file's absolute path.
 * @property string  $current    Slug of the current log file.
 * @property string  $delete_url Nonce url to delete the log file (if the log is deletable).
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="llms-setting-group top">

	<p class="llms-label"><?php esc_html_e( 'View and Manage Logs', 'lifterlms' ); ?></p>

	<form action="<?php echo esc_url( LLMS_Admin_Page_Status::get_url( 'logs' ) ); ?>" method="POST">
		<select name="llms_log_file">
			<?php foreach ( $logs as $name => $file ) : ?>
				<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $current, $name ); ?>>
					<?php echo esc_html( basename( $file ) ); ?>
					(<?php echo esc_html( date_i18n( $date_format, filemtime( $file ) ) ); ?>)
				</option>
			<?php endforeach; ?>
		</select>
		<button class="llms-button-secondary small" type="submit"><?php esc_html_e( 'View Log', 'lifterlms' ); ?></button>
	</form>
	<hr />
	<h2>
		<?php
		printf(
			// Translators: %s = File name of the log.
			esc_html__( 'Viewing: %s', 'lifterlms' ),
			esc_html(
				basename(
					$logs[ $current ]
				)
			)
		);
		?>
		<?php if ( $delete_url ) : ?>
			<a class="llms-button-danger small" href="<?php echo esc_url( $delete_url ); ?>"><?php esc_html_e( 'Delete', 'lifterlms' ); ?></a>
		<?php endif; ?>
	</h2>

	<div class="llms-log-viewer">
		<pre><?php echo esc_html( file_get_contents( $logs[ $current ] ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Not a remote URL. ?></pre>
	</div>

</div>
