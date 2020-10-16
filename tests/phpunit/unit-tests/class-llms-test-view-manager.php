<?php
/**
 * Test view manager
 *
 * @package LifterLMS/Tests
 *
 * @group view_manager
 *
 * @since 4.5.1
 */
class LLMS_Test_View_Manager extends LLMS_UnitTestCase {

	/**
	 * Setup test case
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->main = new LLMS_View_Manager();

	}

	/**
	 * Test constructor
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function test__construct() {

		// Remove existing action.
		remove_action( 'init', array( $this->main, 'add_actions' ) );
		$this->assertFalse( has_action( 'init', array( $this->main, 'add_actions' ) ) );

		// Reinit.
		$this->main = new LLMS_View_Manager();
		$this->assertEquals( 10, has_action( 'init', array( $this->main, 'add_actions' ) ) );

	}

	/**
	 * Test constructor when a pending order is being created.
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function test__construct_pending_order() {

		// Remove existing action.
		remove_action( 'init', array( $this->main, 'add_actions' ) );
		$this->assertFalse( has_action( 'init', array( $this->main, 'add_actions' ) ) );

		// Reinit.
		$this->mockPostRequest( array(
			'action' => 'create_pending_order',
		) );
		$this->main = new LLMS_View_Manager();
		$this->assertFalse( has_action( 'init', array( $this->main, 'add_actions' ) ) );
	}

	/**
	 * Test should_display() when viewing valid post types with a valid user
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function test_should_display_on_valid_post_types() {

		global $post;
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		foreach ( array( 'course', 'lesson', 'llms_membership', 'llms_quiz' ) as $post_type ) {
			$post = $this->factory->post->create_and_get( compact( 'post_type' ) );
			$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );
		}

	}

	/**
	 * Test should_display() when viewing checkout valid with a valid user
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function test_should_display_on_checkout() {
		LLMS_Install::create_pages();
		$this->go_to( llms_get_page_url( 'checkout' ) );
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );

	}

	/**
	 * Test should_display() when no user is present
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function test_should_display_no_user() {
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );
	}

	/**
	 * Test should_display() when an invalid user is logged in
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function test_should_display_invalid_user() {
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'student' ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );
	}

	/**
	 * Test should_display() on the admin panel
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function test_should_display_in_admin() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		set_current_screen( 'users.php' );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );
		set_current_screen( 'front' ); // Reset for later tests.

	}

	/**
	 * Test should_display() on a post type archive
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function test_should_display_post_type_archive() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->go_to( get_post_type_archive_link( 'course' ) );
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );

	}

	/**
	 * Test should_display() when a valid using is viewing an invalid post type
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function test_should_display_on_invalid_post_types() {

		global $post;
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		foreach ( array( 'post', 'page' ) as $post_type ) {
			$post = $this->factory->post->create_and_get( compact( 'post_type' ) );
			$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );
		}

	}

}
