<?php
/**
 * Tests for awarded user achievements
 *
 * @group models
 * @group achievements
 * @group engagements
 * @group LLMS_User_Achievement
 *
 * @since 4.5.0
 * @since 6.0.0 Added tests for the new methods.
 */
class LLMS_Test_LLMS_User_Achievement extends LLMS_PostModelUnitTestCase {

	/**
	 * Class name for the model being tested by the class
	 *
	 * @var string
	 */
	protected $class_name = 'LLMS_User_Achievement';

	/**
	 * Will hold an instance of the model being tested by the class.
	 *
	 * @var LLMS_User_Achievement
	 */
	protected $obj;

	/**
	 * DB post type of the model being tested
	 *
	 * @var string
	 */
	protected $post_type = 'llms_my_achievement';

	/**
	 * Get data to fill a create post with
	 *
	 * This is used by test_getters_setters.
	 *
	 * @since 4.5.0
	 * @since 6.0.0 Add new properties.
	 *
	 * @return array
	 */
	protected function get_data() {
		return array(
			'awarded'    => '2021-12-10 23:02:59',
			'engagement' => 3,
			'related'    => 4,
		);
	}

	/**
	 * Setup before class
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();
		llms()->achievements();

	}

	/**
	 * Test the after_create() method.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_after_create() {

		$actions = did_action( 'llms_achievement_synchronized' );

		$template_id = $this->create_achievement_template();

		$this->obj = new $this->class_name( 'new', array( 'post_parent' => $template_id ) );

		$this->assertEquals( ++$actions, did_action( 'llms_achievement_synchronized' ) );
	}

	/**
	 * Test creation of the model
	 *
	 * @since 4.5.0
	 *
	 * @return void
	 */
	public function test_create_model() {

		$this->create( 'test title' );

		$id = $this->obj->get( 'id' );

		$test = new LLMS_User_Achievement( $id );

		$this->assertEquals( $id, $test->get( 'id' ) );
		$this->assertEquals( $this->post_type, $test->get( 'type' ) );
		$this->assertEquals( 'test title', $test->get( 'title' ) );

	}

	/**
	 * Test delete() method
	 *
	 * @since 4.5.0
	 *
	 * @return void
	 */
	public function test_delete() {

		global $wpdb;

		$uid      = $this->factory->student->create();
		$earned   = $this->earn_achievement( $uid, $this->create_achievement_template(), $this->factory->post->create() );
		$achievement_id  = $earned[1];
		$achievement = new LLMS_User_Achievement( $achievement_id );

		$actions = array(
			'before' => did_action( 'llms_before_delete_achievement' ),
			'after'  => did_action( 'llms_delete_achievement' ),
		);

		$achievement->delete();

		// User meta is gone.
		$res = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE user_id = {$uid} AND meta_key = '_achievement_earned' AND meta_value = {$achievement_id}" );
		$this->assertEquals( array(), $res );

		// Post is deleted.
		$this->assertNull( get_post( $achievement_id ) );

		// Ran actions.
		$this->assertEquals( ++$actions['before'], did_action( 'llms_before_delete_achievement' ) );
		$this->assertEquals( ++$actions['after'], did_action( 'llms_delete_achievement' ) );

	}

	/**
	 * Test get_earned_date()
	 *
	 * @since 4.5.0
	 *
	 * @return void
	 */
	public function test_get_earned_date() {

		$this->create();

		$date = $this->obj->post->post_date;

		// Request a format.
		$this->assertEquals( $date, $this->obj->get_earned_date( 'Y-m-d H:i:s' ) );

		// Default blog format.
		$this->assertEquals( date( 'F j, Y', strtotime( $date ) ), $this->obj->get_earned_date() );

	}

	/**
	 * Test get_image()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_get_image() {

		// Default.
		$achievement = new LLMS_User_Achievement( $this->factory->post->create( array( 'post_type' => $this->post_type ) ) );

		$src = $achievement->get_image();
		$this->assertStringContainsString( 'default-achievement.png', $src );

		// Has image.
		$attachment = $this->create_attachment( 'yura-timoshenko-R7ftweJR8ks-unsplash.jpeg' );
		set_post_thumbnail( $achievement->get( 'id' ), $attachment );

		$src = $achievement->get_image();
		$this->assertMatchesRegularExpression(
			'#http:\/\/example.org\/wp-content\/uploads\/\d{4}\/\d{2}\/yura-timoshenko-R7ftweJR8ks-unsplash(?:(-\d+)*(-\d+x\d+)*).jpeg#',
			$src
		);

	}

	/**
	 * Test get_image_html()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_get_image_html() {

		// Default.
		$achievement = new LLMS_User_Achievement( $this->factory->post->create( array( 'post_type' => $this->post_type, 'post_title' => 'Test Title' ) ) );

		$attachment = $this->create_attachment( 'yura-timoshenko-R7ftweJR8ks-unsplash.jpeg' );
		set_post_thumbnail( $achievement->get( 'id' ), $attachment );

		$html = $achievement->get_image_html();

		$this->assertEquals( 0, strpos( $html, '<img ' ) );
		$this->assertStringContainsString( 'alt="Test Title"', $html );
		$this->assertStringContainsString( 'class="llms-achievement-img"', $html );

		$this->assertEquals(
			1,
			preg_match(
				'#src="http:\/\/example.org\/wp-content\/uploads\/\d{4}\/\d{2}\/yura-timoshenko-R7ftweJR8ks-unsplash(?:(-\d+)*(-\d+x\d+)*).jpeg"#',
				$html
			)
		);

	}


	/**
	 * Test get_related_post_id()
	 *
	 * @since 4.5.0
	 *
	 * @return void
	 */
	public function test_get_related_post_id() {

		$uid      = $this->factory->student->create();
		$related  = $this->factory->post->create();
		$earned   = $this->earn_achievement( $uid, $this->create_achievement_template(), $related );
		$achievement_id  = $earned[1];
		$achievement = new LLMS_User_Achievement( $achievement_id );

		$this->assertEquals( $related, $achievement->get_related_post_id() );

	}

	/**
	 * Test get_user_id()
	 *
	 * @since 4.5.0
	 *
	 * @return void
	 */
	public function test_get_user_id() {

		$uid      = $this->factory->student->create();
		$related  = $this->factory->post->create();
		$earned   = $this->earn_achievement( $uid, $this->create_achievement_template(), $related );
		$achievement_id  = $earned[1];
		$achievement = new LLMS_User_Achievement( $achievement_id );

		$this->assertEquals( $uid, $achievement->get_user_id() );

	}

	/**
	 * Test get_user_postmeta()
	 *
	 * @since 4.5.0
	 *
	 * @return void
	 */
	public function test_get_user_postmeta() {

		$uid      = $this->factory->student->create();
		$related  = $this->factory->post->create();
		$earned   = $this->earn_achievement( $uid, $this->create_achievement_template(), $related );
		$achievement_id  = $earned[1];
		$achievement = new LLMS_User_Achievement( $achievement_id );

		$expect = new stdClass();
		$expect->user_id = $uid;
		$expect->post_id = $related;
		$this->assertEquals( $expect, $achievement->get_user_postmeta() );

	}

	/**
	 * Test is_awarded().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_is_awarded() {

		$this->create();

		$this->obj->set( 'status', 'publish' );
		$this->obj->set( 'awarded', '' );

		$this->assertFalse( $this->obj->is_awarded() );

		$this->obj->set( 'awarded', llms_current_time( 'mysql' ) );
		$this->assertTrue( $this->obj->is_awarded() );

		$this->obj->set( 'status', 'draft' );
		$this->assertFalse( $this->obj->is_awarded() );

	}

	/**
	 * Test sync()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_sync() {

		LLMS_Install::create_pages();

		$user_info = array(
			'first_name' => 'Walter',
			'last_name'  => 'Sobchak',
			'user_email' => 'sync-achievement-user@mail.tld',
			'user_login' => 'sync-achievement-user'
		);

		$user    = $this->factory->student->create_and_get( $user_info );
		$related = $this->factory->post->create();

		$content          = '';
		$expected_content = '';

		$template    = $this->create_achievement_template( 'Title', $content, 456 );
		/** @var LLMS_User_Achievement $achievement */
		$achievement = LLMS_Unit_Test_Util::call_method( 'LLMS_Engagement_Handler', 'create', array(
			'achievement',
			$user->get( 'id' ),
			$template,
			$related
		) );

		$this->assertEquals( $expected_content, $achievement->get( 'content', true ) );

		// Update the template and sync.
		$thumbnail_id = $this->create_attachment( 'christian-fregnan-unsplash.jpg' );
		wp_update_post( array(
			'ID'           => $template,
			'post_content' => 'Updated',
			'post_title'   => 'Template Title',
			'meta_input'   => array(
				'_thumbnail_id' => $thumbnail_id,
			)
		) );

		$this->assertTrue( $achievement->sync() );
		$this->assertEquals( 'Updated', $achievement->get( 'content', true ) );
		$this->assertEquals( 'Title', $achievement->get( 'title', true ) );
		$this->assertEquals( $thumbnail_id, get_post_thumbnail_id( $achievement->get( 'id' ) ) );
	}

	/**
	 * Test sync() when an error is encountered.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_sync_errors() {

		$this->create();
		$this->obj->set( 'parent', $this->factory->post->create() + 1 );

		// This is just testing that an error is returned, the rest of the conditions are tested against LLMS_Engagement_Handler::check_post() directly.
		$this->assertFalse( $this->obj->sync() );
	}

	/**
	 * Test syncing an awarded engagement with its template after removing a thumbnail.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_sync_template_after_removing_thumbnail() {

		// Create a template with a thumbnail.
		$img_id      = $this->create_attachment( 'yura-timoshenko-R7ftweJR8ks-unsplash.jpeg' );
		$title       = 'Sync Template Removing Thumbnail';
		$template_id = $this->create_achievement_template( $title, null, $img_id );

		// Create an awarded engagement.
		$this->create( array( 'post_parent' => $template_id ) );

		// Test that the awarded engagement matches the template.
		$id = $this->obj->get( 'id' );
		$this->assertEquals( $img_id, get_post_thumbnail_id( $id ) );

		// Remove the template thumbnail.
		delete_post_thumbnail( $template_id );

		// Sync the awarded engagement with the template.
		$this->assertTrue( $this->obj->sync() );

		// Test that the awarded engagement no longer has a thumbnail.
		$this->assertFalse( (bool) get_post_thumbnail_id( $id ) );
	}

	/**
	 * Test that syncing an awarded engagement with its template twice keeps the same thumbnail.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_sync_template_twice_keep_thumbnail() {

		// Create a template with a thumbnail.
		$img_id      = $this->create_attachment( 'yura-timoshenko-R7ftweJR8ks-unsplash.jpeg' );
		$title       = 'Sync Template Twice with Thumbnail';
		$template_id = $this->create_achievement_template( $title, null, $img_id );

		// Create an awarded engagement.
		$this->create( array( 'post_parent' => $template_id ) );
		$id = $this->obj->get( 'id' );

		// Test that the awarded engagement matches the template.
		$this->assertEquals( $img_id, get_post_thumbnail_id( $id ) );

		// Sync (twice).
		$this->assertTrue( $this->obj->sync() );
		$this->assertTrue( $this->obj->sync() );
		$this->assertEquals( $img_id, get_post_thumbnail_id( $id ) );
	}
}
