<?php
/**
 * File Summary
 *
 * File description.
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

class LLMS_Admin_Screen_Options {

	protected $options = array();

	use LLMS_Trait_Singleton;

	private function __construct() {

		add_filter( 'screen_settings', array( $this, 'render' ) );
		$this->add_group( 'options', __( 'Screen Options', 'lifterlms' ) );

	}

	protected function get_screen_id() {

		$screen = get_current_screen();
		return $screen->id;

	}

	protected function get_value( $id, $default = '' ) {

		$opts = get_user_option( 'screen_opts_' . $this->get_screen_id() );
		$opts = is_array( $opts ) ? $opts : array();
		return $opts[ $id ] ?? $default;

	}

	public function add_group( $id, $label ) {

		if ( ! isset( $this->options[ $id ] ) ) {
			$this->options[ $id ] = array(
				'label'   => $label,
				'options' => array(),
			);
		}

	}

	public function add( $id, $args = array() ) {

		$args = wp_parse_args( $args, array(
			'label'   => $id,
			'name'    => $id,
			'default' => '',
			'value'   => $this->get_value( $id, $args['default'] ?? '' ),
			'type'    => 'text',
			'group'   => 'options',
		) );

		if ( ! isset( $this->options[ $args['group'] ] ) ) {
			$this->add_group( $args['group'], $args['group'] );
		}

		$this->options[ $args['group'] ]['options'][ $id ] = $args;

		add_filter( 'screen_options_show_screen', '__return_true' );
		add_filter( 'screen_options_show_submit', '__return_true' );

		return $args;

	}

	public function render( $settings ) {

		$groups = $this->options;

		ob_start();
		include LLMS_PLUGIN_DIR . '/includes/admin/views/screen-options.php';
		$settings .= ob_get_clean();

		return $settings;

	}


		// add_action( 'load-lifterlms_page_llms-reporting', function() {

		// 	// $screen = get_current_screen();

		// 	// $screen->add_option( 'per_page', array( 'default' => 25, 'option' => 'llms_reporting_per_page' ) );
		// 	// $screen->add_option( 'test', array( 'label' => 'My Label', 'default' => 1, 'option' => 'option_name' ) );

		// } );

		// add_filter( 'screen_settings', function( $settings ) {

		// 	add_filter( 'screen_options_show_submit', '__return_true' );
		// 	$settings .= '<input type="text" value="arst">';

		// 	return $settings;

		// } );

}
