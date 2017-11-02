<?php
/**
 * LifterLMS Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Sidebars {

	/**
	 * Static Constructor
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function init() {

		// replaces sidebars with course & lesson sidebars
		add_filter( 'sidebars_widgets', array( __CLASS__, 'replace_default_sidebars' ) );

		// registers llms core sidebars
		add_action( 'widgets_init', array( __CLASS__, 'register_sidebars' ), 5 );

		// custom actions for genesis
		add_action( 'genesis_init', array( __CLASS__, 'genesis_support' ) );

	}

	/**
	 * Output course sidebar
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function do_course_sidebar() {
		if ( is_active_sidebar( 'llms_course_widgets_side' ) ) {
			dynamic_sidebar( 'llms_course_widgets_side' );
		}
	}

	/**
	 * Output lesson sidebar
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function do_lesson_sidebar() {
		if ( is_active_sidebar( 'llms_lesson_widgets_side' ) ) {
			dynamic_sidebar( 'llms_lesson_widgets_side' );
		}
	}

	/**
	 * Get the theme default sidebar that will be replaced by course and lesson sidebars
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.1
	 */
	private static function get_theme_default_sidebar() {

		$theme = get_option( 'template' );

		switch ( $theme ) {

			case 'canvas':
				$id = 'primary';
			break;

			case 'Divi':
			case 'twentyeleven':
			case 'twentyfifteen':
			case 'twentyfourteen':
			case 'twentyseventeen':
			case 'twentysixteen':
			case 'twentytwelve':
				$id = 'sidebar-1';
			break;

			case 'twentythirteen':
				$id = 'sidebar-2';
			break;

			case 'twentyten':
				$id = 'primary-widget-area';
			break;

			default:
				$id = '';

		}

		return apply_filters( 'llms_get_theme_default_sidebar', $id, $theme );

	}

	/**
	 * Custom static constructor that modifies methods for native genesis sidebar compatibility
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function genesis_support() {

		// remove default registration in favor of genesis registration methods
		remove_action( 'widgets_init', array( __CLASS__, 'register_sidebars' ), 5 );

		// add genesis registration method
		add_action( 'widgets_init', array( __CLASS__, 'genesis_register_sidebars' ), 5 );

		// replace primary genesis sidebar with our course / lesson sidebar
		add_action( 'genesis_before_sidebar_widget_area', array( __CLASS__, 'genesis_do_sidebar' ) );

		// genesis uses it's own reg method so we can send an empty array of settings
		add_filter( 'llms_sidebar_settings', '__return_empty_array' );

	}

	/**
	 * Outputs llms sidebars in place of the default Genesis Primary Sidebar
	 * Removes the default sidebar action and calls the respective output method
	 * from this class instead
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function genesis_do_sidebar() {

		$post_type = get_post_type();

		if ( in_array( $post_type, array( 'course', 'lesson' ) ) ) {

			remove_action( 'genesis_sidebar', 'genesis_do_sidebar' );

			$method = 'do_' . $post_type . '_sidebar';

			if ( method_exists( __CLASS__, $method ) ) {
				add_action( 'genesis_sidebar', array( __CLASS__, $method ) );
			}
		}

	}

	/**
	 * Register LifteLMS Sidebars using genesis methods
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function genesis_register_sidebars() {

		$sidebars = self::get_sidebars();

		foreach ( $sidebars as $sidebar ) {

			genesis_register_sidebar( $sidebar );

		}

	}

	/**
	 * Get a filtered array of sidebars to register
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function get_sidebars() {

		$sidebars = array(
			apply_filters( 'lifterlms_register_lesson_sidebar', array(
				'id' => 'llms_course_widgets_side',
				'description' => __( 'Widgets in this area will be shown on LifterLMS courses.', 'lifterlms' ),
				'name' => __( 'Course Sidebar', 'lifterlms' ),
			) ),
			apply_filters( 'lifterlms_register_course_sidebar', array(
				'description' => __( 'Widgets in this area will be shown on LifterLMS lessons.', 'lifterlms' ),
				'id' => 'llms_lesson_widgets_side',
				'name' => __( 'Lesson Sidebar', 'lifterlms' ),
			) ),
		);

		$settings = apply_filters( 'llms_sidebar_settings', array(
			'before_widget' => '<li id="%1$s" class="widget %2$s">',
			'after_widget'  => '</li>',
			'before_title'  => '<h2 class="widgettitle">',
			'after_title'   => '</h2>',
		) );

		foreach ( $sidebars as &$s ) {

			$s = array_merge( $settings, $s );

		}

		return $sidebars;
	}

	/**
	 * Registers all sidebars
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function register_sidebars() {

		$sidebars = self::get_sidebars();

		foreach ( $sidebars as $sidebar ) {

			register_sidebar( $sidebar );

		}

	}


	/**
	 * Replaces existing sidebars with Course / Lesson sidebar widgets for supporting themes
	 * @param    array $widgets    array of sidebars and their widgets
	 * @return   array
	 * @since    1.0.0
	 * @version  3.0.0
	 */
	public static function replace_default_sidebars( $widgets ) {

		if ( is_singular( 'course' ) || is_singular( 'lesson' ) ) {

			$sidebar_id = self::get_theme_default_sidebar();

			if ( $sidebar_id ) {

				if ( is_singular( 'course' ) && array_key_exists( 'llms_course_widgets_side', $widgets ) ) {

					$widgets[ $sidebar_id ] = $widgets['llms_course_widgets_side'];

				} elseif ( is_singular( 'lesson' ) && array_key_exists( 'llms_lesson_widgets_side', $widgets ) ) {

					$widgets[ $sidebar_id ] = $widgets['llms_lesson_widgets_side'];

				}
			}
		}

		return $widgets;

	}

}

LLMS_Sidebars::init();
