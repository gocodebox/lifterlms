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
 * Retrieve the number of columns needed for a picture choice question
 * @param    int     $num_choices  number of choices
 * @return   int
 * @since    [version]
 * @version  [version]
 */
function llms_get_picture_choice_question_cols( $num_choices ) {

	/**
	 * Allow 3rd parties to override this function with a custom number of columns
	 * If this responds with a non null will bypass column counter function return it immediately
	 */
	$cols = apply_filters( 'llms_get_picture_choice_question_cols', null, $num_choices );

	if ( 1 === $num_choices ) {
		$cols = 1;
	} elseif ( $num_choices >= 25 ) {
		$cols = 5;
	} elseif ( $num_choices >= 10 ) {
		$max_cols = 5;
		$min_cols = 3;
	} else {
		$max_cols = 4;
		$min_cols = 2;
	}

	if ( is_null( $cols ) ) {

		$i = $max_cols;
		while ( $i >= $min_cols ) {
			if ( 0 === $num_choices % $i ) {
				$cols = $i;
				break;
			}
			$i--;
		}

		if ( ! $cols ) {
			$cols = llms_get_picture_choice_question_cols( $num_choices + 1 );
		}

	}

	return apply_filters( 'llms_get_picture_choice_question_cols', $cols, $num_choices );

}

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

		'choice' => array(
			'choices' => array(
				'max' => -1,
				'min' => 2,
				'multi' => true,
				'type' => 'text',
			),
			'clarifications' => true,
			'description' => true,
			'grading' => 'auto',
			'icon' => 'check',
			'id' => 'choice',
			'image' => true,
			'name' => esc_html__( 'Multiple Choice', 'lifterlms' ),
			'points' => true,
			'video' => true,
		),

		'picture_choice' => array(
			'choices' => array(
				'max' => -1,
				'min' => 2,
				'multi' => true,
				'type' => 'image',
			),
			'clarifications' => true,
			'description' => true,
			'grading' => 'auto',
			'icon' => 'picture-o',
			'id' => 'picture_choice',
			'image' => true,
			'name' => esc_html__( 'Picture Choice', 'lifterlms' ),
			'points' => true,
			'video' => true,
		),

		'true_false' => array(
			'choices' => array(
				'max' => 2,
				'min' => 2,
				'multi' => false,
				'type' => 'text',
			),
			'clarifications' => true,
			'description' => true,
			'grading' => 'auto',
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
			'image' => true,
			'name' => esc_html__( 'True or False', 'lifterlms' ),
			'points' => true,
			'video' => true,
		),

		'content' => array(
			'choices' => false,
			'clarifications' => false,
			'description' => true,
			'icon' => 'window-maximize',
			'id' => 'content',
			'image' => true,
			'grading' => false,
			'name' => esc_html__( 'Content', 'lifterlms' ),
			'points' => false,
			'video' => true,
		),

		'group' => array(
			'choices' => false,
			'clarifications' => false,
			'description' => true,
			'icon' => 'sitemap',
			'id' => 'group',
			'image' => true,
			'grading' => false,
			'name' => esc_html__( 'Question Group', 'lifterlms' ),
			'points' => true,
			'video' => true,
		),

	) );

}
