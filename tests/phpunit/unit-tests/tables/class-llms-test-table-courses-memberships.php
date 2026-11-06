<?php
/**
 * Test the instructors column of the courses and memberships reporting tables.
 *
 * @package LifterLMS/Tests/Tables
 *
 * @group admin_tables
 *
 * @since [version]
 */
class LLMS_Test_Table_Courses_Memberships extends LLMS_UnitTestCase {

	/**
	 * Load the table classes.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/llms.abstract.exportable.admin.table.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/reporting/tables/llms.table.courses.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/reporting/tables/llms.table.memberships.php';

	}

	/**
	 * Data provider: table class and product post type.
	 *
	 * @since [version]
	 *
	 * @return array[]
	 */
	public function provider_tables() {
		return array(
			'courses'     => array( 'LLMS_Table_Courses', 'course' ),
			'memberships' => array( 'LLMS_Table_Memberships', 'llms_membership' ),
		);
	}

	/**
	 * Test instructor labels are sanitized when saved and escaped in the instructors column.
	 *
	 * @since [version]
	 *
	 * @dataProvider provider_tables
	 *
	 * @param string $table_class Table class name.
	 * @param string $post_type   Product post type.
	 * @return void
	 */
	public function test_instructor_label_markup( $table_class, $post_type ) {

		$instructor_id = $this->factory->user->create(
			array(
				'role'         => 'instructor',
				'display_name' => 'Jane',
			)
		);
		$post_id       = $this->factory->post->create( array( 'post_type' => $post_type ) );
		$product       = llms_get_post( $post_id );
		$table         = new $table_class();

		$product->set_instructors(
			array(
				array(
					'id'    => $instructor_id,
					'label' => '<img src=x onerror=alert(1)>Coach',
				),
			)
		);
		$this->assertSame( 'Coach', $product->get_instructors()[0]['label'] );

		// Labels saved before they were sanitized are escaped on output.
		update_post_meta(
			$post_id,
			'_llms_instructors',
			array(
				array(
					'id'         => $instructor_id,
					'label'      => '<img src=x onerror=alert(1)>',
					'visibility' => 'visible',
				),
			)
		);
		$this->assertSame( 'Jane (&lt;img src=x onerror=alert(1)&gt;)', LLMS_Unit_Test_Util::call_method( $table, 'get_data', array( 'instructors', get_post( $post_id ) ) ) );

	}
}
