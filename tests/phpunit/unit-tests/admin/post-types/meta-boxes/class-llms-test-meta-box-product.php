<?php
/**
 * Tests for LifterLMS Access Plans (Product) Metabox.
 *
 * @package LifterLMS/Tests
 *
 * @group metabox_product
 * @group admin
 * @group metaboxes
 * @group metaboxes_post_type
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Meta_Box_Product extends LLMS_PostTypeMetaboxTestCase {

	/**
	 * Setup test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->metabox = new LLMS_Meta_Box_Product();

	}

	/**
	 * Test the default screens returned by the metabox.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_screens_default() {

		$this->assertEquals(
			array( 'course', 'llms_membership' ),
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'get_screens' )
		);

	}

	/**
	 * Test that the `llms_metabox_product_screens` filter can disable the metabox.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_screens_filter_disables_metabox() {

		add_filter( 'llms_metabox_product_screens', '__return_empty_array' );

		try {
			$metabox = new LLMS_Meta_Box_Product();
			$this->assertEquals(
				array(),
				LLMS_Unit_Test_Util::call_method( $metabox, 'get_screens' )
			);
		} finally {
			remove_filter( 'llms_metabox_product_screens', '__return_empty_array' );
		}

	}

	/**
	 * Test that the `llms_metabox_product_screens` filter can add a custom screen.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_screens_filter_adds_custom_screen() {

		$callback = function() {
			return array( 'course', 'llms_membership', 'custom_cpt' );
		};

		add_filter( 'llms_metabox_product_screens', $callback );

		try {
			$metabox = new LLMS_Meta_Box_Product();
			$this->assertEquals(
				array( 'course', 'llms_membership', 'custom_cpt' ),
				LLMS_Unit_Test_Util::call_method( $metabox, 'get_screens' )
			);
		} finally {
			remove_filter( 'llms_metabox_product_screens', $callback );
		}

	}

}
