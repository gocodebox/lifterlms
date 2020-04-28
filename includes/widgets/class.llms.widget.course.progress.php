<?php
/**
 * Course progress widget
 *
 * Displays course progress
 *
 * @package LifterLMS/Widgets/Classes
 *
 * @since 1.0.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Widget_Course_Progress
 *
 * @since 1.0.0
 * @since [version] Introduced a new option to display/hide the course progress widget to enrolled students only.
 *                Hidden to not enrolled students by default.
 */
class LLMS_Widget_Course_Progress extends LLMS_Widget {

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		WP_Widget::__construct(
			'course_progress',
			__( 'Course Progress', 'lifterlms' ),
			array(
				'description' => __( 'Displays Course Progress on Course or Lesson', 'lifterlms' ),
			)
		);

	}

	/**
	 * Back-end widget form.
	 *
	 * @since [version]
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		// Call widget defaults from parent.
		parent::form( $instance );
		$check_enrollment = ( isset( $instance['check_enrollment'] ) ) ? $instance['check_enrollment'] : 1;
		?>
		<p>
			<input <?php checked( 1, $check_enrollment ); ?> class="checkbox llms-course-progress-check-enrollment" id="<?php echo $this->get_field_id( 'check_enrollment' ); ?>" name="<?php echo $this->get_field_name( 'check_enrollment' ); ?>" type="checkbox" value="1">
			<label for="<?php echo $this->get_field_id( 'check_enrollment' ); ?>">
				<?php _e( 'Display to enrolled students only?', 'lifterlms' ); ?><br>
				<em><?php _e( 'When checked the course progress bar will be shown only to those students enrolled in the course.', 'lifterlms' ); ?></em>
			</label>
		</p>
		<?php
	}


	/**
	 * Widget Content
	 *
	 * Overrides parent class
	 *
	 * @since 1.0.0
	 * @since [version] Added the logic to display/hide the course progress widget to enrolled students only, according to the new option.
	 *                Hidden to not enrolled students by default.
	 *
	 * @see LLMS_Widget::widget_contents()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 * @return void
	 */
	public function widget_contents( $args, $instance ) {
		$check_enrollment = ( ! isset( $instance['check_enrollment'] ) ) ? 1 : $instance['check_enrollment'];
		echo do_shortcode( '[lifterlms_course_progress check_enrollment=' . $check_enrollment . ']' );
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @since [version]
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = parent::update( $new_instance, $old_instance );

		$instance['check_enrollment'] = ( ! empty( $new_instance['check_enrollment'] ) ) ? 1 : 0;

		return $instance;
	}

}
