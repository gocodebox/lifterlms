<?php
/**
 * Test LLMS_Form_Post_Type class
 *
 * @package LifterLMS/Tests
 *
 * @group forms
 *
 * @since 5.0.0
 * @version [version]
 */
class LLMS_Test_Form_Post_Type extends LLMS_UnitTestCase {

	/**
	 * Setup the test case
	 *
	 * @since 5.0.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Form_Post_Type( LLMS_Forms::instance() );

	}

	/**
	 * Test class properties.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_properties() {

		$this->assertEquals( 'llms_form', $this->main->post_type );
		$this->assertEquals( 'manage_lifterlms', $this->main->capability );

	}

	/**
	 * Test enabled_post_type_visibility() when skipping the override
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_enable_post_type_visibility() {

		$res_data  = array( 'viewable' => false );
		$res       = rest_ensure_response( $res_data );
		$post_type = get_post_type_object( 'llms_form' );

		// Not admin.
		$this->assertEquals( $res_data, $this->main->enable_post_type_visibility( $res, $post_type )->get_data() );

		// Is admin.
		set_current_screen( 'admin.php' );

		// Wrong post type.
		$this->assertEquals( $res_data, $this->main->enable_post_type_visibility( $res, get_post_type_object( 'course' ) )->get_data() );

		// Okay.
		$this->assertEquals( array( 'viewable' => true ), $this->main->enable_post_type_visibility( $res, $post_type )->get_data() );

		set_current_screen( 'front' ); // Reset.

	}

	/**
	 * Test permalink retrieval for account updates.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_permalink_for_account() {

		LLMS_Install::create_pages();

		$url = parse_url( get_permalink( LLMS_Forms::instance()->create( 'account' ) ) );
		parse_str( $url['query'], $qs );

		$this->assertEquals( parse_url( get_site_url(), PHP_URL_HOST ), $url['host'] );
		$this->assertEquals( get_option( 'lifterlms_myaccount_page_id' ), $qs['page_id'] );
		$this->assertArrayHasKey( 'edit-account', $qs );

	}

	/**
	 * Test permalink retrieval for checkout when no access plans exist.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_permalink_for_checkout_no_plans() {

		global $wpdb;
		$wpdb->delete( $wpdb->posts, array( 'post_type' => 'llms_access_plan' ) );

		LLMS_Install::create_pages();

		$url = parse_url( get_permalink( LLMS_Forms::instance()->create( 'checkout' ) ) );
		parse_str( $url['query'], $qs );

		$this->assertEquals( parse_url( get_site_url(), PHP_URL_HOST ), $url['host'] );
		$this->assertEquals( get_option( 'lifterlms_checkout_page_id' ), $qs['page_id'] );
		$this->assertEquals( 'visitor', $qs['llms-view-as'] );

		$this->assertEquals( 1, wp_verify_nonce( $qs['view_nonce'], 'llms-view-as' ) );

	}

	/**
	 * Test permalink retrieval for checkout with access plans.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_permalink_for_checkout_with_plans() {

		LLMS_Install::create_pages();
		$plan = $this->get_mock_plan();

		$url = parse_url( get_permalink( LLMS_Forms::instance()->create( 'checkout' ) ) );
		parse_str( $url['query'], $qs );

		$this->assertEquals( parse_url( get_site_url(), PHP_URL_HOST ), $url['host'] );
		$this->assertEquals( get_option( 'lifterlms_checkout_page_id' ), $qs['page_id'] );
		$this->assertEquals( 'visitor', $qs['llms-view-as'] );
		$this->assertEquals( $plan->get( 'id' ), $qs['plan'] );

		$this->assertEquals( 1, wp_verify_nonce( $qs['view_nonce'], 'llms-view-as' ) );

	}

	/**
	 * Test permalink retrieval for registration form when open registration is not enabled.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_permalink_for_registration_not_enabled() {

		$form = get_post( LLMS_Forms::instance()->create( 'registration' ) );
		update_option( 'lifterlms_enable_myaccount_registration', 'no' );
		$this->assertFalse( get_permalink( LLMS_Forms::instance()->create( 'registration' ) ) );

	}

	/**
	 * Test permalink retrieval for registration form when open registration is enabled.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_permalink_for_registration_enabled() {

		LLMS_Install::create_pages();
		update_option( 'lifterlms_enable_myaccount_registration', 'yes' );

		$url = parse_url( get_permalink( LLMS_Forms::instance()->create( 'registration' ) ) );
		parse_str( $url['query'], $qs );

		$this->assertEquals( parse_url( get_site_url(), PHP_URL_HOST ), $url['host'] );
		$this->assertEquals( get_option( 'lifterlms_myaccount_page_id' ), $qs['page_id'] );
		$this->assertEquals( 'visitor', $qs['llms-view-as'] );

		$this->assertEquals( 1, wp_verify_nonce( $qs['view_nonce'], 'llms-view-as' ) );

	}

	/**
	 * Test maybe_prevent_deletion() for other post types
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_maybe_prevent_deletion_wrong_post_type() {
		$post = $this->factory->post->create_and_get();
		$this->assertNull( $this->main->maybe_prevent_deletion( null, $post ) );
	}

	/**
	 * Test maybe_prevent_deletion() for non-core forms
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_maybe_prevent_deletion_not_core() {
		$post = $this->factory->post->create_and_get( array( 'post_type' => 'llms_form' ) );
		$this->assertNull( $this->main->maybe_prevent_deletion( null, $post ) );
	}

	/**
	 * Test maybe_prevent_deletion() for core forms that cannot be deleted.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_maybe_prevent_deletion() {
		$post = $this->factory->post->create_and_get( array( 'post_type' => 'llms_form' ) );
		update_post_meta( $post->ID, '_llms_form_is_core', 'yes' );
		$this->assertFalse( $this->main->maybe_prevent_deletion( null, $post ) );
	}

	/**
	 * Test meta_auth_callback()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_meta_auth_callback() {

		LLMS_Install::create_pages();
		$form = get_post( LLMS_Forms::instance()->create( 'registration' ) );

		$roles = array(
			'administrator' => true,
			'lms_manager'   => true,
			'instructor'    => false,
			'student'       => false,
			'editor'        => false,
			'subscriber'    => false,
		);

		// Logged out user can't do stuff.
		$this->assertFalse( $this->main->meta_auth_callback( false, 'does_not_matter', $form->ID, null, 'does_not_matter', array() ) );

		// Test various roes.
		foreach ( $roles as $role => $expect ) {
			$user = $this->factory->user->create_and_get( array( 'role' => $role ) );
			$this->assertSame( $expect, $this->main->meta_auth_callback( false, 'does_not_matter', $form->ID, $user->ID, 'does_not_matter', $user->caps ) );
		}


	}

	/**
	 * Test post type registration.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_register_post_type() {

		// Remove it so we can ensure we register it.
		unregister_post_type( 'llms_form' );

		// Make sure the filter runs.
		global $form_post_type_registrion_runs;
		$filter_ran = 0;
		$handler = function( $name ) {
			global $form_post_type_registrion_runs;
			++$form_post_type_registrion_runs;
			return $name;
		};
		add_filter( 'lifterlms_register_post_type_form', $handler );

		$this->main->register_post_type();

		// Post type has been registered.
		$this->assertTrue( post_type_exists( 'llms_form' ) );

		// Filter ran.
		$this->assertEquals( 1, $form_post_type_registrion_runs );

		remove_filter( 'lifterlms_register_post_type_form', $handler );

	}

	/**
	 * Test custom meta prop registration.
	 *
	 * @since 5.0.0
	 * @since [version] Added expected meta prop '_llms_form_title_free_access_plans'.
	 *
	 * @return void
	 */
	public function test_register_meta() {

		do_action( 'init' );

		global $wp_meta_keys;
		$this->assertArrayHasKey( 'post', $wp_meta_keys );
		$this->assertArrayHasKey( 'llms_form', $wp_meta_keys['post'] );

		// Expected meta props.
		$props = array(
			'_llms_form_location',
			'_llms_form_show_title',
			'_llms_form_is_core',
			'_llms_form_title_free_access_plans',
		);

		foreach ( $props as $meta ) {
			$this->assertArrayHasKey( $meta, $wp_meta_keys['post']['llms_form'] );
		}

	}

}
