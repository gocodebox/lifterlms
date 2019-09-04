<?php
/**
 * Admin Settings Page "Courses" Tab
 *
 * @since   3.5.0
 * @version 3.5.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class LLMS_Settings_Courses extends LLMS_Settings_Page {

	/**
	 * Constructor
	 *
	 * executes settings tab actions
	 */
	public function __construct() {

		$this->id    = 'courses';
		$this->label = __( 'Courses', 'lifterlms' );

		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'save' ) );

	}

	/**
	 * Get settings array
	 *
	 * @return array
	 * @since   3.5.0
	 * @version 3.5.0
	 */
	public function get_settings() {

		$course = apply_filters(
			'lifterlms_course_settings',
			array(

				array(
					'class' => 'top',
					'id'    => 'course_general_options',
					'type'  => 'sectionstart',
				),

				array(
					'id'    => 'course_general_options_title',
					'title' => __( 'Course Settings', 'lifterlms' ),
					'type'  => 'title',
				),

				array(
					'desc'    => __( 'Enabling this setting allows students to mark a lesson as "incomplete" after they have completed a lesson.', 'lifterlms' ),
					'default' => 'no',
					'id'      => 'lifterlms_retake_lessons',
					'title'   => __( 'Retake Lessons', 'lifterlms' ),
					'type'    => 'checkbox',
				),

				array(
					'type' => 'sectionend',
					'id'   => 'course_general_options',
				),

				array(
					'class' => 'top',
					'id'    => 'course_archive_options',
					'type'  => 'sectionstart',
				),

				array(
					'id'    => 'course_options',
					'title' => __( 'Course Catalog Settings', 'lifterlms' ),
					'type'  => 'title',
				),

				array(
					'class'             => 'llms-select2-post',
					'custom_attributes' => array(
						'data-allow-clear' => true,
						'data-post-type'   => 'page',
						'data-placeholder' => __( 'Select a page', 'lifterlms' ),
					),
					'desc'              => '<br/>' . sprintf( __( 'This page is where your visitors will find a list of all your available courses. %1$sMore information%2$s.', 'lifterlms' ), '<a href="https://lifterlms.com/docs/course-catalog/" target="_blank">', '</a>' ),
					'id'                => 'lifterlms_shop_page_id',
					'options'           => llms_make_select2_post_array( get_option( 'lifterlms_shop_page_id', '' ) ),
					'title'             => __( 'Course Catalog', 'lifterlms' ),
					'type'              => 'select',
				),

				array(
					'default' => 9,
					'desc'    => '<br/>' . __( 'To show all courses on one page, enter -1', 'lifterlms' ),
					'id'      => 'lifterlms_shop_courses_per_page',
					'title'   => __( 'Courses per page', 'lifterlms' ),
					'type'    => 'number',
				),

				array(
					'default' => 'menu_order',
					'desc'    => '<br />' . __( 'Determines the display order for courses on the courses page.', 'lifterlms' ),
					'id'      => 'lifterlms_shop_ordering',
					'options' => array(
						'menu_order,ASC' => __( 'Order (Low to High)', 'lifterlms' ),
						'title,ASC'      => __( 'Title (A - Z)', 'lifterlms' ),
						'title,DESC'     => __( 'Title (Z - A)', 'lifterlms' ),
						'date,DESC'      => __( 'Most Recent', 'lifterlms' ),
					),
					'title'   => __( 'Catalog Sorting', 'lifterlms' ),
					'type'    => 'select',

				),

				array(
					'type' => 'sectionend',
					'id'   => 'course_archive_options',
				),

			)
		);

		/**
		 * Ensure deprecated filter sticks around for a while
		 *
		 * @todo  deprecate this filter
		 */
		$deprecated = apply_filters( 'lifterlms_catalog_settings', array() );

		return array_merge( $course, $deprecated );

	}

	/**
	 * Save settings
	 *
	 * @since   3.5.0
	 *
	 * @return void
	 */
	public function save() {
		$settings = $this->get_settings();
		LLMS_Admin_Settings::save_fields( $settings );
	}

	/**
	 * Output settings on screen
	 *
	 * @since   3.5.0
	 *
	 * @return void
	 */
	public function output() {
		$settings = $this->get_settings();
		LLMS_Admin_Settings::output_fields( $settings );
	}

}

return new LLMS_Settings_Courses();
