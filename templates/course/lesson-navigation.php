<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
global $post, $lesson;

if ( ! ($lesson instanceof LLMS_Lesson) ) {
	$lesson = new LLMS_Lesson( $post->ID ); }
?>

<nav class="llms-course-navigation">

<?php
if ( $lesson->get_previous_lesson() ) {
	$previous_lesson_id = $lesson->get_previous_lesson();
	$previous_lesson_link = get_permalink( $previous_lesson_id );
?>

	<div class="llms-lesson-preview prev-lesson previous">
		<a class="llms-lesson-link" href="<?php echo $previous_lesson_link; ?>" alt="<?php echo __( 'Previous Lesson', 'lifterlms' ); ?>">
			<span class="llms-span"><?php echo __( 'Previous Lesson', 'lifterlms' ); ?>:</span>
			<h5 class="llms-h5"><?php echo get_the_title( $previous_lesson_id ) ?></h5>
			<?php if (get_option( 'lifterlms_lesson_nav_display_excerpt', 'no' ) == 'yes') { echo '<p>' . llms_get_excerpt( $previous_lesson_id ) . '</p>'; } ?>
		</a>
	</div>

<?php }

if ( ! $lesson->get_previous_lesson() || ! $lesson->get_next_lesson() ) {
	$parent_style = $lesson->get_next_lesson() ? 'llms-lesson-preview prev-lesson previous' : 'llms-lesson-preview next-lesson next';
	$parent_course_id = $lesson->get_parent_course();
	$parent_course_link = get_permalink( $parent_course_id );

?>
	<div class="llms-lesson-preview <?php echo $parent_style; ?>">
		<a class="llms-lesson-link" href="<?php echo $parent_course_link; ?>" alt="<?php echo __( 'Back to Course', 'lifterlms' ); ?>">
			<span class="llms-span"><?php echo __( 'Back to Course', 'lifterlms' ); ?>:</span>
			<h5 class="llms-h5"><?php echo get_the_title( $parent_course_id ) ?></h5>
		</a>
	</div>
<?php }

if ( $lesson->get_next_lesson() ) {
	$next_lesson_id = $lesson->get_next_lesson();
	$next_lesson_link = get_permalink( $next_lesson_id );
?>

	<div class="llms-lesson-preview next-lesson next">
		<a class="llms-lesson-link" href="<?php echo $next_lesson_link; ?>" alt="<?php echo __( 'Next Lesson', 'lifterlms' ); ?>">
			<span class="llms-span"><?php echo __( 'Next Lesson', 'lifterlms' ); ?>:</span>
			<h5 class="llms-h5"><?php echo get_the_title( $next_lesson_id ) ?></h5>
			<?php if (get_option( 'lifterlms_lesson_nav_display_excerpt', 'no' ) == 'yes') { echo '<p>' . llms_get_excerpt( $next_lesson_id ) . '</p>'; } ?>
		</a>
	</div>

<?php } ?>

</nav>
<div class="clear"></div>
