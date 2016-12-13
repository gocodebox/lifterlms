<?php
/**
 * Admin Reporting Base Class
 * @since   3.2.0
 * @version 3.2.0
 */
class LLMS_Admin_Reporting {

	/**
	 * Constructor
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function __construct() {

		self::includes();

	}

	/**
	 * Get array of course IDs selected accoding to applied filters
	 * @return   array
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public static function get_current_courses() {

		$r = isset( $_GET['course_ids'] ) ? $_GET['course_ids'] : array();
		if ( '' === $r ) {
			$r = array();
		}
		if ( is_string( $r ) ) {
			$r = explode( ',', $r );
		}
		return $r;

	}

	/**
	 * Get array of membership IDs selected accoding to applied filters
	 * @return   array
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public static function get_current_memberships() {

		$r = isset( $_GET['membership_ids'] ) ? $_GET['membership_ids'] : array();
		if ( '' === $r ) {
			$r = array();
		}
		if ( is_string( $r ) ) {
			$r = explode( ',', $r );
		}
		return $r;

	}

	/**
	 * Get the currently selected date range filter
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public static function get_current_range() {

		return ( isset( $_GET['range'] ) ) ? $_GET['range'] : 'last-7-days';

	}

	/**
	 * Get array of student IDs according to current filters
	 * @return   array
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public static function get_current_students() {

		$r = isset( $_GET['student_ids'] ) ? $_GET['student_ids'] : array();
		if ( '' === $r ) {
			$r = array();
		}
		if ( is_string( $r ) ) {
			$r = explode( ',', $r );
		}
		return $r;

	}

	/**
	 * Retrieve the current reporting tab
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public static function get_current_tab() {
		return isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'students';
	}

	/**
	 * Get the current end date accoring to filters
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public static function get_date_end() {
		return ( isset( $_GET['date_end'] ) ) ? $_GET['date_end'] : '';
	}

	/**
	 * Get the current strart date accoring to filters
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public static function get_date_start() {
		return ( isset( $_GET['date_start'] ) ) ? $_GET['date_start'] : '';
	}

	/**
	 * Get dates via the current date string
	 * @param    string   $range   date range string
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
				$dates['end'] = date( 'Y-m-d', strtotime( 'last day of last month', $now ) );

			break;

			case 'this-month':

				$dates['start'] = date( 'Y-m', $now ) . '-01';

			break;

			case 'last-7-days':

				$dates['start'] = date( 'Y-m-d', strtotime( '-7 days', $now ) );

			break;

			case 'custom':

				$dates['start'] = self::get_date_start();
				$dates['end'] = self::get_date_end();

			break;

		}

		return $dates;

	}

	public static function get_current_tab_url( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'page' => 'llms-reporting',
			'tab' => self::get_current_tab(),
		) );
		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * Get the full URL to a sub-tab within a reporting screen
	 * @param    string     $stab  slug of the sub-tab
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public static function get_stab_url( $stab ) {

		return add_query_arg( array(
			'page' => 'llms-reporting',
			'tab' => self::get_current_tab(),
			'stab' => $stab,
			'student_id' => $_GET['student_id'],
		), admin_url( 'admin.php' ) );

	}

	/**
	 * Get an array of tabs to output in the main reporting menu
	 * @return   array
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	private function get_tabs() {
		return apply_filters( 'lifterlms_reporting_tabs', array(
			'students' => __( 'Students', 'lifterlms' ),
			'sales' => __( 'Sales', 'lifterlms' ),
			'enrollments' => __( 'Enrollments', 'lifterlms' ),
		) );
	}

	/**
	 * Retrieve an array of data to pass to the reporting page template
	 * @return   array
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	private function get_template_data() {

		return array(
			'current_tab' => self::get_current_tab(),
			'tabs' => $this->get_tabs(),
		);

	}

	/**
	 * Include all required classes & files for the Reporting screens
	 * @return   void
	 * @since    3.2.0
	 * @version  3.2.0
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
	 * @return   void
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function output() {

		llms_get_template( 'admin/reporting/reporting.php', $this->get_template_data() );

	}

}
