<?php
/**
* Base Widgets Class
* Calls WP register widgets for each widget in filter lifterlms_widgets
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Widgets {

	/**
	 * Constructor
	 */
	public function __construct() {

		add_action( 'widgets_init', array( $this, 'register_widgets' ) );

	}

	/**
	 * Registers all lifterlms_widgets
	 * @return void
	 */
	public function register_widgets() {

		apply_filters( 'lifterlms_widgets',
			$widgets = array(
				'LLMS_Widget_Course_Progress',
				'LLMS_Widget_Course_Syllabus',
			)
		);

		foreach ( $widgets as $widget ) {

			register_widget( $widget );
		}

	}

}

return new LLMS_Widgets;
