<?php
/**
 * LifterLMS Course Syllabus Module
 *
 * @package LifterLMS_Labs/Labs/BeaverBuilder/Modules/CourseSyllabus/Classes
 *
 * @since 1.3.0
 * @version 1.7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Course Syllabus Module class.
 *
 * @since 1.3.0
 */
class LLMS_Lab_Course_Syllabus_Module extends FLBUilderModule {

	/**
	 * Constructor.
	 *
	 * @since 1.3.0
	 * @since 1.7.0 Escape strings.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct(
			array(
				'name'          => esc_html__( 'Course Syllabus', 'lifterlms' ),
				'description'   => esc_html__( 'Displays a course syllabus current course.', 'lifterlms' ),
				'category'      => esc_html__( 'LifterLMS Modules', 'lifterlms' ),
				'dir'           => LLMS_BB_MODULES_DIR . 'course-syllabus/',
				'url'           => LLMS_BB_MODULES_URL . 'course-syllabus/',
				'editor_export' => false,
				'enabled'       => true,
			)
		);
	}
}

FLBuilder::register_module( 'LLMS_Lab_Course_Syllabus_Module', array() );
