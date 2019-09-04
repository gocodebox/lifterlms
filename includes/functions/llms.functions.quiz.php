<?php
/**
 * LifterLMS Quiz Functions
 *
 * @author    LifterLMS
 * @category  Core
 * @package   LifterLMS/Functions
 * @since     3.16.0
 * @version   3.16.12
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Retrieve the number of columns needed for a picture choice question
 *
 * @param    int $num_choices  number of choices
 * @return   int
 * @since    3.16.0
 * @version  3.16.0
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
 *
 * @param    string $type  id of the question type
 * @return   array|false
 * @since    3.16.0
 * @version  3.16.0
 */
function llms_get_question_type( $type ) {

	$types = llms_get_question_types();
	$ret   = isset( $types[ $type ] ) ? $types[ $type ] : false;
	return apply_filters( 'llms_get_question_type', $ret, $type );

}

/**
 * Retrieve question types
 * see LLMS_Question_Types class for actual loading of core question types
 *
 * @return   array
 * @since    3.16.0
 * @version  3.16.0
 */
function llms_get_question_types() {
	return apply_filters( 'llms_get_question_types', array() );
}

/**
 * Retrieve statuses for quiz attempts
 *
 * @return   array
 * @since    3.16.0
 * @version  3.16.0
 */
function llms_get_quiz_attempt_statuses() {
	return apply_filters(
		'llms_get_quiz_attempt_statuses',
		array(
			'incomplete' => __( 'Incomplete', 'lifterlms' ),
			'pending'    => __( 'Pending Review', 'lifterlms' ),
			'fail'       => __( 'Fail', 'lifterlms' ),
			'pass'       => __( 'Pass', 'lifterlms' ),
		)
	);
}

/**
 * Get quiz settings defined by supporting themes
 *
 * @param    string $setting  name of setting, if omitted returns all settings
 * @param    string $default  default fallback if setting not set
 * @return   array
 * @since    3.16.8
 * @version  3.16.8
 */
function llms_get_quiz_theme_setting( $setting = '', $default = '' ) {

	$settings = apply_filters(
		'llms_get_quiz_theme_settings',
		array(
			'layout' => array(
				'id'      => '',
				'name'    => __( 'Layout', 'lifterlms' ),
				'options' => array(),
				'type'    => 'select', // select, image_select
			),
		)
	);

	if ( $setting ) {
		return isset( $settings[ $setting ] ) ? $settings[ $setting ] : $default;
	}

	return $settings;

}

/**
 * Shuffles choices until the choice order has changed from the original
 * The smaller the list of choices the greater the chance of shuffling not changing the array
 *
 * @param    array $choices  choices from an LLMS_Question
 * @return   array
 * @since    3.16.12
 * @version  3.16.12
 */
function llms_shuffle_choices( $choices ) {

	$count = count( $choices );

	// if we only have one choice there's not much to shuffle with
	if ( $count <= 1 ) {
		return $choices;

		// reverse the array when we only have two
	} elseif ( 2 === $count ) {
		$shuffled = array_reverse( $choices );

		// shuffle until the order has changed
	} else {

		$shuffled = $choices;

		while ( $shuffled === $choices ) {
			shuffle( $shuffled );
		}
	}

	return $shuffled;

}
