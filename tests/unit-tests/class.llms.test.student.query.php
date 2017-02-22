<?php
/**
 * Tests for the LLMS_Install Class
 * @since    3.3.1
 * @version  3.3.1
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
	 * @return   void
	 * @since    3.4.0
	 * @version  3.4.0
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

			// test defaults
			unset( $query->query_vars[ $key ] );
			$this->assertEquals( 'default_val', $query->get( $key, 'default_val' ) );

		}

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
