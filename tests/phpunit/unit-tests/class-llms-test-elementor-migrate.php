<?php
/**
 * Test LLMS_Elementor_Migrate
 *
 * @package LifterLMS/Tests
 *
 * @group elementor_migrate
 *
 * @since [version]
 */
class LLMS_Test_Elementor_Migrate extends LLMS_UnitTestCase {

	/**
	 * Original value of `$pagenow` global.
	 *
	 * @var string
	 */
	private $orig_pagenow;

	/**
	 * Instance of the tested class.
	 *
	 * @var LLMS_Elementor_Migrate
	 */
	private $migrate;

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		global $pagenow;
		$this->orig_pagenow = $pagenow;
		$pagenow            = 'post.php';

		$this->migrate = new LLMS_Elementor_Migrate();

	}

	/**
	 * Teardown the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tear_down() {

		global $pagenow;
		$pagenow = $this->orig_pagenow;

		unset( $_GET['post'], $_REQUEST['action'] );

		wp_set_current_user( 0 );

		parent::tear_down();

	}

	/**
	 * Simulate a request to `post.php?post={id}&action=elementor`.
	 *
	 * @since [version]
	 *
	 * @param int $post_id WP_Post ID.
	 * @return void
	 */
	private function mock_request( $post_id ) {

		$_GET['post']         = $post_id;
		$_REQUEST['post']     = $post_id;
		$_REQUEST['action']   = 'elementor';

		$this->migrate->migrate_post();

	}

	/**
	 * Meta is written for a user who can edit the course.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_migrate_authorized() {

		$course_id = $this->factory->course->create();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$this->mock_request( $course_id );

		$this->assertNotFalse( get_post_meta( $course_id, '_elementor_data', true ) );
		$this->assertSame( 'yes', get_post_meta( $course_id, '_llms_elementor_migrated', true ) );

	}

	/**
	 * Meta is not written for a user who has the generic `edit_posts` capability
	 * but cannot edit the requested course.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_migrate_unauthorized() {

		$course_id = $this->factory->course->create();

		// An author can edit posts in general, but not this course (authored by someone else).
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'author' ) ) );

		$this->mock_request( $course_id );

		$this->assertEmpty( get_post_meta( $course_id, '_elementor_data', true ) );
		$this->assertSame( '', get_post_meta( $course_id, '_llms_elementor_migrated', true ) );

	}

}
