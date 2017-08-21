<?php
/**
* Base Widgets Class
* Calls WP register widgets for each widget in filter lifterlms_widgets
* @since    1.0.0
* @version  3.12.0
*/
class LLMS_Widgets {

	/**
	 * Constructor
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function __construct() {

		add_action( 'widgets_init', array( $this, 'register_widgets' ) );

	}

	/**
	 * Registers all lifterlms_widgets
	 * @return   void
	 * @since    1.0.0
	 * @version  3.12.0
	 */
	public function register_widgets() {

		$widgets = apply_filters( 'lifterlms_widgets', array(
			'LLMS_Widget_Course_Progress',
			'LLMS_Widget_Course_Syllabus',
		) );

		if ( class_exists( 'bbPress' ) && 'yes' === get_option( 'llms_integration_bbpress_enabled', 'no' ) ) {
			require_once LLMS_PLUGIN_DIR . 'includes/widgets/class.llms.bbp.widget.course.forums.list.php';
			$widgets[] = 'LLMS_BBP_Widget_Course_Forums_List';
		}

		foreach ( $widgets as $widget ) {

			register_widget( $widget );

		}

	}

}

return new LLMS_Widgets;
