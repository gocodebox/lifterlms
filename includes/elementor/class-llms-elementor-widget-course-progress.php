<?php

class LLMS_Elementor_Widget_Course_Progress extends \Elementor\Widget_Base {

	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );
	}

	public function get_name() {
		return 'llms_course_progress_widget';
	}

	public function get_title() {
		return __( 'Course Progress', 'lifterlms' );
	}

	public function get_icon() {
		return 'dashicons-before dashicons-welcome-learn-more';
	}

	public function get_categories() {
		return array( 'lifterlms' );
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Course Progress', 'lifterlms' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'description',
			array(
				'label'     => esc_html__( 'Show course progress to students for the current course.', 'lifterlms' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		// We could add an option to pick course ID

		echo do_shortcode( '[lifterlms_course_progress]' );
	}

	protected function _content_template() {
		// Define your template variables here
	}
}
