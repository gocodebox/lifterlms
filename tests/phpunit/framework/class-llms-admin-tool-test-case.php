<?php
/**
 * Admin Tool base test case
 *
 * @package LifterLMS/Tests/Framework
 *
 * @since 5.3.0
 */

require_once 'class-llms-unit-test-case.php';

class LLMS_Admin_Tool_Test_Case extends LLMS_UnitTestCase {

	/**
	 * Name of the class being tested.
	 *
	 * This must be added to extending classes.
	 *
	 * @var sting
	 */
	// const CLASS_NAME = 'LLMS_Admin_Tool_Class_Name';

	/**
	 * Setup before class
	 *
	 * Include abstract required classes.
	 *
	 * @since 5.3.0
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();

		// Abstract tool.
		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/llms-abstract-admin-tool.php';

		// Include the tool itself.
		$filename = 'class-' . str_replace( '_', '-', strtolower( static::CLASS_NAME ) ) . '.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/tools/' . $filename;

	}

	/**
	 * Setup test case
	 *
	 * @since 5.3.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$classname = static::CLASS_NAME;
		$this->main = new $classname();

	}

	/**
	 * Test get_description()
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_get_description() {

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'get_description' );
		$this->assertTrue( ! empty( $res ) );
		$this->assertTrue( is_string( $res ) );

	}

	/**
	 * Test get_label()
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_get_label() {

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'get_label' );
		$this->assertTrue( ! empty( $res ) );
		$this->assertTrue( is_string( $res ) );

	}

	/**
	 * Test get_text()
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_get_text() {

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'get_text' );
		$this->assertTrue( ! empty( $res ) );
		$this->assertTrue( is_string( $res ) );

	}

}
