<?php
/**
 * Tests for the LLMS_Admin_Metabox class
 *
 * @package LifterLMS/Tests/Abstracts
 *
 * @group abstracts
 * @group metaboxes
 * @group metabox_abstract
 *
 * @since [version]
 */
class LLMS_Test_Admin_Metabox extends LLMS_PostTypeMetaboxTestCase {

	private function get_stub() {

		$stub = $this->getMockForAbstractClass( 'LLMS_Admin_Metabox' );

		$stub->title = 'Mock Metabox';
		$stub->id    = 'mocker';

		return $stub;

	}

	/**
	 * Test get_screens() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_screens() {

		$stub = $this->get_stub();

		// As string.
		$stub->screens = 'post';
		$this->assertEquals( array( 'post' ), LLMS_Unit_Test_Util::call_method( $stub, 'get_screens' ) );

		// Array.
		$stub->screens = array( 'post' );
		$this->assertEquals( array( 'post' ), LLMS_Unit_Test_Util::call_method( $stub, 'get_screens' ) );

		// Array with multiple post types.
		$stub->screens[] = 'page';
		$this->assertEquals( array( 'post', 'page' ), LLMS_Unit_Test_Util::call_method( $stub, 'get_screens' ) );

	}

}
