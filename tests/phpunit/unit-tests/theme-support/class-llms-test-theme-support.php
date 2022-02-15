<?php
/**
 * Test LLMS_Theme_Support
 *
 * @package LifterLMS/Tests
 *
 * @group theme_support
 *
 * @since 3.37.0
 * @since 4.10.0 Added tests for Twenty Twenty-One theme.
 * @since 5.9.0 Added tests for Twenty Twenty-Two
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
		'twentynineteen'  => 'LLMS_Twenty_Nineteen',
		'twentytwenty'    => 'LLMS_Twenty_Twenty',
		'twentytwentyone' => 'LLMS_Twenty_Twenty_One',
		'twentytwentytwo' => 'LLMS_Twenty_Twenty_Two',
	);

	/**
	 * Test get_css()
	 *
	 * @since 4.10.0
	 *
	 * @return void
	 */
	public function test_get_css() {
		$this->assertEquals( 'body, .el { background: red; }', LLMS_Theme_Support::get_css( array( 'body', '.el' ), array( 'background' => 'red' ) ) );
		$this->assertEquals( 'body, .el { background: red; color: black; }', LLMS_Theme_Support::get_css( array( 'body', '.el' ), array( 'background' => 'red', 'color' => 'black' ) ) );
	}


	/**
	 * Test get_css() with a prefix
	 *
	 * @since 4.10.0
	 *
	 * @return void
	 */
	public function test_get_css_with_prefix() {
		$this->assertEquals( '#prefix body, #prefix .el { background: red; }', LLMS_Theme_Support::get_css( array( 'body', '.el' ), array( 'background' => 'red' ), '#prefix' ) );
		$this->assertEquals( '#prefix body, #prefix .el { background: red; color: black; }', LLMS_Theme_Support::get_css( array( 'body', '.el' ), array( 'background' => 'red', 'color' => 'black' ), '#prefix' ) );
	}

	/**
	 * Test get_css() when passing in an array for a rule
	 *
	 * @since 4.10.0
	 *
	 * @return void
	 */
	public function test_get_css_with_array_of_rules() {

		$css = array(
			'background-image' => array(
				'-webkit-radial-gradient(fake)',
				'radial-gradient(fake)',
			)
		);

		$expected = '#prefix body, #prefix .el { background-image: -webkit-radial-gradient(fake); background-image: radial-gradient(fake); }';

		$this->assertEquals( $expected, LLMS_Theme_Support::get_css( array( 'body', '.el' ), $css, '#prefix' ) );
	}

	/**
	 * Test get_selectors_primary_color_background()
	 *
	 * @since 4.10.0
	 *
	 * @return void
	 */
	public function test_get_selectors_primary_color_background() {
		$res = LLMS_Theme_Support::get_selectors_primary_color_background();
		$this->assertTrue( is_array( $res ) );
		foreach ( $res as $sel ) {
			$this->assertTrue( is_string( $sel ) );
		}
	}

	/**
	 * Test get_selectors_primary_color_border()
	 *
	 * @since 4.10.0
	 *
	 * @return void
	 */
	public function test_get_selectors_primary_color_border() {
		$res = LLMS_Theme_Support::get_selectors_primary_color_border();
		$this->assertTrue( is_array( $res ) );
		foreach ( $res as $sel ) {
			$this->assertTrue( is_string( $sel ) );
		}
	}

	/**
	 * Test get_selectors_primary_color_text()
	 *
	 * @since 4.10.0
	 *
	 * @return void
	 */
	public function test_get_selectors_primary_color_text() {
		$res = LLMS_Theme_Support::get_selectors_primary_color_text();
		$this->assertTrue( is_array( $res ) );
		foreach ( $res as $sel ) {
			$this->assertTrue( is_string( $sel ) );
		}
	}


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
