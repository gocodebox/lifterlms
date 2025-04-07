<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LifterLMS Bricks Lesson Progression class.
 *
 * @since 8.0.3
 */
class LLMS_Bricks_Element_Lesson_Progression extends \Bricks\Element {
	public $block        = 'llms/lesson-progression';
	public $category     = 'lifterlms';
	public $name         = 'llms-lesson-progression';
	public $icon         = 'llms-bricks-icon llms-bricks-icon-lesson-progression';
	public $css_selector = '.llms-lesson-progression-wrapper';
	public $scripts      = array();

	public function get_label() {
		return esc_html__( 'Lesson Progression (Mark Complete)', 'lifterlms' );
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
		$root_classes[] = 'llms-lesson-progression-wrapper';

		$this->set_attribute( '_root', 'class', $root_classes );

		echo "<div {$this->render_attributes( '_root' )}>"; // Element root attributes

		echo do_shortcode( '[lifterlms_lesson_mark_complete]' );

		echo '</div>';
	}
}
