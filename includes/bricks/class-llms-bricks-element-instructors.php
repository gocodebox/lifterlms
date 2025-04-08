<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LifterLMS Bricks Instructors class.
 *
 * @since 8.0.3
 */
class LLMS_Bricks_Element_Instructors extends \Bricks\Element {
	public $block        = 'llms/instructors';
	public $category     = 'lifterlms';
	public $name         = 'llms-instructors';
	public $icon         = 'llms-bricks-icon llms-bricks-icon-instructors';
	public $css_selector = '.llms-instructors';
	public $scripts      = array();

	public function get_label() {
		return esc_html__( 'Instructors', 'lifterlms' );
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
		$root_classes[] = 'llms-instructors';

		$this->set_attribute( '_root', 'class', $root_classes );

		echo "<div {$this->render_attributes( '_root' )}>"; // Element root attributes

		if ( 'llms_membership' === get_post_type() ) {
			echo do_shortcode( '[lifterlms_membership_instructors]' );
		} else {
			echo do_shortcode( '[lifterlms_course_instructors]' );
		}

		echo '</div>';
	}
}
