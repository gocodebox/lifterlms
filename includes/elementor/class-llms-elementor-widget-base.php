<?php

abstract class LLMS_Elementor_Widget_Base extends \Elementor\Widget_Base {

	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );
	}

	public function get_icon() {
		return 'dashicons-before dashicons-welcome-learn-more';
	}

	public function get_categories() {
		return array( 'lifterlms' );
	}

	protected function add_footer_promo_control() {

		$this->add_control(
			'llms_footer_promo',
			array(
				'label' => '<hr><div style="margin-top: 20px;">' . esc_html__( 'Using LifterLMS with Elementor', 'lifterlms' ) . '</div>',
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => '<p style="margin-top: 10px;">' . sprintf( esc_html__( '%sRead more on creating and editing courses with Elementor%s', 'lifterlms' ), '<a target="_blank" href="https://lifterlms.com/docs/how-to-edit-courses-with-elementor/">', '</a>' ) . '</p>',
				'content_classes' => 'lifterlms-notice',
			)
		);

	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		echo do_shortcode( '[lifterlms_course_continue_button]' );
	}

	protected function _content_template() {
		// Define your template variables here
	}
}
