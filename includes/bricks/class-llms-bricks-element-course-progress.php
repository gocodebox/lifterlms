<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LifterLMS Bricks Course Progress class.
 *
 * @since 8.0.3
 */
class LLMS_Bricks_Element_Course_Progress extends \Bricks\Element {
	public $block        = 'llms/course-progress';
	public $category     = 'lifterlms';
	public $name         = 'llms-course-progress';
	public $icon         = 'llms-bricks-icon llms-bricks-icon-course-progress';
	public $css_selector = '.llms-course-progress-wrapper';
	public $scripts      = array();

	public function get_label() {
		return esc_html__( 'Course Progress', 'lifterlms' );
	}

	public function set_control_groups() {
	}

	public function set_controls() {
	}

	public function enqueue_scripts() {
	}

	public function convert_block_to_element_settings( $block, $attributes ) {
		// Need to return an array of something for it to be converted.
		return array( 'setting' => true );
	}

	public function render() {
		$root_classes[] = 'llms-course-progress-wrapper';

		$this->set_attribute( '_root', 'class', $root_classes );

		echo "<div {$this->render_attributes( '_root' )}>"; // Element root attributes

		echo do_shortcode( '[lifterlms_course_progress]' );

		echo '</div>';
	}
}
