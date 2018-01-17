<?php
/**
 * LifterLMS Quiz Functions
 *
 * @author    LifterLMS
 * @category  Core
 * @package   LifterLMS/Functions
 * @since     [version]
 * @version   [version]
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Retrieve question choice markers
 * @return   array
 * @since    [version]
 * @version  [version]
 */
function llms_get_question_choice_markers() {
	return apply_filters( 'llms_question_choice_markers', range( 'A', 'Z' ) );
}

/**
 * Retrieve data for a single question type
 * @param    string     $type  id of the question type
 * @return   array|false
 * @since    [version]
 * @version  [version]
 */
function llms_get_question_type( $type ) {

	$types = llms_get_question_types();
	$ret = isset( $types[ $type ] ) ? $types[ $type ] : false;
	return apply_filters( 'llms_get_question_type', $ret, $type );

}

/**
 * Retrieve question type data
 * @return   array
 * @since    [version]
 * @version  [version]
 */
function llms_get_question_types() {

	return apply_filters( 'llms_get_question_types', array(

		'check' => array(
			'choices' => array(
				'max' => -1,
				'min' => 2,
				'multi' => true,
				'type' => 'text',
			),
			'keywords' => array(
				__( 'multi', 'lifterlms' ),
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
			'keywords' => array(
				__( 'image', 'lifterlms' ),
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
			'icon' => 'window-maximize',
			'id' => 'content',
			'name' => esc_html__( 'Content', 'lifterlms' ),
			'points' => false,
		),

		'group' => array(
			'choices' => false,
			'icon' => 'sitemap',
			'id' => 'group',
			'name' => esc_html__( 'Question Group', 'lifterlms' ),
			'points' => true,
		),

	) );

}
