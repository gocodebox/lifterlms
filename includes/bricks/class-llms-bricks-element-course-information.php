<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LifterLMS Bricks Course Information class.
 *
 * @since 8.0.3
 */
class LLMS_Bricks_Element_Course_Information extends \Bricks\Element {
	public $block        = 'llms/course-information';
	public $category     = 'lifterlms';
	public $name         = 'llms-course-information';
	public $icon         = 'llms-bricks-icon llms-bricks-icon-course-information';
	public $css_selector = '.llms-course-information-wrapper';
	public $scripts      = array();

	public function get_label() {
		return esc_html__( 'Course Information', 'lifterlms' );
	}

	public function set_control_groups() {
	}

	public function set_controls() {
		// Convert to nested elements.
		$this->controls['title'] = array(
			'tab'     => 'content',
			'label'   => esc_html__( 'Title', 'lifterlms' ),
			'type'    => 'text',
			'default' => esc_html__( 'Course Information', 'lifterlms' ),
		);

		$this->controls['title_size'] = array(
			'tab'         => 'content',
			// 'group' => 'settings',
			'label'       => esc_html__( 'Title Headline Size', 'lifterlms' ),
			'type'        => 'select',
			'options'     => array(
				'h1' => esc_html__( 'h1', 'lifterlms' ),
				'h2' => esc_html__( 'h2', 'lifterlms' ),
				'h3' => esc_html__( 'h3', 'lifterlms' ),
				'h4' => esc_html__( 'h4', 'lifterlms' ),
				'h5' => esc_html__( 'h5', 'lifterlms' ),
				'h6' => esc_html__( 'h6', 'lifterlms' ),
			),
			'inline'      => true,
			'clearable'   => false,
			'pasteStyles' => false,
			'default'     => 'h2',
		);
	}

	public function enqueue_scripts() {
	}

	public function convert_block_to_element_settings( $block, $attributes ) {
		$element_settings = array(
			'title'      => isset( $attributes['title'] ) ? $attributes['title'] : __( 'Course Information', 'lifterlms' ),
			'title_size' => isset( $attributes['title_size'] ) ? $attributes['title_size'] : 'h2',
		);

		return $element_settings;
	}

	public function render() {
		$root_classes[] = 'llms-course-information-wrapper';

		$this->set_attribute( '_root', 'class', $root_classes );

		$title      = $this->settings['title'] ? $this->settings['title'] : __( 'Course Information', 'lifterlms' );
		$title_size = $this->settings['title_size'] ? $this->settings['title_size'] : 'h2';

		echo "<div {$this->render_attributes( '_root' )}>"; // Element root attributes

		echo wp_kses_post( "<{$title_size} class='llms-meta-title'>{$title}</{$title_size}>" );

		echo do_shortcode( '[lifterlms_course_meta_info]' );

		echo '</div>';
	}
}
