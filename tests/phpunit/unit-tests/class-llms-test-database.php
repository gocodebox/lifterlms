<?php
/**
 * Tests {@see LLMS_Database}
 *
 * @package LifterLMS/Tests
 *
 * @group db
 *
 * @since [version]
 */
class LLMS_Test_Database extends LLMS_UnitTestCase {

	/**
	 * The tested class instance.
	 *
	 * @var LLMS_Database
	 */
	private LLMS_Database $main;

	/**
	 * Original LLMS_Database::$tables array.
	 *
	 * @var array
	 */
	private array $original_tables;

	/**
	 * Sets up the test.
	 *
	 * @since [version]
	 */
	public function set_up(): void {
		parent::set_up();
		$this->main = LLMS_Database::instance();

		$this->original_tables = LLMS_Unit_Test_Util::get_private_property_value(
			$this->main,
			'tables'
		);
	}

	/**
	 * Clean up after tests.
	 *
	 * @since [version]
	 */
	public function tear_down(): void {

		parent::tear_down();
		LLMS_Unit_Test_Util::set_private_property(
			$this->main,
			'tables',
			$this->original_tables
		);

	}

	/**
	 * Tests {@see LLMS_Database::get_schema} when no schema file can be located
	 * for the specified table.
	 *
	 * Also tests {@see LLMS_Database::is_table_registered}.
	 *
	 * @since [version]
	 */
	public function test_get_schema_already_registered() {

		$mock_schema = array( 'name' => 'fake', 'columns' => array() );
		LLMS_Unit_Test_Util::set_private_property(
			$this->main,
			'tables',
			array(
				'fake' => $mock_schema,
			)
		);

		$this->assertEquals( $mock_schema, $this->main->get_schema( 'fake' ) );

	}

	/**
	 * Tests {@see LLMS_Database::get_schema} with a real existing schema file.
	 *
	 * Also tests {@see LLMS_Database::locate_schema_file} and
	 * {@see LLMS_Database::is_table_registered}.
	 *
	 * @since [version]
	 */
	public function test_get_schema_load_from_file() {

		LLMS_Unit_Test_Util::set_private_property(
			$this->main,
			'tables',
			array()
		);

		$this->assertMatchesJsonSnapshot( $this->main->get_schema( 'user_postmeta' ) );

	}

	/**
	 * Tests {@see LLMS_Database::get_schema} when no schema file can be located
	 * for the specfied table.
	 *
	 * Also tests {@see LLMS_Database::locate_schema_file}.
	 *
	 * @since [version]
	 */
	public function test_get_schema_invalid() {
		$this->assertFalse( $this->main->get_schema( 'fake' ) );
	}

	/**
	 * Tests {@see LLMS_Database::get_table} for an invalid table.
	 *
	 * @since [version]
	 */
	public function test_get_table_invalid() {
		$this->assertFalse( $this->main->get_table( 'fake' ) );
	}

	/**
	 * Tests {@see LLMS_Database::get_table}.
	 *
	 * @since [version]
	 */
	public function test_get_table() {

		$this->main->register_table( 'user_postmeta' );
		$this->assertInstanceOf(
			'LLMS_Database_Table',
			$this->main->get_table( 'user_postmeta' )
		);

	}

	/**
	 * Tests {@see LLMS_Database::get_table_options} when the value is
	 * cached.
	 *
	 * @since [version]
	 */
	public function test_get_table_options_cached() {

		LLMS_Unit_Test_Util::set_private_property(
			$this->main,
			'table_options',
			'TEST'
		);
		$this->assertEquals( 'TEST', $this->main->get_table_options() );

		LLMS_Unit_Test_Util::set_private_property(
			$this->main,
			'table_options',
			null
		);

	}

	/**
	 * Tests {@see LLMS_Database::get_table_options} when the database doesn't
	 * have the `collation` capability.
	 *
	 * @since [version]
	 */
	public function test_get_table_options_no_collation() {

		global $wpdb;

		$mock_wpdb = new class {
			public function has_cap() {
				return false;
			}
		};
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'wpdb', $mock_wpdb );

		$this->assertSame( '', $this->main->get_table_options() );

		LLMS_Unit_Test_Util::set_private_property( $this->main, 'wpdb', $wpdb );
		LLMS_Unit_Test_Util::set_private_property(
			$this->main,
			'table_options',
			null
		);

	}

	/**
	 * Tests {@see LLMS_Database::get_table_options}.
	 *
	 * @since [version]
	 */
	public function test_get_table_options() {

		global $wpdb;

		$mock_wpdb = new class {
			public $charset = 'utf8mb4';
			public $collate = 'utf8mb4_unicode_520_ci';
			public function has_cap() {
				return true;
			}
		};
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'wpdb', $mock_wpdb );

		$this->assertSame(
			'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci',
			$this->main->get_table_options()
		);

		LLMS_Unit_Test_Util::set_private_property( $this->main, 'wpdb', $wpdb );

	}

	/**
	 * Tests {@see LLMS_Database::register_schema_path}
	 *
	 * @since [version]
	 */
	public function test_register_schema_path() {

		$this->main->register_schema_path( '/fake/path' );

		$this->assertTrue(
			in_array(
				'/fake/path/',
				LLMS_Unit_Test_Util::get_private_property_value(
					$this->main,
					'schema_paths'
				),
				true
			)
		);

	}

	/**
	 * Tests {@see LLMS_Database::register_table} for a table which has already
	 * been registered.
	 *
	 * @since [version]
	 */
	public function test_register_table_already_registered() {

		LLMS_Unit_Test_Util::set_private_property(
			$this->main,
			'tables',
			array(
				'fake' => array( 'name' => 'fake', 'columns' => array() ),
			)
		);

		$this->assertNull( $this->main->register_table( 'fake' ) );

	}

	/**
	 * Tests {@see LLMS_Database::register_table} for an invalid table.
	 *
	 * @since [version]
	 */
	public function test_register_table_not_found() {
		$this->assertFalse( $this->main->register_table( 'invalid' ) );
	}

	/**
	 * Tests {@see LLMS_Database::register_table}
	 *
	 * @since [version]
	 */
	public function test_register_table() {

		global $lifterlms_tests, $wpdb;
		$this->main->register_schema_path( $lifterlms_tests->assets_dir );

		// Registration success.
		$this->assertTrue(
			$this->main->register_table( 'mock_db_table_schema' )
		);

		// Internally stored.
		$this->assertTrue(
			$this->main->is_table_registered( 'mock_db_table_schema' )
		);

		// Registered with WPDB.
		$this->assertTrue(
			in_array(
				'lifterlms_mock_db_table_schema',
				$wpdb->tables,
				true
			)
		);

		$this->assertEquals(
			$wpdb->prefix . 'lifterlms_mock_db_table_schema',
			$wpdb->lifterlms_mock_db_table_schema
		);

	}

}
