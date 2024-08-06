<?php
/**
 * LifterLMS Elementor Widgets
 *
 * @package LifterLMS/Classes
 *
 * @since 7.7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Elementor_Widgets
 *
 * @since 7.7.0
 */
class LLMS_Elementor_Widgets {

	/**
	 * Constructor.
	 *
	 * @since 7.7.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'elementor/widgets/widgets_registered', array( $this, 'init' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'add_widget_categories' ) );
	}

	public function init() {
		require_once LLMS_PLUGIN_DIR . 'includes/elementor/class-llms-elementor-widget-base.php';
		require_once LLMS_PLUGIN_DIR . 'includes/elementor/class-llms-elementor-widget-course-meta-info.php';
		require_once LLMS_PLUGIN_DIR . 'includes/elementor/class-llms-elementor-widget-course-instructors.php';
		require_once LLMS_PLUGIN_DIR . 'includes/elementor/class-llms-elementor-widget-pricing-table.php';
		require_once LLMS_PLUGIN_DIR . 'includes/elementor/class-llms-elementor-widget-course-progress.php';
		require_once LLMS_PLUGIN_DIR . 'includes/elementor/class-llms-elementor-widget-course-continue-button.php';
		require_once LLMS_PLUGIN_DIR . 'includes/elementor/class-llms-elementor-widget-course-syllabus.php';

		\Elementor\Plugin::instance()->widgets_manager->register( new LLMS_Elementor_Widget_Course_Meta_Info() );
		\Elementor\Plugin::instance()->widgets_manager->register( new LLMS_Elementor_Widget_Course_Instructors() );
		\Elementor\Plugin::instance()->widgets_manager->register( new LLMS_Elementor_Widget_Pricing_Table() );
		\Elementor\Plugin::instance()->widgets_manager->register( new LLMS_Elementor_Widget_Course_Progress() );
		\Elementor\Plugin::instance()->widgets_manager->register( new LLMS_Elementor_Widget_Course_Continue_Button() );
		\Elementor\Plugin::instance()->widgets_manager->register( new LLMS_Elementor_Widget_Course_Syllabus() );
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
