<?php
/**
 * Course Builder Metabox
 * @since    3.13.0
 * @version  3.13.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

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
	 * @version  3.13.0
	 */
	private function get_title_html( $title, $url = false ) {

		if ( $url ) {
			$title = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $url ), $title );
		}

		return $title;

	}

	/**
	 * Override the output method to output a button
	 * @return   void
	 * @since    3.13.0
	 * @version  3.13.0
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

		$url = add_query_arg( array(
			'page' => 'llms-course-builder',
			'course_id' => $course->get( 'id' ),
		), admin_url( 'admin.php' ) );
		?>
		<div class="llms-builder-launcher">

			<?php if ( $lesson && $section ) : ?>

				<h4><?php printf( __( 'Course: %s', 'lifterlms' ), $this->get_title_html( $course->get( 'title' ), get_edit_post_link( $course->get( 'id' ) ) ) ); ?></h4>

				<?php $this->output_section( $section, 'previous' ); ?>

				<?php $this->output_section( $section, 'current' ); ?>

				<?php $this->output_section( $section, 'next' ); ?>

			<?php endif; ?>

			<a class="llms-button-primary full" href="<?php echo esc_url( $url ); ?>"><?php _e( 'Launch Course Builder', 'lifterlms' ); ?></a>

		</div>
		<?php

	}

	/**
	 * HTML helper to output info for a section
	 * @param    obj        $section  LLMS_Section object
	 * @param    string     $which    positioning [current|previous|next]
	 * @return   void
	 * @since    3.13.0
	 * @version  3.13.0
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
			<?php foreach ( $section->get_lessons() as $lesson ) : ?>
				<li>
					<?php if ( $this->post->ID != $lesson->get( 'id' ) ) : ?>
						<?php echo $this->get_title_html( $lesson->get( 'title' ), get_edit_post_link( $lesson->get( 'id' ) ) ); ?>
					<?php else : ?>
						<?php echo $lesson->get( 'title' ); ?>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
			</ol>
		<?php endif;

	}

}

return new LLMS_Metabox_Course_Builder();
