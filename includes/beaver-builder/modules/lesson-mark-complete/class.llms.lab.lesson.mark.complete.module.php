<?php
/**
 * LifterLMS Lesson Mark Complete Module
 *
 * @package LifterLMS_Labs/Labs/BeaverBuilder/Modules/LessonMarkComplete/Classes
 *
 * @since 1.3.0
 * @version 1.7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Lesson Mark Complete Module class.
 *
 * @since 1.3.0
 */
class LLMS_Lab_Lesson_Mark_Complete_Module extends FLBUilderModule {

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
				'name'          => esc_html__( 'Lesson Mark Complete Button', 'lifterlms' ),
				'description'   => esc_html__( 'Displays the mark complete / incomplete button(s) for a lesson', 'lifterlms' ),
				'category'      => esc_html__( 'LifterLMS Modules', 'lifterlms' ),
				'dir'           => LLMS_BB_MODULES_DIR . 'lesson-mark-complete/',
				'url'           => LLMS_BB_MODULES_URL . 'lesson-mark-complete/',
				'editor_export' => false,
				'enabled'       => true,
			)
		);
	}
}

FLBuilder::register_module( 'LLMS_Lab_Lesson_Mark_Complete_Module', array() );
