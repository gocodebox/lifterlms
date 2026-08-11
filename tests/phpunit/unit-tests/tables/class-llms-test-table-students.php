<?php
/**
 * Test the students reporting table.
 *
 * @package LifterLMS/Tests/Tables
 *
 * @group reporting_tables
 *
 * @since 3.28.0
 */
class LLMS_Test_Table_Students extends LLMS_UnitTestCase {

	/**
	 * Setup test
	 *
	 * @since 3.28.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/reporting/tables/llms.table.students.php';
		$this->table = new LLMS_Table_Students();

	}


	/**
	 * test the get_export() method.
	 *
	 * @since 3.28.0
	 *
	 * @return void
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
	 * @return void
	 * @since   3.28.0
	 * @version 3.28.1
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
	 * Test generate_export_file(): prevent invalid filetypes.
	 *
	 * @since 3.37.15
	 *
	 * @return void
	 */
	public function test_generate_export_file_invalid_file_type() {

		$table = new LLMS_Table_Students();

		// No.
		$this->assertFalse( $table->generate_export_file( array(), 'f.php' ) );

		// Okay.
		$this->assertTrue( is_array( $table->generate_export_file( array(), 'ok.csv' ) ) );
		$this->assertTrue( is_array( $table->generate_export_file( ) ) );

	}

	/**
	 * test the get_results() method.
	 *
	 * @since 3.28.0
	 *
	 * @return void
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
	 * @since 3.28.0
	 *
	 * @return void
	 */
	public function test_set_args() {

		$this->assertEquals( array( 'per_page' => 25 ), $this->table->set_args() );

	}

	/**
	 * Test the set_columns() method
	 *
	 * @since 3.28.0
	 * @since 3.36.0 Add "last_seen" col.
	 *
	 * @return void
	 */
	public function test_set_columns() {

		$cols = $this->table->set_columns();
		$this->assertTrue( is_array( $cols ) );
		$this->assertEquals( 27, count( $cols ) );
		$this->assertEquals( array (
			'id',
			'email',
			'name',
			'name_last',
			'name_first',
			'registered',
			'last_seen',
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
	 * @since 3.28.0
	 *
	 * @return void
	 */
	public function test_variables() {

		$this->assertEquals( 'Students', $this->table->get_title() );
		$this->table->set( 'title', 'Something Else' );
		$this->assertEquals( 'Something Else', $this->table->get_title() );

	}

	/**
	 * Test the last_seen column is rendered in the site's timezone, not UTC.
	 *
	 * Events stored in wp_lifterlms_events.date are UTC MySQL datetimes (gmdate
	 * storage). The reporting column previously fed those strings into
	 * date_i18n(), which produced a UTC display regardless of the site's
	 * timezone setting.
	 *
	 * @since [version]
	 *
	 * @link https://github.com/gocodebox/lifterlms/issues/1738
	 *
	 * @return void
	 */
	public function test_get_data_last_seen_respects_site_timezone() {

		// Fixed UTC timestamp so the expected local time is deterministic.
		$utc_ts           = strtotime( '2024-06-15 12:00:00 UTC' );
		$utc_date_string  = gmdate( 'Y-m-d H:i:s', $utc_ts );

		// Truncate event table so query results are predictable.
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}lifterlms_events" );

		$user_id = $this->factory->user->create();

		$event = new LLMS_Event();
		$event->setUp(
			array(
				'actor_id'     => $user_id,
				'object_type'  => 'post',
				'object_id'    => 1,
				'event_type'   => 'page',
				'event_action' => 'load',
				'date'         => $utc_date_string,
			)
		);
		$event->save();

		// Use the DB-resident event's `date` column directly so we exercise
		// the same string format the production code path receives.
		$stored = (string) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT date FROM {$wpdb->prefix}lifterlms_events WHERE actor_id = %d ORDER BY id DESC LIMIT 1",
				$user_id
			)
		);
		$this->assertSame( $utc_date_string, $stored );

		$student = new LLMS_Student( $user_id );

		$cases = array(
			'12'  => wp_date( get_option( 'date_format' ), strtotime( $utc_date_string . ' UTC' ) + ( 12 * HOUR_IN_SECONDS ) ),
			'-12' => wp_date( get_option( 'date_format' ), strtotime( $utc_date_string . ' UTC' ) - ( 12 * HOUR_IN_SECONDS ) ),
			'0'   => wp_date( get_option( 'date_format' ), strtotime( $utc_date_string . ' UTC' ) ),
		);

		foreach ( $cases as $offset => $expected ) {

			update_option( 'gmt_offset', $offset );
			// Force refresh of the site timezone derived from gmt_offset.
			wp_timezone_override_offset();

			$table = new LLMS_Table_Students();
			$rendered = $table->get_data( 'last_seen', $student );

			$this->assertSame(
				$expected,
				$rendered,
				"last_seen at gmt_offset {$offset} should match expected local date"
			);

		}

		update_option( 'gmt_offset', 0 );

		// Clean up so subsequent tests aren't affected.
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}lifterlms_events" );

	}

}
