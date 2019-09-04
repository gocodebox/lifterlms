<?php
/**
 * Serves Export CSVs on the admin panel
 *
 * @since   3.28.1
 * @version 3.28.1
 */
defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Export_Download class.
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
	 * Serve an export file as a download
	 *
	 * @return  void
	 * @since   3.28.1
	 * @version 3.28.1
	 */
	public function maybe_serve_export() {

		$export = filter_input( INPUT_GET, 'llms-dl-export', FILTER_SANITIZE_STRING );
		if ( ! $export ) {
			return;
		}

		// Only allow people who can view reports view exports.
		if ( ! current_user_can( 'view_others_lifterlms_reports' ) && ! current_user_can( 'view_lifterlms_reports' ) ) {
			wp_die( __( 'Cheatin&#8217; huh?', 'lifterlms' ) );
		}

		$path = LLMS_TMP_DIR . $export;
		if ( ! file_exists( $path ) ) {
			wp_die( __( 'Cheatin&#8217; huh?', 'lifterlms' ) );
		}

		$info = pathinfo( $path );
		if ( 'csv' !== $info['extension'] ) {
			wp_die( __( 'Cheatin&#8217; huh?', 'lifterlms' ) );
		}

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $export . '"' );

		$file = file_get_contents( $path );
		unlink( $path );
		echo $file;
		exit;

	}

}

return new LLMS_Admin_Export_Download();
