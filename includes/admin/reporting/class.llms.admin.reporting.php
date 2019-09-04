<?php
/**
 * Admin Reporting Base Class
 *
 * @since 3.2.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Reporting Base Class
 *
 * @since 3.2.0
 * @since 3.31.0 Fix redundant `if` statement in the `output_widget` method.
 * @since 3.32.0 Added Memberships tab.
 * @since 3.32.0 The `output_event()` method now outputs the student's avatar whent in 'membership' context.
 * @since 3.35.0 Sanitize input data.
 */
class LLMS_Admin_Reporting {

	/**
	 * Constructor
	 *
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function __construct() {

		self::includes();

	}

	/**
	 * Get array of course IDs selected according to applied filters
	 *
	 * @since 3.2.0
	 * @since 3.35.0 Sanitize input data.
	 *
	 * @return   array
	 */
	public static function get_current_courses() {

		$r = isset( $_GET['course_ids'] ) ? llms_filter_input( INPUT_GET, 'course_ids', FILTER_SANITIZE_STRING ) : array();
		if ( '' === $r ) {
			$r = array();
		}
		if ( is_string( $r ) ) {
			$r = array_map( 'absint', explode( ',', $r ) );
		}
		return $r;

	}

	/**
	 * Get array of membership IDs selected according to applied filters
	 *
	 * @since 3.2.0
	 * @since 3.35.0 Sanitize input data.
	 *
	 * @return   array
	 */
	public static function get_current_memberships() {

		$r = isset( $_GET['membership_ids'] ) ? llms_filter_input( INPUT_GET, 'membership_ids', FILTER_SANITIZE_STRING ) : array();
		if ( '' === $r ) {
			$r = array();
		}
		if ( is_string( $r ) ) {
			$r = array_map( 'absint', explode( ',', $r ) );
		}
		return $r;
	}

	/**
	 * Get the currently selected date range filter
	 *
	 * @since 3.2.0
	 * @since 3.35.0 Sanitize input data.
	 *
	 * @return   string
	 */
	public static function get_current_range() {

		return ( isset( $_GET['range'] ) ) ? llms_filter_input( INPUT_GET, 'range', FILTER_SANITIZE_STRING ) : 'last-7-days';

	}

	/**
	 * Get array of student IDs according to current filters
	 *
	 * @since 3.2.0
	 * @since 3.35.0 Sanitize input data.
	 *
	 * @return   array
	 */
	public static function get_current_students() {

		$r = isset( $_GET['student_ids'] ) ? llms_filter_input( INPUT_GET, 'student_ids', FILTER_SANITIZE_STRING ) : array();
		if ( '' === $r ) {
			$r = array();
		}
		if ( is_string( $r ) ) {
			$r = array_map( 'absint', explode( ',', $r ) );
		}
		return $r;

	}

	/**
	 * Retrieve the current reporting tab
	 *
	 * @since 3.2.0
	 * @since 3.35.0 Sanitize input data.
	 *
	 * @return   string
	 */
	public static function get_current_tab() {
		return isset( $_GET['tab'] ) ? llms_filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING ) : 'students';
	}

	/**
	 * Get the current end date according to filters
	 *
	 * @since 3.2.0
	 * @since 3.35.0 Sanitize input data.
	 *
	 * @return   string
	 */
	public static function get_date_end() {
		return ( isset( $_GET['date_end'] ) ) ? llms_filter_input( INPUT_GET, 'date_end', FILTER_SANITIZE_STRING ) : '';
	}

	/**
	 * Get the current start date according to filters
	 *
	 * @since 3.2.0
	 * @since 3.35.0 Sanitize input data.
	 *
	 * @return   string
	 */
	public static function get_date_start() {
		return ( isset( $_GET['date_start'] ) ) ? llms_filter_input( INPUT_GET, 'date_start', FILTER_SANITIZE_STRING ) : '';
	}

	/**
	 * Get dates via the current date string
	 *
	 * @param    string $range   date range string
	 * @return   array
	 * @since    3.2.0
	 * @version  3.2.0
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
	 * Retrieve an array of period filters
	 * used by self::output_widget_range_filter()
	 *
	 * @since 3.16.0
	 *
	 * @return   array
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
	 * Get the WP capability required to access a reporting tab
	 * Defaults to 'view_lifterlms_reports' -- most reports implement additional permissions within the view
	 * Sales & Enrollments tab require 'view_others_lifterlms_reports' b/c they don't add any additional filters within the view
	 *
	 * @param    string $tab  id/slug of the tab
	 * @return   string
	 * @since    3.19.4
	 * @version  3.19.4
	 */
	private function get_tab_cap( $tab = null ) {

		$tab = is_null( $tab ) ? self::get_current_tab() : $tab;

		$cap = 'view_lifterlms_reports';
		if ( in_array( $tab, array( 'sales', 'enrollments' ) ) ) {
			$cap = 'view_others_lifterlms_reports';
		}

		return apply_filters( 'lifterlms_reporting_tab_cap', $cap );

	}

	/**
	 * Retrieve an array of data to pass to the reporting page template
	 *
	 * @return   array
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	private function get_template_data() {

		return array(
			'current_tab' => self::get_current_tab(),
			'tabs'        => $this->get_tabs(),
		);

	}

	/**
	 * Include all required classes & files for the Reporting screens
	 *
	 * @return   void
	 * @since    3.2.0
	 * @version  3.16.0
	 */
	public static function includes() {

		include_once LLMS_PLUGIN_DIR . '/includes/abstracts/abstract.llms.admin.table.php';

		// include all the table classes
		foreach ( glob( LLMS_PLUGIN_DIR . '/includes/admin/reporting/tables/*.php' ) as $filename ) {
			include_once $filename;
		}

		// include tab classes
		foreach ( glob( LLMS_PLUGIN_DIR . '/includes/admin/reporting/tabs/*.php' ) as $filename ) {
			include_once $filename;
		}

	}

	/**
	 * Output the reporting screen html
	 *
	 * @return   void
	 * @since    3.2.0
	 * @version  3.19.4
	 */
	public function output() {

		if ( ! current_user_can( $this->get_tab_cap() ) ) {
			wp_die( __( 'You don\'t have permission to do that', 'lifterlms' ) );
		}

		llms_get_template( 'admin/reporting/reporting.php', $this->get_template_data() );

	}

	/**
	 * Output the HTML for a postmeta event in the recent events sidebar of various reporting screens.
	 *
	 * @since 3.15.0
	 * @since 3.32.0 Outputs the student's avatar whent in 'membership' context
	 *
	 * @param obj    $event   Instance of an LLMS_User_Postmeta item.
	 * @param string $context Optional. Display context [course|student|quiz|membership]. Default 'course'.
	 * @return   void
	 */
	public static function output_event( $event, $context = 'course' ) {

		$student = $event->get_student();
		if ( ! $student ) {
			return;
		}

		$url = $event->get_link( $context );

		?>
		<div class="llms-reporting-event <?php echo $event->get( 'meta_key' ); ?> <?php echo $event->get( 'meta_value' ); ?>">

			<?php if ( $url ) : ?>
				<a href="<?php echo esc_url( $url ); ?>">
			<?php endif; ?>

				<?php if ( 'course' === $context || 'membership' === $context || 'quiz' === $context ) : ?>
					<?php echo $student->get_avatar( 24 ); ?>
				<?php endif; ?>

				<?php echo $event->get_description( $context ); ?>
				<time datetime="<?php echo $event->get( 'updated_date' ); ?>"><?php echo llms_get_date_diff( current_time( 'timestamp' ), $event->get( 'updated_date' ), 1 ); ?></time>

			<?php if ( $url ) : ?>
				</a>
			<?php endif; ?>

		</div>
		<?php

	}

	/**
	 * Output the HTML for a reporting widget
	 *
	 * @since 3.15.0
	 * @since 3.31.0 Remove redundant `if` statement.
	 *
	 * @param    array $args   widget options
	 * @return   void
	 */
	public static function output_widget( $args = array() ) {

		$args = wp_parse_args(
			$args,
			array(

				'cols'         => 'd-1of2',
				'data'         => '',
				'data_compare' => '',
				'data_type'    => 'numeric', // [numeric|monetary|text|percentage|date]
				'icon'         => '',
				'id'           => '',
				'impact'       => 'positive',
				'text'         => '',

			)
		);

		$data_after = '';
		if ( 'percentage' === $args['data_type'] && is_numeric( $args['data'] ) ) {
			$data_after = '<sup>%</sup>';
		}

		$change = false;
		if ( $args['data_compare'] && $args['data'] ) {

			$change           = round( ( $args['data'] - $args['data_compare'] ) / $args['data'] * 100, 2 );
			$compare_operator = ( $change <= 0 ) ? '' : '+';
			if ( 'positive' === $args['impact'] ) {
				$compare_class = ( $change <= 0 ) ? 'negative' : 'positive';
			} else {
				$compare_class = ( $change <= 0 ) ? 'positive' : 'negative';
			}
		}

		if ( 'monetary' === $args['data_type'] && is_numeric( $args['data'] ) ) {
			$args['data']         = llms_price( $args['data'] );
			$args['data_compare'] = llms_price_raw( $args['data_compare'] );
		}

		?>
		<div class="<?php echo esc_attr( $args['cols'] ); ?>">
			<div class="llms-reporting-widget <?php echo esc_attr( $args['id'] ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>">
				<?php if ( $args['icon'] ) : ?>
					<i class="fa fa-<?php echo $args['icon']; ?>" aria-hidden="true"></i>
				<?php endif; ?>
				<div class="llms-reporting-widget-data">
					<strong><?php echo $args['data'] . $data_after; ?></strong>
					<?php if ( $change ) : ?>
						<small class="compare tooltip <?php echo $compare_class; ?>" title="<?php printf( esc_attr__( 'Previously %s', 'lifterlms' ), $args['data_compare'] ); ?>">
							<?php echo $compare_operator . $change; ?>%
						</small>
					<?php endif; ?>
				</div>
				<small><?php echo $args['text']; ?></small>
			</div>
		</div>
		<?php

	}

	/**
	 * Output a range filter select
	 * Used by overview data tabs
	 *
	 * @param    string $selected_period  currently selected period
	 * @param    string $tab              current tab name
	 * @param    array  $args             additional args to be passed when form is submitted
	 * @return   void
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public static function output_widget_range_filter( $selected_period, $tab, $args = array() ) {
		?>
		<div class="llms-reporting-tab-filter">
			<form action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" method="GET">
				<select class="llms-select2" name="period" onchange="this.form.submit();">
					<?php foreach ( self::get_period_filters() as $val => $text ) : ?>
						<option value="<?php echo $val; ?>"<?php selected( $val, $selected_period ); ?>><?php echo $text; ?></option>
					<?php endforeach; ?>
				</select>
				<input type="hidden" name="page" value="llms-reporting">
				<input type="hidden" name="tab" value="<?php echo $tab; ?>">
				<?php foreach ( $args as $key => $val ) : ?>
					<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $val; ?>">
				<?php endforeach; ?>
			</form>
		</div>
		<?php
	}

}
