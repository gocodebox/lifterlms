<?php
/**
 * Serves Export CSVs on the admin panel
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 3.28.1
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Export_Download class
 *
 * @since 3.28.1
 */
class LLMS_Admin_Export_Download {

	/**
	 * Constructor.
	 *
	 * @since   3.28.1
	 * @version 3.28.1
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'maybe_serve_export' ) );
	}

	/**
	 * Serve an export file as a download.
	 *
	 * @since 3.28.1
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 * @since 7.5.0 Check nonce and only consider the basename of the file to be downloaded.
	 *
	 * @return void
	 */
	public function maybe_serve_export() {

		$export = llms_filter_input( INPUT_GET, 'llms-dl-export', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( ! $export ) {
			return;
		}

		// Verify nonce.
		if ( ! llms_verify_nonce( 'llms_dl_export_nonce', LLMS_Abstract_Exportable_Admin_Table::EXPORT_NONCE_ACTION, 'GET' ) ) {
			wp_die( esc_html__( 'Cheatin&#8217; huh?', 'lifterlms' ) );
		}

		// Only allow people who can view reports view exports.
		if ( ! current_user_can( 'view_others_lifterlms_reports' ) && ! current_user_can( 'view_lifterlms_reports' ) ) {
			wp_die( esc_html__( 'Cheatin&#8217; huh?', 'lifterlms' ) );
		}

		$path = LLMS_TMP_DIR . basename( $export );
		if ( ! file_exists( $path ) ) {
			wp_die( esc_html__( 'Cheatin&#8217; huh?', 'lifterlms' ) );
		}

		$info = pathinfo( $path );
		if ( 'csv' !== $info['extension'] ) {
			wp_die( esc_html__( 'Cheatin&#8217; huh?', 'lifterlms' ) );
		}

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $export . '"' );

		$file = file_get_contents( $path );
		unlink( $path );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $file;
		exit;
	}
}

return new LLMS_Admin_Export_Download();
