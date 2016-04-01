<?php
/**
* Register WordPress AJAX methods for Analytics Widgets
*
* @author codeBOX
* @project LifterLMS
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Analytics_Widget_Ajax {

	public function __construct() {

		// only proceed if we're doing ajax
		if( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {

			return;

		}

		$methods = array(

			'enrollments'

		);

		include LLMS_PLUGIN_DIR . 'includes/admin/analytics/widgets/abstract.llms.analytics.widget.php';

		foreach( $methods as $method ) {

			$file = LLMS_PLUGIN_DIR . 'includes/admin/analytics/widgets/class.llms.analytics.widget.' . $method . '.php';

			if( file_exists( $file ) ) {

				include $file;
				$class = 'LLMS_Analytics_' . ucwords( $method ) . '_Widget';
				add_action( 'wp_ajax_llms_widget_' . $method, array( new $class, 'output' ) );

			}



		}

	}

}

return new LLMS_Analytics_Widget_Ajax();
