<?php
/**
 * Tests for earned user certificates
 *
 * @group models
 * @group certificates
 * @group engagements
 * @group LLMS_User_Certificate
 *
 * @since 4.5.0
 * @since [version] Added tests for the new methods.
 */
class LLMS_Test_LLMS_User_Certificate extends LLMS_PostModelUnitTestCase {

	/**
	 * Class name for the model being tested by the class
	 *
	 * @var string
	 */
	protected $class_name = 'LLMS_User_Certificate';

	/**
	 * DB post type of the model being tested
	 *
	 * @var string
	 */
	protected $post_type = 'llms_my_certificate';

	/**
	 * Get data to fill a create post with
	 *
	 * This is used by test_getters_setters.
	 *
	 * @since 4.5.0
	 * @since [version] Add new properties.
	 *
	 * @return array
	 */
	protected function get_data() {
		return array(
			'allow_sharing' => 'no',
			'background'    => '#eaeaea',
			'engagement'    => 3,
			'height'        => 5.5,
			'margins'       => array( 2, 3, 0.5, 1.83 ),
			'orientation'   => 'landscape',
			'parent'        => 2,
			'related'       => 4,
			'sequential_id' => 5,
			'size'          => 'A4',
			'unit'          => 'mm',
			'width'         => 230,
		);
	}

	/**
	 * Setup before class
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();
		llms()->certificates();

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

		$test = new LLMS_User_Certificate( $id );

		$this->assertEquals( $id, $test->get( 'id' ) );
		$this->assertEquals( $this->post_type, $test->get( 'type' ) );
		$this->assertEquals( 'test title', $test->get( 'title' ) );

	}

	/**
	 * Test the create_after() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_after() {

		$actions = did_action( 'llms_certificate_synchronized' );

		$template = $this->create_certificate_template();
		update_post_meta( $template, '_llms_sequential_id', 25 );

		$cert = new LLMS_User_Certificate( 'new', array( 'post_parent' => $template ) );

		$this->assertEquals( 26, $cert->get( 'sequential_id' ) );
		$this->assertEquals( ++$actions, did_action( 'llms_certificate_synchronized' ) );

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
		$earned   = $this->earn_certificate( $uid, $this->create_certificate_template(), $this->factory->post->create() );
		$cert_id  = $earned[1];
		$cert = new LLMS_User_Certificate( $cert_id );

		$actions = array(
			'before' => did_action( 'llms_before_delete_certificate' ),
			'after'  => did_action( 'llms_delete_certificate' ),
		);

		$cert->delete();

		// User meta is gone.
		$res = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE user_id = {$uid} AND meta_key = '_certificate_earned' AND meta_value = {$cert_id}" );
		$this->assertEquals( array(), $res );

		// Post is deleted.
		$this->assertNull( get_post( $cert_id ) );

		// Ran actions.
		$this->assertEquals( ++$actions['before'], did_action( 'llms_before_delete_certificate' ) );
		$this->assertEquals( ++$actions['after'], did_action( 'llms_delete_certificate' ) );

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
	 * Test get_background()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_background() {

		$this->create();
		$this->assertEquals( '#ffffff', $this->obj->get_background() );

		$this->obj->set( 'background', '#eaeaea' );
		$this->assertEquals( '#eaeaea', $this->obj->get_background() );

	}

	/**
	 * Test get_background_image()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_background_image() {

		// Default.
		$cert = llms_get_certificate( $this->factory->post->create( array( 'post_type' => $this->post_type ) ) );

		$img = $cert->get_background_image();
		$this->assertTrue( $img['is_default'] );
		$this->assertEquals( 800, $img['width'] );
		$this->assertEquals( 616, $img['height'] );
		$this->assertStringContainsString( 'default-certificate.png', $img['src'] );

		// Has image.
		$attachment = $this->create_attachment( 'yura-timoshenko-R7ftweJR8ks-unsplash.jpeg' );
		set_post_thumbnail( $cert->get( 'id' ), $attachment );

		$img = $cert->get_background_image();
		$this->assertFalse( $img['is_default'] );
		$this->assertEquals( 462, $img['width'] );
		$this->assertEquals( 616, $img['height'] );
		$this->assertMatchesRegularExpression(
			'#http:\/\/example.org\/wp-content\/uploads\/\d{4}\/\d{2}\/yura-timoshenko-R7ftweJR8ks-unsplash(?:-\d+).jpeg#',
			$img['src']
		);

	}

	/**
	 * Test get_dimension(), get_height(), get_width(), and get_unit()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_dimensions() {

		$this->create();

		// Letter.
		$this->obj->set( 'size', 'LETTER' );

		$this->assertEquals( 'in', $this->obj->get_unit() );

		$this->assertEquals( 8.5, $this->obj->get_width() );
		$this->assertEquals( '8.5in', $this->obj->get_width( true ) );

		$this->assertEquals( 11, $this->obj->get_height() );
		$this->assertEquals( '11in', $this->obj->get_height( true ) );

		// A4.
		$this->obj->set( 'size', 'A4' );

		$this->assertEquals( 'mm', $this->obj->get_unit() );

		$this->assertEquals( 210, $this->obj->get_width() );
		$this->assertEquals( '210mm', $this->obj->get_width( true ) );

		$this->assertEquals( 297, $this->obj->get_height() );
		$this->assertEquals( '297mm', $this->obj->get_height( true ) );

		// Custom.
		$this->obj->set( 'size', 'CUSTOM' );
		$this->obj->set( 'unit', 'in' );
		$this->obj->set( 'width', 20 );
		$this->obj->set( 'height', 25 );

		$this->assertEquals( 'in', $this->obj->get_unit() );

		$this->assertEquals( 20, $this->obj->get_width() );
		$this->assertEquals( '20in', $this->obj->get_width( true ) );

		$this->assertEquals( 25, $this->obj->get_height() );
		$this->assertEquals( '25in', $this->obj->get_height( true ) );

	}

	/**
	 * Test get_dimensions_for_display()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_dimensions_for_display() {

		$this->create();

		$dimensions = $this->obj->get_dimensions_for_display();
		$this->assertEquals( '8.5in', $dimensions['height'] );
		$this->assertEquals( '11in', $dimensions['width'] );

		// Flip orientation.
		$this->obj->set( 'orientation', 'portrait' );
		$dimensions = $this->obj->get_dimensions_for_display();
		$this->assertEquals( '11in', $dimensions['height'] );
		$this->assertEquals( '8.5in', $dimensions['width'] );

	}

	/**
	 * Test get_margins()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_margins() {

		$this->create();

		$this->assertEquals( array( 5, 5, 5, 5 ), $this->obj->get_margins() );
		$this->assertEquals( array( '5%', '5%', '5%', '5%' ), $this->obj->get_margins( true ) );

	}

	/**
	 * Test get_orientation()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_orientation() {

		$this->create();
		$this->assertEquals( 'landscape', $this->obj->get_orientation() );

		$this->obj->set( 'orientation', 'portrait' );
		$this->assertEquals( 'portrait', $this->obj->get_orientation() );

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
		$earned   = $this->earn_certificate( $uid, $this->create_certificate_template(), $related );
		$cert_id  = $earned[1];
		$cert = new LLMS_User_Certificate( $cert_id );

		$this->assertEquals( $related, $cert->get_related_post_id() );

	}

	/**
	 * Test get_sequential_id()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_sequential_id() {

		$this->create();

		$ids = array(
			1       => '000001',
			25      => '000025',
			302     => '000302',
			4999    => '004999',
			12032   => '012032',
			932012  => '932012',
			// Longer than the default max length of 6.
			1329101 => '1329101',
		);

		foreach( $ids as $raw => $formatted ) {

			$this->obj->set( 'sequential_id', $raw );
			$this->assertEquals( $formatted, $this->obj->get_sequential_id() );

		}

	}

	/**
	 * Test get_size()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_size() {

		$this->create();
		$this->assertEquals( 'LETTER', $this->obj->get_size() );

		$this->obj->set( 'size', 'A3' );
		$this->assertEquals( 'A3', $this->obj->get_size() );

	}

	/**
	 * Test get_template_version()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_template_version() {

		$this->create();

		// No content.
		$this->assertEquals( 2, $this->obj->get_template_version() );

		// Some blocks.
		$blocks = serialize_blocks( array(
			array(
				'blockName'    => 'core/paragraph',
				'innerContent' => array( 'Lorem ipsum dolor sit.' ),
				'attrs'        => array(),
			),
		) );
		$this->obj->set( 'content', $blocks );
		$this->assertEquals( 2, $this->obj->get_template_version() );

		// Content & no blocks.
		$this->obj->set( 'content', 'No blocks' );
		$this->assertEquals( 1, $this->obj->get_template_version() );


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
		$earned   = $this->earn_certificate( $uid, $this->create_certificate_template(), $related );
		$cert_id  = $earned[1];
		$cert = new LLMS_User_Certificate( $cert_id );

		$this->assertEquals( $uid, $cert->get_user_id() );

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
		$earned   = $this->earn_certificate( $uid, $this->create_certificate_template(), $related );
		$cert_id  = $earned[1];
		$cert = new LLMS_User_Certificate( $cert_id );

		$expect = new stdClass();
		$expect->user_id = $uid;
		$expect->post_id = $related;
		$this->assertEquals( $expect, $cert->get_user_postmeta() );

	}

	/**
	 * Test merge_content()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_merge_content_and_sync() {

		LLMS_Install::create_pages();

		$user_info = array(
			'first_name' => 'Walter',
			'last_name' => 'Sobchak',
			'user_email' => 'mergecontentcertuser@mail.tld',
			'user_login' => 'mergecontentcertuser'
		);

		$user    = $this->factory->student->create_and_get( $user_info );
		$related = $this->factory->post->create();

		$content = '';
		$expected_content = '';

		$merge_codes = llms_get_certificate_merge_codes();
		// Add user info shortcodes.
		$merge_codes['[llms-user display_name]'] = 'Display Name';

		foreach ( $merge_codes as $code => $desc ) {

			// Build the actual content for the template.
			$content .= "{$desc}: {$code}\n\n";

			// Build the expected content of the earned cert after merging.
			$expected = '';
			switch ( $code ) {

				case '{site_title}':
					$expected = 'Test Blog';
					break;
				case '{site_url}':
					$expected = get_permalink( llms_get_page_id( 'myaccount' ) );
					break;
				case '{current_date}':
					$expected = date_i18n( get_option( 'date_format' ), current_time( 'timestamp' ) );
					break;
				case '{email_address}':
					$expected = $user_info['user_email'];
					break;
				case '{first_name}':
					$expected = $user_info['first_name'];
					break;
				case '{last_name}':
					$expected = $user_info['last_name'];
					break;
				case '{student_id}':
					$expected = $user->get( 'id' );
					break;
				case '{user_login}':
					$expected = $user_info['user_login'];
					break;
				case '{certificate_id}':
					$expected = '[[CERTID]]';
					break;
				case '{sequential_id}':
					$expected = '000001';
					break;

				case '[llms-user display_name]':
					$expected = "{$user_info['first_name']} {$user_info['last_name']}";
					break;

			}

			$expected_content .= "{$desc}: {$expected}\n\n";

		}

		$template = $this->create_certificate_template( 'Title', $content, 456 );
		$cert     = LLMS_Unit_Test_Util::call_method( 'LLMS_Engagement_Handler', 'create', array( 'certificate', $user->get( 'id' ), $template, $related ) );

		// Add the cert id (not available until the earned post exists).
		$expected_content = str_replace( '[[CERTID]]', $cert->get( 'id' ), $expected_content );

		$this->assertEquals( $expected_content, $cert->get( 'content', true ) );

		// Update the template and sync.
		$thumbnail_id = $this->create_attachment( 'christian-fregnan-unsplash.jpg' );
		wp_update_post( array(
			'ID'           => $template,
			'post_content' => 'Updated and {user_login}',
			'post_title'   => 'Template Title',
			'meta_input'   => array(
				'_thumbnail_id' => $thumbnail_id,
			)
		) );

		$this->assertTrue( $cert->sync() );
		$this->assertEquals( "Updated and {$user_info['user_login']}", $cert->get( 'content', true ) );
		$this->assertEquals( 'Title', $cert->get( 'title', true ) );
		$this->assertEquals( $thumbnail_id, get_post_thumbnail_id( $cert->get('id') ) );
	}

	/**
	 * Test get_merge_data() to ensure deprecated hooks run when they're attached.
	 *
	 * @since [version]
	 *
	 * @expectedDeprecated llms_certificate_merge_codes
	 * @expectedDeprecated LLMS_Certificate_User::init
	 *
	 * @return void
	 */
	public function test_get_merge_data_deprecated_hook() {

		$uid      = $this->factory->student->create();
		$earned   = $this->earn_certificate( $uid, $this->create_certificate_template(), $this->factory->post->create() );

		$cert_id  = $earned[1];
		$cert = new LLMS_User_Certificate( $cert_id );

		$handler = function( $codes, $old_cert ) {
			$this->assertInstanceOf( 'LLMS_Certificate_User', $old_cert );
			return $codes;
		};

		add_filter( 'llms_certificate_merge_codes', $handler, 10, 2 );

		LLMS_Unit_Test_Util::call_method( $cert, 'get_merge_data' );

		remove_filter( 'llms_certificate_merge_codes', $handler, 10, 2 );

	}

	/**
	 * Test can_user_manage()
	 *
	 * @since 4.5.0
	 *
	 * @return void
	 */
	public function test_can_user_manage() {

		$admin    = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$other    = $this->factory->student->create();
		$uid      = $this->factory->student->create();
		$related  = $this->factory->post->create();
		$earned   = $this->earn_certificate( $uid, $this->create_certificate_template(), $related );
		$cert_id  = $earned[1];
		$cert = new LLMS_User_Certificate( $cert_id );

		// Other student cannot manage.
		$this->assertFalse( $cert->can_user_manage() );
		$this->assertFalse( $cert->can_user_manage( $other ) );

		// Fake user cannot manage.
		$this->assertFalse( $cert->can_user_manage( $uid + 1 ) );

		// Admin can.
		$this->assertTrue( $cert->can_user_manage( $admin ) );

		// Owner can.
		$this->assertTrue( $cert->can_user_manage( $uid ) );

		// Current user cannot manage.
		$this->assertFalse( $cert->can_user_manage() );

		// Current User Can.
		wp_set_current_user( $admin );
		$this->assertTrue( $cert->can_user_manage() );

		// Current user is owner.
		wp_set_current_user( $uid );
		$this->assertTrue( $cert->can_user_manage() );

	}

	/**
	 * Test can_user_view()
	 *
	 * @since 4.5.0
	 *
	 * @return void
	 */
	public function test_can_user_view() {

		$uid      = $this->factory->student->create();
		$related  = $this->factory->post->create();
		$earned   = $this->earn_certificate( $uid, $this->create_certificate_template(), $related );
		$cert_id  = $earned[1];
		$cert = new LLMS_User_Certificate( $cert_id );

		// Any user that can manage can always view the cert.
		add_filter( 'llms_certificate_can_user_manage', '__return_true' );
		$this->assertTrue( $cert->can_user_view() );
		remove_filter( 'llms_certificate_can_user_manage', '__return_true' );

		add_filter( 'llms_certificate_can_user_manage', '__return_false' );

		// User cannot manage so they cannot view.
		$this->assertFalse( $cert->can_user_view() );

		// Unless sharing is enabled.
		$cert->set( 'allow_sharing', 'yes' );
		$this->assertTrue( $cert->can_user_view() );

		// Explicitly disabled.
		$cert->set( 'allow_sharing', 'no' );
		$this->assertFalse( $cert->can_user_view() );

		remove_filter( 'llms_certificate_can_user_manage', '__return_false' );


	}

	/**
	 * Test is_sharing_enabled()
	 *
	 * @since 4.5.0
	 *
	 * @return void
	 */
	public function test_is_sharing_enabled() {

		$cert = new LLMS_User_Certificate( 'new', 'test' );

		// No set.
		$this->assertFalse( $cert->is_sharing_enabled() );

		// Explicitly disabled.
		$cert->set( 'allow_sharing', 'no' );
		$this->assertFalse( $cert->is_sharing_enabled() );

		// Enabled.
		$cert->set( 'allow_sharing', 'yes' );
		$this->assertTrue( $cert->is_sharing_enabled() );

	}

	/**
	 * Test sync() when an error is encountered.
	 *
	 * @since [version]
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
	 * Test sync() with a v1 template.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sync_template_v1() {

		$img_id      = $this->create_attachment( 'yura-timoshenko-R7ftweJR8ks-unsplash.jpeg' );
		$title       = 'Sync Template V1';
		$template_id = $this->create_certificate_template( $title, 'ID:{certificate_id}', $img_id );
		$template    = llms_get_certificate( $template_id, true );
		$template->set( 'background', '#000000' );

		$this->create();
		$this->obj->set( 'parent', $template_id );
		$id = $this->obj->get( 'id' );

		$this->assertTrue( $this->obj->sync() );

		// Title and content updated.
		$this->assertEquals( $title, $this->obj->get( 'title' ) );
		$this->assertEquals( "ID:{$id}", $this->obj->get( 'content', true ) );
		$this->assertEquals( $img_id, get_post_thumbnail_id( $id ) );

		// Layout meta isn't synced so it should return the default.
		$this->assertEquals( '#ffffff', $this->obj->get( 'background' ) );

	}

	/**
	 * Test sync() with a v2 template.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sync_template_v2() {

		$img_id      = $this->create_attachment( 'yura-timoshenko-R7ftweJR8ks-unsplash.jpeg' );
		$title       = 'Sync Template V2';
		$content     = serialize_blocks( array(
			array(
				'blockName'    => 'core/paragraph',
				'innerContent' => array( 'ID:{certificate_id}' ),
				'attrs'        => array(),
			),
		) );
		$template_id = $this->create_certificate_template( $title, $content, $img_id );
		$template    = llms_get_certificate( $template_id, true );

		$layout_meta = array(
			'background'  => '#323323',
			'height'      => 25,
			'margins'     => array( 10, 5, 2.5, 1.25 ),
			'orientation' => 'portrait',
			'size'        => 'A3',
			'unit'        => 'mm',
			'width'       => 291,
		);
		$template->set_bulk( $layout_meta );

		$this->create();
		$this->obj->set( 'parent', $template_id );
		$id = $this->obj->get( 'id' );

		$this->assertTrue( $this->obj->sync() );

		// Title and content updated.
		$this->assertEquals( $title, $this->obj->get( 'title' ) );
		$this->assertEquals( "<!-- wp:paragraph -->ID:{$id}<!-- /wp:paragraph -->", $this->obj->get( 'content', true ) );
		$this->assertEquals( $img_id, get_post_thumbnail_id( $id ) );

		// Layout meta isn't synced so it should return the default.
		foreach ( $layout_meta as $prop => $val ) {
			$this->assertEquals( $val, $this->obj->get( $prop ), $prop );
		}

	}

	/**
	 * Test sync a template after removing a thumbnail.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sync_template_after_removing_thumbnail() {

		$img_id      = $this->create_attachment( 'yura-timoshenko-R7ftweJR8ks-unsplash.jpeg' );
		$title       = 'Sync Template Removing Thumbnail';
		$template_id = $this->create_certificate_template( $title, 'ID:{certificate_id}', $img_id );
		$template    = llms_get_certificate( $template_id, true );

		$this->create();
		$this->obj->set( 'parent', $template_id );
		$id = $this->obj->get( 'id' );
		$this->assertTrue( $this->obj->sync() );
		$this->assertEquals( $img_id, get_post_thumbnail_id( $id ) );

		// Remove the template thumbnail.
		delete_post_thumbnail( $template_id );
		// Sync.
		$this->assertTrue( $this->obj->sync() );

		$this->assertFalse( (bool) get_post_thumbnail_id( $id ) );

		$img = $this->obj->get_background_image();
		$this->assertTrue( $img['is_default'] );

	}

}
