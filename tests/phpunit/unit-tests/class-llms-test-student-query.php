<?php
/**
 * Tests for the LLMS_Install Class
 *
 * @group student_query
 *
 * @since 3.3.1
 */
class LLMS_Test_Student_Query extends LLMS_UnitTestCase {

	/**
	 * Create a new query for use in these tests
	 * @param    array      $args  args to pass to the query
	 * @return   obj
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	private function query( $args = array() ) {
		return new LLMS_Student_Query( $args );
	}

	/**
	 * Test get() and set() functions
	 *
	 * @since 3.4.0
	 * @since 6.0.0 Don't access `LLMS_Student_Query` properties directly.
	 *
	 * @return void
	 */
	public function test_getters_setters() {

		$args = array(
			'page' => 2,
			'per_page' => 25,
			'post_id' => 1234,
			'search' => 'a search string',
			'sort' => array(
				'id' => 'ASC',
			),
			'suppress_filters' => true,
			'statuses' => array(
				'enrolled', 'expired'
			),
		);

		$query = $this->query();

		foreach ( $args as $key => $val ) {

			$query->set( $key, $val );
			$this->assertEquals( $args[ $key ], $query->get( $key ) );

			// Test defaults.
			LLMS_Unit_Test_Util::set_private_property( $query, 'query_vars', array() );
			$this->assertEquals( 'default_val', $query->get( $key, 'default_val' ) );

		}

	}

	/**
	 * Test some real queries
	 *
	 * @since 3.13.0
	 * @since 6.0.0 Don't access `LLMS_Student_Query` properties directly.
	 *
	 * @return void
	 */
	public function test_get_students() {

		$course_id = $this->generate_mock_courses( 1, 1, 1, 0 )[0];

		$students = $this->factory->user->create_many( 25, array( 'role' => 'student' ) );
		foreach ( $students as $sid ) {
			llms_enroll_student( $sid, $course_id, 'testing' );
		}

		// 25 students enrolled
		$query = $this->query( array(
			'post_id' => $course_id,
			'per_page' => 10,
		) );

		$this->assertEquals( 25, $query->get_found_results() );
		$this->assertEquals( 10, $query->get_number_results() );
		$this->assertEquals( 3, $query->get_max_pages() );

		sleep( 1 ); // sleep because timestamps can't be the same for the next queries to work correctly

		// unenroll 10 students & results should stay the same
		foreach ( $query->get_students() as $student ) {
			$student->unenroll( $course_id, 'testing' );
		}

		// check for expired from any courses
		$query = $this->query( array(
			'per_page' => 10,
			'statuses' => 'expired',
		) );
		$this->assertEquals( 10, $query->get_found_results() );

		// check for any status again
		$query = $this->query( array(
			'post_id' => $course_id,
			'per_page' => 10,
		) );
		$this->assertEquals( 25, $query->get_found_results() );
		$this->assertEquals( 10, $query->get_number_results() );
		$this->assertEquals( 3, $query->get_max_pages() );

		// check for enrolled only
		$query = $this->query( array(
			'post_id' => $course_id,
			'per_page' => 10,
			'statuses' => 'enrolled',
		) );
		$this->assertEquals( 15, $query->get_found_results() );
		$this->assertEquals( 10, $query->get_number_results() );
		$this->assertEquals( 2, $query->get_max_pages() );


		// second course
		$course_id2 = $this->generate_mock_courses( 1, 1, 1, 0 )[0];
		$students2 = $this->factory->user->create_many( 25, array( 'role' => 'student' ) );
		foreach ( array_merge( $students, $students2 ) as $sid ) {
			llms_enroll_student( $sid, $course_id2, 'testing' );
		}

		// check for enrolled only
		$query = $this->query( array(
			'post_id' => array( $course_id, $course_id2 ),
			'per_page' => 10,
			// 'statuses' => 'enrolled',
		) );
		$this->assertEquals( 50, $query->get_found_results() );
		$this->assertEquals( 10, $query->get_number_results() );
		$this->assertEquals( 5, $query->get_max_pages() );

		// more students who aren't enrolled
		$students3 = $this->factory->user->create_many( 25, array( 'role' => 'student' ) );

		// anything in any course
		$query = $this->query( array(
			'per_page' => 10,
		) );
		$this->assertEquals( 50, $query->get_found_results() );

		// cancelled in any course (shouldn't have anything here)
		$query = $this->query( array(
			'per_page' => 10,
			'statuses' => 'cancelled',
		) );
		$this->assertEquals( 0, $query->get_found_results() );


		// test some searches
		$query = $this->query( array(
			'search' => 'No Results Found Plz'
		) );
		$this->assertEquals( 0, $query->get_found_results() );

		// should hit all the mock users
		$query = $this->query( array(
			'search' => 'user_'
		) );
		$this->assertEquals( 50, $query->get_found_results() );


		update_user_meta( $students2[5], 'first_name', 'testymcname' );
		$query = $this->query( array(
			'search' => 'testymcname'
		) );
		$this->assertEquals( 1, $query->get_found_results() );
		$this->assertEquals( $students2[5], $query->get_students()[0]->get_id() );

	}

	/**
	 * Test the parse_setup_args() function
	 * @return   void
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	public function test_parse_setup_args() {

		$query = $this->query();
		$this->assertEquals( array_keys( llms_get_enrollment_statuses() ), $query->get( 'statuses' ) );

		// ensure valid string is converted to array
		$query = $this->query( array( 'statuses' => 'enrolled' ) );
		$this->assertEquals( array( 'enrolled' ), $query->get( 'statuses' ) );

		// ensure invalid status is removed
		$query = $this->query( array( 'statuses' => array( 'ooboi', 'enrolled' ) ) );
		$this->assertFalse( in_array( 'ooboi', $query->get( 'statuses' ) ) );

		// ensure at least one status is returned
		$query = $this->query( array( 'statuses' => array( 'ooboi', 'fake' ) ) );
		$this->assertGreaterThanOrEqual( 1, count( $query->get( 'statuses' ) ) );

	}

}
