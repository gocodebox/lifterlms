<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Grade_Book {

	public function __construct() {

		add_action( 'llms_grade_book_content', array( $this, 'output_current_view' ) );

	}

	private function get_tabs() {
		return apply_filters( 'llms_grade_book_get_tabs', array(
			'students' => __( 'Students', 'lifterlms' ),
			'courses' => __( 'Courses', 'lifterlms' ),
		) );
	}

	private function get_current_tab() {
		return isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'students';
	}

	private function get_template_data() {

		return array(
			'current_tab' => $this->get_current_tab(),
			'tabs' => $this->get_tabs(),
		);

	}

	public static function get_student_data( $student, $data ) {

		switch( $data ) {

			case 'achievements':
				$data = count( $student->get_achievements() );
			break;

			case 'certificates':
				$data = count( $student->get_certificates() );
			break;

			case 'completions':

				$courses = $student->get_completed_courses();

				$data = count( $courses['results'] );

			break;

			case 'enrollments':

				$r = 0;

				$page = 1;
				$skip = 0;

				while ( true ) {

					$courses = $student->get_courses( array(
						'limit' => 5000,
						'skip' => 5000 * ( $page - 1 ),
					) );

					$r = $r + count( $courses['results'] );

					if ( ! $courses['more'] ) {
						break;
					} else {
						$page++;
					}

				}

				$data = $r;

			break;

			case 'id':
				$data = $student->get_id();
			break;

			case 'memberships':

				$data = count( $student->get_membership_levels() );

			break;

			case 'name':

				$first = $student->get( 'first_name' );
				$last = $student->get( 'last_name' );

				if ( ! $first || ! $last ) {
					$data = $student->get( 'display_name' );
				} else {
					$data = $last . ', ' . $first;
				}

			break;

			case 'registered':

				$data = $student->get_registration_date();

			break;


		}

		return $data;
	}

	public function output() {

		llms_get_template( 'admin/grade-book/grade-book.php', $this->get_template_data()  );

	}

	public static function get_current_page() {
		return isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
	}

	public static function get_results_per_page() {
		return apply_filters( 'llms_grade_book_results_per_page', 20 );
	}

	private function get_students() {

		$per_page = self::get_results_per_page();
		$current_page = self::get_current_page();

		$args = array(

			'number' => $per_page,
			'offset' => ( $current_page - 1 ) * $per_page,
			'paged' => $current_page,
			'orderby' => isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'name',
			'order' => isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'ASC',

		);

		$q = new WP_User_Query( $args );

		$q->set( 'max_pages', ceil( $q->total_users / $per_page ) );

		return $q;


	}

	public static function get_prev_page_url() {

		return add_query_arg( 'paged', self::get_current_page() - 1 );

	}

	public static function get_next_page_url() {

		return add_query_arg( 'paged', self::get_current_page() + 1 );

	}

	public function output_current_view() {

		switch ( $this->get_current_tab() ) {

			case 'students':

				$table_cols = apply_filters( 'llms_grade_book_students_cols', array(
					'id' => __( 'ID', 'lifterlms' ),
					'name' => __( 'Name', 'lifterlms' ),
					'registered' => __( 'Registration Date', 'lifterlms' ),
					'memberships' => __( 'Memberships', 'lifterlms' ),
					'enrollments' => __( 'Course Enrollments', 'lifterlms' ),
					'completions' => __( 'Course Completions', 'lifterlms' ),
					'certificates' => __( 'Certificates', 'lifterlms' ),
					'achievements' => __( 'Achievements', 'lifterlms' ),
					'active' => __( 'Last Activity', 'lifterlms' ),
				) );

				llms_get_template( 'admin/grade-book/students.php', array(
					'cols' => $table_cols,
					'students' => $this->get_students()
				) );

			break;

		}

	}

}

