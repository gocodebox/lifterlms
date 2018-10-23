<?php
/**
 * Tests for LLMS_Admin_Review class
 * @group    admin
 * @since    [version]
 * @version  [version]
 */
class LLMS_Test_Admin_Review extends LLMS_UnitTestCase {

	public function setUp() {

		parent::setUp();

		include_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-admin-review.php';

	}

	public function test_round_down() {

		$this->assertEquals( 25, LLMS_Admin_Review::round_down( 25 ) );
		$this->assertEquals( 1, LLMS_Admin_Review::round_down( 1 ) );
		$this->assertEquals( 49, LLMS_Admin_Review::round_down( 49 ) );
		$this->assertEquals( 50, LLMS_Admin_Review::round_down( 50 ) );
		$this->assertEquals( 50, LLMS_Admin_Review::round_down( 99 ) );
		$this->assertEquals( 100, LLMS_Admin_Review::round_down( 105 ) );
		$this->assertEquals( 200, LLMS_Admin_Review::round_down( 293 ) );
		$this->assertEquals( 300, LLMS_Admin_Review::round_down( 392 ) );
		$this->assertEquals( 500, LLMS_Admin_Review::round_down( 532 ) );
		$this->assertEquals( 700, LLMS_Admin_Review::round_down( 781 ) );
		$this->assertEquals( 800, LLMS_Admin_Review::round_down( 850 ) );
		$this->assertEquals( 900, LLMS_Admin_Review::round_down( 900 ) );
		$this->assertEquals( 1000, LLMS_Admin_Review::round_down( 1000 ) );
		$this->assertEquals( 1000, LLMS_Admin_Review::round_down( 1101 ) );
		$this->assertEquals( 1000, LLMS_Admin_Review::round_down( 1500 ) );
		$this->assertEquals( 2000, LLMS_Admin_Review::round_down( 2205 ) );
		$this->assertEquals( 5000, LLMS_Admin_Review::round_down( 5878 ) );
		$this->assertEquals( 9000, LLMS_Admin_Review::round_down( 9999 ) );
		$this->assertEquals( 10000, LLMS_Admin_Review::round_down( 10000 ) );
		$this->assertEquals( 10000, LLMS_Admin_Review::round_down( 10001 ) );
		$this->assertEquals( 10000, LLMS_Admin_Review::round_down( 10299 ) );
		$this->assertEquals( 10000, LLMS_Admin_Review::round_down( 50099 ) );

	}

}
