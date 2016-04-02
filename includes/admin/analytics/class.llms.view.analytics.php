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

		$widget_data = $this->get_widgets();

		$range_dates = $this->get_dates( $current_range );
		$date_start = $range_dates['start'];
		$date_end = $range_dates['end'];

		// json to pass to the view to be accessible by javascript
		$json = json_encode( array(

			'dates' => $range_dates,
			'range' => $current_range,
			'students' => $current_students,
			'tab' => $current_tab,

		) );

		include LLMS_PLUGIN_DIR . 'includes/admin/views/html.admin.analytics_new.php';

	}


	private function includes() {

		include LLMS_PLUGIN_DIR . 'includes/admin/analytics/widgets/abstract.llms.analytics.widget.php';
		include LLMS_PLUGIN_DIR . 'includes/admin/analytics/widgets/class.llms.analytics.widget.enrollments.php';

	}



	private function get_date_start() {

		return ( isset( $_GET['date_start'] ) ) ? $_GET['date_start'] : '';

	}


	private function get_date_end() {

		return ( isset( $_GET['date_end'] ) ) ? $_GET['date_end'] : '';

	}


	private function get_current_range() {

		return ( isset( $_GET['range'] ) ) ? $_GET['range'] : 'last-7-days';

	}


	private function get_current_students() {

		return ( isset( $_GET['student_ids'] ) ) ? $_GET['student_ids'] : '';

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
			'students' => __( 'Students', 'lifterlms' ),
			'engagements' => __( 'Engagements', 'lifterlms' ),
			'quizzes' => __( 'Quizzes', 'lifterlms' ),

		) );

	}


	private function get_widgets() {

		return array(
			array(
				'enrollments' => array(
					'title' => 'Enrollments',
					'cols' => '1-3',
					'content' => 'loading...',
					'info' => 'Number of total enrollments during the selected period',
				),
				'whatever2' => array(
					'title' => 'test title 2',
					'cols' => '1-3',
					'content' => 'loading...',
					'info' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
				),
				'whatever3' => array(
					'title' => 'test title 3',
					'cols' => '1-3',
					'content' => 'loading...',
					'info' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
				),
			),
			array(
				'whatever' => array(
					'title' => 'test title',
					'cols' => '1-4',
					'content' => 'loading...',
					'info' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
				),
				'whatever2' => array(
					'title' => 'test title 2',
					'cols' => '1-4',
					'content' => 'loading...',
					'info' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
				),
				'whatever3' => array(
					'title' => 'test title',
					'cols' => '1-4',
					'content' => 'loading...',
					'info' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
				),
				'whatever4' => array(
					'title' => 'test title 2',
					'cols' => '1-4',
					'content' => 'loading...',
					'info' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
				),
			),
		);

	}


}
return new LLMS_Analytics_View();
