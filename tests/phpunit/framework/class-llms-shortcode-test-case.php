<?php
/**
 * Unit Test Case with tests and utilities specific to testing LifterLMS Shortcodes
 *
 * @since 3.24.1
 * @since 5.0.0 Add helper method `get_class()`.
 * @version 5.0.0
 */

require_once 'class-llms-unit-test-case.php';

class LLMS_ShortcodeTestCase extends LLMS_UnitTestCase {

	/**
	 * Class name of the Shortcode Class
	 *
	 * @var string
	 */
	public $class_name = '';

	/**
	 * Retrieve an instance of the shortcode generator class.
	 *
	 * @since 5.0.0
	 *
	 * @return obj
	 */
	protected function get_class() {

		return call_user_func( array( $this->class_name, 'instance' ) );

	}

	/**
	 * Assertion to expect the output of a given shortcode string.
	 *
	 * @since 5.0.0
	 *
	 * @param string $expect Expected shortcode output.
	 * @param string $shortcode Shortcode string (to be wrapped in `do_shortcode()`).
	 * @return void
	 */
	protected function assertShortcodeOutputEquals( $expect, $shortcode ) {

		ob_start();
		echo do_shortcode( $shortcode );
		$actual = ob_get_clean();

		return $this->assertEquals( $expect, $actual );

	}

	/**
	 * Test shortcode registration
	 *
	 * @since 3.24.1
	 * @since 3.24.3 Unknown.
	 *
	 * @return void
	 */
	public function test_registration() {

		$obj = $this->get_class();
		$this->assertTrue( shortcode_exists( $obj->tag ) );
		$this->assertTrue( is_a( $obj, 'LLMS_Shortcode' ) );
		$this->assertTrue( ! empty( $obj->tag ) );
		$this->assertTrue( is_string( $obj->output() ) );
		$this->assertTrue( is_array( $obj->get_attributes() ) );
		$this->assertTrue( is_string( $obj->get_content() ) );

	}

}
