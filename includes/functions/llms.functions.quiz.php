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
 * Retrieve question types
 * see LLMS_Question_Types class for actual loading of core question types
 * @return   array
 * @since    [version]
 * @version  [version]
 */
function llms_get_question_types() {
	return apply_filters( 'llms_get_question_types', array() );
}

/**
 * Retrieve statuses for quiz attempts
 * @return   array
 * @since    [version]
 * @version  [version]
 */
function llms_get_quiz_attempt_statuses() {
	return apply_filters( 'llms_get_quiz_attempt_statuses', array(
		'current' => __( 'Current', 'lifterlms' ),
		'fail' => __( 'Fail', 'lifterlms' ),
		'incomplete' => __( 'Incomplete', 'lifterlms' ),
		'pass' => __( 'Pass', 'lifterlms' ),
		'pending' => __( 'Pending Review', 'lifterlms' ),
	) );
}
