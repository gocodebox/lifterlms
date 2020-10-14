<?php
/**
 * Achievements & Related template functions
 *
 * @package LifterLMS/Functions
 *
 * @since 3.14.0
 * @version 3.14.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get the content of a single achievement
 *
 * @since 3.14.0
 *
 * @param LLMS_User_Achievement $achievement Instance of an LLMS_User_Achievement.
 * @return void
 */
function llms_get_achievement( $achievement ) {

	ob_start();

	llms_get_template(
		'achievements/template.php',
		array(
			'achievement' => $achievement,
		)
	);

	return ob_get_clean();

}

/**
 * Output the content of a single achievement
 *
 * @since 3.14.0
 *
 * @param LLMS_Achievement $achievement Instance of an LLMS_User_Achievement.
 * @return void
 */
function llms_the_achievement( $achievement ) {
	echo llms_get_achievement( $achievement );
}

/**
 * Retrieve the number of columns used in achievement loops
 *
 * @since 3.14.0
 *
 * @return int
 */
function llms_get_achievement_loop_columns() {
	return apply_filters( 'llms_achievement_loop_columns', 4 );
}


/**
 * Get template for achievements loop
 *
 * @since 3.14.0
 * @since 3.14.1 Unknown.
 *
 * @param LLMS_Student $student Optional. LLMS_Student (uses current if none supplied). Default is `null`.
 *                              The current student will be used if none supplied.
 * @param bool|int     $limit   Optional. Number of achievements to show (defaults to all). Default is `false`.
 * @param int          $columns Optional. Number of achievements columns. Default is `null`.
 *                              The default achievement loop columns will be used if none supplied. See `llms_get_achievement_loop_columns()`.
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_achievements_loop' ) ) {
	function lifterlms_template_achievements_loop( $student = null, $limit = false, $columns = null ) {

		// Get the current student if none supplied.
		if ( ! $student ) {
			$student = llms_get_student();
		}

		// Don't proceed without a student.
		if ( ! $student ) {
			return;
		}

		$cols = $columns ? $columns : llms_get_achievement_loop_columns();
		// Get achievements.
		$achievements = $student->get_achievements( 'updated_date', 'DESC', 'achievements' );
		if ( $limit && $achievements ) {
			$achievements = array_slice( $achievements, 0, $limit );
			if ( $limit < $cols && ! $columns ) {
				$cols = $limit;
			}
		}

		llms_get_template(
			'achievements/loop.php',
			array(
				'cols'         => $cols,
				'achievements' => $achievements,
			)
		);

	}
}
