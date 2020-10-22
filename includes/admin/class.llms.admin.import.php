<?php
/**
 * Admin Import Screen and form submission handling
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 3.3.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Import Screen and form submission handling class
 *
 * @since 3.3.0
 * @since 3.30.1 Explicitly include template functions during imports.
 * @since 3.35.0 Initialize at `admin_init` instead of `init`.
 *               Import template from the admin views directory instead of the frontend templates directory.
 *               Improve error handling.
 * @since 3.36.3 Fixed a typo where "$generator" was spelled "$generater".
 * @since 3.37.3 Don't unslash uploaded file `tmp_name`.
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
	 * Convert an array of generated content IDs to a list of anchor tags to edit the generated content
	 *
	 * @since [version]
	 *
	 * @param int[]  $ids  Array of object IDs. Either WP_Post IDs or WP_User IDs.
	 * @param string $type Object's type. Either "post" or "user".
	 * @return string A comma-separated list of HTML anchor tags.
	 */
	protected function get_generated_content_list( $ids, $type ) {

		$list = array();
		foreach ( $ids as $id ) {

			if ( 'post' === $type ) {
				$link = get_edit_post_link( $id );
				$text = get_the_title( $id );
			} elseif ( 'user' === $type ) {
				$link = get_edit_user_link( $id );
				$text = get_user_by( 'ID', $id )->display_name;
			}

			$list[] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $link ), $text );

		}

		return implode( ', ', $list );

	}

	/**
	 * Retrieves a "Success" message providing information about the imported content.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Generator $generator Generator instance.
	 * @return string
	 */
	protected function get_success_message( $generator ) {

		$msg  = '<strong>' . __( 'Import Successful!', 'lifterlms' ) . '</strong><br>';
		$msg .= '<ul>';

		$generated = $generator->get_generated_content();

		if ( ! empty( $generated['course'] ) ) {
			// Translators: %s = comma-separated list of anchors to the imported courses.
			$msg .= '<li>' . sprintf( __( 'Imported courses: %s', 'lifterlms' ), $this->get_generated_content_list( $generated['course'], 'post' ) ) . '</li>';
		}
		if ( ! empty( $generated['user'] ) ) {
			// Translators: %s = comma-separated list of anchors to the imported users.
			$msg .= '<li>' . sprintf( __( 'Imported users: %s', 'lifterlms' ), $this->get_generated_content_list( $generated['user'], 'user' ) ) . '</li>';
		}

		$msg .= '</ul>';

		return $msg;

	}

	/**
	 * Handle HTML output on the screen
	 *
	 * @since 3.3.0
	 * @since 3.35.0 Import template from the admin views directory instead of the frontend templates directory.
	 * @since [version] Moved logic for generating success message into its own method.
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
	 *               Updated return signature.
	 * @since 3.36.3 Fixed a typo where "$generator" was spelled "$generater".
	 * @since 3.37.3 Don't unslash uploaded file `tmp_name`.
	 *
	 * @return boolean|WP_Error false for nonce or permission errors, WP_Error when an error is encountered, true on success.
	 */
	public function upload_import() {

		if ( ! llms_verify_nonce( 'llms_importer_nonce', 'llms-importer' ) || ! current_user_can( 'manage_lifterlms' ) || empty( $_FILES['llms_import'] ) ) {
			return false;
		}

		// Fixes an issue where hooks are loaded in an unexpected order causing template functions required to parse an import aren't available.
		LLMS()->include_template_functions();

		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$validate = $this->validate_upload( $_FILES['llms_import'] );

		// File upload error.
		if ( is_wp_error( $validate ) ) {
			LLMS_Admin_Notices::flash_notice( $validate->get_error_message(), 'error' );
			return $validate;
		}

		$raw = ! empty( $_FILES['llms_import']['tmp_name'] ) ? file_get_contents( sanitize_text_field( $_FILES['llms_import']['tmp_name'] ) ) : array();
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$generator = new LLMS_Generator( $raw );
		if ( is_wp_error( $generator->set_generator() ) ) {
			LLMS_Admin_Notices::flash_notice( $generator->error->get_error_message(), 'error' );
			return $generator->error;
		}

		$generator->generate();
		if ( $generator->is_error() ) {
			LLMS_Admin_Notices::flash_notice( $generator->error->get_error_message(), 'error' );
			return $generator->error;
		}

		LLMS_Admin_Notices::flash_notice( $this->get_success_message( $generator ), 'success' );
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
		}

		if ( ! empty( $msg ) ) {
			return new WP_Error( 'llms_import_file_error', $msg );
		}

		return true;

	}

	/**
	 * Localize statistic information for display on success.
	 *
	 * @since 3.35.0
	 * @deprecated [version] `LLMS_Admin_Import::localize_stat()` is deprecated with no replacement.
	 *
	 * @param string $stat Statistic key name.
	 * @return string
	 */
	protected function localize_stat( $stat ) {

		llms_deprecated_function( 'LLMS_Admin_Import::localize_stat()', '[version]' );

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

		}

		return $name;

	}

}

return new LLMS_Admin_Import();
