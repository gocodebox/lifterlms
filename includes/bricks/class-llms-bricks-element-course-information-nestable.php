<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LLMS_Bricks_Element_Course_Information_Nested extends \Bricks\Element {
	public $block        = array( 'llms/course-information' );
	public $category     = 'lifterlms'; // Use predefined element category 'general'
	public $name         = 'llms-course-information-nestable'; // Make sure to prefix your elements
	public $icon         = 'ti-bolt-alt'; // Themify icon font class
	public $css_selector = '.llms-course-information-wrapper'; // Default CSS selector
	public $scripts      = array(); // Script(s) run when element is rendered on frontend or updated in builder

	// Return localised element label
	public function get_label() {
		return esc_html__( 'Course Information (Nestable)', 'lifterlms' );
	}

	// Set builder control groups
	public function set_control_groups() {
		// $this->control_groups['settings'] = array(
		// 'title' => esc_html__( 'Settings', 'bricks' ),
		// 'tab'   => 'content',
		// );
	}

	public function get_nestable_children() {
		error_log( 'get_nestable_children' );
		error_log( print_r( $this->settings, true ) );
		return array(
			array(
				'name'     => 'heading',
				'settings' => array(
					'text' => isset( $this->settings['title'] ) ? $this->settings['title'] : esc_html__( 'Course Information', 'lifterlms' ),
					'tag'  => isset( $this->settings['title_size'] ) ? $this->settings['title_size'] : 'h2',
				),
			),
			array(
				'name' => 'llms-course-information',
			),
		);
	}

	public function enqueue_scripts() {
		// wp_enqueue_script( 'prefix-test-script' );
	}

	public function convert_block_to_element_settings( $block, $attributes ) {
		$element_settings = array(
			'title'      => isset( $attributes['title'] ) ? $attributes['title'] : __( 'Course Information', 'lifterlms' ),
			'title_size' => isset( $attributes['title_size'] ) ? $attributes['title_size'] : 'h2',
		);
		error_log( 'convert block to element' );
		error_log( print_r( $element_settings, true ) );
		return $element_settings;
	}

	// Render element HTML
	public function render() {
		$root_classes[] = 'llms-course-information-wrapper';

		$this->set_attribute( '_root', 'class', $root_classes );

		$title      = $this->settings['title'] ? $this->settings['title'] : __( 'Course Information', 'lifterlms' );
		$title_size = $this->settings['title_size'] ? $this->settings['title_size'] : 'h2';

		echo "<div {$this->render_attributes( '_root' )}>"; // Element root attributes

		echo \Bricks\Frontend::render_children( $this );

		//
		// echo wp_kses_post( "<{$title_size} class='llms-meta-title'>{$title}</{$title_size}>" );
		//
		// echo do_shortcode( '[lifterlms_course_meta_info]' );

		echo '</div>';
	}
}
