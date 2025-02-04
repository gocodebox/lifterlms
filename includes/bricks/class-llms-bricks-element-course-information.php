<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LLMS_Bricks_Element_Course_Information extends \Bricks\Element {
	public $category     = 'lifterlms'; // Use predefined element category 'general'
	public $name         = 'llms-course-information'; // Make sure to prefix your elements
	public $icon         = 'ti-bolt-alt'; // Themify icon font class
	public $css_selector = '.llms-course-information-wrapper'; // Default CSS selector
	public $scripts      = array(); // Script(s) run when element is rendered on frontend or updated in builder

	// Return localised element label
	public function get_label() {
		return esc_html__( 'Course Information', 'lifterlms' );
	}

	// Set builder control groups
	public function set_control_groups() {
		// $this->control_groups['settings'] = array(
		// 'title' => esc_html__( 'Settings', 'bricks' ),
		// 'tab'   => 'content',
		// );
	}

	// Set builder controls
	public function set_controls() {
		// Convert to nested elements.
		// $this->controls['title'] = array( // Unique control identifier (lowercase, no spaces)
		// 'tab'     => 'content', // Control tab: content/style
		// 'label'   => esc_html__( 'Title', 'lifterlms' ), // Control label
		// 'type'    => 'text', // Control type
		// 'default' => esc_html__( 'Course Information', 'lifterlms' ), // Default setting
		// );
		//
		// $this->controls['title_size'] = array(
		// 'tab'         => 'content',
		// 'group' => 'settings',
		// 'label'       => esc_html__( 'Title Headline Size', 'lifterlms' ),
		// 'type'        => 'select',
		// 'options'     => array(
		// 'h1' => esc_html__( 'h1', 'lifterlms' ),
		// 'h2' => esc_html__( 'h2', 'lifterlms' ),
		// 'h3' => esc_html__( 'h3', 'lifterlms' ),
		// 'h4' => esc_html__( 'h4', 'lifterlms' ),
		// 'h5' => esc_html__( 'h5', 'lifterlms' ),
		// 'h6' => esc_html__( 'h6', 'lifterlms' ),
		// ),
		// 'inline'      => true,
		// 'clearable'   => false,
		// 'pasteStyles' => false,
		// 'default'     => 'h2',
		// );
	}

	public function enqueue_scripts() {
		// wp_enqueue_script( 'prefix-test-script' );
	}

	// Render element HTML
	public function render() {
		$root_classes[] = 'llms-course-information-wrapper';

		$this->set_attribute( '_root', 'class', $root_classes );

		echo "<div {$this->render_attributes( '_root' )}>"; // Element root attributes

		// echo wp_kses_post( "<{$title_size} class='llms-meta-title'>{$title}</{$title_size}>" );

		echo do_shortcode( '[lifterlms_course_meta_info]' );

		echo '</div>';
	}
}
