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
 * LLMS_Post_Types
 */
class LLMS_Post_Types {

	public function __construct () {
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
	}

	/**
	 * Register Taxonomies
	 */
	public static function register_taxonomies () {
		if ( taxonomy_exists( 'course_type' ) )
			return;

		do_action( 'lifterlms_register_taxonomy' );

	    //no permalinks yet... soon
		$permalinks = get_option( 'lifterlms_permalinks' ); 

		register_taxonomy( 'course_type',
			apply_filters( 'lifterlms_taxonomy_objects_course_type', array( 'course' ) ),
			apply_filters( 'lifterlms_taxonomy_args_course_type', array( 
				'hierarchical' 		=> false,
	            'show_ui' 			=> false,
	            'show_in_nav_menus' => false,
	            'query_var' 		=> is_admin(),
	            'rewrite'			=> false,
	            'public'    		=> false
				) 
			)
		);

		register_taxonomy( 'course_cat',
	        apply_filters( 'lifterlms_taxonomy_objects_course_cat', array( 'course' ) ),
	        apply_filters( 'lifterlms_taxonomy_args_course_cat', array(
	            'hierarchical' 			=> true,
	            'update_count_callback' => '_llms_term_recount',
	            'label' 				=> __( 'Course Categories', 'lifterlms' ),
	            'labels' => array(
	                    'name' 				=> __( 'Course Categories', 'lifterlms' ),
	                    'singular_name' 	=> __( 'Course Category', 'lifterlms' ),
						'menu_name'			=> _x( 'Course Categories', 'Admin menu name', 'lifterlms' ),
	                    'search_items' 		=> __( 'Search Course Categories', 'lifterlms' ),
	                    'all_items' 		=> __( 'All Course Categories', 'lifterlms' ),
	                    'parent_item' 		=> __( 'Parent Course Category', 'lifterlms' ),
	                    'parent_item_colon' => __( 'Parent Course Category:', 'lifterlms' ),
	                    'edit_item' 		=> __( 'Edit Course Category', 'lifterlms' ),
	                    'update_item' 		=> __( 'Update Course Category', 'lifterlms' ),
	                    'add_new_item' 		=> __( 'Add New Course Category', 'lifterlms' ),
	                    'new_item_name' 	=> __( 'New Course Category Name', 'lifterlms' )
	            	),
	            'show_ui' 				=> true,
	            'query_var' 			=> true,
	            //'capabilities'			=> array(
	            	//'manage_terms' 		=> 'manage_course_terms',
					//'edit_terms' 		=> 'edit_course_terms',
					// 'delete_terms' 		=> 'delete_course_terms',
					// 'assign_terms' 		=> 'assign_course_terms',
	             //),
	            'rewrite' 				=> array(
					'slug'         => empty( $permalinks['category_base'] ) ? _x( 'course-category', 'slug', 'lifterlms' ) : $permalinks['category_base'],
					'with_front'   => false,
					'hierarchical' => true,
	            ),
	        ) )
	    );

		register_taxonomy( 'course_tag',
	        apply_filters( 'lifterlms_taxonomy_objects_course_tag', array( 'course' ) ),
	        apply_filters( 'lifterlms_taxonomy_args_course_tag', array(
	            'hierarchical' 			=> false,
	            'update_count_callback' => '_llms_term_recount',
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
	                    'new_item_name' 	=> __( 'New Course Tag Name', 'lifterlms' )
	            	),
	            'show_ui' 				=> true,
	            'query_var' 			=> true,
				// 'capabilities'			=> array(
				// 	'manage_terms' 		=> 'manage_course_terms',
				// 	'edit_terms' 		=> 'edit_course_terms',
				// 	'delete_terms' 		=> 'delete_course_terms',
				// 	'assign_terms' 		=> 'assign_course_terms',
				// ),
	            'rewrite' 				=> array(
					'slug'       => empty( $permalinks['tag_base'] ) ? _x( 'course-tag', 'slug', 'lifterlms' ) : $permalinks['tag_base'],
					'with_front' => false
	            ),
	        ) )
	    );

		register_taxonomy( 'course_difficulty',
	        apply_filters( 'lifterlms_taxonomy_objects_course_difficulty', array( 'course' ) ),
	        apply_filters( 'lifterlms_taxonomy_args_course_difficulty', array(
	            'hierarchical' 			=> false,
	            'update_count_callback' => '_llms_term_recount',
	            'label' 				=> __( 'Course Difficulties', 'lifterlms' ),
	            'labels' => array(
	                    'name' 				=> __( 'Course Difficulties', 'lifterlms' ),
	                    'singular_name' 	=> __( 'Course Difficulty', 'lifterlms' ),
						'menu_name'			=> _x( 'Course Difficulties', 'Admin menu name', 'lifterlms' ),
	                    'search_items' 		=> __( 'Search Course Difficulties', 'lifterlms' ),
	                    'all_items' 		=> __( 'All Course Difficulties', 'lifterlms' ),
	                    'parent_item' 		=> __( 'Parent Course Difficulty', 'lifterlms' ),
	                    'parent_item_colon' => __( 'Parent Course Difficulty:', 'lifterlms' ),
	                    'edit_item' 		=> __( 'Edit Course Difficulty', 'lifterlms' ),
	                    'update_item' 		=> __( 'Update Course Difficulty', 'lifterlms' ),
	                    'add_new_item' 		=> __( 'Add New Course Difficulty', 'lifterlms' ),
	                    'new_item_name' 	=> __( 'New Course Difficulty Name', 'lifterlms' )
	            	),
	            'show_ui' 				=> true,
	            'query_var' 			=> true,
	            //'capabilities'			=> array(
	            	//'manage_terms' 		=> 'manage_course_terms',
					//'edit_terms' 		=> 'edit_course_terms',
					// 'delete_terms' 		=> 'delete_course_terms',
					// 'assign_terms' 		=> 'assign_course_terms',
	             //),
	            'rewrite' 				=> array(
					'slug'         => empty( $permalinks['difficulty_base'] ) ? _x( 'course-difficulty', 'slug', 'lifterlms' ) : $permalinks['difficulty_base'],
					'with_front'   => false,
	            ),
	        ) )
	    );

		global $llms_course_attributes, $lifterlms;

		$llms_course_attributes = array();

		if ( $attribute_taxonomies = llms_get_attribute_taxonomies() ) {
			foreach ( $attribute_taxonomies as $tax ) {
				if ( $name = $llms_attribute_taxonomy_name( $tax->attribute_name ) ) {
					$label = ! empty( $tax->attribute_label ) ? $tax->attribute_label : $tax->attribute_name;
				
					$llms_course_attributes[ $name ] = $tax;

					register_taxonomy( $name,
				        apply_filters( 'lifterlms_taxonomy_objects_' . $name, array( 'course' ) ),
				        apply_filters( 'lifterlms_taxonomy_args_' . $name, array(
				            'hierarchical' 				=> true,
	            			'update_count_callback' 	=> '_update_post_term_count',
				            'labels' => array(
				                    'name' 						=> $label,
				                    'singular_name' 			=> $label,
				                    'search_items' 				=> sprintf( __( 'Search %s', 'lifterlms' ), $label ),
				                    'all_items' 				=> sprintf( __( 'All %s', 'lifterlms' ), $label ),
				                    'parent_item' 				=> sprintf( __( 'Parent %s', 'lifterlms' ), $label ),
				                    'parent_item_colon' 		=> sprintf( __( 'Parent %s:', 'lifterlms' ), $label ),
				                    'edit_item' 				=> sprintf( __( 'Edit %s', 'lifterlms' ), $label ),
				                    'update_item' 				=> sprintf( __( 'Update %s', 'lifterlms' ), $label ),
				                    'add_new_item' 				=> sprintf( __( 'Add New %s', 'lifterlms' ), $label ),
				                    'new_item_name' 			=> sprintf( __( 'New %s', 'lifterlms' ), $label )
				            	),
				            'show_ui' 					=> false,
				            'query_var' 				=> true,
				    //         'capabilities'			=> array(
				    //         	'manage_terms' 		=> 'manage_course_terms',
								// 'edit_terms' 		=> 'edit_course_terms',
								// 'delete_terms' 		=> 'delete_course_terms',
								// 'assign_terms' 		=> 'assign_course_terms',
				    //         ),
				            'show_in_nav_menus' 		=> apply_filters( 'lifterlms_attribute_show_in_nav_menus', false, $name ),
				            'rewrite' 					=> array(
								'slug'         => ( empty( $permalinks['attribute_base'] ) ? '' : trailingslashit( $permalinks['attribute_base'] ) ) . sanitize_title( $tax->attribute_name ),
								'with_front'   => false,
								'hierarchical' => true
				            ),
				        ) )
				    );
				}
			}
			do_action( 'llms_after_register_taxonomy' );
		}
	}

	/**
	 * Register Post Types
	 */
	public static function register_post_types() {
		if ( post_type_exists('course') ) {
			return;
		}
		if ( post_type_exists('section') ) {
			return;
		}
		if ( post_type_exists('lesson') ) {
			return;
		}

		do_action( 'lifterlms_register_post_type' );

		$permalinks = get_option( 'lifterlms_permalinks' );

		/**
		 * Course Post Type
		 */
		$course_permalink = empty( $permalinks['course_base'] ) ? _x( 'course', 'slug', 'lifterlms' ) : $permalinks['course_base'];
		
		register_post_type( "course",
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
							'parent' 				=> __( 'Parent Course', 'lifterlms' )
						),
					'description' 			=> __( 'This is where you can add new courses.', 'lifterlms' ),
					'public' 				=> true,
					'show_ui' 				=> true,
					//'capability_type' 		=> 'course',
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> true,
					'exclude_from_search' 	=> false,
					'hierarchical' 			=> false, 
					'rewrite' 				=> $course_permalink ? array( 'slug' => untrailingslashit( $course_permalink ), 'with_front' => false, 'feeds' => true ) : false,
					'query_var' 			=> true,
					'supports' 				=> array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields', 'page-attributes' ),
					'has_archive' 			=> ( $shop_page_id = llms_get_page_id( 'shop' ) ) && get_page( $shop_page_id ) ? get_page_uri( $shop_page_id ) : 'shop',
					'show_in_nav_menus' 	=> true
				)
			)
		);

		register_post_type( "course_variation",
			apply_filters( 'lifterlms_register_post_type_course_variation',
				array(
					'label'        => __( 'Variations', 'lifterlms' ),
					'public'       => false,
					'hierarchical' => false,
					'supports'     => false
				)
			)
		);


		/**
		 * Section Post Type
		 */
	    register_post_type( "section",
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
							'menu_name'				=> _x('Sections', 'Admin menu name', 'lifterlms' )
						),
					'description' 			=> __( 'This is where sections are stored.', 'lifterlms' ),
					'public' 				=> false,
					'show_ui' 				=> true,
					//'capability_type' 		=> 'section',
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> false,
					'exclude_from_search' 	=> true,
					'show_in_menu' 			=> 'edit.php?post_type=course',
					'hierarchical' 			=> false,
					'show_in_nav_menus' 	=> false,
					'rewrite' 				=> false,
					'query_var' 			=> false,
					'supports' 				=> array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields', 'page-attributes' ),
					'has_archive' 			=> false,
				)
			)
		);

		/**
		 * Lesson Post Type
		 */

		$lesson_permalink = empty( $permalinks['lesson_base'] ) ? _x( 'lesson', 'slug', 'lifterlms' ) : $permalinks['lesson_base'];

	    register_post_type( "lesson",
		    apply_filters( 'lifterlms_register_post_type_section',
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
							'menu_name'				=> _x('Lessons', 'Admin menu name', 'lifterlms' )
						),
					'description' 			=> __( 'This is where you can view all of the lessons.', 'lifterlms' ),
					'public' 				=> true,
					'show_ui' 				=> true,
					//'capability_type' 		=> 'lesson',
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> true,
					'exclude_from_search' 	=> false,
					'show_in_menu' 			=> 'edit.php?post_type=course',
					'hierarchical' 			=> false,
					'rewrite' 				=> $lesson_permalink ? array( 'slug' => untrailingslashit( $lesson_permalink ), 'with_front' => false, 'feeds' => true ) : false,
					'show_in_nav_menus' 	=> false,
					'query_var' 			=> true,
					'supports' 				=> array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields', 'page-attributes' ),
					//'has_archive' 			=> false,
				)
			)
		);


	}

}

new LLMS_Post_types();
