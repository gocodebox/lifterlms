<?php
/**
 * Tests {@see LLMS_Abstract_Exportable_Admin_Table}.
 *
 * @package LifterLMS/Tests/Abstracts
 *
 * @group abstracts
 * @group admin_tables
 *
 * @since [version]
 */
class LLMS_Test_Abstract_Exportable_Admin_Table extends LLMS_UnitTestCase {

	/**
	 * Retrieves a mock for the abstract class.
	 *
	 * @since [version]
	 *
	 * @return LLMS_Abstract_Exportable_Admin_Table
	 */
	private function get_mock( $id = 'mock', $title = 'Mock Title' ) {
		$mock = $this->getMockForAbstractClass(
			LLMS_Abstract_Exportable_Admin_Table::class,
			array(),
			'',
			true,
			true,
			true,
			array( 'get_title' )
		);
		LLMS_Unit_Test_Util::set_private_property( $mock, 'id', $id );

		$mock->method( 'get_title' )->willReturn( $title );

		return $mock;
	}

	/**
	 * Tests {@see LLMS_Abstract_Exportable_Admin_Table::get_export_file_name}
	 *
	 * @since [version]
	 */
	public function test_get_export_file_name() {

		$pass = function( $pass ) {
			return 'ABCD1234';
		};
		add_filter( 'random_password', $pass );

		$now  = time();
		$date = date( 'Y-m-d', $now );
		llms_tests_mock_current_time( $now );

		$this->assertEquals(
			"mock-title_export_{$date}_ABCD1234",
			$this->get_mock()->get_export_file_name()
		);

		remove_filter( 'random_password', $pass );

	}

	/**
	 * Tests {@see LLMS_Abstract_Exportable_Admin_Table::get_export_file_name}
	 * when the table's title contains special characters.
	 *
	 * @link https://github.com/gocodebox/lifterlms/issues/1540
	 *
	 * @since [version]
	 */
	public function test_get_export_file_name_special_chars() {

		$pass = function( $pass ) {
			return 'ABCD1234';
		};
		add_filter( 'random_password', $pass );

		$now  = time();
		$date = date( 'Y-m-d', $now );
		llms_tests_mock_current_time( $now );

		$this->assertEquals(
			"الطلاب_export_{$date}_ABCD1234",
			$this->get_mock( 'mock', 'الطلاب' )->get_export_file_name()
		);

		remove_filter( 'random_password', $pass );

	}

	/**
	 * Tests {@see LLMS_Abstract_Exportable_Admin_Table::get_title} stub.
	 *
	 * @since [version]
	 */
	public function test_get_title() {

		$mock = $this->getMockForAbstractClass(
			LLMS_Abstract_Exportable_Admin_Table::class
		);
		LLMS_Unit_Test_Util::set_private_property( $mock, 'id', 'mock' );

		$this->setExpectedIncorrectUsage(
			'LLMS_Abstract_Exportable_Admin_Table::get_title'
		);
		$this->assertEquals( 'mock', $mock->get_title() );

	}

}
