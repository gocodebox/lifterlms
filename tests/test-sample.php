<?php

class SampleTest extends WP_UnitTestCase {

	function testSample() {
		// replace this with some actual testing code
		$this->assertTrue( true );
	}

	function test_sample_string() {

		$string = 'Unit tests are sweet';

		$this->assertEquals( 'Unit tests are sweet', $string);
	}
}

