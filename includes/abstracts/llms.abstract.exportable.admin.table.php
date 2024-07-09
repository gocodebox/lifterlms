<?php
/**
 * Admin Table Export Functions
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 3.28.0
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Exportable admin table abstract class
 *
 * @since 3.28.0
 * @since 3.30.3 Explicitly define undefined properties.
 * @since 3.37.15 Ensure filenames of generated export files are for supported filetypes.
 * @since 4.0.0 Removed previously deprecated method `LLMS_Admin_Table::queue_export()`.
 */
abstract class LLMS_Abstract_Exportable_Admin_Table {

	/**
	 * The current page.
	 *
	 * @var int
	 */
	protected $current_page;

	/**
	 * Unique ID for the table
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Is the Table Exportable?
	 *
	 * @var boolean
	 */
	protected $is_exportable = true;

	/**
	 * Export download nonce action.
	 *
	 * @var string
	 */
	public const EXPORT_NONCE_ACTION = 'llms_export_table';

	/**
	 * Generate an export file for the current table.
	 *
	 * @since 3.28.0
	 * @since 3.28.1 Unknown.
	 * @since 3.37.15 "Sanitize" submitted filename.
	 *
	 * @param array  $args     Arguments to pass get_results().
	 * @param string $filename Filename of the existing file, if omitted creates a new file, if passed, will continue adding to existing file.
	 * @param string $type     Export file type for forward compatibility. Currently only accepts 'csv'.
	 * @return WP_Error|array
	 */
	public function generate_export_file( $args = array(), $filename = null, $type = 'csv' ) {

		// We only support CSVs and don't allow fakers.
		if ( ! empty( $filename ) && pathinfo( $filename, PATHINFO_EXTENSION ) !== $type ) {
			return false;
		}

		// Always force page 1 regardless of what is requested. Pagination is handled below.
		$args['page'] = 1;

		/**
		 * Customize the number of records per page when generating an export file.
		 *
		 * @since 3.28.0
		 *
		 * @param int $per_page Number of records per page.
		 */
		$args['per_page'] = apply_filters( 'llms_table_generate_export_file_per_page_boost', 250 );

		$filename    = $filename ? $filename : $this->get_export_file_name() . '.' . $type;
		$file_path   = LLMS_TMP_DIR . $filename;
		$option_name = 'llms_gen_export_' . basename( $filename, '.' . $type );
		$args        = get_option( $option_name, $args );

		$handle = @fopen( $file_path, 'a+' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Yea but we handle the error alright I think.
		if ( ! $handle ) {
			return new WP_Error( 'file_error', __( 'Unable to generate export file, could not open file for writing.', 'lifterlms' ) );
		}

		/**
		 * Customize the delimiter used when generating CSV export files.
		 *
		 * @since 3.28.0
		 *
		 * @param int                                  $delim Delimiter.
		 * @param LLMS_Abstract_Exportable_Admin_Table $table Instance of the table.
		 * @param array                                $args  Array of arguments.
		 */
		$delim = apply_filters( 'llms_table_generate_export_file_delimiter', ',', $this, $args );

		foreach ( $this->get_export( $args ) as $row ) {
			fputcsv( $handle, $row, $delim );
		}

		if ( ! $this->is_last_page() ) {

			$args['page'] = $this->get_current_page() + 1;
			update_option( $option_name, $args );
			$progress = round( ( $this->get_current_page() / $this->get_max_pages() ) * 100, 2 );

		} else {

			delete_option( $option_name );
			$progress = 100;

		}

		return array(
			'filename' => $filename,
			'progress' => $progress,
			'url'      => $this->get_export_file_url( $file_path ),
		);
	}

	/**
	 * Gets data prepared for an export
	 *
	 * @since 3.15.0
	 * @since 3.15.1 Unknown.
	 *
	 * @param array $args Query arguments to be passed to get_results().
	 * @return array
	 */
	public function get_export( $args = array() ) {

		$this->get_results( $args );

		$export = array();
		if ( 1 === $this->current_page ) {
			$export[] = $this->get_export_header();
		}

		foreach ( $this->get_tbody_data() as $row ) {
			$row_data = array();
			foreach ( array_keys( $this->get_columns( 'export' ) ) as $row_key ) {
				$row_data[ $row_key ] = html_entity_decode( $this->get_export_data( $row_key, $row ) );
			}
			$export[] = $row_data;
		}

		return $export;
	}

	/**
	 * Retrieve data for a cell in an export file
	 * Should be overridden in extending classes
	 *
	 * @since 3.15.0
	 *
	 * @param string $key  The column id / key.
	 * @param mixed  $data Object / array of data that the function can use to extract the data.
	 * @return mixed
	 */
	public function get_export_data( $key, $data ) {
		return trim( strip_tags( $this->get_data( $key, $data ) ) );
	}

	/**
	 * Retrieve the download URL to an export file
	 *
	 * @since 3.28.0
	 * @since 3.28.1 Unknown.
	 * @since 7.5.0 Add nonce to export file url.
	 *
	 * @param string $file_path Full path to a download file.
	 * @return string
	 */
	protected function get_export_file_url( $file_path ) {
		return add_query_arg(
			array(
				'llms-dl-export'       => basename( $file_path ),
				'llms_dl_export_nonce' => wp_create_nonce( self::EXPORT_NONCE_ACTION ),
			),
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Retrieve the header row for generating an export file
	 *
	 * @since 3.15.0
	 * @since 3.17.3 Fixed SYLK warning generated when importing into Excel.
	 *
	 * @return array
	 */
	public function get_export_header() {

		$cols = wp_list_pluck( $this->get_columns( 'export' ), 'title' );

		/**
		 * If the first column is "ID" force it to lowercase
		 * to prevent Excel from attempting to interpret the .csv as SYLK
		 *
		 * @see https://github.com/gocodebox/lifterlms/issues/397
		 */
		foreach ( $cols as &$title ) {
			if ( 'id' === strtolower( $title ) ) {
				$title = strtolower( $title );
			}
			break;
		}

		/**
		 * Customize the export file header columns.
		 *
		 * The dynamic portion of this hook `$this->id` refers to the ID of the table.
		 *
		 * @since 3.15.0
		 *
		 * @param string[] $cols Array of file headers.
		 */
		return apply_filters( "llms_table_get_{$this->id}_export_header", $cols );
	}

	/**
	 * Retrieves the file name for an export file.
	 *
	 * @since 3.15.0
	 * @since 3.28.0 Unknown.
	 * @since 7.0.1 Fixed issue encountered when special characters are present in the table's title.
	 *
	 * @param array $args Optional arguments passed from table to csv processor.
	 * @return string
	 */
	public function get_export_file_name( $args = array() ) {

		$parts = array(
			sanitize_file_name( strtolower( $this->get_export_title( $args ) ) ),
			_x( 'export', 'Used in export filenames', 'lifterlms' ),
			llms_current_time( 'Y-m-d' ),
			wp_generate_password( 8, false, false ),
		);

		$filename = implode( '_', $parts );

		/**
		 * Filters the file name for an export file.
		 *
		 * The dynamic portion of this hook, `$this->id`, refers to the table's
		 * `$id` property.
		 *
		 * @since Unknown
		 * @since 7.0.1 Added the `$parts` and `$table` parameters.
		 *
		 * @param string                               $filename The generated filename.
		 * @param string[]                             $parts    An array of strings that makeup the generated filename
		 *                                                       when joined with the underscore separator character.
		 * @param LLMS_Abstract_Exportable_Admin_Table $table    Instance of the table object.
		 */
		return apply_filters(
			"llms_table_get_{$this->id}_export_file_name",
			$filename,
			$parts,
			$this
		);
	}

	/**
	 * Get a lock key unique to the table & user for locking the table during export generation
	 *
	 * @since 3.15.0
	 *
	 * @return string
	 */
	public function get_export_lock_key() {
		return sprintf( '%1$s:%2$d', $this->id, get_current_user_id() );
	}

	/**
	 * Allow customization of the title for export files
	 *
	 * @since 3.15.0
	 * @since 3.28.0 Unknown.
	 *
	 * @param array $args Optional arguments passed from table to csv processor.
	 * @return string
	 */
	public function get_export_title( $args = array() ) {
		return apply_filters( 'llms_table_get_' . $this->id . '_export_title', $this->get_title(), $args );
	}

	/**
	 * Retrieves the table's title.
	 *
	 * This method must be overwritten by extending classes.
	 *
	 * @since 7.0.1
	 *
	 * @return string
	 */
	public function get_title() {
		_doing_it_wrong(
			__METHOD__,
			esc_html(
				sprintf(
				// Translators: %s = the name of the method.
					__( "Method '%s' must be overridden.", 'lifterlms' ),
					__METHOD__
				)
			),
			'[version]'
		);
		return $this->id;
	}

	/**
	 * Determine if the table is currently locked due to export generation.
	 *
	 * @since 3.28.0
	 *
	 * @return bool
	 */
	public function is_export_locked() {
		return llms()->processors()->get( 'table_to_csv' )->is_table_locked( $this->get_export_lock_key() );
	}
}
