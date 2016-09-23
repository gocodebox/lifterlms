<?php
/**
 * Course difficulty template
 * @author 		LifterLMS
 * @package 	LifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post, $course;

if ( ! $course->get_difficulty() ) {
	return;
}
?>

<div class="llms-meta llms-difficulty">
	<p><?php printf( __( 'Difficulty: <span class="difficulty">%s</span>', 'lifterlms' ), $course->get_difficulty() ); ?></p>
</div>
