<?php
/**
 * Unit Test Case with tests and utilities specific to testing LifterLMS Notification Classes
 * @since    3.8.0
 * @version  3.8.0
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
	 * @since   [version]
	 * @version [version]
	 */
	public function test_registration() {

		$obj = $this->class_name::instance();
		$this->assertTrue( shortcode_exists( $obj->tag ) );
		$this->assertTrue( is_a( $obj, 'LLMS_Shortcode' ) );
		$this->assertTrue( ! empty( $obj->tag ) );
		$this->assertTrue( is_string( $obj->output() ) );
		$this->assertTrue( is_array( $obj->get_attributes() ) );
		$this->assertTrue( is_string( $obj->get_content() ) );

	}

}
