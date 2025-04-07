<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LifterLMS Bricks Pricing Table class.
 *
 * @since 8.0.3
 */
class LLMS_Bricks_Element_Pricing_Table extends \Bricks\Element {
	public $block        = 'llms/pricing-table';
	public $category     = 'lifterlms';
	public $name         = 'llms-pricing-table';
	public $icon         = 'llms-bricks-icon llms-bricks-icon-pricing-table';
	public $css_selector = '.llms-pricing-table-wrapper';
	public $scripts      = array();

	public function get_label() {
		return esc_html__( 'LifterLMS Pricing Table', 'lifterlms' );
	}

	public function set_control_groups() {
	}

	public function set_controls() {
	}

	public function enqueue_scripts() {
	}

	public function convert_block_to_element_settings( $block, $attributes ) {
		// TODO: Visibility settings.
		// Need to return an array of something for it to be converted.
		return array( 'setting' => true );
	}

	public function render() {
		$root_classes[] = 'llms-pricing-table-wrapper';

		$this->set_attribute( '_root', 'class', $root_classes );

		echo "<div {$this->render_attributes( '_root' )}>"; // Element root attributes

		echo do_shortcode( '[lifterlms_pricing_table]' );

		echo '</div>';
	}
}
