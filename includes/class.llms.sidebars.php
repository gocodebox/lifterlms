<?php
/**
 * Sidebar Base Class
 *
 * Registers sidebars
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * LLMS_Sidebars
 */
class LLMS_Sidebars {

    /**
     * Constructor
     * Registers sidebars
     */
    public function __construct()
    {
        add_action( 'widgets_init', array(__CLASS__,'register_lesson_sidebars'), 5 );
        add_action( 'widgets_init', array(__CLASS__,'register_course_sidebars'), 5 );
    }

    /**
     * Register Lesson Sidebar
     * @return void
     */
    public static function register_lesson_sidebars () {

        $theme = wp_get_theme();

        // If theme is genesis or a genesis child theme
        if ($theme->template == 'genesis')
        {
            genesis_register_sidebar( array(
                'id' => 'llms-lesson-sidebar',
                'name' => 'Lesson Sidebar',
                'description' => __( 'Widgets in this area will be shown on posts with the post type of lesson.', 'lifterlms' ),
            ) );
            
            add_action('get_header',array('LLMS_Sidebars','cd_change_genesis_lesson_sidebar'));
        }
        else
        {
            register_sidebar( array(
            'name' => __( 'Lesson Sidebar', 'lifterlms' ),
            'id' => 'llms_lesson_widgets_side',
            'description' => __( 'Widgets in this area will be shown on posts with the post type of lesson.', 'lifterlms' ),
            'before_title' => '<h1>',
            'after_title' => '</h1>',
          ) );
        }       
    }

    /**
     * Register Course Sidebar
     * @return void
     */
    public static function register_course_sidebars () {

        $theme = wp_get_theme();

        // If theme is genesis or a genesis child theme
        if ($theme->template == 'genesis')
        {
            genesis_register_sidebar( array(
                'id' => 'llms-course-sidebar',
                'name' => 'Course Sidebar',
                'description' => __( 'Widgets in this area will be shown on posts with the post type of course.', 'lifterlms' ),
                ) );
            
            add_action('get_header', array('LLMS_Sidebars','cd_change_genesis_course_sidebar'));
        }
        else
        {
            register_sidebar( array(
                'name' => __( 'Course Sidebar', 'lifterlms' ),
                'id' => 'llms_course_widgets_side',
                'description' => __( 'Widgets in this area will be shown on posts with the post type of course.', 'lifterlms' ),
                'before_title' => '<h1>',
                'after_title' => '</h1>',
            ) );
        }
    }

    /**
     * Handles registration of genesis course sidebar
     */
    public static function cd_change_genesis_course_sidebar() 
    {
        if ( is_singular('course')) 
        {
            remove_action( 'genesis_sidebar', 'genesis_do_sidebar' ); //remove the default genesis sidebar
            add_action( 'genesis_sidebar', array('LLMS_Sidebars','cd_course_sidebar')); //add an action hook to call the function
        }
    }
    
    /**
     * Handles output of genesis course sidebar
     */
    public static function cd_course_sidebar() 
    {
        dynamic_sidebar( 'llms-course-sidebar' );
    }

    /**
     * Handles registration of genesis lesson sidebar
     */
    public static function cd_change_genesis_lesson_sidebar() 
    {
        if ( is_singular('lesson')) 
        {
            remove_action( 'genesis_sidebar', 'genesis_do_sidebar' ); //remove the default genesis sidebar
            add_action( 'genesis_sidebar', array('LLMS_Sidebars','cd_lesson_sidebar')); //add an action hook to call the function
        }
    }
    
    /**
     * Handles output of genesis lesson sidebar
     */
    public static function cd_lesson_sidebar() 
    {
        dynamic_sidebar( 'llms-lesson-sidebar' );
    }        
}

new LLMS_Sidebars();

