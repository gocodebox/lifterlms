<?php

class LLMS_Divi_Course_Instructors extends ET_Builder_Module {

	public $slug       = 'lifterlms_divi_course_instructors';
	public $vb_support = 'on';
	public $icon_path;

	protected $module_credits = array(
		'module_uri' => 'https://lifterlms.com/',
		'author'     => 'LifterLMS',
		'author_uri' => 'https://lifterlms.com',
	);

	public function init() {
		$this->name      = esc_html__( 'LifterLMS Course Instructors', 'lifterlms' );
		$this->icon_path = LLMS_PLUGIN_DIR . 'assets/images/lifterlms-icon-grey.svg';
	}

	public function get_fields() {
		return array(
			'dummy'          => array(
				'type'             => 'hidden',
				'default'          => '1',
				'option_category'  => 'basic_option',
				'computed_affects' => array( '__preview_html' ),
			),
			'__preview_html' => array(
				'type'                => 'computed',
				'computed_callback'   => array( 'LLMS_Divi_Course_Instructors', 'get_preview_html' ),
				'computed_depends_on' => array( 'dummy' ),
			),
		);
	}

	static function get_preview_html( $args = array(), $conditional_tags = array(), $current_page = array() ) {
		global $post;
		global $id, $authordata, $currentday, $currentmonth, $page, $pages, $multipage, $more, $numpages;

		$current_post = $post;
		$course_id    = absint( $current_page['id'] );
		$post         = get_post( $course_id );
		setup_postdata( $post );

		$output = do_shortcode( '[lifterlms_course_instructors]' );
		$post   = $current_post;

		if ( $post ) {
			setup_postdata( $post );
		} else {
			$id = $authordata = $current_day = $currentmonth = $page = $pages = $multipage = $more = $numpages = null;
		}

		return $output;
	}

	public function render( $attrs, $content, $render_slug ) {
		return do_shortcode( '[lifterlms_course_instructors]' );
	}
}

new LLMS_Divi_Course_Instructors();
