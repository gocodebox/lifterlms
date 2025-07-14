<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LifterLMS Course Author class.
 *
 * @since 8.0.3
 */
class LLMS_Bricks_Element_Course_Author extends \Bricks\Element {
	public $block        = 'llms/course-author';
	public $category     = 'lifterlms';
	public $name         = 'llms-course-author';
	public $icon         = 'llms-bricks-icon llms-bricks-icon-course-author';
	public $css_selector = '.llms-course-author-wrapper';
	public $scripts      = array();

	public function get_label() {
		return esc_html__( 'Course Author', 'lifterlms' );
	}

	public function set_control_groups() {
	}

	public function set_controls() {
		$this->controls['avatar_size'] = array(
			'tab'         => 'content',
			'label'       => esc_html__( 'Avatar size', 'lifterlms' ),
			'type'        => 'slider',
			'units'       => array(
				'px' => array(
					'min'  => 1,
					'max'  => 300,
					'step' => 1,
				),
			),
			'default'     => 48,
			'description' => esc_html__( 'The size of the avatar in pixels.', 'lifterlms' ),
		);

		$this->controls['bio'] = array(
			'tab'     => 'content',
			'label'   => esc_html__( 'Display Bio', 'lifterlms' ),
			'type'    => 'checkbox',
			'inline'  => false,
			'small'   => true,
			'default' => true, // Default: false
		);

		$courses_posts = get_posts(
			array(
				'post_type'      => 'course',
				'posts_per_page' => -1,         // Retrieve all posts
				'post_status'    => 'publish',   // Only published posts
			)
		);
		$courses       = array(
			'inherit' => __( 'Inherit from current course', 'lifterlms' ),
		);
		foreach ( $courses_posts as $course ) {
			$courses[ $course->ID ] = $course->post_title;
		}

		$this->controls['course_id'] = array(
			'tab'         => 'content',
			'label'       => esc_html__( 'Course', 'lifterlms' ),
			'type'        => 'select',
			'options'     => $courses,
			'inline'      => false,
			'clearable'   => false,
			'pasteStyles' => false,
			'default'     => 'inherit',
		);
	}

	public function enqueue_scripts() {
	}

	public function convert_block_to_element_settings( $block, $attributes ) {
		$element_settings = array(
			'avatar_size' => isset( $attributes['avatar_size'] ) ? intval( $attributes['avatar_size'] ) : 48,
			'bio'         => ( isset( $attributes['bio'] ) && 'no' === $attributes['bio'] ) ? false : true,
			'course_id'   => isset( $attributes['course_id'] ) ? intval( $attributes['course_id'] ) : 'inherit',
		);

		return $element_settings;
	}

	public function render() {
		$root_classes[] = 'llms-course-author-wrapper';

		$this->set_attribute( '_root', 'class', $root_classes );

		$avatar_size = isset( $this->settings['avatar_size'] ) && $this->settings['avatar_size'] ? intval( $this->settings['avatar_size'] ) : 48;
		$bio         = isset( $this->settings['bio'] ) && $this->settings['bio'] ? '' : 'no';
		$course_id   = isset( $this->settings['course_id'] ) && is_numeric( $this->settings['course_id'] ) ? intval( $this->settings['course_id'] ) : '';

		echo "<div {$this->render_attributes( '_root' )}>"; // Element root attributes

		echo do_shortcode( '[lifterlms_course_author avatar_size="' . esc_attr( $avatar_size ) . '" bio="' . esc_attr( $bio ) . '" course_id="' . $course_id . '"]' );

		echo '</div>';
	}
}
