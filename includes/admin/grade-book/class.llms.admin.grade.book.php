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

	public static function get_lesson_data( $lesson, $student, $data ) {

		switch ( $data ) {

			case 'completion':
				$date = $student->get_completion_date( $lesson->get( 'id' ) );
				$data = $date ? $date : '&ndash;';
			break;

			case 'grade':
				$grade = $student->get_grade( $lesson->get( 'id' ) );
				$data  = is_numeric( $grade ) ? $grade . '%' : $grade;
			break;

			case 'id':
				$data = '<a href="' . esc_url( get_edit_post_link( $lesson->get( 'id' ) ) ) . '">' . $lesson->get( 'id' ) . '</a>';
			break;

			case 'name':
				$data = $lesson->get( 'title' );
			break;

			case 'quiz':

				$q = $lesson->get( 'assigned_quiz' );

				if ( $q ) {

					$url = esc_url( add_query_arg( 'quiz_id', $q ) );
					$data = '<a href="' . $url . '">' . get_the_title( $q ) . '</a>';

				} else {

					$data = '&ndash;';

				}

			break;



		}

		return $data;

	}

	public static function get_course_data( $course, $student, $data ) {

		switch ( $data ) {

			case 'progress':
				$data = $student->get_progress( $course->get( 'id' ), 'course' ) . '%';
			break;

			case 'completed':
				$date = $student->get_completion_date( $course->get( 'id' ) );
				$data = $date ? $date : '&ndash;';
			break;

			case 'grade':

				$grade = $student->get_grade( $course->get( 'id' ) );

				$data  = is_numeric( $grade ) ? $grade . '%' : $grade;

			break;

			case 'id':
				$data = '<a href="' . esc_url( get_edit_post_link( $course->get( 'id' ) ) ) . '">' . $course->get( 'id' ) . '</a>';
			break;

			case 'name':
				$url = esc_url( add_query_arg( 'course_id', $course->get( 'id' ) ) );
				$data = '<a href="' . $url . '">' . $course->get( 'title' ) . '</a>';
			break;



		}

		return $data;

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
				$data = '<a href="' . esc_url( get_edit_user_link( $student->get_id() ) ) . '">' . $student->get_id() . '</a>';
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

				$url = esc_url( add_query_arg( 'student_id', $student->get_id(), admin_url( 'admin.php?page=llms-grade-book' ) ) );
				$data = '<a href="' . $url . '">' . $data . '</a>';

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
		return apply_filters( 'llms_grade_book_results_per_page', 30 );
	}

	private function get_students() {

		$per_page = self::get_results_per_page();
		$current_page = self::get_current_page();

		$args = array(

			'number' => $per_page,
			'offset' => ( $current_page - 1 ) * $per_page,
			'paged' => $current_page,
			// 'orderby' => isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'name',
			'order' => isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'ASC',

		);

		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'name';

		switch ( $orderby ) {

			case 'name':

				$args = array_merge( $args, array(
					'meta_key' => 'last_name',
					'orderby' => 'meta_value',
				) );

			break;

			case 'registered':

				$args = array_merge( $args, array(
					'orderby' => 'registered',
				) );

			break;

		}

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

				// single student
				if ( isset( $_GET['student_id'] ) ) {

					$tabs = apply_filters( 'llms_grade_book_student_tabs', array(
						'courses' => __( 'Courses', 'lifterlms' ),
						'information' => __( 'Information', 'lifterlms' ),
						'achievements' => __( 'Achievements', 'lifterlms' ),
					) );

					llms_get_template( 'admin/grade-book/student.php', array(
						'tabs' => $tabs,
						'student' => new LLMS_Student( intval( $_GET['student_id'] ) ),
					) );

				}
				// table
				else {

					$table_cols = apply_filters( 'llms_grade_book_students_cols', array(
						'id' => array(
							'sortable' => false,
							'title' => __( 'ID', 'lifterlms' ),
						),
						'name' => array(
							'sortable' => true,
							'title' => __( 'Name', 'lifterlms' ),
						),
						'registered' => array(
							'sortable' => true,
							'title' => __( 'Registration Date', 'lifterlms' ),
						),
						'memberships' => array(
							'sortable' => false,
							'title' => __( 'Memberships', 'lifterlms' ),
						),
						'enrollments' => array(
							'sortable' => false,
							'title' => __( 'Course Enrollments', 'lifterlms' ),
						),
						'completions' => array(
							'sortable' => false,
							'title' => __( 'Course Completions', 'lifterlms' ),
						),
						'certificates' => array(
							'sortable' => false,
							'title' => __( 'Certificates', 'lifterlms' ),
						),
						'achievements' => array(
							'sortable' => false,
							'title' => __( 'Achievements', 'lifterlms' ),
						),
						'active' => array(
							'sortable' => false,
							'title' => __( 'Last Activity', 'lifterlms' ),
						),
					) );

					llms_get_template( 'admin/grade-book/students.php', array(
						'cols' => $table_cols,
						'students' => $this->get_students()
					) );

				}


			break;

		}

	}

}

