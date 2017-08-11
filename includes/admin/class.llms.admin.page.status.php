<?php
/**
 * Admin System Report Class
 *
 * @since    2.1.0
 * @version  3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Page_Status {

	public static function handle_actions() {

		if ( ! empty( $_REQUEST['log'] ) ) {
			self::remove_log_file();
		}

	}

	private static function get_logs() {

		$files  = @scandir( LLMS_LOG_DIR );
		$result = array();
		if ( ! empty( $files ) ) {
			foreach ( $files as $key => $value ) {
				if ( ! in_array( $value, array( '.', '..' ) ) ) {
					if ( ! is_dir( $value ) && strstr( $value, '.log' ) ) {
						$result[ sanitize_title( $value ) ] = $value;
					}
				}
			}
		}
		return $result;

	}

	/**
	 * Output the system report
	 * @return   void
	 * @since    2.1.0
		 * @version  3.0.0
	 */
	public static function output() {

		$tabs = array(
			'report' => __( 'System Report', 'lifterlms' ),
			// 'tools' => __( 'Tools & Utilities', 'lifterlms' ),
			'logs' => __( 'Logs', 'lifterlms' ),
		);

		$current_tab = ! isset( $_GET['tab'] ) ? 'report' : sanitize_text_field( $_GET['tab'] );

		?>

		<div class="wrap lifterlms llms-status">

			<nav class="llms-nav-tab-wrapper">
				<ul class="llms-nav-items">
				<?php foreach ( $tabs as $name => $label ) :
					$active = ( $current_tab == $name ) ? ' llms-active' : ''; ?>
					<li class="llms-nav-item<?php echo $active; ?>"><a class="llms-nav-link" href="<?php echo admin_url( 'admin.php?page=llms-status&tab=' . $name ); ?>"><?php echo $label; ?></a></li>
				<?php endforeach; ?>
				</ul>
			</nav>

			<h1 style="display:none;"></h1>

			<?php do_action( 'llms_before_admin_page_status' );

			switch ( $current_tab ) {
				case 'report':
					include_once 'class.llms.admin.system-report.php';
					LLMS_Admin_System_Report::output();
				break;

				case 'logs':
					self::output_logs_content();
				break;

			}

			do_action( 'llms_after_admin_page_status' ); ?>

		</div>

		<?php
	}

	private static function remove_log_file() {

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'delete_log' ) ) {
			wp_die( __( 'Action failed. Please refresh the page and retry.', 'woocommerce' ) );
		}

		if ( ! empty( $_REQUEST['log'] ) ) {

			$logs = self::get_logs();
			$handle = sanitize_title( $_REQUEST['log'] );

			$log = isset( $logs[ $handle ] ) ? $logs[ $handle ] : false;
			if ( ! $log ) {
				return;
			}

			$file = LLMS_LOG_DIR . $log;

			if ( is_file( $file ) && is_writable( $file ) ) {
				unlink( $file );
				wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=llms-status&tab=logs' ) ) );
				exit();
			}

		}

	}

	private static function output_logs_content() {

		$logs = self::get_logs();
		$date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

		if ( $logs && ! isset( $_POST['llms_log_file'] ) ) {
			$log_keys = array_keys( $logs );
			$current = array_shift( $log_keys );
		} else {
			$current = sanitize_title( $_POST['llms_log_file'] );
		}

		if ( $logs ) : ?>

			<form action="" method="POST">
				<select name="llms_log_file">
					<?php foreach ( $logs as $name => $file ) : ?>
						<option value="<?php echo esc_attr( $name ); ?>" <?php selected( sanitize_title( $current ), $name ); ?>>
							<?php echo esc_html( $file ); ?>
							(<?php echo date_i18n( $date_format, filemtime( LLMS_LOG_DIR . $file ) ); ?>)
						</option>
					<?php endforeach; ?>
				</select>
				<button class="llms-button-secondary small" type="submit"><?php _e( 'View Log', 'lifterlms' ); ?></button>
			</form>

			<h2>
				<?php printf( esc_html__( 'Viewing: %s', 'lifterlms' ), $logs[ $current ] ); ?>
				<a class="llms-button-danger small" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'log' => $current ), admin_url( 'admin.php?page=llms-status&tab=logs' ) ), 'delete_log' ) ); ?>""><?php _e( 'Delete Log', 'lifterlms' ); ?></a>
			</h2>

			<div class="llms-log-viewer">
				<pre><?php echo esc_html( file_get_contents( LLMS_LOG_DIR . $logs[ $current ] ) ); ?></pre>
			</div>

		<?php else : ?>
			<div class="updated"><p><?php _e( 'There are currently no logs to view.', 'lifterlms' ); ?></p></div>
		<?php endif;

	}

}
