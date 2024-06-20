<?php
/**
* Test updates functions when updating to 7.7.
 *
 * @package LifterLMS/Tests/Functions/Updates
 *
 * @group functions
 * @group updates
 * @group updates_770
 *
 * @since [version]
 */
class LLMS_Test_Functions_Updates_770 extends LLMS_UnitTestCase {

	/**
	 * Setup before class.
	 *
	 * Include update functions file.
	 *
 	 * @since [version]
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/functions/updates/llms-functions-updates-770.php';
		require_once LLMS_PLUGIN_DIR . 'includes/functions/llms.functions.updates.php';
	}

	/**
	 * Test migrate_spanish_users().
	 *
	 * @since 6.10.0
	 *
	 * @return void
	 */
	public function test_migrate_elementor_course() {
		global $llms_elementor_migrate;

		if ( ! class_exists( 'Elementor\Plugin') ) {
			$this->markTestSkipped( 'Elementor not installed.' );
		}

		$course_with_elementor = $this->factory->course->create();
		$course_without_elementor = $this->factory->course->create();

		$default_content = array();
		$default_content[] = array(
			'id'       => uniqid(),
			'elType'   => 'container',
			'settings' => array(),
			'elements' => array(
			),
			'isInner'  => false,
		);

		update_post_meta( $course_with_elementor, '_elementor_edit_mode', 'builder' );
		update_post_meta( $course_with_elementor, '_elementor_data', trim( wp_json_encode( $default_content ), '"' ) );

		\LLMS\Updates\Version_7_7_0\elementor_migrate_courses();

		$elementor_data_of_course = json_decode( get_post_meta( $course_with_elementor, '_elementor_data', true ) );
		$this->assertEquals(
			count( $llms_elementor_migrate->get_elementor_data_template() ) + 1, count( $elementor_data_of_course )
		);
		$this->assertEquals( $default_content[0]['id'], $elementor_data_of_course[0]->id );
		$this->assertTrue( empty( get_post_meta( $course_without_elementor, '_elementor_data', true ) ) );
	}

	/**
	 * Test update_db_version().
	 *
	 * @since 6.10.0
	 *
	 * @return void
	 */
	public function test_update_update_db_version() {

		$orig = get_option( 'lifterlms_db_version' );

		// Remove existing db version.
		delete_option( 'lifterlms_db_version' );

		\LLMS\Updates\Version_7_7_0\update_db_version();

		$this->assertEquals( \LLMS\Updates\Version_7_7_0\_get_db_version(), get_option( 'lifterlms_db_version' ) );

		update_option( 'lifterlms_db_version', $orig );

	}

}
