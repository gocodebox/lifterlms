<?php
defined( 'ABSPATH' ) || exit;

/**
 * Admin Tables
 *
 * @since   3.2.0
 * @version 3.24.0
 */
abstract class LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 * @var  string
	 */
	protected $id = '';

	/**
	 * When pagination is enabled, the current page
	 * @var  integer
	 */
	protected $current_page = 1;

	/**
	 * Value of the field being filtered by
	 * Only applicable if $filterby is set
	 * @var  string
	 */
	protected $filter = '';

	/**
	 * Field results are filtered by
	 * @var  string
	 */
	protected $filterby = '';

	/**
	 * Is the Table Exportable?
	 * @var  boolean
	 */
	protected $is_exportable = false;

	/**
	 * When pagination enabled, determines if this is the last page of results
	 * @var  boolean
	 */
	protected $is_last_page = true;

	/**
	 * If true, tfoot will add ajax pagination links
	 * @var  boolean
	 */
	protected $is_paginated = false;

	/**
	 * Determine if the table is filterable
	 * @var  boolean
	 */
	protected $is_filterable = false;

	/**
	 * If true will be a table with a larger font size
	 * @var  bool
	 */
	protected $is_large = false;

	/**
	 * Determine of the table is searchable
	 * @var  boolean
	 */
	protected $is_searchable = false;

	/**
	 * If true, tbody will be zebra striped
	 * @var  boolean
	 */
	protected $is_zebra = true;

	/**
	 * If an integer supplied, used to jump to last page
	 * @var  int
	 */
	protected $max_pages = null;

	/**
	 * Results sort order
	 * 'ASC' or 'DESC'
	 * Only applicable of $orderby is not set
	 * @var  string
	 */
	protected $order = '';

	/**
	 * Field results are sorted by
	 * @var  string
	 */
	protected $orderby = '';

	/**
	 * The search query submitted for a searchable table
	 * @var  string
	 */
	protected $search = '';

	/**
	 * Table Data
	 * Array of objects or arrays
	 * each item represents as row in the table's body, each item is a cell
	 * @var  array
	 */
	protected $tbody_data = array();

	/**
	 * Table Title Displayed on Screen
	 * @var  string
	 */
	protected $title = '';

	/**
	 * Retrieve data for a cell
	 * @param    string     $key   the column id / key
	 * @param    mixed      $data  object / array of data that the function can use to extract the data
	 * @return   mixed
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	abstract protected function get_data( $key, $data );

	/**
	 * Execute a query to retrieve results from the table
	 * @param    array      $args  array of query args
	 * @return   void
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	abstract public function get_results( $args = array() );

	/**
	 * Define the structure of arguments used to pass to the get_results method
	 * @return   array
	 * @since    2.3.0
	 * @version  2.3.0
	 */
	abstract public function set_args();

	/**
	 * Define the structure of the table
	 * @return   array
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	abstract protected function set_columns();

	/**
	 * Constructor
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function __construct() {
		$this->register_hooks();
	}

	/**
	 * Ensure that a valid array of data is passed to a query
	 * Used by AJAX methods to clean unnecssarry parameters before passing the request data
	 * to the get_results function
	 * @param    array      $args  array of arguments
	 * @return   array
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	protected function clean_args( $args = array() ) {

		$allowed = array_keys( $this->get_args() );

		foreach ( $args as $key => $val ) {
			if ( ! in_array( $key, $allowed ) ) {
				unset( $args[ $key ] );
			}
		}

		return $args;

	}

	/**
	 * Ensures that all data requested by $this->get_data if filterable
	 * before being output on screen / in the export file
	 * @param    mixed     $value     value to be displayed
	 * @param    string    $key       column key / id
	 * @param    mixed     $data      original data object / array
	 * @param    string    $context   display context [display|export]
	 * @return   mixed
	 * @since    3.2.0
	 * @version  3.17.6
	 */
	protected function filter_get_data( $value, $key, $data, $context = 'display' ) {
		return apply_filters( 'llms_table_get_data_' . $this->id, $value, $key, $data, $context, $this );
	}

	/**
	 * Retrieve the arguments defined in `set_args`
	 * @return   array
	 * @since    3.2.0
	 * @version  3.15.0
	 */
	public function get_args() {

		$default = array(
			'page'    => $this->get_current_page(),
			'order'   => $this->get_order(),
			'orderby' => $this->get_orderby(),
		);

		if ( $this->is_filterable ) {
			$default['filter'] = $this->get_filter();
			$default['filterby'] = $this->get_filterby();
		}

		if ( $this->is_searchable ) {
			$default['search'] = $this->get_search();
		}

		$args = wp_parse_args( $this->set_args(), $default );

		return apply_filters( 'llms_table_get_args_' . $this->id, $args );
	}

	/**
	 * Retrieve the array of columns defined by set_columns
	 * @return   array
	 * @since    3.2.0
	 * @version  3.24.0
	 */
	public function get_columns( $context = 'display' ) {

		$cols = apply_filters( 'llms_table_get_' . $this->id . '_columns', $this->set_columns(), $context );

		if ( $this->is_exportable ) {

			foreach ( $cols as $id => $data ) {

				if ( ! $this->is_col_visible( $data, $context ) ) {
					unset( $cols[ $id ] );
				}
			}
		}

		return $cols;

	}

	/**
	 * Get the current page
	 * @return   int
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_current_page() {
		return $this->current_page;
	}

	/**
	 * Get $this->empty_msg string
	 * @return   string
	 * @since    3.2.0
	 * @version  3.15.0
	 */
	public function get_empty_message() {
		return apply_filters( 'llms_table_get_' . $this->id . '_empty_message', $this->set_empty_message() );
	}

	/**
	 * Gets data prepared for an export
	 * @param    array     $args  query arguements to be passed to get_results()
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
	 * Should be overriden in extending classes
	 * @param    string     $key   the column id / key
	 * @param    mixed      $data  object / array of data that the function can use to extract the data
	 * @return   mixed
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_export_data( $key, $data ) {
		return trim( strip_tags( $this->get_data( $key, $data ) ) );
	}

	/**
	 * Retrieve the header row for generating an export file
	 * @return   array
	 * @since    3.15.0
	 * @version  3.17.3
	 */
	public function get_export_header() {

		$cols = wp_list_pluck( $this->get_columns( 'export' ), 'title' );

		/**
		 * If the first column is "ID" force it to lowercase
		 * to prevent Excel from attempting to interpret the .csv as SYLK
		 * @see  https://github.com/gocodebox/lifterlms/issues/397
		 */
		foreach ( $cols as $key => &$title ) {
			if ( 'id' === strtolower( $title ) ) {
				$title = strtolower( $title );
			}
			break;
		}

		return apply_filters( 'llms_table_get_' . $this->id . '_export_header', $cols );
	}

	/**
	 * Get the file name for an export file
	 * @param    array    $args   optional arguements passed from table to csv processor
	 * @return   string
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_export_file_name( $args = array() ) {

		$title = sprintf( '%1$s_export_%2$s', sanitize_title( $this->get_export_title( $args ), 'llms-' . $this->id ), current_time( 'Y-m-d' ) );
		return apply_filters( 'llms_table_get_' . $this->id . '_export_file_name', $title );

	}

	/**
	 * Get a lock key unique to the table & user for locking the table during export generation
	 * @return   string
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_export_lock_key() {
		return sprintf( '%1$s:%2$d', $this->id, get_current_user_id() );
	}

	/**
	 * Allow customization of the title for export files
	 * @param    array    $args   optional arguements passed from table to csv processor
	 * @return   string
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_export_title( $args = array() ) {
		return apply_filters( 'llms_table_get_' . $this->id . '_export_title', $this->get_title() );
	}

	/**
	 * Get the text for the default/placeholder for a filterable column
	 * @param    string     $column_id  id of the column
	 * @return   string
	 * @since    3.4.0
	 * @version  3.15.0
	 */
	public function get_filter_placeholder( $column_id, $column_data ) {
		$placeholder = __( 'Any', 'lifterlms' );
		if ( is_array( $column_data ) && isset( $column_data['title'] ) ) {
			$placeholder = sprintf( __( 'Any %s', 'lifterlms' ), $column_data['title'] );
		} elseif ( is_strinp( $column_data ) ) {
			$placeholder = sprintf( __( 'Any %s', 'lifterlms' ), $column_data );
		}
		return apply_filters( 'llms_table_get_' . $this->id . '_filter_placeholder', $placeholder, $column_id );
	}

	/**
	 * Get the current filter
	 * @return   string
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	public function get_filter() {
		return $this->filter;
	}

	/**
	 * Get the current field results are filtered by
	 * @return   string
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	public function get_filterby() {
		return $this->filterby;
	}

	/**
	 * Retrieve a modified classname that can be passed via AJAX for new queries
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_handler() {
		return str_replace( 'LLMS_Table_', '', get_class( $this ) );
	}

	/**
	 * Retrieve the max number of pages for the table
	 * @return   int
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_max_pages() {
		return $this->max_pages;
	}

	/**
	 * Get the current sort order
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_order() {
		return $this->order;
	}

	/**
	 * Get the current field results are ordered by
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_orderby() {
		return $this->orderby;
	}

	/**
	 * Gets the opposite of the current order
	 * Used to determine what order should be displayed when resorting
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	protected function get_new_order( $orderby = '' ) {

		// current order matches submitted order, return oppossite
		if ( $this->orderby === $orderby ) {
			return ( 'ASC' === $this->order ) ? 'DESC' : 'ASC';
		} // End if().
		else {
			return 'ASC';
		}

	}

	/**
	 * Retrieves the current search query
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_search() {
		return esc_attr( trim( $this->search ) );
	}

	/**
	 * Get HTML for the filters displayed in the head of the table
	 * @return   string
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	public function get_table_filters_html() {
		ob_start();
		?>
		<div class="llms-table-filters">
			<?php foreach ( $this->get_columns() as $id => $data ) : ?>
				<?php if ( is_array( $data ) && isset( $data['filterable'] ) && is_array( $data['filterable'] ) ) : ?>
					<div class="llms-table-filter-wrap">
						<select class="llms-select2 llms-table-filter" id="<?php printf( '%1$s-%2$s-filter', $this->id, $id ); ?>" name="<?php echo $id; ?>">
							<option value="<?php echo $this->get_filter(); ?>"><?php echo $this->get_filter_placeholder( $id, $data ); ?></option>
							<?php foreach ( $data['filterable'] as $val => $name ) : ?>
								<option value="<?php echo $val; ?>"><?php echo $name; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get the HTML for the entire table
	 * @return   string
	 * @since    3.2.0
	 * @version  3.17.8
	 */
	public function get_table_html() {

		$classes = array(
			'llms-table',
			'llms-gb-table',
			'llms-gb-table-' . $this->id,
		);

		if ( $this->is_zebra ) {
			$classes[] = 'zebra';
		}

		if ( $this->is_large ) {
			$classes[] = 'size-large';
		}

		ob_start();
		?>
		<div class="llms-table-wrap">
			<header class="llms-table-header">
				<?php echo $this->get_table_title_html(); ?>
				<?php if ( $this->is_searchable ) : ?>
					<?php echo $this->get_table_search_form_html(); ?>
				<?php endif; ?>
				<?php if ( $this->is_filterable ) : ?>
					<?php echo $this->get_table_filters_html(); ?>
				<?php endif; ?>
			</header>
			<table
				class="<?php echo implode( $classes, ' ' ); ?>"
				data-args='<?php echo json_encode( $this->get_args() ); ?>'
				data-handler="<?php echo $this->get_handler(); ?>"
				id="llms-gb-table-<?php echo $this->id; ?>"
			>
				<?php echo $this->get_thead_html(); ?>
				<?php echo $this->get_tbody_html(); ?>
				<?php echo $this->get_tfoot_html(); ?>
			</table>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get the HTML of the search form for a searchable table
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_table_search_form_html() {
		ob_start();
		?>
		<div class="llms-table-search">
			<input class="regular-text" id="<?php echo $this->id; ?>-search-input" placeholder="<?php echo $this->get_table_search_form_placeholder(); ?>" type="text">
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get the Text to be used as the placeholder in a searchable tables search input
	 * @return   string
	 * @since    3.2.0
	 * @version  3.15.0
	 */
	public function get_table_search_form_placeholder() {
		return apply_filters( 'llms_table_get_' . $this->id . '_search_placeholder', __( 'Search', 'lifterlms' ) );
	}

	/**
	 * Get the HTML for the table's title
	 * @return   string
	 * @since    3.2.0
	 * @version  3.15.0
	 */
	public function get_table_title_html() {
		$title = $this->get_title();
		if ( $title ) {
			return '<h2 class="llms-table-title">' . $title . '</h2>';
		} else {
			return '';
		}
	}

	/**
	 * Get $this->tbody_data array
	 * @return   array
	 * @since    3.2.0
	 * @version  3.15.0
	 */
	public function get_tbody_data() {
		return apply_filters( 'llms_table_get_' . $this->id . '_tbody_data', $this->tbody_data );
	}

	/**
	 * Get a tbody element for the table
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_tbody_html() {
		$data = $this->get_tbody_data();
		ob_start();
		?>
		<tbody>
			<?php if ( $data ) : ?>
				<?php foreach ( $data as $row ) : ?>
					<?php echo $this->get_tr_html( $row ); ?>
				<?php endforeach; ?>
			<?php else : ?>
				<tr><td class="llms-gb-table-empty" colspan="<?php echo $this->get_columns_count(); ?>"><p><?php echo $this->get_empty_message(); ?></p></td></tr>
			<?php endif; ?>
		</tbody>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get a tfoot element for the table
	 * @return   string
	 * @since    3.2.0
	 * @version  3.15.0
	 */
	public function get_tfoot_html() {
		ob_start();
		?>
		<tfoot>
			<tr>
				<th colspan="<?php echo $this->get_columns_count(); ?>">
					<?php if ( $this->is_exportable ) : ?>
						<?php $locked = LLMS()->processors()->get( 'table_to_csv' )->is_table_locked( $this->get_export_lock_key() ); ?>
						<div class="llms-table-export">
							<button class="llms-button-primary small" name="llms-table-export"<?php echo $locked ? ' disabled="disabled"' : ''; ?>>
								<span class="dashicons dashicons-download"></span> <?php _e( 'Export', 'lifterlms' ); ?>
							</button>
							<?php if ( $locked ) : ?>
								<em><small>The export is being generated.</small></em>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if ( $this->is_paginated ) : ?>
						<div class="llms-table-pagination">
						<?php if ( $this->max_pages ) : ?>
							<span class="llms-table-page-count"><?php printf( _x( '%d of %d', 'pagination', 'lifterlms' ), $this->current_page, $this->max_pages ); ?></span>
						<?php endif; ?>
						<?php if ( 1 !== $this->get_current_page() ) : ?>
							<?php if ( $this->max_pages ) : ?>
								<button class="llms-button-primary small" data-page="1" name="llms-table-paging"><span class="dashicons dashicons-arrow-left-alt"></span> <?php _e( 'First', 'lifterlms' ); ?></button>
							<?php endif; ?>
							<button class="llms-button-primary small" data-page="<?php echo $this->current_page - 1; ?>" name="llms-table-paging"><span class="dashicons dashicons-arrow-left-alt2"></span> <?php _e( 'Back', 'lifterlms' ); ?></button>
						<?php endif; ?>
						<?php if ( ! $this->is_last_page ) : ?>
							<button class="llms-button-primary small" data-page="<?php echo $this->current_page + 1; ?>" name="llms-table-paging"><?php _e( 'Next', 'lifterlms' ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span></button>
							<?php if ( $this->max_pages ) : ?>
								<button class="llms-button-primary small" data-page="<?php echo $this->max_pages; ?>" name="llms-table-paging"><?php _e( 'Last', 'lifterlms' ); ?> <span class="dashicons dashicons-arrow-right-alt"></span></button>
							<?php endif; ?>
						<?php endif; ?>
						</div>
					<?php endif; ?>
				</th>
			</tr>
		</tfoot>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get a thead element for the table
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_thead_html() {
		ob_start();
		?>
		<thead>
			<tr>
			<?php foreach ( $this->get_columns() as $id => $data ) : ?>
				<th class="<?php echo $id; ?>">
					<?php if ( is_array( $data ) ) : ?>
						<?php if ( isset( $data['sortable'] ) && $data['sortable'] ) : ?>
							<a class="llms-sortable<?php echo ( $this->get_orderby() === $id ) ? ' active' : ''; ?>" data-order="<?php echo $this->get_new_order( $id ); ?>" data-orderby="<?php echo $id; ?>" href="#llms-gb-table-resort">
								<?php echo $data['title']; ?>
								<span class="dashicons dashicons-arrow-up asc"></span>
								<span class="dashicons dashicons-arrow-down desc"></span>
							</a>
						<?php else : ?>
							<?php echo $data['title']; ?>
						<?php endif; ?>
					<?php else : ?>
						<?php echo $data; ?>
					<?php endif; ?>
				</th>
			<?php endforeach; ?>
			</tr>
		</thead>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get a CSS class list (as a string) for each TR
	 * @param    mixed      $data  object / array of data that the function can use to extract the data
	 * @return   string
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	protected function get_tr_classes( $row ) {
		return apply_filters( 'llms_table_get_' . $this->id . '_tr_classes', 'llms-table-tr', $row );
	}

	/**
	 * Get the HTML for a single row in the body of the table
	 * @param    mixed     $row  array/object of data describing a single row in the table
	 * @return   string
	 * @since    3.2.0
	 * @version  3.21.0
	 */
	public function get_tr_html( $row ) {
		ob_start();
		do_action( 'llms_table_before_tr', $row, $this );
		?>
		<tr class="<?php echo esc_attr( $this->get_tr_classes( $row ) ); ?>">
		<?php foreach ( $this->get_columns() as $id => $title ) : ?>
			<td class="<?php echo $id; ?>"><?php echo $this->get_data( $id, $row ); ?></td>
		<?php endforeach; ?>
		</tr>
		<?php
		do_action( 'llms_table_after_tr', $row, $this );
		return ob_get_clean();
	}

	/**
	 * Get the total number of columns in the table
	 * Useful for creating full with tds via colspan
	 * @return   int
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_columns_count() {
		return count( $this->get_columns() );
	}

	/**
	 * Get the HTML to output a progress bar within a td
	 * Improve ugly tables with a small visual flourish
	 * Useful when displaying a percentage within a table!
	 * Bonus if the table sorts by that percentage column
	 * @param    float      $percentage  the percentage to be displayed
	 * @param    string     $text        text to display over the progress bar, defaults to $percentage
	 * @return   string
	 * @since    3.4.1
	 * @version  3.4.1
	 */
	public function get_progress_bar_html( $percentage, $text = '' ) {
		$text = $text ? $text : $percentage . '%';
		return '<div class="llms-table-progress">
			<span class="llms-table-progress-text">' . $text . '</span>
			<div class="llms-table-progress-inner" style="width:' . $percentage . '%"></div>
		</div>';
	}

	/**
	 * Get the HTML for a WP Post Link
	 * @param    int        $post_id  WP Post ID
	 * @param    string     $text     Optional text to display within the anchor, if none supplied $post_id if used
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_post_link( $post_id, $text = '' ) {
		if ( ! $text ) {
			$text = $post_id;
		}
		return '<a href="' . esc_url( get_edit_post_link( $post_id ) ) . '">' . $text . '</a>';
	}

	/**
	 * Get the title of the table
	 * @return   string
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_title() {
		return apply_filters( 'llms_table_get_' . $this->id . '_table_title', $this->title );
	}

	/**
	 * Get the HTML for a WP User Link
	 * @param    int        $post_id  WP User ID
	 * @param    string     $text     Optional text to display within the anchor, if none supplied $user_id if used
	 * @return   string
	 * @since    3.17.2
	 * @version  3.17.2
	 */
	public function get_user_link( $user_id, $text = '' ) {
		if ( ! $text ) {
			$text = $user_id;
		}
		return '<a href="' . esc_url( get_edit_user_link( $user_id ) ) . '">' . $text . '</a>';
	}

	/**
	 * Determine if a column is visible based on the current context
	 * @param    [type]     $data     array of a single column's data from set_columns()
	 * @param    string     $context  context [display|export]
	 * @return   bool
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	private function is_col_visible( $data, $context = 'display' ) {

		// display if 'export_only' does not exist or it does exist and is false
		if ( 'display' === $context ) {
			return ( ! isset( $data['export_only'] ) || ! $data['export_only'] );

			// display if exportable is set and is true
		} elseif ( 'export' === $context ) {
			return ( isset( $data['exportable'] ) && $data['exportable'] );
		}

		return true;

	}

	/**
	 * Return protected is_last_page var
	 * @return   bool
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function is_last_page() {
		return $this->is_last_page;
	}

	/**
	 * Queues an export for the table to be generated
	 * @return   void
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function queue_export( $args = array() ) {

		$args = $this->clean_args( $args );

		foreach ( $args as $key => $val ) {
			$this->$key = $val;
		}

		do_action( 'llms_table_generate_csv', $this );

	}

	/**
	 * Allow custom hooks to be registered for use within the class
	 * @return   void
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	protected function register_hooks() {}

	/**
	 * Setter
	 * @param    string     $key  variable name
	 * @param    mixed      $val  variable data
	 * @since    2.3.0
	 * @version  2.3.0
	 */
	public function set( $key, $val ) {
		$this->$key = $val;
	}

	/**
	 * Empty message displayed when no results are found
	 * @return   string
	 * @since    3.2.0
	 * @version  3.15.0
	 */
	protected function set_empty_message() {
		return apply_filters( 'llms_table_default_empty_message', __( 'No results were found.', 'lifterlms' ) );
	}

}
