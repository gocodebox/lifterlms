<?php

class LLMS_Divi_Course_Continue_Button extends ET_Builder_Module {

	public $slug       = 'lifterlms_course_continue_button';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://lifterlms.com/',
		'author'     => 'LifterLMS',
		'author_uri' => 'https://lifterlms.com',
	);

	public function init() {
		$this->name = esc_html__( 'LifterLMS Course Continue Button', 'lifterlms' );
	}

	public function get_fields() {
		return array(
			'dummy'          => array(
				'type'             => 'hidden',
				'default'          => '1',
				'computed_affects' => array( '__preview_html' ),
			),
			'__preview_html' => array(
				'type'                => 'computed',
				'computed_callback'   => array( 'LLMS_Divi_Course_Continue_Button', 'get_preview_html' ),
				'computed_depends_on' => array( 'dummy' ), // nothing to depend on
			),
		);
	}

	static function get_preview_html( $args = array(), $conditional_tags = array(), $current_page = array() ) {
		return do_shortcode( '[lifterlms_course_continue_button course_id="' . absint( $current_page['id'] ) . '"]' );
	}

	public function render( $attrs, $content, $render_slug ) {
		return do_shortcode( '[lifterlms_course_continue_button]' );
	}
}

new LLMS_Divi_Course_Continue_Button();
