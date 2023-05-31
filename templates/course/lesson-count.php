<?php
/**
 * Template: Lessons count in a Course.
 *
 * @package LifterLMS/Templates/Course
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

global $post;

$course        = new LLMS_Course( $post );
$lessons_count = $course->get_lessons_count();
?>

<div class="llms-meta llms-lessons-count">
	<p>
		<?php
			// Translators: %1$s = Lessons Count.
			printf( esc_html__( 'Number of Lessons: %1$s', 'lifterlms' ), '<span class="lessons-count">' . $lessons_count . '</span>' );
		?>
	</p>
</div>
