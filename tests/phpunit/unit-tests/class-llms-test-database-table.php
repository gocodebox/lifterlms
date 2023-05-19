<?php
/**
 * Tests {@see LLMS_Database_Table}
 *
 * @package LifterLMS/Tests
 *
 * @group db
 *
 * @since [version]
 */
class LLMS_Test_Database_Table extends LLMS_UnitTestCase {

	/**
	 * Drops the specified temporary table created during testing.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Database_Table $table The table object.
	 */
	private function drop_table( LLMS_Database_Table $table ): void {

		global $wpdb;
		$wpdb->query( "DROP TEMPORARY TABLE IF EXISTS {$table->get_prefixed_name()};" );

	}

	/**
	 * Tests {@see LLMS_Database_Table::create}.
	 *
	 * @since [version]
	 */
	public function test_create(): void {

		$table = new LLMS_Database_Table( array(
			'name'    => 'table_to_create',
			'columns' => array(
				'id' => array(
					'type' => 'primary_id',
				),
				'value' => array(
					'length' => 5,
				),
			),
			'keys'    => array(
				'id' => LLMS_Database_Table::KEY_PRIMARY,
			),
		) );

		$this->assertTrue( $table->create() );
		$this->drop_table( $table );

	}

	/**
	 * Tests {@see LLMS_Database_Table::create} error.
	 *
	 * @since [version]
	 */
	public function test_create_error(): void {

		$table = new LLMS_Database_Table( array(
			'name'    => 'table_to_create',
			'columns' => array(
				'id' => array(
					'type' => 'primary_id',
				),
				'value' => array(
					'type'   => 'timestamp',
					'length' => 5,
				),
			),
			'keys'    => array(
				'primary' => 'id',
			),
		) );

		$res = $table->create();
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( LLMS_Database_Table::E_CREATE, $res );

		$this->assertEquals(
			"Invalid default value for 'value'",
			$res->get_error_data()['error']
		);

	}

	/**
	 * Tests {@see LLMS_Database::get_create_statement}.
	 *
	 * @since [version]
	 */
	public function test_get_create_statement(): void {

		LLMS_Database::instance()->register_table( 'user_postmeta' );
		$table = LLMS_Database::instance()->get_table( 'user_postmeta' );

		$mock_wpdb = new class {
			public $prefix  = 'testing_';
			public $charset = 'utf8mb4';
			public $collate = 'utf8mb4_unicode_520_ci';
			public function has_cap() {
				return true;
			}
		};
		LLMS_Unit_Test_Util::set_private_property( $table, 'wpdb', $mock_wpdb );

		$this->assertMatchesSnapshot(
			LLMS_Unit_Test_Util::call_method(
				$table,
				'get_create_statement',
				array( 'user-postmeta' )
			)
		);

	}

	/**
	 * Tests {@see LLMS_Database_Table::get_column} for an invalid column.
	 *
	 * @since [version]
	 */
	public function test_get_column_invalid(): void {

		$table = new LLMS_Database_Table( array(
			'name'    => 'table_name',
			'columns' => array(),
		) );

		$this->assertFalse( $table->get_column( 'id' ) );

	}

	/**
	 * Tests {@see LLMS_Database_Table::get_column}.
	 *
	 * @since [version]
	 */
	public function test_get_column(): void {

		$table = new LLMS_Database_Table( array(
			'name'    => 'table_name',
			'columns' => array(
				'id' => array(),
			),
		) );

		$this->assertInstanceOf(
			'LLMS_Database_Column',
			$table->get_column( 'id' )
		);

	}

	/**
	 * Tests {@see LLMS_Database::get_create_key_string}.
	 *
	 * @since [version]
	 */
	public function test_get_create_key_string(): void {

		$table = new LLMS_Database_Table( array( 'name' => 'table_name' ) );

		$tests = array(
			array(
				'colname',
				LLMS_Database_Table::KEY_PRIMARY,
				'PRIMARY KEY (`colname`)',
			),
			array(
				'colname',
				LLMS_Database_Table::KEY_UNIQUE,
				'UNIQUE KEY `colname` (`colname`)',
			),
			array(
				'colname',
				array(),
				'KEY `colname` (`colname`)',
			),
			array(
				'colname',
				array( 'length' => 25 ),
				'KEY `colname` (`colname`(25))',
			),
			array(
				'compound_key',
				array(
					'parts' => array(
						'key1' => 10,
						'key2' => 20,
					),
				),
				'KEY `compound_key` (`key1`(10),`key2`(20))',
			),
		);

		foreach ( $tests as $test ) {
			list( $key, $parts, $expected ) = $test;
			$this->assertSame(
				$expected,
				LLMS_Unit_Test_Util::call_method(
					$table,
					'get_create_key_string',
					array( $key, $parts )
				)
			);
		}

	}

	/**
	 * Tests {@see LLMS_Database_Table::get_name}.
	 *
	 * @since [version]
	 */
	public function test_get_name(): void {

		$table = new LLMS_Database_Table( array( 'name' => 'table_name' ) );
		$this->assertEquals( 'table_name', $table->get_name() );

	}

	/**
	 * Tests {@see LLMS_Database_Table::get_prefixed_name}.
	 *
	 * @since [version]
	 */
	public function test_get_prefixed_name(): void {

		$table = new LLMS_Database_Table( array( 'name' => 'table_name' ) );

		$mock_wpdb = new class {
			public $prefix  = 'testing_';
		};
		LLMS_Unit_Test_Util::set_private_property( $table, 'wpdb', $mock_wpdb );

		$this->assertEquals(
			'testing_lifterlms_table_name',
			$table->get_prefixed_name()
		);

	}

	/**
	 * Tests {@see LLMS_Database_Table::get_schema} when computing all col props.
	 *
	 * @since [version]
	 */
	public function test_get_schema_computed(): void {

		$db = LLMS_Database::instance();
		$table = $db->get_table( 'user_postmeta' );
		$this->assertMatchesJsonSnapshot( $table->get_schema( false ) );

	}

	/**
	 * Tests {@see LLMS_Database_Table::get_schema}.
	 *
	 * @since [version]
	 */
	public function test_get_schema(): void {

		$schema = array( 'name' => 'table_name' );
		$table = new LLMS_Database_Table( $schema );
		$this->assertEquals( $schema, $table->get_schema() );

	}

	/**
	 * Tests {@see LLMS_Database_Table::is_installed}.
	 *
	 * @since [version]
	 */
	public function test_is_installed(): void {

		$tests = array(
			'fake'          => false,
			'user_postmeta' => true,
		);

		foreach ( $tests as $name => $expected ) {
			$table = new LLMS_Database_Table( compact( 'name' ) );
			$this->assertSame( $expected, $table->is_installed() );
		}

	}

}
