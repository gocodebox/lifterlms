<?php
/**
 * Admin Import Screen and form submission handling
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 3.3.0
 * @version 6.0.0
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
 * @since 6.0.0 Removed the deprecated `LLMS_Admin_Import::localize_stat()` method.
 */
class LLMS_Admin_Import {

	/**
	 * Constructor
	 *
	 * @since 3.3.0
	 * @since 3.35.0 Initialize at `admin_init` instead of `init`.
	 * @since 4.8.0 Added hooks handling cloud imports and outputting WP help tabs.
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'cloud_import' ) );
		add_action( 'admin_init', array( $this, 'upload_import' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		add_action( 'current_screen', array( $this, 'add_help_tabs' ) );

	}

	/**
	 * Add WP_Screen help tabs
	 *
	 * @since 4.8.0
	 *
	 * @return WP_Screen|boolean Returns the WP_Screen on success or false if called outside of the intended screen context.
	 */
	public function add_help_tabs() {

		$screen = $this->get_screen();
		if ( ! $screen ) {
			return false;
		}

		$screen->add_help_tab(
			array(
				'id'      => 'llms_import_overview',
				'title'   => __( 'Overview', 'lifterlms' ),
				'content' => $this->get_view( 'help-tab-overview' ),
			)
		);

		$screen->set_help_sidebar( $this->get_view( 'help-sidebar' ) );

		return $screen;

	}

	/**
	 * Handle form submission of a cloud import file
	 *
	 * @since 4.8.0
	 *
	 * @return WP_Error|boolean Returns `false` for nonce or user permission errors, `true` on success, or an error object.
	 */
	public function cloud_import() {

		if ( ! llms_verify_nonce( 'llms_cloud_importer_nonce', 'llms-cloud-importer' ) || ! current_user_can( 'manage_lifterlms' ) ) {
			return false;
		}

		$course_id = llms_filter_input( INPUT_POST, 'llms_cloud_import_course_id', FILTER_SANITIZE_NUMBER_INT );
		if ( ! $course_id ) {
			return $this->show_error( new WP_Error( 'llms-cloud-import-missing-id', __( 'Error: Missing course ID.', 'lifterlms' ) ) );
		}

		$res = LLMS_Export_API::get( array( $course_id ) );
		if ( is_wp_error( $res ) ) {
			return $this->show_error( $res );
		}

		return $this->handle_generation( $res );

	}

	/**
	 * Enqueue static assets used on the screen
	 *
	 * @since 4.8.0
	 *
	 * @return null|boolean Returns `null` when called outside of the intended screen context, `true` on success, or `false` on error.
	 */
	public function enqueue() {

		if ( ! $this->get_screen() ) {
			return null;
		}

		return llms()->assets->enqueue_style( 'llms-admin-importer' );

	}

	/**
	 * Convert an array of generated content IDs to a list of anchor tags to edit the generated content
	 *
	 * @since 4.7.0
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
	 * Retrieve an instance of the WP_Screen for the import screen
	 *
	 * @since 4.8.0
	 *
	 * @return WP_Screen|boolean Returns a `WP_Screen` object when on the import screen, otherwise returns `false`.
	 */
	protected function get_screen() {

		$screen = get_current_screen();
		if ( $screen instanceof WP_Screen && 'lifterlms_page_llms-import' === $screen->id ) {
			return $screen;
		}

		return false;

	}

	/**
	 * Retrieves the HTML of a view from the views/import directory.
	 *
	 * @since 4.8.0
	 *
	 * @param string $file The file basename of the view to retrieve.
	 * @return string The HTML content of the view.
	 */
	protected function get_view( $file ) {

		ob_start();
		include 'views/import/' . $file . '.php';
		return ob_get_clean();

	}

	/**
	 * Retrieves a "Success" message providing information about the imported content.
	 *
	 * @since 4.7.0
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
	 * Instantiate and generate raw data via LLMS_Generator
	 *
	 * @since 4.8.0
	 *
	 * @param string|array $raw A JSON string or array or raw data which can be parsed by an LLMS_Generator instance.
	 * @return WP_Error|boolean On success, returns true or an error object on failure.
	 */
	protected function handle_generation( $raw ) {

		$generator = new LLMS_Generator( $raw );
		if ( is_wp_error( $generator->set_generator() ) ) {
			return $this->show_error( $generator->error );
		}

		$generator->generate();
		if ( $generator->is_error() ) {
			return $this->show_error( $generator->error );
		}

		LLMS_Admin_Notices::flash_notice( $this->get_success_message( $generator ), 'success' );
		return true;

	}

	/**
	 * Handle HTML output on the screen
	 *
	 * @since 3.3.0
	 * @since 3.35.0 Import template from the admin views directory instead of the frontend templates directory.
	 * @since 4.7.0 Moved logic for generating success message into its own method.
	 *
	 * @return void
	 */
	public static function output() {
		include 'views/import.php';
	}

	/**
	 * Output an admin notice from a WP_Error object
	 *
	 * @since 4.8.0
	 *
	 * @param WP_Error $error WP_Error object.
	 * @return WP_Error Returns the same error passed into the object.
	 */
	protected function show_error( $error ) {
		LLMS_Admin_Notices::flash_notice( $error->get_error_message(), 'error' );
		return $error;
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
	 * @since 4.8.0 Use helper methods `show_error()` and `handle_generation()`.
	 *
	 * @return boolean|WP_Error false for nonce or permission errors, WP_Error when an error is encountered, true on success.
	 */
	public function upload_import() {

		if ( ! llms_verify_nonce( 'llms_importer_nonce', 'llms-importer' ) || ! current_user_can( 'manage_lifterlms' ) || empty( $_FILES['llms_import'] ) ) {
			return false;
		}

		// Fixes an issue where hooks are loaded in an unexpected order causing template functions required to parse an import aren't available.
		llms()->include_template_functions();

		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$validate = $this->validate_upload( $_FILES['llms_import'] );

		// File upload error.
		if ( is_wp_error( $validate ) ) {
			return $this->show_error( $validate );
		}

		$raw = ! empty( $_FILES['llms_import']['tmp_name'] ) ? file_get_contents( sanitize_text_field( $_FILES['llms_import']['tmp_name'] ) ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitizedr

		return $this->handle_generation( $raw );

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

}

return new LLMS_Admin_Import();
