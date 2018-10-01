<?php
/**
 * My Grades Template
 * @since    [version]
 * @version  [version]
 */
defined( 'ABSPATH' ) || exit;
llms_print_notices();
?>

<?php if ( $course ) : ?>

	<section class="llms-sd-widgets">

		<div class="llms-sd-widget">
			<h4 class="llms-sd-widget-title"><?php _e( 'Progress', 'lifterlms' ); ?></h4>
			<?php echo llms_get_donut( $student->get_progress( $course->get( 'id' ) ), __( 'Complete', 'lifterlms' ), 'medium' ); ?>
		</div>

		<div class="llms-sd-widget">
			<h4 class="llms-sd-widget-title"><?php _e( 'Grade', 'lifterlms' ); ?></h4>
			<?php echo llms_get_donut( $student->get_grade( $course->get( 'id' ) ), __( 'Overall Grade', 'lifterlms' ), 'medium' ); ?>
		</div>

		<div class="llms-sd-widget">
			<h4 class="llms-sd-widget-title"><?php _e( 'Latest Achievement', 'lifterlms' ); ?></h4>

		</div>

		<div class="llms-sd-widget">
			<h4 class="llms-sd-widget-title"><?php _e( 'Next Achievement', 'lifterlms' ); ?></h4>

		</div>

	</section>

	<table class="llms-table">
	<?php foreach ( $course->get_sections() as $section ) : ?>

		<tr class="llms-section">
			<th colspan="2">
				<?php printf( __( 'Section %1$d: %2$s', 'lifterlms' ), $section->get( 'order' ), $section->get( 'title' ) ); ?>
			</th>
			<th><?php _e( 'Completion Date', 'lifterlms' ); ?></th>
			<th><?php _e( 'Quiz', 'lifterlms' ); ?></th>
			<th><?php _e( 'Grade', 'lifterlms' ); ?></th>
		</tr>

		<?php foreach ( $section->get_lessons() as $lesson ) : ?>
			<tr>
				<td class="llms-spacer"></td>
				<td>
					<?php printf( __( 'Lesson %1$d: %2$s', 'lifterlms' ), $lesson->get( 'order' ), $lesson->get( 'title' ) ); ?>
				</td>
				<td>
					<?php echo $student->get_completion_date( $lesson->get( 'id' ), get_option( 'date_format' ) ); ?>
				</td>
				<td>
					<?php if ( $lesson->has_quiz() ) :
						$attempt = $student->quizzes()->get_best_attempt( $lesson->get( 'quiz' ) );
						$url = $attempt ? $attempt->get_permalink() : get_permalink( $lesson->get( 'quiz' ) );
						?>
						<a href="<?php echo $url; ?>"><?php _e( 'View', 'lifterlms' ); ?></a>
					<?php else : ?>
						&ndash;
					<?php endif; ?>
				</td>
				<td>
					<?php
						$grade = $student->get_grade( $lesson->get( 'id' ) );
						echo is_numeric( $grade ) ? llms_get_donut( $grade, '', 'mini' ) : '&ndash;';
					?>
				</td>
			</tr>
		<?php endforeach; ?>

	<?php endforeach; ?>
	</table>

<?php else : ?>

	<p><?php _e( 'Invalid course.', 'lifterlms' ); ?>

<?php endif; ?>
