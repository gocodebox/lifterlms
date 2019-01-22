<?php
/**
 * LifterLMS Question Types
 *
 * @since    3.16.0
 * @version  3.16.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Question_Types class.
 */
class LLMS_Question_Types {

	/**
	 * Initializer
	 *
	 * @return   void
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public static function init() {

		add_filter( 'llms_get_question_types', array( __CLASS__, 'load' ), 5 );

	}

	/**
	 * Retrieve question type model defaults
	 *
	 * @return   array
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public static function get_model() {

		return apply_filters( 'llms_question_type_model_defaults', array(
			'choices' => array(
				'selectable' => true,
				'markers' => range( 'A', 'Z' ),
				'max' => 26,
				'min' => 2,
				'multi' => true,
				'type' => 'text',
			),
			'clarifications' => true,
			'description' => true,
			'default_choices' => array(),
			'grading' => 'auto',
			'group' => array(
				'order' => 20,
				'name' => __( 'Other', 'lifterlms' ),
			),
			'icon' => 'question-cirlce',
			'id' => 'generic',
			'image' => true,
			'name' => esc_html__( 'Question', 'lifterlms' ),
			'placeholder' => esc_attr__( 'Enter your question...', 'lifterlms' ),
			'points' => true,
			'random_lock' => false,
			'video' => true,
		) );

	}

	/**
	 * Retrieve all the default question types loaded by the LifterLMS core
	 *
	 * @return   array
	 * @since    3.16.0
	 * @version  3.27.0
	 */
	private static function get_types() {

		$upgrade_url = 'https://lifterlms.com/product/advanced-quizzes/?utm_source=LifterLMS%20Plugin&utm_medium=Quiz%20Builder%20Button&utm_campaign=Advanced%20Question%20Upsell&utm_content=3.16.0&utm_term=';

		return array(

			'choice' => array(
				'choices' => array(),
				'group' => array(
					'order' => 0,
					'name' => __( 'Basic Questions', 'lifterlms' ),
				),
				'icon' => 'check',
				'id' => 'choice',
				'name' => esc_html__( 'Multiple Choice', 'lifterlms' ),
			),

			'picture_choice' => array(
				'choices' => array(
					'type' => 'image',
				),
				'group' => array(
					'order' => 0,
					'name' => __( 'Basic Questions', 'lifterlms' ),
				),
				'icon' => 'picture-o',
				'id' => 'picture_choice',
				'name' => esc_html__( 'Picture Choice', 'lifterlms' ),
			),

			'true_false' => array(
				'choices' => array(
					'max' => 2,
					'min' => 2,
					'multi' => false,
				),
				'default_choices' => array(
					array(
						'choice' => esc_html__( 'True', 'lifterlms' ),
						'correct' => true,
						'marker' => 'A',
					),
					array(
						'choice' => esc_html__( 'False', 'lifterlms' ),
						'marker' => 'B',
					),
				),
				'group' => array(
					'order' => 0,
					'name' => __( 'Basic Questions', 'lifterlms' ),
				),
				'icon' => 'toggle-on',
				'id' => 'true_false',
				'name' => esc_html__( 'True or False', 'lifterlms' ),
			),

			'content' => array(
				'choices' => false,
				'clarifications' => false,
				'icon' => 'window-maximize',
				'id' => 'content',
				'grading' => false,
				'name' => esc_html__( 'Content', 'lifterlms' ),
				'placeholder' => esc_attr__( 'Enter your content title...', 'lifterlms' ),
				'points' => false,
				'random_lock' => true,
			),

			'existing' => array(
				'choices' => false,
				'clarifications' => false,
				'icon' => 'file-text-o',
				'id' => 'existing',
				'grading' => false,
				'name' => esc_html__( 'Add Existing Question', 'lifterlms' ),
				'placeholder' => '',
				'points' => false,
				'random_lock' => true,
			),

			// 'group' => array(
			// 	'choices' => false,
			// 	'clarifications' => false,
			// 	'group' => array(
			//		'order' => 0,
			//		'name' => __( 'Basic Questions', 'lifterlms' )
			//	),
			// 	'icon' => 'sitemap',
			// 	'id' => 'group',
			// 	'grading' => false,
			// 	'name' => esc_html__( 'Question Group', 'lifterlms' ),
			// 	'placeholder' => esc_attr__( 'Enter your group title...', 'lifterlms' ),
			// ),

			'blank' => array(
				'choices' => false,
				'group' => array(
					'order' => 10,
					'name' => __( 'Advanced Questions', 'lifterlms' ),
				),
				'icon' => 'window-minimize',
				'id' => 'blank',
				'name' => esc_html__( 'Fill in the Blank', 'lifterlms' ),
				'upgrade' => $upgrade_url . 'blank',
			),

			'reorder' => array(
				'choices' => false,
				'group' => array(
					'order' => 10,
					'name' => __( 'Advanced Questions', 'lifterlms' ),
				),
				'icon' => 'sort-numeric-asc',
				'id' => 'reorder',
				'name' => esc_html__( 'Reorder Items', 'lifterlms' ),
				'upgrade' => $upgrade_url . 'reorder',
			),

			'picture_reorder' => array(
				'choices' => false,
				'group' => array(
					'order' => 10,
					'name' => __( 'Advanced Questions', 'lifterlms' ),
				),
				'icon' => 'picture-o',
				'id' => 'picture_reorder',
				'name' => esc_html__( 'Reorder Pictures', 'lifterlms' ),
				'upgrade' => $upgrade_url . 'picture_reorder',
			),

			'short_answer' => array(
				'choices' => false,
				'group' => array(
					'order' => 10,
					'name' => __( 'Advanced Questions', 'lifterlms' ),
				),
				'icon' => 'align-left',
				'id' => 'short_answer',
				'name' => esc_html__( 'Short Answer', 'lifterlms' ),
				'upgrade' => $upgrade_url . 'short_answer',
			),

			'long_answer' => array(
				'choices' => false,
				'group' => array(
					'order' => 10,
					'name' => __( 'Advanced Questions', 'lifterlms' ),
				),
				'icon' => 'paragraph',
				'id' => 'long_answer',
				'name' => esc_html__( 'Long Answer', 'lifterlms' ),
				'upgrade' => $upgrade_url . 'long_answer',
			),

			'upload' => array(
				'choices' => false,
				'group' => array(
					'order' => 10,
					'name' => __( 'Advanced Questions', 'lifterlms' ),
				),
				'icon' => 'cloud-upload',
				'id' => 'upload',
				'name' => esc_html__( 'File Upload', 'lifterlms' ),
				'upgrade' => $upgrade_url . 'upload',
			),

			'code' => array(
				'choices' => false,
				'group' => array(
					'order' => 10,
					'name' => __( 'Advanced Questions', 'lifterlms' ),
				),
				'icon' => 'code',
				'id' => 'code',
				'name' => esc_html__( 'Code', 'lifterlms' ),
				'upgrade' => $upgrade_url . 'code',
			),

			'scale' => array(
				'choices' => false,
				'group' => array(
					'order' => 10,
					'name' => __( 'Advanced Questions', 'lifterlms' ),
				),
				'icon' => 'sliders',
				'id' => 'scale',
				'name' => esc_html__( 'Scale', 'lifterlms' ),
				'upgrade' => $upgrade_url . 'scale',
			),

		);

	}

	/**
	 * Load core question types
	 *
	 * @param    array     $questions  array of question types (probably empty).
	 * @return   void
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public static function load( $questions ) {

		$model = self::get_model();

		foreach ( self::get_types() as $id => $type ) {

			if ( is_array( $type['choices'] ) ) {
				$type['choices'] = wp_parse_args( $type['choices'], $model['choices'] );
			}
			$questions[ $id ] = wp_parse_args( $type, $model );

		}

		return $questions;

	}

}

LLMS_Question_Types::init();
