<?php
/**
 * LifterLMS Course Meta Info Module Module
 *
 * @package LifterLMS_Labs/Labs/BeaverBuilder/Modules/CourseMetaInfo/Classes
 *
 * @since 1.3.0
 * @version 1.7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLifterLMS Course Meta Info Module class.
 *
 * @since 1.3.0
 */
class LLMS_Lab_Course_Meta_Info_Module extends FLBUilderModule {

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
				'name'          => esc_html__( 'Course Information', 'lifterlms' ),
				'description'   => esc_html__( 'Displays course information: length, difficulty, tracks, categories, and tags.', 'lifterlms' ),
				'category'      => esc_html__( 'LifterLMS Modules', 'lifterlms' ),
				'dir'           => LLMS_BB_MODULES_DIR . 'course-meta-info/',
				'url'           => LLMS_BB_MODULES_URL . 'course-meta-info/',
				'editor_export' => false,
				'enabled'       => true,
			)
		);
	}
}

FLBuilder::register_module(
	'LLMS_Lab_Course_Meta_Info_Module',
	array(
		'general' => array(
			'title'    => esc_html__( 'General', 'lifterlms' ),
			'sections' => array(
				'general' => array(
					'title'  => esc_html__( 'General', 'lifterlms' ),
					'fields' => array(
						'llms_course_id' => array(
							'type'    => 'suggest',
							'action'  => 'fl_as_posts',
							'data'    => 'course',
							'limit'   => 1,
							'label'   => esc_html__( 'Course', 'lifterlms' ),
							'help'    => esc_html__( 'Select the course to display the course information from. Leave blank for the current course.', 'lifterlms' ),
							'preview' => array(
								'type' => 'none',
							),
						),
					),
				),
			),
		),
	)
);
