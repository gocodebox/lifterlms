<?php
/**
 * Tests for LifterLMS Achievement Metabox.
 *
 * @package LifterLMS/Tests
 *
 * @group metabox_achievement
 * @group admin
 * @group metaboxes
 * @group metaboxes_post_type
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Meta_Box_Achievement extends LLMS_PostTypeMetaboxTestCase {

	/**
	 * Setup test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->metabox = new LLMS_Meta_Box_Achievement();

	}

	/**
	 * Test the get_screens() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_screens() {

		$this->assertEquals( array( 'llms_achievement' ), LLMS_Unit_Test_Util::call_method( $this->metabox, 'get_screens' ) );

	}

	/**
	 * Test get fields.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_fields() {

		$this->assertEqualSets(
			array(
				'_llms_achievement_title',
				'_llms_achievement_content',
			),
			array_column(
				$this->metabox->get_fields()[0]['fields'],
				'id'
			)
		);

	}

}
