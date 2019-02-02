<?php
/**
 * Test the students reporting table.
 *
 * @package  LifterLMS/Tests/Tables
 * @group    reporting_tables
 * @since    3.28.0
 * @version  [version]
 */
class LLMS_Test_Table_Students extends LLMS_UnitTestCase {

	/**
	 * Setup test
	 * @since   3.28.0
	 * @version 3.28.0
	 */
	public function setUp() {

		parent::setUp();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/reporting/tables/llms.table.students.php';
		$this->table = new LLMS_Table_Students();

	}


	/**
	 * test the get_export() method.
	 *
	 * @return  void
	 * @since   3.28.0
	 * @version 3.28.0
	 */
	public function test_get_export() {

		// Enroll a bunch of students.
		$this->factory->student->create_and_enroll_many( 10, $this->factory->course->create() );

		// Setup an admin user
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$table = new LLMS_Table_Students();
		$export = $table->get_export();
		$this->assertTrue( count( $export ) >= 11 );
		$this->assertEquals( $table->get_export_header(), $export[0] );

	}

	/**
	 * test the generate_export_file() method.
	 *
	 * @return  void
	 * @since   3.28.0
	 * @version [version]
	 */
	public function test_generate_export_file() {

		// Create a course.
		$course = $this->factory->course->create_and_get();

		// Enroll a bunch of students.
		$this->factory->student->create_and_enroll_many( 50, $course->get( 'id' ) );

		// Setup an instructor.
		$instructor_id = $this->factory->instructor->create();
		$course->instructors()->set_instructors( array( array( 'id' => $instructor_id ) ) );
		wp_set_current_user( $instructor_id );

		// unboost to make testing faster.
		add_filter( 'llms_table_generate_export_file_per_page_boost', function() {
			return 25;
		} );

		$table = new LLMS_Table_Students();
		$file = $table->generate_export_file();

		$this->assertTrue( file_exists( LLMS_TMP_DIR . $file['filename'] ) );
		$this->assertEquals( 50, $file['progress'] );

		$file = $table->generate_export_file( array(), $file['filename'] );
		$this->assertEquals( 100, $file['progress'] );

	}

	/**
	 * test the get_results() method.
	 *
	 * @return  void
	 * @since   3.28.0
	 * @version 3.28.0
	 */
	public function test_get_results() {

		$checks = array(
			array(
				'key' => 'page',
				'func' => 'get_current_page',
				'default' => 1,
				'change' => 2,
			),
			array(
				'key' => 'order',
				'func' => 'get_order',
				'default' => 'ASC',
				'change' => 'DESC',
			),
			array(
				'key' => 'orderby',
				'func' => 'get_orderby',
				'default' => 'name',
				'change' => 'id',
			),
			array(
				'key' => 'per_page',
				'func' => 'get_per_page',
				'default' => 25,
				'change' => 5,
			),
		);

		$result_args = wp_list_pluck( $checks, 'change', 'key' );

		// Setup course.
		$course = $this->factory->course->create_and_get();

		// Enroll a bunch of students.
		$this->factory->student->create_and_enroll_many( 10, $course->get( 'id' ) );

		// Current user has no access to anything.
		$table = new LLMS_Table_Students();
		$table->get_results();
		$this->assertEmpty( $table->get_tbody_data() );
		foreach ( $checks as $data ) {
			$this->assertEquals( $data['default'], $table->{ $data['func'] }() );
		}
		$table->get_results( $result_args );
		foreach ( $checks as $data ) {
			$this->assertEquals( $data['default'], $table->{ $data['func'] }() );
		}

		// Setup an instructor.
		$instructor_id = $this->factory->instructor->create();
		$course->instructors()->set_instructors( array( array( 'id' => $instructor_id ) ) );

		wp_set_current_user( $instructor_id );
		$table = new LLMS_Table_Students();
		$table->get_results();
		$this->assertEquals( 10, count( $table->get_tbody_data() ) );
		foreach ( $checks as $data ) {
			$this->assertEquals( $data['default'], $table->{ $data['func'] }() );
		}
		$table->get_results( $result_args );
		foreach ( $checks as $data ) {
			$this->assertEquals( $data['change'], $table->{ $data['func'] }() );
		}
		$this->assertEquals( 2, $table->get_max_pages() );
		$this->assertTrue( $table->is_last_page() );

		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		$table = new LLMS_Table_Students();
		$table->get_results();
		$this->assertTrue( count( $table->get_tbody_data() ) >= 10 );
		foreach ( $checks as $data ) {
			$this->assertEquals( $data['default'], $table->{ $data['func'] }() );
		}
		$table->get_results( $result_args );
		foreach ( $checks as $data ) {
			$this->assertEquals( $data['change'], $table->{ $data['func'] }() );
		}
		$this->assertTrue( $table->get_max_pages() >= 2 );

	}

	/**
	 * Test the set_args() method.
	 *
	 * @return  void
	 * @since   3.28.0
	 * @version 3.28.0
	 */
	public function test_set_args() {

		$this->assertEquals( array( 'per_page' => 25 ), $this->table->set_args() );

	}

	/**
	 * Test the set_columns() method
	 * @return  [type]
	 * @since   3.28.0
	 * @version 3.28.0
	 */
	public function test_set_columns() {

		$cols = $this->table->set_columns();
		$this->assertTrue( is_array( $cols ) );
		$this->assertEquals( 26, count( $cols ) );
		$this->assertEquals( array (
			'id',
			'email',
			'name',
			'name_last',
			'name_first',
			'registered',
			'overall_progress',
			'overall_grade',
			'enrollments',
			'completions',
			'certificates',
			'achievements',
			'memberships',
			'billing_address_1',
			'billing_address_2',
			'billing_city',
			'billing_state',
			'billing_zip',
			'billing_country',
			'phone',
			'courses_enrolled',
			'courses_cancelled',
			'courses_expired',
			'memberships_enrolled',
			'memberships_cancelled',
			'memberships_expired',
		), array_keys( $cols ) );

	}

	/**
	 * Test that variables are setup correctly during construction.
	 *
	 * @return  void
	 * @since   3.28.0
	 * @version 3.28.0
	 */
	public function test_variables() {

		$this->assertEquals( 'Students', $this->table->get_title() );
		$this->table->set( 'title', 'Something Else' );
		$this->assertEquals( 'Something Else', $this->table->get_title() );

	}

}
