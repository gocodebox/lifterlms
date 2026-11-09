<?php
/**
 * Tests for the LLMS_Admin_Post_Table_Courses class.
 *
 * @package LifterLMS/Tests
 *
 * @group admin
 * @group post_tables
 * @group post_table_courses
 *
 * @since [version]
 */
class LLMS_Test_Admin_Post_Table_Courses extends LLMS_UnitTestCase {

	/**
	 * Set up before class.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/post-types/post-tables/class.llms.admin.post.table.courses.php';

	}

	/**
	 * Test handle_bulk_actions() only exports courses the current user can edit.
	 *
	 * Runs in a separate process because the export sends headers.
	 *
	 * @since [version]
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function test_handle_bulk_actions_export_only_editable_courses() {

		$instructor_id = $this->factory->user->create( array( 'role' => 'instructor' ) );
		$other_id      = $this->factory->user->create( array( 'role' => 'instructor' ) );

		$own_course   = $this->factory->post->create( array( 'post_type' => 'course', 'post_author' => $instructor_id ) );
		$other_course = $this->factory->post->create( array( 'post_type' => 'course', 'post_author' => $other_id ) );
		$admin_draft  = $this->factory->post->create( array( 'post_type' => 'course', 'post_status' => 'draft', 'post_author' => 1 ) );
		$own_post     = $this->factory->post->create( array( 'post_type' => 'post', 'post_author' => $instructor_id ) );

		wp_set_current_user( $instructor_id );

		$main = new LLMS_Admin_Post_Table_Courses();

		// The plugged llms_exit() passes `null` to Exception::__construct(), which is deprecated on PHP 8.1+.
		error_reporting( error_reporting() & ~E_DEPRECATED );

		ob_start();
		try {
			$main->handle_bulk_actions( '', 'llms_export', array( $own_course, $other_course, $admin_draft, $own_post ) );
		} catch ( LLMS_Unit_Test_Exception_Exit $exception ) {}
		$data = json_decode( ob_get_clean(), true );

		$this->assertSame( 'LifterLMS/BulkCourseExporter', $data['_generator'] );
		$this->assertSame( array( $own_course ), wp_list_pluck( $data['courses'], 'id' ) );

	}

}
