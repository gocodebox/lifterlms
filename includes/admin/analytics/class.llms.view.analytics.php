<?php
/**
* Admin Reporting Base Class
*
* @author codeBOX
* @project LifterLMS
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Analytics_View {

	public function __construct() {

		$this->includes();

		$tabs = $this->get_tabs();

		$current_tab = $this->get_current_tab();

		$current_range = $this->get_current_range();

		$current_students = $this->get_current_students();

		$current_courses = $this->get_current_courses();

		$current_memberships = $this->get_current_memberships();

		$widget_data = $this->get_widgets();

		$range_dates = $this->get_dates( $current_range );
		$date_start = $range_dates['start'];
		$date_end = $range_dates['end'];

		// json to pass to the view to be accessible by javascript
		$json = json_encode( array(

			'courses' => $current_courses,
			'dates' => $range_dates,
			'memberships' => $current_memberships,
			'range' => $current_range,
			'students' => $current_students,
			'tab' => $current_tab,

		) );

		include LLMS_PLUGIN_DIR . 'templates/admin/analytics/analytics.php';

	}


	private function includes() {

		include LLMS_PLUGIN_DIR . 'includes/abstracts/abstract.llms.analytics.widget.php';
		include LLMS_PLUGIN_DIR . 'includes/admin/analytics/widgets/class.llms.analytics.widget.enrollments.php';

	}






	private function get_current_courses() {

		$r = isset( $_GET['course_ids'] ) ? $_GET['course_ids'] : array();
		if ( '' === $r ) {
			$r = array();
		}
		if ( is_string( $r ) ) {
			$r = explode( ',', $r );
		}
		return $r;

	}

	private function get_current_memberships() {

		$r = isset( $_GET['membership_ids'] ) ? $_GET['membership_ids'] : array();
		if ( '' === $r ) {
			$r = array();
		}
		if ( is_string( $r ) ) {
			$r = explode( ',', $r );
		}
		return $r;

	}

	private function get_current_range() {

		return ( isset( $_GET['range'] ) ) ? $_GET['range'] : 'last-7-days';

	}


	private function get_current_students() {

		$r = isset( $_GET['student_ids'] ) ? $_GET['student_ids'] : array();
		if ( '' === $r ) {
			$r = array();
		}
		if ( is_string( $r ) ) {
			$r = explode( ',', $r );
		}
		return $r;

	}

	private function get_date_end() {

		return ( isset( $_GET['date_end'] ) ) ? $_GET['date_end'] : '';

	}

	private function get_date_start() {

		return ( isset( $_GET['date_start'] ) ) ? $_GET['date_start'] : '';

	}

	private function get_dates( $range ) {

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

				$dates['start'] = $this->get_date_start();
				$dates['end'] = $this->get_date_end();

			break;

		}

		return $dates;

	}


	private function get_current_tab() {

		return ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : 'sales';

	}


	private function get_tabs() {

		return apply_filters( 'lifterlms_reporting_tabs', array(

			'sales' => __( 'Sales', 'lifterlms' ),
			'enrollments' => __( 'Enrollments', 'lifterlms' ),
			// 'students' => __( 'Students', 'lifterlms' ),
			// 'engagements' => __( 'Engagements', 'lifterlms' ),
			// 'quizzes' => __( 'Quizzes', 'lifterlms' ),

		) );

	}


	private function get_widgets() {

		$current_tab = $this->get_current_tab();

		switch ( $current_tab ) {
			case 'sales':
				$widgets = array(
					array(
						'sales' => array(
							'title' => __( '# of Sales', 'lifterlms' ),
							'cols' => '1-4',
							'content' => __( 'loading...', 'lifterlms' ),
							'info' => __( 'Number of new active or completed orders placed within this period', 'lifterlms' ),
						),
						'sold' => array(
							'title' => __( 'Net Sales', 'lifterlms' ),
							'cols' => '1-4',
							'content' => __( 'loading...', 'lifterlms' ),
							'info' => __( 'Total of all successful transactions during this period', 'lifterlms' ),
						),
						'refunds' => array(
							'title' => __( '# of Refunds', 'lifterlms' ),
							'cols' => '1-4',
							'content' => __( 'loading...', 'lifterlms' ),
							'info' => __( 'Number of orders refunded during this period', 'lifterlms' ),
						),
						'refunded' => array(
							'title' => __( 'Amount Refunded', 'lifterlms' ),
							'cols' => '1-4',
							'content' => __( 'loading...', 'lifterlms' ),
							'info' => __( 'Total of all transactions refunded during this period', 'lifterlms' ),
						),
					),
					array(
						// 'revenue' => array(
						// 	'title' => __( 'Grosse Revenue', 'lifterlms' ),
						// 	'cols' => '1-4',
						// 	'content' => __( 'loading...', 'lifterlms' ),
						// 	'info' => __( 'Total of all transactions minus all refunds processed during this period', 'lifterlms' ),
						// ),
						'coupons' => array(
							'title' => __( '# of Coupons Used', 'lifterlms' ),
							'cols' => '1-4',
							'content' => __( 'loading...', 'lifterlms' ),
							'info' => __( 'Number of orders completed using coupons during this period', 'lifterlms' ),
						),
						'discounts' => array(
							'title' => __( 'Amount of Coupons', 'lifterlms' ),
							'cols' => '1-4',
							'content' => __( 'loading...', 'lifterlms' ),
							'info' => __( 'Total amount of coupons used during this period', 'lifterlms' ),
						),
						// 'whatever3' => array(
						// 	'title' => 'test title',
						// 	'cols' => '1-4',
						// 	'content' => __( 'loading...', 'lifterlms' ),
						// 	'info' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
						// ),
						// 'whatever4' => array(
						// 	'title' => 'test title 2',
						// 	'cols' => '1-4',
						// 	'content' => __( 'loading...', 'lifterlms' ),
						// 	'info' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
						// ),
					),
				);
			break;

			case 'enrollments':
				$widgets = array(
					array(
						'enrollments' => array(
							'title' => __( 'Enrollments', 'lifterlms' ),
							'cols' => '1-3',
							'content' => __( 'loading...', 'lifterlms' ),
							'info' => __( 'Number of total enrollments during the selected period', 'lifterlms' ),
						),
					),
				);
			break;

			default:
				$widgets = array();

		}

		return apply_filters( 'lifterlms_reporting_widgets_' . $current_tab, $widgets, $this );

	}


}
return new LLMS_Analytics_View();
