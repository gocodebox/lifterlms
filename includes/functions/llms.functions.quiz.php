<?php
/**
 * LifterLMS Quiz Functions.
 *
 * @package LifterLMS/Functions
 *
 * @since 3.16.0
 * @version 5.3.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retrieve the number of columns needed for a picture choice question.
 *
 * @since 3.16.0
 *
 * @param int $num_choices Number of choices.
 * @return int
 */
function llms_get_picture_choice_question_cols( $num_choices ) {

	/**
	 * Allow 3rd parties to override this function with a custom number of columns.
	 *
	 * If this responds with a non null will bypass column counter function return it immediately
	 *
	 * @since 3.16.0
	 *
	 * @param null|int $cols        Number of columns needed for a picture choice question.
	 * @param int      $num_choices Number of choices.
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

	/** This filter is documented above */
	return apply_filters( 'llms_get_picture_choice_question_cols', $cols, $num_choices );

}

/**
 * Retrieve data for a single question type.
 *
 * @since 3.16.0
 *
 * @param string $type Id of the question type.
 * @return array|false
 */
function llms_get_question_type( $type ) {

	$types = llms_get_question_types();
	$ret   = isset( $types[ $type ] ) ? $types[ $type ] : false;

	/**
	 * Filters the data for a single question type.
	 *
	 * @since 3.16.0
	 *
	 * @param array|false $data Data for a single question type. False it there's no data for a given quesiton type.
	 * @param string      $type Id of the question type.
	 */
	return apply_filters( 'llms_get_question_type', $ret, $type );

}

/**
 * Retrieve question types.
 *
 * See `LLMS_Question_Types` class for actual loading of core question types.
 *
 * @since 3.16.0
 * @return array
 */
function llms_get_question_types() {
	/**
	 * Filters the question types.
	 *
	 * @since 3.16.0
	 *
	 * @param array $question_types Question types.
	 */
	return apply_filters( 'llms_get_question_types', array() );
}

/**
 * Retrieve statuses for quiz attempts.
 *
 * @since 3.16.0
 *
 * @return array
 */
function llms_get_quiz_attempt_statuses() {
	/**
	 * Filters the quiz attempt statuses
	 *
	 * @since 3.16.0
	 *
	 * @param array $quiz_attempt_statuses Statuses for quiz attempts.
	 */
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
 * @since 3.16.8
 * @since 3.38.0 Moved deprecation notice from `LLMS_Admin_Builder::get_custom_schemas()`.
 * @since 4.6.0 Removed logging and use `apply_filters_deprecated()` in favor of `apply_filters()`.
 * @since 5.3.3 Correctly pass an array of settings as parameter for `apply_filters_deprecated()`.
 * @deprecated 3.38.0 See https://lifterlms.com/docs/course-builder-custom-fields-for-developers for more information.
 *
 * @param string $setting Name of setting, if omitted returns all settings.
 * @param string $default Default fallback if setting not set.
 * @return array
 */
function llms_get_quiz_theme_setting( $setting = '', $default = '' ) {

	/**
	 * Deprecated.
	 *
	 * @since 3.17.0
	 * @deprecated 3.17.6 Deprecated. See https://lifterlms.com/docs/course-builder-custom-fields-for-developers for more information.
	 *
	 * @param array[] $settings Array of quiz theme settings.
	 */
	$settings = apply_filters_deprecated(
		'llms_get_quiz_theme_settings',
		array(
			array(
				'layout' => array(
					'id'      => '',
					'name'    => __( 'Layout', 'lifterlms' ),
					'options' => array(),
					'type'    => 'select', // Either: select or image_select.
				),
			),
		),
		'3.17.6'
	);

	if ( $setting ) {
		return isset( $settings[ $setting ] ) ? $settings[ $setting ] : $default;
	}

	return $settings;

}

/**
 * Shuffles choices until the choice order has changed from the original.
 *
 * The smaller the list of choices the greater the chance of shuffling not changing the array.
 *
 * @since 3.16.12
 *
 * @param array $choices Choices from an LLMS_Question
 * @return array
 */
function llms_shuffle_choices( $choices ) {

	$count = count( $choices );

	// If we only have one choice there's not much to shuffle with.
	if ( $count <= 1 ) {
		return $choices;

		// Reverse the array when we only have two.
	} elseif ( 2 === $count ) {
		$shuffled = array_reverse( $choices );

		// Shuffle until the order has changed.
	} else {

		$shuffled = $choices;

		while ( $shuffled === $choices ) {
			shuffle( $shuffled );
		}
	}

	return $shuffled;

}
