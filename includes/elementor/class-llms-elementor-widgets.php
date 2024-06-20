<?php
/**
 * LifterLMS Elementor Widgets
 *
 * @package LifterLMS/Classes/Shortcodes
 *
 * @since 1.0.0
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcodes
 *
 * @since 1.0.0
 * @since 4.0.0 Remove reliance on deprecated class `LLMS_Quiz_Legacy` & stop registering deprecated shortcode `[courses]` and `[lifterlms_user_statistics]`.
 */
class LLMS_Elementor_Widgets {

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'elementor/widgets/widgets_registered', array( $this, 'init' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'add_widget_categories' ) );
	}

	public function init() {
		require_once LLMS_PLUGIN_DIR . 'includes/elementor/class-llms-elementor-widget-course-meta-info.php';
		\Elementor\Plugin::instance()->widgets_manager->register( new LLMS_Elementor_Widget_Course_Meta_Info() );
	}

	public function add_widget_categories( $elements_manager ) {

		$elements_manager->add_category(
			'lifterlms',
			array(
				'title' => 'LifterLMS',
				'icon'  => 'dashicons-before dashicons-welcome-learn-more',
			)
		);
	}
}

return new LLMS_Elementor_Widgets();
