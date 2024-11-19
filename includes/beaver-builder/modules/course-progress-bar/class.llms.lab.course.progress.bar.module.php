<?php
/**
 * LifterLMS Course Progress Bar Module
 *
 * @package LifterLMS_Labs/Labs/BeaverBuilder/Modules/CourseProgressBar/Classes
 *
 * @since 1.3.0
 * @version 1.7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Course Progress Module class.
 *
 * @since 1.3.0
 */
class LLMS_Lab_Course_Progress_Bar_Module extends FLBUilderModule {

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
				'name'          => esc_html__( 'Course Progress Bar', 'lifterlms' ),
				'description'   => esc_html__( 'Displays a course progress bar for the current course.', 'lifterlms' ),
				'category'      => esc_html__( 'LifterLMS Modules', 'lifterlms' ),
				'dir'           => LLMS_BB_MODULES_DIR . 'course-progress-bar/',
				'url'           => LLMS_BB_MODULES_URL . 'course-progress-bar/',
				'editor_export' => false,
				'enabled'       => true,
			)
		);
	}
}

FLBuilder::register_module( 'LLMS_Lab_Course_Progress_Bar_Module', array() );
