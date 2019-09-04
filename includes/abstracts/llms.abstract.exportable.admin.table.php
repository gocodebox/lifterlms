<?php
/**
 * Admin Table Export Functions
 *
 * @since 3.28.0
 * @version 3.30.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Table abstract.
 *
 * @since 3.28.0
 * @since 3.30.3 Explicitly define undefined properties.
 */
abstract class LLMS_Abstract_Exportable_Admin_Table {

	/**
	 * The current page.
	 *
	 * @var int
	 * @since 3.28.0
	 */
	protected $current_page;

	/**
	 * Unique ID for the table
	 *
	 * @var  string
	 * @since 3.28.0
	 */
	protected $id;

	/**
	 * Is the Table Exportable?
	 *
	 * @var  boolean
	 */
	protected $is_exportable = true;

	/**
	 * Generate an export file for the current table.
	 *
	 * @param   array  $args      arguments to pass get_results().
	 * @param   string $filename  filename of the existing file, if omitted creates a new file, if passed, will continue adding to existing file.
	 * @param   string $type      export file type for forward compatibility. Currently only accepts 'csv'.
	 * @return  WP_Error|array
	 * @since   3.28.0
	 * @version 3.28.1
	 */
	public function generate_export_file( $args = array(), $filename = null, $type = 'csv' ) {

		if ( 'csv' !== $type ) {
			return false;
		}

		// always force page 1 regardless of what is requested. Pagination is handled below.
		$args['page'] = 1;
		// Boost records / page to speed up generation.
		$args['per_page'] = apply_filters( 'llms_table_generate_export_file_per_page_boost', 250 );

		$filename    = $filename ? $filename : $this->get_export_file_name() . '.' . $type;
		$file_path   = LLMS_TMP_DIR . $filename;
		$option_name = 'llms_gen_export_' . basename( $filename, '.' . $type );
		$args        = get_option( $option_name, $args );

		$handle = @fopen( $file_path, 'a+' );
		if ( ! $handle ) {
			return new WP_Error( 'file_error', __( 'Unable to generate export file, could not open file for writing.', 'lifterlms' ) );
		}

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
	 * @param    array $args  query arguments to be passed to get_results()
	 * @return   array
	 * @since    3.15.0
	 * @version  3.15.1
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
	 * @param    string $key   the column id / key
	 * @param    mixed  $data  object / array of data that the function can use to extract the data
	 * @return   mixed
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_export_data( $key, $data ) {
		return trim( strip_tags( $this->get_data( $key, $data ) ) );
	}

	/**
	 * Retrieve the download URL to an export file
	 *
	 * @param   string $file_path full path to a download file.
	 * @return  string
	 * @since   3.28.0
	 * @version 3.28.1
	 */
	protected function get_export_file_url( $file_path ) {
		return add_query_arg(
			array(
				'llms-dl-export' => basename( $file_path ),
			),
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Retrieve the header row for generating an export file
	 *
	 * @return   array
	 * @since    3.15.0
	 * @version  3.17.3
	 */
	public function get_export_header() {

		$cols = wp_list_pluck( $this->get_columns( 'export' ), 'title' );

		/**
		 * If the first column is "ID" force it to lowercase
		 * to prevent Excel from attempting to interpret the .csv as SYLK
		 *
		 * @see  https://github.com/gocodebox/lifterlms/issues/397
		 */
		foreach ( $cols as &$title ) {
			if ( 'id' === strtolower( $title ) ) {
				$title = strtolower( $title );
			}
			break;
		}

		return apply_filters( 'llms_table_get_' . $this->id . '_export_header', $cols );
	}

	/**
	 * Get the file name for an export file
	 *
	 * @param    array $args   optional arguments passed from table to csv processor
	 * @return   string
	 * @since    3.15.0
	 * @version  3.28.0
	 */
	public function get_export_file_name( $args = array() ) {

		$title = sprintf( '%1$s_export_%2$s_%3$s', sanitize_title( $this->get_export_title( $args ), 'llms-' . $this->id ), current_time( 'Y-m-d' ), wp_generate_password( 8, false, false ) );
		return apply_filters( 'llms_table_get_' . $this->id . '_export_file_name', $title );

	}

	/**
	 * Get a lock key unique to the table & user for locking the table during export generation
	 *
	 * @return   string
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_export_lock_key() {
		return sprintf( '%1$s:%2$d', $this->id, get_current_user_id() );
	}

	/**
	 * Allow customization of the title for export files
	 *
	 * @param    array $args   optional arguments passed from table to csv processor
	 * @return   string
	 * @since    3.15.0
	 * @version  3.28.0
	 */
	public function get_export_title( $args = array() ) {
		return apply_filters( 'llms_table_get_' . $this->id . '_export_title', $this->get_title(), $args );
	}

	/**
	 * Determine if the table is currently locked due to export generation.
	 *
	 * @return  bool
	 * @since   3.28.0
	 * @version 3.28.0
	 */
	public function is_export_locked() {
		return LLMS()->processors()->get( 'table_to_csv' )->is_table_locked( $this->get_export_lock_key() );
	}

	/**
	 * Queues an export for the table to be generated
	 *
	 * @return   void
	 * @since    3.15.0
	 * @version  3.28.0
	 * @deprecated 3.28.0
	 */
	public function queue_export( $args = array() ) {

		llms_deprecated_function( 'LLMS_Admin_Table::queue_export()', '3.28.0' );

		$args = $this->clean_args( $args );

		foreach ( $args as $key => $val ) {
			$this->$key = $val;
		}

		do_action( 'llms_table_generate_csv', $this );

	}

}
