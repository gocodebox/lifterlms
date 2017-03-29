<?php
/**
 * Register Post Types, Taxonomies, Statuses
 * @since    1.0.0
 * @version  3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Post_Types {

	/**
	 * Constructor
	 * @since    1.0.0
	 * @version  3.0.4
	 */
	public static function init () {

		add_action( 'init', array( __CLASS__, 'add_membership_restriction_support' ) );
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
		add_action( 'init', array( __CLASS__, 'register_post_statuses' ), 9 );
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );

		add_action( 'after_setup_theme', array( __CLASS__, 'add_thumbnail_support' ), 777 );

	}

	/**
	 * Add post type support for membership restrictions
	 * This enables the "Membership Access" metabox to display
	 *
	 * @since    3.0.0
	 * @version  3.0.4
	 */
	public static function add_membership_restriction_support() {
		$post_types = apply_filters( 'llms_membership_restricted_post_types', array( 'post', 'page' ) );
		foreach ( $post_types as $post_type ) {
			add_post_type_support( $post_type, 'llms-membership-restrictions' );
		}
	}

	/**
	 * Ensure LifterLMS Post Types have thumbnail support
	 * @return void
	 *
	 * @since    2.4.1
	 * @version  3.0.4
	 */
	public static function add_thumbnail_support() {

		// ensure theme support exists for LifterLMS post types
		if ( ! current_theme_supports( 'post-thumbnails' ) ) {
			add_theme_support( 'post-thumbnails' );
		}

		$thumbnail_post_types = array(
			'course',
			'lesson',
			'llms_membership',
		);

		foreach ( $thumbnail_post_types as $p ) {

			add_post_type_support( $p, 'thumbnail' );

		}

	}

	/**
	 * Register Taxonomies
	 * @since    1.0.0
	 * @version  3.6.0
	 */
	public static function register_taxonomies () {

		if ( ! taxonomy_exists( 'course_type' ) ) {

			do_action( 'lifterlms_register_taxonomy' );

		    /**
		     * @todo this option doesn't exist...
		     */
			$permalinks = get_option( 'lifterlms_permalinks' );

			register_taxonomy( 'course_type',
				apply_filters( 'lifterlms_taxonomy_objects_course_type', array( 'course' ) ),
				apply_filters( 'lifterlms_taxonomy_args_course_type', array(
					'hierarchical' 		=> false,
		            'show_ui' 			=> false,
		            'show_in_nav_menus' => false,
		            'query_var' 		=> is_admin(),
		            'rewrite'			=> false,
		            'public'    		=> false,
					)
				)
			);

			register_taxonomy( 'course_cat',
		        apply_filters( 'lifterlms_taxonomy_objects_course_cat', array( 'course' ) ),
		        apply_filters( 'lifterlms_taxonomy_args_course_cat', array(
		            'hierarchical' 			=> true,
		            'label' 				=> __( 'Course Categories', 'lifterlms' ),
		            'labels' => array(
		                    'name' 				=> __( 'Course Categories', 'lifterlms' ),
		                    'singular_name' 	=> __( 'Course Category', 'lifterlms' ),
							'menu_name'			=> _x( 'Categories', 'Admin menu name', 'lifterlms' ),
		                    'search_items' 		=> __( 'Search Course Categories', 'lifterlms' ),
		                    'all_items' 		=> __( 'All Course Categories', 'lifterlms' ),
		                    'parent_item' 		=> __( 'Parent Course Category', 'lifterlms' ),
		                    'parent_item_colon' => __( 'Parent Course Category:', 'lifterlms' ),
		                    'edit_item' 		=> __( 'Edit Course Category', 'lifterlms' ),
		                    'update_item' 		=> __( 'Update Course Category', 'lifterlms' ),
		                    'add_new_item' 		=> __( 'Add New Course Category', 'lifterlms' ),
		                    'new_item_name' 	=> __( 'New Course Category Name', 'lifterlms' ),
		            	),
		            'show_ui' 				=> true,
		            'query_var' 			=> true,
		            'rewrite' 				=> array(
						'slug'         => empty( $permalinks['category_base'] ) ? _x( 'course-category', 'slug', 'lifterlms' ) : $permalinks['category_base'],
						'with_front'   => false,
						'hierarchical' => true,
		            ),
		        ) )
		    );

			register_taxonomy( 'course_difficulty',
		        apply_filters( 'lifterlms_taxonomy_objects_course_difficulty', array( 'course' ) ),
		        apply_filters( 'lifterlms_taxonomy_args_course_difficulty', array(
		            'hierarchical' 			=> false,
		            'label' 				=> __( 'Course Difficulties', 'lifterlms' ),
		            'labels' => array(
		                    'name' 				=> __( 'Course Difficulties', 'lifterlms' ),
		                    'singular_name' 	=> __( 'Course Difficulty', 'lifterlms' ),
							'menu_name'			=> _x( 'Difficulties', 'Admin menu name', 'lifterlms' ),
		                    'search_items' 		=> __( 'Search Course Difficulties', 'lifterlms' ),
		                    'all_items' 		=> __( 'All Course Difficulties', 'lifterlms' ),
		                    'parent_item' 		=> __( 'Parent Course Difficulty', 'lifterlms' ),
		                    'parent_item_colon' => __( 'Parent Course Difficulty:', 'lifterlms' ),
		                    'edit_item' 		=> __( 'Edit Course Difficulty', 'lifterlms' ),
		                    'update_item' 		=> __( 'Update Course Difficulty', 'lifterlms' ),
		                    'add_new_item' 		=> __( 'Add New Course Difficulty', 'lifterlms' ),
		                    'new_item_name' 	=> __( 'New Course Difficulty Name', 'lifterlms' ),
		            	),
		            'show_ui' 				=> true,
		            'query_var' 			=> true,
		            'rewrite' 				=> array(
						'slug'         => empty( $permalinks['difficulty_base'] ) ? _x( 'course-difficulty', 'slug', 'lifterlms' ) : $permalinks['difficulty_base'],
						'with_front'   => false,
		            ),
		        ) )
		    );

			register_taxonomy( 'course_tag',
		        apply_filters( 'lifterlms_taxonomy_objects_course_tag', array( 'course' ) ),
		        apply_filters( 'lifterlms_taxonomy_args_course_tag', array(
		            'hierarchical' 			=> false,
		            'label' 				=> __( 'Course Tags', 'lifterlms' ),
		            'labels' => array(
		                    'name' 				=> __( 'Course Tags', 'lifterlms' ),
		                    'singular_name' 	=> __( 'Course Tag', 'lifterlms' ),
							'menu_name'			=> _x( 'Tags', 'Admin menu name', 'lifterlms' ),
		                    'search_items' 		=> __( 'Search Course Tags', 'lifterlms' ),
		                    'all_items' 		=> __( 'All Course Tags', 'lifterlms' ),
		                    'parent_item' 		=> __( 'Parent Course Tag', 'lifterlms' ),
		                    'parent_item_colon' => __( 'Parent Course Tag:', 'lifterlms' ),
		                    'edit_item' 		=> __( 'Edit Course Tag', 'lifterlms' ),
		                    'update_item' 		=> __( 'Update Course Tag', 'lifterlms' ),
		                    'add_new_item' 		=> __( 'Add New Course Tag', 'lifterlms' ),
		                    'new_item_name' 	=> __( 'New Course Tag Name', 'lifterlms' ),
		            	),
		            'show_ui' 				=> true,
		            'query_var' 			=> true,
		            'rewrite' 				=> array(
						'slug'       => empty( $permalinks['tag_base'] ) ? _x( 'course-tag', 'slug', 'lifterlms' ) : $permalinks['tag_base'],
						'with_front' => false,
		            ),
		        ) )
		    );

			register_taxonomy( 'course_track',
		        apply_filters( 'lifterlms_taxonomy_objects_course_track', array( 'course' ) ),
		        apply_filters( 'lifterlms_taxonomy_args_course_track', array(
		            'hierarchical' 			=> true,
		            'label' 				=> __( 'Course Track', 'lifterlms' ),
		            'labels' => array(
		                    'name' 				=> __( 'Course Tracks', 'lifterlms' ),
		                    'singular_name' 	=> __( 'Course Track', 'lifterlms' ),
							'menu_name'			=> _x( 'Tracks', 'Admin menu name', 'lifterlms' ),
		                    'search_items' 		=> __( 'Search Course Tracks', 'lifterlms' ),
		                    'all_items' 		=> __( 'All Course Tracks', 'lifterlms' ),
		                    'parent_item' 		=> __( 'Parent Course Track', 'lifterlms' ),
		                    'parent_item_colon' => __( 'Parent Course Track:', 'lifterlms' ),
		                    'edit_item' 		=> __( 'Edit Course Track', 'lifterlms' ),
		                    'update_item' 		=> __( 'Update Course Track', 'lifterlms' ),
		                    'add_new_item' 		=> __( 'Add New Course Track', 'lifterlms' ),
		                    'new_item_name' 	=> __( 'New Course Track Name', 'lifterlms' ),
		            	),
		            'show_ui' 				=> true,
		            'query_var' 			=> true,
		            'rewrite' 				=> array(
						'slug'         => empty( $permalinks['track_base'] ) ? _x( 'course-track', 'slug', 'lifterlms' ) : $permalinks['track_base'],
						'with_front'   => false,
						'hierarchical' => true,
		            ),
		        ) )
		    );

		}

		if ( ! taxonomy_exists( 'membership_cat' ) ) {

			do_action( 'lifterlms_register_taxonomy' );

			register_taxonomy( 'membership_cat',
		        apply_filters( 'lifterlms_taxonomy_objects_membership_cat', array( 'llms_membership' ) ),
		        apply_filters( 'lifterlms_taxonomy_args_membership_cat', array(
		            'hierarchical' 			=> true,
		            'label' 				=> __( 'Membership Categories', 'lifterlms' ),
		            'labels' => array(
		                    'name' 				=> __( 'Membership Categories', 'lifterlms' ),
		                    'singular_name' 	=> __( 'Membership Category', 'lifterlms' ),
							'menu_name'			=> _x( 'Categories', 'Admin menu name', 'lifterlms' ),
		                    'search_items' 		=> __( 'Search Membership Categories', 'lifterlms' ),
		                    'all_items' 		=> __( 'All Membership Categories', 'lifterlms' ),
		                    'parent_item' 		=> __( 'Parent Membership Category', 'lifterlms' ),
		                    'parent_item_colon' => __( 'Parent Membership Category:', 'lifterlms' ),
		                    'edit_item' 		=> __( 'Edit Membership Category', 'lifterlms' ),
		                    'update_item' 		=> __( 'Update Membership Category', 'lifterlms' ),
		                    'add_new_item' 		=> __( 'Add New Membership Category', 'lifterlms' ),
		                    'new_item_name' 	=> __( 'New Membership Category Name', 'lifterlms' ),
		            	),
		            'show_ui' 				=> true,
		            'show_in_menu' 			=> true,
		            'query_var' 			=> true,
		            'rewrite' 				=> array(
						'slug'         => empty( $permalinks['category_base'] ) ? _x( 'membership-category', 'slug', 'lifterlms' ) : $permalinks['category_base'],
						'with_front'   => false,
						'hierarchical' => true,
		            ),
		        ) )
		    );

			register_taxonomy( 'membership_tag',
		        apply_filters( 'lifterlms_taxonomy_objects_membership_tag', array( 'llms_membership' ) ),
		        apply_filters( 'lifterlms_taxonomy_args_membership_tag', array(
		            'hierarchical' 			=> false,
		            'label' 				=> __( 'Membership Tags', 'lifterlms' ),
		            'labels' => array(
		                    'name' 				=> __( 'Membership Tags', 'lifterlms' ),
		                    'singular_name' 	=> __( 'Membership Tag', 'lifterlms' ),
							'menu_name'			=> _x( 'Tags', 'Admin menu name', 'lifterlms' ),
		                    'search_items' 		=> __( 'Search Membership Tags', 'lifterlms' ),
		                    'all_items' 		=> __( 'All Membership Tags', 'lifterlms' ),
		                    'parent_item' 		=> __( 'Parent Membership Tag', 'lifterlms' ),
		                    'parent_item_colon' => __( 'Parent Membership Tag:', 'lifterlms' ),
		                    'edit_item' 		=> __( 'Edit Membership Tag', 'lifterlms' ),
		                    'update_item' 		=> __( 'Update Membership Tag', 'lifterlms' ),
		                    'add_new_item' 		=> __( 'Add New Membership Tag', 'lifterlms' ),
		                    'new_item_name' 	=> __( 'New Membership Tag Name', 'lifterlms' ),
		            	),
		            'show_ui' 				=> true,
		            'show_in_menu' 			=> 'lifterlms',
		            'query_var' 			=> true,
		            'rewrite' 				=> array(
						'slug'       => empty( $permalinks['tag_base'] ) ? _x( 'membership-tag', 'slug', 'lifterlms' ) : $permalinks['tag_base'],
						'with_front' => false,
		            ),
		        ) )
		    );
		}

		register_taxonomy( 'llms_product_visibility',
			apply_filters( 'lifterlms_taxonomy_objects_product_visibility', array( 'course', 'llms_membership' ) ),
			apply_filters( 'lifterlms_taxonomy_args_product_visibility', array(
				'hierarchical'      => false,
				'show_ui'           => false,
				'show_in_nav_menus' => false,
				'query_var'         => is_admin(),
				'rewrite'           => false,
				'public'            => false,
			) )
		);

	}

	/**
	 * Register Post Types
	 * @since    1.0.0
	 * @version  3.6.0
	 */
	public static function register_post_types() {
		if ( post_type_exists( 'course' ) ) {
			return;
		} elseif ( post_type_exists( 'section' ) ) {
			return;
		} elseif ( post_type_exists( 'lesson' ) ) {
			return;
		}

		/**
		 * @todo this doesn't exist or do anything (i think)
		 */
		$permalinks = get_option( 'lifterlms_permalinks' );

		/**
		 * Course Post Type
		 */
		$course_permalink = empty( $permalinks['course_base'] ) ? _x( 'course', 'slug', 'lifterlms' ) : $permalinks['course_base'];

		register_post_type( 'course',
			apply_filters( 'lifterlms_register_post_type_course',
				array(
					'labels' => array(
							'name' 					=> __( 'Courses', 'lifterlms' ),
							'singular_name' 		=> __( 'Course', 'lifterlms' ),
							'menu_name'				=> _x( 'Courses', 'Admin menu name', 'lifterlms' ),
							'add_new' 				=> __( 'Add Course', 'lifterlms' ),
							'add_new_item' 			=> __( 'Add New Course', 'lifterlms' ),
							'edit' 					=> __( 'Edit', 'lifterlms' ),
							'edit_item' 			=> __( 'Edit Course', 'lifterlms' ),
							'new_item' 				=> __( 'New Course', 'lifterlms' ),
							'view' 					=> __( 'View Course', 'lifterlms' ),
							'view_item' 			=> __( 'View Course', 'lifterlms' ),
							'search_items' 			=> __( 'Search Courses', 'lifterlms' ),
							'not_found' 			=> __( 'No Courses found', 'lifterlms' ),
							'not_found_in_trash' 	=> __( 'No Courses found in trash', 'lifterlms' ),
							'parent' 				=> __( 'Parent Course', 'lifterlms' ),
						),
					'description' 			=> __( 'This is where you can add new courses.', 'lifterlms' ),
					'public' 				=> true,
					'show_ui' 				=> ( current_user_can( apply_filters( 'lifterlms_admin_courses_access', 'manage_options' ) ) ) ? true : false,
					'menu_icon'             => 'dashicons-welcome-learn-more',
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> true,
					'exclude_from_search' 	=> false,
					'hierarchical' 			=> false,
					'rewrite' 				=> $course_permalink ? array( 'slug' => untrailingslashit( $course_permalink ), 'with_front' => false, 'feeds' => true ) : false,
					'query_var' 			=> true,
					'supports' 				=> array( 'title', 'excerpt', 'thumbnail', 'comments', 'custom-fields', 'page-attributes', 'author', 'llms-clone-post', 'llms-export-post' ),
					'has_archive' 			=> ( $shop_page_id = llms_get_page_id( 'shop' ) ) && get_page( $shop_page_id ) ? get_page_uri( $shop_page_id ) : 'shop',
					'show_in_nav_menus' 	=> true,
					'menu_position'         => 52,
				)
			)
		);

		register_post_type( 'course_variation',
			apply_filters( 'lifterlms_register_post_type_course_variation',
				array(
					'label'        => __( 'Variations', 'lifterlms' ),
					'public'       => false,
					'hierarchical' => false,
					'supports'     => false,
				)
			)
		);

		/**
		 * Section Post Type
		 */
	    register_post_type( 'section',
		    apply_filters( 'lifterlms_register_post_type_section',
				array(
					'labels' => array(
							'name' 					=> __( 'Sections', 'lifterlms' ),
							'singular_name' 		=> __( 'Section', 'lifterlms' ),
							'add_new' 				=> __( 'Add Section', 'lifterlms' ),
							'add_new_item' 			=> __( 'Add New Section', 'lifterlms' ),
							'edit' 					=> __( 'Edit', 'lifterlms' ),
							'edit_item' 			=> __( 'Edit Section', 'lifterlms' ),
							'new_item' 				=> __( 'New Section', 'lifterlms' ),
							'view' 					=> __( 'View Section', 'lifterlms' ),
							'view_item' 			=> __( 'View Section', 'lifterlms' ),
							'search_items' 			=> __( 'Search Sections', 'lifterlms' ),
							'not_found' 			=> __( 'No Sections found', 'lifterlms' ),
							'not_found_in_trash' 	=> __( 'No Sections found in trash', 'lifterlms' ),
							'parent' 				=> __( 'Parent Sections', 'lifterlms' ),
							'menu_name'				=> _x( 'Sections', 'Admin menu name', 'lifterlms' ),
						),
					'description' 			=> __( 'This is where sections are stored.', 'lifterlms' ),
					'public' 				=> false,
					'show_ui' 				=> false,
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> false,
					'exclude_from_search' 	=> true,
					'hierarchical' 			=> false,
					'show_in_nav_menus' 	=> false,
					'rewrite' 				=> false,
					'query_var' 			=> false,
					'supports' 				=> array( 'title' ),
					'has_archive' 			=> false,
				)
			)
		);

		/**
		 * Lesson Post Type
		 */
		$lesson_permalink = empty( $permalinks['lesson_base'] ) ? _x( 'lesson', 'slug', 'lifterlms' ) : $permalinks['lesson_base'];

	    register_post_type( 'lesson',
		    apply_filters( 'lifterlms_register_post_type_lesson',
				array(
					'labels' => array(
							'name' 					=> __( 'Lessons', 'lifterlms' ),
							'singular_name' 		=> __( 'Lesson', 'lifterlms' ),
							'add_new' 				=> __( 'Add Lesson', 'lifterlms' ),
							'add_new_item' 			=> __( 'Add New Lesson', 'lifterlms' ),
							'edit' 					=> __( 'Edit', 'lifterlms' ),
							'edit_item' 			=> __( 'Edit Lesson', 'lifterlms' ),
							'new_item' 				=> __( 'New Lesson', 'lifterlms' ),
							'view' 					=> __( 'View Lesson', 'lifterlms' ),
							'view_item' 			=> __( 'View Lesson', 'lifterlms' ),
							'search_items' 			=> __( 'Search Lessons', 'lifterlms' ),
							'not_found' 			=> __( 'No Lessons found', 'lifterlms' ),
							'not_found_in_trash' 	=> __( 'No Lessons found in trash', 'lifterlms' ),
							'parent' 				=> __( 'Parent Lessons', 'lifterlms' ),
							'menu_name'				=> _x( 'Lessons', 'Admin menu name', 'lifterlms' ),
						),
					'description' 			=> __( 'This is where you can view all of the lessons.', 'lifterlms' ),
					'public' 				=> true,
					'show_ui' 				=> ( current_user_can( apply_filters( 'lifterlms_admin_courses_access', 'manage_options' ) ) ) ? true : false,
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> true,
					'exclude_from_search' 	=> true,
					'show_in_menu' 			=> 'edit.php?post_type=course',
					'hierarchical' 			=> false,
					'rewrite' 				=> $lesson_permalink ? array( 'slug' => untrailingslashit( $lesson_permalink ), 'with_front' => false, 'feeds' => true ) : false,
					'show_in_nav_menus' 	=> false,
					'query_var' 			=> true,
					'supports' 				=> array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields', 'page-attributes', 'author' ),
				)
			)
		);

		/**
		 * Membership Post Type
		 */
		$membership_permalink = empty( $permalinks['membership_base'] ) ? _x( 'membership', 'slug', 'lifterlms' ) : $permalinks['membership_base'];
		register_post_type( 'llms_membership',
			apply_filters( 'lifterlms_register_post_type_membership',
				array(
					'labels' => array(
							'name' 					=> __( 'Membership', 'lifterlms' ),
							'singular_name' 		=> __( 'Membership', 'lifterlms' ),
							'menu_name'				=> _x( 'Memberships', 'Admin menu name', 'lifterlms' ),
							'add_new' 				=> __( 'Add Membership', 'lifterlms' ),
							'add_new_item' 			=> __( 'Add New Membership', 'lifterlms' ),
							'edit' 					=> __( 'Edit', 'lifterlms' ),
							'edit_item' 			=> __( 'Edit Membership', 'lifterlms' ),
							'new_item' 				=> __( 'New Membership', 'lifterlms' ),
							'view' 					=> __( 'View Membership', 'lifterlms' ),
							'view_item' 			=> __( 'View Membership', 'lifterlms' ),
							'search_items' 			=> __( 'Search Memberships', 'lifterlms' ),
							'not_found' 			=> __( 'No Memberships found', 'lifterlms' ),
							'not_found_in_trash' 	=> __( 'No Memberships found in trash', 'lifterlms' ),
							'parent' 				=> __( 'Parent Membership', 'lifterlms' ),
						),
					'description' 			=> __( 'This is where you can add new Membership levels.', 'lifterlms' ),
					'public' 				=> true,
					'show_ui' 				=> ( current_user_can( apply_filters( 'lifterlms_admin_membership_access', 'manage_options' ) ) ) ? true : false,
					'map_meta_cap'			=> true,
					'menu_icon'             => 'dashicons-groups',
					'publicly_queryable' 	=> true,
					'exclude_from_search' 	=> false,
					'show_in_menu' 			=> true,
					'hierarchical' 			=> false,
					'rewrite' 				=> $membership_permalink ? array( 'slug' => untrailingslashit( $membership_permalink ), 'with_front' => false, 'feeds' => true ) : false,
					'query_var' 			=> true,
					'supports' 				=> array( 'title', 'thumbnail', 'comments', 'custom-fields', 'page-attributes', 'author' ),
					'has_archive' 			=> ( $membership_page_id = llms_get_page_id( 'memberships' ) ) && get_page( $membership_page_id ) ? get_page_uri( $membership_page_id ) : 'memberships',
					'show_in_nav_menus' 	=> true,
					'menu_position'         => 52,
				)
			)
		);

		/**
		 * Engagement Post type
		 */
	    register_post_type( 'llms_engagement',
		    apply_filters( 'lifterlms_register_post_type_llms_engagement',
				array(
					'labels' => array(
							'name' 					=> __( 'Engagements', 'lifterlms' ),
							'singular_name' 		=> __( 'Engagement', 'lifterlms' ),
							'add_new' 				=> __( 'Add Engagement', 'lifterlms' ),
							'add_new_item' 			=> __( 'Add New Engagement', 'lifterlms' ),
							'edit' 					=> __( 'Edit', 'lifterlms' ),
							'edit_item' 			=> __( 'Edit Engagement', 'lifterlms' ),
							'new_item' 				=> __( 'New Engagement', 'lifterlms' ),
							'view' 					=> __( 'View Engagement', 'lifterlms' ),
							'view_item' 			=> __( 'View Engagement', 'lifterlms' ),
							'search_items' 			=> __( 'Search Engagement', 'lifterlms' ),
							'not_found' 			=> __( 'No Engagement found', 'lifterlms' ),
							'not_found_in_trash' 	=> __( 'No Engagement found in trash', 'lifterlms' ),
							'parent' 				=> __( 'Parent Engagement', 'lifterlms' ),
							'menu_name'				=> _x( 'Engagements', 'Admin menu name', 'lifterlms' ),
						),
					'description' 			=> __( 'This is where engagements are stored.', 'lifterlms' ),
					'public' 				=> false,
					'show_ui' 				=> ( current_user_can( apply_filters( 'lifterlms_admin_engagements_access', 'manage_options' ) ) ) ? true : false,
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> false,
					'exclude_from_search' 	=> true,
					// 'show_in_menu' 			=> 'lifterlms',
					'menu_position'         => 52,
					'menu_icon'             => 'dashicons-awards',
					'hierarchical' 			=> false,
					'show_in_nav_menus' 	=> false,
					'rewrite' 				=> false,
					'query_var' 			=> false,
					'supports' 				=> array( 'title' ),
					'has_archive' 			=> false,
				)
			)
		);

		/**
		 * Order post type
		 */
	    register_post_type( 'llms_order',
		    apply_filters( 'lifterlms_register_post_type_order',
				array(
					'labels' => array(
							'name' 					=> __( 'Orders', 'lifterlms' ),
							'singular_name' 		=> __( 'Order', 'lifterlms' ),
							'add_new' 				=> __( 'Add Order', 'lifterlms' ),
							'add_new_item' 			=> __( 'Add New Order', 'lifterlms' ),
							'edit' 					=> __( 'Edit', 'lifterlms' ),
							'edit_item' 			=> __( 'Edit Order', 'lifterlms' ),
							'new_item' 				=> __( 'New Order', 'lifterlms' ),
							'view' 					=> __( 'View Order', 'lifterlms' ),
							'view_item' 			=> __( 'View Order', 'lifterlms' ),
							'search_items' 			=> __( 'Search Orders', 'lifterlms' ),
							'not_found' 			=> __( 'No Orders found', 'lifterlms' ),
							'not_found_in_trash' 	=> __( 'No Orders found in trash', 'lifterlms' ),
							'parent' 				=> __( 'Parent Orders', 'lifterlms' ),
							'menu_name'				=> _x( 'Orders', 'Admin menu name', 'lifterlms' ),
						),
					'description' 			=> __( 'This is where orders are managed', 'lifterlms' ),
					'public' 				=> false,
					'show_ui' 				=> ( current_user_can( apply_filters( 'lifterlms_admin_order_access', 'manage_options' ) ) ) ? true : false,
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> false,
					'menu_icon'             => 'dashicons-cart',
					'menu_position'         => 52,
					'exclude_from_search' 	=> true,
					'hierarchical' 			=> false,
					'show_in_nav_menus' 	=> false,
					'rewrite' 				=> false,
					'query_var' 			=> false,
					'supports' 				=> array( 'title', 'comments', 'custom-fields' ),
					'has_archive' 			=> false,
					'capabilities'  	    => array( 'create_posts' => false ),
				)
			)
		);

	    /**
	     * Transaction Post Type
	     */
	    register_post_type( 'llms_transaction',
		    apply_filters( 'lifterlms_register_post_type_transaction',
				array(
					'labels' => array(
							'name' 					=> __( 'Transactions', 'lifterlms' ),
							'singular_name' 		=> __( 'Transaction', 'lifterlms' ),
							'add_new' 				=> __( 'Add Transaction', 'lifterlms' ),
							'add_new_item' 			=> __( 'Add New Transaction', 'lifterlms' ),
							'edit' 					=> __( 'Edit', 'lifterlms' ),
							'edit_item' 			=> __( 'Edit Transaction', 'lifterlms' ),
							'new_item' 				=> __( 'New Transaction', 'lifterlms' ),
							'view' 					=> __( 'View Transaction', 'lifterlms' ),
							'view_item' 			=> __( 'View Transaction', 'lifterlms' ),
							'search_items' 			=> __( 'Search Transactions', 'lifterlms' ),
							'not_found' 			=> __( 'No Transactions found', 'lifterlms' ),
							'not_found_in_trash' 	=> __( 'No Transactions found in trash', 'lifterlms' ),
							'parent' 				=> __( 'Parent Transactions', 'lifterlms' ),
							'menu_name'				=> _x( 'Orders', 'Admin menu name', 'lifterlms' ),
						),
					'description' 			=> __( 'This is where single and recurring order transactions are stored', 'lifterlms' ),
					'public' 				=> false,
					'show_ui' 				=> false,
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> false,
					'exclude_from_search' 	=> true,
					'show_in_menu' 			=> false,
					'hierarchical' 			=> false,
					'show_in_nav_menus' 	=> false,
					'rewrite' 				=> false,
					'query_var' 			=> false,
					'supports' 				=> array( '' ),
					'has_archive' 			=> false,
					'capabilities'  	    => array( 'create_posts' => false ),
				)
			)
		);

		/**
		 * Achievement Post type
		 */
	    register_post_type( 'llms_achievement',
		    apply_filters( 'lifterlms_register_post_type_llms_achievement',
				array(
					'labels' => array(
							'name' 					=> __( 'Achievements', 'lifterlms' ),
							'singular_name' 		=> __( 'Achievement', 'lifterlms' ),
							'add_new' 				=> __( 'Add Achievement', 'lifterlms' ),
							'add_new_item' 			=> __( 'Add New Achievement', 'lifterlms' ),
							'edit' 					=> __( 'Edit', 'lifterlms' ),
							'edit_item' 			=> __( 'Edit Achievement', 'lifterlms' ),
							'new_item' 				=> __( 'New Achievement', 'lifterlms' ),
							'view' 					=> __( 'View Achievement', 'lifterlms' ),
							'view_item' 			=> __( 'View Achievement', 'lifterlms' ),
							'search_items' 			=> __( 'Search Achievement', 'lifterlms' ),
							'not_found' 			=> __( 'No Achievement found', 'lifterlms' ),
							'not_found_in_trash' 	=> __( 'No Achievement found in trash', 'lifterlms' ),
							'parent' 				=> __( 'Parent Achievement', 'lifterlms' ),
							'menu_name'				=> _x( 'Achievements', 'Admin menu name', 'lifterlms' ),
						),
					'description' 			=> __( 'This is where achievements are stored.', 'lifterlms' ),
					'public' 				=> false,
					'show_ui' 				=> ( current_user_can( apply_filters( 'lifterlms_admin_achievements_access', 'manage_options' ) ) ) ? true : false,
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> false,
					'exclude_from_search' 	=> true,
					'show_in_menu' 			=> 'edit.php?post_type=llms_engagement',
					'hierarchical' 			=> false,
					'show_in_nav_menus' 	=> false,
					'rewrite' 				=> false,
					'query_var' 			=> false,
					'supports' 				=> array( 'title' ),
					'has_archive' 			=> false,
				)
			)
		);

		/**
		 * Certificate Post type
		 */
		$certificate_permalink = empty( $permalinks['certificate_base'] ) ? _x( 'certificate', 'slug', 'lifterlms' ) : $permalinks['certificate_base'];

	    register_post_type( 'llms_certificate',
		    apply_filters( 'lifterlms_register_post_type_llms_certificate',
				array(
					'labels' => array(
							'name' 					=> __( 'Certificates', 'lifterlms' ),
							'singular_name' 		=> __( 'Certificate', 'lifterlms' ),
							'add_new' 				=> __( 'Add Certificate', 'lifterlms' ),
							'add_new_item' 			=> __( 'Add New Certificate', 'lifterlms' ),
							'edit' 					=> __( 'Edit', 'lifterlms' ),
							'edit_item' 			=> __( 'Edit Certificate', 'lifterlms' ),
							'new_item' 				=> __( 'New Certificate', 'lifterlms' ),
							'view' 					=> __( 'View Certificate', 'lifterlms' ),
							'view_item' 			=> __( 'View Certificate', 'lifterlms' ),
							'search_items' 			=> __( 'Search Certificates', 'lifterlms' ),
							'not_found' 			=> __( 'No Certificates found', 'lifterlms' ),
							'not_found_in_trash' 	=> __( 'No Certificates found in trash', 'lifterlms' ),
							'parent' 				=> __( 'Parent Certificates', 'lifterlms' ),
							'menu_name'				=> _x( 'Certificates', 'Admin menu name', 'lifterlms' ),
						),
					'description' 			=> __( 'This is where you can view all of the certificates.', 'lifterlms' ),
					'public' 				=> true,
					'show_ui' 				=> ( current_user_can( apply_filters( 'lifterlms_admin_certificates_access', 'manage_options' ) ) ) ? true : false,
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> ( current_user_can( apply_filters( 'lifterlms_admin_certificates_access', 'manage_options' ) ) ) ? true : false,
					'exclude_from_search' 	=> true,
					'show_in_menu' 			=> 'edit.php?post_type=llms_engagement',
					'hierarchical' 			=> false,
					'rewrite' 				=> $certificate_permalink ? array( 'slug' => untrailingslashit( $certificate_permalink ), 'with_front' => false, 'feeds' => true ) : false,
					'show_in_nav_menus' 	=> false,
					'query_var' 			=> true,
					'supports' 				=> array( 'title', 'editor' ),
				)
			)
		);

		/**
		 * User specific certificate
		 */
		$my_certificate_permalink = empty( $permalinks['my_certificate_base'] ) ? _x( 'my_certificate', 'slug', 'lifterlms' ) : $permalinks['my_certificate_base'];
	    register_post_type( 'llms_my_certificate',
		    apply_filters( 'lifterlms_register_post_type_llms_my_certificate',
				array(
					'labels' => array(
							'name' 					=> __( 'My Certificates', 'lifterlms' ),
							'singular_name' 		=> __( 'My Certificate', 'lifterlms' ),
							'add_new' 				=> __( 'Add My Certificate', 'lifterlms' ),
							'add_new_item' 			=> __( 'Add New My Certificate', 'lifterlms' ),
							'edit' 					=> __( 'Edit', 'lifterlms' ),
							'edit_item' 			=> __( 'Edit My Certificate', 'lifterlms' ),
							'new_item' 				=> __( 'New My Certificate', 'lifterlms' ),
							'view' 					=> __( 'View My Certificate', 'lifterlms' ),
							'view_item' 			=> __( 'View My Certificate', 'lifterlms' ),
							'search_items' 			=> __( 'Search My Certificates', 'lifterlms' ),
							'not_found' 			=> __( 'No My Certificates found', 'lifterlms' ),
							'not_found_in_trash' 	=> __( 'No My Certificates found in trash', 'lifterlms' ),
							'parent' 				=> __( 'Parent My Certificates', 'lifterlms' ),
							'menu_name'				=> _x( 'My Certificates', 'Admin menu name', 'lifterlms' ),
						),
					'description' 			=> __( 'This is where you can view all of the certificates.', 'lifterlms' ),
					'public' 				=> true,
					'show_ui' 				=> true,
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> true,
					'exclude_from_search' 	=> true,
					'show_in_menu' 			=> false,
					'hierarchical' 			=> false,
					'rewrite' 				=> $my_certificate_permalink ? array( 'slug' => untrailingslashit( $my_certificate_permalink ), 'with_front' => false, 'feeds' => true ) : false,
					'show_in_nav_menus' 	=> false,
					'query_var' 			=> true,
					'supports' 				=> array( 'title', 'editor' ),
				)
			)
		);

		/**
		 * Email Post Type
		 */
	    register_post_type( 'llms_email',
		    apply_filters( 'lifterlms_register_post_type_llms_email',
				array(
					'labels' => array(
							'name' 					=> __( 'Emails', 'lifterlms' ),
							'singular_name' 		=> __( 'Email', 'lifterlms' ),
							'add_new' 				=> __( 'Add Email', 'lifterlms' ),
							'add_new_item' 			=> __( 'Add New Email', 'lifterlms' ),
							'edit' 					=> __( 'Edit', 'lifterlms' ),
							'edit_item' 			=> __( 'Edit Email', 'lifterlms' ),
							'new_item' 				=> __( 'New Email', 'lifterlms' ),
							'view' 					=> __( 'View Email', 'lifterlms' ),
							'view_item' 			=> __( 'View Email', 'lifterlms' ),
							'search_items' 			=> __( 'Search Emails', 'lifterlms' ),
							'not_found' 			=> __( 'No Emails found', 'lifterlms' ),
							'not_found_in_trash' 	=> __( 'No Emails found in trash', 'lifterlms' ),
							'parent' 				=> __( 'Parent Emails', 'lifterlms' ),
							'menu_name'				=> _x( 'Emails', 'Admin menu name', 'lifterlms' ),
						),
					'description' 			=> __( 'This is where emails are stored.', 'lifterlms' ),
					'public' 				=> false,
					'show_ui' 				=> ( current_user_can( apply_filters( 'lifterlms_admin_emails_access', 'manage_options' ) ) ) ? true : false,
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> false,
					'exclude_from_search' 	=> true,
					'show_in_menu' 			=> 'edit.php?post_type=llms_engagement',
					'hierarchical' 			=> false,
					'show_in_nav_menus' 	=> false,
					'rewrite' 				=> false,
					'query_var' 			=> false,
					'supports' 				=> array( 'title', 'editor' ),
					'has_archive' 			=> false,
				)
			)
		);

		/**
		 * Quiz Post Type
		 */
		$quiz_permalink = empty( $permalinks['quiz_base'] ) ? _x( 'llms_quiz', 'slug', 'lifterlms' ) : $permalinks['quiz_base'];
	    register_post_type( 'llms_quiz',
		    apply_filters( 'lifterlms_register_post_type_quiz',
				array(
					'labels' => array(
							'name' 					=> __( 'Quizzes', 'lifterlms' ),
							'singular_name' 		=> __( 'Quiz', 'lifterlms' ),
							'add_new' 				=> __( 'Add Quiz', 'lifterlms' ),
							'add_new_item' 			=> __( 'Add New Quiz', 'lifterlms' ),
							'edit' 					=> __( 'Edit', 'lifterlms' ),
							'edit_item' 			=> __( 'Edit Quiz', 'lifterlms' ),
							'new_item' 				=> __( 'New Quiz', 'lifterlms' ),
							'view' 					=> __( 'View Quiz', 'lifterlms' ),
							'view_item' 			=> __( 'View Quiz', 'lifterlms' ),
							'search_items' 			=> __( 'Search Quiz', 'lifterlms' ),
							'not_found' 			=> __( 'No Quizzes found', 'lifterlms' ),
							'not_found_in_trash' 	=> __( 'No Quizzes found in trash', 'lifterlms' ),
							'parent' 				=> __( 'Parent Quizzes', 'lifterlms' ),
							'menu_name'				=> _x( 'Quizzes', 'Admin menu name', 'lifterlms' ),
						),
					'description' 			=> __( 'This is where you can view all of the quizzes.', 'lifterlms' ),
					'public' 				=> true,
					'show_ui' 				=> ( current_user_can( apply_filters( 'lifterlms_admin_courses_access', 'manage_options' ) ) ) ? true : false,
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> true,
					'exclude_from_search' 	=> true,
					'show_in_menu' 			=> 'edit.php?post_type=course',
					'hierarchical' 			=> false,
					'rewrite' 				=> $quiz_permalink ? array( 'slug' => untrailingslashit( $quiz_permalink ), 'with_front' => false, 'feeds' => true ) : false,
					'show_in_nav_menus' 	=> false,
					'query_var' 			=> true,
					'supports' 				=> array( 'title', 'editor', 'author', 'custom-fields' ),
				)
			)
		);

		/**
		 * Quiz Question Post Type
		 */
		$question_permalink = empty( $permalinks['question_base'] ) ? _x( 'llms_question', 'slug', 'lifterlms' ) : $permalinks['question_base'];
	    register_post_type( 'llms_question',
		    apply_filters( 'lifterlms_register_post_type_question',
				array(
					'labels' => array(
							'name' 					=> __( 'Questions', 'lifterlms' ),
							'singular_name' 		=> __( 'Question', 'lifterlms' ),
							'add_new' 				=> __( 'Add Question', 'lifterlms' ),
							'add_new_item' 			=> __( 'Add New Question', 'lifterlms' ),
							'edit' 					=> __( 'Edit', 'lifterlms' ),
							'edit_item' 			=> __( 'Edit Question', 'lifterlms' ),
							'new_item' 				=> __( 'New Question', 'lifterlms' ),
							'view' 					=> __( 'View Question', 'lifterlms' ),
							'view_item' 			=> __( 'View Question', 'lifterlms' ),
							'search_items' 			=> __( 'Search Questions', 'lifterlms' ),
							'not_found' 			=> __( 'No Questions found', 'lifterlms' ),
							'not_found_in_trash' 	=> __( 'No Questions found in trash', 'lifterlms' ),
							'parent' 				=> __( 'Parent Questions', 'lifterlms' ),
							'menu_name'				=> _x( 'Quiz Questions', 'Admin menu name', 'lifterlms' ),
						),
					'description' 			=> __( 'This is where you can view all of the Quiz Questions.', 'lifterlms' ),
					'public' 				=> true,
					'show_ui' 				=> ( current_user_can( apply_filters( 'lifterlms_admin_courses_access', 'manage_options' ) ) ) ? true : false,
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> true,
					'exclude_from_search' 	=> true,
					'show_in_menu' 			=> 'edit.php?post_type=course',
					'hierarchical' 			=> false,
					'rewrite' 				=> $question_permalink ? array( 'slug' => untrailingslashit( $question_permalink ), 'with_front' => false, 'feeds' => true ) : false,
					'show_in_nav_menus' 	=> false,
					'query_var' 			=> true,
					'supports' 				=> array( 'title', 'editor' ),
				)
			)
		);

		/**
		 * Coupon Post type
		 */
	    register_post_type( 'llms_coupon',
		    apply_filters( 'lifterlms_register_post_type_llms_coupon',
				array(
					'labels' => array(
							'name' 					=> __( 'Coupons', 'lifterlms' ),
							'singular_name' 		=> __( 'Coupon', 'lifterlms' ),
							'add_new' 				=> __( 'Add Coupon', 'lifterlms' ),
							'add_new_item' 			=> __( 'Add New Coupon', 'lifterlms' ),
							'edit' 					=> __( 'Edit', 'lifterlms' ),
							'edit_item' 			=> __( 'Edit Coupon', 'lifterlms' ),
							'new_item' 				=> __( 'New Coupon', 'lifterlms' ),
							'view' 					=> __( 'View Coupon', 'lifterlms' ),
							'view_item' 			=> __( 'View Coupon', 'lifterlms' ),
							'search_items' 			=> __( 'Search Coupon', 'lifterlms' ),
							'not_found' 			=> __( 'No Coupon found', 'lifterlms' ),
							'not_found_in_trash' 	=> __( 'No Coupon found in trash', 'lifterlms' ),
							'parent' 				=> __( 'Parent Coupon', 'lifterlms' ),
							'menu_name'				=> _x( 'Coupons', 'Admin menu name', 'lifterlms' ),
						),
					'description' 			=> __( 'This is where coupons are stored.', 'lifterlms' ),
					'public' 				=> false,
					'show_ui' 				=> ( current_user_can( apply_filters( 'lifterlms_admin_coupons_access', 'manage_options' ) ) ) ? true : false,
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> false,
					'exclude_from_search' 	=> true,
					'show_in_menu' 			=> 'edit.php?post_type=llms_order',
					'hierarchical' 			=> false,
					'show_in_nav_menus' 	=> false,
					'rewrite' 				=> false,
					'query_var' 			=> false,
					'supports' 				=> array( 'title' ),
					'has_archive' 			=> false,
				)
			)
		);

		/**
		 * Voucher Post type
		 */
		register_post_type( 'llms_voucher',
			apply_filters( 'lifterlms_register_post_type_llms_voucher',
				array(
					'labels' => array(
						'name' 					=> __( 'Vouchers', 'lifterlms' ),
						'singular_name' 		=> __( 'Voucher', 'lifterlms' ),
						'add_new' 				=> __( 'Add Voucher', 'lifterlms' ),
						'add_new_item' 			=> __( 'Add New Voucher', 'lifterlms' ),
						'edit' 					=> __( 'Edit', 'lifterlms' ),
						'edit_item' 			=> __( 'Edit Voucher', 'lifterlms' ),
						'new_item' 				=> __( 'New Voucher', 'lifterlms' ),
						'view' 					=> __( 'View Voucher', 'lifterlms' ),
						'view_item' 			=> __( 'View Voucher', 'lifterlms' ),
						'search_items' 			=> __( 'Search Voucher', 'lifterlms' ),
						'not_found' 			=> __( 'No Voucher found', 'lifterlms' ),
						'not_found_in_trash' 	=> __( 'No Voucher found in trash', 'lifterlms' ),
						'parent' 				=> __( 'Parent Voucher', 'lifterlms' ),
						'menu_name'				=> _x( 'Vouchers', 'Admin menu name', 'lifterlms' ),
					),
					'description' 			=> __( 'This is where voucher are stored.', 'lifterlms' ),
					'public' 				=> false,
					'show_ui' 				=> ( current_user_can( apply_filters( 'lifterlms_admin_vouchers_access', 'manage_options' ) ) ) ? true : false,
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> false,
					'exclude_from_search' 	=> true,
					'show_in_menu' 			=> 'edit.php?post_type=llms_order',
					'hierarchical' 			=> false,
					'show_in_nav_menus' 	=> false,
					'rewrite' 				=> false,
					'query_var' 			=> false,
					'supports' 				=> array( 'title' ),
					'has_archive' 			=> false,
				)
			)
		);

		/**
		 * Review Post Type
		 */
		register_post_type( 'llms_review',
			apply_filters( 'lifterlms_register_post_type_review',
				array(
					'labels' => array(
							'name' 					=> __( 'Reviews', 'lifterlms' ),
							'singular_name' 		=> __( 'Review', 'lifterlms' ),
							'menu_name'				=> _x( 'Reviews', 'Admin menu name', 'lifterlms' ),
							'add_new' 				=> __( 'Add Review', 'lifterlms' ),
							'add_new_item' 			=> __( 'Add New Review', 'lifterlms' ),
							'edit' 					=> __( 'Edit', 'lifterlms' ),
							'edit_item' 			=> __( 'Edit Review', 'lifterlms' ),
							'new_item' 				=> __( 'New Review', 'lifterlms' ),
							'view' 					=> __( 'View Review', 'lifterlms' ),
							'view_item' 			=> __( 'View Review', 'lifterlms' ),
							'search_items' 			=> __( 'Search Reviews', 'lifterlms' ),
							'not_found' 			=> __( 'No Reviews found', 'lifterlms' ),
							'not_found_in_trash' 	=> __( 'No Reviews found in trash', 'lifterlms' ),
							'parent' 				=> __( 'Parent Review', 'lifterlms' ),
						),
					'description' 			=> __( 'This is where you can add new reviews.', 'lifterlms' ),
					'public' 				=> false,
					'show_ui' 				=> ( current_user_can( apply_filters( 'lifterlms_admin_reviews_access', 'manage_options' ) ) ) ? true : false,
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> false,
					'exclude_from_search' 	=> true,
					'show_in_menu' 			=> 'edit.php?post_type=course',
					'hierarchical' 			=> false,
					'show_in_nav_menus' 	=> false,
					'rewrite' 				=> false,
					'query_var' 			=> false,
					'has_archive' 			=> false,
					'supports' 				=> array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields', 'page-attributes' ),
				)
			)
		);

		/**
		 * Access Plan Post Type
		 */
	    register_post_type( 'llms_access_plan',
		    apply_filters( 'lifterlms_register_post_type_access_plan',
				array(
					'labels' => array(
							'name' 					=> __( 'Access Plans', 'lifterlms' ),
							'singular_name' 		=> __( 'Access Plan', 'lifterlms' ),
							'add_new' 				=> __( 'Add Access Plan', 'lifterlms' ),
							'add_new_item' 			=> __( 'Add New Access Plan', 'lifterlms' ),
							'edit' 					=> __( 'Edit', 'lifterlms' ),
							'edit_item' 			=> __( 'Edit Access Plan', 'lifterlms' ),
							'new_item' 				=> __( 'New Access Plan', 'lifterlms' ),
							'view' 					=> __( 'View Access Plan', 'lifterlms' ),
							'view_item' 			=> __( 'View Access Plan', 'lifterlms' ),
							'search_items' 			=> __( 'Search Access Plans', 'lifterlms' ),
							'not_found' 			=> __( 'No Access Plans found', 'lifterlms' ),
							'not_found_in_trash' 	=> __( 'No Access Plans found in trash', 'lifterlms' ),
							'parent' 				=> __( 'Parent Access Plans', 'lifterlms' ),
							'menu_name'				=> _x( 'Access Plans', 'Admin menu name', 'lifterlms' ),
						),
					'description' 			=> __( 'This is where access plans are stored.', 'lifterlms' ),
					'public' 				=> false,
					'show_ui' 				=> false,
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> false,
					'exclude_from_search' 	=> true,
					/**
					 * Making this post type hierachical prevents a conflict
					 * with the Redirection plugin (https://wordpress.org/plugins/redirection/)
					 * When 301 monitoring is turned on, Redirection creates access plans
					 * for each access plan that redirect the course or membership
					 * to the site's home page
					 * @since    3.0.4
					 * @version  3.0.4
					 */
					'hierarchical' 			=> true,
					'show_in_nav_menus' 	=> false,
					'rewrite' 				=> false,
					'query_var' 			=> false,
					'supports' 				=> array( 'title' ),
					'has_archive' 			=> false,
				)
			)
		);

	}

	/**
	 * Register post statuses
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.4
	 */
	public static function register_post_statuses() {

		$order_statuses = apply_filters( 'lifterlms_register_order_post_statuses',
			array(
				// single payment only
				'llms-completed'  => array(
					'label'                     => _x( 'Completed', 'Order status', 'lifterlms' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', 'lifterlms' ),
				),

				// recurring only
				'llms-active'  => array(
					'label'                     => _x( 'Active', 'Order status', 'lifterlms' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'lifterlms' ),
				),
				'llms-expired'  => array(
					'label'                     => _x( 'Expired', 'Order status', 'lifterlms' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'lifterlms' ),
				),

				// shared
				'llms-pending'    => array(
					'label'                     => _x( 'Pending Payment', 'Order status', 'lifterlms' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Pending Payment <span class="count">(%s)</span>', 'Pending Payment <span class="count">(%s)</span>', 'lifterlms' ),
				),
				'llms-cancelled'  => array(
					'label'                     => _x( 'Cancelled', 'Order status', 'lifterlms' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'lifterlms' ),
				),
				'llms-refunded'   => array(
					'label'                     => _x( 'Refunded', 'Order status', 'lifterlms' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'lifterlms' ),
				),
				'llms-failed'     => array(
					'label'                     => _x( 'Failed', 'Order status', 'lifterlms' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'lifterlms' ),
				),
			)
		);

		$txn_statuses = apply_filters( 'lifterlms_register_transaction_post_statuses',
			array(
				'llms-txn-failed'  => array(
					'label'                     => _x( 'Failed', 'Transaction status', 'lifterlms' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'lifterlms' ),
				),
				'llms-txn-pending'  => array(
					'label'                     => _x( 'Pending', 'Transaction status', 'lifterlms' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'lifterlms' ),
				),
				'llms-txn-refunded'  => array(
					'label'                     => _x( 'Refunded', 'Transaction status', 'lifterlms' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'lifterlms' ),
				),
				'llms-txn-succeeded'  => array(
					'label'                     => _x( 'Succeeded', 'Transaction status', 'lifterlms' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Succeeded <span class="count">(%s)</span>', 'Succeeded <span class="count">(%s)</span>', 'lifterlms' ),
				),
			)
		);

		foreach ( array_merge( $order_statuses, $txn_statuses ) as $status => $values ) {

			register_post_status( $status, $values );

		}

	}

}

LLMS_Post_Types::init();
