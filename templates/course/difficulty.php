<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post, $course;
?>
<?php if ( $difficulty = $course->get_difficulty() ) : ?>
<p class="llms-difficulty"><?php printf( __( 'Difficulty: <span class="difficulty">%s</span>', 'lifterlms' ), $difficulty ); ?></p>
<?php endif; ?>
