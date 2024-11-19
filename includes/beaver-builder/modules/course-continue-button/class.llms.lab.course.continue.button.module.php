<?php
/**
 * LifterLMS Course Continue Button Module
 *
 * @package LifterLMS_Labs/Labs/BeaverBuilder/Modules/CourseContinueButton/Classes
 *
 * @since 1.3.0
 * @version 1.7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Course Continue Button Module class.
 *
 * @since 1.3.0
 */
class LLMS_Lab_Course_Continue_Button_Module extends FLBUilderModule {

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
				'name'          => esc_html__( 'Course Continue Button', 'lifterlms' ),
				'description'   => esc_html__( 'Displays a course progress bar for the current course.', 'lifterlms' ),
				'category'      => esc_html__( 'LifterLMS Modules', 'lifterlms' ),
				'dir'           => LLMS_BB_MODULES_DIR . 'course-continue-button/',
				'url'           => LLMS_BB_MODULES_URL . 'course-continue-button/',
				'editor_export' => false,
				'enabled'       => true,
			)
		);

		add_filter( 'llms_course_continue_button_next_lesson', array( $this, 'force_display' ) );
	}

	/**
	 * Force display.
	 *
	 * @since 1.7.0
	 *
	 * @param int $lesson_id WP_Post ID of the lesson.
	 * @return int
	 */
	public function force_display( $lesson_id ) {

		if ( ! $lesson_id && FLBuilderModel::is_builder_active() ) {
			return get_the_ID();
		}

		return $lesson_id;
	}
}

FLBuilder::register_module(
	'LLMS_Lab_Course_Continue_Button_Module',
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
							'help'    => esc_html__( 'Select the course to display a continue button for. Leave blank for the current course.', 'lifterlms' ),
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
