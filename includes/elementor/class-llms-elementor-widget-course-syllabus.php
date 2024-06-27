<?php

class LLMS_Elementor_Widget_Course_Syllabus extends LLMS_Elementor_Widget_Base {

	public function get_name() {
		return 'llms_course_syllabus_widget';
	}

	public function get_title() {
		return __( 'Course Syllabus', 'lifterlms' );
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Course Syllabus', 'lifterlms' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'description',
			array(
				'label'     => esc_html__( 'Show course syllabus for the current course.', 'lifterlms' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_footer_promo_control();

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		echo do_shortcode( '[lifterlms_course_syllabus]' );
	}
}
