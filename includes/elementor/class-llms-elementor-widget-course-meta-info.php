<?php

class LLMS_Elementor_Widget_Course_Meta_Info extends \Elementor\Widget_Base {

	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );
	}

	public function get_name() {
		return 'llms_course_information_widget';
	}

	public function get_title() {
		return __( 'Course Meta Information', 'lifterlms' );
	}

	public function get_icon() {
		return 'dashicons-before dashicons-welcome-learn-more';
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Course Meta Information', 'lifterlms' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'description',
			array(
				'label'     => esc_html__( 'Show current course meta information.', 'lifterlms' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		// We could add an option to pick course ID

		echo do_shortcode( '[lifterlms_course_meta_info]' );
	}

	protected function _content_template() {
		// Define your template variables here
	}
}
