<?php
/**
 * Admin Import Screen and form submission handling
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 3.3.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Import Screen and form submission handling
 *
 * @since 3.3.0
 * @since 3.30.1 Explicitly include template functions during imports.
 * @since 3.35.0 Initialize at `admin_init` instead of `init`.
 *               Import template from the admin views directory instead of the frontend templates directory.
 *               Improve error handling
 */
class LLMS_Admin_Import {

	/**
	 * Constructor
	 *
	 * @since 3.3.0
	 * @since 3.35.0 Initialize at `admin_init` instead of `init`.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'upload_import' ) );
	}

	/**
	 * Localize statistic information for display on success.
	 *
	 * @since 3.35.0
	 *
	 * @param string $stat Statistic key name.
	 * @return string
	 */
	protected function localize_stat( $stat ) {

		switch ( $stat ) {

			case 'authors':
				$name = __( 'Authors', 'lifterlms' );
				break;

			case 'courses':
				$name = __( 'Courses', 'lifterlms' );
				break;

			case 'sections':
				$name = __( 'Sections', 'lifterlms' );
				break;

			case 'lessons':
				$name = __( 'Lessons', 'lifterlms' );
				break;

			case 'plans':
				$name = __( 'Plans', 'lifterlms' );
				break;

			case 'quizzes':
				$name = __( 'Quizzes', 'lifterlms' );
				break;

			case 'questions':
				$name = __( 'Questions', 'lifterlms' );
				break;

			case 'terms':
				$name = __( 'Terms', 'lifterlms' );
				break;

			default:
				$name = $stat;

		}// End switch().

		return $name;

	}

	/**
	 * Handle HTML output on the screen
	 *
	 * @since 3.3.0
	 * @since 3.35.0 Import template from the admin views directory instead of the frontend templates directory.
	 *
	 * @return void
	 */
	public static function output() {
		include 'views/import.php';
	}

	/**
	 * Handle form submission
	 *
	 * @since 3.3.0
	 * @since 3.30.1 Explicitly include template functions.
	 * @since 3.35.0 Validate nonce and user permissions before processing import data.
	 *               Moved statistic localization into its own function.
	 *               UpdateD return signature.
	 *
	 * @return boolean|WP_Error false for nonce or permission errors, WP_Error when an error is encountered, true on success.
	 */
	public function upload_import() {

		if ( ! llms_verify_nonce( 'llms_importer_nonce', 'llms-importer' ) || empty( $_FILES['llms_import'] ) ) {
			return false;
		}

		if ( ! current_user_can( 'manage_lifterlms' ) ) {
			return false;
		}

		// Fixes an issue where hooks are loaded in an unexpected order causing template functions required to parse an import aren't available.
		LLMS()->include_template_functions();

		$validate = $this->validate_upload( wp_unslash( $_FILES['llms_import'] ) ); // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// File upload error.
		if ( is_wp_error( $validate ) ) {
			LLMS_Admin_Notices::flash_notice( $validate->get_error_message(), 'error' );
			return $validate;
		}

		$raw = ! empty( $_FILES['llms_import']['tmp_name'] ) ? file_get_contents( sanitize_text_field( wp_unslash( $_FILES['llms_import']['tmp_name'] ) ) ) : array();

		$generator = new LLMS_Generator( $raw );
		if ( is_wp_error( $generator->set_generator() ) ) {
			LLMS_Admin_Notices::flash_notice( $generator->error->get_error_message(), 'error' );
			return $generater->error;
		}

		$generator->generate();
		if ( $generator->is_error() ) {
			LLMS_Admin_Notices::flash_notice( $generator->error->get_error_message(), 'error' );
			return $generator->error;
		}

		$msg  = '<strong>' . __( 'Import Successful', 'lifterlms' ) . '</strong><br>';
		$msg .= '<ul>';
		foreach ( $generator->get_results() as $stat => $count ) {
			$msg .= '<li>' . sprintf( '%s: %d', $this->localize_stat( $stat ), $count ) . '</li>';
		}
		$msg .= '</ul>';

		LLMS_Admin_Notices::flash_notice( $msg, 'success' );
		return true;

	}

	/**
	 * Validate the uploaded file
	 *
	 * @since 3.3.0
	 * @since 3.35.0 Fix undefined variable error.
	 *
	 * @link https://www.php.net/manual/en/features.file-upload.errors.php
	 *
	 * @param array $file  array of file data.
	 * @return WP_Error|true
	 */
	private function validate_upload( $file ) {

		if ( ! empty( $file['error'] ) ) {

			switch ( $file['error'] ) {

				case UPLOAD_ERR_INI_SIZE:
					$msg = __( 'The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'lifterlms' );
					break;

				case UPLOAD_ERR_FORM_SIZE:
					$msg = __( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', 'lifterlms' );
					break;

				case UPLOAD_ERR_PARTIAL:
					$msg = __( 'The uploaded file was only partially uploaded.', 'lifterlms' );
					break;

				case UPLOAD_ERR_NO_FILE:
					$msg = __( 'No file was uploaded.', 'lifterlms' );
					break;

				case UPLOAD_ERR_NO_TMP_DIR:
					$msg = __( 'Missing a temporary folder.', 'lifterlms' );
					break;

				case UPLOAD_ERR_CANT_WRITE:
					$msg = __( 'Failed to write file to disk.', 'lifterlms' );
					break;

				case UPLOAD_ERR_EXTENSION:
					$msg = __( 'File upload stopped by extension.', 'lifterlms' );
					break;

				default:
					$msg = __( 'Unknown upload error.', 'lifterlms' );

			}
		} else {

			$info = pathinfo( $file['name'] );

			if ( 'json' !== strtolower( $info['extension'] ) ) {
				$msg = __( 'Only valid JSON files can be imported.', 'lifterlms' );
			}
		}// End if().

		if ( ! empty( $msg ) ) {
			return new WP_Error( 'llms_import_file_error', $msg );
		}

		return true;

	}

}

return new LLMS_Admin_Import();
