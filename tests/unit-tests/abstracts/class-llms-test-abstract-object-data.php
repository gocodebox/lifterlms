<?php
/**
 * Test LLMS_Abstract_Object_Data
 *
 * @package LifterLMS_Tests/Abstracts
 *
 * @group abstracts
 * @group object_data
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Abstract_Object_Data extends LLMS_Unit_Test_Case {

	/**
	 * Get a mocked stub of an object extending the LLMS_Abstract_Object_Data class
	 *
	 * @since [version]
	 *
	 * @param int $id an int.
	 * @return obj
	 */
	public function get_stub( $id = 123 ) {

		return new class( $id ) extends LLMS_Abstract_Object_Data {

			protected function set_object( $id ) {
				$obj = new stdClass();
				$obj->id = $id;
				return $obj;
			}

		};

	}

	/**
	 * Test constructor
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_object() {

		$stub = $this->get_stub( 1 );

		$expected = new stdClass();
		$expected->id = 1;

		$this->assertEquals( $expected, $stub->get_object() );

	}

	/**
	 * Test constructor
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_object_id() {

		$stub = $this->get_stub( 2 );

		$this->assertEquals( 2, $stub->get_object_id() );

	}

	/**
	 * Test setting the period for "all_time"
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_set_period_all_time() {

		$stub = $this->get_stub();

		llms_tests_mock_current_time( '2019-05-06 13:14' );

		$stub->set_period( 'all_time' );

		$this->assertEquals( '1970-01-01 00:00:00', $stub->get_date( 'current', 'start' ) );
		$this->assertEquals( '2019-05-06 23:59:59', $stub->get_date( 'current', 'end' ) );

		$this->assertEquals( '1970-01-01 00:00:00', $stub->get_date( 'previous', 'start' ) );
		$this->assertEquals( '2019-05-06 23:59:59', $stub->get_date( 'previous', 'end' ) );

		llms_tests_mock_current_time( '2019-12-31 23:59:59' );

		$stub->set_period( 'all_time' );

		$this->assertEquals( '1970-01-01 00:00:00', $stub->get_date( 'current', 'start' ) );
		$this->assertEquals( '2019-12-31 23:59:59', $stub->get_date( 'current', 'end' ) );

		$this->assertEquals( '1970-01-01 00:00:00', $stub->get_date( 'previous', 'start' ) );
		$this->assertEquals( '2019-12-31 23:59:59', $stub->get_date( 'previous', 'end' ) );


		llms_tests_mock_current_time( '2019-01-01 00:00:00' );

		$stub->set_period( 'all_time' );

		$this->assertEquals( '1970-01-01 00:00:00', $stub->get_date( 'current', 'start' ) );
		$this->assertEquals( '2019-01-01 23:59:59', $stub->get_date( 'current', 'end' ) );

		$this->assertEquals( '1970-01-01 00:00:00', $stub->get_date( 'previous', 'start' ) );
		$this->assertEquals( '2019-01-01 23:59:59', $stub->get_date( 'previous', 'end' ) );

	}

	/**
	 * Test setting the period for "last_year"
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_set_period_last_year() {

		$stub = $this->get_stub();
		llms_tests_mock_current_time( '2019-02-25 01:01:35' );

		$stub->set_period( 'last_year' );

		$this->assertEquals( '2018-01-01 00:00:00', $stub->get_date( 'current', 'start' ) );
		$this->assertEquals( '2018-12-31 23:59:59', $stub->get_date( 'current', 'end' ) );

		$this->assertEquals( '2017-01-01 00:00:00', $stub->get_date( 'previous', 'start' ) );
		$this->assertEquals( '2017-12-31 23:59:59', $stub->get_date( 'previous', 'end' ) );

	}

	/**
	 * Test setting the period for "year"
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_set_period_year() {

		$stub = $this->get_stub();
		llms_tests_mock_current_time( '2015-08-01' );

		$stub->set_period( 'year' );

		$this->assertEquals( '2015-01-01 00:00:00', $stub->get_date( 'current', 'start' ) );
		$this->assertEquals( '2015-12-31 23:59:59', $stub->get_date( 'current', 'end' ) );

		$this->assertEquals( '2014-01-01 00:00:00', $stub->get_date( 'previous', 'start' ) );
		$this->assertEquals( '2014-12-31 23:59:59', $stub->get_date( 'previous', 'end' ) );

	}

	/**
	 * Test setting the period for "last_month"
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_set_period_last_month() {

		$stub = $this->get_stub();
		llms_tests_mock_current_time( '2018-01-31' );

		$stub->set_period( 'last_month' );

		$this->assertEquals( '2017-12-01 00:00:00', $stub->get_date( 'current', 'start' ) );
		$this->assertEquals( '2017-12-31 23:59:59', $stub->get_date( 'current', 'end' ) );

		$this->assertEquals( '2017-11-01 00:00:00', $stub->get_date( 'previous', 'start' ) );
		$this->assertEquals( '2017-11-30 23:59:59', $stub->get_date( 'previous', 'end' ) );

	}

	/**
	 * Test setting the period for "month"
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_set_period_month() {

		$stub = $this->get_stub();
		llms_tests_mock_current_time( '2016-11-15' );

		$stub->set_period( 'month' );

		$this->assertEquals( '2016-11-01 00:00:00', $stub->get_date( 'current', 'start' ) );
		$this->assertEquals( '2016-11-30 23:59:59', $stub->get_date( 'current', 'end' ) );

		$this->assertEquals( '2016-10-01 00:00:00', $stub->get_date( 'previous', 'start' ) );
		$this->assertEquals( '2016-10-31 23:59:59', $stub->get_date( 'previous', 'end' ) );

	}

	/**
	 * Test setting the period for "last_week"
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_set_period_last_week() {

		$stub = $this->get_stub();
		llms_tests_mock_current_time( '2019-07-05' );

		$stub->set_period( 'last_week' );

		$this->assertEquals( '2019-06-24 00:00:00', $stub->get_date( 'current', 'start' ) );
		$this->assertEquals( '2019-06-30 23:59:59', $stub->get_date( 'current', 'end' ) );

		$this->assertEquals( '2019-06-17 00:00:00', $stub->get_date( 'previous', 'start' ) );
		$this->assertEquals( '2019-06-23 23:59:59', $stub->get_date( 'previous', 'end' ) );

	}

	/**
	 * Test setting the period for "week"
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_set_period_week() {

		$stub = $this->get_stub();
		llms_tests_mock_current_time( '2019-07-05' );

		$stub->set_period( 'week' );

		$this->assertEquals( '2019-07-01 00:00:00', $stub->get_date( 'current', 'start' ) );
		$this->assertEquals( '2019-07-07 23:59:59', $stub->get_date( 'current', 'end' ) );

		$this->assertEquals( '2019-06-24 00:00:00', $stub->get_date( 'previous', 'start' ) );
		$this->assertEquals( '2019-06-30 23:59:59', $stub->get_date( 'previous', 'end' ) );

	}

	/**
	 * Test setting the period for "yesterday"
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_set_period_yesterday() {

		$stub = $this->get_stub();
		llms_tests_mock_current_time( '2025-03-02' );

		$stub->set_period( 'yesterday' );

		$this->assertEquals( '2025-03-01 00:00:00', $stub->get_date( 'current', 'start' ) );
		$this->assertEquals( '2025-03-01 23:59:59', $stub->get_date( 'current', 'end' ) );

		$this->assertEquals( '2025-02-28 00:00:00', $stub->get_date( 'previous', 'start' ) );
		$this->assertEquals( '2025-02-28 23:59:59', $stub->get_date( 'previous', 'end' ) );

	}

	/**
	 * Test setting the period for "today"
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_set_period_today() {

		$stub = $this->get_stub();
		llms_tests_mock_current_time( '2012-12-03 23:32:12' );

		$stub->set_period( 'today' );

		$this->assertEquals( '2012-12-03 00:00:00', $stub->get_date( 'current', 'start' ) );
		$this->assertEquals( '2012-12-03 23:59:59', $stub->get_date( 'current', 'end' ) );

		$this->assertEquals( '2012-12-02 00:00:00', $stub->get_date( 'previous', 'start' ) );
		$this->assertEquals( '2012-12-02 23:59:59', $stub->get_date( 'previous', 'end' ) );

	}

}
