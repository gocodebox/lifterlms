<?php
/**
 * Unit Test Case with tests and utilities specific to testing LifterLMS Shortcodes
 * @since    3.24.1
 * @version  3.24.3
 */
class LLMS_ShortcodeTestCase extends LLMS_UnitTestCase {

	/**
	 * Class name of the Shortcode Class
	 * @var string
	 */
	public $class_name = '';

	protected function assertOutputEquals( $expect, $shortcode ) {

		ob_start();
		echo do_shortcode( $shortcode );
		$actual = ob_get_clean();

		return $this->assertEquals( $expect, $actual );

	}

	/**
	 * Test shortcode registration
	 * @return  void
	 * @since   3.24.1
	 * @version 3.24.3
	 */
	public function test_registration() {

		$obj = call_user_func( array( $this->class_name, 'instance' ) );
		$this->assertTrue( shortcode_exists( $obj->tag ) );
		$this->assertTrue( is_a( $obj, 'LLMS_Shortcode' ) );
		$this->assertTrue( ! empty( $obj->tag ) );
		$this->assertTrue( is_string( $obj->output() ) );
		$this->assertTrue( is_array( $obj->get_attributes() ) );
		$this->assertTrue( is_string( $obj->get_content() ) );

	}

}
