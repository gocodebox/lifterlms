<?php
/**
 * Test LLMS_Form_Post_Type class
 *
 * @package LifterLMS/Tests
 *
 * @group forms
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Form_Post_Type extends LLMS_UnitTestCase {

	public function setUp() {

		parent::setUp();
		$this->main = new LLMS_Form_Post_Type( LLMS_Forms::instance() );

	}

	public function test_properties() {

		$this->assertEquals( 'llms_form', $this->main->post_type );
		$this->assertEquals( 'manage_lifterlms', $this->main->capability );

	}

	/**
	 * Test permalink retrieval for account updates.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_permalink_for_account() {

		LLMS_Install::create_pages();
		$form = get_post( LLMS_Forms::instance()->create( 'account' ) );
		$link = LLMS_Unit_Test_Util::call_method( $this->main, 'get_permalink', array( $form ) );
		$this->assertEquals( add_query_arg( 'edit-account', '', get_permalink( get_option( 'lifterlms_myaccount_page_id' ) ) ), $link );

	}

	/**
	 * Test permalink retrieval for checkout when no access plans exist.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_permalink_for_checkout_no_plans() {

		global $wpdb;
		$wpdb->delete( $wpdb->posts, array( 'post_type' => 'llms_access_plan' ) );

		LLMS_Install::create_pages();
		$form = get_post( LLMS_Forms::instance()->create( 'checkout' ) );
		$link = LLMS_Unit_Test_Util::call_method( $this->main, 'get_permalink', array( $form ) );
		$this->assertEquals( get_permalink( get_option( 'lifterlms_checkout_page_id' ) ), $link );

	}

	/**
	 * Test permalink retrieval for checkout with access plans.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_permalink_for_checkout_with_plans() {

		LLMS_Install::create_pages();
		$plan = $this->get_mock_plan();
		$form = get_post( LLMS_Forms::instance()->create( 'checkout' ) );
		$link = LLMS_Unit_Test_Util::call_method( $this->main, 'get_permalink', array( $form ) );
		$this->assertEquals( add_query_arg( 'plan', $plan->get( 'id' ), get_permalink( get_option( 'lifterlms_checkout_page_id' ) ) ), $link );

	}

	/**
	 * Test permalink retrieval for registration form when open registration is not enabled.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_permalink_for_registration_not_enabled() {

		$form = get_post( LLMS_Forms::instance()->create( 'registration' ) );
		update_option( 'lifterlms_enable_myaccount_registration', 'no' );
		$link = LLMS_Unit_Test_Util::call_method( $this->main, 'get_permalink', array( $form ) );
		$this->assertFalse( $link );

	}

	/**
	 * Test permalink retrieval for registration form when open registration is enabled.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_permalink_for_registration_enabled() {

		LLMS_Install::create_pages();
		$form = get_post( LLMS_Forms::instance()->create( 'registration' ) );
		update_option( 'lifterlms_enable_myaccount_registration', 'yes' );
		$link = LLMS_Unit_Test_Util::call_method( $this->main, 'get_permalink', array( $form ) );
		$this->assertEquals( get_permalink( get_option( 'lifterlms_myaccount_page_id' ) ), $link );

	}

	public function test_meta_auth_callback() {

		LLMS_Install::create_pages();
		$form = get_post( LLMS_Forms::instance()->create( 'registration' ) );

		$roles = array(
			'administrator' => true,
			'lms_manager'   => true,
			'instructor'    => false,
			'student'       => false,
			'editor'        => false,
		);

		$this->assertFalse( $this->main->meta_auth_callback( false, 'does_not_matter', $form->ID, null, 'does_not_matter', array() ) );

		foreach ( $roles as $role => $expect ) {
			$user = $this->factory->user->create_and_get( array( 'role' => $role ) );
			$this->assertSame( $expect, $this->main->meta_auth_callback( false, 'does_not_matter', $form->ID, $user->ID, 'does_not_matter', $user->caps ) );
		}


	}

	/**
	 * Test post type registration.
	 *
	 * @since [version]
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
	 * @since [version]
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
		);

		foreach ( $props as $meta ) {
			$this->assertArrayHasKey( $meta, $wp_meta_keys['post']['llms_form'] );
		}

	}

}
