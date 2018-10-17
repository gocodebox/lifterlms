<?php

class LLMS_Dashboard_Activity_Widget {

	public function __construct() {
		add_action('wp_dashboard_setup', array( $this, 'register_widget' ) );
	}

	public function register_widget() {

		wp_add_dashboard_widget(
			'llms-activity-widget',
			'LifterLMS Activity',
			array( $this, 'render_widget' )
		);

	}

	public function render_widget() {

		// Get sections and also remove false, null, empty sections
		$sections = array_filter( $this->get_sections() );
		$html = '<table class="wp-list-table widefat striped posts"><thead><tbody>';

		foreach( $sections as $section_key => $section ) {
			$html .= sprintf( '<tr class="llms-dashboard-widget-section %s">', $section_key );
			$html .= sprintf( '<td class="section-title">%s</td>', esc_html( $section['title'] ) );
			$html .= sprintf( '<td class="section-value">%s</td>', is_callable( $section['callback'] ) ? call_user_func( $section['callback'] ) : 0 );
			$html .= '</tr>';
		}

		$html .= '<tbody></table>';

		echo $html;

	}

	public function get_sections() {

		return apply_filters( 'llms_dashboard_widget_activity_sections', array(
			'total-students' => array(
				'title'     => __( 'Total Students', 'lifterlms' ),
				'callback'  => array( $this, 'get_student_count' )
			),
			'enrollments' => array(
				'title'     => __( 'Enrollments (Current Month)', 'lifterlms' ),
				'callback'  => array( $this, 'get_enrollment_count' )
			),
			'revenue' => array(
				'title'     => __( 'Revenue (Current Month)', 'lifterlms' ),
				'callback'  => array( $this, 'get_revenue_count' )
			),
			'top-courses' => array(
				'title'     => __( 'Top Courses (Current Month)', 'lifterlms' ),
				'callback'  => array( $this, 'get_top_courses' )
			),
			'top-memberships' => array(
				'title'     => __( 'Top Memberships (Current Month)', 'lifterlms' ),
				'callback'  => array( $this, 'get_top_memberships' )
			),
			'lessons-completed' => array(
				'title'     => __( 'Lesson Completed (Current Month)', 'lifterlms' ),
				'callback'  => array( $this, 'get_lessons_completed_count' )
			),
			'quiz-passed' => array(
				'title'     => __( 'Quizzes Passed (Current Month)', 'lifterlms' ),
				'callback'  => array( $this, 'get_quizzes_passed_count' )
			),
			'quiz-failed' => array(
				'title'     => __( 'Quizzes Failed (Current Month)', 'lifterlms' ),
				'callback'  => array( $this, 'get_quizzes_failed_count' )
			),
			'quiz-pass-fail-ratio' => array(
				'title'     => __( 'Quizzes Pass/Fail Ratio', 'lifterlms' ),
				'callback'  => array( $this, 'get_quizzes_passed_fail_ratio' )
			),

		) );

	}

	public function get_student_count() {
		return 5;
	}


}

new LLMS_Dashboard_Activity_Widget();