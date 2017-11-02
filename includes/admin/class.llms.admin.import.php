<?php
/**
 * Admin Import Screen and form submission handling
 * @since   3.3.0
 * @version 3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Import {

	/**
	 * Constructor
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'upload_import' ) );
	}

	/**
	 * Handle HTML output on the screen
	 * @return   void
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public static function output() {
		llms_get_template( 'admin/import/import.php' );
	}

	/**
	 * Handle form submission
	 * @return   void
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function upload_import() {

		if ( ! isset( $_FILES['llms_import'] ) || ! $_FILES['llms_import'] ) {
			return;
		}

		$validate = $this->validate_upload( $_FILES['llms_import'] );

		if ( is_wp_error( $validate ) ) {
			return LLMS_Admin_Notices::flash_notice( $validate->get_error_message(), 'error' );
		}

		$raw = file_get_contents( $_FILES['llms_import']['tmp_name'] );

		$generator = new LLMS_Generator( $raw );
		if ( is_wp_error( $generator->set_generator() ) ) {
			return LLMS_Admin_Notices::flash_notice( $generator->error->get_error_message(), 'error' );
		} else {
			$generator->generate();
			if ( $generator->is_error( ) ) {
				return LLMS_Admin_Notices::flash_notice( $generator->error->get_error_message(), 'error' );
			} else {

				$msg = '<strong>' . __( 'Import Successful', 'lifterlms' ) . '</strong><br>';

				$msg .= '<ul>';

				foreach ( $generator->get_results() as $stat => $count ) {

					// translate like a boss ya'll
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

					}

					$msg .= '<li>' . sprintf( '%s: %d', $name, $count ) . '</li>';

				}// End foreach().

				$msg .= '</ul>';

				return LLMS_Admin_Notices::flash_notice( $msg, 'success' );
			}// End if().
		}// End if().

	}

	/**
	 * Validate the uploaded file
	 * @param    arry     $file  array of file data
	 * @return   WP_Error|true
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	private function validate_upload( $file ) {

		if ( ! empty( $file['error'] ) ) {

			switch ( $file['error'] ) {
				case UPLOAD_ERR_INI_SIZE:
					$error_message = __( 'The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'lifterlms' );
				break;

				case UPLOAD_ERR_FORM_SIZE:
					$error_message = __( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', 'lifterlms' );
				break;

				case UPLOAD_ERR_PARTIAL:
					$error_message = __( 'The uploaded file was only partially uploaded.', 'lifterlms' );
				break;

				case UPLOAD_ERR_NO_FILE:
					$error_message = __( 'No file was uploaded.', 'lifterlms' );
				break;

				case UPLOAD_ERR_NO_TMP_DIR:
					$error_message = __( 'Missing a temporary folder.', 'lifterlms' );
				break;

				case UPLOAD_ERR_CANT_WRITE:
					$error_message = __( 'Failed to write file to disk.', 'lifterlms' );
				break;

				case UPLOAD_ERR_EXTENSION:
					$error_message = __( 'File upload stopped by extension.', 'lifterlms' );
				break;

				default:
					$error_message = __( 'Unknown upload error.', 'lifterlms' );
				break;
			}
		} else {
			$info = pathinfo( $file['name'] );

			if ( 'json' !== strtolower( $info['extension'] ) ) {
				$msg = __( 'Only valid JSON files can be imported.', 'lifterlms' );
			}
		}// End if().
		if ( ! empty( $msg ) ) {
			return new WP_Error( 'upload-error', $msg );
		}

		return true;

	}

}

return new LLMS_Admin_Import();
