<?php
/**
 * Tests for deprecated functions
 *
 * @group functions
 * @group deprecated
 *
 * @since [version]
 */
class LLMS_Test_Functions_Deprecated extends LLMS_UnitTestCase {

	/**
	 * Test earned engagement deprecated keys against an invalid post type.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_engagement_handle_deprecated_meta_keys_invalid_post() {

		$attachment   = $this->create_attachment( 'yura-timoshenko-R7ftweJR8ks-unsplash.jpeg' );
		$post_title   = 'Actual Title';
		$post_content = wpautop( 'Actual Content' );
		$meta_input = array(
			'_llms_achievement_content' => 'Not the Content',
			'_llms_certificate_content' => 'Not the Content	',
			'_llms_achievement_image' => 123,
			'_llms_certificate_image' => 123,
			'_llms_achievement_title' => 'Not the Title',
			'_llms_certificate_title' => 'Not the Title	',
			'_llms_achievement_template' => 456,
			'_llms_certificate_template' => 456,
		);

		$post = $this->factory->post->create( compact( 'meta_input', 'post_title', 'post_content' ) );
		set_post_thumbnail( $post, $attachment );

		foreach ( $meta_input as $key => $value ) {
			$this->assertEquals( $value, get_post_meta( $post, $key, true ) );
		}

	}

	/**
	 * Test llms_my_certificate deprecated keys
	 *
	 * @since [version]
	 *
	 * @expectedDeprecated LLMS_User_Certificate meta key 'certificate_content'
	 * @expectedDeprecated LLMS_User_Certificate meta key 'certificate_image'
	 * @expectedDeprecated LLMS_User_Certificate meta key 'certificate_title'
	 * @expectedDeprecated LLMS_User_Certificate meta key 'certificate_template'
	 *
	 * @return void
	 */
	public function test_llms_engagement_handle_deprecated_meta_keys_certificate_post() {

		$attachment   = $this->create_attachment( 'yura-timoshenko-R7ftweJR8ks-unsplash.jpeg' );
		$post_type    = 'llms_my_certificate';
		$post_title   = 'Actual Title';
		$post_content = wpautop( 'Actual Content' );
		$post_parent  = $this->factory->post->create();
		$meta_input   = array(
			'_llms_certificate_content'  => 'Not the Content',
			'_llms_certificate_image'    => $attachment + 1,
			'_llms_certificate_title'    => 'Not the Title',
			'_llms_certificate_template' => $post_parent + 1,
		);

		$post = $this->factory->post->create( compact( 'meta_input', 'post_title', 'post_type', 'post_parent', 'post_content' ) );
		set_post_thumbnail( $post, $attachment );

		// Via `get_post_meta()`.
		$this->assertEquals( $post_content, get_post_meta( $post, '_llms_certificate_content', true ) );
		$this->assertEquals( $post_title, get_post_meta( $post, '_llms_certificate_title', true ) );
		$this->assertEquals( $attachment, get_post_meta( $post, '_llms_certificate_image', true ) );
		$this->assertEquals( $post_parent, get_post_meta( $post, '_llms_certificate_template', true ) );

		// Via LLMS_User_Certificate->get().
		$obj = new LLMS_User_Certificate( $post );
		$this->assertEquals( $post_content, $obj->get( 'content', true ) );
		$this->assertEquals( $post_title, $obj->get( 'certificate_title' ) );
		$this->assertEquals( $attachment, $obj->get( 'certificate_image' ) );
		$this->assertEquals( $post_parent, $obj->get( 'certificate_template' ) );

	}

	/**
	 * Test llms_my_achievement deprecated keys
	 *
	 * @since [version]
	 *
	 * @expectedDeprecated LLMS_User_Achievement meta key 'achievement_content'
	 * @expectedDeprecated LLMS_User_Achievement meta key 'achievement_image'
	 * @expectedDeprecated LLMS_User_Achievement meta key 'achievement_title'
	 * @expectedDeprecated LLMS_User_Achievement meta key 'achievement_template'
	 *
	 * @return void
	 */
	public function test_llms_engagement_handle_deprecated_meta_keys_achievement_post() {

		$attachment   = $this->create_attachment( 'yura-timoshenko-R7ftweJR8ks-unsplash.jpeg' );
		$post_type    = 'llms_my_achievement';
		$post_title   = 'Actual Title';
		$post_content = wpautop( 'Actual Content' );
		$post_parent  = $this->factory->post->create();
		$meta_input   = array(
			'_llms_achievement_content'  => 'Not the Content',
			'_llms_achievement_image'    => $attachment + 1,
			'_llms_achievement_title'    => 'Not the Title',
			'_llms_achievement_template' => $post_parent + 1,
		);

		$post = $this->factory->post->create( compact( 'meta_input', 'post_title', 'post_type', 'post_parent', 'post_content' ) );
		set_post_thumbnail( $post, $attachment );

		// Via `get_post_meta()`.
		$this->assertEquals( $post_content, get_post_meta( $post, '_llms_achievement_content', true ) );
		$this->assertEquals( $post_title, get_post_meta( $post, '_llms_achievement_title', true ) );
		$this->assertEquals( $attachment, get_post_meta( $post, '_llms_achievement_image', true ) );
		$this->assertEquals( $post_parent, get_post_meta( $post, '_llms_achievement_template', true ) );

		// Via LLMS_User_Certificate->get().
		$obj = new LLMS_User_Certificate( $post );
		$this->assertEquals( $post_content, $obj->get( 'content', true ) );
		$this->assertEquals( $post_title, $obj->get( 'achievement_title' ) );
		$this->assertEquals( $attachment, $obj->get( 'achievement_image' ) );
		$this->assertEquals( $post_parent, $obj->get( 'achievement_template' ) );

	}

}
