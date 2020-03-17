<?php
/**
 * Log file viewer
 *
 * Used on the LifterLMS Admin Status Logs tab to output a single log file.
 *
 * @package LifterLMS/Admin/Views
 *
 * @since [version]
 * @version [version]
 *
 * @property array[] $logs    Associative array of log files. The array key is the file "slug" and the value is the file's absolute path.
 * @property string  $current Slug of the current log file.
 */

defined( 'ABSPATH' ) || exit;

// Nonce URL to delete a log file.
$delete_url = 'debug-log' === $current ? '' : wp_nonce_url(
	add_query_arg(
		array(
			'llms_delete_log' => $current,
		),
		admin_url( 'admin.php?page=llms-status&tab=logs' )
	),
	'delete_log'
);
?>

<form action="<?php echo esc_url( LLMS_Admin_Page_Status::get_url( 'logs' ) ); ?>" method="POST">
	<select name="llms_log_file">
		<?php foreach ( $logs as $name => $file ) : ?>
			<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $current, $name ); ?>>
				<?php echo esc_html( basename( $file ) ); ?>
				(<?php echo date_i18n( $date_format, filemtime( $file ) ); ?>)
			</option>
		<?php endforeach; ?>
	</select>
	<button class="llms-button-secondary small" type="submit"><?php _e( 'View Log', 'lifterlms' ); ?></button>
</form>

<h2>
	<?php printf( esc_html__( 'Viewing: %s', 'lifterlms' ), basename( $logs[ $current ] ) ); ?>
	<?php if ( $delete_url ) : ?>
		<a class="llms-button-danger small" href="<?php echo esc_url( $delete_url ); ?>"><?php _e( 'Delete', 'lifterlms' ); ?></a>
	<?php endif; ?>
</h2>

<div class="llms-log-viewer">
	<pre><?php echo esc_html( file_get_contents( $logs[ $current ] ) ); ?></pre>
</div>
