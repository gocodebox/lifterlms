<?php
/**
 * Tests for LifterLMS User Postmeta functions
 *
 * @package LifterLMS/Tests
 *
 * @group functions
 * @group content_functions
 *
 * @since 3.25.1
 */
class LLMS_Test_Functions_Content extends LLMS_UnitTestCase {

	public function get_post_content( $post ) {
		return trim( apply_filters( 'the_content', $post->post_content ) );
	}

	public function test_llms_get_post_content() {

		llms_post_content_init();

		$content = '<p>Lorem ipsum dolor sit amet.</p>';
		$post_types = array( 'llms_membership', 'course', 'lesson', 'post', 'page' );
		foreach ( $post_types as $post_type ) {

			global $post;
			$post = $this->factory->post->create_and_get( array(
				'post_type' => $post_type,
				'post_content' => $content,
			) );

			if ( in_array( $post_type, array( 'post', 'page', 'llms_membership' ), true ) ) {
				$this->assertEquals( $content, $this->get_post_content( $post ) );
			} else {
				$this->assertNotEquals( $content, $this->get_post_content( $post ) );
			}

		}

	}

	/**
	 * Test that llms_get_post_content() will return early if the `$post` global is not set.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_get_post_content_no_global() {

		llms_post_content_init();

		$input = 'whatever';
		$this->assertEquals( $input, llms_get_post_content( $input ) );

	}

	/**
	 * Test llms_post_content_init() when filters should be applied
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_post_content_init() {

		remove_filter( 'the_content', 'llms_get_post_content' );

		$this->assertTrue( llms_post_content_init() );
		$this->assertEquals( 10, has_filter( 'the_content', 'llms_get_post_content' ) );

	}

	/**
	 * Test llms_post_content_init() when on the admin panel
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_post_content_init_is_admin() {

		remove_filter( 'the_content', 'llms_get_post_content' );

		set_current_screen( 'admin.php' );

		$this->assertFalse( llms_post_content_init() );
		$this->assertFalse( has_filter( 'the_content', 'llms_get_post_content' ) );

		set_current_screen( 'front' ); // Reset.

	}

	/**
	 * Test llms_post_content_init() during REST requests
	 *
	 * @since [version]
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function test_llms_post_content_init_is_rest() {

		remove_filter( 'the_content', 'llms_get_post_content' );

		define( 'REST_REQUEST', true );
		$this->assertFalse( llms_post_content_init() );
		$this->assertFalse( has_filter( 'the_content', 'llms_get_post_content' ) );

	}

}
