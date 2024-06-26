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
				'label'           => '',
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => '<hr><p style="margin-top: 20px;">' . sprintf( esc_html__( 'Learn more about %1$sediting LifterLMS courses with Elementor%2$s', 'lifterlms' ), '<a target="_blank" href="https://lifterlms.com/docs/how-to-edit-courses-with-elementor/?utm_source=LifterLMS%20Plugin&utm_medium=Elementor%20Edit%20Panel%20&utm_campaign=Plugin%20to%20Sale">', '</a>' ) . '</p>',
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
