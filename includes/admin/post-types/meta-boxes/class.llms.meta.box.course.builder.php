<?php
/**
 * Course Builder Metabox
 * @since    3.13.0
 * @version  3.27.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Metabox_Course_Builder class.
 */
class LLMS_Metabox_Course_Builder extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 * @return   void
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function configure() {

		$this->id = 'course_builder';
		$this->title = __( 'Course Builder', 'lifterlms' );
		$this->screens = array(
			'course',
			'lesson',
		);
		$this->context = 'side';
		$this->capability = 'edit_course';

	}

	/**
	 * Get a URL to the course builder with an optional hash to a lesson/quiz/assignment
	 * @param   int    $course_id WP Post ID of a course.
	 * @param   string $hash      Hash of the lesson & tab info (lesson:{$lesson_id}:tab).
	 * @return  string
	 * @since   3.27.0
	 * @version 3.27.0
	 */
	public function get_builder_url( $course_id, $hash = null ) {

		$url = add_query_arg( array(
			'page' => 'llms-course-builder',
			'course_id' => $course_id,
		), admin_url( 'admin.php' ) );

		if ( $hash ) {
			$url = $url . '#' . $hash;
		}

		return $url;

	}

	/**
	 * This metabox has no options
	 * @return   array
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function get_fields() {
		return array();
	}

	/**
	 * Get the HTML for a title, optionally as an anchor
	 * @param    string     $title  title to display
	 * @param    boolean    $url    url to link to
	 * @return   string
	 * @since    3.13.0
	 * @version  3.27.0
	 */
	public function get_title_html( $title, $url = false ) {

		if ( $url ) {
			$title = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $url ), $title );
		}

		return $title;

	}

	/**
	 * Override the output method to output a button
	 * @return   void
	 * @since    3.13.0
	 * @version  3.27.0
	 */
	public function output() {

		$post_id = $this->post->ID;

		$lesson = false;
		$section = false;
		if ( 'lesson' === $this->post->post_type ) {
			$course = llms_get_post_parent_course( $post_id );
			if ( ! $course ) {
				_e( 'This lesson is not attached to a course.', 'lifterlms' );
				return;
			}
			$course_id = $course->get( 'id' );
			$lesson = llms_get_post( $this->post );
			$section = $lesson->get_parent_section() ? llms_get_post( $lesson->get_parent_section() ) : false;
		} else {
			$course = llms_get_post( $post_id );
		}
		?>
		<div class="llms-builder-launcher">

			<?php if ( $lesson && $section ) : ?>

				<h4><?php printf( __( 'Course: %s', 'lifterlms' ), $this->get_title_html( $course->get( 'title' ), get_edit_post_link( $course->get( 'id' ) ) ) ); ?></h4>

				<?php $this->output_section( $section, 'previous' ); ?>

				<?php $this->output_section( $section, 'current' ); ?>

				<?php $this->output_section( $section, 'next' ); ?>

			<?php endif; ?>

			<a class="llms-button-primary full" href="<?php echo esc_url( $this->get_builder_url( $course->get( 'id' ) ) ); ?>"><?php _e( 'Launch Course Builder', 'lifterlms' ); ?></a>

		</div>
		<?php

	}

	/**
	 * HTML helper to output info for a section
	 * @param    obj        $section  LLMS_Section object
	 * @param    string     $which    positioning [current|previous|next]
	 * @return   void
	 * @since    3.13.0
	 * @version  3.27.0
	 */
	private function output_section( $section, $which ) {

		$url = false;

		if ( 'previous' == $which ) {
			$section = $section->get_previous();
		} elseif ( 'next' === $which ) {
			$section = $section->get_next();
		}

		if ( ! $section ) {
			return;
		}

		if ( 'previous' == $which || 'next' === $which ) {
			$lessons = $section->get_lessons( 'ids' );
			if ( $lessons ) {
				$url = get_edit_post_link( $lessons[0] );
			}
		}
		?>

		<h5><?php printf( __( 'Section %1$d: %2$s', 'lifterlms' ), $section->get( 'order' ), $this->get_title_html( $section->get( 'title' ), $url ) ); ?></h5>

		<?php if ( 'current' === $which ) : ?>
			<ol>
			<?php foreach ( $section->get_lessons() as $lesson ) :
				$hash = 'lesson:' . $lesson->get( 'id' ); ?>
				<li>
					<?php if ( $this->post->ID != $lesson->get( 'id' ) ) : ?>
						<?php echo $this->get_title_html( $lesson->get( 'title' ), get_edit_post_link( $lesson->get( 'id' ) ) ); ?>
					<?php else : ?>
						<?php echo $lesson->get( 'title' ); ?>
					<?php endif; ?>
					<a class="tip--top-left" href="<?php echo esc_url( $this->get_builder_url( $lesson->get( 'parent_course' ), $hash ) ); ?>" data-tip="<?php esc_attr_e( 'Edit lesson in builder', 'lifterlms' ); ?>"><i class="fa fa-cog"></i></a>
					<?php if ( $lesson->has_quiz() ) :
						$quiz = $lesson->get_quiz(); ?>
						<br>
						<?php printf( __( 'Quiz: %s', 'lifterlms' ), $this->get_title_html( $quiz->get( 'title' ), $this->get_builder_url( $lesson->get( 'parent_course' ), $hash . ':quiz' ) ) ); ?>
					<?php endif; ?>
					<?php do_action( 'llms_builder_mb_after_lesson', $lesson, $this ); ?>
				</li>
			<?php endforeach; ?>
			</ol>
		<?php endif;

	}

}

return new LLMS_Metabox_Course_Builder();
