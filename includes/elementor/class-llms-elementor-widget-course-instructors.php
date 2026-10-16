<?php
/**
 * Course Instructors Elementor widget.
 *
 * @package LifterLMS/Classes/Elementor
 *
 * @since 7.7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Elementor_Widget_Course_Instructors class.
 *
 * @since 7.7.0
 * @since [version] Added enrollment visibility controls.
 */
class LLMS_Elementor_Widget_Course_Instructors extends LLMS_Elementor_Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @since 7.7.0
	 *
	 * @return string
	 */
	public function get_name() {
		return 'llms_course_instructors_widget';
	}

	/**
	 * Get widget title.
	 *
	 * @since 7.7.0
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Course Instructors', 'lifterlms' );
	}

	/**
	 * Register widget controls.
	 *
	 * @since 7.7.0
	 * @since [version] Added enrollment visibility section.
	 *
	 * @return void
	 */
	protected function _register_controls() {

		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Course Instructors', 'lifterlms' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'description',
			array(
				'label'     => esc_html__( 'Show current course instructors.', 'lifterlms' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_footer_promo_control();

		$this->end_controls_section();

		$this->add_visibility_controls();
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * @since 7.7.0
	 *
	 * @return void
	 */
	protected function render_widget() {
		echo do_shortcode( '[lifterlms_course_instructors]' );
	}
}
