<?php
/**
* Course syllabus widget
* Displays all lessons in the course
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Widget_Course_Syllabus extends LLMS_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {

		WP_Widget::__construct(
			'course_syllabus',
			__( 'Course Syllabus', 'lifterlms' ),
			array( 'description' => __( 'Displays All Course lessons on Course or Lesson page', 'lifterlms' ) )
		);

	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		// call widget defaults from parent
		parent::form( $instance );

		$collapse = ! empty( $instance['collapse'] ) ? $instance['collapse'] : 0;

		?>
		<p>
			<input <?php checked( 1, $collapse ); ?> class="checkbox" id="<?php echo $this->get_field_id( 'collapse' ); ?>" name="<?php echo $this->get_field_name( 'collapse' ); ?>" type="checkbox" value="1">
			<label for="<?php echo $this->get_field_id( 'collapse' ); ?>">
				<?php _e( 'Make outline collapsible?', 'lifterlms' ); ?><br>
				<em><?php _e( 'Allow students to hide lessons within a section by clicking the section titles.', 'lifterlms' ); ?></em>
			</label>
		</p>
		<?php
	}

	/**
	 * Widget Content
	 * Overrides parent class
	 *
	 * @see  LLMS_Widget()
	 * @return echo
	 */
	public function widget_contents( $args, $instance ) {
		$collapse = ( isset( $instance['collapse'] ) ) ? $instance['collapse'] : 0;
		echo do_shortcode( '[lifterlms_course_outline collapse="' . $collapse . '"]' );
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = parent::update( $new_instance, $old_instance );

		$instance['collapse'] = ( ! empty( $new_instance['collapse'] ) ) ? 1 : 0;

		return $instance;
	}

}
