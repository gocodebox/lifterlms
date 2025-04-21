<?php
/**
 * Admin Table Abstract
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 3.2.0
 * @version 7.8.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Table abstract class.
 *
 * @since 3.2.0
 * @since 3.34.0 Added get_table_classes().
 * @since 3.37.7 Fix PHP 7.4 deprecation notice.
 */
abstract class LLMS_Admin_Table extends LLMS_Abstract_Exportable_Admin_Table {

	/**
	 * Unique ID for the Table.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * When pagination is enabled, the current page.
	 *
	 * @var integer
	 */
	protected $current_page = 1;

	/**
	 * Value of the field being filtered by.
	 *
	 * @var string Only applicable if $filterby is set.
	 */
	protected $filter = '';

	/**
	 * Field results are filtered by.
	 *
	 * @var string
	 */
	protected $filterby = '';

	/**
	 * Is the Table Exportable?
	 *
	 * @var bool
	 */
	protected $is_exportable = false;

	/**
	 * When pagination enabled, determines if this is the last page of results.
	 *
	 * @var bool
	 */
	protected $is_last_page = true;

	/**
	 * If true, tfoot will add ajax pagination links.
	 *
	 * @var bool
	 */
	protected $is_paginated = false;

	/**
	 * Determine if the table is filterable.
	 *
	 * @var bool
	 */
	protected $is_filterable = false;

	/**
	 * If true will be a table with a larger font size.
	 *
	 * @var bool
	 */
	protected $is_large = false;

	/**
	 * Determine of the table is searchable.
	 *
	 * @var bool
	 */
	protected $is_searchable = false;

	/**
	 * If true, tbody will be zebra striped.
	 *
	 * @var bool
	 */
	protected $is_zebra = true;

	/**
	 * If an integer supplied, used to jump to last page.
	 *
	 * @var int
	 */
	protected $max_pages = null;

	/**
	 * Results sort order.
	 *
	 * @var string 'ASC' or 'DESC'.
	 *             Only applicable of $orderby is not set.
	 */
	protected $order = '';

	/**
	 * Field results are sorted by.
	 *
	 * @var string
	 */
	protected $orderby = '';

	/**
	 * Number of records to display per page.
	 *
	 * @var int
	 */
	protected $per_page = -1;

	/**
	 * The search query submitted for a searchable table.
	 *
	 * @var string
	 */
	protected $search = '';

	/**
	 * Table Data.
	 *
	 * @var array Array of objects or arrays.
	 *            Each item represents as row in the table's body, each item is a cell.
	 */
	protected $tbody_data = array();

	/**
	 * Table Title Displayed on Screen.
	 *
	 * @var string
	 */
	protected $title = '';

	/**
	 * Retrieve data for a cell.
	 *
	 * @since 3.2.0
	 *
	 * @param string $key  The column ID/key.
	 * @param mixed  $data Object/array of data that the function can use to extract the data.
	 * @return mixed
	 */
	abstract protected function get_data( $key, $data );

	/**
	 * Execute a query to retrieve results from the table.
	 *
	 * @since 3.2.0
	 *
	 * @param array $args Array of query args.
	 * @return mixed
	 */
	abstract public function get_results( $args = array() );

	/**
	 * Define the structure of arguments used to pass to the get_results method.
	 *
	 * @since 2.3.0
	 *
	 * @return array
	 */
	abstract public function set_args();

	/**
	 * Define the structure of the table.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	abstract protected function set_columns();

	/**
	 * Constructor.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->title = $this->set_title();
		$this->register_hooks();
	}

	/**
	 * Ensure that a valid array of data is passed to a query.
	 *
	 * Used by AJAX methods to clean unnecessary parameters before passing the request data
	 * to the get_results function.
	 *
	 * @since 3.2.0
	 *
	 * @param array $args Array of arguments
	 * @return array
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
	 * Ensures that all data requested by $this->get_data is filterable
	 * before being output on screen / in the export file.
	 *
	 * @since 3.2.0
	 * @since 3.17.6 Unknown.
	 *
	 * @param mixed  $value   Value to be displayed.
	 * @param string $key     Column key/ID.
	 * @param mixed  $data    Original data object/array.
	 * @param string $context Display context [display|export].
	 * @return mixed
	 */
	protected function filter_get_data( $value, $key, $data, $context = 'display' ) {
		/**
		 * Filters the table data.
		 *
		 * The dynamic portion of this filter `{$this->id}` refers to the unique ID for the table.
		 *
		 * @since 3.2.0
		 *
		 * @param mixed            $value        Value to be displayed.
		 * @param string           $key          Column key/ID.
		 * @param mixed            $data         Original data object/array.
		 * @param string           $context      Display context [display|export].
		 * @param LLMS_Admin_Table $table_object Instance of the class extending `LLMS_Admin_Table`.
		 */
		return apply_filters( "llms_table_get_data_{$this->id}", $value, $key, $data, $context, $this );
	}

	/**
	 * Retrieve the arguments defined in `set_args`.
	 *
	 * @since 3.2.0
	 * @since 3.15.0 Fix filter name.
	 *
	 * @return array
	 */
	public function get_args() {

		$default = array(
			'page'    => $this->get_current_page(),
			'order'   => $this->get_order(),
			'orderby' => $this->get_orderby(),
		);

		if ( $this->is_filterable ) {
			$default['filter']   = $this->get_filter();
			$default['filterby'] = $this->get_filterby();
		}

		if ( $this->is_searchable ) {
			$default['search'] = $this->get_search();
		}

		$args = wp_parse_args( $this->set_args(), $default );

		/**
		 * Filters the arguments used to build the query.
		 *
		 * The dynamic portion of this filter `{$this->id}` refers to the unique ID for the table.
		 *
		 * @since 3.15.0
		 *
		 * @param array $args Arguments to build the query whose results will populate the table.
		 */
		return apply_filters( "llms_table_get_args_{$this->id}", $args );
	}

	/**
	 * Retrieve the array of columns defined by set_columns.

	 * @since 3.2.0
	 * @since 3.24.0 Unknown.
	 *
	 * @param string $context Display context [display|export].
	 * @return array
	 */
	public function get_columns( $context = 'display' ) {

		/**
		 * Filters the array of table columns.
		 *
		 * The dynamic portion of this filter `{$this->id}` refers to the unique ID for the table.
		 *
		 * @since 3.2.0
		 *
		 * @param array  $columns The array of table columns.
		 * @param string $context Display context [display|export].
		 */
		$cols = apply_filters( "llms_table_get_{$this->id}_columns", $this->set_columns(), $context );

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
	 * Get the current page.
	 *
	 * @since 3.2.0
	 *
	 * @return int
	 */
	public function get_current_page() {
		return $this->current_page;
	}

	/**
	 * Get `$this->empty_msg` string.
	 *
	 * @since 3.2.0
	 * @since 3.15.0 Fix filter name.
	 *
	 * @return string
	 */
	public function get_empty_message() {
		/**
		 * Filters the message displayed when the table is empty.
		 *
		 * The dynamic portion of this filter `{$this->id}` refers to the unique ID for the table.
		 *
		 * @since 3.15.0
		 *
		 * @param string $columns The message displayed when the table is empty.
		 */
		return apply_filters( "llms_table_get_{$this->id}_empty_message", $this->set_empty_message() );
	}

	/**
	 * Get the text for the default/placeholder for a filterable column.
	 *
	 * @since 3.4.0
	 * @since 3.15.0 Fix filter name.
	 * @since 7.3.0 Fixed typo in function name (`is_strinp` => `is_string` ).
	 *
	 * @param string $column_id The ID of the column.
	 * @return string
	 */
	public function get_filter_placeholder( $column_id, $column_data ) {
		$placeholder = __( 'Any', 'lifterlms' );
		if ( is_array( $column_data ) && isset( $column_data['title'] ) ) {
			$placeholder = sprintf( __( 'Any %s', 'lifterlms' ), $column_data['title'] );
		} elseif ( is_string( $column_data ) ) {
			$placeholder = sprintf( __( 'Any %s', 'lifterlms' ), $column_data );
		}
		/**
		 * Filters the placeholder string for a filterable column.
		 *
		 * The dynamic portion of this filter `{$this->id}` refers to the unique ID for the table.
		 *
		 * @since 3.15.0
		 *
		 * @param string $placeholder Placeholder string.
		 * @param string $column_id   The ID of the column.
		 */
		return apply_filters( "llms_table_get_{$this->id}_filter_placeholder", $placeholder, $column_id );
	}

	/**
	 * Get the current filter.
	 *
	 * @since 3.4.0
	 *
	 * @return string
	 */
	public function get_filter() {
		return $this->filter;
	}

	/**
	 * Get the current field results are filtered by.
	 *
	 * @since 3.4.0
	 *
	 * @return string
	 */
	public function get_filterby() {
		return $this->filterby;
	}

	/**
	 * Retrieve a modified classname that can be passed via AJAX for new queries.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public function get_handler() {
		return str_replace( 'LLMS_Table_', '', get_class( $this ) );
	}

	/**
	 * Retrieve the max number of pages for the table.
	 *
	 * @since 3.15.0
	 *
	 * @return int
	 */
	public function get_max_pages() {
		return $this->max_pages;
	}

	/**
	 * Get the current sort order.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public function get_order() {
		return $this->order;
	}

	/**
	 * Get the current field results are ordered by.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public function get_orderby() {
		return $this->orderby;
	}

	/**
	 * Get the current number of results to display per page.
	 *
	 * @since 3.28.0
	 *
	 * @return int
	 */
	public function get_per_page() {
		return $this->per_page;
	}

	/**
	 * Gets the opposite of the current order.
	 *
	 * Used to determine what order should be displayed when resorting.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	protected function get_new_order( $orderby = '' ) {

		// Current order matches submitted order, return opposite.
		if ( $this->orderby === $orderby ) {
			return ( 'ASC' === $this->order ) ? 'DESC' : 'ASC';
		} else {
			return 'ASC';
		}
	}

	/**
	 * Retrieves the current search query.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public function get_search() {
		return esc_attr( trim( $this->search ) );
	}

	/**
	 * Returns an array of CSS class names to use on this table.
	 *
	 * @since 3.34.0
	 *
	 * @return array
	 */
	protected function get_table_classes() {
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

		/**
		 * Filters the CSS classes to use on the table.
		 *
		 * @since 3.34.0
		 *
		 * @param array $classes  CSS class names.
		 * @param array $table_id Id property of this table object.
		 */
		return apply_filters( 'llms_table_get_table_classes', $classes, $this->id );
	}

	/**
	 * Get HTML for the filters displayed in the head of the table.
	 *
	 * @since 3.4.0
	 *
	 * @return string
	 * @deprecated 7.7.0 Use output_table_filters_html() instead.
	 */
	public function get_table_filters_html() {
		ob_start();
		$this->output_table_filters_html();
		return ob_get_clean();
	}

	/**
	 * Output HTML for the filters displayed in the head of the table.
	 *
	 * @since 7.7.0
	 *
	 * @return string
	 */
	public function output_table_filters_html() {
		?>
		<div class="llms-table-filters">
			<?php foreach ( $this->get_columns() as $id => $data ) : ?>
				<?php if ( is_array( $data ) && isset( $data['filterable'] ) && is_array( $data['filterable'] ) ) : ?>
					<div class="llms-table-filter-wrap">
						<select class="llms-select2 llms-table-filter" id="<?php echo esc_attr( sprintf( '%1$s-%2$s-filter', $this->id, $id ) ); ?>" name="<?php echo esc_attr( $id ); ?>">
							<option value="<?php echo esc_attr( $this->get_filter() ); ?>"><?php echo esc_html( $this->get_filter_placeholder( $id, $data ) ); ?></option>
							<?php foreach ( $data['filterable'] as $val => $name ) : ?>
								<option value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
		<?php
	}


	/**
	 * Return the HTML for the entire table.
	 *
	 * @since 3.2.0
	 * @since 3.17.8 Unknown.
	 * @since 3.37.7 Use correct argument order for implode to fix php 7.4 deprecation.
	 * @since 7.8.0 Added button for clearing resumable attempts.
	 *
	 * @return string
	 * @deprecated 7.7.0 Use output_table_html() instead.
	 */
	public function get_table_html() {
		ob_start();
		$this->output_table_html();

		return ob_get_clean();
	}

	/**
	 * Output the HTML for the entire table.
	 *
	 * @since 7.7.0
	 *
	 * @return void
	 */
	public function output_table_html() {

		$classes = $this->get_table_classes();

		?>
		<div class="llms-table-wrap">
			<header class="llms-table-header">
				<?php $this->output_table_title_html(); ?>
				<?php if ( $this->is_searchable ) : ?>
					<?php $this->output_table_search_form_html(); ?>
				<?php endif; ?>
				<?php if ( $this->is_filterable ) : ?>
					<?php $this->output_table_filters_html(); ?>
				<?php endif; ?>
			</header>
			<table
				class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
				data-args='<?php echo esc_attr( wp_json_encode( $this->get_args() ) ); ?>'
				data-handler="<?php echo esc_attr( $this->get_handler() ); ?>"
				id="llms-gb-table-<?php echo esc_attr( $this->id ); ?>"
			>
				<?php $this->output_thead_html(); ?>
				<?php $this->output_tbody_html(); ?>
				<?php $this->output_tfoot_html(); ?>
			</table>
		</div>
		<?php
	}

	/**
	 * Get the HTML of the search form for a searchable table.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 * @deprecated 7.7.0 Use output_table_search_form_html() instead.
	 */
	public function get_table_search_form_html() {
		ob_start();
		$this->output_table_search_form_html();
		return ob_get_clean();
	}

	/**
	 * Output the HTML of the search form for a searchable table.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public function output_table_search_form_html() {
		?>
		<div class="llms-table-search">
			<input class="regular-text" id="<?php echo esc_attr( $this->id ); ?>-search-input" placeholder="<?php echo esc_attr( $this->get_table_search_form_placeholder() ); ?>" type="text">
		</div>
		<?php
	}


	/**
	 * Get the Text to be used as the placeholder in a searchable tables search input.
	 *
	 * @since 3.2.0
	 * @since 3.15.0 Fix filter name.
	 *
	 * @return string
	 */
	public function get_table_search_form_placeholder() {
		/**
		 * Filters the text to be used as the placeholder in a searchable tables search input.
		 *
		 * The dynamic portion of this filter `{$this->id}` refers to the unique ID for the table.
		 *
		 * @since 3.15.0
		 *
		 * @param string $text Text to be used as the placeholder in a searchable tables search input.
		 */
		return apply_filters( "llms_table_get_{$this->id}_search_placeholder", __( 'Search', 'lifterlms' ) );
	}

	/**
	 * Get the HTML for the table's title.
	 *
	 * @since 3.2.0
	 * @since 3.15.0 Unknown.
	 *
	 * @return string
	 * @deprecated 7.7.0 Use output_table_title_html() instead.
	 */
	public function get_table_title_html() {
		ob_start();
		$this->output_table_title_html();
		return ob_get_clean();
	}

	/**
	 * Output the HTML for the table's title.
	 *
	 * @since 7.7.0
	 *
	 * @return void
	 */
	public function output_table_title_html() {
		$title = $this->get_title();
		if ( $title ) {
			echo '<h2 class="llms-table-title">' . esc_html( $title ) . '</h2>';
		}
	}

	/**
	 * Get `$this->tbody_data` array.

	 * @since 3.2.0
	 * @since 3.15.0 Fix filter name.
	 *
	 * @return array
	 */
	public function get_tbody_data() {
		/**
		 * Filters the array of tbody data.
		 *
		 * The dynamic portion of this filter `{$this->id}` refers to the unique ID for the table.
		 *
		 * @since 3.15.0
		 *
		 * @param array $tbody_data Array of data that will be used to create the table body.
		 */
		return apply_filters( "llms_table_get_{$this->id}_tbody_data", $this->tbody_data );
	}

	/**
	 * Get a tbody element for the table.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 * @deprecated 7.7.0 Use output_tbody_html() instead.
	 */
	public function get_tbody_html() {
		ob_start();
		$this->output_tbody_html();
		return ob_get_clean();
	}

	/**
	 * Output a tbody element for the table.
	 *
	 * @since 7.7.0
	 *
	 * @return void
	 */
	public function output_tbody_html() {
		$data = $this->get_tbody_data();
		?>
		<tbody>
		<?php if ( $data ) : ?>
			<?php foreach ( $data as $row ) : ?>
				<?php $this->output_tr_html( $row ); ?>
			<?php endforeach; ?>
		<?php else : ?>
			<tr><td class="llms-gb-table-empty" colspan="<?php echo esc_attr( $this->get_columns_count() ); ?>"><p><?php echo esc_html( $this->get_empty_message() ); ?></p></td></tr>
		<?php endif; ?>
		</tbody>
		<?php
	}

	/**
	 * Get a tfoot element for the table.
	 *
	 * @since 3.2.0
	 * @since 3.28.0 Unknown.
	 *
	 * @return string
	 * @deprecated 7.7.0 Use output_tfoot_html() instead.
	 */
	public function get_tfoot_html() {
		ob_start();
		$this->output_tfoot_html();
		return ob_get_clean();
	}

	/**
	 * Output a tfoot element for the table.
	 *
	 * @since 7.7.0
	 *
	 * @return void
	 */
	public function output_tfoot_html() {
		?>
		<tfoot>
		<tr>
			<th colspan="<?php echo esc_attr( $this->get_columns_count() ); ?>">
				<?php if ( $this->has_resumable_attempts() ) : ?>
					<div class="llms-clear-resumable-attempts">
						<form action="" method="POST">
							<button class="llms-button-primary small" name="llms_quiz_resumable_attempt_action" type="submit" value="llms_clear_resumable_attempts">
								<i class="fa fa-trash-o" aria-hidden="true"></i>
								<?php _e( 'Clear resumable attempts', 'lifterlms' ); ?>
							</button>
							<input type="hidden" name="llms_quiz_id" value="<?php echo esc_attr( $this->quiz_id ); ?>">
							<?php wp_nonce_field( 'llms_quiz_attempt_actions', '_llms_quiz_attempt_nonce' ); ?>
						</form>
					</div>
				<?php endif; ?>

				<?php if ( $this->is_exportable ) : ?>
					<div class="llms-table-export">
						<button class="llms-button-primary small" name="llms-table-export">
							<span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Export', 'lifterlms' ); ?>
						</button>
						<?php $this->output_progress_bar_html( 0 ); ?>
						<em><small class="llms-table-export-msg"></small></em>
					</div>
				<?php endif; ?>

				<?php if ( $this->is_paginated ) : ?>
					<div class="llms-table-pagination">
						<?php if ( $this->max_pages ) : ?>
							<span class="llms-table-page-count"><?php echo esc_html( sprintf( esc_html_x( '%1$d of %2$d', 'pagination', 'lifterlms' ), $this->current_page, $this->max_pages ) ); ?></span>
						<?php endif; ?>
						<?php if ( 1 !== $this->get_current_page() ) : ?>
							<?php if ( $this->max_pages ) : ?>
								<button class="llms-button-primary small" data-page="1" name="llms-table-paging"><span class="dashicons dashicons-arrow-left-alt"></span> <?php esc_html_e( 'First', 'lifterlms' ); ?></button>
							<?php endif; ?>
							<button class="llms-button-primary small" data-page="<?php echo esc_attr( $this->current_page - 1 ); ?>" name="llms-table-paging"><span class="dashicons dashicons-arrow-left-alt2"></span> <?php esc_html_e( 'Back', 'lifterlms' ); ?></button>
						<?php endif; ?>
						<?php if ( ! $this->is_last_page ) : ?>
							<button class="llms-button-primary small" data-page="<?php echo esc_attr( $this->current_page + 1 ); ?>" name="llms-table-paging"><?php esc_html_e( 'Next', 'lifterlms' ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span></button>
							<?php if ( $this->max_pages ) : ?>
								<button class="llms-button-primary small" data-page="<?php echo esc_attr( $this->max_pages ); ?>" name="llms-table-paging"><?php esc_html_e( 'Last', 'lifterlms' ); ?> <span class="dashicons dashicons-arrow-right-alt"></span></button>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</th>
		</tr>
		</tfoot>
		<?php
	}

	/**
	 * Check if any quiz has resumable attempts.
	 *
	 * @since 7.8.0
	 *
	 * @return bool
	 */
	public function has_resumable_attempts() {
		if ( 'quizzes' === llms_filter_input( INPUT_GET, 'tab' ) && 'attempts' === llms_filter_input( INPUT_GET, 'stab' ) ) {
			$admin_quiz_attempts = new LLMS_Controller_Admin_Quiz_Attempts();
			$quizzes             = $admin_quiz_attempts->get_resumable_attempts( $this->quiz_id );
			return ! empty( $quizzes );
		}
		return false;
	}

	/**
	 * Get a thead element for the table.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 * @deprecated 7.7.0 Use output_thead_html() instead.
	 */
	public function get_thead_html() {
		ob_start();
		$this->output_thead_html();
		return ob_get_clean();
	}

	/**
	 * Output the thead element for the table.
	 *
	 * @since 7.7.0
	 *
	 * @return void
	 */
	public function output_thead_html() {
		?>
		<thead>
		<tr>
			<?php foreach ( $this->get_columns() as $id => $data ) : ?>
				<th class="<?php echo esc_attr( $id ); ?>">
					<?php if ( is_array( $data ) ) : ?>
						<?php if ( isset( $data['sortable'] ) && $data['sortable'] ) : ?>
							<a class="llms-sortable<?php echo ( $this->get_orderby() === $id ) ? ' active' : ''; ?>" data-order="<?php echo esc_attr( $this->get_new_order( $id ) ); ?>" data-orderby="<?php echo esc_attr( $id ); ?>" href="#llms-gb-table-resort">
								<?php echo esc_html( $data['title'] ); ?>
								<span class="dashicons dashicons-arrow-up asc"></span>
								<span class="dashicons dashicons-arrow-down desc"></span>
							</a>
						<?php else : ?>
							<?php echo esc_html( $data['title'] ); ?>
						<?php endif; ?>
					<?php else : ?>
						<?php echo esc_html( $data ); ?>
					<?php endif; ?>
				</th>
			<?php endforeach; ?>
		</tr>
		</thead>
		<?php
	}

	/**
	 * Get a CSS class list (as a string) for each TR.
	 *
	 * @since 3.24.0
	 *
	 * @param mixed $row Object/array of data that the function can use to extract the data.
	 * @return string
	 */
	protected function get_tr_classes( $row ) {
		/**
		 * Filters the CSS class of a table row.
		 *
		 * The dynamic portion of this filter `{$this->id}` refers to the unique ID for the table.
		 *
		 * @since 3.24.0
		 *
		 * @param string $class CSS class list (as a string) for a given TR.
		 * @param mixed  $row   Object/array of data that the function can use to extract the data.
		 */
		return apply_filters( "llms_table_get_{$this->id}_tr_classes", 'llms-table-tr', $row );
	}

	/**
	 * Get the HTML for a single row in the body of the table.
	 *
	 * @since 3.2.0
	 * @since 3.21.0 Fix action hooks names.
	 *
	 * @param mixed $row Array/object of data describing a single row in the table.
	 * @return string
	 * @deprecated 7.7.0 Use output_tr_html() instead.
	 */
	public function get_tr_html( $row ) {
		ob_start();
		$this->output_tr_html( $row );
		return ob_get_clean();
	}

	/**
	 * Output the HTML for a single row in the body of the table.
	 *
	 * @since 7.7.0
	 *
	 * @param mixed $row Array/object of data describing a single row in the table.
	 * @return void
	 */
	public function output_tr_html( $row ) {
		/**
		 * Fired before a table `<tr>`.
		 *
		 * @since 3.21.0
		 *
		 * @param string           $row          Array/object of data describing a single row in the table.
		 * @param LLMS_Admin_Table $table_object Instance of the class extending `LLMS_Admin_Table`.
		 */
		do_action( 'llms_table_before_tr', $row, $this );
		?>
		<tr class="<?php echo esc_attr( $this->get_tr_classes( $row ) ); ?>">
			<?php foreach ( $this->get_columns() as $id => $title ) : ?>
				<td class="<?php echo esc_attr( $id ); ?>">
					<?php echo $this->get_data( $id, $row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</td>
			<?php endforeach; ?>
		</tr>
		<?php
		/**
		 * Fired after a table `<tr>`.
		 *
		 * @since 3.21.0
		 *
		 * @param string           $row          Array/object of data describing a single row in the table.
		 * @param LLMS_Admin_Table $table_object Instance of the class extending `LLMS_Admin_Table`.
		 */
		do_action( 'llms_table_after_tr', $row, $this );
	}



	/**
	 * Get the total number of columns in the table.
	 *
	 * Useful for creating full width tds via colspan.
	 *
	 * @since 3.2.0
	 *
	 * @return int
	 */
	public function get_columns_count() {
		return count( $this->get_columns() );
	}

	/**
	 * Get the HTML to output a progress bar within a td.
	 *
	 * Improve ugly tables with a small visual flourish.
	 * Useful when displaying a percentage within a table!
	 * Bonus if the table sorts by that percentage column.
	 *
	 * @since 3.4.1
	 *
	 * @param float  $percentage The percentage to be displayed.
	 * @param string $text       Text to display over the progress bar, defaults to $percentage.
	 * @return string
	 * @deprecated 7.7.0 Use output_progress_bar_html() instead.
	 */
	public function get_progress_bar_html( $percentage, $text = '' ) {
		ob_start();
		$this->output_progress_bar_html( $percentage, $text );
		return ob_get_clean();
	}

	/**
	 * Output the HTML to output a progress bar within a td.
	 *
	 * Improve ugly tables with a small visual flourish.
	 * Useful when displaying a percentage within a table!
	 * Bonus if the table sorts by that percentage column.
	 *
	 * @since 7.7.0
	 *
	 * @param float  $percentage The percentage to be displayed.
	 * @param string $text       Text to display over the progress bar, defaults to $percentage.
	 * @return void
	 */
	public function output_progress_bar_html( $percentage, $text = '' ) {
		$text = $text ? $text : $percentage . '%';
		?>
		<div class="llms-table-progress">
			<div class="llms-table-progress-bar"><div class="llms-table-progress-inner" style="width:<?php echo esc_attr( $percentage ); ?>%"></div></div>
			<span class="llms-table-progress-text"><?php echo esc_html( $text ); ?></span>
		</div>
		<?php
	}


	/**
	 * Get the HTML for a WP Post Link.
	 *
	 * @since 3.2.0
	 *
	 * @param int    $post_id WP Post ID.
	 * @param string $text    Optional text to display within the anchor, if none supplied $post_id if used.
	 * @return string
	 */
	public function get_post_link( $post_id, $text = '' ) {
		if ( ! $text ) {
			$text = $post_id;
		}
		return '<a href="' . esc_url( get_edit_post_link( $post_id ) ) . '">' . $text . '</a>';
	}

	/**
	 * Get the title of the table.
	 *
	 * @since 3.15.0
	 *
	 * @return string
	 */
	public function get_title() {
		/**
		 * Filters the table title.
		 *
		 * The dynamic portion of this filter `{$this->id}` refers to the unique ID for the table.
		 *
		 * @since 3.15.0
		 *
		 * @param string $title The title of the table.
		 */
		return apply_filters( "llms_table_get_{$this->id}_table_title", $this->title );
	}

	/**
	 * Get the HTML for a WP User Link.
	 *
	 * @since 3.17.2
	 *
	 * @param int    $user_id WP User ID.
	 * @param string $text    Optional text to display within the anchor, if none supplied $user_id if used.
	 * @return string
	 */
	public function get_user_link( $user_id, $text = '' ) {
		if ( ! $text ) {
			$text = $user_id;
		}
		return '<a href="' . esc_url( get_edit_user_link( $user_id ) ) . '">' . $text . '</a>';
	}

	/**
	 * Determine if a column is visible based on the current context.
	 *
	 * @since 3.15.0
	 *
	 * @param array  $data    Array of a single column's data from `set_columns()`.
	 * @param string $context Context [display|export].
	 * @return bool
	 */
	private function is_col_visible( $data, $context = 'display' ) {

		// Display if 'export_only' does not exist or it does exist and is false.
		if ( 'display' === $context ) {
			return ( ! isset( $data['export_only'] ) || ! $data['export_only'] );

			// Display if exportable is set and is true.
		} elseif ( 'export' === $context ) {
			return ( isset( $data['exportable'] ) && $data['exportable'] );
		}

		return true;
	}

	/**
	 * Return protected is_last_page var.
	 *
	 * @since 3.15.0
	 *
	 * @return bool
	 */
	public function is_last_page() {
		return $this->is_last_page;
	}

	/**
	 * Allow custom hooks to be registered for use within the class.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	protected function register_hooks() {}

	/**
	 * Setter.
	 *
	 * @since 2.3.0
	 *
	 * @param string $key Variable name.
	 * @param mixed  $val Variable data.
	 * @return void
	 */
	public function set( $key, $val ) {
		$this->$key = $val;
	}

	/**
	 * Empty message displayed when no results are found.
	 *
	 * @since 3.2.0
	 * @since 3.15.0 Fix filter name.
	 *
	 * @return string
	 */
	protected function set_empty_message() {
		/**
		 * Filters the default message displayed when the table is empty.
		 *
		 * The dynamic portion of this filter `{$this->id}` refers to the unique ID for the table.
		 *
		 * @since 3.15.0
		 *
		 * @param string $columns The default message displayed when the table is empty.
		 */
		return apply_filters( 'llms_table_default_empty_message', __( 'No results were found.', 'lifterlms' ) );
	}

	/**
	 * Stub used to set the title during table construction.
	 *
	 * @since 3.28.0
	 *
	 * @return string
	 */
	protected function set_title() {
		return '';
	}
}
