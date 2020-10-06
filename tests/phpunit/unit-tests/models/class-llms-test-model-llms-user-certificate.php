<?php
/**
 * Tests for earned user certificates
 *
 * @group models
 * @group certificates
 * @group LLMS_User_Certificate
 *
 * @since 4.5.0
 */
class LLMS_Test_LLMS_User_Certificate extends LLMS_PostModelUnitTestCase {

	/**
	 * Class name for the model being tested by the class
	 *
	 * @var  string
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
	 *
	 * @return   array
	 */
	protected function get_data() {
		return array(
			'certificate_title'    => 'Eaned Cert Title',
			'certificate_image'    => 1,
			'certificate_template' => 2,
			'allow_sharing'        => 'no',
		);
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

}
