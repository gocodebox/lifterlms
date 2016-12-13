<?php
/**
 * Students Tab on Reporting Screen
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Reporting_Tab_Enrollments {

	/**
	 * Constructor
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function __construct() {

		add_action( 'llms_reporting_after_nav', array( $this, 'output_filters' ), 10, 1 );
		add_action( 'llms_reporting_content_enrollments', array( $this, 'output' ) );

	}

	public static function get_filter_data() {

		$data = array();

		$data['current_tab'] = LLMS_Admin_Reporting::get_current_tab();

		$data['current_range'] = LLMS_Admin_Reporting::get_current_range();

		$data['current_students'] = LLMS_Admin_Reporting::get_current_students();

		$data['current_courses'] = LLMS_Admin_Reporting::get_current_courses();

		$data['current_memberships'] = LLMS_Admin_Reporting::get_current_memberships();

		$data['dates'] = LLMS_Admin_Reporting::get_dates( $data['current_range'] );
		$data['date_start'] = $data['dates']['start'];
		$data['date_end'] = $data['dates']['end'];

		return $data;

	}

	/**
	 * Get an array of ajax widgets to load on page load
	 * @return   array
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_widget_data() {
		return apply_filters( 'llms_reporting_tab_enrollments_widgets', array(
			array(
				'enrollments' => array(
					'title' => __( 'Enrollments', 'lifterlms' ),
					'cols' => '1-3',
					'content' => __( 'loading...', 'lifterlms' ),
					'info' => __( 'Number of total enrollments during the selected period', 'lifterlms' ),
				),
			),
		) );
	}

	/**
	 * Outupt the template for the sales tab
	 * @return   void
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function output() {

		llms_get_template( 'admin/reporting/tabs/widgets.php', array(
			'json' => json_encode( self::get_filter_data() ),
			'widget_data' => $this->get_widget_data(),
		) );

	}

	/**
	 * Outupt filters navigation
	 * @return   void
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function output_filters( $tab ) {

		if ( 'enrollments' === $tab ) {

			llms_get_template( 'admin/reporting/nav-filters.php', self::get_filter_data() );

		}

	}

}
return new LLMS_Admin_Reporting_Tab_Enrollments();
