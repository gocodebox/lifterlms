<?php
/**
 * Admin Reporting Base Class
 *
 * @package LifterLMS/Admin/Reporting/Classes
 *
 * @since 3.2.0
 * @version 7.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Reporting Base class.
 *
 * @since 3.2.0
 * @since 3.31.0 Fix redundant `if` statement in the `output_widget` method.
 * @since 3.32.0 Added Memberships tab.
 * @since 3.32.0 The `output_event()` method now outputs the student's avatar whent in 'membership' context.
 * @since 3.35.0 Sanitize input data.
 * @since 3.36.3 Fixed sanitization for input data array.
 */
class LLMS_Admin_Reporting {

	/**
	 * Constructor.
	 *
	 * @since 3.2.0
	 */
	public function __construct() {

		self::includes();
	}

	/**
	 * Get array of course IDs selected according to applied filters.
	 *
	 * @since 3.2.0
	 * @since 3.35.0 Sanitize input data.
	 * @since 3.36.3 Fixed sanitization for input data array.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return array
	 */
	public static function get_current_courses() {

		$r = isset( $_GET['course_ids'] ) ? llms_filter_input( INPUT_GET, 'course_ids', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY ) : array();

		if ( '' === $r ) {
			$r = array();
		}
		if ( is_string( $r ) ) {
			$r = array_map( 'absint', explode( ',', $r ) );
		}
		return $r;
	}

	/**
	 * Get array of membership IDs selected according to applied filters.
	 *
	 * @since 3.2.0
	 * @since 3.35.0 Sanitize input data.
	 * @since 3.36.3 Fixed sanitization for input data array.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return array
	 */
	public static function get_current_memberships() {

		$r = isset( $_GET['membership_ids'] ) ? llms_filter_input( INPUT_GET, 'membership_ids', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY ) : array();

		if ( '' === $r ) {
			$r = array();
		}
		if ( is_string( $r ) ) {
			$r = array_map( 'absint', explode( ',', $r ) );
		}
		return $r;
	}

	/**
	 * Get the currently selected date range filter.
	 *
	 * @since 3.2.0
	 * @since 3.35.0 Sanitize input data.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return string
	 */
	public static function get_current_range() {

		return ( isset( $_GET['range'] ) ) ? llms_filter_input_sanitize_string( INPUT_GET, 'range' ) : 'last-7-days';
	}

	/**
	 * Get array of student IDs according to current filters.
	 *
	 * @since 3.2.0
	 * @since 3.35.0 Sanitize input data.
	 * @since 3.36.3 Fixed sanitization for input data array.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return array
	 */
	public static function get_current_students() {

		$r = isset( $_GET['student_ids'] ) ? llms_filter_input( INPUT_GET, 'student_ids', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY ) : array();
		if ( '' === $r ) {
			$r = array();
		}
		if ( is_string( $r ) ) {
			$r = array_map( 'absint', explode( ',', $r ) );
		}
		return $r;
	}

	/**
	 * Retrieve the current reporting tab.
	 *
	 * @since 3.2.0
	 * @since 3.35.0 Sanitize input data.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return string
	 */
	public static function get_current_tab() {

		return isset( $_GET['tab'] ) ? llms_filter_input_sanitize_string( INPUT_GET, 'tab' ) : 'students';
	}

	/**
	 * Get the current end date according to filters.
	 *
	 * @since 3.2.0
	 * @since 3.35.0 Sanitize input data.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return string
	 */
	public static function get_date_end() {

		return ( isset( $_GET['date_end'] ) ) ? llms_filter_input_sanitize_string( INPUT_GET, 'date_end' ) : '';
	}

	/**
	 * Get the current start date according to filters.
	 *
	 * @since 3.2.0
	 * @since 3.35.0 Sanitize input data.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return string
	 */
	public static function get_date_start() {

		return ( isset( $_GET['date_start'] ) ) ? llms_filter_input_sanitize_string( INPUT_GET, 'date_start' ) : '';
	}

	/**
	 * Get dates via the current date string.
	 *
	 * @since 3.2.0
	 *
	 * @param string $range Date range string.
	 * @return array
	 */
	public static function get_dates( $range ) {

		$now = current_time( 'timestamp' );

		$dates = array(
			'start' => '',
			'end'   => date( 'Y-m-d', $now ),
		);

		switch ( $range ) {

			case 'this-year':
				$dates['start'] = date( 'Y', $now ) . '-01-01';
				break;

			case 'last-month':
				$dates['start'] = date( 'Y-m-d', strtotime( 'first day of last month', $now ) );
				$dates['end']   = date( 'Y-m-d', strtotime( 'last day of last month', $now ) );
				break;

			case 'this-month':
				$dates['start'] = date( 'Y-m', $now ) . '-01';
				break;

			case 'last-7-days':
				$dates['start'] = date( 'Y-m-d', strtotime( '-7 days', $now ) );
				break;

			case 'custom':
				$dates['start'] = self::get_date_start();
				$dates['end']   = self::get_date_end();
				break;
		}

		return $dates;
	}

	/**
	 * Returns an admin URL with the given arguments added as query variables.
	 *
	 * @since 3.2.0
	 *
	 * @param array $args Arguments to add to the query string.
	 * @return string
	 */
	public static function get_current_tab_url( $args = array() ) {

		$args = wp_parse_args(
			$args,
			array(
				'page' => 'llms-reporting',
				'tab'  => self::get_current_tab(),
			)
		);
		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * Retrieves arguments for {@see LifterLMS_Admin_Reporting::output_widget}.
	 *
	 * Merges the supplied arguments with the default args.
	 *
	 * @since 6.11.0
	 *
	 * @param array $args Widget settings and data, {@see LifterLMS_Adming_Reporting::output_widget}.
	 * @return array Merged arguments.
	 */
	private static function get_output_widget_args( $args = array() ) {

		return wp_parse_args(
			$args,
			array(
				'id'           => '',
				'text'         => '',
				'data'         => '',
				'data_compare' => '',
				'data_type'    => 'numeric', // Enum: numeric, monetary, text, percentage, or date.
				'icon'         => '',
				'impact'       => 'positive', // Enum: positive or negative.
				'cols'         => 'd-1of2',
			)
		);
	}

	/**
	 * Retrieve an array of period filters used by self::output_widget_range_filter().
	 *
	 * @since 3.16.0
	 *
	 * @return array
	 */
	public static function get_period_filters() {

		return array(
			'today'      => esc_attr__( 'Today', 'lifterlms' ),
			'yesterday'  => esc_attr__( 'Yesterday', 'lifterlms' ),
			'week'       => esc_attr__( 'This Week', 'lifterlms' ),
			'last_week'  => esc_attr__( 'Last Week', 'lifterlms' ),
			'month'      => esc_attr__( 'This Month', 'lifterlms' ),
			'last_month' => esc_attr__( 'Last Month', 'lifterlms' ),
			'year'       => esc_attr__( 'This Year', 'lifterlms' ),
			'last_year'  => esc_attr__( 'Last Year', 'lifterlms' ),
			'all_time'   => esc_attr__( 'All Time', 'lifterlms' ),
		);
	}

	/**
	 * Get the full URL to a sub-tab within a reporting screen.
	 *
	 * @since 3.2.0
	 * @since 3.32.0 Added Memberships tab.
	 * @since 3.35.0 Sanitize input data.
	 *
	 * @param string $stab Slug of the sub-tab.
	 * @return string
	 */
	public static function get_stab_url( $stab ) {

		$args = array(
			'page' => 'llms-reporting',
			'tab'  => self::get_current_tab(),
			'stab' => $stab,
		);

		switch ( self::get_current_tab() ) {
			case 'memberships':
				$args['membership_id'] = llms_filter_input( INPUT_GET, 'membership_id', FILTER_SANITIZE_NUMBER_INT );
				break;

			case 'courses':
				$args['course_id'] = llms_filter_input( INPUT_GET, 'course_id', FILTER_SANITIZE_NUMBER_INT );
				break;

			case 'students':
				$args['student_id'] = llms_filter_input( INPUT_GET, 'student_id', FILTER_SANITIZE_NUMBER_INT );
				break;

			case 'quizzes':
				$args['quiz_id'] = llms_filter_input( INPUT_GET, 'quiz_id', FILTER_SANITIZE_NUMBER_INT );
				break;
		}

		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * Get an array of tabs to output in the main reporting menu.
	 *
	 * @since 3.2.0
	 * @since 3.32.0 Added Memberships tab.
	 *
	 * @return array
	 */
	private function get_tabs() {

		$tabs = array(
			'students'    => __( 'Students', 'lifterlms' ),
			'courses'     => __( 'Courses', 'lifterlms' ),
			'memberships' => __( 'Memberships', 'lifterlms' ),
			'quizzes'     => __( 'Quizzes', 'lifterlms' ),
			'sales'       => __( 'Sales', 'lifterlms' ),
			'enrollments' => __( 'Enrollments', 'lifterlms' ),
		);
		foreach ( $tabs as $slug => $tab ) {
			if ( ! current_user_can( $this->get_tab_cap( $slug ) ) ) {
				unset( $tabs[ $slug ] );
			}
		}
		return apply_filters( 'lifterlms_reporting_tabs', $tabs );
	}

	/**
	 * Get the WP capability required to access a reporting tab.
	 *
	 * Defaults to 'view_lifterlms_reports'. Most reports implement additional permissions within the view.
	 * Sales & Enrollments tab requires 'view_others_lifterlms_reports' b/c they don't add any additional filters
	 * within the view.
	 *
	 * @since 3.19.4
	 * @since 7.3.0 Use `in_array()` with strict type comparison.
	 *
	 * @param string $tab ID/slug of the tab.
	 * @return string
	 */
	private function get_tab_cap( $tab = null ) {

		$tab = is_null( $tab ) ? self::get_current_tab() : $tab;

		$cap = 'view_lifterlms_reports';
		if ( in_array( $tab, array( 'sales', 'enrollments' ), true ) ) {
			$cap = 'view_others_lifterlms_reports';
		}

		/**
		 * Filters the WP capability required to access a reporting tab.
		 *
		 * @since 3.19.4
		 * @since 7.3.0 Added the `$tab` parameter.
		 *
		 * @param string      $cap The required WP capability.
		 * @param string|null $tab ID/slug of the tab.
		 */
		return apply_filters( 'lifterlms_reporting_tab_cap', $cap, $tab );
	}

	/**
	 * Retrieve an array of data to pass to the reporting page template.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	private function get_template_data() {

		return array(
			'current_tab' => self::get_current_tab(),
			'tabs'        => $this->get_tabs(),
		);
	}

	/**
	 * Include all required classes & files for the Reporting screens.
	 *
	 * @since 3.2.0
	 * @since 3.16.0 Unknown.
	 * @since 6.0.0 Removed loading of class files that don't instantiate their class in favor of autoloading.
	 *
	 * @return void
	 */
	public static function includes() {

		// Include tab classes.
		foreach ( glob( LLMS_PLUGIN_DIR . '/includes/admin/reporting/tabs/*.php' ) as $filename ) {
			include_once $filename;
		}
	}

	/**
	 * Output the reporting screen HTML.
	 *
	 * @since 3.2.0
	 * @since 3.19.4 Unknown.
	 *
	 * @return void
	 */
	public function output() {

		if ( ! current_user_can( $this->get_tab_cap() ) ) {
			wp_die( esc_html__( 'You don\'t have permission to do that', 'lifterlms' ) );
		}

		llms_get_template( 'admin/reporting/reporting.php', $this->get_template_data() );
	}

	/**
	 * Output the HTML for a postmeta event in the recent events sidebar of various reporting screens.
	 *
	 * @since 3.15.0
	 * @since 3.32.0 Outputs the student's avatar when in 'membership' context.
	 *
	 * @param LLMS_User_Postmeta $event   Instance of an LLMS_User_Postmeta item.
	 * @param string             $context Optional. Display context [course|student|quiz|membership]. Default 'course'.
	 * @return void
	 */
	public static function output_event( $event, $context = 'course' ) {

		$student = $event->get_student();
		if ( ! $student ) {
			return;
		}

		$url = $event->get_link( $context );

		?>
		<div class="llms-reporting-event <?php echo esc_attr( $event->get( 'meta_key' ) ); ?> <?php echo esc_attr( $event->get( 'meta_value' ) ); ?>">

			<?php if ( $url ) : ?>
				<a href="<?php echo esc_url( $url ); ?>">
			<?php endif; ?>

				<?php if ( 'course' === $context || 'membership' === $context || 'quiz' === $context ) : ?>
					<?php echo wp_kses_post( $student->get_avatar( 24 ) ); ?>
				<?php endif; ?>

				<?php echo wp_kses_post( $event->get_description( $context ) ); ?>
				<time datetime="<?php echo esc_attr( $event->get( 'updated_date' ) ); ?>"><?php echo esc_attr( llms_get_date_diff( current_time( 'timestamp' ), $event->get( 'updated_date' ), 1 ) ); ?></time>

			<?php if ( $url ) : ?>
				</a>
			<?php endif; ?>

		</div>
		<?php
	}

	/**
	 * Outputs the HTML for a reporting widget.
	 *
	 * @since 3.15.0
	 * @since 3.31.0 Remove redundant `if` statement.
	 * @since 6.11.0 Moved HTML into a view file.
	 *               Fixed division by zero error encountered during data comparisons when `$data` is `0`.
	 *               Added a check to ensure only numeric, monetary, or percentage data types will generate comparison data.
	 * @since 7.3.0 Better rounding of float values of percentage data types.
	 *
	 * @param array $args {
	 *    Array of widget options and data to be displayed.
	 *
	 *    @type string           $id           Required. A unique identifier for the widget.
	 *    @type string           $text         A short description of the widget's data.
	 *    @type int|string|float $data         The value of the data to display.
	 *    @type int|string|float $data_compare Additional data to compare $data against.
	 *    @type string           $data_type    The type of data. Used to format displayed data. Accepts "numeric",
	 *                                         "monetary", "text", "percentage", or "date".
	 *    @type string           $icon         An optional Font Awesome icon used to help visually identify the widget.
	 *                                         If supplied, should be supplied without the `fa-` icon prefix.
	 *    @type string           $impact       The type of impact the data has, either "positive" or "negative". This
	 *                                         is used when displaying comparisons to determine if the change was a positive
	 *                                         change or negative change. For example: student enrollments has a positive
	 *                                         impact while quiz failures has a negative impact. An increase in enrollments
	 *                                         will be displayed in green while a decrease will be displayed in red. An
	 *                                         increase in quiz failures will be displayed in red while a decrease will be
	 *                                         displayed in green.
	 *    @type string           $cols         Grid class widget width ID. See: assets/scss/admin/partials/_grid.scss.
	 * }
	 * @return void
	 */
	public static function output_widget( $args = array() ) {

		$args = self::get_output_widget_args( $args );

		// Only these data types can make comparisons.
		$can_compare = in_array( $args['data_type'], array( 'numeric', 'monetary', 'percentage' ), true );

		// Adds a percentage symbol after data.
		$data_after = 'percentage' === $args['data_type'] && is_numeric( $args['data'] ) ? '<sup>%</sup>' : '';

		$change             = false;
		$compare_operator   = '';
		$compare_class      = '';
		$compare_title      = '';
		$floating_precision = llms_get_floats_rounding_precision();

		if ( $can_compare && $args['data_compare'] && floatval( $args['data'] ) ) {
			$change           = round( ( $args['data'] - $args['data_compare'] ) / $args['data'] * 100, $floating_precision );
			$compare_operator = ( $change <= 0 ) ? '' : '+';
			$compare_title    = sprintf(
				// Translators: %s = The value of the data from the previous data set.
				esc_attr__( 'Previously %s', 'lifterlms' ),
				round( $args['data_compare'], $floating_precision ) . wp_strip_all_tags( $data_after )
			);

			$compare_class = ( $change <= 0 ) ? 'negative' : 'positive';
			if ( 'negative' === $args['impact'] ) {
				$compare_class = ( $change <= 0 ) ? 'positive' : 'negative';
			}
		}

		if ( is_numeric( $args['data'] ?? '' ) ) {
			if ( 'percentage' === $args['data_type'] ) {
				$args['data'] = round( $args['data'], $floating_precision );
			} elseif ( 'monetary' === $args['data_type'] ) {
				$args['data']         = llms_price( $args['data'] );
				$args['data_compare'] = llms_price_raw( $args['data_compare'] );
			}
		}

		$args['id'] = esc_attr( $args['id'] );

		include LLMS_PLUGIN_DIR . 'includes/admin/views/reporting/widget.php';
	}

	/**
	 * Output a range filter select.
	 *
	 * Used by overview data tabs
	 *
	 * @since 3.16.0
	 *
	 * @param string $selected_period Currently selected period.
	 * @param string $tab             Current tab name.
	 * @param array  $args            Additional args to be passed when form is submitted.
	 * @return void
	 */
	public static function output_widget_range_filter( $selected_period, $tab, $args = array() ) {
		?>
		<div class="llms-reporting-tab-filter">
			<form action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" method="GET">
				<select class="llms-select2" name="period" onchange="this.form.submit();">
					<?php foreach ( self::get_period_filters() as $val => $text ) : ?>
						<option value="<?php echo esc_attr( $val ); ?>"<?php selected( $val, $selected_period ); ?>><?php echo esc_html( $text ); ?></option>
					<?php endforeach; ?>
				</select>
				<input type="hidden" name="page" value="llms-reporting">
				<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>">
				<?php foreach ( $args as $key => $val ) : ?>
					<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $val ); ?>">
				<?php endforeach; ?>
			</form>
		</div>
		<?php
	}
}
