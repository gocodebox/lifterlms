<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LLMS_Bricks_Element_Course_Information extends \Bricks\Element {
	// Element properties
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
		$this->control_groups['text'] = array( // Unique group identifier (lowercase, no spaces)
			'title' => esc_html__( 'Text', 'bricks' ), // Localized control group title
			'tab'   => 'content', // Set to either "content" or "style"
		);

		$this->control_groups['settings'] = array(
			'title' => esc_html__( 'Settings', 'bricks' ),
			'tab'   => 'content',
		);
	}

	// Set builder controls
	public function set_controls() {
		$this->controls['content'] = array( // Unique control identifier (lowercase, no spaces)
			'tab'     => 'content', // Control tab: content/style
			'group'   => 'text', // Show under control group
			'label'   => esc_html__( 'Content', 'bricks' ), // Control label
			'type'    => 'text', // Control type
			'default' => esc_html__( 'Content goes here ..', 'bricks' ), // Default setting
		);

		$this->controls['type'] = array(
			'tab'         => 'content',
			'group'       => 'settings',
			'label'       => esc_html__( 'Type', 'bricks' ),
			'type'        => 'select',
			'options'     => array(
				'info'    => esc_html__( 'Info', 'bricks' ),
				'success' => esc_html__( 'Success', 'bricks' ),
				'warning' => esc_html__( 'Warning', 'bricks' ),
				'danger'  => esc_html__( 'Danger', 'bricks' ),
				'muted'   => esc_html__( 'Muted', 'bricks' ),
			),
			'inline'      => true,
			'clearable'   => false,
			'pasteStyles' => false,
			'default'     => 'info',
		);
	}

	// Enqueue element styles and scripts
	public function enqueue_scripts() {
		// wp_enqueue_script( 'prefix-test-script' );
	}

	// Render element HTML
	public function render() {
		// Set element attributes
		$root_classes[] = 'llms-course-information-wrapper';
		//
		// if ( ! empty( $this->settings['type'] ) ) {
		// $root_classes[] = "color-{$this->settings['type']}";
		// }

		// Add 'class' attribute to element root tag
		$this->set_attribute( '_root', 'class', $root_classes );

		echo "<div {$this->render_attributes( '_root' )}>"; // Element root attributes

		echo do_shortcode( '[lifterlms_course_meta_info]' );

		echo '</div>';
	}
}
