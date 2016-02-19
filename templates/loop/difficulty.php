<?php
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
global $course;
?>

<?php if ( $difficulty = $course->get_difficulty() ) : ?>
	<span class="llms-difficulty"><?php printf( __( 'Difficulty: %s', 'lifterlms' ), $difficulty ); ?></span>
<?php endif; ?>
