<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post, $course;

?>

<p class="llms-difficulty"><?php printf( __( 'Difficulty: <span class="difficulty">%s</span>', 'lifterlms' ), $course->get_difficulty() ); ?></p>

