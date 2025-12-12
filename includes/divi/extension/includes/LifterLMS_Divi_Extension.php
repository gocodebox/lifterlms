<?php

class LifterLMS_Divi_Extension extends DiviExtension {

	/**
	 * The gettext domain for the extension's translations.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $gettext_domain = 'lifterlms';

	/**
	 * The extension's WP Plugin name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $name = 'lifterlms-divi';

	/**
	 * The extension's version
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	public function __construct( $name = 'lifterlms-divi', $args = array() ) {
		$this->plugin_dir     = plugin_dir_path( __FILE__ );
		$this->plugin_dir_url = plugin_dir_url( $this->plugin_dir );

		parent::__construct( $name, $args );

		// Add field to Divi post settings modal.
		add_filter( 'et_builder_post_settings_fields', array( $this, 'add_post_settings_field' ), 10, 2 );

		// Remove default LifterLMS content hooks if setting is enabled.
		add_action( 'template_redirect', array( $this, 'maybe_remove_default_llms_content' ) );
	}

	/**
	 * Add field to Divi post settings modal
	 *
	 * @since 1.0.0
	 *
	 * @param array  $fields Existing fields.
	 * @param string $post_type Current post type.
	 * @return array
	 */
	public function add_post_settings_field( $fields, $post_type ) {
		if ( 'course' !== $post_type ) {
			return $fields;
		}

		$fields['llms_disable_default_content'] = array(
			'label'            => esc_html__( 'Disable Default LifterLMS Content', 'lifterlms' ),
			'type'             => 'yes_no_button',
			'option_category'  => 'configuration',
			'options'          => array(
				'off' => esc_html__( 'No', 'lifterlms' ),
				'on'  => esc_html__( 'Yes', 'lifterlms' ),
			),
			'default'          => 'off',
			'description'       => esc_html__( 'Enable this to disable the default LifterLMS content output at the bottom of the course page. Use this when you have manually added LifterLMS Divi modules.', 'lifterlms' ),
			'show_if'          => array(),
			'show_if_not'      => array(),
			'tab_slug'         => 'general',
			'toggle_slug'      => 'main_content',
		);

		return $fields;
	}

	/**
	 * Remove default LifterLMS content hooks if Divi setting is enabled
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function maybe_remove_default_llms_content() {
		if ( ! is_singular( 'course' ) ) {
			return;
		}

		global $post;
		$post_settings = get_post_meta( $post->ID, '_et_pb_use_builder', true ) ? get_post_meta( $post->ID, '_et_pb_post_settings', true ) : array();
		$disable_default = isset( $post_settings['llms_disable_default_content'] ) ? $post_settings['llms_disable_default_content'] : 'off';

		if ( 'on' === $disable_default ) {
			$this->remove_default_llms_hooks();
		}
	}

	/**
	 * Remove default LifterLMS content hooks
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function remove_default_llms_hooks() {
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_meta_wrapper_start', 5 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_length', 10 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_difficulty', 20 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_course_tracks', 25 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_course_categories', 30 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_course_tags', 35 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_course_author', 40 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_meta_wrapper_end', 50 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_prerequisites', 55 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_pricing_table', 60 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_course_progress', 60 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_syllabus', 90 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_reviews', 100 );
	}
}

new LifterLMS_Divi_Extension();
