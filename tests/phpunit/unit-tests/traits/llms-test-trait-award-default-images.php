<?php
/**
 * Tests for {@see LLMS_Trait_Award_Default_Images}.
 *
 * @group traits
 * @group awards
 * @group awards_default_images
 *
 * @since [version]
 */
class LLMS_Test_Trait_Award_Default_Images extends LLMS_UnitTestCase {

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->instances = array(
			'achievement' => llms()->achievements(),
			'certificate' => llms()->certificates(),
		);

	}

	/**
	 * Test get_default_default_image_src().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_default_default_image_src() {

		foreach ( $this->instances as $id => $instance ) {

			$this->assertStringContainsString(
				"default-{$id}.png",
				LLMS_Unit_Test_Util::call_method( $instance, 'get_default_default_image_src' )
			);

			add_filter( 'llms_use_legacy_award_images', '__return_true' );
			$this->assertStringContainsString(
				"optional_{$id}.png",
				LLMS_Unit_Test_Util::call_method( $instance, 'get_default_default_image_src' )
			);
			remove_filter( 'llms_use_legacy_award_images', '__return_true' );

		}

	}

	/**
	 * Test get_default_image() and get_default_image_id()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_default_image() {

		foreach ( $this->instances as $id => $instance ) {

			$opt_name = "lifterlms_{$id}_default_img";

			// Non-existent option.
			delete_option( $opt_name );
			$this->assertEquals( 0, $instance->get_default_image_id() );
			$this->assertStringContainsString( "/default-{$id}.png", $instance->get_default_image( 123 ) );

			// Empty option
			update_option( $opt_name, '' );
			$this->assertEquals( 0, $instance->get_default_image_id() );
			$this->assertStringContainsString( "/default-{$id}.png", $instance->get_default_image( 123 ) );

			// Non-existent attachment.
			update_option( $opt_name, 123 );
			$this->assertEquals( 0, $instance->get_default_image_id() );
			$this->assertStringContainsString( "/default-{$id}.png", $instance->get_default_image( 123 ) );

			// A "real" attachment.
			$attachment_id = $this->create_attachment( 'christian-fregnan-unsplash.jpg' );
			update_option( $opt_name, $attachment_id );
			$this->assertEquals( $attachment_id, $instance->get_default_image_id() );
			$this->assertMatchesRegularExpression(
				'#http:\/\/example.org\/wp-content\/uploads\/\d{4}\/\d{2}\/christian-fregnan-unsplash(-)?\d*.jpg#',
				$instance->get_default_image( $attachment_id )
			);

		}


	}

}