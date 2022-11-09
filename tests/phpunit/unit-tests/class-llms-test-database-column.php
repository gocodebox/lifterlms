<?php
/**
 * Tests {@see LLMS_Database_Column}
 *
 * @package LifterLMS/Tests
 *
 * @group db
 *
 * @since [version]
 */
class LLMS_Test_Database_Column extends LLMS_Unit_Test_Case {

	/**
	 * Tests {@see LLMS_Database_Column::get_create_string()}.
	 *
	 * @since [version]
	 */
	public function test_get_create_string() {

		$tests = array(
			array(
				'colname',
				array(),
				'`colname` varchar DEFAULT NULL',
			),
			array(
				'colname',
				array(
					'type'     => 'int',
					'length'   => 5,
					'unsigned' => true,
				),
				'`colname` int(5) UNSIGNED DEFAULT NULL',
			),
			array(
				'colname',
				array(
					'type'           => 'int',
					'length'         => 5,
					'unsigned'       => true,
					'auto_increment' => true,
				),
				'`colname` int(5) UNSIGNED DEFAULT NULL AUTO_INCREMENT',
			),
			array(
				'colname',
				array(
					'type'       => 'datetime',
					'allow_null' => false,
				),
				'`colname` datetime NOT NULL',
			),
			array(
				'colname',
				array(
					'length'  => 5,
					'default' => 'ABCD'
				),
				"`colname` varchar(5) DEFAULT 'ABCD'",
			),
			array(
				'colname',
				array(
					'type' => 'id',
				),
				"`colname` bigint(20) UNSIGNED NOT NULL",
			),
			array(
				'colname',
				array(
					'type' => 'primary_id',
				),
				"`colname` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT",
			),
		);

		foreach ( $tests as $test ) {
			list( $name, $cfg, $expected ) = $test;

			$col = new LLMS_Database_Column( $name, $cfg );
			$this->assertEquals( $expected, $col->get_create_string() );
		}

	}

	/**
	 * Retrieves whether or not `AUTO_INCREMENT` is enabled for the column.
	 *
	 * @since [version]
	 */
	public function test_get_auto_increment(): void {
		$value = true;
		$col = new LLMS_Database_Column( 'colname', array( 'auto_increment' => $value ) );
		$this->assertSame( $value, $col->get_auto_increment() );
	}

	/**
	 * Retrieves whether column value can be `null`.
	 *
	 * @since [version]
	 */
	public function test_get_allow_null(): void {
		$value = true;
		$col = new LLMS_Database_Column( 'colname', array( 'allow_null' => $value ) );
		$this->assertSame( $value, $col->get_allow_null() );
	}

	/**
	 * Retrieves the column's default value.
	 *
	 * A `null` return denotes there is no default value.
	 *
	 * If the default value is `null`, the string `NULL` will be returned.
	 *
	 * @since [version]
	 */
	public function test_get_default(): void {
		$value = 'ABC';
		$col = new LLMS_Database_Column( 'colname', array( 'default' => $value ) );
		$this->assertSame( $value, $col->get_default() );
	}

	/**
	 * Retrieves the columns length.

	 *
	 * @since [version]
	 */
	public function test_get_length(): void {
		$value = 105;
		$col = new LLMS_Database_Column( 'colname', array( 'length' => $value ) );
		$this->assertSame( $value, $col->get_length() );
	}

	/**
	 * Retrieves the column name.
	 *
	 * @since [version]
	 */
	public function test_get_name(): void {
		$value = 'colname';
		$col = new LLMS_Database_Column( 'colname' );
		$this->assertSame( $value, $col->get_name() );
	}

	/**
	 * Retrieves the columns type.
	 *
	 * @since [version]
	 */
	public function test_get_type(): void {
		$value = 'longtext';
		$col = new LLMS_Database_Column( 'colname', array( 'type' => $value ) );
		$this->assertSame( $value, $col->get_type() );
	}

	/**
	 * Retrieves whether or not the column is unsigned.
	 *
	 * Non-numeric column types will return `null`.
	 *
	 * @since [version]
	 */
	public function test_get_unsigned(): void {
		$value = false;
		$col = new LLMS_Database_Column( 'colname', array( 'unsigned' => $value ) );
		$this->assertSame( $value, $col->get_unsigned() );
	}

}
