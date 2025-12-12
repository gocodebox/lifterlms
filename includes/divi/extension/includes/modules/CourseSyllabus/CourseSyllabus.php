<?php

class LLMS_Divi_Course_Syllabus extends ET_Builder_Module {

	public $slug       = 'lifterlms_divi_course_syllabus';
	public $vb_support = 'on';
	public $icon_path;

	protected $module_credits = array(
		'module_uri' => 'https://lifterlms.com/',
		'author'     => 'LifterLMS',
		'author_uri' => 'https://lifterlms.com',
	);

	public function init() {
		$this->name      = esc_html__( 'LifterLMS Course Syllabus', 'lifterlms' );
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
			'course_id'      => array(
				'label'            => esc_html__( 'Course', 'lifterlms' ),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'options'          => $this->get_course_options(),
				'default'          => 'current',
				'computed_affects' => array( '__preview_html' ),
				'description'      => esc_html__( 'Select a course or leave blank to use the current page.', 'lifterlms' ),
			),
			'__preview_html' => array(
				'type'                => 'computed',
				'computed_callback'   => array( 'LLMS_Divi_Course_Syllabus', 'get_preview_html' ),
				'computed_depends_on' => array( 'course_id', 'dummy' ),
			),
		);
	}

	private function get_course_options() {
		$options = array(
			'current' => esc_html__( 'Select a course', 'lifterlms' ),
		);

		$courses = get_posts(
			array(
				'post_type'      => 'course',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'post_status'    => 'publish',
			)
		);

		foreach ( $courses as $course ) {
			$options[ $course->ID ] = $course->post_title;
		}

		return $options;
	}

	static function get_preview_html( $args = array(), $conditional_tags = array(), $current_page = array() ) {
		global $post;
		$course_id = ( ! empty( $args['course_id'] ) && is_numeric( $args['course_id'] ) ) ? $args['course_id'] : $current_page['id'];

		if ( ! $course_id ) {
			return __( 'Cannot show preview. No course found.', 'lifterlms' );
		}

		$current_post = $post;

		$post   = get_post( $course_id );
		$output = do_shortcode( '[lifterlms_course_syllabus course_id="' . absint( $course_id ) . '"]' );
		$post   = $current_post;

		return $output;
	}

	public function render( $attrs, $content, $render_slug ) {
		global $post;

		$course_id = ! empty( $this->props['course_id'] ) ? $this->props['course_id'] : ( $post ? $post->ID : 0 );
		return do_shortcode( '[lifterlms_course_syllabus course_id="' . absint( $course_id ) . '"]' );
	}
}

new LLMS_Divi_Course_Syllabus();
