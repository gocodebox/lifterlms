<?php
/**
 * Test LLMS_Forms_Admin_Bar class
 *
 * @package LifterLMS/Tests
 *
 * @group forms
 * @group forms_admin_bar
 *
 * @since 5.0.0
 */
class LLMS_Test_Forms_Admin_Bar extends LLMS_UnitTestCase {

	/**
	 * Setup the test
	 *
	 * @since 5.0.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Forms_Admin_Bar();

	}

	/**
	 * Initiate (and retrieve) an instance of WP_Admin_Bar
	 *
	 * @since 5.0.0
	 *
	 * @return WP_Admin_Bar
	 */
	private function get_admin_bar() {

		add_filter( 'show_admin_bar', '__return_true' );
		_wp_admin_bar_init();

		global $wp_admin_bar;

		remove_filter( 'show_admin_bar', '__return_true' );

		return $wp_admin_bar;

	}

	/**
	 * Test add_menu_items() when nothing should be added
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_add_menu_items_no_display() {

		$bar = $this->get_admin_bar();

		$this->main->add_menu_items( $bar );

		$this->assertNull( $bar->get_nodes() );

	}

	/**
	 * Test add_menu_items()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_add_menu_items() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		LLMS_Forms::instance()->install();
		LLMS_Install::create_pages();
		$this->go_to( get_permalink( llms_get_page_id( 'checkout' ) ) );

		$bar = $this->get_admin_bar();

		add_filter( 'llms_view_manager_should_display', '__return_true' );

		$this->main->add_menu_items( $bar );

		$this->assertEquals( array( 'llms-edit-form' ), array_keys( $bar->get_nodes() ) );

		remove_filter( 'llms_view_manager_should_display', '__return_true' );

	}

	/**
	 * Test get_current_location()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_current_location() {

		// Invalid screen.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'get_current_location' ) );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		LLMS_Install::create_pages();

		// Checkout.
		$this->go_to( get_permalink( llms_get_page_id( 'checkout' ) ) );
		$this->assertEquals( 'checkout', LLMS_Unit_Test_Util::call_method( $this->main, 'get_current_location' ) );

		// Edit Account.
		$this->go_to( llms_person_edit_account_url() );
		$this->assertEquals( 'account', LLMS_Unit_Test_Util::call_method( $this->main, 'get_current_location' ) );

		// Open Reg.
		update_option( 'lifterlms_enable_myaccount_registration', 'yes' );
		$this->go_to( get_permalink( llms_get_page_id( 'myaccount' ) ) );
		$this->mockGetRequest( array( 'llms-view-as' => 'visitor' ) );
		$this->assertEquals( 'registration', LLMS_Unit_Test_Util::call_method( $this->main, 'get_current_location' ) );


	}

	/**
	 * Test should_display() on checkout page
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_should_display() {

		// No user.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// Invalid screen.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );

		LLMS_Install::create_pages();

		// Checkout.
		$this->go_to( get_permalink( llms_get_page_id( 'checkout' ) ) );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );

		// Edit Account.
		$this->go_to( llms_person_edit_account_url() );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );

		// Open Reg.
		update_option( 'lifterlms_enable_myaccount_registration', 'yes' );
		$this->go_to( get_permalink( llms_get_page_id( 'myaccount' ) ) );
		$this->mockGetRequest( array( 'llms-view-as' => 'visitor' ) );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );

	}

}
