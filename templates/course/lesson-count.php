<?php
/**
 * Template: Lessons count in a Course.
 *
 * @package LifterLMS/Templates/Course
 *
 * @since 7.5.0
 * @version 7.5.0
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
			echo wp_kses_post( sprintf( esc_html__( 'Number of lessons: %1$s', 'lifterlms' ), '<span class="lessons-count">' . $lessons_count . '</span>' ) );
		?>
	</p>
</div>
