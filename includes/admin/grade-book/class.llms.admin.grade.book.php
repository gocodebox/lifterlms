<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Grade_Book {

	public function __construct() {

		self::includes();

		add_action( 'llms_grade_book_content', array( $this, 'output_current_view' ) );


	}


	public static function includes() {

		include_once LLMS_PLUGIN_DIR . '/includes/abstracts/abstract.llms.admin.gradebook.table.php';

		// include all the table classes
		foreach ( glob( LLMS_PLUGIN_DIR . '/includes/admin/grade-book/tables/*.php' ) as $filename ) {
			include_once $filename;
		}

	}

	private function get_tabs() {
		return apply_filters( 'llms_grade_book_get_tabs', array(
			'students' => __( 'Students', 'lifterlms' ),
			// 'courses' => __( 'Courses', 'lifterlms' ),
		) );
	}


	public static function get_stab_url( $stab ) {

		return add_query_arg( array(
			'page' => 'llms-grade-book',
			'stab' => $stab,
			'student_id' => $_GET['student_id'],
		), admin_url( 'admin.php' ) );

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

	public function output() {

		llms_get_template( 'admin/grade-book/grade-book.php', $this->get_template_data()  );

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
						'current_tab' => isset( $_GET['stab'] ) ? esc_attr( $_GET['stab'] ) : 'courses',
						'tabs' => $tabs,
						'student' => new LLMS_Student( intval( $_GET['student_id'] ) ),
					) );

				}
				// table
				else {

					llms_get_template( 'admin/grade-book/students.php' );

				}


			break;

		}

	}

}

