<?php
/**
 * Test LLMS_Forms_Classic_Editor class
 *
 * @package LifterLMS/Tests
 *
 * @group forms
 * @group forms_classic
 *
 * @since 5.0.0
 * @version 5.0.0
 */
class LLMS_Test_Forms_Classic_Editor extends LLMS_UnitTestCase {

	/**
	 * Test init()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_init() {

		remove_filter( 'use_block_editor_for_post_type', array( 'LLMS_Forms_Classic_Editor', 'force_block_editor' ), 200 );
		remove_filter( 'classic_editor_enabled_editors_for_post_type', array( 'LLMS_Forms_Classic_Editor', 'disable_classic_editor' ), 20 );

		LLMS_Forms_Classic_Editor::init();

		$this->assertEquals( 200, has_filter( 'use_block_editor_for_post_type', array( 'LLMS_Forms_Classic_Editor', 'force_block_editor' ) ) );
		$this->assertEquals( 20, has_filter( 'classic_editor_enabled_editors_for_post_type', array( 'LLMS_Forms_Classic_Editor', 'disable_classic_editor' ) ) );

	}

	/**
	 * Test force_block_editor()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_force_block_editor() {

		$tests = array(
			'post' => array(
				array(
					'input'  => true,
					'output' => true
				),
				array(
					'input'  => false,
					'output' => false,
				)
			),
			'page' => array(
				array(
					'input'  => true,
					'output' => true
				),
				array(
					'input'  => false,
					'output' => false,
				)
			),
			'course' => array(
				array(
					'input'  => true,
					'output' => true
				),
				array(
					'input'  => false,
					'output' => false,
				)
			),
			'llms_form' => array(
				array(
					'input'  => true,
					'output' => true
				),
				array(
					'input'  => false,
					'output' => true,
				)
			),
		);

		foreach ( $tests as $post_type => $groups ) {
			foreach ( $groups as $data ) {
				$this->assertSame( $data['output'], LLMS_Forms_Classic_Editor::force_block_editor( $data['input'], $post_type ) );
			}
		}

	}

	/**
	 * Test disable_classic_editor()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_disable_classic_editor() {

		$expected = array(
			'classic_editor' => true,
			'block_editor'   => true,
		);
		foreach ( array( 'post', 'page', 'course' ) as $post_type ) {
			$this->assertEquals( $expected, LLMS_Forms_Classic_Editor::disable_classic_editor( $expected, $post_type ) );
		}

		$expected['classic_editor'] = false;
		$this->assertEquals( $expected, LLMS_Forms_Classic_Editor::disable_classic_editor( $expected, 'llms_form' ) );

	}

}
