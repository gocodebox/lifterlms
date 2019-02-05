<?php
/**
 * LifterLMS * LifterLMS bbPress forms list widget
 *
 * @package  LifterLMS/Classes/bbPress
 * @since    3.12.0
 * @version  3.24.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS bbPress forms list widget
 */
class LLMS_BBP_Widget_Course_Forums_List extends WP_Widget {

	/**
	 * Constructor
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function __construct() {

		$options = array(
			'classname' => 'llms-bbp-widget-course-forums',
			'description' => esc_html__( 'Displays a list of bbPress forums associated with the course.', 'lifterlms' ),
		);

		parent::__construct( 'llms_bbp_widget_course_forums_list', esc_html__( 'LifterLMS Course Forums List', 'lifterlms' ), $options );

	}

	/**
	 * Output the wigdet
	 * @param    array     $args      arguments passed to the widget
	 * @param    array     $instance  instance information
	 * @return   void
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function widget( $args, $instance ) {

		$id = get_the_ID();
		if ( 'course' !== get_post_type( $id ) ) {
			$course = llms_get_post_parent_course( $id );
		} else {
			$course = llms_get_post( $id );
		}

		if ( ! $course ) {
			return;
		}

		if ( ! $course->get( 'bbp_forum_ids' ) ) {
			return;
		}

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

			echo do_shortcode( '[lifterlms_bbp_course_forums]' );

		echo $args['after_widget'];

	}

	/**
	 * Output widget options form
	 * @param    array     $instance  instance data
	 * @return   void
	 * @since    3.12.0
	 * @version  3.24.0
	 */
	public function form( $instance ) {

		$title = isset( $instance['title'] ) ? $instance['title'] : __( 'Course Forums', 'lifterlms' );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'lifterlms' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

}
