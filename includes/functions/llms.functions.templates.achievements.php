<?php
/**
 * Achievements & Related template functions
 * @since    3.14.0
 * @version  3.14.1
 */

// Restrict direct access
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Get the content of a single achievement
 * @param    obj     $achievement  instance of an LLMS_User_Achievement
 * @return   void
 * @since    3.14.0
 * @version  3.14.0
 */
function llms_get_achievement( $achievement ) {

	ob_start();

	llms_get_template( 'achievements/template.php', array(
		'achievement' => $achievement,
	) );

	return ob_get_clean();

}
	/**
	 * Output the content of a single achievement
	 * @param    obj     $achievement  instance of an LLMS_User_Achievement
	 * @return   void
	 * @since    3.14.0
	 * @version  3.14.0
	 */
function llms_the_achievement( $achievement ) {
	echo llms_get_achievement( $achievement );
}

/**
 * Retrieve the number of columns used in achievement loops
 * @return   int
 * @since    3.14.0
 * @version  3.14.0
 */
function llms_get_achievement_loop_columns() {
	return apply_filters( 'llms_achievement_loop_columns', 4 );
}


/**
 * Get template for achievements loop
 * @param    obj       $student  LLMS_Student (uses current if none supplied)
 * @param    bool|int  $limit    number of achievements to show (defaults to all)
 * @param    int       $columns  number of achievements columns
 * @return   void
 * @since    3.14.0
 * @version  3.14.1
 */
if ( ! function_exists( 'lifterlms_template_achievements_loop' ) ) {
	function lifterlms_template_achievements_loop( $student = null, $limit = false, $columns = null ) {

		// get the current student if none supplied
		if ( ! $student ) {
			$student = llms_get_student();
		}

		// don't proceed without a student
		if ( ! $student ) {
			return;
		}

		$cols = $columns ? $columns : llms_get_achievement_loop_columns();
		// get achievements
		$achievements = $student->get_achievements( 'updated_date', 'DESC', 'achievements' );
		if ( $limit && $achievements ) {
			$achievements = array_slice( $achievements, 0, $limit );
			if ( $limit < $cols && ! $columns ) {
				$cols = $limit;
			}
		}

		llms_get_template( 'achievements/loop.php', array(
			'cols' => $cols,
			'achievements' => $achievements,
		) );

	}
}
