<?php
/**
 * Tests for LifterLMS User Postmeta functions
 * @group    functions
 * @group    content_functions
 * @since    3.25.1
 * @version  3.25.1
 */
class LLMS_Test_Functions_Content extends LLMS_UnitTestCase {

	public function get_post_content( $post ) {
		return trim( apply_filters( 'the_content', $post->post_content ) );
	}

	public function test_llms_get_post_content() {

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

}
