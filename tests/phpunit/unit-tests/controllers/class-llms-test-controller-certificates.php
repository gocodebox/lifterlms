<?php
/**
 * Test LLMS_Controller_Certificates.
 *
 * @package LifterLMS/Tests/Controllers
 *
 * @group controllers
 * @group certificates
 * @group controller_certificates
 *
 * @since 3.37.4
 * @since 4.5.0 Added tests for managing certificate sharing settings.
 * @since [version] Added tests for handling awarded certificates sync actions.
 */
class LLMS_Test_Controller_Certificates extends LLMS_UnitTestCase {

	/**
	 * Setup the test case.
	 *
	 * @since 3.37.4
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->instance = new LLMS_Controller_Certificates();

	}

	/**
	 * Setup before loading the class.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();
		// Include admin classes.
		( new LLMS_Loader )->includes_admin();

	}

	/**
	 * Test maybe_allow_public_query(): no authorization data in query string.
	 *
	 * @since 3.37.4
	 *
	 * @return void
	 */
	public function test_maybe_allow_public_query_no_auth() {
		$this->assertEquals( array(), $this->instance->maybe_allow_public_query( array() ) );
	}

	/**
	 * Test maybe_allow_public_query(): authorization present but invalid.
	 *
	 * @since 3.37.4
	 *
	 * @return void
	 */
	public function test_maybe_allow_public_query_invalid_auth() {

		// Doesn't exist.
		$args = array(
			'publicly_queryable' => false,
		);

		$this->mockGetRequest( array(
			'_llms_cert_auth' => 'fake',
		) );

		$this->assertEquals( $args, $this->instance->maybe_allow_public_query( $args ) );

		// Post exists but submitted nocne is incorrect.
		$post_id = $this->factory->post->create( array( 'post_type' => 'llms_certificate' ) );
		update_post_meta( $post_id, '_llms_auth_nonce', 'mock-nonce' );

		$this->mockGetRequest( array(
			'_llms_cert_auth' => 'incorrect-nonce',
		) );
		$this->assertEquals( $args, $this->instance->maybe_allow_public_query( $args ) );

	}

	/**
	 * Test maybe_allow_public_query(): authorization present and exists but on an invalid post type.
	 *
	 * @since 3.37.4
	 *
	 * @return void
	 */
	public function test_maybe_allow_public_query_invalid_post_type() {

		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, '_llms_auth_nonce', 'mock-nonce' );

		$this->mockGetRequest( array(
			'_llms_cert_auth' => 'mock-nonce',
		) );

		$args = array(
			'publicly_queryable' => false,
		);

		$this->assertEquals( $args, $this->instance->maybe_allow_public_query( $args ) );

	}

	/**
	 * Test maybe_allow_public_query(): valid auth and post type.
	 *
	 * @since 3.37.4
	 *
	 * @return void
	 */
	public function test_maybe_allow_public_query_update() {

		$post_id = $this->factory->post->create( array( 'post_type' => 'llms_certificate' ) );
		update_post_meta( $post_id, '_llms_auth_nonce', 'mock-nonce' );

		$this->mockGetRequest( array(
			'_llms_cert_auth' => 'mock-nonce',
		) );

		$args = array(
			'publicly_queryable' => false,
		);
		$expect = array(
			'publicly_queryable' => true,
		);

		$this->assertEquals( $expect, $this->instance->maybe_allow_public_query( $args ) );

	}

	/**
	 * Test maybe_authenticate_export_generation() when no authorization data is passed.
	 *
	 * @since 3.37.4
	 *
	 * @return void
	 */
	public function test_maybe_authenticate_export_generation_no_auth() {

		$this->instance->maybe_authenticate_export_generation();
		$this->assertEquals( 0, get_current_user_id() );

	}

	/**
	 * Test maybe_authenticate_export_generation() when no authorization data is passed.
	 *
	 * @since 3.37.4
	 *
	 * @return void
	 */
	public function test_maybe_authenticate_export_generation_invalid_post_type() {

		global $post;
		$temp = $post;
		$post = $this->factory->post->create_and_get();

		$this->mockGetRequest( array(
			'_llms_cert_auth' => 'fake',
		) );

		$this->instance->maybe_authenticate_export_generation();
		$this->assertEquals( 0, get_current_user_id() );

		// Reset post.
		$post = $temp;

	}

	/**
	 * Test maybe_authenticate_export_generation() when no authorization data is passed.
	 *
	 * @since 3.37.4
	 *
	 * @return void
	 */
	public function test_maybe_authenticate_export_generation_invalid_nonce() {

		foreach ( array( 'llms_certificate', 'llms_my_certificate' ) as $post_type ) {

			global $post;
			$temp = $post;
			$post = $this->factory->post->create_and_get( array( 'post_type' => $post_type ) );

			update_post_meta( $post->ID, '_llms_auth_nonce', 'mock-nonce' );

			$this->mockGetRequest( array(
				'_llms_cert_auth' => 'fake',
			) );

			$this->instance->maybe_authenticate_export_generation();
			$this->assertEquals( 0, get_current_user_id() );

			// Reset post.
			$post = $temp;

		}

	}

	/**
	 * Test maybe_authenticate_export_generation() for a certificate template.
	 *
	 * @since 3.37.4
	 *
	 * @return void
	 */
	public function test_maybe_authenticate_export_generation_for_template() {

		$uid = $this->factory->user->create( array( 'role' => 'lms_manager' ) );

		$template = $this->create_certificate_template();
		update_post_meta( $template, '_llms_auth_nonce', 'mock-nonce' );
		wp_update_post( array(
			'ID' => $template,
			'post_author' => $uid,
		) );

		global $post;
		$temp = $post;
		$post = get_post( $template );

		$this->mockGetRequest( array(
			'_llms_cert_auth' => 'mock-nonce',
		) );

		$this->instance->maybe_authenticate_export_generation();
		$this->assertEquals( $uid, get_current_user_id() );

		// Reset post.
		$post = $temp;

	}

	/**
	 * Test maybe_authenticate_export_generation() for an earned certificate.
	 *
	 * @since 3.37.4
	 *
	 * @return void
	 */
	public function test_maybe_authenticate_export_generation_for_earned_cert() {

		$uid = $this->factory->student->create();

		$template = $this->create_certificate_template();

		$earned = $this->earn_certificate( $uid, $template, $this->factory->post->create() );

		global $post;
		$temp = $post;
		$post = get_post( $earned[1] );
		update_post_meta( $post->ID, '_llms_auth_nonce', 'mock-nonce' );

		$this->mockGetRequest( array(
			'_llms_cert_auth' => 'mock-nonce',
		) );

		$this->instance->maybe_authenticate_export_generation();
		$this->assertEquals( $uid, get_current_user_id() );

		// Reset post.
		$post = $temp;

	}

	/**
	 * Test on_awarded()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_on_awarded() {

		$cert = $this->factory->post->create( array(
			'post_type' => 'llms_my_certificate',
		) );

		$ts = '2021-12-23 11:45:55';
		llms_mock_current_time( $ts );

		$this->assertEquals( $ts, $this->instance->on_awarded( 1, $cert ) );

	}

	/**
	 * Test on_awarded() when an invalid post type is passed in.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_on_awarded_wrong_post_type() {

		$post = $this->factory->post->create();
		$this->assertFalse( $this->instance->on_awarded( 1, $post ) );

	}

	/**
	 * Test on_save_award() with an invalid post type.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_on_save_award_invalid_post_type() {

		$post = $this->factory->post->create();
		$this->assertFalse( $this->instance->on_save_award( $post ) );

	}

	/**
	 * Test on_save_award().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_on_save_award() {

		$first_name = 'Sarah';

		$actions = did_action( 'llms_user_earned_certificate' );

		$cert_id = $this->factory->post->create( array(
			'post_type'    => 'llms_my_certificate',
			'post_content' => '{first_name}',
			'post_author'  => $this->factory->user->create( compact( 'first_name' ) ),
		) );


		$this->assertTrue( $this->instance->on_save_award( $cert_id ) );

		$cert = llms_get_certificate( $cert_id );
		$this->assertEquals( $first_name, $cert->get( 'content', true ) );

		$this->assertEquals( ++$actions, did_action( 'llms_user_earned_certificate' ) );

		// Action shouldn't run again.
		$this->assertTrue( $this->instance->on_save_award( $cert_id ) );
		$this->assertEquals( $actions, did_action( 'llms_user_earned_certificate' ) );

	}

	/**
	 * Test change_sharing_settings() when user has insufficient permissions
	 *
	 * @since 4.5.0
	 *
	 * @return void
	 */
	public function test_change_sharing_settings_invalid_permissions() {

		$earned = $this->earn_certificate( $this->factory->student->create(), $this->create_certificate_template(), $this->factory->post->create() );

		$res = LLMS_Unit_Test_Util::call_method( $this->instance, 'change_sharing_settings', array( $earned[1], true ) );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'insufficient-permissions', $res );

	}

	/**
	 * Test change_sharing_settings()
	 *
	 * @since 4.5.0
	 *
	 * @return void
	 */
	public function test_change_sharing_settings() {

		$uid      = $this->factory->student->create();
		$earned   = $this->earn_certificate( $uid, $this->create_certificate_template(), $this->factory->post->create() );
		$cert_id  = $earned[1];
		$cert = new LLMS_User_Certificate( $cert_id );

		wp_set_current_user( $uid );

		// Enable Sharing
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->instance, 'change_sharing_settings', array( $cert_id, true ) ) );
		$this->assertEquals( 'yes', $cert->get( 'allow_sharing' ) );

		// Already enabled.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->instance, 'change_sharing_settings', array( $cert_id, true ) ) );
		$this->assertEquals( 'yes', $cert->get( 'allow_sharing' ) );

		// Disable sharing.
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->instance, 'change_sharing_settings', array( $cert_id, false ) ) );
		$this->assertEquals( 'no', $cert->get( 'allow_sharing' ) );

	}

	/**
	 * Test maybe handle awarded certificates sunc actions when not supplying a (or supplying an invalid) nonce.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_handle_awarded_certificates_sync_actions_missing_invalid_nonce() {

		// Not supplying a nonce.
		$this->mockGetRequest(
			array(
				'action' => 'sync_awarded_certificate',
			)
		);

		$this->assertWPErrorCodeEquals(
			'llms-sync-awarded-certificates-nonce',
			LLMS_Controller_Certificates::maybe_handle_awarded_certificates_sync_actions()
		);

		// Supplying an invalid nonce.
		$this->mockGetRequest(
			$this->add_nonce_to_array(
				array(
					'action' => 'sync_awarded_certificate',
				),
				false
			)
		);

		$this->assertWPErrorCodeEquals(
			'llms-sync-awarded-certificates-nonce',
			LLMS_Controller_Certificates::maybe_handle_awarded_certificates_sync_actions()
		);

	}

	/**
	 * Test maybe handle awarded certificates sync actions when not supplying an action or supplying an invalid action.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_handle_awarded_certificates_sync_actions_missing_invalid_action() {

		// Not supplying an action.
		$this->mockGetRequest(
			$this->add_nonce_to_array(
				array()
			)
		);

		$this->assertWPErrorCodeEquals(
			'llms-sync-awarded-certificates-missing-action',
			LLMS_Controller_Certificates::maybe_handle_awarded_certificates_sync_actions()
		);

		// Supplying an invalid nonce.
		$this->mockGetRequest(
			$this->add_nonce_to_array(
				array(
					'action' => 'sync_awarded_certificate_wrong',
				)
			)
		);

		$this->assertWPErrorCodeEquals(
			'llms-sync-awarded-certificates-invalid-action',
			LLMS_Controller_Certificates::maybe_handle_awarded_certificates_sync_actions()
		);

	}

	/**
	 * Test maybe handle awarded certificates sync actions when not supplying a certificate/template id.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_handle_awarded_certificates_sync_actions_missing_certificate_or_template_id() {

		// Not supplying a certificate id.
		$this->mockGetRequest(
			$this->add_nonce_to_array(
				array(
					'action' => 'sync_awarded_certificate',
				)
			)
		);

		$this->assertWPErrorCodeEquals(
			'llms-sync-awarded-certificate-missing-certificate-id',
			LLMS_Controller_Certificates::maybe_handle_awarded_certificates_sync_actions()
		);

		// Not supplying a certificate template id.
		$this->mockGetRequest(
			$this->add_nonce_to_array(
				array(
					'action' => 'sync_awarded_certificates',
				)
			)
		);

		$this->assertWPErrorCodeEquals(
			'llms-sync-awarded-certificates-missing-template-id',
			LLMS_Controller_Certificates::maybe_handle_awarded_certificates_sync_actions()
		);

	}

	/**
	 * Test sync_awarded_certificates handling.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sync_awarded_certificates_handling() {

		// Create a certificate template.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		// Unregister the llms_certificate post type then re-register it so that the post type property _edit_link is populated (admin can edit the post type).
		unregister_post_type( 'llms_certificate' );
		LLMS_Post_Types::register_post_types();
		$certificate_template = $this->factory->post->create( array( 'post_type' => 'llms_certificate' ) );

		// Current user cannot edit 'llms_my_certificate' post type.
		wp_set_current_user( 0 );
		$this->assertWPErrorCodeEquals(
			'llms-sync-awarded-certificates-insufficient-permissions',
			LLMS_Unit_Test_Util::call_method(
				$this->instance,
				'sync_awarded_certificates',
				array( $certificate_template )
			)
		);

		// Current user can edit 'llms_my_certificate' post type.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'lms_manager' ) ) );
		$this->mockGetRequest(
			$this->add_nonce_to_array(
				array(
					'action' => 'sync_awarded_certificates',
					'post'   => $certificate_template,
				)
			)
		);
		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( get_edit_post_link( $certificate_template, 'raw' ) . ' [302] YES' );
		LLMS_Controller_Certificates::maybe_handle_awarded_certificates_sync_actions();

	}

	/**
	 * Test sync_awarded_certificate handling.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sync_awarded_certificate_handling() {

		// Create a certificate template.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		// Unregister the llms_my_certificate post type then re-register it so that the post type property _edit_link is populated (admin can edit the post type).
		unregister_post_type( 'llms_my_certificate' );
		LLMS_Post_Types::register_post_types();
		$certificate_template = $this->factory->post->create(
			array(
				'post_type' => 'llms_certificate',
			)
		);
		$awarded_certificate  = $this->factory->post->create(
			array(
				'post_type' => 'llms_my_certificate',
				'post_parent' => $certificate_template,
			)
		);

		// Current user cannot edit 'llms_my_certificate'.
		wp_set_current_user( 0 );
		$this->assertWPErrorCodeEquals(
			'llms-sync-awarded-certificate-insufficient-permissions',
			LLMS_Unit_Test_Util::call_method(
				$this->instance,
				'sync_awarded_certificate',
				array( $awarded_certificate )
			)
		);

		// Current user can edit 'llms_my_certificate'.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'lms_manager' ) ) );
		$this->mockGetRequest(
			$this->add_nonce_to_array(
				array(
					'action' => 'sync_awarded_certificate',
					'post'   => $awarded_certificate,
				)
			)
		);
		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( get_edit_post_link( $awarded_certificate, 'raw' ) . '&message=1 [302] YES' ); // Update success.
		LLMS_Controller_Certificates::maybe_handle_awarded_certificates_sync_actions();

	}

	/**
	 * Test sync_awarded_certificate method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_sync_awarded_certificate_method_invalid_template() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		// Unregister the llms_my_certificate post type then re-register it so that the post type property _edit_link is populated (admin can edit the post type).
		unregister_post_type( 'llms_my_certificate' );
		LLMS_Post_Types::register_post_types();
		// Invalid certificate template.
		$certificate_template = $this->factory->post->create(
			array(
				'post_type'    => 'post',
			)
		);
		$awarded_certificate  = $this->factory->post->create(
			array(
				'post_type' => 'llms_my_certificate',
				'post_parent' => $certificate_template,
			)
		);

		// Current user can edit 'llms_my_certificate'.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'lms_manager' ) ) );
		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( get_edit_post_link( $awarded_certificate, 'raw' ) . ' [302] YES' ); // No update.
		LLMS_Unit_Test_Util::call_method( $this->instance, 'sync_awarded_certificate', array( $awarded_certificate ) );

	}

	/**
	 * Add nonce to array.
	 *
	 * @since [version]
	 *
	 * @param array $data Data array.
	 * @param bool  $real If true, uses a real nonce. Otherwise uses a fake nonce (useful for testing negative cases).
	 * @return array
	 */
	protected function add_nonce_to_array( $data = array(), $real = true ) {
		$nonce_string = $real ? wp_create_nonce( 'llms-cert-sync-actions' ) : wp_create_nonce( 'fake' );

		return wp_parse_args( $data, array(
			'_llms_cert_sync_actions_nonce' => $nonce_string,
		) );
	}

}
