<?php
/**
 * Setup menus in WP Admin.
 *
 * @author 		codeBOX
 * @category 	Admin
 * @package 	LifterLMS/Course
 * @version     0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * LLMS_Sidebars
 */
class LLMS_Sidebars {

    public function __construct()
    {
        add_action( 'widgets_init', array(__CLASS__,'register_lesson_sidebars'), 5 );
        add_action( 'widgets_init', array(__CLASS__,'register_course_sidebars'), 5 );
    }


  public function register_lesson_sidebars () {

    register_sidebar( array(
          'name' => __( 'Lesson Sidebar', 'lifterlms' ),
          'id' => 'llms_lesson_widgets_side',
          'description' => __( 'Widgets in this area will be shown on posts with the post type of lesson.', 'lifterlms' ),
          'before_title' => '<h1>',
          'after_title' => '</h1>',
      ) );
  }

  public function register_course_sidebars () {

    register_sidebar( array(
          'name' => __( 'Course Sidebar', 'lifterlms' ),
          'id' => 'llms_course_widgets_side',
          'description' => __( 'Widgets in this area will be shown on posts with the post type of course.', 'lifterlms' ),
          'before_title' => '<h1>',
          'after_title' => '</h1>',
      ) );
  }
}
new LLMS_Sidebars();

