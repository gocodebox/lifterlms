<?php
/**
 * Modify LifterLMS Custom Post Types for Gutenberg editor compatibility
 *
 * @package  LifterLMS_Blocks/Main
 * @since    1.0.0
 * @version  1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Setup editor templates for LifterLMS custom Post Types
 */
class LLMS_Blocks_Post_Types {

	/**
	 * Constructor
	 *
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function __construct() {

		// Enable REST API for custom post types.
		add_filter( 'lifterlms_register_post_type_course', array( $this, 'enable_rest' ), 5 );
		add_filter( 'lifterlms_register_post_type_lesson', array( $this, 'enable_rest' ), 5 );
		add_filter( 'lifterlms_register_post_type_membership', array( $this, 'enable_rest' ), 5 );

		// Enable REST API for custom post taxonomies.
		add_filter( 'lifterlms_register_taxonomy_args_course_cat', array( $this, 'enable_rest' ), 5 );
		add_filter( 'lifterlms_register_taxonomy_args_course_tag', array( $this, 'enable_rest' ), 5 );
		add_filter( 'lifterlms_register_taxonomy_args_course_track', array( $this, 'enable_rest' ), 5 );
		add_filter( 'lifterlms_register_taxonomy_args_course_difficulty', array( $this, 'enable_rest' ), 5 );

		// Setup block editor templates.
		add_filter( 'lifterlms_register_post_type_course', array( $this, 'add_course_template' ), 5 );
		add_filter( 'lifterlms_register_post_type_lesson', array( $this, 'add_lesson_template' ), 5 );

	}

	/**
	 * Enable the rest API for custom post types & taxonomies
	 *
	 * @param   array $data post type / taxonomy data.
	 * @return  array
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function enable_rest( $data ) {

		$data['show_in_rest'] = true;

		return $data;

	}

	/**
	 * Add an editor template for courses
	 *
	 * @param   array $post_type post type data.
	 * @return  array
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function add_course_template( $post_type ) {

		$post_type['template'] = array(
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Add a short description of your course visible to all visitors...', 'lifterlms' ),
				),
			),
			array( 'llms/course-information' ),
			array( 'llms/instructors' ),
			array( 'llms/pricing-table' ),
			array( 'llms/course-progress' ),
			array( 'llms/course-continue-button' ),
			array( 'llms/course-syllabus' ),
		);

		return $post_type;

	}

	/**
	 * Add an editor template for lessons
	 *
	 * @param   array $post_type post type data.
	 * @return  array
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function add_lesson_template( $post_type ) {

		$post_type['template'] = array(
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Add your lesson content...', 'lifterlms' ),
				),
			),
			array( 'llms/lesson-progression' ),
			array( 'llms/lesson-navigation' ),
		);

		return $post_type;

	}

}

return new LLMS_Blocks_Post_Types();
