<?php
/**
 * LifterLMS Course Instructors Module
 *
 * @package LifterLMS_Labs/Labs/BeaverBuilder/Modules/CourseContinueButton/Classes
 *
 * @since 8.0.0
 */

defined( 'ABSPATH' ) || exit;

class LLMS_Lab_Course_Instructors_Module extends FLBUilderModule {

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
				'name'          => esc_html__( 'Course Instructors', 'lifterlms' ),
				'description'   => esc_html__( 'Displays instructors for the current course.', 'lifterlms' ),
				'category'      => esc_html__( 'LifterLMS Modules', 'lifterlms' ),
				'dir'           => LLMS_BB_MODULES_DIR . 'course-instructors/',
				'url'           => LLMS_BB_MODULES_URL . 'course-instructors/',
				'editor_export' => false,
				'enabled'       => true,
			)
		);
	}
}

FLBuilder::register_module( 'LLMS_Lab_Course_Instructors_Module', array() );
