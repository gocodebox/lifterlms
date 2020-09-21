<?php
/**
 * LLMS Generator Tests
 *
 * @group generator
 *
 * @since Unknown
 * @since 3.36.3 Add tests for `is_generator_valid()` and `set_generator()` methods.
 *              Split `is_error()` method tests into multiple tests.
 * @since 3.37.4 Don't test against core metadata.
 * @since [version] Add tests for image sideloading methods.
 */
class LLMS_Test_Generator extends LLMS_UnitTestCase {

	/**
	 * Test create_reusable_block() when the block already exists
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_reusable_block_already_exists() {

		$gen = new LLMS_Generator( array() );

		$title   = 'Dupcheck reuse block';
		$content = 'Block content';

		$dup = $this->factory->post->create( array(
			'post_type' => 'wp-block',
			'post_title' => $title,
			'post_content' => $content,
		) );

		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $gen, 'create_reusable_block', array( $dup, compact( 'title', 'content' ) ) ) );

	}

	/**
	 * Test create_reusable_block() when there's an error creating the block
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_reusable_block_error() {

		$gen = new LLMS_Generator( array() );

		// Force an error response.
		add_filter( 'wp_insert_post_empty_content', '__return_true' );

		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $gen, 'create_reusable_block', array( $this->factory->post->create(), array(
			'title' => '',
			'content' => '',
		) ) ) );

		remove_filter( 'wp_insert_post_empty_content', '__return_true' );

	}

	/**
	 * Test create_reusable_block() for success
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_reusable_block_success() {

		$gen = new LLMS_Generator( array() );

		$orig_id = $this->factory->post->create();

		$title   = 'Reusable block title';
		$content = 'Reusable block content';

		$id = LLMS_Unit_Test_Util::call_method( $gen, 'create_reusable_block', array( $orig_id,  compact( 'title', 'content' ) ) );
		$post = get_post( $id );

		$this->assertTrue( is_numeric( $id ) );
		$this->assertEquals( 'wp-block', $post->post_type );
		$this->assertEquals( $title, $post->post_title );
		$this->assertEquals( $content, $post->post_content );

		$blocks = LLMS_Unit_Test_Util::get_private_property_value( $gen, 'reusable_blocks' );
		$this->assertEquals( $id, $blocks[ $orig_id ] );

	}

	/**
	 * Test generate method.
	 *
	 * @since Unknown.
	 * @since 3.37.4 Don't test against core metadata.
	 *
	 * @return void
	 */
	public function test_generate() {

		$course = $this->get_mock_course_array( 1, 3, 5, 1, 5 );

		$course['author'] = array(
			'email' => 'test@test.tld',
			'id' => 12345,
		);
		$course['categories'] = array( 'cat' );
		$course['tags'] = array( 'tag1', 'tag2' );
		$course['tracks'] = array( 'track' );
		$course['difficulty'] = 'hard';
		$course['access_plans'] = array(
			array(
				'title' => 'plan1'
			),
			array(
				'title' => 'plan2'
			),
		);

		$course['custom'] = array(
			'customdata' => array( 'yes' ),
			'customdata2' => array( 'no', 'yes', 'maybe' ),
			'customdata3' => array( serialize( array( 'no', 'yes', 'maybe' ) ) ),
		);

		$gen = new LLMS_Generator( $course );
		$gen->set_generator( 'LifterLMS/SingleCourseGenerator' );
		$gen->set_default_post_status( 'publish' );
		$gen->generate();

		$this->assertEquals( array(
			'authors' => 1,
			'courses' => 1,
			'sections' => 3,
			'lessons' => 15,
			'quizzes' => 3,
			'questions' => 15,
			'terms' => 5,
			'plans' => 2,
		), $gen->get_results() );

		// ensure custom data is properly added
		$courses = $gen->get_generated_courses();
		$custom  = get_post_custom( $courses[0] );
		unset( $custom['_llms_instructors'] ); // remove core meta data.
		$this->assertEquals( $course['custom'], $custom );


	}

	/**
	 * Test is_error() method: no generator supplied.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_is_error_no_generator() {

		$gen = new LLMS_Generator( array() );
		$gen->generate();
		$this->assertTrue( $gen->is_error() );

	}

	/**
	 * Test is_error() method: valid generator but no data to generate.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_is_error_no_data() {

		$gen = new LLMS_Generator( array() );
		$gen->set_generator( 'LifterLMS/BulkCourseGenerator' );
		$gen->generate();
		$this->assertTrue( $gen->is_error() );

	}

	/**
	 * Test is_error() method: valid generator but data formatted improperly.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_is_error_invalid_data_format() {

		$gen = new LLMS_Generator( array( 'title' => 'course title' ) );
		$gen->set_generator( 'LifterLMS/BulkCourseGenerator' );
		$gen->generate();
		$this->assertTrue( $gen->is_error() );

	}

	/**
	 * Test is_error() method: not an error
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_is_error_not_an_error() {

		$gen = new LLMS_Generator( array( 'title' => 'course title' ) );
		$gen->set_generator( 'LifterLMS/SingleCourseExporter' );
		$gen->generate();
		$this->assertFalse( $gen->is_error() );

	}

	/**
	 * Test is_generator_valid() method: valid generators.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_is_generator_valid_valid_generators() {

		$gen = new LLMS_Generator( array() );
		$list = array_keys( LLMS_Unit_Test_Util::call_method( $gen, 'get_generators' ) );
		foreach ( $list as $name ) {
			$this->assertTrue( LLMS_Unit_Test_Util::call_method( $gen, 'is_generator_valid', array( $name ) ) );
		}

	}

	/**
	 * Test is_generator_valid() method: invalid generators.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_is_generator_valid_invalid() {

		$gen = new LLMS_Generator( array() );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $gen, 'is_generator_valid', array( 'fake' ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $gen, 'is_generator_valid', array( 'LifterLMS/SingleFakeExporter' ) ) );

	}

	/**
	 * Test is_image_sideloading_enabled()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_image_sideloading_enabled() {

		$gen = new LLMS_Generator( array() );
		$this->assertTrue( $gen->is_image_sideloading_enabled() );

	}

	/**
	 * Test is_reusable_block_importing_enabled()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_reusable_block_importing_enabled() {

		$gen = new LLMS_Generator( array() );
		$this->assertTrue( $gen->is_reusable_block_importing_enabled() );

	}

	/**
	 * Test maybe_sideload_choice_image() for various conditions where the choice can't be sideloaded.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_sideload_choice_image_disabled() {

		$gen    = new LLMS_Generator( array() );
		$choice = array(
			'id'     => 'mock',
			'choice' => 'string',
		);

		// The 'choice_type' prop is missing.
		$this->assertEquals( $choice, LLMS_Unit_Test_Util::call_method( $gen, 'maybe_sideload_choice_image', array( $choice, 123 ) ) );

		$choice['choice_type'] = 'text';

		// The 'choice_type' prop is not "image".
		$this->assertEquals( $choice, LLMS_Unit_Test_Util::call_method( $gen, 'maybe_sideload_choice_image', array( $choice, 123 ) ) );

		// Sideloading is disabled.
		add_filter( 'llms_generator_is_image_sideloading_enabled', '__return_false' );
		$this->assertEquals( $choice, LLMS_Unit_Test_Util::call_method( $gen, 'maybe_sideload_choice_image', array( $choice, 123 ) ) );
		remove_filter( 'llms_generator_is_image_sideloading_enabled', '__return_false' );

	}

	/**
	 * Test maybe_sideload_choice_image()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_sideload_choice_image() {

		$gen    = new LLMS_Generator( array() );
		$choice = array(
			'id'          => 'mock',
			'choice_type' => 'image',
			'choice'      => array(
				'id'  => 123,
				'src' => 'https://raw.githubusercontent.com/gocodebox/lifterlms/trunk/tests/assets/christian-fregnan-unsplash.jpg',
			),
		);

		$res = LLMS_Unit_Test_Util::call_method( $gen, 'maybe_sideload_choice_image', array( $choice, 123 ) );

		$this->assertTrue( 123 !== $res['choice']['id'] );
		$this->assertTrue( $choice['choice']['src'] !== $res['choice']['src'] );
		$this->assertEquals( wp_get_attachment_url( $res['choice']['id'] ),  $res['choice']['src'] );

	}

	/**
	 * Test maybe_sideload_choice_image() when an error is encountered during sideloading
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_sideload_choice_image_error() {

		$gen    = new LLMS_Generator( array() );
		$choice = array(
			'id'          => 'mock',
			'choice_type' => 'image',
			'choice'      => array(
				'id'  => 123,
				'src' => 'fake.jpg',
			),
		);

		$this->assertEquals( $choice, LLMS_Unit_Test_Util::call_method( $gen, 'maybe_sideload_choice_image', array( $choice, 123 ) ) );

	}

	/**
	 * Test set_generator(): interpret from raw missing generator.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_set_generator_interpret_from_raw_missing() {

		$gen = new LLMS_Generator( array() );
		$err = $gen->set_generator();
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'missing-generator', $err );

	}

	/**
	 * Test set_generator(): interpret from raw invalid generator.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_set_generator_interpret_from_raw_invalid() {

		$gen = new LLMS_Generator( array(
			'_generator' => 'Fake/Generator',
		) );
		$err = $gen->set_generator();
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'invalid-generator', $err );

	}

	/**
	 * Test set_generator(): interpret from raw success.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_set_generator_interpret_from_raw_success() {

		$gen = new LLMS_Generator( array(
			'_generator' => 'LifterLMS/SingleCourseExporter',
		) );
		$this->assertEquals( 'LifterLMS/SingleCourseExporter', $gen->set_generator() );

	}

	/**
	 * Test set_generator(): explicitly supplied invalid.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_set_generator_explicit_invalid() {

		$gen = new LLMS_Generator( array() );
		$err = $gen->set_generator( 'Fake/Generator' );
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'invalid-generator', $err );

	}

	/**
	 * Test set_generator(): explicitly supplied success.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_set_generator_explicit_success() {

		$gen = new LLMS_Generator( array() );
		$this->assertEquals( 'LifterLMS/SingleCourseExporter', $gen->set_generator( 'LifterLMS/SingleCourseExporter' ) );

	}

	/**
	 * Test sideload_image()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sideload_image() {

		$gen  = new LLMS_Generator( array() );
		$post = $this->factory->post->create();
		$url  = 'https://raw.githubusercontent.com/gocodebox/lifterlms/trunk/tests/assets/christian-fregnan-unsplash.jpg';

		$res = LLMS_Unit_Test_Util::call_method( $gen, 'sideload_image', array( $post, $url ) );

		$this->assertStringNotContains( 'raw.githubusercontent', $res );
		$this->assertStringContains( 'christian-fregnan-unsplash', $res );

		// Image already sideloaded so it's not sideloaded again.
		$res2 = LLMS_Unit_Test_Util::call_method( $gen, 'sideload_image', array( $post, $url ) );
		$this->assertEquals( $res, $res2 );

		// Test ID return.
		$id = LLMS_Unit_Test_Util::call_method( $gen, 'sideload_image', array( $post, $url, 'id' ) );
		$this->assertTrue( is_numeric( $id ) );
		$this->assertEquals( $res2, wp_get_attachment_url( $id ) );

	}

	/**
	 * Test sideload_image() error
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sideload_image_error() {

		$gen  = new LLMS_Generator( array() );
		$post = $this->factory->post->create();
		$url  = 'fake.jpg';

		$res = LLMS_Unit_Test_Util::call_method( $gen, 'sideload_image', array( $post, $url ) );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'http_request_failed', $res );

	}

	/**
	 * Test sideload_images()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sideload_images() {

		$gen    = new LLMS_Generator( array() );
		$course = llms_get_post( $this->factory->post->create( array(
			'post_type'    => 'course',
			'post_content' => '<!-- wp:image {"id":552,"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="https://raw.githubusercontent.com/gocodebox/lifterlms/trunk/tests/assets/christian-fregnan-unsplash.jpg" alt="" class="wp-image-552"/></figure>
<!-- /wp:image -->

<!-- wp:gallery {"ids":[552,11]} -->
<figure class="wp-block-gallery columns-2 is-cropped"><ul class="blocks-gallery-grid">
<li class="blocks-gallery-item"><figure><img src="https://raw.githubusercontent.com/gocodebox/lifterlms/trunk/tests/assets/christian-fregnan-unsplash.jpg" alt="" data-id="552" data-full-url="https://raw.githubusercontent.com/gocodebox/lifterlms/trunk/tests/assets/christian-fregnan-unsplash.jpg" data-link="https://raw.githubusercontent.com/gocodebox/lifterlms/trunk/tests/assets/christian-fregnan-unsplash.jpg" class="wp-image-552"/></figure></li>
<li class="blocks-gallery-item"><figure><img src="https://raw.githubusercontent.com/gocodebox/lifterlms/trunk/tests/assets/richard-i49WGMPd5aA-unsplash.jpg" alt="" data-id="11" data-full-url="https://raw.githubusercontent.com/gocodebox/lifterlms/trunk/tests/assets/richard-i49WGMPd5aA-unsplash.jpg" data-link="https://raw.githubusercontent.com/gocodebox/lifterlms/trunk/tests/assets/richard-i49WGMPd5aA-unsplash.jpg" class="wp-image-11"/></figure></li></ul></figure>
<!-- /wp:gallery -->

<img src="https://raw.githubusercontent.com/gocodebox/lifterlms/trunk/tests/assets/christian-fregnan-unsplash.jpg" alt="" class="wp-image-552"/>'
		) ) );

		$raw = array(
			'_extras' => array(
				'images' => array(
					'https://raw.githubusercontent.com/gocodebox/lifterlms/trunk/tests/assets/christian-fregnan-unsplash.jpg',
					'https://raw.githubusercontent.com/gocodebox/lifterlms/trunk/tests/assets/richard-i49WGMPd5aA-unsplash.jpg',
				),
			),
		);

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $gen, 'sideload_images', array( $course, $raw ) ) );
		$this->assertStringNotContains( 'raw.githubusercontent', $course->post->post_content );

	}

	/**
	 * Test sideload_images(): skip sideloading of images from the same site.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sideload_images_from_same_site() {

		$gen    = new LLMS_Generator( array() );
		$course = llms_get_post( $this->factory->post->create( array(
			'post_type'    => 'course',
			'post_content' => '<img src="https://example.org/fake-image.png" />',
		) ) );

		$raw = array(
			'_extras' => array(
				'images' => array(
					'https://example.org/fake-image.png',
				),
			),
		);

		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $gen, 'sideload_images', array( $course, $raw ) ) );
		$this->assertEquals( '<img src="https://example.org/fake-image.png" />', $course->post->post_content );


	}

	/**
	 * Test sideload_images() with no images in post content
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sideload_images_none() {

		$gen    = new LLMS_Generator( array() );
		$course = llms_get_post( $this->factory->post->create( array( 'post_type' => 'course' ) ) );

		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $gen, 'sideload_images', array( $course, array() ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $gen, 'sideload_images', array( $course, array( '_extras' => array() ) ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $gen, 'sideload_images', array( $course, array( '_extras' => array( 'images' => array() ) ) ) ) );

	}

	/**
	 * Test sideload_images() with sideloading disabled
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sideload_images_disabled() {

		$gen    = new LLMS_Generator( array() );
		$course = llms_get_post( $this->factory->post->create( array( 'post_type' => 'course' ) ) );

		add_filter( 'llms_generator_is_image_sideloading_enabled', '__return_false' );
		$this->assertNull( LLMS_Unit_Test_Util::call_method( $gen, 'sideload_images', array( $course, array() ) ) );
		remove_filter( 'llms_generator_is_image_sideloading_enabled', '__return_false' );

	}

}
