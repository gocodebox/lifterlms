<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LLMS_Bricks {

	use LLMS_Trait_Singleton;

	public function __construct() {
		$this->init();
	}

	public function is_available() {
		return class_exists( '\Bricks\Elements' ) && function_exists( 'bricks_is_builder' );
	}

	protected function init() {

		if ( ! $this->is_available() ) {
			return;
		}

		add_action( 'init', array( $this, 'register_elements' ), 11 );
		add_action( 'init', array( $this, 'add_builder_css' ), 11 );
		add_filter( 'bricks/builder/i18n', array( $this, 'i18n' ) );
	}

	public function register_elements() {

		$element_files = glob( LLMS_PLUGIN_DIR . 'includes/bricks/class-llms-bricks-element-*.php' );

		foreach ( $element_files as $file ) {
			\Bricks\Elements::register_element( $file );
		}
	}

	public function add_builder_css() {
		if ( ! bricks_is_builder() ) {
			return;
		}
		wp_enqueue_style( 'llms-bricks-editor', LLMS_PLUGIN_URL . 'assets/css/bricks-editor.css', array(), filemtime( LLMS_PLUGIN_DIR . 'assets/css/bricks-editor.css' ) );
	}

	public function i18n( $i18n ) {
		$i18n['lifterlms'] = esc_html__( 'LifterLMS', 'lifterlms' );

		return $i18n;
	}
}

return new LLMS_Bricks();
