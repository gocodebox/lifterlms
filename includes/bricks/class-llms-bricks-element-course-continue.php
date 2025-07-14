<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LifterLMS Bricks Course Progress with Continue Button class.
 *
 * @since 8.0.3
 */
class LLMS_Bricks_Element_Course_Continue extends \Bricks\Element {
	public $block        = 'llms/course-continue';
	public $category     = 'lifterlms';
	public $name         = 'llms-course-continue';
	public $icon         = 'llms-bricks-icon llms-bricks-icon-course-continue';
	public $css_selector = '.llms-course-continue';
	public $scripts      = array();

	public function get_label() {
		return esc_html__( 'Course Progress with Continue Button', 'lifterlms' );
	}

	public function set_control_groups() {
	}

	public function set_controls() {
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
			'course_id' => isset( $attributes['course_id'] ) ? intval( $attributes['course_id'] ) : 'inherit',
		);

		return $element_settings;
	}

	public function render() {
		$root_classes[] = 'llms-course-meta-info';

		$this->set_attribute( '_root', 'class', $root_classes );

		$course_id = isset( $this->settings['course_id'] ) && is_numeric( $this->settings['course_id'] ) ? intval( $this->settings['course_id'] ) : '';

		echo "<div {$this->render_attributes( '_root' )}>"; // Element root attributes

		echo do_shortcode( '[lifterlms_course_continue course_id="' . $course_id . '"]' );

		echo '</div>';
	}
}
