<?php
/**
 * Tests for LifterLMS User Postmeta functions
 *
 * @group functions
 * @group content_functions
 *
 * @since 3.25.1
 * @since [version] Fixed test to handle `llms_page_restriction()` without `is_singular()` reliance.
 */
class LLMS_Test_Functions_Content extends LLMS_UnitTestCase {

	public function get_post_content( $post ) {
		return trim( apply_filters( 'the_content', $post->post_content ) );
	}

	/**
	 * Test llms_get_post_content()
	 *
	 * @todo This is a terrible test that proves almost nothing.
	 *
	 * @since 3.25.1
	 * @since [version] Set an empty excerpt.
	 *
	 * @return void
	 */
	public function test_llms_get_post_content() {

		$content = '<p>Lorem ipsum dolor sit amet.</p>';
		$post_types = array( 'llms_membership', 'course', 'lesson', 'post', 'page' );
		foreach ( $post_types as $post_type ) {

			global $post;
			$post = $this->factory->post->create_and_get( array(
				'post_type' => $post_type,
				'post_content' => $content,
				'post_excerpt' => '',
			) );

			if ( in_array( $post_type, array( 'post', 'page', 'llms_membership' ), true ) ) {
				$this->assertEquals( $content, $this->get_post_content( $post ) );
			} else {
				$this->assertNotEquals( $content, $this->get_post_content( $post ) );
			}

		}

	}

}
