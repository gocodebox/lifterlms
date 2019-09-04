<?php
/**
 * Admin Status Pages
 *
 * @since 3.11.2
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Page_Status class.
 *
 * @since 3.11.2
 * @since 3.32.0 Add "Scheduled Actions" tab.
 * @since 3.33.1 Read log files using `llms_filter_input`.
 * @since 3.33.2 Fix undefined index when viewing log files.
 * @since 3.35.0 Sanitize input data.
 */
class LLMS_Admin_Page_Status {

	/**
	 * Handle tools actions
	 *
	 * @since 3.11.2
	 * @since 3.35.0 Sanitize input data.
	 *
	 * @return void
	 */
	private static function do_tool() {

		if ( ! llms_verify_nonce( '_wpnonce', 'llms_tool' ) ) {
			wp_die( __( 'Action failed. Please refresh the page and retry.', 'lifterlms' ) );
		}

		$tool = llms_filter_input( INPUT_POST, 'llms_tool', FILTER_SANITIZE_STRING );

		/**
		 * Custom Tools can hook into this action to do the tool action
		 */
		do_action( 'llms_status_tool', $tool );

		switch ( $tool ) {

			case 'automatic-payments':
				LLMS_Site::clear_lock_url();
				update_option( 'llms_site_url_ignore', 'no' );
				break;

			case 'clear-cache':
				global $wpdb;
				$wpdb->query(
					$wpdb->prepare(
						"DELETE FROM {$wpdb->prefix}usermeta WHERE meta_key = %s or meta_key = %s;",
						'llms_overall_progress',
						'llms_overall_grade'
					)
				);

				break;

			case 'clear-sessions':
				WP_Session_Utils::delete_all_sessions();
				break;

			case 'reset-tracking':
				update_option( 'llms_allow_tracking', 'no' );
				break;

			case 'setup-wizard':
				wp_safe_redirect( esc_url_raw( admin_url( '?page=llms-setup' ) ) );
				exit;
			break;

		}
	}

	/**
	 * Handle form / link actions on the status pages
	 *
	 * @return   void
	 * @since    3.11.2
	 * @version  3.11.2
	 */
	public static function handle_actions() {

		if ( ! empty( $_REQUEST['llms_delete_log'] ) ) {
			self::remove_log_file();
		} elseif ( ! empty( $_REQUEST['llms_tool'] ) ) {
			self::do_tool();
		}

	}

	/**
	 * Retrieve the URL to the status page
	 *
	 * @param    string $tab  optionally add a tab
	 * @return   string
	 * @since    3.11.2
	 * @version  3.11.2
	 */
	public static function get_url( $tab = null ) {
		$args = array(
			'page' => 'llms-status',
		);
		if ( $tab ) {
			$args['tab'] = $tab;
		}
		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * Retrieve an array of log files
	 *
	 * @return   array         log key => log file name
	 * @since    3.11.2
	 * @version  3.11.2
	 */
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
	 *
	 * @since 2.1.0
	 * @since 3.32.0 Add "Scheduled Actions" tab output.
	 * @since 3.35.0 Sanitize input data.
	 *
	 * @return void
	 */
	public static function output() {

		$tabs = apply_filters(
			'llms_admin_page_status_tabs',
			array(
				'report'           => __( 'System Report', 'lifterlms' ),
				'tools'            => __( 'Tools & Utilities', 'lifterlms' ),
				'logs'             => __( 'Logs', 'lifterlms' ),
				'action-scheduler' => __( 'Scheduled Actions', 'lifterlms' ),
			)
		);

		$current_tab = empty( $_GET['tab'] ) ? 'report' : llms_filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
		?>

		<div class="wrap lifterlms llms-status llms-status--<?php echo $current_tab; ?>">

			<nav class="llms-nav-tab-wrapper">
				<ul class="llms-nav-items">
				<?php
				foreach ( $tabs as $name => $label ) :
					$active = ( $current_tab == $name ) ? ' llms-active' : '';
					?>
					<li class="llms-nav-item<?php echo $active; ?>"><a class="llms-nav-link" href="<?php echo esc_url( self::get_url( $name ) ); ?>"><?php echo $label; ?></a></li>
				<?php endforeach; ?>
				</ul>
			</nav>

			<h1 style="display:none;"></h1>

			<?php
			do_action( 'llms_before_admin_page_status', $current_tab );

			switch ( $current_tab ) {

				case 'action-scheduler':
					ActionScheduler_AdminView::instance()->render_admin_ui();
					break;

				case 'logs':
					self::output_logs_content();
					break;

				case 'report':
					include_once 'class.llms.admin.system-report.php';
					LLMS_Admin_System_Report::output();
					break;

				case 'tools':
					self::output_tools_content();
					break;

			}

			do_action( 'llms_after_admin_page_status', $current_tab );
			?>

		</div>

		<?php
	}

	/**
	 * Delete a log file
	 *
	 * @since 3.11.2
	 * @since 3.35.0 Sanitize input data.
	 *
	 * @return   void
	 */
	private static function remove_log_file() {

		if ( ! llms_verify_nonce( '_wpnonce', 'delete_log', 'GET' ) ) {
			wp_die( __( 'Action failed. Please refresh the page and retry.', 'lifterlms' ) );
		}

		if ( ! empty( $_REQUEST['llms_delete_log'] ) ) {

			$logs   = self::get_logs();
			$handle = sanitize_title( wp_unslash( $_REQUEST['llms_delete_log'] ) );

			$log = isset( $logs[ $handle ] ) ? $logs[ $handle ] : false;
			if ( ! $log ) {
				return;
			}

			$file = LLMS_LOG_DIR . $log;

			if ( is_file( $file ) && is_writable( $file ) ) {
				unlink( $file );
				wp_safe_redirect( esc_url_raw( self::get_url( 'logs' ) ) );
				exit();
			}
		}

	}

	/**
	 * Output the HTML for the Logs tab
	 *
	 * @since 3.11.2
	 * @since 3.33.1 Use `llms_filter_input` to read current log file.
	 * @since 3.33.2 Fix undefined variable notice.
	 *
	 * @return   void
	 */
	private static function output_logs_content() {

		$logs        = self::get_logs();
		$date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

		$current = llms_filter_input( INPUT_POST, 'llms_log_file', FILTER_SANITIZE_STRING );

		if ( $logs && ! $current ) {
			$log_keys = array_keys( $logs );
			$current  = array_shift( $log_keys );
		}

		if ( $logs ) :
			?>

			<form action="<?php echo esc_url( self::get_url( 'logs' ) ); ?>" method="POST">
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
				<a class="llms-button-danger small" href="
				<?php
				echo esc_url(
					wp_nonce_url(
						add_query_arg(
							array(
								'llms_delete_log' => $current,
							),
							admin_url( 'admin.php?page=llms-status&tab=logs' )
						),
						'delete_log'
					)
				);
				?>
				""><?php _e( 'Delete Log', 'lifterlms' ); ?></a>
			</h2>

			<div class="llms-log-viewer">
				<pre><?php echo esc_html( file_get_contents( LLMS_LOG_DIR . $logs[ $current ] ) ); ?></pre>
			</div>

		<?php else : ?>
			<div class="updated"><p><?php _e( 'There are currently no logs to view.', 'lifterlms' ); ?></p></div>
			<?php
		endif;

	}

	/**
	 * Output the HTML for the tools tab
	 *
	 * @return   void
	 * @since    3.11.2
	 * @version  3.11.2
	 */
	private static function output_tools_content() {

		$tools = apply_filters(
			'llms_status_tools',
			array(

				'automatic-payments' => array(
					'description' => __( 'Allows you to choose to enable or disable automatic recurring payments which may be disabled on a staging site.', 'lifterlms' ),
					'label'       => __( 'Automatic Payments', 'lifterlms' ),
					'text'        => __( 'Reset Automatic Payments', 'lifterlms' ),
				),

				'clear-sessions'     => array(
					'description' => __( 'Manage User Sessions. LifterLMS creates custom user sessions to manage, payment processing, quizzes and user registration. If you are experiencing issues or incorrect error messages are displaying. Clearing out all of the user session data may help.', 'lifterlms' ),
					'label'       => __( 'User Sessions', 'lifterlms' ),
					'text'        => __( 'Clear All Session Data', 'lifterlms' ),
				),

				'reset-tracking'     => array(
					'description' => __( 'If you opted into LifterLMS Tracking and no longer wish to participate, you may opt out here.', 'lifterlms' ),
					'label'       => __( 'Reset Tracking Settings', 'lifterlms' ),
					'text'        => __( 'Reset Tracking Settings', 'lifterlms' ),
				),

				'clear-cache'        => array(
					'description' => __( 'Clears the cached data displayed on various reporting screens. This does not affect actual student progress, it only clears cached progress data. This data will be regenerated the next time it is accessed.', 'lifterlms' ),
					'label'       => __( 'Student Progress Cache', 'lifterlms' ),
					'text'        => __( 'Clear cache', 'lifterlms' ),
				),

				'setup-wizard'       => array(
					'description' => __( 'If you want to run the LifterLMS Setup Wizard again or skipped it and want to return now, click below.', 'lifterlms' ),
					'label'       => __( 'Setup Wizard', 'lifterlms' ),
					'text'        => __( 'Return to Setup Wizard', 'lifterlms' ),
				),

			)
		);

		?>
		<form action="<?php echo esc_url( self::get_url( 'tools' ) ); ?>" method="POST">
			<table class="llms-table text-left zebra">
			<?php foreach ( $tools as $slug => $data ) : ?>
				<tr>
					<th><?php echo $data['label']; ?></th>
					<td>
						<p><?php echo $data['description']; ?></p>
						<button class="llms-button-secondary small" name="llms_tool" type="submit" value="<?php echo $slug; ?>"><?php echo $data['text']; ?></button>
					</td>
				</tr>
			<?php endforeach; ?>
			</table>
			<?php wp_nonce_field( 'llms_tool' ); ?>
		</form>
		<?php

	}

}
