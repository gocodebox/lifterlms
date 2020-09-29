<?php
/**
 * Test Logging Functions
 *
 * @package LifterLMS/Tests/Functions
 *
 * @group functions
 * @group functions_logs
 *
 * @since [version]
 */
class LLMS_Test_Functions_Logs extends LLMS_UnitTestCase {

	/**
	 * Test llms_get_log_path()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_get_log_path() {

		$handle = 'testhandle';
		$expected_hash = wp_hash( $handle );

		$expected_file = sprintf( '%1$s-%2$s.log', $handle, $expected_hash );

		$path = llms_get_log_path( $handle );

		$this->assertEquals( $expected_file, basename( $path ) );
		$this->assertEquals( untrailingslashit( LLMS_LOG_DIR ), dirname( $path ) );

		$this->assertEquals( LLMS_LOG_DIR . $expected_file, $path );

	}

	/**
	 * Test llms_log() when logging a string
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_log_string() {

		$this->assertTrue( llms_log( 'Test message', 'teststringlog' ) );

		$logs = explode( ' - ', file_get_contents( llms_get_log_path( 'teststringlog' ) ) );

		$this->assertTrue( date_create( $logs[0] ) instanceof DateTime );

		$this->assertEquals( "Test message\n", $logs[1] );

	}

	/**
	 * Test llms_log() when logging an array
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_log_array() {

		$this->assertTrue( llms_log( array( 'Test message' ), 'testarrlog' ) );

		$logs = explode( ' - ', file_get_contents( llms_get_log_path( 'testarrlog' ) ) );

		$this->assertTrue( date_create( $logs[0] ) instanceof DateTime );

		$this->assertEquals( "Array
(
    [0] => Test message
)

", $logs[1] );

	}

	/**
	 * Test llms_log() when logging an object
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_log_object() {

		$this->assertTrue( llms_log( (object) array( 'Test' => 1 ), 'testobjlog' ) );

		$logs = explode( ' - ', file_get_contents( llms_get_log_path( 'testobjlog' ) ) );

		$this->assertTrue( date_create( $logs[0] ) instanceof DateTime );

		$this->assertEquals( "stdClass Object
(
    [Test] => 1
)

", $logs[1] );

	}

	/**
	 * Test llms_split_log
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_split_log() {

		// Reduce the max filesize to 1MB to make testing easier.
		$handler = function( $max ) {
			return 1;
		};
		add_filter( 'llms_log_max_filesize', $handler );

		$handle = 'logtosplit';
		$file   = llms_get_log_path( $handle );

		// File doesn't exist, no need to split.
		$this->assertFalse( llms_split_log( $handle ) );

		llms_log( str_repeat( '01', 999 ), $handle );

		// File does exist but doesn't need to be split yet.
		$this->assertFalse( llms_split_log( $handle ) );

		clearstatcache( true, $file );

		// Create a file that exceeds 1MB.
		$i = 0;
		while ( $i <= 501 ) {
			llms_log( str_repeat( '01', 999 ), $handle );
			++$i;
		}

		// Get the contents of the original to compare later.
		$original = file_get_contents( $file );

		// Split the file.
		$copy = llms_split_log( $handle );

		// We made a copy.
		$this->assertTrue( false !== $copy );

		// Return should be different than than the original.
		$this->assertNotEquals( $copy, $file );

		// Copy exists.
		$this->assertTrue( file_exists( $copy ) );

		// Original has been removed.
		$this->assertFalse( file_exists( $file ) );

		// Compare copy contents to the original.
		$this->assertEquals( $original, file_get_contents( $copy ) );

		// Remove small max size.
		remove_filter( 'llms_log_max_filesize', $handler );

	}

}
