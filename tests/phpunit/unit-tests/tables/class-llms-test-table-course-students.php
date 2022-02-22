<?php
/**
 * Test the course students reporting table.
 *
 * @package LifterLMS/Tests/Tables
 *
 * @group reporting_tables
 *
 * @since [version]
 */
class LLMS_Test_Table_Course_Students extends LLMS_UnitTestCase {
	/**
	 * The course ID used in tests.
	 *
	 * @since [version]
	 *
	 * @var int
	 */
	private $course_id;

	/**
	 * An instance of LLMS_Table_Course_Students to run tests on.
	 *
	 * @since [version]
	 *
	 * @var LLMS_Table_Course_Students
	 */
	private $table;

	/**
	 * Executes a student query and asserts that the resulting students match the given expected students.
	 *
	 * @since [version]
	 *
	 * @param array $args          Arguments to pass to {@see LLMS_Table_Course_Students::get_results()}.
	 * @param array $expected_data The expected array of data.
	 * @return void
	 */
	private function assert_student_results_equal( $args, $expected_data ) {

		$this->table->get_results( $args );
		$actual_students = $this->table->get_tbody_data();

		$actual_data = array();
		/** @var LLMS_Student[] $actual_students */
		foreach ( $actual_students as $student ) {
			switch ( $args['orderby'] ) {
				case 'completed':
					$actual_data[ $student->get( 'id' ) ] = $student->get_completion_date( $this->course_id, 'U' );
					break;
				case 'enrolled':
					$actual_data[ $student->get( 'id' ) ] = $student->get_enrollment_date( $this->course_id, 'updated', 'U' );
					break;
				default:
					$actual_data[ $student->get( 'id' ) ] = $this->table->get_data( $args['orderby'], $student );
			}
		}

		$this->assertEquals( $expected_data, $actual_data );
	}

	/**
	 * Setup test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/reporting/tables/llms.table.course.students.php';
		$this->table = new LLMS_Table_Course_Students();
	}

	/**
	 * Test the generate_export_file() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_generate_export_file() {

		// Create a course.
		$course = $this->factory->course->create_and_get();
		$args   = array( 'course_id' => $course->get( 'id' ) );

		// Enroll a bunch of students.
		$this->factory->student->create_and_enroll_many( 50, $course->get( 'id' ) );

		// Setup an instructor.
		$instructor_id = $this->factory->instructor->create();
		$course->instructors()->set_instructors( array( array( 'id' => $instructor_id ) ) );
		wp_set_current_user( $instructor_id );

		// Unboost to make testing faster.
		add_filter( 'llms_table_generate_export_file_per_page_boost', function () {
			return 25;
		} );

		$file = $this->table->generate_export_file( $args );

		$this->assertTrue( file_exists( LLMS_TMP_DIR . $file['filename'] ) );
		$this->assertEquals( 50, $file['progress'] );

		$file = $this->table->generate_export_file( $args, $file['filename'] );
		$this->assertEquals( 100, $file['progress'] );
	}

	/**
	 * Test generate_export_file(): prevent invalid filetypes.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_generate_export_file_invalid_file_type() {

		$args = array( 'course_id' => $this->factory->course->create() );

		// No.
		$this->assertFalse( $this->table->generate_export_file( $args, 'f.php' ) );

		// Okay.
		$this->assertTrue( is_array( $this->table->generate_export_file( $args, 'ok.csv' ) ) );
		$this->assertTrue( is_array( $this->table->generate_export_file( $args ) ) );
	}

	/**
	 * Test the get_export() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_export() {

		$this->course_id = $this->factory->course->create();
		$args            = array( 'course_id' => $this->course_id );

		// Enroll a bunch of students.
		$this->factory->student->create_and_enroll_many( 10, $this->course_id );

		// Setup an admin user
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$export = $this->table->get_export( $args );
		$this->assertTrue( count( $export ) >= 11 );
		$this->assertEquals( $this->table->get_export_header(), $export[0] );
	}

	/**
	 * Test the get_results() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_results() {

		/** {@see LLMS_Table_Course_Students::get_results()} order by 'name' uses the user's last name. */
		$this->factory->student->default_generation_definitions['first_name'] = 'Student';
		$this->factory->student->default_generation_definitions['last_name']  = new WP_UnitTest_Generator_Sequence( '%s' );

		// Test as an administrator.
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		// Create a course with one lesson.
		$course                 = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1 ) );
		$this->course_id        = $course->get( 'id' );
		$lessons                = $course->get_lessons();
		$lesson_id              = reset( $lessons )->get( 'id' );
		$this->table->course_id = $this->course_id;

		$student_completed_dates = array();
		$student_enrolled_dates  = array();
		$student_ids             = array();
		$student_names           = array();
		$student_statuses        = array();
		for ( $i = 0; $i < 10; $i ++ ) {
			// Enroll each student at a different time.
			llms_tests_mock_current_time( '2022-03-' . str_pad( $i + 1, 2, '0', STR_PAD_LEFT ) );
			$student = $this->factory->student->create_and_get();
			$student->enroll( $this->course_id );
			$student_id = $student->get( 'id' );

			if ( $student_id % 2 ) {
				// Students with odd numbered IDs have completed the course in reverse order.
				$student->mark_complete( $lesson_id, 'lesson' );
				llms_tests_mock_current_time( "now - $student_id days" );
			} else {
				// Students with odd numbered IDs have expired enrollment.
				$student->unenroll( $this->course_id );
			}

			// Gather expected data.
			$student_completed_dates[ $student_id ] = $student->get_completion_date( $this->course_id, 'U' );
			$student_enrolled_dates[ $student_id ]  = $student->get_enrollment_date( $this->course_id, 'updated', 'U' );
			$student_ids[ $student_id ]             = $this->table->get_data( 'id', $student );
			$student_names[ $student_id ]           = $this->table->get_data( 'name', $student );
			$student_statuses[ $student_id ]        = $this->table->get_data( 'status', $student );
		}
		asort( $student_completed_dates );
		asort( $student_statuses );

		// Default arguments.
		$args = array(
			'course_id' => $this->course_id,
			'filter'    => 'any',
			'filterby'  => 'status',
			'page'      => 1,
			'search'    => '',
		);

		// Results ordered by ascending completed date.
		$args['order']   = 'ASC';
		$args['orderby'] = 'completed';
		$expected        = $student_completed_dates;
		$this->assert_student_results_equal( $args, $expected );

		// Results ordered by descending completed date.
		$args['order']   = 'DESC';
		$args['orderby'] = 'completed';
		$expected        = $student_completed_dates;
		arsort( $expected );
		$this->assert_student_results_equal( $args, $expected );

		// Results ordered by ascending enrolled date.
		$args['order']   = 'ASC';
		$args['orderby'] = 'enrolled';
		$expected        = $student_enrolled_dates;
		$this->assert_student_results_equal( $args, $expected );

		// Results ordered by descending enrolled date.
		$args['order']   = 'DESC';
		$args['orderby'] = 'enrolled';
		$expected        = $student_enrolled_dates;
		arsort( $expected );
		$this->assert_student_results_equal( $args, $expected );

		// Results ordered by ascending ID.
		$args['order']   = 'ASC';
		$args['orderby'] = 'id';
		$expected        = $student_ids;
		$this->assert_student_results_equal( $args, $expected );

		// Results ordered by descending ID.
		$args['order']   = 'DESC';
		$args['orderby'] = 'id';
		$expected        = $student_ids;
		arsort( $expected );
		$this->assert_student_results_equal( $args, $expected );

		// Results ordered by ascending name.
		$args['order']   = 'ASC';
		$args['orderby'] = 'name';
		$expected        = $student_names;
		$this->assert_student_results_equal( $args, $expected );

		// Results ordered by descending name.
		$args['order']   = 'DESC';
		$args['orderby'] = 'name';
		$expected        = $student_names;
		arsort( $expected );
		$this->assert_student_results_equal( $args, $expected );

		// Results ordered by ascending status.
		$args['order']   = 'ASC';
		$args['orderby'] = 'status';
		$expected        = $student_statuses;
		$this->assert_student_results_equal( $args, $expected );

		// Results ordered by descending status.
		$args['order']   = 'DESC';
		$args['orderby'] = 'status';
		$expected        = $student_statuses;
		arsort( $expected );
		$this->assert_student_results_equal( $args, $expected );
	}

	/**
	 * Test the set_args() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_set_args() {

		$_GET['course_id'] = 123;
		$this->assertEquals( array( 'course_id' => 123 ), $this->table->set_args() );
	}

	/**
	 * Test the set_columns() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_set_columns() {

		$cols = $this->table->set_columns();
		$this->assertTrue( is_array( $cols ) );
		$this->assertEquals( 11, count( $cols ) );
		$this->assertEquals( array(
			'id',
			'name',
			'name_last',
			'name_first',
			'email',
			'status',
			'enrolled',
			'completed',
			'progress',
			'grade',
			'last_lesson',
		), array_keys( $cols ) );
	}
}
