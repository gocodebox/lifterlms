<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * LifterLMS Question Types
 *
 * @since    [version]
 * @version  [version]
 */
class LLMS_Question_Types {

	/**
	 * Initializer
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public static function init() {

		add_filter( 'llms_get_question_types', array( __CLASS__, 'load' ), 5 );

	}

	/**
	 * Retrieve question type model defaults
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public static function get_model() {

		return apply_filters( 'llms_question_type_model_defaults', array(
			'choices' => array(
				'max' => 0,
				'min' => 0,
				'multi' => false,
				'type' => 'text',
			),
			'clarifications' => true,
			'description' => true,
			'default_choices' => array(),
			'grading' => 'auto',
			'icon' => 'question-cirlce',
			'id' => 'generic',
			'image' => true,
			'locked' => false,
			'name' => esc_html__( 'Question', 'lifterlms' ),
			'placeholder' => esc_attr__( 'Enter your question...', 'lifterlms' ),
			'points' => true,
			'video' => true,
		) );

	}

	/**
	 * Retrieve all the default question types loaded by the LifterLMS core
	 * @return   [type]
	 * @since    [version]
	 * @version  [version]
	 */
	private static function get_types() {

		return array(

			'choice' => array(
				'choices' => array(
					'max' => -1,
					'min' => 2,
					'multi' => true,
					'type' => 'text',
				),
				'icon' => 'check',
				'id' => 'choice',
				'name' => esc_html__( 'Multiple Choice', 'lifterlms' ),
			),

			'picture_choice' => array(
				'choices' => array(
					'max' => -1,
					'min' => 2,
					'multi' => true,
					'type' => 'image',
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
					'type' => 'text',
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
					)
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
				'locked' => true,
				'name' => esc_html__( 'Content', 'lifterlms' ),
				'placeholder' => esc_attr__( 'Enter your content title...', 'lifterlms' ),
				'points' => false,
			),

			'group' => array(
				'choices' => false,
				'clarifications' => false,
				'icon' => 'sitemap',
				'id' => 'group',
				'grading' => false,
				'name' => esc_html__( 'Question Group', 'lifterlms' ),
				'placeholder' => esc_attr__( 'Enter your group title...', 'lifterlms' ),
			),

		);

	}

	/**
	 * Load core question types
	 * @param    array     $questions  array of question types (probably empty)
	 * @return   [type]
	 * @since    [version]
	 * @version  [version]
	 */
	public static function load( $questions ) {

		$model = self::get_model();

		foreach ( self::get_types() as $id => $type ) {

			$questions[ $id ] = wp_parse_args( $type, $model );

		}

		return $questions;

	}

}

LLMS_Question_Types::init();
