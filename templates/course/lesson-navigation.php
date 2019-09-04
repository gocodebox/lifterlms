<?php
/**
 * @author      codeBOX
 * @package     lifterLMS/Templates
 */

defined( 'ABSPATH' ) || exit;

global $post;

$lesson = new LLMS_Lesson( $post->ID );

$prev_id = $lesson->get_previous_lesson();
$next_id = $lesson->get_next_lesson();
?>

<nav class="llms-course-navigation">

	<?php if ( $prev_id ) : ?>

		<div class="llms-course-nav llms-prev-lesson">
			<?php
			llms_get_template(
				'course/lesson-preview.php',
				array(
					'lesson'   => new LLMS_Lesson( $prev_id ),
					'pre_text' => __( 'Previous Lesson', 'lifterlms' ),
				)
			);
			?>
		</div>

	<?php endif; ?>

	<?php if ( ! $prev_id || ! $next_id ) : ?>
		<div class="llms-course-nav llms-back-to-course">
			<div class="llms-lesson-preview">
				<a class="llms-lesson-link" href="<?php echo get_permalink( $lesson->get_parent_course() ); ?>">
					<section class="llms-main">
						<h6 class="llms-pre-text"><?php echo __( 'Back to Course', 'lifterlms' ); ?></h6>
						<h5 class="llms-h5 llms-lesson-title"><?php echo get_the_title( $lesson->get_parent_course() ); ?></h5>
					</section>
				</a>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( $next_id ) : ?>

		<div class="llms-course-nav llms-next-lesson">
			<?php
			llms_get_template(
				'course/lesson-preview.php',
				array(
					'lesson'   => new LLMS_Lesson( $next_id ),
					'pre_text' => __( 'Next Lesson', 'lifterlms' ),
				)
			);
			?>
		</div>

	<?php endif; ?>



</nav>
<div class="clear"></div>
