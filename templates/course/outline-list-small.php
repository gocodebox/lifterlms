<?php
/**
 * Course Outline Small List
 * Used for lifterlms_course_outline Shortcode & Course Syllabus Widget
 *
 * @property  $collapse         bool   whether or not sections are collapsible via user interaction
 * @property  $course           obj    instance of the LLMS_Course for the current course
 * @property  $current_section  int    WP Post ID of the current section, this determines which section is open when the outline is collapsible
 * @property  $current_lesson   int    WP Post ID of the lesson being currently viewed, will be null if used outside of a lesson
 * @property  $sections         array  array of LLMS_Sections
 * @property  $student          obj    Instance of the LLMS_Student for the current user
 * @property  $toggles          bool   whether or not open/close all toggles should display in the outline footer. Only works when $collapse is also true
 * @since     1.0.0
 * @version   3.19.2
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="llms-widget-syllabus<?php echo ( $collapse ) ? ' llms-widget-syllabus--collapsible' : ''; ?>">

	<?php do_action( 'lifterlms_outline_before' ); ?>

	<ul class="llms-course-outline">

		<?php foreach ( $sections as $section ) : ?>

			<li class="llms-section<?php echo ( $collapse ) ? ( $section->get( 'id' ) == $current_section ) ? ' llms-section--opened' : ' llms-section--closed' : ''; ?>">

				<div class="section-header">

					<?php do_action( 'lifterlms_outline_before_header' ); ?>

					<?php if ( $collapse ) : ?>

						<span class="llms-collapse-caret">
							<i class="fa fa-caret-down"></i>
							<i class="fa fa-caret-right"></i>
						</span>

					<?php endif; ?>

					<span class="section-title"><?php echo apply_filters( 'llms_widget_syllabus_section_title', $section->get( 'title' ), $section ); ?></span>

					<?php do_action( 'lifterlms_outline_after_header' ); ?>

				</div>

				<?php
				foreach ( $section->get_lessons() as $lesson ) :
					$current     = ( $current_lesson == $lesson->get( 'id' ) );
					$is_complete = $student ? $student->is_complete( $lesson->get( 'id' ), 'lesson' ) : false;
					$restricted  = llms_page_restricted( $lesson->get( 'id' ) );
					?>

					<ul class="llms-lesson<?php echo $current ? ' current-lesson' : ''; ?>">

						<li>

							<span class="llms-lesson-complete <?php echo ( $is_complete ? 'done' : '' ); ?>">
								<i class="fa fa-check-circle"></i>
							</span>

							<?php do_action( 'lifterlms_outline_before_lesson_title', $lesson ); ?>

							<span class="lesson-title <?php echo ( $is_complete ? 'done' : '' ); ?>">

								<?php if ( $lesson->is_free() || ( $student && ! $restricted['is_restricted'] ) ) : ?>

									<a href="<?php echo get_permalink( $lesson->get( 'id' ) ); ?>">
										<?php echo apply_filters( 'llms_widget_syllabus_section_title', $lesson->get( 'title' ) ); ?>
									</a>

								<?php else : ?>

									<?php echo apply_filters( 'llms_widget_syllabus_section_title', $lesson->get( 'title' ) ); ?>

								<?php endif; ?>

							</span>

							<?php do_action( 'lifterlms_outline_after_lesson_title', $lesson ); ?>

						</li>

					</ul>

				<?php endforeach; ?>

			</li>

		<?php endforeach; ?>

		<?php if ( $collapse && $toggles ) : ?>

			<li class="llms-section llms-syllabus-footer">

				<?php do_action( 'lifterlms_outline_before_footer' ); ?>

				<a class="llms-button-text llms-collapse-toggle" data-action="open" href="#"><?php _e( 'Open All', 'lifterlms' ); ?></a>
				<span>&middot;</span>
				<a class="llms-button-text llms-collapse-toggle" data-action="close" href="#"><?php _e( 'Close All', 'lifterlms' ); ?></a>

				<?php do_action( 'lifterlms_outline_after_footer' ); ?>

			</li>

		<?php endif; ?>

	</ul>

	<?php do_action( 'lifterlms_outline_after' ); ?>

</div>
