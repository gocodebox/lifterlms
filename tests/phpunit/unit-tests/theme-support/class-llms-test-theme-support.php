<?php
/**
 * Test LLMS_Theme_Support
 *
 * @package LifterLMS/Tests
 *
 * @group theme_support
 *
 * @since 3.37.0
 */
class LLMS_Test_Theme_Support extends LLMS_Unit_Test_Case {

	/**
	 * Array of supported themes
	 *
	 * template => support class name.
	 *
	 * @var array
	 */
	protected $supported = array(
		'twentynineteen' => 'LLMS_Twenty_Nineteen',
		'twentytwenty' => 'LLMS_Twenty_Twenty',
	);

	/**
	 * Test theme support classes are loaded based on the current theme template.
	 *
	 * @since 3.37.0
	 *
	 * @return void
	 */
	public function test_includes_no_support() {

		update_option( 'template', 'default' );

		foreach ( array_values( $this->supported ) as $class ) {
			$this->assertTrue( ! class_exists( $class ) );
		}

	}

	/**
	 * Test theme support classes are loaded based on the current theme template.
	 *
	 * @since 3.37.0
	 * @since 4.3.0 Update theme support class instantiation.
	 *
	 * @return void
	 */
	public function test_includes_with_support() {

		foreach ( $this->supported as $template => $class ) {
			update_option( 'template', $template );
			$support = new LLMS_Theme_Support();
			$support->includes();
			$this->assertTrue( class_exists( $class ) );
		}

		update_option( 'template', 'default' );

	}

}
