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
		return class_exists( '\Bricks\Elements' );
	}

	protected function init() {

		if ( ! $this->is_available() ) {
			return;
		}

		add_action( 'init', array( $this, 'register_elements' ), 11 );
		add_filter( 'bricks/builder/i18n', array( $this, 'i18n' ) );

		// Prevent uneditable llms post types from being enabled for page building.
		// add_filter( 'fl_builder_admin_settings_post_types', array( $this, 'remove_uneditable_post_types' ) );
		//
		// Add migrateable post types to the builder by default.
		// add_filter( 'fl_builder_post_types', array( $this, 'enable_post_types_by_default' ) );

		// add_action( 'wp', array( $this, 'load_modules' ), 1 );
		// add_action( 'init', array( $this, 'load_templates' ) );
		//
		// add_filter( 'fl_builder_register_module', array( $this, 'register_module' ), 10, 2 );
		//
		// add_filter( 'llms_page_restricted', array( $this, 'mod_page_restrictions' ), 999, 2 );
		//
		// add_filter( 'fl_builder_register_settings_form', array( $this, 'add_visibility_settings' ), 999, 2 );
		//
		// add_filter( 'fl_builder_is_node_visible', array( $this, 'is_node_visible' ), 10, 2 );
		//
		// Hide editors when builder is enabled for a post.
		// add_filter( 'llms_metabox_fields_lifterlms_course_options', array( $this, 'mod_metabox_fields' ) );
		// add_filter( 'llms_metabox_fields_lifterlms_membership', array( $this, 'mod_metabox_fields' ) );
		//
		// add_filter( 'fl_builder_upgrade_url', array( $this, 'upgrade_url' ) );
		//
		// LifterLMS Private Areas.
		// add_action( 'llms_pa_before_do_area_content', array( $this, 'llms_pa_before_content' ) );
		// add_action( 'llms_pa_after_do_area_content', array( $this, 'llms_pa_after_content' ) );
	}

	public function register_elements() {

		$element_files = glob( LLMS_PLUGIN_DIR . 'includes/bricks/class-llms-bricks-element-*.php' );

		foreach ( $element_files as $file ) {
			\Bricks\Elements::register_element( $file );
		}
	}

	public function i18n( $i18n ) {
		$i18n['lifterlms'] = esc_html__( 'LifterLMS', 'lifterlms' );

		return $i18n;
	}
}

return new LLMS_Bricks();
