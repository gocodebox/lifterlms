<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'LLMS_Settings_Courses' ) ) :

/**
* Admin Settings Page, Courses Tab
*
* @version 1.0
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
			$shop_page_id = llms_get_page_id('shop');

			$base_slug = ($shop_page_id > 0 && get_page( $shop_page_id )) ? get_page_uri( $shop_page_id ) : 'shop';

			return apply_filters( 'lifterlms_course_settings', array(

				array(	'title' => __( 'Course Settings', 'lifterlms' ), 'type' => 'title','desc' => 'Customize your courses for a unique user experience.', 'id' => 'course_options' ),

				array(
					'title' => __( 'Courses Page', 'lifterlms' ),
					'desc' 		=> '<br/>' . sprintf( __( 'Page used for displaying courses.', 'lifterlms' ), admin_url( 'options-permalink.php' ) ),
					'id' 		=> 'lifterlms_shop_page_id',
					'type' 		=> 'single_select_page',
					'default'	=> '',
					'class'		=> 'chosen_select_nostd',
					'css' 		=> 'min-width:300px;',
				),

				array( 'type' => 'sectionend', 'id' => 'course_options' ),

				array( 'title' => __( 'Display Settings', 'lifterlms' ), 'type' => 'title', 'id' => 'course_options' ),

				array(
				'title'         => __( 'Course Display', 'lifterlms' ),
				'desc'          => __( 'Display author name on course.', 'lifterlms' ),
				'id'            => 'lifterlms_course_display_author',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
				'autoload'      => false
				),

				array(
				'desc'          => __( 'Display featured image as course banner.', 'lifterlms' ),
				'id'            => 'lifterlms_course_display_banner',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
				'autoload'      => false
				),

				array(
				'desc'          => __( 'Display Difficulty on course.', 'lifterlms' ),
				'id'            => 'lifterlms_course_display_difficulty',
				'default'       => 'no',
				'type'          => 'checkbox',
				'autoload'      => false
				),

				array(
				'desc'          => __( 'Display Estimated Time on course.', 'lifterlms' ),
				'id'            => 'lifterlms_course_display_length',
				'default'       => 'no',
				'type'          => 'checkbox',
				'autoload'      => false
				),

				array(
				'desc'          => __( 'Display Section Title on Syllabus', 'lifterlms' ),
				'id'            => 'lifterlms_course_display_syllabus',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'autoload'      => false
				),

				array(
				'desc'          => __( 'Display Lesson excerpts in lesson navigation', 'lifterlms' ),
				'id'            => 'lifterlms_lesson_nav_display_excerpt',
				'default'       => 'no',
				'type'          => 'checkbox',
				'autoload'      => false
				),

				array( 'type' => 'sectionend', 'id' => 'course_options'),

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

endif;

return new LLMS_Settings_Courses();
