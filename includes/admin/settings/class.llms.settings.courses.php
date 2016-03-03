<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Admin Settings Page, Courses Tab
*
* @author codeBOX
* @project lifterLMS
*/
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
	 */
	public function get_settings() {
		// Get shop page
		$shop_page_id = llms_get_page_id( 'shop' );

		$base_slug = ($shop_page_id > 0 && get_page( $shop_page_id )) ? get_page_uri( $shop_page_id ) : 'llms_shop';

		return apply_filters( 'lifterlms_course_settings', array(

			array( 'type' => 'sectionstart', 'id' => 'course_archive_options', 'class' => 'top' ),

			array( 'title' => __( 'Archive Settings', 'lifterlms' ), 'type' => 'title','desc' => 'Customize your courses for a unique user experience.', 'id' => 'course_options' ),

			array(
				'title' => __( 'Courses Page', 'lifterlms' ),
				'desc' 		=> '<br/>' . sprintf( __( 'Page used for displaying courses.', 'lifterlms' ), admin_url( 'options-permalink.php' ) ),
				'id' 		=> 'lifterlms_shop_page_id',
				'type' 		=> 'single_select_page',
				'default'	=> '',
				'class'		=> 'chosen_select_nostd',
			),

			array(
				'title' => __( 'Courses per page', 'lifterlms' ),
				'desc' 		=> '<br/>' . sprintf( __( 'To show all courses on one page, enter -1', 'lifterlms' ), admin_url( 'options-permalink.php' ) ),
				'id' 		=> 'lifterlms_shop_courses_per_page',
				'type' 		=> 'text',
				'default'	=> '10',
				'css' 		=> 'min-width:200px;',
			),

			array( 'type' => 'sectionend', 'id' => 'course_archive_options' ),

			array( 'type' => 'sectionstart', 'id' => 'course_display_options' ),

			array( 'title' => __( 'Display Settings', 'lifterlms' ), 'type' => 'title', 'id' => 'course_options' ),

			array(
				'title' => __( 'Course Purchase Button Text', 'lifterlms' ),
				'desc' 		=> '<br/>' . sprintf( __( 'Enter custom text to display on the Course Purchase Button.', 'lifterlms' ), admin_url( 'options-permalink.php' ) ),
				'id' 		=> 'lifterlms_button_purchase_course_custom_text',
				'type' 		=> 'text',
				'default'	=> 'Take This Course',
				'css' 		=> 'min-width:200px;',
			),

			array(
				'title' => __( 'Membership Signup Button Text', 'lifterlms' ),
				'desc' 		=> '<br/>' . sprintf( __( 'Enter custom text to display on Membership sign up button (displays on course page).', 'lifterlms' ), admin_url( 'options-permalink.php' ) ),
				'id' 		=> 'lifterlms_button_purchase_membership_custom_text',
				'type' 		=> 'text',
				'default'	=> 'Become a Member',
				'css' 		=> 'min-width:200px;',
			),

			array(
				'title'         => __( 'Course Display', 'lifterlms' ),
				'desc'          => __( 'Display author name on course.', 'lifterlms' ),
				'id'            => 'lifterlms_course_display_author',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
				'autoload'      => false,
			),

			array(
				'desc'          => __( 'Display featured image as course banner.', 'lifterlms' ),
				'id'            => 'lifterlms_course_display_banner',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'autoload'      => false,
			),

			array(
				'desc'          => __( 'Display Difficulty on course.', 'lifterlms' ),
				'id'            => 'lifterlms_course_display_difficulty',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'autoload'      => false,
			),

			array(
				'desc'          => __( 'Display Estimated Time on course.', 'lifterlms' ),
				'id'            => 'lifterlms_course_display_length',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'autoload'      => false,
			),

			array(
				'desc'          => __( 'Display Lesson excerpts in lesson navigation', 'lifterlms' ),
				'id'            => 'lifterlms_lesson_nav_display_excerpt',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => 'end',
				'autoload'      => false,
			),

			// course outline settings
			array(
				'title'         => __( 'Course Outline', 'lifterlms' ),
				'desc'          => __( 'Display course outline on the course page', 'lifterlms' ),
				'id'            => 'lifterlms_course_display_outline',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
				'autoload'      => false,
			),

			array(
				'desc'          => __( 'Display  section titles in outline', 'lifterlms' ),
				'id'            => 'lifterlms_course_display_outline_titles',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'autoload'      => false,
			),

			array(
				'desc'          => __( 'Display lesson featured images in outline', 'lifterlms' ),
				'id'            => 'lifterlms_course_display_outline_lesson_thumbnails',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'autoload'      => false,
			),

			array(
				'desc'          => __( 'Enrolled students will see greyed out checkmarks on uncompleted lessons.', 'lifterlms' ),
				'id'            => 'lifterlms_display_lesson_complete_placeholders',
				'default'       => 'no',
				'type'          => 'checkbox',
				'autoload'      => false,
				'checkboxgroup' => 'end',
			),

			array(
				'title'         => __( 'Auto-Advance Lessons', 'lifterlms' ),
				'desc'          => __( 'Automatically advance to the next lesson when a student clicks the Mark Complete button.', 'lifterlms' ),
				'id'            => 'lifterlms_autoadvance',
				'default'       => 'no',
				'type'          => 'checkbox',
				'autoload'      => false,
			),

			array( 'type' => 'sectionend', 'id' => 'course_display_options' ),

		) );
	}

	/**
	 * save settings to the database
	 *
	 * @return LLMS_Admin_Settings::save_fields
	 */
	public function save() {
		$settings = $this->get_settings();

		LLMS_Admin_Settings::save_fields( $settings );

	}

	/**
	 * get settings from the database
	 *
	 * @return array
	 */
	public function output() {
		$settings = $this->get_settings( );

			LLMS_Admin_Settings::output_fields( $settings );
	}

}

return new LLMS_Settings_Courses();
