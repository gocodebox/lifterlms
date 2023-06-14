<?php
/**
 * Tests for the LLMS_Abstract_Generator_Posts class
 *
 * @group abstracts
 * @group generator
 * @group generator_posts
 *
 * @since 4.7.0
 */
class LLMS_Test_Abstract_Generator_Posts extends LLMS_UnitTestCase {

	/**
	 * Setup the test case
	 *
	 * @since 4.7.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->stub = $this->get_stub();

	}

	/**
	 * Retrieve the abstract class mock stub
	 *
	 * @since 4.7.0
	 *
	 * @return LLMS_Abstract_Generator_Posts
	 */
	private function get_stub( $raw = array() ) {
		return $this->getMockForAbstractClass( 'LLMS_Abstract_Generator_Posts', array( $raw ) );
	}

	/**
	 * Test add_custom_values()
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_add_custom_values() {

		$post_id = $this->factory->post->create();

		$raw = array(
			'custom' => array(
				'_mock_multi'      => array( 1, 2, 3, ),
				'_mock_single'     => array( 'value', ),
				'_mock_empty'      => array( '', ),
				'_mock_serialized' => array( serialize( array( 'data' => true ) ) ),
				'_mock_json'       => array( '{"data":"string"}' ),
			),
		);

		$this->stub->add_custom_values( $post_id, $raw );

		$this->assertEquals( array( 1, 2, 3 ), get_post_meta( $post_id, '_mock_multi' ) );
		$this->assertEquals( 'value', get_post_meta( $post_id, '_mock_single', true ) );
		$this->assertEquals( '', get_post_meta( $post_id, '_mock_empty', true ) );
		$this->assertEquals( array( 'data' => true ), get_post_meta( $post_id, '_mock_serialized', true ) );
		$this->assertEquals( '{"data":"string"}', get_post_meta( $post_id, '_mock_json', true ) );
	}

	/**
	 * Test create_post() success
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_create_post() {

		$res = LLMS_Unit_Test_Util::call_method( $this->stub, 'create_post', array( 'course', array( 'title' => 'test' ) ) );
		$this->assertInstanceOf( 'LLMS_Course', $res );
		$this->assertEquals( 'test', $res->get( 'title' ) );

	}

	/**
	 * Test create_post() for invalid post type classes
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_create_post_invalid_type() {

		$this->setExpectedException( Exception::class, 'The class "LLMS_Fake_Type" does not exist.', 1100 );
		LLMS_Unit_Test_Util::call_method( $this->stub, 'create_post', array( 'fake_type' ) );

	}

	/**
	 * Test create_post() when an error is encountered during creation
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_create_post_error() {

		// Force post creation to fail.
		$handler = function( $args ) {
			return array();
		};
		add_filter( 'llms_new_course', $handler );

		$this->setExpectedException( Exception::class, 'Error creating the course post object.', 1000 );
		LLMS_Unit_Test_Util::call_method( $this->stub, 'create_post', array( 'course', array( 'title' => '' ) ) );

		remove_filter( 'llms_new_course', $handler );

	}

	/**
	 * Test create_reusable_block() when the block already exists
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_create_reusable_block_already_exists() {

		$title   = 'Dupcheck reuse block';
		$content = 'Block content';

		$dup = $this->factory->post->create( array(
			'post_type' => 'wp_block',
			'post_title' => $title,
			'post_content' => $content,
		) );

		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->stub, 'create_reusable_block', array( $dup, compact( 'title', 'content' ) ) ) );

	}

	/**
	 * Test create_reusable_block() when there's an error creating the block
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_create_reusable_block_error() {

		// Force an error response.
		add_filter( 'wp_insert_post_empty_content', '__return_true' );
		$block_post_id = $this->factory->post->create();

		$this->assertFalse(
			LLMS_Unit_Test_Util::call_method( $this->stub, 'create_reusable_block',
			array(
				is_wp_error( $block_post_id ) ? 0 : $block_post_id,
				array(
					'title' => '',
					'content' => '',
				)
			)
		) );

		remove_filter( 'wp_insert_post_empty_content', '__return_true' );

	}

	/**
	 * Test create_reusable_block() for success
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_create_reusable_block_success() {

		$orig_id = $this->factory->post->create();

		$title   = 'Reusable block title';
		$content = 'Reusable block content';

		$id = LLMS_Unit_Test_Util::call_method( $this->stub, 'create_reusable_block', array( $orig_id,  compact( 'title', 'content' ) ) );
		$post = get_post( $id );

		$this->assertTrue( is_numeric( $id ) );
		$this->assertEquals( 'wp_block', $post->post_type );
		$this->assertEquals( $title, $post->post_title );
		$this->assertEquals( $content, $post->post_content );

		$blocks = LLMS_Unit_Test_Util::get_private_property_value( $this->stub, 'reusable_blocks' );
		$this->assertEquals( $id, $blocks[ $orig_id ] );

	}

	/**
	 * Test format_date()
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_format_date() {

		// No date supplied, use current time.
		$expect = '2020-03-25 09:54:12';
		llms_tests_mock_current_time( $expect );

		$this->assertEquals( $expect, $this->stub->format_date() );

		llms_tests_reset_current_time();

		// Format is okay.
		$this->assertEquals( '2015-03-02 23:12:32', $this->stub->format_date( '2015-03-02 23:12:32' ) );

		// Missing time.
		$this->assertEquals( '2019-01-01 00:00:00', $this->stub->format_date( '2019-01-01' ) );

		// Valid format.
		$this->assertEquals( '2019-01-01 00:00:00', $this->stub->format_date( 'January 1, 2019' ) );

	}

	public function test_get_author_id_no_id_or_email() {

		$uid = $this->factory->user->create();
		wp_set_current_user( $uid );

		$this->assertEquals( $uid, LLMS_Unit_Test_Util::call_method( $this->stub, 'get_author_id', array( array() ) ) );

	}

	public function test_get_author_id() {

		$email = 'mockauthor@test.tld';
		$uid   = $this->factory->user->create( array( 'user_email' => $email ) );

		// Only email.
		$this->assertEquals( $uid, LLMS_Unit_Test_Util::call_method( $this->stub, 'get_author_id', array( array(
			'email' => $email,
		) ) ) );

		// Only ID.
		$this->assertEquals( $uid, LLMS_Unit_Test_Util::call_method( $this->stub, 'get_author_id', array( array(
			'id'    => $uid,
		) ) ) );

		// ID & EMail and the email matches the existing user.
		$this->assertEquals( $uid, LLMS_Unit_Test_Util::call_method( $this->stub, 'get_author_id', array( array(
			'id'    => $uid,
			'email' => $email,
		) ) ) );

		// ID & email and the email does not match the existing user.
		$res = LLMS_Unit_Test_Util::call_method( $this->stub, 'get_author_id', array( array(
			'id'    => $uid,
			'email' => 'adifferentemail@test.tld',
		) ) );
		$this->assertEquals( 'adifferentemail@test.tld', get_user_by( 'ID', $res )->user_email );

		// User doesn't exist, create a new one.
		$res = LLMS_Unit_Test_Util::call_method( $this->stub, 'get_author_id', array( array(
			'id'    => $res + 1,
			'email' => 'anotheremail@test.tld',
		) ) );
		$this->assertEquals( 'anotheremail@test.tld', get_user_by( 'ID', $res )->user_email );

		// Email only, create a new user.
		$raw = array(
			'email'       => 'el_duderino@earthlink.net',
			'first_name'  => 'Jeffrey',
			'last_name'   => 'Lebowski',
			'description' => "Nobody calls me Lebowski. You got the wrong guy. I'm the Dude, man.",
		);
		$res = LLMS_Unit_Test_Util::call_method( $this->stub, 'get_author_id', array( $raw ) );
		$user = get_user_by( 'ID', $res );
		$this->assertEquals( $raw['email'], $user->user_email );
		$this->assertEquals( $raw['first_name'] . ' ' . $raw['last_name'], $user->display_name );
		$this->assertEquals( $raw['first_name'], $user->first_name );
		$this->assertEquals( $raw['last_name'], $user->last_name );
		$this->assertEquals( $raw['description'], $user->description );
		$this->assertTrue( $user->has_cap( 'administrator' ) ); // Default role.

		// Pass in a role.
		$res = LLMS_Unit_Test_Util::call_method( $this->stub, 'get_author_id', array( array(
			'email' => 'instructoruser@test.tld',
			'role'  => 'instructor',
		) ) );
		$this->assertTrue( get_user_by( 'ID', $res )->has_cap( 'instructor' ) );

	}

	/**
	 * Test get_author_id() when an error creating the user is encountered.
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_get_author_id_error() {

		// Error during creation.
		$handler = function( $data ) {
			$data['user_login'] = '';
			return $data;
		};
		add_filter( 'llms_generator_new_author_data', $handler );
		$this->setExpectedException( Exception::class, 'Cannot create a user with an empty login name.', 1002 );
		LLMS_Unit_Test_Util::call_method( $this->stub, 'get_author_id', array( array( 'email' => 'fake@test.tld' ) ) );
		remove_filter( 'llms_generator_new_author_data', $handler );

	}

	/**
	 * Test get_author_id_from_raw()
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_get_author_id_from_raw() {

		$user = $this->factory->user->create();

		// Retrievable from raw.
		$this->assertEquals( $user, $this->stub->get_author_id_from_raw( array( 'author' => array( 'id' => $user ) ) ) );

		// No raw submitted & no fallback, use current user.
		wp_set_current_user( $user );
		$this->assertEquals( $user, $this->stub->get_author_id_from_raw( array() ) );

		// Use fallback id.
		$this->assertEquals( 832, $this->stub->get_author_id_from_raw( array(), 832 ) );

	}

	/**
	 * Test default post status getter & setter
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_get_set_default_post_status() {

		// Default.
		$this->assertEquals( 'draft', $this->stub->get_default_post_status() );

		// Modify.
		$this->stub->set_default_post_status( 'publish' );
		$this->assertEquals( 'publish', $this->stub->get_default_post_status() );

	}

	/**
	 * Test get_term_id()
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_get_term_id() {

		$name = 'mock generator term';
		$tax  = 'course_cat';

		// Create a term that doesn't already exist.
		$id = LLMS_Unit_Test_Util::call_method( $this->stub, 'get_term_id', array( $name, $tax ) );

		$term = get_term_by( 'id', $id, $tax );
		$this->assertTrue( is_numeric( $id ) );
		$this->assertEquals( $name, $term->name );

		// Already exists.
		$this->assertEquals( $id, LLMS_Unit_Test_Util::call_method( $this->stub, 'get_term_id', array( $name, $tax ) ) );

	}

	/**
	 * Test get_term_id() when an error is encountered during creation of a new term
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_get_term_id_error() {

		$handler = function( $term ) {
			return new WP_Error( 'mock-term-insert-err', 'Error' );
		};
		add_filter( 'pre_insert_term', $handler );
		$this->setExpectedException( Exception::class, 'Error creating new term "mock gen term".', 1001 );
		LLMS_Unit_Test_Util::call_method( $this->stub, 'get_term_id', array( 'mock gen term', 'course_cat' ) );
		remove_filter( 'pre_insert_term', $handler );

	}

	/**
	 * Test handle_reusable_blocks() when importing is disabled
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_handle_reusable_blocks_disabled() {

		add_filter( 'llms_generator_is_reusable_block_importing_enabled', '__return_false' );
		$this->assertNull( LLMS_Unit_Test_Util::call_method( $this->stub, 'handle_reusable_blocks', array( 1, 2 ) ) );
		remove_filter( 'llms_generator_is_reusable_block_importing_enabled', '__return_false' );

	}

	/**
	 * Test handle_reusable_blocks() when no blocks to import
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_handle_reusable_blocks_none() {

		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->stub, 'handle_reusable_blocks', array( 1, array() ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->stub, 'handle_reusable_blocks', array( 1, array( '_extras' => array() ) ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->stub, 'handle_reusable_blocks', array( 1, array( '_extras' => array( 'blocks' => array() ) ) ) ) );

	}

	/**
	 * Test handle_reusable_blocks() when no blocks to import
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_handle_reusable_blocks() {

		$html = serialize_blocks( array(
			array(
				'blockName' => 'core/block',
				'innerContent' => array( '' ),
				'attrs' => array(
					'ref' => 123,
				)
			),
			array(
				'blockName'    => 'core/paragraph',
				'innerContent' => array( 'Lorem ipsum dolor sit.' ),
				'attrs'        => array(),
			),
			array(
				'blockName' => 'core/block',
				'innerContent' => array( '' ),
				'attrs' => array(
					'ref' => 456,
				)
			),
		) );

		$course_id = $this->factory->post->create( array(
			'post_content' => $html,
			'post_type'    => 'course',
		) );
		$course    = llms_get_post( $course_id );

		$raw = array(
			'_extras' => array(
				'blocks' => array(
					'123' => array(
						'title'  => 'Mock Block 1',
						'content' => 'mock content 1'
					),
					'456' => array(
						'title'  => 'Mock Block 2',
						'content' => 'mock content 2'
					),
				),
			),
		);

		$res = LLMS_Unit_Test_Util::call_method( $this->stub, 'handle_reusable_blocks', array( $course, $raw ) );

		// Proper return.
		$this->assertTrue( $res );

		// Post content updated with newly created blocks.
		$block = parse_blocks( llms_get_post( $course_id )->get( 'content', true ) );

		$this->assertEquals( 'core/block', $block[0]['blockName'] );
		$this->assertNotEquals( 123, $block[0]['attrs']['ref'] );
		$block1 = get_post( $block[0]['attrs']['ref'] );
		$this->assertEquals( 'Mock Block 1', $block1->post_title );
		$this->assertEquals( 'mock content 1', $block1->post_content );

		$this->assertEquals( 'core/paragraph', $block[1]['blockName'] );

		$this->assertEquals( 'core/block', $block[2]['blockName'] );
		$this->assertNotEquals( 456, $block[2]['attrs']['ref'] );
		$block2 = get_post( $block[2]['attrs']['ref'] );
		$this->assertEquals( 'Mock Block 2', $block2->post_title );
		$this->assertEquals( 'mock content 2', $block2->post_content );

	}

	/**
	 * Test is_image_sideloading_enabled()
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_is_image_sideloading_enabled() {
		$this->assertTrue( $this->stub->is_image_sideloading_enabled() );
	}

	/**
	 * Test is_reusable_block_importing_enabled()
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_is_reusable_block_importing_enabled() {
		$this->assertTrue( $this->stub->is_reusable_block_importing_enabled() );
	}

	/**
	 * Test set_featured_image()
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_set_featured_image() {

		$tests = array(
			// String.
			'https://raw.githubusercontent.com/gocodebox/lifterlms/trunk/tests/assets/christian-fregnan-unsplash.jpg',
			// Parse from raw.
			array( 'featured_image' => 'https://raw.githubusercontent.com/gocodebox/lifterlms/trunk/tests/assets/christian-fregnan-unsplash.jpg' ),
		);

		foreach ( $tests as $arg ) {

			$post_id = $this->factory->post->create();
			$id      = LLMS_Unit_Test_Util::call_method( $this->stub, 'set_featured_image', array( $arg, $post_id ) );

			$this->assertTrue( is_numeric( $id ) );
			$this->assertEquals( $id, get_post_thumbnail_id( $post_id ) );

		}

		// No image.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->stub, 'set_featured_image', array( array(), $post_id ) ) );

		// Error.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->stub, 'set_featured_image', array( 'fake', $post_id ) ) );

		// Disabled.
		add_filter( 'llms_generator_is_image_sideloading_enabled', '__return_false' );
		$this->assertNull( LLMS_Unit_Test_Util::call_method( $this->stub, 'set_featured_image', array( 'fake', $post_id ) ) );
		remove_filter( 'llms_generator_is_image_sideloading_enabled', '__return_false' );

	}

	/**
	 * Test sideload_image()
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_sideload_image() {

		$post = $this->factory->post->create();
		$url  = 'https://raw.githubusercontent.com/gocodebox/lifterlms/trunk/tests/assets/christian-fregnan-unsplash.jpg';

		$res = LLMS_Unit_Test_Util::call_method( $this->stub, 'sideload_image', array( $post, $url ) );

		$this->assertStringNotContains( 'raw.githubusercontent', $res );
		$this->assertStringContains( 'christian-fregnan-unsplash', $res );

		// Image already sideloaded so it's not sideloaded again.
		$res2 = LLMS_Unit_Test_Util::call_method( $this->stub, 'sideload_image', array( $post, $url ) );
		$this->assertEquals( $res, $res2 );

		// Test ID return.
		$id = LLMS_Unit_Test_Util::call_method( $this->stub, 'sideload_image', array( $post, $url, 'id' ) );
		$this->assertTrue( is_numeric( $id ) );
		$this->assertEquals( $res2, wp_get_attachment_url( $id ) );

	}

	/**
	 * Test sideload_image() error
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_sideload_image_error() {

		$post = $this->factory->post->create();
		$url  = 'fake.jpg';

		$res = LLMS_Unit_Test_Util::call_method( $this->stub, 'sideload_image', array( $post, $url ) );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'http_request_failed', $res );

	}

	/**
	 * Test sideload_images()
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_sideload_images() {

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

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->stub, 'sideload_images', array( $course, $raw ) ) );
		$this->assertStringNotContains( 'raw.githubusercontent', $course->post->post_content );

	}

	/**
	 * Test sideload_images(): skip sideloading of images from the same site.
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_sideload_images_from_same_site() {

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

		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->stub, 'sideload_images', array( $course, $raw ) ) );
		$this->assertEquals( '<img src="https://example.org/fake-image.png" />', $course->post->post_content );


	}

	/**
	 * Test sideload_images() with no images in post content
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_sideload_images_none() {

		$course = llms_get_post( $this->factory->post->create( array( 'post_type' => 'course' ) ) );

		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->stub, 'sideload_images', array( $course, array() ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->stub, 'sideload_images', array( $course, array( '_extras' => array() ) ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->stub, 'sideload_images', array( $course, array( '_extras' => array( 'images' => array() ) ) ) ) );

	}

	/**
	 * Test sideload_images() with sideloading disabled
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_sideload_images_disabled() {

		$course = llms_get_post( $this->factory->post->create( array( 'post_type' => 'course' ) ) );

		add_filter( 'llms_generator_is_image_sideloading_enabled', '__return_false' );
		$this->assertNull( LLMS_Unit_Test_Util::call_method( $this->stub, 'sideload_images', array( $course, array() ) ) );
		remove_filter( 'llms_generator_is_image_sideloading_enabled', '__return_false' );

	}

}
