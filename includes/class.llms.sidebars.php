<?php
/**
 * Sidebar Base Class
 *
 * Registers sidebars
 *
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * LLMS_Sidebars
 */
class LLMS_Sidebars {

	/**
	 * Constructor
	 * Registers sidebars
	 */
	public function __construct() {

		add_filter( 'sidebars_widgets', array( __CLASS__, 'replace_default_sidebars' ) );
		add_action( 'widgets_init', array( __CLASS__, 'register_lesson_sidebars' ), 5 );
		add_action( 'widgets_init', array( __CLASS__, 'register_course_sidebars' ), 5 );

	}

	/**
	 * Display lesson and course custom sidebars
	 *
	 * @param  array $sidebars_widgets [WP array of widgets in sidebar]
	 * @return array $sidebars_widgets [Filtered WP array of widgets in sidebar]
	 */
	public static function replace_default_sidebars( $sidebars_widgets ) {
		if (is_singular( 'course' ) && array_key_exists( 'llms_course_widgets_side', $sidebars_widgets )) {
			$sidebars_widgets['sidebar-1'] = $sidebars_widgets['llms_course_widgets_side'];
			$sidebars_widgets['layers-right-sidebar'] = $sidebars_widgets['llms_course_widgets_side'];
			$sidebars_widgets['main-sidebar'] = $sidebars_widgets['llms_course_widgets_side'];
			$sidebars_widgets['single-sidebar'] = $sidebars_widgets['llms_course_widgets_side'];
			$sidebars_widgets['primary'] = $sidebars_widgets['llms_course_widgets_side']; // woocanvas
		} elseif (is_singular( 'lesson' ) && array_key_exists( 'llms_lesson_widgets_side', $sidebars_widgets )) {
			$sidebars_widgets['sidebar-1'] = $sidebars_widgets['llms_lesson_widgets_side'];
			$sidebars_widgets['layers-right-sidebar'] = $sidebars_widgets['llms_lesson_widgets_side'];
			$sidebars_widgets['single-sidebar'] = $sidebars_widgets['llms_lesson_widgets_side'];
			$sidebars_widgets['main-sidebar'] = $sidebars_widgets['llms_course_widgets_side'];
			$sidebars_widgets['primary'] = $sidebars_widgets['llms_lesson_widgets_side']; // woocanvas
		}
		return $sidebars_widgets;

	}

	/**
	 * Register Lesson Sidebar
	 * @return void
	 */
	public static function register_lesson_sidebars () {

		$theme = wp_get_theme();

		// If theme is genesis or a genesis child theme
		if ($theme->template == 'genesis') {
			genesis_register_sidebar( apply_filters( 'lifterlms_register_lesson_sidebar', array(
				'id' => 'llms-lesson-sidebar',
				'name' => 'Lesson Sidebar',
				'description' => __( 'Widgets in this area will be shown on posts with the post type of lesson.', 'lifterlms' ),
			) ) );

			add_action( 'get_header',array( 'LLMS_Sidebars', 'cd_change_genesis_lesson_sidebar' ) );
		} else {
			register_sidebar( apply_filters( 'lifterlms_register_lesson_sidebar', array(
				'name' => __( 'Lesson Sidebar', 'lifterlms' ),
				'id' => 'llms_lesson_widgets_side',
				'description' => __( 'Widgets in this area will be shown on posts with the post type of lesson.', 'lifterlms' ),
				'before_title' => '<h1>',
				'after_title' => '</h1>',
			) ) );
		}
	}

	/**
	 * Register Course Sidebar
	 * @return void
	 */
	public static function register_course_sidebars () {

		$theme = wp_get_theme();

		// If theme is genesis or a genesis child theme
		if ($theme->template == 'genesis') {
			genesis_register_sidebar( apply_filters( 'lifterlms_register_course_sidebar', array(
				'id' => 'llms-course-sidebar',
				'name' => 'Course Sidebar',
				'description' => __( 'Widgets in this area will be shown on posts with the post type of course.', 'lifterlms' ),
			) ) );

			add_action( 'get_header', array( 'LLMS_Sidebars', 'cd_change_genesis_course_sidebar' ) );
		} else {
			register_sidebar( apply_filters( 'lifterlms_register_course_sidebar', array(
				'name' => __( 'Course Sidebar', 'lifterlms' ),
				'id' => 'llms_course_widgets_side',
				'description' => __( 'Widgets in this area will be shown on posts with the post type of course.', 'lifterlms' ),
				'before_title' => '<h1>',
				'after_title' => '</h1>',
			) ) );
		}
	}

	/**
	 * Handles registration of genesis course sidebar
	 */
	public static function cd_change_genesis_course_sidebar() {

		if ( is_singular( 'course' )) {
			remove_action( 'genesis_sidebar', 'genesis_do_sidebar' ); //remove the default genesis sidebar
			add_action( 'genesis_sidebar', array( 'LLMS_Sidebars', 'cd_course_sidebar' ) ); //add an action hook to call the function
		}
	}

	/**
	 * Handles output of genesis course sidebar
	 */
	public static function cd_course_sidebar() {

		dynamic_sidebar( 'llms-course-sidebar' );
	}

	/**
	 * Handles registration of genesis lesson sidebar
	 */
	public static function cd_change_genesis_lesson_sidebar() {

		if ( is_singular( 'lesson' )) {
			remove_action( 'genesis_sidebar', 'genesis_do_sidebar' ); //remove the default genesis sidebar
			add_action( 'genesis_sidebar', array( 'LLMS_Sidebars', 'cd_lesson_sidebar' ) ); //add an action hook to call the function
		}
	}

	/**
	 * Handles output of genesis lesson sidebar
	 */
	public static function cd_lesson_sidebar() {

		dynamic_sidebar( 'llms-lesson-sidebar' );
	}
}

new LLMS_Sidebars();

