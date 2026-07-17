<?php
/**
 * Test LLMS_Blocks_Reusable class & methods.
 *
 * @package LifterLMS_Blocks/Tests
 *
 * @group reusable
 *
 * @since 2.0.0
 */
class LLMS_Blocks_Test_Reusable extends LLMS_Blocks_Unit_Test_Case {

	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Blocks_Reusable();

	}

	/**
	 * Test rest callbacks.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function test_rest_callbacks() {

		$id  = $this->factory->post->create( array( 'post_type' => 'wp_block' ) );
		$arr = compact( 'id' );
		$obj = get_post( $id );

		// Value not set.
		$this->assertEquals( 'no', $this->main->rest_callback_get( $arr, null ) );

		// Update to no.
		$this->assertTrue( $this->main->rest_callback_update( 'no', $obj, null ) );
		$this->assertEquals( 'no', $this->main->rest_callback_get( $arr, null ) );

		// Update to yes.
		$this->assertTrue( $this->main->rest_callback_update( 'yes', $obj, null ) );
		$this->assertEquals( 'yes', $this->main->rest_callback_get( $arr, null ) );

		// Sanitize.
		$this->assertTrue( $this->main->rest_callback_update( 'fake', $obj, null ) );
		$this->assertEquals( 'no', $this->main->rest_callback_get( $arr, null ) );

	}

	/**
	 * Test rest_register_fields()
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function test_rest_register_fields() {

		$this->main->rest_register_fields();

		global $wp_rest_additional_fields;
		$this->assertArrayHasKey( 'wp_block', $wp_rest_additional_fields );
		$this->assertArrayHasKey( 'is_llms_field', $wp_rest_additional_fields['wp_block'] );

	}

	/**
	 * Test mod_wp_block_query()
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function test_mod_wp_block_query() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$args    = array( 'input' );
		$request = new WP_REST_Request();

		// No referer.
		$this->assertEquals( $args, $this->main->mod_wp_block_query( $args, $request ) );

		// Referer doesn't have a post query string var.
		$request->set_header( 'referer', 'https://fake.tld' );
		$this->assertEquals( $args, $this->main->mod_wp_block_query( $args, $request ) );

		// Refering post is a wp_block.
		$block = $this->factory->post->create( array( 'post_type' => 'wp_block' ) );
		$request->set_header( 'referer', get_edit_post_link( $block ) );

		$this->assertEquals( $args, $this->main->mod_wp_block_query( $args, $request ) );

		// From a form.
		$form = $this->factory->post->create( array( 'post_type' => 'llms_form' ) );
		$request->set_header( 'referer', get_edit_post_link( $form ) );

		$expect = array(
			'input',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => '_is_llms_field',
					'value' => 'yes',
				),
			),
		);
		$res = $this->main->mod_wp_block_query( $args, $request );
		$this->assertEquals( $expect, $res );


		// From any other post or the widgets screen.
		$tests = array(
			get_edit_post_link( $this->factory->post->create() ),
			get_edit_post_link( $this->factory->post->create( array( 'post_type' => 'course' ) ) ),
			get_edit_post_link( $this->factory->post->create( array( 'post_type' => 'page' ) ) ),
			'https://example.tld/wp-admin/widgets.php',
		);

		foreach ( $tests as $test ) {

			$request->set_header( 'referer', $test );

			$expect = array(
				'input',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'relation' => 'OR',
						array(
							'key' => '_is_llms_field',
							'value' => 'yes',
							'compare' => '!=',
						),
						array(
							'key' => '_is_llms_field',
							'compare' => 'NOT EXISTS',
						),
					),
				),
			);
			$res = $this->main->mod_wp_block_query( $args, $request );
			$this->assertEquals( $expect, $res );

		}

	}

}
