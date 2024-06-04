<?php
/**
 * Admin Status Pages
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 3.11.2
 * @version 5.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Page_Status class
 *
 * @since 3.11.2
 * @since 3.32.0 Add "Scheduled Actions" tab.
 * @since 3.33.1 Read log files using `llms_filter_input`.
 * @since 3.33.2 Fix undefined index when viewing log files.
 * @since 3.35.0 Sanitize input data.
 * @since 3.37.14 Added the WP Core `debug.log` file as log that's viewable via the log viewer.
 * @since 4.0.0 The `clear-sessions` tool has been moved to `LLMS_Admin_Tool_Clear_Sessions`.
 */
class LLMS_Admin_Page_Status {

	/**
	 * Register "unclassed" core tools
	 *
	 * @since 4.13.0
	 *
	 * @param array[] $tools List of tool definitions.
	 * @return array[]
	 */
	public static function add_core_tools( $tools ) {

		return array_merge(
			$tools,
			array(

				'reset-tracking' => array(
					'description' => __( 'If you opted into LifterLMS Tracking and no longer wish to participate, you may opt out here.', 'lifterlms' ),
					'label'       => __( 'Reset Tracking Settings', 'lifterlms' ),
					'text'        => __( 'Reset Tracking Settings', 'lifterlms' ),
				),

				'clear-cache'    => array(
					'description' => __( 'Clears the cached data displayed on various reporting screens. This does not affect actual student progress, it only clears cached progress data. This data will be regenerated the next time it is accessed.', 'lifterlms' ),
					'label'       => __( 'Student Progress Cache', 'lifterlms' ),
					'text'        => __( 'Clear cache', 'lifterlms' ),
				),

				'setup-wizard'   => array(
					'description' => __( 'If you want to run the LifterLMS Setup Wizard again or skipped it and want to return now, click below.', 'lifterlms' ),
					'label'       => __( 'Setup Wizard', 'lifterlms' ),
					'text'        => __( 'Return to Setup Wizard', 'lifterlms' ),
				),

			)
		);
	}

	/**
	 * Handle tools actions
	 *
	 * @since 3.11.2
	 * @since 3.35.0 Sanitize input data.
	 * @since 3.37.14 Verify user capabilities when doing a tool action.
	 *                Use `llms_redirect_and_exit()` in favor of `wp_safe_redirect()`.
	 * @since 4.0.0 The `clear-sessions` tool has been moved to `LLMS_Admin_Tool_Clear_Sessions`.
	 * @since 4.13.0 The `automatic-payments` tool has been moved to `LLMS_Admin_Tool_Reset_Automatic_Payments`.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return void
	 */
	private static function do_tool() {

		if ( ! llms_verify_nonce( '_wpnonce', 'llms_tool' ) || ! current_user_can( 'manage_lifterlms' ) ) {
			wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'lifterlms' ) );
		}

		$tool = llms_filter_input_sanitize_string( INPUT_POST, 'llms_tool' );

		/**
		 * Custom and 3rd party tools can use this action to perform the tool's action
		 *
		 * @since Unknown
		 *
		 * @see llms_status_tools For the filter used to register tools.
		 *
		 * @param string $tool Tool name or ID.
		 */
		do_action( 'llms_status_tool', $tool );

		switch ( $tool ) {

			case 'clear-cache':
				global $wpdb;
				$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					"DELETE FROM {$wpdb->usermeta} WHERE meta_key = 'llms_overall_progress' or meta_key = 'llms_overall_grade';"
				);

				break;

			case 'reset-tracking':
				update_option( 'llms_allow_tracking', 'no' );
				break;

			case 'setup-wizard':
				llms_redirect_and_exit( esc_url_raw( admin_url( 'admin.php?page=llms-setup' ) ) );
				break;

		}
	}

	/**
	 * Handle form / link actions on the status pages
	 *
	 * @since 3.11.2
	 *
	 * @return void
	 */
	public static function handle_actions() {

		if ( ! empty( $_REQUEST['llms_delete_log'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonces are verified elsewhere.
			self::remove_log_file();
		} elseif ( ! empty( $_REQUEST['llms_tool'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonces are verified elsewhere.
			self::do_tool();
		}
	}

	/**
	 * Retrieve the URL to the status page
	 *
	 * @since 3.11.2
	 *
	 * @param string $tab Optionally add a tab.
	 * @return string
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
	 * @since 3.11.2
	 * @since 3.37.14 Add the WP debug.log file to the array if `WP_DEBUG_LOG` is enabled.
	 *
	 * @return array[] Associative array of log files. The array key is the file "slug" and the value is the file's absolute path.
	 */
	private static function get_logs() {

		$result = array();

		// Retrieve all the files in our log directory.
		$files = @scandir( LLMS_LOG_DIR ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- It's okay though.
		if ( ! empty( $files ) ) {
			foreach ( $files as $key => $value ) {

				// Ignore directory dots, directories, and non .log files.
				if ( in_array( $value, array( '.', '..' ), true ) || is_dir( $value ) || ! strstr( $value, '.log' ) ) {
					continue;
				}

				$result[ sanitize_title( $value ) ] = LLMS_LOG_DIR . $value;

			}
		}

		// Add the site's debug.log or native error log file if it exists.
		$err_path = ini_get( 'error_log' );
		if ( $err_path ) {
			$result['debug-log'] = $err_path;
		}

		return $result;
	}

	/**
	 * Output the system report
	 *
	 * @since 2.1.0
	 * @since 3.32.0 Add "Scheduled Actions" tab output.
	 * @since 3.35.0 Sanitize input data.
	 * @since 3.37.14 Use strict comparators.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 * @since 6.0.0 Removed loading of class files that don't instantiate their class in favor of autoloading.
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

		$current_tab = empty( $_GET['tab'] ) ? 'report' : llms_filter_input_sanitize_string( INPUT_GET, 'tab' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- We're not processing the form data.
		?>

		<div class="wrap lifterlms llms-status llms-status--<?php echo esc_attr( $current_tab ); ?>">

			<nav class="llms-nav-tab-wrapper llms-nav-secondary">
				<ul class="llms-nav-items">
				<?php
				foreach ( $tabs as $name => $label ) :
					$active = ( $current_tab === $name ) ? ' llms-active' : '';
					?>
					<li class="llms-nav-item<?php echo esc_attr( $active ); ?>"><a class="llms-nav-link" href="<?php echo esc_url( self::get_url( $name ) ); ?>"><?php echo esc_html( $label ); ?></a></li>
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
	 * @since 3.37.14 Added user capability check.
	 *
	 * @return void
	 */
	private static function remove_log_file() {

		if ( ! llms_verify_nonce( '_wpnonce', 'delete_log', 'GET' ) || ! current_user_can( 'manage_lifterlms' ) ) {
			wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'lifterlms' ) );
		}

		if ( ! empty( $_REQUEST['llms_delete_log'] ) ) {

			$logs   = self::get_logs();
			$handle = sanitize_title( wp_unslash( $_REQUEST['llms_delete_log'] ) );
			$log    = isset( $logs[ $handle ] ) ? $logs[ $handle ] : false;

			if ( $log && is_file( $log ) && is_writable( $log ) ) {
				unlink( $log );
				llms_redirect_and_exit( esc_url_raw( self::get_url( 'logs' ) ) );
			}
		}
	}

	/**
	 * Output the HTML for the Logs tab
	 *
	 * @since 3.11.2
	 * @since 3.33.1 Use `llms_filter_input` to read current log file.
	 * @since 3.33.2 Fix undefined variable notice.
	 * @since 3.37.14 Moved HTML output to the view file located at includes/admin/views/status/view-log.php.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return void
	 */
	private static function output_logs_content() {

		$logs        = self::get_logs();
		$date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

		$current = sanitize_title( llms_filter_input_sanitize_string( INPUT_POST, 'llms_log_file' ) );

		if ( $logs && ! $current ) {
			$log_keys = array_keys( $logs );
			$current  = array_shift( $log_keys );
		}

		if ( $logs ) {

			// Nonce URL to delete a log file.
			$delete_url = 'debug-log' === $current ? '' : wp_nonce_url(
				add_query_arg(
					array(
						'llms_delete_log' => $current,
					),
					self::get_url( 'logs' )
				),
				'delete_log'
			);

			include_once 'views/status/view-log.php';

		} else {
			echo '<div class="llms-log-viewer">' . esc_html__( 'There are currently no logs to view.', 'lifterlms' ) . '</div>';
		}
	}

	/**
	 * Output the HTML for the tools tab
	 *
	 * @since 3.11.2
	 * @since 4.0.0 The `clear-sessions` tool has been moved to `LLMS_Admin_Tool_Clear_Sessions`.
	 * @since 4.13.0 Move "unclassed" core actions to be added to the `llms_status_tools` filter at priority 5 via `LLMS_Admin_Page_Status::add_core_tools()`.
	 *
	 * @return void
	 */
	private static function output_tools_content() {

		// Load unclassed core tools at priority 5 to "preserve" their original order before we started classing tools.
		add_filter( 'llms_status_tools', array( __CLASS__, 'add_core_tools' ), 5 );

		/**
		 * Register tools with the LifterLMS core
		 *
		 * When registering a custom tool you should additionally have an action triggered for the tool using the action
		 * `llms_status_tool` which will be called to process or handle the action.
		 *
		 * @since Unknown
		 *
		 * @see llms_status_tool For the action called to handle a tool.
		 *
		 * @param array[] $tools {
		 *     Associative array of status tool definitions.
		 *
		 *     The array key is a unique "id" for the tool and the array value should be an associative array
		 *     as described below:
		 *
		 *     @type string $description Description of what the tool does.
		 *     @type string $label       The title of the tool.
		 *     @type string $text        The text displayed on the tool's button.
		 * }
		 */
		$tools = apply_filters( 'llms_status_tools', array() );

		?>
		<form action="<?php echo esc_url( self::get_url( 'tools' ) ); ?>" method="POST">
			<div class="llms-setting-group top">
				<p class="llms-label"><?php esc_html_e( 'Tools & Utilities', 'lifterlms' ); ?></p>
				<table class="llms-table text-left zebra">
				<?php foreach ( $tools as $slug => $data ) : ?>
					<tr>
						<th><?php echo esc_html( $data['label'] ); ?></th>
						<td>
							<p><?php echo wp_kses_post( $data['description'] ); ?></p>
							<button class="llms-button-secondary small" name="llms_tool" type="submit" value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $data['text'] ); ?></button>
						</td>
					</tr>
				<?php endforeach; ?>
				</table>
				<?php wp_nonce_field( 'llms_tool' ); ?>
			</div>
		</form>
		<?php
	}
}
